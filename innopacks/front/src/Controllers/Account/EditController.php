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
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CustomerRepo;

class EditController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return inno_view('account.edit');
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function update(Request $request): mixed
    {
        try {
            $data     = $request->only(['avatar', 'name', 'email']);
            $customer = current_customer();
            CustomerRepo::getInstance()->update($customer, $data);

            return redirect(account_route('edit.index'))
                ->with('instance', $customer)
                ->with('success', front_trans('common.updated_success'));

        } catch (Exception $e) {
            return redirect(account_route('edit.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
