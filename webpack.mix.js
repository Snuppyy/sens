const mix = require('laravel-mix');

mix.disableNotifications();

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.js$/,
                use: ['source-map-loader'],
                enforce: 'pre'
            }
        ],
    },
    plugins: [
        new (require('vuetify-loader/lib/plugin'))({
            match(originalTag, {kebabTag, camelTag, path, component}) {
                if([
                    'VTextarea',
                    'VDataTable',
                    'VEditDialog',
                    'VAutocomplete',
                    'VSelectListItemContent'
                ].indexOf(camelTag) >= 0) {
                    return [camelTag, `import ${camelTag} from '~/components/${camelTag}/${camelTag}'`]
                }
            }
        })
    ],
    resolve: {
        alias: {
            '~': require('path').join(__dirname, './resources/frontend/spa/js')
        }
    },
    devtool: !mix.inProduction() && 'eval-source-map',
});

mix.options({
    runtimeChunkPath: 'js',
    terser: {
        extractComments: false
    }
});

mix.ts('resources/frontend/spa/js/app.js', 'js/spa')
    .vue()
    .sass('resources/frontend/spa/styles/app.scss', 'css/spa');

mix.js('resources/frontend/site/js/index.js', 'js/site.js')
    .js('resources/frontend/site/js/survey.js', 'js')
    .sass('resources/frontend/site/sass/site.scss', 'css');

if(!Mix.isUsing('hmr')) {
    mix.extract([
        'bootstrap',
        'bs-custom-file-input',
        'inputmask',
        'jquery',
        'microplugin',
        'popper.js',
        'selectize',
        'sifter'
    ], 'js/vendor.js');

    mix.extract([
        'lodash'
    ], 'js/common.js');

    mix.extract();

    mix.version();
}