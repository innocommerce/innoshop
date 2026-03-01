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
use Illuminate\Http\Request;
use InnoShop\Common\Models\Visit\Visit;
use InnoShop\Common\Repositories\VisitRepo;

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
}
