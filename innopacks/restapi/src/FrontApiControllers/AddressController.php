<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Address;
use InnoShop\Common\Repositories\AddressRepo;
use InnoShop\Common\Resources\AddressListItem;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Front - Addresses')]
#[Authenticated]
class AddressController extends BaseController
{
    /**
     * @return mixed
     */
    #[Endpoint('List addresses')]
    public function index(): mixed
    {
        $filters = [
            'customer_id' => token_customer_id(),
        ];

        $items     = AddressRepo::getInstance()->builder($filters)->get();
        $addresses = AddressListItem::collection($items)->jsonSerialize();

        return read_json_success($addresses);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create address')]
    #[BodyParam('name', type: 'string')]
    #[BodyParam('phone', type: 'string')]
    #[BodyParam('zipcode', type: 'string')]
    #[BodyParam('address_1', type: 'string')]
    #[BodyParam('address_2', type: 'string', required: false)]
    #[BodyParam('city', type: 'string')]
    #[BodyParam('state_id', type: 'integer', required: false)]
    #[BodyParam('country_id', type: 'integer')]
    public function store(Request $request): mixed
    {
        $data = $request->all();

        $data['customer_id'] = token_customer_id();

        $address = AddressRepo::getInstance()->create($data);
        $result  = new AddressListItem($address);

        return create_json_success($result);
    }

    /**
     * @param  Address  $address
     * @return mixed
     */
    #[Endpoint('Get address detail')]
    #[UrlParam('address', type: 'integer', description: 'Address ID')]
    public function show(Address $address): mixed
    {
        $customerID = token_customer_id();
        if ($customerID != $address->customer_id) {
            return json_fail('Unauthorized', null, 403);
        }

        $result = new AddressListItem($address);

        return read_json_success($result);
    }

    /**
     * @param  Request  $request
     * @param  Address  $address
     * @return mixed
     */
    #[Endpoint('Update address')]
    #[UrlParam('address', type: 'integer', description: 'Address ID')]
    public function update(Request $request, Address $address): mixed
    {
        $customerID = token_customer_id();
        if ($customerID != $address->customer_id) {
            return json_fail('Unauthorized', null, 403);
        }

        $data = $request->all();

        $data['customer_id'] = $customerID;

        $address = AddressRepo::getInstance()->update($address, $data);
        $result  = new AddressListItem($address);

        return update_json_success($result);
    }

    /**
     * Partial update an address.
     * PATCH /api/front/addresses/{address}
     *
     * @param  Request  $request
     * @param  Address  $address
     * @return mixed
     */
    #[Endpoint('Partial update address')]
    #[UrlParam('address', type: 'integer', description: 'Address ID')]
    public function patch(Request $request, Address $address): mixed
    {
        $customerID = token_customer_id();
        if ($customerID != $address->customer_id) {
            return json_fail('Unauthorized', null, 403);
        }

        $data = $request->all();

        $address = AddressRepo::getInstance()->patch($address, $data);
        $result  = new AddressListItem($address);

        return update_json_success($result);
    }

    /**
     * @param  Address  $address
     * @return mixed
     */
    #[Endpoint('Delete address')]
    #[UrlParam('address', type: 'integer', description: 'Address ID')]
    public function destroy(Address $address): mixed
    {
        $customerID = token_customer_id();
        if ($customerID != $address->customer_id) {
            return json_fail('Unauthorized', null, 403);
        }

        AddressRepo::getInstance()->destroy($address);

        return delete_json_success('success');
    }
}
