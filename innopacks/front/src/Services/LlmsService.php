<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\PageRepo;

class LlmsService extends BaseService
{
    /**
     * Generate llms.txt content.
     * If custom content is set, use it directly.
     * Otherwise, auto-generate from store data.
     *
     * @return string
     */
    public function generate(): string
    {
        $customContent = trim((string) system_setting('llms_custom_content', ''));
        if ($customContent !== '') {
            return $customContent;
        }

        return $this->generateDefault();
    }

    /**
     * Generate default llms.txt content (always, ignoring custom content).
     *
     * @return string
     */
    public function generateDefault(): string
    {
        $lines = [];

        $storeName       = front_store_name();
        $metaDescription = system_setting_locale('meta_description', '');

        $lines[] = '# '.$storeName;
        if ($metaDescription) {
            $lines[] = '> '.$metaDescription;
        }
        $lines[] = '';

        $lines[] = '## Links';
        $lines[] = '';
        $lines[] = '- [Home]('.url('/').')';
        $lines[] = '- [Products]('.url('/products').')';
        $lines[] = '- [Sitemap XML]('.url('/sitemap.xml').')';
        $lines[] = '';

        $categories = CategoryRepo::getInstance()->builder(['active' => true, 'parent_id' => 0])->limit(50)->get();
        if ($categories->count() > 0) {
            $lines[] = '## Categories';
            $lines[] = '';
            foreach ($categories as $category) {
                $lines[] = '- ['.$category->fallbackName().']('.$category->url.')';
            }
            $lines[] = '';
        }

        $pages = PageRepo::getInstance()->builder(['active' => true])->get();
        if ($pages->count() > 0) {
            $lines[] = '## Pages';
            $lines[] = '';
            foreach ($pages as $page) {
                $lines[] = '- ['.$page->fallbackName('title').']('.$page->url.')';
            }
            $lines[] = '';
        }

        $email     = system_setting('email', '');
        $telephone = system_setting('telephone', '');
        if ($email || $telephone) {
            $lines[] = '## Contact';
            $lines[] = '';
            if ($email) {
                $lines[] = '- Email: '.$email;
            }
            if ($telephone) {
                $lines[] = '- Phone: '.$telephone;
            }
            $lines[] = '';
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * Write llms.txt to public directory.
     *
     * @return void
     */
    public function writeFile(): void
    {
        file_put_contents(public_path('llms.txt'), "\xEF\xBB\xBF".$this->generate());
    }
}
