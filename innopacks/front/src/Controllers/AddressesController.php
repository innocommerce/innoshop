<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Address;
use InnoShop\Common\Repositories\AddressRepo;
use InnoShop\Common\Resources\AddressListItem;

class AddressesController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Throwable
     */
    public function store(Request $request): mixed
    {
        $data                = $request->all();
        $data['customer_id'] = current_customer_id();
        $data['guest_id']    = current_guest_id();

        $address = AddressRepo::getInstance()->create($data);
        $result  = new AddressListItem($address);

        return create_json_success($result);
    }

    /**
     * @param  Request  $request
     * @param  Address  $address
     * @return mixed
     */
    public function update(Request $request, Address $address): mixed
    {
        $data                = $request->all();
        $data['customer_id'] = current_customer_id();
        $data['guest_id']    = current_guest_id();

        $address = AddressRepo::getInstance()->update($address, $data);
        $result  = new AddressListItem($address);

        return update_json_success($result);
    }

    /**
     * Delete address
     *
     * @param  Address  $address
     * @return mixed
     */
    public function destroy(Address $address): mixed
    {
        $address->delete();

        return delete_json_success();
    }
}
