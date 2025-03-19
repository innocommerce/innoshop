<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LocaleTranslation\Services;

use Exception;
use Illuminate\Support\Facades\File;
use InnoShop\Plugin\Core\Plugin;

class LocaleService
{
    public const BASE_LOCALE = 'zh_cn';

    private static ?array $allLanguages = null;

    private string $basePath;

    public function __construct()
    {
        $this->basePath = lang_path(self::BASE_LOCALE);
    }

    /**
     * @throws Exception
     */
    public function setPlugin(Plugin $plugin): static
    {
        $pluginPath     = $plugin->getPath();
        $pluginLangPath = $pluginPath.'/Lang/'.self::BASE_LOCALE;

        if (! file_exists($pluginLangPath)) {
            throw new Exception("插件语言包目录 $pluginLangPath 不存在");
        }

        $this->basePath = $pluginLangPath;

        return $this;
    }

    /**
     * 获取语言包某个目录下的文件夹和文件列表
     *
     * @param  string  $path
     * @return array
     */
    public function getLocaleItems(string $path = ''): array
    {
        $folders = $this->getFolders($path);
        $files   = $this->getFiles($path);

        $result = [];
        if ($folders) {
            $result['folders'] = $folders;
        }
        if ($files) {
            $result['files'] = $files;
        }

        return $result;
    }

    /**
     * 获取基础语言, 以该语言为基础翻译到其他语言
     *
     * @param  $filePath
     * @return array
     * @throws Exception
     */
    public function getBaseLanguage($filePath): array
    {
        $fullPath   = $this->getFullPath($filePath);
        $baseValues = require $fullPath;

        return [
            'code'   => self::BASE_LOCALE,
            'keys'   => array_keys($baseValues),
            'values' => $baseValues,
        ];
    }

    /**
     * 获取所有语言包文件具体的语言数据
     *
     * @param  $filePath
     * @return array
     * @throws Exception
     */
    public function getLanguages($filePath): array
    {
        if (self::$allLanguages !== null) {
            return self::$allLanguages;
        }

        $fullPath = $this->getFullPath($filePath);

        $localeValues   = [];
        $localePackages = language_codes();
        foreach ($localePackages as $localePackage) {
            if ($localePackage == self::BASE_LOCALE) {
                continue;
            }
            $localePath = str_replace(self::BASE_LOCALE, $localePackage, $fullPath);

            $localeValues[$localePackage] = [];
            if (file_exists($localePath)) {
                $localeValues[$localePackage] = require $localePath;
            }
        }

        return self::$allLanguages = $localeValues;
    }

    /**
     * 根据翻译结果替换语言包文件
     *
     * @param  $filePath
     * @param  $localeValues
     * @return void
     * @throws Exception
     */
    public function replaceValues($filePath, $localeValues): void
    {
        $baseValues = $this->getBaseLanguage($filePath);
        $baseKeys   = $baseValues['keys'];
        if (empty($baseKeys)) {
            throw new Exception("默认语言包 $filePath 为空");
        }

        $fullPath = $this->getFullPath($filePath);
        foreach ($localeValues as $locale => $values) {
            $localePath = str_replace(self::BASE_LOCALE, $locale, $fullPath);
            if (! file_exists($localePath)) {
                File::ensureDirectoryExists(str_replace($filePath.'.php', '', $localePath));
                touch($localePath);
            }

            if (! file_exists($localePath)) {
                throw new Exception('创建语言包文件失败');
            }

            $originValues = require $localePath;
            if (! is_array($originValues)) {
                $originValues = [];
            }

            foreach ($originValues as $index => $originValue) {
                if (is_string($originValue)) {
                    $originValue = addslashes($originValue);
                } else {
                    throw new Exception('该文件包含二级词条,不能自动翻译');
                }
                $originValues[$index] = $originValue;
            }
            $finalValues = array_merge($originValues, $values);
            ksort($finalValues);

            file_put_contents($localePath, '<?php'.PHP_EOL);
            $this->addLicenseComment($localePath);
            file_put_contents($localePath, PHP_EOL.'return [', FILE_APPEND);

            $lines = '';
            foreach ($finalValues as $key => $finalValue) {
                if (! in_array($key, $baseKeys)) {
                    continue;
                }
                $lines .= PHP_EOL."    '$key' => '$finalValue',";
            }
            $lines .= PHP_EOL.'];';
            file_put_contents($localePath, $lines, FILE_APPEND);
        }
    }

