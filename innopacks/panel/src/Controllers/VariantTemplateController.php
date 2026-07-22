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
use Illuminate\Http\Response;
use InnoShop\Common\Models\Product\VariantTemplate;
use InnoShop\Common\Repositories\Product\VariantTemplateRepo;
use InnoShop\Panel\Requests\VariantTemplateRequest;

class VariantTemplateController extends BaseController
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $templates = VariantTemplateRepo::getInstance()
            ->all()
            ->map(fn (VariantTemplate $template) => [
                'id'   => $template->id,
                'name' => $template->name,
            ]);

        return response()->json($templates);
    }

    /**
     * @param  VariantTemplate  $variantTemplate
     * @return JsonResponse
     */
    public function show(VariantTemplate $variantTemplate): JsonResponse
    {
        return response()->json([
            'id'         => $variantTemplate->id,
            'name'       => $variantTemplate->name,
            'variables'  => $variantTemplate->variables,
            'sku_matrix' => $variantTemplate->sku_matrix,
        ]);
    }

    /**
     * @param  VariantTemplateRequest  $request
     * @return JsonResponse
     */
    public function store(VariantTemplateRequest $request): JsonResponse
    {
        $template = VariantTemplateRepo::getInstance()->create($request->validated());

        return response()->json([
            'id'      => $template->id,
            'name'    => $template->name,
            'message' => common_trans('base.saved_success'),
        ], Response::HTTP_CREATED);
    }

    /**
     * @param  VariantTemplate  $variantTemplate
     * @return JsonResponse
     */
    public function destroy(VariantTemplate $variantTemplate): JsonResponse
    {
        VariantTemplateRepo::getInstance()->destroy($variantTemplate);

        return response()->json([
            'message' => common_trans('base.deleted_success'),
        ]);
    }
}
