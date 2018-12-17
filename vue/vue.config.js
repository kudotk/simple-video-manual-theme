'use strict'
const path = require('path')
const merge = require('webpack-merge')
const webpack = require('webpack')
const chalk = require('chalk')

const mode = process.argv[1 + process.argv.indexOf('--mode')]
if (!mode) {
  console.log(chalk.yellow('  need --mode argument.\n'))
  return
}

/**
 * Base config.
 */
const baseConfig = {
  baseUrl: '',
  filenameHashing: false,
  productionSourceMap: (process.env.NODE_ENV !== 'production'),
  lintOnSave: (process.env.NODE_ENV !== 'production'),
  css: {
    // add options to .vue, .scss... style-loader.
    loaderOptions: {
      sass: {
        // add: For using material components in .vue file.
        // the statement '@import "@~"' exists in material node_modules.
        includePaths: [path.resolve(__dirname, 'node_modules')]
      }
    },
    sourceMap: (process.env.NODE_ENV !== 'production'),
    extract: true
  },
  chainWebpack: config => {
    // set setting only when lintOnSave is true.
    if (process.env.NODE_ENV !== 'production') {
      config.module
        .rule('eslint')
        .use('eslint-loader')
        .tap(options => {
          if (mode.match(/^admin/)) {
            // admin view uses jQuery, underscore.js, wp in WordPress.
            options.globals = [
              'jQuery',
              // underscore.js
              '_',
              // wordpress plugin values
              'wp'
            ]
          }
          return options
        })
    }
  },
}

/**
 * User view Webpack config
 */
const userConfig = merge(baseConfig, {
  outputDir: 'dist/user',

  // merge webpack config
  configureWebpack: config => {
    config.entry = {
      app: [
        './src/user/main.js'
      ]
    }
  },
  chainWebpack: config => {
    config
      .plugin('provide')
      .use(
        new webpack.ProvidePlugin({
          // Include jQuery.
          $: 'jquery',
          jQuery: 'jquery'
        })
      )
  }
})
const userStaticConfig = merge(userConfig, {
  outputDir: 'dist/user-static',
})

/**
 * Admin view Webpack config
 */
const adminConfig = merge(baseConfig, {
  outputDir: 'dist/admin',

  // merge webpack config
  configureWebpack: config => {
    config.entry = {
      app: [
        './src/admin/main.js'
      ]
    }
  },
  chainWebpack: config => {
    // @see https://github.com/vuejs/vue-cli/issues/1478
    config.plugins.delete('html')
    config.plugins.delete('preload')
    config.plugins.delete('prefetch')
    config.plugin('copy').tap(options => {
      options[0][0].ignore.push('*.ico')
      return options
    })
  }
})

if (mode === 'user' || mode === 'user-serve') {
  module.exports = userConfig
} else if (mode === 'user-static') {
  module.exports = userStaticConfig
} else if (mode === 'admin') {
  module.exports = adminConfig
} 
