<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use InnoShop\Common\Components\Base;
use InnoShop\Common\Components\Forms;
use InnoShop\Common\Console\Commands;
use InnoShop\Common\Services\StorageService;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Register common view namespace early so Blade components can resolve
     * `common::...` even if boot() is skipped or reordered (e.g. Octane edge cases).
     */
    public function register(): void
    {
        $this->loadViewsFrom($this->basePath.'resources/views', 'common');
        $this->app->singleton(StorageService::class);
    }

    /**
     * Boot front service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        load_settings();
        $this->loadAiConfig();
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();
        $this->loadMailSettings();
        $this->loadViewComponents();
        $this->loadViewTemplates();
    }

    /**
     * Load AI config from system_setting into config('ai.*')
     */
    private function loadAiConfig(): void
    {
        if (! installed()) {
            return;
        }

        $defaultProvider = system_setting('ai_model', 'glm');
        config(['ai.default' => $defaultProvider]);

        $providers = [
            'openai', 'anthropic', 'deepseek', 'kimi',
            'doubao', 'qianwen', 'hunyuan', 'glm', 'minimax',
        ];

        // Map our provider names to Laravel AI SDK driver names
        $driverMap = [
            'anthropic' => 'anthropic',
            'kimi'      => 'openai',
            'doubao'    => 'openai',
            'qianwen'   => 'openai',
            'hunyuan'   => 'openai',
        ];

        $baseUrlMap = [
            'openai'   => 'https://api.openai.com/v1',
            'deepseek' => 'https://api.deepseek.com/v1',
            'kimi'     => 'https://api.moonshot.cn/v1',
            'doubao'   => 'https://ark.cn-beijing.volces.com/api/v3',
            'qianwen'  => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
            'hunyuan'  => 'https://api.hunyuan.cloud.tencent.com/v1',
            'glm'      => 'https://open.bigmodel.cn/api/paas/v4',
            'minimax'  => 'https://api.minimax.chat/v1',
        ];

        $defaultModelMap = [
            'openai'   => 'gpt-4o',
            'deepseek' => 'deepseek-chat',
            'kimi'     => 'moonshot-v1-8k',
            'doubao'   => 'doubao-lite-4k',
            'qianwen'  => 'qwen-turbo',
            'hunyuan'  => 'hunyuan-standard',
            'glm'      => 'glm-5',
            'minimax'  => 'MiniMax-Text-01',
        ];

        foreach ($providers as $name) {
            $apiKey = system_setting("{$name}_api_key");
            if (empty($apiKey)) {
                continue;
            }

            $driver  = $driverMap[$name] ?? $name;
            $baseUrl = system_setting("{$name}_base_url") ?: ($baseUrlMap[$name] ?? null);
            $model   = system_setting("{$name}_model") ?: ($defaultModelMap[$name] ?? null);

            $config = [
                'driver' => $driver,
                'key'    => $apiKey,
                'model'  => $model,
                'models' => [
                    'text' => [
                        'default' => $model,
                    ],
                ],
            ];

            if ($baseUrl) {
                $config['url'] = $baseUrl;
            }

            config(["ai.providers.{$name}" => $config]);
        }
    }

    /**
     * Register config.
     *
     * @return void
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom($this->basePath.'config/innoshop.php', 'innoshop');
        if (installed()) {
            Config::set('app.debug', system_setting('debug', false));
        }
    }

    /**
     * Register migrations.
     *
     * @return void
     */
    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->basePath.'database/migrations');
    }

    /**
     * Register common commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\UpdateCountries::class,
                Commands\UpdateStates::class,
                Commands\PublishFrontTheme::class,
                Commands\MigrateProductImages::class,
                Commands\NormalizeLocales::class,
                Commands\MigrateImagePaths::class,
                Commands\OrderComplete::class,
                Commands\AggregateVisitStatistics::class,
            ]);
        }
    }

    /**
     * Load the email configuration, fetch values from the backend mail,
     * and override them in config/mail and config/services.
     * @return void
     */
    private function loadMailSettings(): void
    {
        $mailEngine = strtolower(system_setting('email_engine'));

        if (empty($mailEngine)) {
            return;
        }

        $storeMailAddress = system_setting('email', '');

        Config::set('mail.default', $mailEngine);
        Config::set('mail.from.address', $storeMailAddress);
        Config::set('mail.from.name', config('app.name'));

        if ($mailEngine == 'smtp') {
            Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => system_setting('smtp_host'),
                'port'       => (int) system_setting('smtp_port', 587),
                'encryption' => strtolower(system_setting('smtp_encryption')),
                'username'   => system_setting('smtp_username'),
                'password'   => system_setting('smtp_password'),
                'timeout'    => (int) system_setting('smtp_timeout', 60),
            ]);
        }
    }

    /**
     * Load view components.
     *
     * @return void
     */
    protected function loadViewComponents(): void
    {
        // Base components
        $this->loadViewComponentsAs('common', [
            'alert'         => Base\Alert::class,
            'no-data'       => Base\NoData::class,
            'delete-button' => Base\DeleteButton::class,
        ]);

        // Form components
        $this->loadViewComponentsAs('common-form', [
            'locale-input' => Forms\LocaleInput::class,
            'locale-modal' => Forms\LocaleModal::class,
            'input'        => Forms\Input::class,
            'select'       => Forms\Select::class,
            'textarea'     => Forms\Textarea::class,
            'rich-text'    => Forms\RichText::class,
            'image'        => Forms\Image::class,
            'imagep'       => Forms\ImagePure::class,
            'images'       => Forms\Images::class,
            'imagesp'      => Forms\ImagesPure::class,
            'file'         => Forms\File::class,
            'date'         => Forms\Date::class,
            'switch-radio' => Forms\SwitchRadio::class,
            'model-switch' => Forms\ModelSwitch::class,
        ]);
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = inno_path('common/resources/views');
        $customViewPath = resource_path('views/vendor/common');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');
    }
}
