const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    //.addEntry('appjs', './assets/js/app.js')
    .enableSingleRuntimeChunk()
    .addStyleEntry('app', './assets/styles/app.css')
    .enablePostCssLoader()

    // Activez les sourcemaps
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();



