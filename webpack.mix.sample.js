const mix = require('laravel-mix');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Configuration
const config = {
    theme: process.env.THEME || '',
    paths: {
        themes: 'themes',
        build: 'public/build',
        static: 'public/static/themes',
        front: 'innopacks/front/resources',
        panel: 'innopacks/panel/resources',
        install: 'innopacks/install/resources'
    },
    permissions: {
        owner: 'www:www',
        mode: '755'
    }
};

// Utility functions
const utils = {
    /**
     * Check if file exists
     */
    fileExists: (path) => fs.existsSync(path),
    
    /**
     * Create directory recursively
     */
    createDir: (path) => fs.mkdirSync(path, { recursive: true }),
    
    /**
     * Remove directory recursively
     */
    removeDir: (path) => fs.rmSync(path, { recursive: true, force: true }),
    
    /**
     * Log with emoji
     */
    log: (message, emoji = 'ℹ️') => console.log(`${emoji} ${message}`)
};

// Theme management
const themeManager = {
    /**
     * Clean up theme build directory
     */
    cleanup: () => {
        if (config.theme && config.theme !== 'default') {
            const themeBuildPath = `${config.paths.static}/${config.theme}`;
            
            if (utils.fileExists(themeBuildPath)) {
                utils.removeDir(themeBuildPath);
                utils.log(`Cleaned up: ${themeBuildPath}`, '🧹');
            }
            
            utils.createDir(`${themeBuildPath}/css`);
            utils.createDir(`${themeBuildPath}/js`);
            utils.log(`Created directories: ${themeBuildPath}/css, ${themeBuildPath}/js`, '📁');
        }
    },
    
    /**
     * Compile theme resources
     */
    compile: () => {
        if (!config.theme || config.theme === 'default') return;
        
        utils.log(`Compiling theme: ${config.theme}`, '🎨');
        
        const themeDir = `${config.paths.themes}/${config.theme}`;
        const outputDir = `${config.paths.static}/${config.theme}`;
        
        // Compile CSS files
        themeManager.compileCSS(themeDir, outputDir);
        
        // Compile JS files
        themeManager.compileJS(themeDir, outputDir);
        
        // Compile Bootstrap
        themeManager.compileBootstrap(themeDir, outputDir);
        
        utils.log(`Theme ${config.theme} compilation completed!`, '✅');
    },
    
    /**
     * Compile theme CSS files
     */
    compileCSS: (themeDir, outputDir) => {
        const appScss = `${themeDir}/css/app.scss`;
        if (utils.fileExists(appScss)) {
            mix.sass(appScss, `${outputDir}/css/app.css`);
            utils.log(`Compiled: ${appScss}`, '✅');
        }
    },
    
    /**
     * Compile theme JS files
     */
    compileJS: (themeDir, outputDir) => {
        const appJs = `${themeDir}/js/app.js`;
        if (utils.fileExists(appJs)) {
            mix.js(appJs, `${outputDir}/js/app.js`);
            utils.log(`Compiled: ${appJs}`, '✅');
        }
    },
    
    /**
     * Compile theme Bootstrap
     */
    compileBootstrap: (themeDir, outputDir) => {
        const bootstrapScss = `${themeDir}/css/bootstrap/bootstrap.scss`;
        if (utils.fileExists(bootstrapScss)) {
            mix.sass(bootstrapScss, `${outputDir}/css/bootstrap.css`);
            utils.log(`Compiled: ${bootstrapScss}`, '✅');
        }
    }
};

// Default resources compilation
const defaultResources = {
    /**
     * Compile frontend resources
     */
    frontend: () => {
        const { front, build } = config.paths;
        
        mix.sass(`${front}/css/bootstrap/bootstrap.scss`, `${build}/front/css/bootstrap.css`);
        mix.sass(`${front}/css/app.scss`, `${build}/front/css/app.css`);
        mix.js(`${front}/js/app.js`, `${build}/front/js/app.js`);
    },
    
    /**
     * Compile panel resources
     */
    panel: () => {
        const { panel, build } = config.paths;
        
        mix.sass(`${panel}/css/bootstrap/bootstrap.scss`, `${build}/panel/css/bootstrap.css`);
        mix.sass(`${panel}/css/app.scss`, `${build}/panel/css/app.css`);
        mix.js(`${panel}/js/app.js`, `${build}/panel/js/app.js`);
    },
    
    /**
     * Compile install resources
     */
    install: () => {
        const { install, build } = config.paths;
        
        mix.sass(`${install}/css/app.scss`, `${build}/install/css/app.css`);
    }
};

