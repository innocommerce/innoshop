const mix = require('laravel-mix');

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

mix.sass('innopacks/front/resources/css/bootstrap/bootstrap.scss', 'public/build/css/bootstrap.css');
mix.sass('innopacks/front/resources/css/app.scss', 'public/build/css/app.css');
mix.js('innopacks/front/resources/js/app.js', 'public/build/js/app.js');

// panel
mix.sass('innopacks/panel/resources/css/bootstrap/bootstrap.scss', 'public/build/panel/css/bootstrap.css');
mix.sass('innopacks/panel/resources/css/app.scss', 'public/build/panel/css/app.css');
mix.js('innopacks/panel/resources/js/app.js', 'public/build/panel/js/app.js');

if (mix.inProduction()) {
  mix.version();
}

mix.options({
  terser: {
    extractComments: false,
  },
});