    /**
     * @param  $filePath
     * @param  $localeValues
     * @return void
     * @throws Exception
     */
    public function formatValues($filePath, $localeValues): void
    {
        $fullPath = $this->getFullPath($filePath);
        foreach ($localeValues as $locale) {
            $localePath = str_replace(self::BASE_LOCALE, $locale, $fullPath);

            $originValues = require $localePath;
            if (! is_array($originValues)) {
                continue;
            }

            foreach ($originValues as $index => $originValue) {
                if (is_string($originValue)) {
                    $originValue = addslashes($originValue);
                    $originValue = preg_replace('/\\\+("|\')/', '\\\$1', $originValue);
                } else {
                    throw new Exception('该文件包含二级词条,不能自动格式化');
                }
                $originValues[$index] = $originValue;
            }
            ksort($originValues);

            file_put_contents($localePath, '<?php'.PHP_EOL);
            $this->addLicenseComment($localePath);
            file_put_contents($localePath, PHP_EOL.'return [', FILE_APPEND);

            $lines = '';
            foreach ($originValues as $key => $finalValue) {
                $lines .= PHP_EOL."    '$key' => '$finalValue',";
            }
            $lines .= PHP_EOL.'];';
            file_put_contents($localePath, $lines, FILE_APPEND);
        }
    }

    /**
     * @throws Exception
     */
    private function getFullPath($filePath): string
    {
        if (! stripos($filePath, '.php')) {
            $filePath = $filePath.'.php';
        }
        $fullPath = $this->basePath.$filePath;
        if (! file_exists($fullPath)) {
            throw new Exception("语言包文件 $fullPath 不存在");
        }

        return $fullPath;
    }

    /**
     * 处理语言包数据为普通列表格式
     *
     * @param  $localeValues
     * @return array
     */
    private function handleValues($localeValues): array
    {
        $result['locale_codes'] = array_keys($localeValues);
        $localeItems            = [];
        foreach ($localeValues as $code => $localeValue) {
            foreach ($localeValue as $key => $value) {
                $localeItems[$key][$code] = $value;
            }
        }

        $itemResult = [];
        foreach ($localeItems as $key => $localeItem) {
            $itemResult[] = [
                'key'    => $key,
                'values' => $localeItem,
            ];
        }
        $result['items'] = $itemResult;

        return $result;
    }

    /**
     * 获取目录下文件夹列表
     *
     * @param  string  $path
     * @return array
     */
    private function getFolders(string $path = ''): array
    {
        if (empty($path)) {
            $basePath = $this->basePath;
        } else {
            $basePath = $this->basePath.$path;
        }
        $items = [];
        $files = glob($basePath.'/*', GLOB_ONLYDIR);
        foreach ($files as $file) {
            $folderName = str_replace($this->basePath, '', $file);
            $items[]    = array_merge(['name' => $folderName], $this->getLocaleItems($folderName));
        }

        return $items;
    }

    /**
     * 获取目录下文件列表
     *
     * @param  string  $path
     * @return array
     */
    private function getFiles(string $path = ''): array
    {
        $items = [];
        if (empty($path)) {
            $basePath = $this->basePath;
        } else {
            $basePath = $this->basePath.$path;
        }
        $files = glob($basePath.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $items[] = str_replace([$this->basePath, '.php'], '', $file);
            }
        }

        return $items;
    }

    private function addLicenseComment($localePath): void
    {
        $comment = '/**'.PHP_EOL;
        $comment .= ' * Copyright (c) Since 2024 InnoShop - All Rights Reserved'.PHP_EOL;
        $comment .= ' *'.PHP_EOL;
        $comment .= ' * @link       https://www.innoshop.com'.PHP_EOL;
        $comment .= ' * @author     InnoShop <team@innoshop.com>'.PHP_EOL;
        $comment .= ' * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)'.PHP_EOL;
        $comment .= ' */'.PHP_EOL;

        file_put_contents($localePath, $comment, FILE_APPEND);
    }
}
