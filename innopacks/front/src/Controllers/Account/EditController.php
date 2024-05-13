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
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CustomerRepo;

class EditController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return view('account.edit');
    }

    public function update(Request $request)
    {
        try {
            $data     = $request->only(['avatar', 'name', 'email']);
            $customer = current_customer();
            CustomerRepo::getInstance()->update($customer, $data);

            return redirect(account_route('edit.index'))
                ->with('success', trans('front::common.updated_success'));

        } catch (\Exception $e) {
            return redirect(account_route('edit.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
