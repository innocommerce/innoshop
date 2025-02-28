<?php

namespace Plugin\ProductLinkImg;

class Boot
{
    public function init(): void
    {

        /**
         * 商品中增加图片链接功能
         *
         *
         * listen_hook_filter('panel.product.edit.response', function (Response $response) {
         *
         *
         * if (is_string($response->getOriginalContent())) {
         * return $response;
         * }
         * $view  = view('ProductLinkImg::panel.add_link_img_button', [])->render();
         * return Dom::getInstance($response->getContent())->insertAfter('.img-upload-trigger', $view);
         * });
         */
        listen_blade_insert('panel.product.edit.img_upload.after', function ($data) {
            $view = view('ProductLinkImg::panel.add_link_img_button', []);

            return $view;
        });
    }
}
