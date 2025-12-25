const mix = require('laravel-mix');
const fs = require('fs');

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
    }
};

// Execute build process
buildProcess.init();
buildProcess.compile();
buildProcess.optimize();
