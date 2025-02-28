<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\Services;

use Exception;
use Illuminate\Support\Str;
use InnoShop\Common\Libraries\Link;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Resources\BrandSimple;
use InnoShop\Common\Resources\ProductSimple;

class DesignService
{
    /**
     * @return self
     */
    public static function getInstance(): DesignService
    {
        return new self;
    }

    /**
     * @param  $modulesData
     * @return array
     */
    public function handleRequestModules($modulesData): array
    {
        $modulesData = $modulesData['modules'];
        if (empty($modulesData)) {
            return [];
        }

        foreach ($modulesData as $index => $moduleData) {
            $moduleId = $moduleData['module_id'] ?? '';
            if (empty($moduleId)) {
                $moduleData['module_id'] = Str::random(16);
            }

            $viewPath = $moduleData['view_path'] ?? '';
            if ($viewPath == 'design.') {
                $moduleData['view_path'] = '';
            }

            $modulesData[$index] = $moduleData;
        }

        return ['modules' => $modulesData];
    }

    /**
     * @throws Exception
     */
    public function handleModuleContent($moduleCode, $content)
    {
        $productCodes = ['product', 'category', 'latest'];

        $content['module_code'] = $moduleCode;
        if ($moduleCode == 'slideshow') {
            return $this->handleSlideShow($content);
        } elseif (in_array($moduleCode, ['image401', 'image402', 'image100', 'image200', 'image300', 'image301'])) {
            return $this->handleImage401($content);
        } elseif ($moduleCode == 'brand') {
            return $this->handleBrand($content);
        } elseif ($moduleCode == 'tab_product') {
            return $this->handleTabProducts($content);
        } elseif (in_array($moduleCode, $productCodes)) {
            return $this->handleProducts($content);
        } elseif ($moduleCode == 'icons') {
            return $this->handleIcons($content);
        } elseif ($moduleCode == 'rich_text') {
            return $this->handleRichText($content);
        } elseif ($moduleCode == 'page') {
            return $this->handlePage($content);
        } elseif ($moduleCode == 'article') {
            return $this->handleArticle($content);
        }

        return fire_hook_filter('service.design.module.content', $content);
    }

    /**
     * Handle slideshow module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleSlideShow($content): array
    {
        $images = $content['images'];
        if (empty($images)) {
            return $content;
        }

        $content['images'] = $this->handleImages($images);

        return $content;
    }

    /**
     * Handle brand module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleBrand($content): array
    {
        $brandIds   = $content['brands'] ?? [];
        $brandItems = BrandRepo::getInstance()->getListByBrandIDs($brandIds);
        $brands     = BrandSimple::collection($brandItems)->jsonSerialize();

        $content['brands'] = $brands;
        $content['title']  = $content['title'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle image four in line module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleImage401($content): array
    {
        $images = $content['images'];
        if (empty($images)) {
            return $content;
        }

        $content['images'] = $this->handleImages($images);
        $content['full']   = $content['full'] ?? false;

        return $content;
    }

    /**
     * Handle icons module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleIcons($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';

        if (empty($content['images'])) {
            return $content;
        }

        $images = [];
        foreach ($content['images'] as $image) {
            $images[] = [
                'image'    => image_origin($image['image'] ?? ''),
                'text'     => $image['text'][locale_code()]     ?? '',
                'sub_text' => $image['sub_text'][locale_code()] ?? '',
                'link'     => $image['link'],
                'url'      => $this->handleLink($image['link']['type'] ?? '', $image['link']['value'] ?? ''),
            ];
        }

        $content['images'] = $images;

        return $content;
    }

    /**
     * Handle rich text module
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleRichText($content): array
    {
        $content['data'] = $content['text'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle tab products
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleTabProducts($content): array
    {
        $tabs = $content['tabs'] ?? [];
        if (empty($tabs)) {
            return [];
        }

        foreach ($tabs as $index => $tab) {
            $tabs[$index]['title'] = $tab['title'][locale_code()] ?? '';
            $productsIds           = $tab['products'];
            if ($productsIds) {
                $productItems             = ProductRepo::getInstance()->getListByProductIDs($productsIds);
                $tabs[$index]['products'] = ProductSimple::collection($productItems)->jsonSerialize();
            }
        }
        $content['tabs']  = $tabs;
        $content['title'] = $content['title'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle page
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleArticle($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';
        $content['items'] = ArticleRepo::getInstance()->getListByArticleIDs($content['items'])->jsonSerialize();

        return $content;
    }

    /**
     * Handle page
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handlePage($content): array
    {
        $content['title'] = $content['title'][locale_code()] ?? '';
        $content['items'] = PageRepo::getInstance()->getListByPageIDs($content['items'])->jsonSerialize();

        return $content;
    }

    /**
     * Handle products
     *
     * @param  $content
     * @return array
     * @throws Exception
     */
    private function handleProducts($content): array
    {
        $productItems        = ProductRepo::getInstance()->getListByProductIDs($content['products']);
        $content['products'] = ProductSimple::collection($productItems)->jsonSerialize();
        $content['title']    = $content['title'][locale_code()] ?? '';

        return $content;
    }

    /**
     * Handle images
     *
     * @throws Exception
     */
    private function handleImages($images): array
    {
        if (empty($images)) {
            return [];
        }

        foreach ($images as $index => $image) {
            $imagePath = is_array($image['image']) ? $image['image'][locale_code()] ?? '' : $image['image'] ?? '';

            $images[$index]['image'] = image_origin($imagePath);

            $link = $image['link'];
            if (empty($link)) {
                continue;
            }

            $type  = $link['type'] ?? '';
            $value = $link['type'] == 'custom' ? $link['value'] : ((int) $link['value'] ?? 0);

            $images[$index]['link']['link'] = $this->handleLink($type, $value);
        }

        return $images;
    }

    /**
     * Handle links
     *
     * @param  $type
     * @param  $value
     * @return string
     * @throws Exception
     */
    private function handleLink($type, $value): string
    {
        return Link::getInstance()->link($type, $value);
    }
}
