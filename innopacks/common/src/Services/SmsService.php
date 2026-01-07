<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\VerifyCode;
use Overtrue\EasySms\EasySms;

class SmsService
{
    /**
     * Send SMS verification code
     *
     * @param  string  $callingCode
     * @param  string  $telephone
     * @param  string  $type
     * @return void
     * @throws Exception
     */
    public function sendVerificationCode(string $callingCode, string $telephone, string $type = 'register'): void
    {
        // Generate cryptographically secure random code
        $codeLength = (int) system_setting('sms_code_length', 6);
        $min        = (int) pow(10, $codeLength - 1);
        $max        = (int) pow(10, $codeLength) - 1;
        $code       = random_int($min, $max);
        $fullPhone  = $callingCode.$telephone;

        // Delete old verification codes of the same type
        VerifyCode::query()
            ->where('account', $fullPhone)
            ->where('type', $type)
            ->delete();

        // Create new verification code
        VerifyCode::query()->create([
            'account' => $fullPhone,
            'code'    => $code,
            'type'    => $type,
        ]);

        // Send SMS (implement actual SMS sending logic here)
        $this->sendSms($callingCode, $telephone, $code, $type);

        // Log without exposing the verification code for security
        Log::info("SMS verification code sent to {$fullPhone}");
    }

    /**
     * Verify SMS code
     *
     * @param  string  $callingCode
     * @param  string  $telephone
     * @param  string  $code
     * @param  string  $type
     * @return bool
     */
    public function verifyCode(string $callingCode, string $telephone, string $code, string $type = 'register'): bool
    {
        $fullPhone  = $callingCode.$telephone;
        $verifyCode = VerifyCode::query()
            ->where('account', $fullPhone)
            ->where('code', $code)
            ->where('type', $type)
            ->whereNotNull('created_at')
            ->first();

        if (! $verifyCode) {
            return false;
        }

        // Check if code is expired (configurable, default 10 minutes)
        $expireMinutes = (int) system_setting('sms_code_expire_minutes', 10);

        // Ensure created_at is not null
        if (! $verifyCode->created_at) {
            $verifyCode->delete();

            return false;
        }

        if ($verifyCode->created_at->addMinutes($expireMinutes)->lt(now())) {
            $verifyCode->delete();

            return false;
        }

        return true;
    }

    /**
     * Delete verification code after use
     *
     * @param  string  $callingCode
     * @param  string  $telephone
     * @return void
     */
    public function deleteCode(string $callingCode, string $telephone): void
    {
        $fullPhone = $callingCode.$telephone;
        VerifyCode::query()->where('account', $fullPhone)->delete();
    }

