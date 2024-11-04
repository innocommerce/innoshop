<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\RedirectResponse;
use InnoShop\Common\Repositories\CustomerRepo;
use InnoShop\Front\Requests\PasswordRequest;

class PasswordController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [];

        return inno_view('account/password', $data);
    }

    /**
     * Request to change password.
     *
     * @param  PasswordRequest  $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(PasswordRequest $request): RedirectResponse
    {
        try {
            CustomerRepo::getInstance()->updatePassword(current_customer(), $request->all());

            return redirect(account_route('password.index'))
                ->with('success', front_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(account_route('password.index'))
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
