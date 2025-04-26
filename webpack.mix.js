const mix = require('laravel-mix');
const path = require('path');

mix.js('resources/js/app.js', 'public/js')
  .vue({
    version: 3,
    options: {
      compilerOptions: {
        isCustomElement: (tag) => ['model-viewer'].includes(tag)
      }
    }
  })
  .postCss('resources/css/app.css', 'public/css', [
    require('@tailwindcss/postcss'),
  ])
  .webpackConfig({
    resolve: {
      alias: {
        '@': path.resolve('resources/js'),
      },
    },
    output: {
      chunkFilename: 'js/[name].js?id=[chunkhash]',
    }
  });

// In development, let's see source maps
if (!mix.inProduction()) {
  mix.sourceMaps();
}
