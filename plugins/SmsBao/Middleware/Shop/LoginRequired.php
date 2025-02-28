<?php

namespace Plugin\SmsBao\Middleware\Shop;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Plugin\SmsBao\Models\CustomerLoginToken;

class LoginRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->isMethod('get')) {
            $path = $request->path();
            if (substr($path, 0, 5) != admin_name()) {
                $loginTokenKey        = 'login_token_key';
                $sessionLoginTokenKey = $loginTokenKey.'_'.session()->getId();
                $isLogin              = Cache::get($sessionLoginTokenKey);
                if (empty($isLogin)) {//没有缓存到登录，则需要检测是否可以登录(减少下面的逻辑执行)
                    $loginToken = (isset($_COOKIE[$loginTokenKey]) && ! empty($_COOKIE[$loginTokenKey])) ? $_COOKIE[$loginTokenKey] : null;
                    if (! empty($loginToken)) {//
                        $customer = current_customer();
                        if (empty($customer)) {//没有登录，要检测是否进行登录
                            $loginToken = explode('.', $loginToken);
                            if (count($loginToken) == 2) {
                                $loginTokenData = CustomerLoginToken::query()->where('index_id', $loginToken[0])->first();
                                //print_r($loginTokenData);exit;
                                if ($loginTokenData && $loginTokenData->token == $loginToken[1] && strtotime($loginTokenData->expire_time) > time()) {//自动登录
                                    auth(Customer::AUTH_GUARD)->loginUsingId($loginTokenData->user_id); //自动登录

                                    $plugin = plugin('sms_bao');
                                    $day    = 7;
                                    if (isset($plugin['remember_login_days']) && ! empty($plugin['remember_login_days']) && is_numeric($plugin['remember_login_days'])) {
                                        $day = $plugin['remember_login_days'];
                                    }
                                    $expire     = 60 * 60 * 24 * $day; //7天
                                    $expireTime = time() + $expire;
                                    Cache::put($sessionLoginTokenKey, 1, $expireTime);
                                } else {//无效的token要删除
                                    setcookie($loginTokenKey, '', 1, '/');
                                    Cache::delete($sessionLoginTokenKey);
                                }
                            } else {
                                setcookie($loginTokenKey, '', 1, '/');
                                Cache::delete($sessionLoginTokenKey);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
