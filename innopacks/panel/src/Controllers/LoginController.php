<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use InnoShop\Panel\Requests\LoginRequest;

class LoginController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        if (auth('admin')->check()) {
            return redirect()->back();
        }

        return view('panel::login');
    }

    /**
     * Login post request
     *
     * @param  LoginRequest  $request
     * @return mixed
     */
    public function store(LoginRequest $request): mixed
    {
        if (auth('admin')->attempt($request->validated())) {
            return redirect(panel_route('home.index'));
        }

        return redirect()->back()->with(['error' => trans('auth.failed')])->withInput();
    }
}
