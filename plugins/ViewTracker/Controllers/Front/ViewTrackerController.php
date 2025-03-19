<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ViewTracker\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Plugin\ViewTracker\Repositories\ViewLogRepo;
use Throwable;

class ViewTrackerController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function virtualImage(Request $request): mixed
    {
        try {
            $imagePath   = $request->image;
            $requestData = $request->all();
            $parsedData  = ViewLogRepo::parseRequest($request);
            $allData     = array_merge($requestData, $parsedData);

            ViewLogRepo::getInstance()->create($allData);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        $path = storage_path('access/').$imagePath;
        if (! file_exists($path)) {
            return '';
        }

        return response()->file($path);
    }
}
