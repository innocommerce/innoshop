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
use InnoShop\Panel\Requests\LoginRequest;

class LoginController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request): mixed
    {
        if (auth('admin')->check()) {
            return redirect()->back();
        }

        if ($request->has('locale')) {
            session(['panel_locale' => $request->get('locale')]);

            return redirect(panel_route('login.index'));
        }

        return inno_view('panel::login');
    }

    /**
     * Login post request
     *
     * @param  LoginRequest  $request
     * @return mixed
     * @throws \Exception
     */
    public function store(LoginRequest $request): mixed
    {
        $redirectUri = session('panel_redirect_uri');
        if (auth('admin')->attempt($request->validated())) {
            if ($redirectUri) {
                session()->forget('panel_redirect_uri');

                return redirect()->to($redirectUri);
            }

            return redirect(panel_route('home.index'));
        }

        return redirect()->back()
            ->with('instance', auth('admin')->user())
            ->with(['error' => trans('auth.failed')])->withInput();
    }
}
