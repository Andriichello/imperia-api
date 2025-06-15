const mix = require('laravel-mix');
const path = require('path');

mix.ts('resources/js/app.ts', 'public/js')
  .vue({
    version: 3,
    options: {
      compilerOptions: {
        isCustomElement: (tag) => ['model-viewer'].includes(tag)
      }
    }
  })
  .postCss('resources/css/app.css', 'public/css')
  .webpackConfig({
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'resources/js'),
        "@api": path.resolve(__dirname, "resources/js/api"),
      },
    },
    output: {
      chunkFilename: 'js/[name].js?id=[chunkhash]',
    },
    watchOptions: {
      ignored: [
        '**/public/**',
        'node_modules/**',
        './dist/**',
        './mix-manifest.json',
        '**/mix-manifest.json',
      ],
    },
  })
  .browserSync({
    proxy: 'http://localhost:8080',
    files: [
      'resources/js/**/*',
      'resources/css/**/*',
      'app/**/*',
      'routes/**/*',
      'resources/views/**/*'
    ],
    open: false
  })
  .version();

// In development, let's see source maps
if (!mix.inProduction()) {
  mix.sourceMaps();
}
