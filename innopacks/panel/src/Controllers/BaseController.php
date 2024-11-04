<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected string $modelClass;

    public function __construct()
    {
        if (empty($this->modelClass)) {
            $this->modelClass = $this->getModelByController();
        }
    }

    /**
     * @return string
     */
    private function getModelByController(): string
    {
        $class     = class_basename($this);
        $modelName = str_replace('Controller', '', $class);

        return "InnoShop\\Common\\Models\\$modelName";
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function checkModel(): void
    {
        if (empty($this->modelClass)) {
            throw new \Exception('Please define model class first!');
        }
        if (! class_exists($this->modelClass)) {
            throw new \Exception("Class $this->modelClass doesn't exit!");
        }
    }

    /**
     * @throws \Exception
     */
    public function active(Request $request, int $id): JsonResponse
    {
        try {
            $this->checkModel();
            $item = $this->modelClass::query()->findOrFail($id);

            $item->active = $request->get('status');
            $item->saveOrFail();

            return json_success(panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
