<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductExporter\Controllers;

use Illuminate\Http\Request;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\ProductExporter\Repositories\ExportRepo;
use Plugin\ProductExporter\Repositories\ImportRepo;
use Rap2hpoutre\FastExcel\FastExcel;

class ProductImportController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $data = [
            'criteria' => ProductRepo::getCriteria(),
        ];

        return view('ProductExporter::index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function export(Request $request): mixed
    {
        try {
            $builder = ProductRepo::getInstance()->builder(['keyword' => $request->get('name')]);
            if ($quantity = $request->get('quantity')) {
                $builder->limit($quantity);
            }

            $sheets   = ExportRepo::getInstance()->getExportData($builder->get());
            $nameName = 'products-'.date('Y-m-d-H-i-s').'.xlsx';

            return (new FastExcel($sheets))->download($nameName);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function import(Request $request): mixed
    {
        try {
            $clearData = $request->get('clear-data', false);
            $excelFile = $request->file('product_excel_file');
            $excelData = (new FastExcel)->importSheets($excelFile);

            ImportRepo::getInstance($clearData)->importSheets($excelData);

            return redirect(panel_route('exporter.index'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
