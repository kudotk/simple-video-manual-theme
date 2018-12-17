'use strict'
const ora = require('ora')
const rm = require('rimraf')
const path = require('path')
const fs = require('fs-extra')
const glob = require('glob')


/**
 * Copy built files to WordPress theme directory.
 */

if (process.argv.indexOf('--mode') === -1) {
  throw new Error('need --mode argument.')
}

const mode = process.argv[1 + process.argv.indexOf('--mode')]

const spinner = ora('Copying to WordPress...')
spinner.start()

let outputDir = '../WordPress/themes/simplevideomanual/assets/admin'
if (mode === 'user') {
  outputDir = '../WordPress/themes/simplevideomanual/assets/user'
} else if (mode === 'user-static') {
  outputDir = '../WordPress/themes/simplevideomanual/admin/static-page-template'
}

// destination absolute path
const wordpressDistDir = path.resolve(__dirname, outputDir)

// clean up at first.
rm(wordpressDistDir, err => {
  if (err) throw err

  const vueConfig = require('./vue.config')
  const vueSrcDir = path.resolve(__dirname, vueConfig.outputDir)
  
  const copyWp = (pattern) => {
    const assetsDir = vueConfig.assetsDir || ''
    const srcAssetsDir = path.resolve(vueSrcDir, assetsDir)
    
    glob.sync(srcAssetsDir + '/**/*').forEach((srcPath) => {
      const srcName = srcPath.replace(srcAssetsDir + '/', '')
      const m = srcName.match(new RegExp(pattern))
      if (!m) {
        return
      }
      // remove hash
      const dstName = m[1] + (m[2] ? '.' + m[2] : '') + '.' + m[3]
      const dstAssetsDir = path.resolve(wordpressDistDir, assetsDir)
      if (!fs.existsSync(dstAssetsDir)) {
        fs.mkdirSync(
          dstAssetsDir,
          {
            recursive: true
          }
        )
      }
      fs.copySync(
        srcPath,
        path.resolve(dstAssetsDir, dstName),
        {
          overwrite: true,
          preserveTimestamps: true
        }
      )
    })
  }
  copyWp('(.*)(\\.[a-z0-9]+)?\\.(js|js\\.map)$')
  copyWp('(.*)(\\.[a-z0-9]+)?\\.(css|css\\.map)$')
  copyWp('(.*)(\\.[a-z0-9]+)?\\.(eot|ttf|woff|woff2)$')

  // copy for the feature that exports static files.
  if (mode === 'user-static') {
    const fileList = ['index.html', 'favicon.ico']
    fileList.forEach((name) => {
      fs.copySync(
        path.resolve(vueSrcDir, name),
        path.resolve(wordpressDistDir, name),
        {
          overwrite: true,
          preserveTimestamps: true
        }
      )
    })
  }

  spinner.succeed()
})
