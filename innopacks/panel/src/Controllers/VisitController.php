<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Repositories\VisitRepo;
use InnoShop\Common\Services\VisitEnrichService;

class VisitController extends BaseController
{
    /**
     * Visit list page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $repo    = new VisitRepo;

        $visits = $repo->list($filters);

        $data = [
            'searchFields'  => VisitRepo::getSearchFieldOptions(),
            'filterButtons' => VisitRepo::getFilterButtonOptions(),
            'visits'        => $visits,
        ];

        return inno_view('panel::visits.index', $data);
    }

    /**
     * Visit detail page
     *
     * @param  Visit  $visit
     * @return mixed
     * @throws Exception
     */
    public function show(Visit $visit): mixed
    {
        $visit->load(['customer', 'visitEvents']);

        $data = [
            'visit' => $visit,
        ];

        return inno_view('panel::visits.show', $data);
    }

    /**
     * Locate and update a visit record's geo data.
     */
    public function locate(Visit $visit): JsonResponse
    {
        if (! $visit->ip_address) {
            return response()->json(['error' => 'No IP address'], 400);
        }

        try {
            $result = app(VisitEnrichService::class)->locate($visit);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($result);
    }

    /**
     * Parse user_agent and update browser/os for a visit record.
     */
    public function parseUA(Visit $visit): JsonResponse
    {
        if (! $visit->user_agent) {
            return response()->json(['error' => 'No user agent'], 400);
        }

        $result = app(VisitEnrichService::class)->parseUA($visit);

        return response()->json($result);
    }

    /**
     * Batch locate and parse UA for all visits with missing data.
     */
    public function batchLocate(): JsonResponse
    {
        try {
            $result = app(VisitEnrichService::class)->batchLocate();
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($result);
    }
}
