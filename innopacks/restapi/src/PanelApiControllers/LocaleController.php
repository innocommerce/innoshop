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
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Locales')]
class LocaleController extends BaseController
{
    #[Endpoint('List locales')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): mixed
    {
        try {
            $filters = $request->all();
            $perPage = $request->get('per_page', 15);
            $locales = LocaleRepo::getInstance()->builder($filters)->paginate($perPage);

            return read_json_success($locales);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get locale detail')]
    #[UrlParam('id', 'integer', description: 'Locale ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $locale = LocaleRepo::getInstance()->builder()->findOrFail($id);

            return read_json_success($locale);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Create locale')]
    public function store(Request $request): mixed
    {
        try {
            $locale = LocaleRepo::getInstance()->create($request->all());

            return create_json_success($locale);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Update locale')]
    #[UrlParam('id', 'integer', description: 'Locale ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $locale = LocaleRepo::getInstance()->builder()->findOrFail($id);
            LocaleRepo::getInstance()->update($locale, $request->all());

            return update_json_success($locale->fresh());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Delete locale')]
    #[UrlParam('id', 'integer', description: 'Locale ID', example: 1)]
    public function destroy(int $id): mixed
    {
        try {
            $locale = LocaleRepo::getInstance()->builder()->findOrFail($id);
            LocaleRepo::getInstance()->destroy($locale);

            return json_success('Locale deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
