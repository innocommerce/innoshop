<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Libraries;

use Illuminate\Support\Str;

class MetaInfo
{
    private object $object;

    private string $type;

    private string $systemMetaTitle;

    private string $systemMetaDescription;

    private string $systemMetaKeywords;

    /**
     * @param  $object
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->setType();
        $this->setSystemInfo();
    }

    public static function getInstance($object): MetaInfo
    {
        return new self($object);
    }

    /**
     * @return MetaInfo
     */
    public function setType(): static
    {
        $this->type = Str::lower(class_basename($this->object));

        return $this;
    }

    /**
     * @return void
     */
    public function setSystemInfo(): void
    {
        $this->systemMetaTitle       = (string) system_setting_locale('meta_title');
        $this->systemMetaDescription = (string) system_setting_locale('meta_description');
        $this->systemMetaKeywords    = (string) system_setting_locale('meta_keywords');
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        $metaTitle = $this->object->translation->meta_title ?? '';
        if (empty($metaTitle)) {
            $metaTitle = $this->getName();
        }

        if ($this->systemMetaTitle) {
            $metaTitle .= ' - '.$this->systemMetaTitle;
        }

        return $metaTitle;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $metaDescription = $this->object->translation->meta_description ?? '';
        if (empty($metaDescription)) {
            $metaDescription = $this->getName();
        }

        if ($this->systemMetaDescription) {
            $metaDescription .= '. '.$this->systemMetaDescription;
        }

        return $metaDescription;
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        $metaKeywords = $this->object->translation->meta_keywords ?? '';
        if (empty($metaKeywords)) {
            $metaKeywords = $this->getName();
        }

        if ($this->systemMetaKeywords) {
            $metaKeywords .= ', '.$this->systemMetaKeywords;
        }

        return $metaKeywords;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $object = $this->object;
        $type   = $this->type;
        if (in_array($type, ['category', 'product', 'tag'])) {
            return $object->fallbackName('name');
        } elseif (in_array($type, ['catalog', 'article', 'page'])) {
            return $object->fallbackName('title');
        } elseif ($type == 'brand') {
            return $object->name;
        }

        return '';
    }
}
