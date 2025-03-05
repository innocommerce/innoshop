<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewImporter\Controllers;

use Exception;
use InnoShop\Common\Models\Review;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\ReviewImporter\Repositories\ReviewRepo;
use Plugin\ReviewImporter\Requests\ReviewRequest;
use Rap2hpoutre\FastExcel\FastExcel;

class ReviewImportController extends BaseController
{
    /**
     * @return mixed
     */
    public function template(): mixed
    {
        try {
            $reviews = Review::all();

            return (new FastExcel($reviews))->download('file.xlsx');
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param  ReviewRequest  $request
     * @return mixed
     */
    public function import(ReviewRequest $request): mixed
    {
        try {
            $collection = (new FastExcel)->import($request->file('reviews'));
            ReviewRepo::getInstance()->import($collection->toArray());

            return redirect(panel_route('reviews.index'));
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
