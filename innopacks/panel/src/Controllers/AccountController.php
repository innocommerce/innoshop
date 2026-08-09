<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\AdminRepo;

class AccountController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'admin' => current_admin(),
            'token' => session('panel_api_token'),
        ];

        return inno_view('panel::account.index', $data);
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            $admin = current_admin();
            AdminRepo::getInstance()->update($admin, $request->only('name', 'email', 'password'));

            return redirect(panel_route('account.index'))
                ->with('instance', $admin)
                ->with('success', common_trans('base.updated_success'));

        } catch (\Exception $e) {
            return redirect(panel_route('account.index'))
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Invalidate the current admin API token and issue a new one.
     * Forces all external clients (MCP, integrations) using the old token to re-authenticate.
     *
     * @return JsonResponse
     */
    public function regenerateToken(): JsonResponse
    {
        try {
            $admin = current_admin();
            $admin->tokens()->where('name', 'admin-token')->delete();

            $newToken = $admin->createToken('admin-token')->plainTextToken;
            session(['panel_api_token' => $newToken]);

            return json_success(trans('panel/account.regenerated'), ['token' => $newToken]);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
