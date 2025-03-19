<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ViewTracker\Controllers\Panel;

use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\ViewTracker\Models\ViewLog;
use Plugin\ViewTracker\Repositories\ViewLogRepo;

class ViewLogController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $data = [
            'criteria' => ViewLogRepo::getCriteria(),
            'items'    => ViewLogRepo::getInstance()->list($request->all()),
        ];

        return view('ViewTracker::panel.index', $data);
    }

    /**
     * @param  ViewLog  $viewLog
     * @return mixed
     */
    public function edit(ViewLog $viewLog): mixed
    {
        $data = [
            'item' => $viewLog,
        ];

        return view('ViewTracker::panel.form', $data);
    }

    /**
     * @param  ViewLog  $viewLog
     * @return mixed
     */
    public function destroy(ViewLog $viewLog): mixed
    {
        $viewLog->delete();

        return redirect(panel_route('partner_links.index'));
    }
}
