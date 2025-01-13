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

    /**
     * @param  $object
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->setType();
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
     * @return string
     */
    public function getTitle(): string
    {
        $metaTitle = $this->object->translation->meta_title ?? '';
        if ($metaTitle) {
            return $metaTitle;
        }

        return $this->getName();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        $metaDescription = $this->object->translation->meta_description ?? '';
        if ($metaDescription) {
            return $metaDescription;
        }

        return $this->getName();
    }

    /**
     * @return string
     */
    public function getKeywords(): string
    {
        $metaKeywords = $this->object->translation->meta_keywords ?? '';
        if ($metaKeywords) {
            return $metaKeywords;
        }

        return $this->getName();
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
