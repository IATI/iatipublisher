/* eslint-disable import/no-extraneous-dependencies */
const path = require('path');
const mix = require('laravel-mix');
require('laravel-mix-svg-vue');
const tailwindCss = require('tailwindcss');

mix.extract();
if (mix.inProduction()) {
  mix.version();
  mix.options({
    terser: {
      terserOptions: {
        compress: {
          drop_console: true,
        },
      },
    },
  });
} else {
  mix.webpackConfig({ devtool: 'inline-source-map' }).sourceMaps();
}

mix.webpackConfig({
  resolve: {
    alias: {
      Components: path.resolve(__dirname, './resources/assets/js/components/'),
      Composable: path.resolve(__dirname, './resources/assets/js/composable/'),
      Organisation: path.resolve(
        __dirname,
        './resources/assets/js/views/organisation'
      ),
      Activity: path.resolve(
        __dirname,
        './resources/assets/js/views/activity/'
      ),
      Interfaces: path.resolve(__dirname, './resources/assets/js/interfaces/'),
      Store: path.resolve(__dirname, './resources/assets/js/store/'),
      Services: path.resolve(__dirname, './resources/assets/js/services/'),
    },
  },
});

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.ts('resources/assets/js/app.ts', 'public/js').vue().version();

mix.ts('resources/assets/js/scripts/script.ts', 'public/js').version();
mix
  .ts('resources/assets/js/scripts/webportal-script.ts', 'public/js')
  .version();

mix.ts('resources/assets/js/scripts/formbuilder.ts', 'public/js').version();

mix
  .sass('resources/assets/sass/app.scss', 'public/css')
  .sass('resources/assets/sass/webportal-app.scss', 'public/css')

  .options({
    processCssUrls: false,
    postCss: [tailwindCss('tailwind.config.js')],
  })
  .svgVue({
    svgPath: 'resources/assets/images/svg',
    svgoSettings: [
      { removeTitle: true },
      { removeViewBox: false },
      { removeDimensions: true },
      { prefixIds: true },
    ],
  })
  .version();

mix.options({ runtimeChunkPath: '.' });
