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
use InnoShop\Common\Repositories\TaxRateRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Tax Rates')]
class TaxRateController extends BaseController
{
    #[Endpoint('List tax rates')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): mixed
    {
        try {
            $filters  = $request->all();
            $perPage  = $request->get('per_page', 15);
            $taxRates = TaxRateRepo::getInstance()->builder($filters)->paginate($perPage);

            return read_json_success($taxRates);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get tax rate detail')]
    #[UrlParam('id', 'integer', description: 'Tax Rate ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $taxRate = TaxRateRepo::getInstance()->builder()->findOrFail($id);

            return read_json_success($taxRate);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Create tax rate')]
    public function store(Request $request): mixed
    {
        try {
            $taxRate = TaxRateRepo::getInstance()->create($request->all());

            return create_json_success($taxRate);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Update tax rate')]
    #[UrlParam('id', 'integer', description: 'Tax Rate ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $taxRate = TaxRateRepo::getInstance()->builder()->findOrFail($id);
            TaxRateRepo::getInstance()->update($taxRate, $request->all());

            return update_json_success($taxRate->fresh());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Delete tax rate')]
    #[UrlParam('id', 'integer', description: 'Tax Rate ID', example: 1)]
    public function destroy(int $id): mixed
    {
        try {
            $taxRate = TaxRateRepo::getInstance()->builder()->findOrFail($id);
            TaxRateRepo::getInstance()->destroy($taxRate);

            return json_success('Tax rate deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
