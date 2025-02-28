<?php

namespace Plugin\ProductLinkImg\Controllers;

use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;

class ProductLinkImgController extends BaseController
{
    public function index(Request $request)
    {
        return view('ProductLinkImg::panel.link_img', []);
    }
}
