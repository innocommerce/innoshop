<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\TaxClassRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Tax Classes')]
class TaxClassController extends BaseController
{
    #[Endpoint('List tax classes')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): mixed
    {
        try {
            $filters    = $request->all();
            $perPage    = $request->get('per_page', 15);
            $taxClasses = TaxClassRepo::getInstance()->builder($filters)->paginate($perPage);

            return read_json_success($taxClasses);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get tax class detail')]
    #[UrlParam('id', 'integer', description: 'Tax Class ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $taxClass = TaxClassRepo::getInstance()->builder()->findOrFail($id);

            return read_json_success($taxClass);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Create tax class')]
    public function store(Request $request): mixed
    {
        try {
            $taxClass = TaxClassRepo::getInstance()->create($request->all());

            return create_json_success($taxClass);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Update tax class')]
    #[UrlParam('id', 'integer', description: 'Tax Class ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $taxClass = TaxClassRepo::getInstance()->builder()->findOrFail($id);
            TaxClassRepo::getInstance()->update($taxClass, $request->all());

            return update_json_success($taxClass->fresh());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Delete tax class')]
    #[UrlParam('id', 'integer', description: 'Tax Class ID', example: 1)]
    public function destroy(int $id): mixed
    {
        try {
            $taxClass = TaxClassRepo::getInstance()->builder()->findOrFail($id);
            TaxClassRepo::getInstance()->destroy($taxClass);

            return json_success('Tax class deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
