<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InnoShop\Common\Models\Admin;
use InnoShop\Panel\Requests\LoginRequest;
use InnoShop\Panel\Services\CliLoginService;

class CliLoginController extends BaseController
{
    /**
     * Show the CLI login page. If already authenticated, redirect to callback with token.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $callback = $request->query('callback', '');

        // Validate callback URL: must be a local address for security
        if ($callback && ! CliLoginService::getInstance()->isValidCallbackUrl($callback)) {
            abort(400, 'Invalid callback URL. Only local addresses are allowed.');
        }

        // If already logged in as admin, generate token and redirect directly
        if (auth('admin')->check()) {
            return $this->redirectWithToken(auth('admin')->user(), $callback);
        }

        // Store callback in session for use after login
        if ($callback) {
            session(['cli_login_callback' => $callback]);
        }

        return inno_view('panel::cli_login');
    }

    /**
     * Handle CLI login POST: authenticate and redirect to callback with token.
     *
     * @param  LoginRequest  $request
     * @return mixed
     */
    public function store(LoginRequest $request): mixed
    {
        $callback = session('cli_login_callback', '');
        if ($callback && ! CliLoginService::getInstance()->isValidCallbackUrl($callback)) {
            session()->forget('cli_login_callback');
            abort(400, 'Invalid callback URL. Only local addresses are allowed.');
        }

        if (! Auth::guard('admin')->attempt($request->validated())) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $admin = Auth::guard('admin')->user();
        if (! $admin instanceof Admin) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        session()->forget('cli_login_callback');

        return $this->redirectWithToken($admin, $callback);
    }

    /**
     * Generate a Sanctum token and redirect to the callback URL.
     *
     * @param  Admin  $admin
     * @param  string  $callback
     * @return mixed
     */
    private function redirectWithToken(Admin $admin, string $callback): mixed
    {
        if ($callback && ! CliLoginService::getInstance()->isValidCallbackUrl($callback)) {
            abort(400, 'Invalid callback URL. Only local addresses are allowed.');
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        if (! $callback) {
            return response()->view('panel::cli_login_success', [
                'token' => $token,
            ]);
        }

        $separator = str_contains($callback, '?') ? '&' : '?';

        return redirect()->away($callback.$separator.'token='.urlencode($token));
    }
}
