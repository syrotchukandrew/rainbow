const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Entry points — one bundle per section
    .addEntry('app', './assets/app.ts')
    .addEntry('site', './assets/site/entry.tsx')
    .addEntry('admin', './assets/admin/entry.tsx')

    // Split shared vendor code into a runtime chunk
    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Babel: core-js polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // ts-loader handles .ts/.tsx; enableReactPreset adds @babel/preset-react for JSX
    .enableTypeScriptLoader((config) => {
        config.transpileOnly = true;
    })
    .enableReactPreset()

    // PostCSS (Tailwind runs through here)
    .enablePostCssLoader()

    // Path alias: @/ → assets/
    .addAliases({
        '@': path.resolve(__dirname, 'assets'),
    })
;

module.exports = Encore.getWebpackConfig();