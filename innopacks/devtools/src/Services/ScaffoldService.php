<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ScaffoldService
{
    /**
     * Get template path.
     *
     * @return string
     */
    private function getTemplatePath(): string
    {
        return config('devtools.template_path', __DIR__.'/../Templates');
    }

    /**
     * Generate plugin scaffold.
     *
     * @param  string  $name
     * @param  array  $options
     * @return bool
     */
    public function generatePlugin(string $name, array $options = []): bool
    {
        $pluginName = Str::studly($name);
        $pluginCode = Str::snake($name);
        $pluginPath = base_path("plugins/{$pluginName}");

        // Check if plugin already exists
        if (File::exists($pluginPath)) {
            throw new \RuntimeException("Plugin {$pluginName} already exists!");
        }

        // Create directory structure
        $directories = [
            'Controllers',
            'Models',
            'Services',
            'Repositories',
            'Routes',
            'Views',
            'Lang/en',
            'Lang/zh-cn',
            'Public/images',
            'Database/Migrations',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$pluginPath}/{$dir}", 0755, true);
        }

        // Generate files
        $this->generateBootFile($pluginPath, $pluginName, $pluginCode);
        $this->generateConfigJson($pluginPath, $pluginName, $pluginCode, $options);
        $this->generateFieldsFile($pluginPath, $pluginName);
        $this->generateLanguageFiles($pluginPath, $pluginName, $pluginCode);

        // Generate optional files
        if ($options['with_controller'] ?? false) {
            $this->generateController($pluginPath, $pluginName, "{$pluginName}Controller");
        }

        if ($options['with_model'] ?? false) {
            $modelName = Str::singular($pluginName);
            $this->generateModel($pluginPath, $pluginName, $modelName, $pluginCode);
        }

        if ($options['with_migration'] ?? false && isset($options['with_model'])) {
            $tableName = Str::plural(Str::snake($pluginName));
            $this->generateMigration($pluginPath, $pluginName, $tableName, "create_{$tableName}_table");
        }

        // Generate route files
        $this->generateRouteFiles($pluginPath, $pluginName);

        return true;
    }

    /**
     * Generate theme scaffold.
     *
     * @param  string  $name
     * @param  array  $options
     * @return bool
     */
    public function generateTheme(string $name, array $options = []): bool
    {
        $themeCode = Str::snake($name);
        $themePath = base_path("themes/{$themeCode}");

        // Check if theme already exists
        if (File::exists($themePath)) {
            throw new \RuntimeException("Theme {$themeCode} already exists!");
        }

        // Create directory structure
        $directories = [
            'views',
            'public/css',
            'public/js',
            'public/images',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$themePath}/{$dir}", 0755, true);
        }

        // Generate config.json
        $this->generateThemeConfigJson($themePath, $themeCode, $options);

        // Generate basic layout
        $this->generateThemeLayout($themePath, $themeCode);

        return true;
    }

    /**
     * Generate file from template.
     *
     * @param  string  $templatePath
     * @param  string  $targetPath
     * @param  array  $replacements
     * @return void
     */
    private function generateFile(string $templatePath, string $targetPath, array $replacements): void
    {
        $content = File::get($templatePath);

        foreach ($replacements as $key => $value) {
            $content = str_replace("{{ \${$key} }}", $value, $content);
        }

        File::put($targetPath, $content);
    }

    /**
     * Generate Boot.php file.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $pluginCode
     * @return void
     */
    private function generateBootFile(string $pluginPath, string $pluginName, string $pluginCode): void
    {
        $template = $this->getTemplatePath().'/plugin/Boot.php.stub';
        $target   = "{$pluginPath}/Boot.php";

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
        ]);
    }

    /**
     * Generate config.json file.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $pluginCode
     * @param  array  $options
     * @return void
     */
    private function generateConfigJson(string $pluginPath, string $pluginName, string $pluginCode, array $options): void
    {
        $template = $this->getTemplatePath().'/plugin/config.json.stub';
        $target   = "{$pluginPath}/config.json";

        $type   = $options['type'] ?? 'feature';
        $nameZh = $options['name_zh'] ?? $pluginName;
        $nameEn = $options['name_en'] ?? $pluginName;
        $descZh = $options['description_zh'] ?? "{$pluginName} 插件";
        $descEn = $options['description_en'] ?? "{$pluginName} Plugin";

        $this->generateFile($template, $target, [
            'pluginCode'          => $pluginCode,
            'pluginNameZh'        => $nameZh,
            'pluginNameEn'        => $nameEn,
            'pluginDescriptionZh' => $descZh,
            'pluginDescriptionEn' => $descEn,
            'pluginType'          => $type,
        ]);
    }

    /**
     * Generate fields.php file.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @return void
     */
    private function generateFieldsFile(string $pluginPath, string $pluginName): void
    {
        $template = $this->getTemplatePath().'/plugin/fields.php.stub';
        $target   = "{$pluginPath}/fields.php";

        File::copy($template, $target);
    }

    /**
     * Generate language files.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $pluginCode
     * @return void
     */
    private function generateLanguageFiles(string $pluginPath, string $pluginName, string $pluginCode): void
    {
        $langFiles = ['common.php', 'panel.php', 'front.php'];

        foreach ($langFiles as $langFile) {
            $enPath = "{$pluginPath}/Lang/en/{$langFile}";
            $zhPath = "{$pluginPath}/Lang/zh-cn/{$langFile}";

            File::put($enPath, "<?php\n\nreturn [\n    // English translations\n];\n");
            File::put($zhPath, "<?php\n\nreturn [\n    // 中文翻译\n];\n");
        }
    }

    /**
     * Generate controller file.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $className
     * @return void
     */
    private function generateController(string $pluginPath, string $pluginName, string $className): void
    {
        $template = $this->getTemplatePath().'/plugin/Controller.php.stub';
        $target   = "{$pluginPath}/Controllers/{$className}.php";

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
            'className'  => $className,
        ]);
    }

    /**
     * Generate model file.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $className
     * @param  string  $pluginCode
     * @return void
     */
    private function generateModel(string $pluginPath, string $pluginName, string $className, string $pluginCode): void
    {
        $template  = $this->getTemplatePath().'/plugin/Model.php.stub';
        $target    = "{$pluginPath}/Models/{$className}.php";
        $tableName = Str::plural(Str::snake($pluginCode));

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
            'className'  => $className,
            'tableName'  => $tableName,
        ]);
    }

    /**
     * Generate migration file.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $tableName
     * @param  string  $migrationName
     * @return void
     */
    private function generateMigration(string $pluginPath, string $pluginName, string $tableName, string $migrationName): void
    {
        $template  = $this->getTemplatePath().'/plugin/Migration.php.stub';
        $timestamp = date('Y_m_d_His');
        $target    = "{$pluginPath}/Database/Migrations/{$timestamp}_{$migrationName}.php";

        $this->generateFile($template, $target, [
            'tableName' => $tableName,
        ]);
    }

    /**
     * Generate route files.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @return void
     */
    private function generateRouteFiles(string $pluginPath, string $pluginName): void
    {
        $panelRoute = "<?php\n/**\n * Copyright (c) Since 2024 InnoShop - All Rights Reserved\n *\n * @link       https://www.innoshop.com\n * @author     InnoShop <team@innoshop.com>\n * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)\n */\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Panel routes\n";
        $frontRoute = "<?php\n/**\n * Copyright (c) Since 2024 InnoShop - All Rights Reserved\n *\n * @link       https://www.innoshop.com\n * @author     InnoShop <team@innoshop.com>\n * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)\n */\n\nuse Illuminate\\Support\\Facades\\Route;\n\n// Front routes\n";

        File::put("{$pluginPath}/Routes/panel.php", $panelRoute);
        File::put("{$pluginPath}/Routes/front.php", $frontRoute);
    }

    /**
     * Generate theme config.json.
     *
     * @param  string  $themePath
     * @param  string  $themeCode
     * @param  array  $options
     * @return void
     */
    private function generateThemeConfigJson(string $themePath, string $themeCode, array $options): void
    {
        $template = $this->getTemplatePath().'/theme/config.json.stub';
        $target   = "{$themePath}/config.json";

        $nameZh = $options['name_zh'] ?? $themeCode;
        $nameEn = $options['name_en'] ?? $themeCode;
        $descZh = $options['description_zh'] ?? "{$themeCode} 主题";
        $descEn = $options['description_en'] ?? "{$themeCode} Theme";

        $this->generateFile($template, $target, [
            'themeCode'          => $themeCode,
            'themeNameZh'        => $nameZh,
            'themeNameEn'        => $nameEn,
            'themeDescriptionZh' => $descZh,
            'themeDescriptionEn' => $descEn,
        ]);
    }

    /**
     * Generate theme layout file.
     *
     * @param  string  $themePath
     * @param  string  $themeCode
     * @return void
     */
    private function generateThemeLayout(string $themePath, string $themeCode): void
    {
        $template = $this->getTemplatePath().'/theme/layout.blade.php.stub';
        $target   = "{$themePath}/views/layout.blade.php";

        File::copy($template, $target);
    }

    /**
     * Generate controller for plugin.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $controllerName
     * @return void
     */
    public function generateControllerForPlugin(string $pluginPath, string $pluginName, string $controllerName): void
    {
        $className = Str::studly($controllerName);
        $template  = $this->getTemplatePath().'/plugin/Controller.php.stub';
        $target    = "{$pluginPath}/Controllers/{$className}.php";

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
            'className'  => $className,
        ]);
    }

    /**
     * Generate model for plugin.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $modelName
     * @return void
     */
    public function generateModelForPlugin(string $pluginPath, string $pluginName, string $modelName): void
    {
        $className  = Str::studly($modelName);
        $pluginCode = Str::snake($pluginName);
        $tableName  = Str::plural(Str::snake($modelName));
        $template   = $this->getTemplatePath().'/plugin/Model.php.stub';
        $target     = "{$pluginPath}/Models/{$className}.php";

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
            'className'  => $className,
            'tableName'  => $tableName,
        ]);
    }

    /**
     * Generate service for plugin.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $serviceName
     * @return void
     */
    public function generateServiceForPlugin(string $pluginPath, string $pluginName, string $serviceName): void
    {
        $className = Str::studly($serviceName);
        $template  = $this->getTemplatePath().'/plugin/Service.php.stub';
        $target    = "{$pluginPath}/Services/{$className}.php";

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
            'className'  => $className,
        ]);
    }

    /**
     * Generate repository for plugin.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $repositoryName
     * @param  string  $modelName
     * @return void
     */
    public function generateRepositoryForPlugin(string $pluginPath, string $pluginName, string $repositoryName, string $modelName): void
    {
        $className      = Str::studly($repositoryName);
        $modelClassName = Str::studly($modelName);
        $template       = $this->getTemplatePath().'/plugin/Repository.php.stub';
        $target         = "{$pluginPath}/Repositories/{$className}.php";

        $this->generateFile($template, $target, [
            'pluginName' => $pluginName,
            'className'  => $className,
            'modelName'  => $modelClassName,
        ]);
    }

    /**
     * Generate migration for plugin.
     *
     * @param  string  $pluginPath
     * @param  string  $pluginName
     * @param  string  $migrationName
     * @param  string  $tableName
     * @return void
     */
    public function generateMigrationForPlugin(string $pluginPath, string $pluginName, string $migrationName, string $tableName): void
    {
        $template  = $this->getTemplatePath().'/plugin/Migration.php.stub';
        $timestamp = date('Y_m_d_His');
        $target    = "{$pluginPath}/Database/Migrations/{$timestamp}_{$migrationName}.php";

        $this->generateFile($template, $target, [
            'tableName' => $tableName,
        ]);
    }
}
