const Encore = require('@symfony/webpack-encore');
const path = require('path');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.setOutputPath('public/build')
    .setPublicPath('/build')
    .addEntry('main', './assets/main/js/base.js')

    .enableStimulusBridge('./assets/controllers.json')

    .splitEntryChunks()

    .enableSingleRuntimeChunk().configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
})

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    }).cleanupOutputBeforeBuild().enableSourceMaps(!Encore.isProduction()).enableVersioning(Encore.isProduction()).enableSassLoader(function (options) {
    // options.includePaths = [...]
    options.sassOptions.sourceComments = false;
    options.sassOptions.outputStyle = 'compressed';
}, {
    resolveUrlLoader: false,
}).addAliases({
    '#': path.resolve(__dirname, 'assets/main'),
    '#images': path.resolve(__dirname, 'assets/main/img'),
    '#node_modules': path.resolve(__dirname, 'node_modules'),
});

Encore.copyFiles({
    from: './assets/main/img',
    to: 'images/[path][name].[hash:8].[ext]',
    pattern: /\.(png|jpg|jpeg|gif|ico|svg|webp)$/,
});

const defaultConfig = Encore.getWebpackConfig();
defaultConfig.name = 'mainConfig';
defaultConfig.experiments = {
    topLevelAwait: true,
};

module.exports = defaultConfig;