// Theme distribution management
const themeDistributor = {
    /**
     * Copy compiled assets to theme's public directory for distribution
     */
    copyToThemePublic: () => {
        if (!config.theme || config.theme === 'default') {
            utils.log('Skipping theme distribution (default theme)', 'ℹ️');
            return;
        }
        
        const sourceDir = `${config.paths.static}/${config.theme}`;
        const targetDir = `${config.paths.themes}/${config.theme}/public`;
        
        if (!utils.fileExists(sourceDir)) {
            utils.log(`Source directory not found: ${sourceDir}`, '⚠️');
            return;
        }
        
        try {
            // Create target directory structure
            utils.createDir(`${targetDir}/css`);
            utils.createDir(`${targetDir}/js`);
            
            // Copy CSS files
            const cssFiles = ['app.css', 'bootstrap.css'];
            cssFiles.forEach(file => {
                const sourceFile = `${sourceDir}/css/${file}`;
                const targetFile = `${targetDir}/css/${file}`;
                
                if (utils.fileExists(sourceFile)) {
                    fs.copyFileSync(sourceFile, targetFile);
                    utils.log(`Copied: ${file} → ${targetDir}/css/`, '📦');
                }
            });
            
            // Copy JS files
            const jsFile = `${sourceDir}/js/app.js`;
            const targetJsFile = `${targetDir}/js/app.js`;
            if (utils.fileExists(jsFile)) {
                utils.createDir(`${targetDir}/js`);
                fs.copyFileSync(jsFile, targetJsFile);
                utils.log(`Copied: app.js → ${targetDir}/js/`, '📦');
            }
            
            // Copy source maps if they exist
            const mapFiles = [
                { source: `${sourceDir}/css/app.css.map`, target: `${targetDir}/css/app.css.map` },
                { source: `${sourceDir}/css/bootstrap.css.map`, target: `${targetDir}/css/bootstrap.css.map` },
                { source: `${sourceDir}/js/app.js.map`, target: `${targetDir}/js/app.js.map` }
            ];
            
            mapFiles.forEach(({ source, target }) => {
                if (utils.fileExists(source)) {
                    const targetDirPath = path.dirname(target);
                    utils.createDir(targetDirPath);
                    fs.copyFileSync(source, target);
                    utils.log(`Copied map: ${path.basename(source)}`, '🗺️');
                }
            });
            
            utils.log(`Theme assets copied to: ${targetDir}`, '✅');
        } catch (error) {
            utils.log(`Error copying theme assets: ${error.message}`, '❌');
        }
    }
};

// Permission management
const permissionManager = {
    /**
     * Set directory permissions recursively
     * @param {string} path - Directory path
     */
    setPermissions: (path) => {
        try {
            // Only execute on Linux systems
            if (process.platform !== 'linux') {
                utils.log(`Skipping permission setup on ${process.platform}: ${path}`, '⚠️');
                return;
            }
            
            // Check if directory exists
            if (!utils.fileExists(path)) {
                utils.log(`Directory not found: ${path}`, '⚠️');
                return;
            }
            
            const { owner, mode } = config.permissions;
            
            // Set ownership recursively
            try {
                execSync(`chown -R ${owner} ${path}`, { stdio: 'pipe' });
                utils.log(`Set ownership ${owner} on: ${path}`, '🔐');
            } catch (error) {
                // If chown fails (e.g., permission denied), log warning but continue
                utils.log(`Failed to set ownership (may need sudo): ${path}`, '⚠️');
            }
            
            // Set permissions recursively
            try {
                execSync(`chmod -R ${mode} ${path}`, { stdio: 'pipe' });
                utils.log(`Set permissions ${mode} on: ${path}`, '🔐');
            } catch (error) {
                utils.log(`Failed to set permissions: ${path}`, '⚠️');
            }
        } catch (error) {
            utils.log(`Error setting permissions for ${path}: ${error.message}`, '❌');
        }
    },
    
    /**
     * Set permissions for public directory after compilation
     */
    setPublicPermissions: () => {
        const publicPath = 'public';
        permissionManager.setPermissions(publicPath);
    }
};

// Build process
const buildProcess = {
    /**
     * Initialize build process
     */
    init: () => {
        themeManager.cleanup();
    },
    
    /**
     * Compile all resources
     */
    compile: () => {
        // Compile default resources
        defaultResources.frontend();
        defaultResources.panel();
        defaultResources.install();
        
        // Compile theme resources
        themeManager.compile();
    },
    
    /**
     * Apply production optimizations
     */
    optimize: () => {
        if (mix.inProduction()) {
            mix.version();
        }
        
        mix.options({
            terser: {
                extractComments: false,
            },
        });
    },
    
    /**
     * Set permissions after compilation
     */
    setPermissions: () => {
        // Use mix.then() to execute after compilation
        mix.then(() => {
            utils.log('Compilation completed, setting permissions...', '🔐');
            permissionManager.setPublicPermissions();
            utils.log('Permission setup completed!', '✅');
        });
    },
    
    /**
     * Copy theme assets to theme's public directory for distribution
     */
    distributeTheme: () => {
        // Use mix.then() to execute after compilation
        mix.then(() => {
            utils.log('Copying theme assets to theme public directory...', '📦');
            themeDistributor.copyToThemePublic();
        });
    }
};

// Execute build process
buildProcess.init();
buildProcess.compile();
buildProcess.optimize();
buildProcess.setPermissions();
buildProcess.distributeTheme();