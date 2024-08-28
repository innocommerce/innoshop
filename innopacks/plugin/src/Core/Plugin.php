<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Core;

use ArrayAccess;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InnoShop\Plugin\Repositories\PluginRepo;
use InnoShop\Plugin\Repositories\SettingRepo;

final class Plugin implements Arrayable, ArrayAccess
{
    public const TYPES = [
        'billing',
        'shipping',
        'feature',
        'fee',
        'social',
        'language',
        'intelli',
    ];

    protected string $type;

    protected string $path;

    protected string $code;

    protected string $icon;

    protected string $author;

    protected array|string $name;

    protected array|string $description;

    protected array $packageInfo;

    protected string $dirName;

    protected bool $installed;

    protected bool $enabled;

    protected int $priority;

    protected string $version;

    protected array $fields = [];

    public function __construct(string $path, array $packageInfo)
    {
        $this->path        = $path;
        $this->packageInfo = $packageInfo;
        $this->validateConfig();
    }

    public function __get($name)
    {
        return $this->packageInfoAttribute(Str::snake($name, '-'));
    }

    public function packageInfoAttribute($name)
    {
        return Arr::get($this->packageInfo, $name);
    }

    /**
     * Set plugin Type
     *
     * @throws Exception
     */
    public function setType(string $type): self
    {
        if (! in_array($type, self::TYPES)) {
            throw new Exception('Invalid plugin type, must be one of '.implode(',', self::TYPES));
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @param  string  $dirName
     * @return $this
     */
    public function setDirname(string $dirName): self
    {
        $this->dirName = $dirName;

        return $this;
    }

    /**
     * @param  string  $code
     * @return $this
     */
    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @param  string|array  $name
     * @return $this
     */
    public function setName(string|array $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param  string|array  $description
     * @return $this
     */
    public function setDescription(string|array $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param  string  $icon
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param  string  $author
     * @return $this
     */
    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @param  bool  $installed
     * @return $this
     */
    public function setInstalled(bool $installed): self
    {
        $this->installed = $installed;

        return $this;
    }

    /**
     * @param  bool  $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @param  int  $priority
     * @return $this
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @param  string  $version
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Set plugin fields.
     *
     * @return $this
     */
    public function setFields(): self
    {
        $fieldsPath = $this->path.DIRECTORY_SEPARATOR.'fields.php';
        if (! file_exists($fieldsPath)) {
            return $this;
        }

        $fieldsData = require_once $fieldsPath;
        if (is_array($fieldsData) && $fieldsData) {
            $this->fields = $fieldsData;
        }

        return $this;
    }

    /**
     * Get name from config
     *
     * @return array|string
     */
    public function getName(): array|string
    {
        return $this->name;
    }

    /**
     * Get current locale name
     *
     * @return string
     * @throws Exception
     */
    public function getLocaleName(): string
    {
        $currentLocale = locale_code();

        if (is_array($this->name)) {
            if ($this->name[$currentLocale] ?? '') {
                return $this->name[$currentLocale];
            }

            return array_values($this->name)[0];
        }

        return (string) $this->name;
    }

    /**
     * Get description from config
     *
     * @return array|string
     */
    public function getDescription(): array|string
    {
        return $this->description;
    }

    /**
     * Get current local description
     *
     * @return string
     * @throws Exception
     */
    public function getLocaleDescription(): string
    {
        $currentLocale = locale_code();

        if (is_array($this->description)) {
            if ($this->description[$currentLocale] ?? '') {
                return $this->description[$currentLocale];
            }

            return array_values($this->description)[0];
        }

        return (string) $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDirname(): string
    {
        return $this->dirName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getEditUrl(): string
    {
        return panel_route('plugins.edit', ['plugin' => $this->code]);
    }

    public function checkActive(): bool
    {
        return PluginRepo::getInstance()->checkActive($this->code);
    }

    public function checkInstalled(): bool
    {
        return PluginRepo::getInstance()->installed($this->code);
    }

    public function checkPriority(): int
    {
        return PluginRepo::getInstance()->getPriority($this->code);
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Retrieve the corresponding setting fields of the plugin,
     * and obtain the field values that have been stored in the database.
     *
     * @return array
     */
    public function getFields(): array
    {
        if ($this->getType() == 'billing') {
            $this->fields[] = SettingRepo::getInstance()->getPluginAvailableField();
        }

        $this->fields[] = SettingRepo::getInstance()->getPluginActiveField();
        $existValues    = SettingRepo::getInstance()->getPluginFields($this->code);
        foreach ($this->fields as $index => $field) {
            $dbField = $existValues[$field['name']] ?? null;
            $value   = $dbField ? $dbField->value : null;
            if ($field['name'] == 'active') {
                $value = (int) $value;
            }
            $this->fields[$index]['value'] = $value;
        }

        return $this->fields;
    }

    /**
     * Handle the multilingual settings of plugin backend fields with the priority: label > label_key.
     * If there is a label field, return it directly; if there is no label field, then use label_key for translation.
     */
    public function handleLabel(): void
    {
        $this->fields = collect($this->fields)->map(function ($item) {
            $item = $this->transLabel($item);
            if (isset($item['options'])) {
                $item['options'] = collect($item['options'])->map(function ($option) {
                    return $this->transLabel($option);
                })->toArray();
            }

            return $item;
        })->toArray();
    }

    /**
     * Retrieve the custom editing template of the plugin.
     *
     * @return string
     */
    public function getFieldView(): string
    {
        $viewFile = $this->getPath().'/Views/panel/config.blade.php';
        if (file_exists($viewFile)) {
            return "{$this->dirName}::panel.config";
        }

        return '';
    }

    /**
     * Get plugin boot class file path.
     *
     * @return string
     */
    public function getBootFile(): string
    {
        return $this->getPath().'/Boot.php';
    }

    /**
     * Field validation
     */
    public function validateConfig(): void
    {
        Validator::validate($this->packageInfo, [
            'type'        => 'required',
            'name'        => 'required',
            'description' => 'required',
            'code'        => 'required|string|min:3|max:64',
            'version'     => 'required|string',
        ]);
    }

    /**
     * Field validation
     *
     * @param  $requestData
     * @return \Illuminate\Validation\Validator
     */
    public function validateFields($requestData): \Illuminate\Validation\Validator
    {
        $rules = array_column($this->getFields(), 'rules', 'name');

        return Validator::make($requestData, $rules);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge([
            'name'    => $this->name,
            'version' => $this->getVersion(),
            'path'    => $this->path,
        ], $this->packageInfo);
    }

    /**
     * Translate label
     * @param  $item
     * @return mixed
     */
    private function transLabel($item): mixed
    {
        $labelKey = $item['label_key'] ?? '';
        $label    = $item['label']     ?? '';
        if (empty($label) && $labelKey) {
            $languageKey   = "$this->dirName::$labelKey";
            $item['label'] = trans($languageKey);
        }

        $descriptionKey = $item['description_key'] ?? '';
        $description    = $item['description']     ?? '';
        if (empty($description) && $descriptionKey) {
            $languageKey         = "$this->dirName::$descriptionKey";
            $item['description'] = trans($languageKey);
        }

        return $item;
    }

    /**
     * @param  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return Arr::has($this->packageInfo, $offset);
    }

    /**
     * @param  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->packageInfoAttribute($offset);
    }

    /**
     * @param  $offset
     * @param  $value
     * @return array
     */
    public function offsetSet($offset, $value): array
    {
        return Arr::set($this->packageInfo, $offset, $value);
    }

    /**
     * @param  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->packageInfo[$offset]);
    }
}