    /**
     * Send SMS (implement actual SMS gateway integration)
     *
     * @param  string  $callingCode
     * @param  string  $telephone
     * @param  string  $code
     * @param  string  $type
     * @return void
     * @throws Exception
     */
    private function sendSms(string $callingCode, string $telephone, string $code, string $type): void
    {
        // Format phone number for SMS gateway
        // Remove + from calling code if present
        $callingCode = ltrim($callingCode, '+');
        $message     = $this->getSmsMessage($code, $type);

        // Get SMS gateway configuration
        $gateway = system_setting('sms_gateway', '');
        if (empty($gateway)) {
            // If no gateway configured, log and fire hook
            $fullPhone = $callingCode.$telephone;
            Log::info("SMS to {$fullPhone}: {$message} (no gateway configured)");
            fire_hook_action('service.sms.send', [
                'calling_code' => $callingCode,
                'telephone'    => $telephone,
                'full_phone'   => $fullPhone,
                'code'         => $code,
                'message'      => $message,
                'type'         => $type,
            ]);

            return;
        }

        try {
            // Format phone number according to gateway requirements
            $formattedPhone = $this->formatPhoneNumber($gateway, $callingCode, $telephone);

            // Initialize EasySms
            $config  = $this->getEasySmsConfig($gateway);
            $easySms = new EasySms($config);

            // Send SMS (format depends on gateway)
            $smsData = $this->getSmsData($gateway, $message, $code, $type);

            // Log actual SMS content being sent
            $actualContent = $smsData['content'] ?? (is_string($smsData) ? $smsData : json_encode($smsData));
            Log::info('SMS content being sent', [
                'gateway'          => $gateway,
                'phone'            => $formattedPhone,
                'type'             => $type,
                'original_message' => $message,
                'actual_content'   => $actualContent,
                'sms_data'         => $smsData,
            ]);

            // Log request details for debugging (only in debug mode)
            if (config('app.debug')) {
                Log::debug('SMS request', [
                    'gateway'      => $gateway,
                    'phone'        => $formattedPhone,
                    'calling_code' => $callingCode,
                    'telephone'    => $telephone,
                    'sms_data'     => $smsData,
                ]);
            }

            $result = $easySms->send($formattedPhone, $smsData);

            // Full phone number for logging and hooks (with country code)
            $fullPhone = $callingCode.$telephone;

            Log::info("SMS sent successfully to {$fullPhone} via {$gateway}", ['result' => $result]);

            // Fire hook for SMS sending
            fire_hook_action('service.sms.send', [
                'calling_code' => $callingCode,
                'telephone'    => $telephone,
                'full_phone'   => $fullPhone,
                'code'         => $code,
                'message'      => $message,
                'type'         => $type,
                'gateway'      => $gateway,
                'result'       => $result,
            ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $e) {
            // Get detailed error messages from all failed gateways
            $exceptions    = $e->getExceptions();
            $errorMessages = [];

            foreach ($exceptions as $gateway => $exception) {
                $gatewayError    = $this->extractDetailedError($exception, $gateway);
                $errorMessages[] = "{$gateway}: {$gatewayError}";
                Log::error("SMS gateway [{$gateway}] error: {$gatewayError}", [
                    'exception' => $exception,
                    'gateway'   => $gateway,
                ]);
            }

            // Combine all error messages
            $errorMessage = ! empty($errorMessages)
                ? implode('; ', $errorMessages)
                : $e->getMessage();

            Log::error("SMS gateway error: {$errorMessage}");
            throw new Exception($errorMessage);
        } catch (\Overtrue\EasySms\Exceptions\GatewayErrorException $e) {
            // Handle gateway-specific errors
            $errorMessage = $this->extractDetailedError($e, $gateway ?? 'unknown');
            Log::error("SMS gateway error: {$errorMessage}", ['exception' => $e]);
            throw new Exception($errorMessage);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error("SMS send error: {$errorMessage}", ['exception' => $e]);
            throw new Exception($errorMessage);
        }
    }

    /**
     * Get EasySms configuration based on gateway
     *
     * @param  string  $gateway
     * @return array
     */
    private function getEasySmsConfig(string $gateway): array
    {
        $config = [
            'timeout' => 5.0,
            'default' => [
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                'gateways' => [$gateway],
            ],
            'gateways' => [],
        ];

        switch ($gateway) {
            case 'yunpian':
                $apiKey = system_setting('sms_yunpian_api_key', '');
                if (empty($apiKey)) {
                    throw new Exception('云片 API Key 未配置');
                }
                $config['gateways']['yunpian'] = [
                    'api_key' => $apiKey,
                ];
                break;

            case 'aliyun':
                $config['gateways']['aliyun'] = [
                    'access_key_id'     => system_setting('sms_aliyun_access_key_id', ''),
                    'access_key_secret' => system_setting('sms_aliyun_access_key_secret', ''),
                    'sign_name'         => system_setting('sms_aliyun_sign_name', ''),
                ];
                break;

            case 'tencent':
                $config['gateways']['qcloud'] = [
                    'sdk_app_id' => system_setting('sms_tencent_sdk_app_id', ''),
                    'secret_id'  => system_setting('sms_tencent_secret_id', ''),
                    'secret_key' => system_setting('sms_tencent_secret_key', ''),
                    'sign_name'  => system_setting('sms_tencent_sign_name', ''),
                ];
                break;

            case 'huawei':
                $config['gateways']['huawei'] = [
                    'endpoint'   => system_setting('sms_huawei_endpoint', ''),
                    'app_key'    => system_setting('sms_huawei_app_key', ''),
                    'app_secret' => system_setting('sms_huawei_app_secret', ''),
                    'from'       => system_setting('sms_huawei_from', ''),
                    'callback'   => system_setting('sms_huawei_callback', ''),
                ];
                break;

            case 'qiniu':
                $config['gateways']['qiniu'] = [
                    'access_key' => system_setting('sms_qiniu_access_key', ''),
                    'secret_key' => system_setting('sms_qiniu_secret_key', ''),
                ];
                break;

            case 'juhe':
                $config['gateways']['juhe'] = [
                    'key' => system_setting('sms_juhe_key', ''),
                ];
                break;

            case 'yunzhixun':
                $config['gateways']['yunzhixun'] = [
                    'sid'    => system_setting('sms_yunzhixun_sid', ''),
                    'token'  => system_setting('sms_yunzhixun_token', ''),
                    'app_id' => system_setting('sms_yunzhixun_app_id', ''),
                ];
                break;

            case 'huyi':
                $config['gateways']['huyi'] = [
                    'api_id'  => system_setting('sms_huyi_api_id', ''),
                    'api_key' => system_setting('sms_huyi_api_key', ''),
                ];
                break;

            case 'luosimao':
                $config['gateways']['luosimao'] = [
                    'api_key' => system_setting('sms_luosimao_api_key', ''),
                ];
                break;

            case 'yuntongxun':
                $config['gateways']['yuntongxun'] = [
                    'app_id'        => system_setting('sms_yuntongxun_app_id', ''),
                    'account_sid'   => system_setting('sms_yuntongxun_account_sid', ''),
                    'account_token' => system_setting('sms_yuntongxun_account_token', ''),
                ];
                break;

            case 'rongcloud':
                $config['gateways']['rongcloud'] = [
                    'app_key'    => system_setting('sms_rongcloud_app_key', ''),
                    'app_secret' => system_setting('sms_rongcloud_app_secret', ''),
                ];
                break;

            case 'avatardata':
                $config['gateways']['avatardata'] = [
                    'app_key' => system_setting('sms_avatardata_app_key', ''),
                ];
                break;

            case 'baiwu':
                $config['gateways']['baiwu'] = [
                    'username' => system_setting('sms_baiwu_username', ''),
                    'password' => system_setting('sms_baiwu_password', ''),
                ];
                break;

            case 'huaxin':
                $config['gateways']['huaxin'] = [
                    'user_id'  => system_setting('sms_huaxin_user_id', ''),
                    'password' => system_setting('sms_huaxin_password', ''),
                    'account'  => system_setting('sms_huaxin_account', ''),
                ];
                break;

            case 'chuanglan':
                $config['gateways']['chuanglan'] = [
                    'username' => system_setting('sms_chuanglan_username', ''),
                    'password' => system_setting('sms_chuanglan_password', ''),
                ];
                break;

            case 'sendcloud':
                $config['gateways']['sendcloud'] = [
                    'sms_user' => system_setting('sms_sendcloud_sms_user', ''),
                    'sms_key'  => system_setting('sms_sendcloud_sms_key', ''),
                ];
                break;

            case 'baidu':
                $config['gateways']['baidu'] = [
                    'ak'        => system_setting('sms_baidu_ak', ''),
                    'sk'        => system_setting('sms_baidu_sk', ''),
                    'invoke_id' => system_setting('sms_baidu_invoke_id', ''),
                ];
                break;

            case 'huawei_cloud':
                $config['gateways']['huawei_cloud'] = [
                    'endpoint'   => system_setting('sms_huawei_cloud_endpoint', ''),
                    'app_key'    => system_setting('sms_huawei_cloud_app_key', ''),
                    'app_secret' => system_setting('sms_huawei_cloud_app_secret', ''),
                    'from'       => system_setting('sms_huawei_cloud_from', ''),
                    'callback'   => system_setting('sms_huawei_cloud_callback', ''),
                ];
                break;

            case 'ucloud':
                $config['gateways']['ucloud'] = [
                    'private_key' => system_setting('sms_ucloud_private_key', ''),
                    'public_key'  => system_setting('sms_ucloud_public_key', ''),
                    'sig_content' => system_setting('sms_ucloud_sig_content', ''),
                    'project_id'  => system_setting('sms_ucloud_project_id', ''),
                ];
                break;

            case 'smsbao':
                $config['gateways']['smsbao'] = [
                    'user'     => system_setting('sms_smsbao_user', ''),
                    'password' => system_setting('sms_smsbao_password', ''),
                ];
                break;

            case 'moduyun':
                $config['gateways']['moduyun'] = [
                    'accesskey' => system_setting('sms_moduyun_accesskey', ''),
                    'secretkey' => system_setting('sms_moduyun_secretkey', ''),
                    'signId'    => system_setting('sms_moduyun_sign_id', ''),
                    'type'      => system_setting('sms_moduyun_type', 0),
                ];
                break;

            default:
                throw new Exception("Unsupported SMS gateway: {$gateway}");
        }

        return $config;
    }

    /**
     * Get SMS message template
     *
     * @param  string  $code
     * @param  string  $type
     * @return string
     */
    private function getSmsMessage(string $code, string $type): string
    {
        $locale = front_locale_code();

        // Try to get template from system settings
        $template = $this->getTemplateFromSettings($type, $locale);

        if (! empty($template)) {
            // Replace placeholders with actual code
            // Support both :code (system default) and #code# (yunpian format)
            return str_replace([':code', '#code#'], [$code, $code], $template);
        }

        // Fallback to language file templates
        $messages = [
            'register' => __('common/sms.register_code', ['code' => $code]),
            'login'    => __('common/sms.login_code', ['code' => $code]),
            'reset'    => __('common/sms.reset_code', ['code' => $code]),
        ];

        return $messages[$type] ?? __('common/sms.verification_code', ['code' => $code]);
    }

    /**
     * Get template from system settings
     * Priority: common template > type-specific template
     *
     * @param  string  $type
     * @param  string  $locale
     * @return string
     */
    private function getTemplateFromSettings(string $type, string $locale): string
    {
        // First, try to get common template (shared by all types)
        // This allows using one template for register, login, reset
        $commonKeys = [
            "sms_template.{$locale}",
            'sms_template',
            'sms_template.zh-cn',
        ];

        foreach ($commonKeys as $key) {
            $template = system_setting($key, '');

            if (is_array($template)) {
                $template = $template[$locale] ?? ($template['zh-cn'] ?? '');
            }

            if (is_string($template) && ! empty($template)) {
                return $template;
            }
        }

        // If no common template, try type-specific template
        $typeKeys = [
            "sms_template_{$type}.{$locale}",
            "sms_template_{$type}",
            "sms_template_{$type}.zh-cn",
        ];

        foreach ($typeKeys as $key) {
            $template = system_setting($key, '');

            if (is_array($template)) {
                $template = $template[$locale] ?? ($template['zh-cn'] ?? '');
            }

            if (is_string($template) && ! empty($template)) {
                return $template;
            }
        }

        return '';
    }

    /**
     * Get SMS data format based on gateway
     *
     * @param  string  $gateway
     * @param  string  $message
     * @param  string  $code
     * @param  string  $type
     * @return array
     */
    private function getSmsData(string $gateway, string $message, string $code, string $type): array
    {
        $smsRepo = \InnoShop\Common\Repositories\SmsRepo::getInstance();

        // Gateways that require template format
        if ($smsRepo->requiresTemplate($gateway)) {
            $templateId = $this->getTemplateId($gateway, $type);
            if (empty($templateId)) {
                // Fallback to content if template not configured
                return ['content' => $message];
            }

            // Different gateways use different template data formats
            switch ($gateway) {
                case 'aliyun':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            'code' => $code,
                        ],
                    ];

                case 'tencent':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            $code,
                        ],
                    ];

                case 'huawei':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            $code,
                        ],
                    ];

                case 'qiniu':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            'code' => $code,
                        ],
                    ];

                case 'baidu':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            'code' => $code,
                        ],
                    ];

                case 'ucloud':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            'code' => $code,
                        ],
                    ];

                case 'moduyun':
                    return [
                        'template' => $templateId,
                        'data'     => [
                            $code,
                        ],
                    ];

                default:
                    return ['content' => $message];
            }
        }

        // Gateways that use content format
        // For yunpian, ensure #code# is replaced in the message
        if ($gateway === 'yunpian') {
            // Replace #code# placeholder if still present
            $message = str_replace('#code#', $code, $message);

            // Add signature prefix if configured and message doesn't already have signature
            $sign = system_setting('sms_yunpian_sign', '');
            if (! empty($sign)) {
                // Check if message already contains signature format 【xxx】
                $hasSignature = preg_match('/^【[^】]+】/', $message);
                if (! $hasSignature) {
                    // Format: 【签名】内容
                    $message = '【'.$sign.'】'.$message;
                }
            }
        }

        return ['content' => $message];
    }

    /**
     * Get template ID for gateway and type
     *
     * @param  string  $gateway
     * @param  string  $type
     * @return string
     */
    private function getTemplateId(string $gateway, string $type): string
    {
        $settingKey = "sms_{$gateway}_template_{$type}";
        $templateId = system_setting($settingKey, '');

        // If specific template not set, try to use default template
        if (empty($templateId)) {
            $defaultKey = "sms_{$gateway}_template";
            $templateId = system_setting($defaultKey, '');
        }

        return $templateId;
    }

    /**
     * Extract detailed error message from exception
     *
     * @param  \Exception  $exception
     * @param  string  $gateway
     * @return string
     */
    private function extractDetailedError(\Exception $exception, string $gateway): string
    {
        $errorMessage = $exception->getMessage();

        // Try to extract error details from exception
        // Use reflection to safely check for methods
        try {
            $reflection = new \ReflectionClass($exception);

            // Try to get raw response data
            if ($reflection->hasMethod('getRaw')) {
                $raw = $reflection->getMethod('getRaw')->invoke($exception);
                if (is_array($raw)) {
                    $details = $this->extractErrorDetails($raw);
                    if (! empty($details)) {
                        return implode('; ', $details);
                    }
                }
            }

            // Try to get HTTP response
            if ($reflection->hasMethod('getResponse')) {
                /** @var \Psr\Http\Message\ResponseInterface|null $response */
                $response = $reflection->getMethod('getResponse')->invoke($exception);
                if ($response && method_exists($response, 'getBody')) {
                    $body     = $response->getBody()->getContents();
                    $bodyData = json_decode($body, true);
                    if (is_array($bodyData)) {
                        $details = $this->extractErrorDetails($bodyData);
                        if (! empty($details)) {
                            return implode('; ', $details);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // If reflection fails, just return the original message
        }

        return $errorMessage;
    }

    /**
     * Extract error details from response array
     *
     * @param  array  $data
     * @return array
     */
    private function extractErrorDetails(array $data): array
    {
        $details = [];

        if (isset($data['code'])) {
            $details[] = "错误代码: {$data['code']}";
        }
        if (isset($data['msg'])) {
            $details[] = $data['msg'];
        }
        if (isset($data['message'])) {
            $details[] = $data['message'];
        }
        if (isset($data['detail'])) {
            $details[] = "详情: {$data['detail']}";
        }
        if (isset($data['error'])) {
            $details[] = is_string($data['error']) ? $data['error'] : json_encode($data['error']);
        }

        return $details;
    }

    /**
     * Format phone number according to gateway requirements
     *
     * @param  string  $gateway
     * @param  string  $callingCode
     * @param  string  $telephone
     * @return string
     */
    private function formatPhoneNumber(string $gateway, string $callingCode, string $telephone): string
    {
        // For Yunpian (云片), China mobile numbers (86) should be 11 digits without country code
        if ($gateway === 'yunpian' && $callingCode === '86') {
            // Return only the 11-digit phone number for China
            return $telephone;
        }

        // For other gateways or international numbers, combine calling code and telephone
        return $callingCode.$telephone;
    }
}
