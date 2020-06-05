// utils
const browserSync = require("browser-sync")
const rimraf = require("rimraf")

// gulp
const gulp = require("gulp")
const babel = require("gulp-babel")
const fileinclude = require("gulp-file-include")
const gulpif = require("gulp-if")
const imagemin = require("gulp-imagemin")
const plumber = require("gulp-plumber")
const prettier = require("gulp-prettier")
const pug = require("gulp-pug")
const replace = require("gulp-replace")
const sass = require("gulp-sass")
const sourcemaps = require("gulp-sourcemaps")
const uglify = require("gulp-uglify")
const packageImporter = require("node-sass-package-importer")

// variable
const outputDir = {release: "release", debug: "debug"}
const workDir = {release: "tmp/release", debug: "tmp/debug"}
const distDir = "../docs"


// ------------------------------------
// sub task

// key
const envKey = function (isRelease) {
  const key = isRelease ? "release" : "debug"
  return key
}

// clean
gulp.task("clean", function () {
  rimraf("./tmp", function () {
  })
  rimraf("./debug", function () {
  })
  rimraf("./release", function () {
  })
})

// output js
const outputJs = function (isRelease) {
  // http://babeljs.io/en/setup/#installation
  return gulp
    .src(["src/**/*.js"])
    .pipe(gulpif(!isRelease, plumber()))
    .pipe(prettier())
    .pipe(gulpif(!isRelease, sourcemaps.init()))
    .pipe(babel())
    .pipe(uglify({preserveComments: "license"}))
    .pipe(gulpif(!isRelease, sourcemaps.write()))
    .pipe(gulp.dest(workDir[envKey(isRelease)]))
}
gulp.task("js", function () {
  return outputJs(true)
})
gulp.task("jsDebug", function () {
  return outputJs(false)
})

// output css
const outputCss = function (isRelease) {
  return gulp
    .src(["src/**/*.scss", "src/**/_*.scss"])
    .pipe(gulpif(!isRelease, plumber()))
    .pipe(gulpif(!isRelease, sourcemaps.init()))
    .pipe(
      sass(
        {
          outputStyle: isRelease ? "compressed" : "nested",
          importer: packageImporter({
            extensions: [".scss", ".css"]
          })
        }
      ).on(
        "error",
        sass.logError
      )
    )
    .pipe(replace(/@charset "UTF-8";/g, ""))
    .pipe(gulpif(!isRelease, sourcemaps.write()))
    .pipe(gulp.dest(workDir[envKey(isRelease)] + "/"))
}
gulp.task("css", function () {
  return outputCss(true)
})
gulp.task("cssDebug", function () {
  return outputCss(false)
})

// compress image
const compressImage = function (isRelease) {
  return gulp
    .src(["src/**/*.+(png|gif|jpg|jpeg|svg)"])
    .pipe(gulpif(!isRelease, plumber()))
    .pipe(
      gulpif(
        isRelease,
        imagemin({
          progressive: true
        })
      )
    )
    .pipe(gulp.dest(outputDir[envKey(isRelease)] + "/"))
}
gulp.task("image", function () {
  return compressImage(true)
})
gulp.task("imageDebug", function () {
  return compressImage(false)
})

// compile pug
const compilePug = function (isRelease) {
  return gulp
    .src(["src/**/*.pug", "!src/_*/**/*"])
    .pipe(gulpif(!isRelease, plumber()))
    .pipe(
      gulpif(
        !isRelease,
        plumber()
      )
    )
    .pipe(
      pug({
        // not minify in this task, for checking indented html.
        pretty: true
      })
    )
    .pipe(
      fileinclude({
        prefix: "@@",
        basepath: workDir[envKey(isRelease)] + "/"
      })
    )
    .pipe(gulp.dest(workDir[envKey(isRelease)] + "/"))
}
gulp.task("pug", function () {
  return compilePug(true)
})
gulp.task("pugDebug", function () {
  return compilePug(false)
})

// static resources
const copyStatic = function (isRelease) {
  return gulp
    .src(["static/**/*"])
    .pipe(gulpif(!isRelease, plumber()))
    .pipe(gulp.dest(workDir[envKey(isRelease)] + "/"))
}
gulp.task("static", function () {
  return copyStatic(true)
})
gulp.task("staticDebug", function () {
  return copyStatic(false)
})

// browser-sync.
const startBrowserSync = function (isRelease) {
  return browserSync.init({
    server: {
      baseDir: workDir[envKey(isRelease)],
      index: "index.html"
    }
  })
}
gulp.task("browserSync", function () {
  return startBrowserSync(true)
})
gulp.task("browserSyncDebug", function () {
  return startBrowserSync(false)
})


// ------------------------------------
// main task

// build release
gulp.task("build", 
  gulp.series(gulp.parallel("js", "css", "image", "static"),
  gulp.series("pug"),
  function (done) {
    rimraf("./" + distDir, function () {
      gulp
        .src([workDir[envKey(true)] + "/**/*", "!" + workDir[envKey(true)] + "/css/**"])
        .pipe(gulp.dest(distDir + "/"))
    })
    done()
  })
)

// build dev
gulp.task("debug",
  gulp.series(
    gulp.parallel("jsDebug", "cssDebug", "imageDebug", "staticDebug"),
    gulp.series("pugDebug")
  )
)

// watch
gulp.task("watch",
  gulp.series(
    gulp.parallel("jsDebug", "cssDebug", "imageDebug", "staticDebug"),
    gulp.series("pugDebug"),
    gulp.parallel("browserSyncDebug", function () {
      gulp.task("watchJs", gulp.series("jsDebug", "pugDebug"), function (done) {
        browserSync.reload()
        done()
      })
      gulp.watch(["src/**/*.js"], gulp.series("watchJs"))

      gulp.task("watchCss", gulp.series("cssDebug", "pugDebug", function (done) {
        browserSync.reload()
        done()
      }))
      gulp.watch(["src/**/*.scss"], gulp.series("watchCss"))

      gulp.task("watchPug", gulp.series("pugDebug"), function (done) {
        browserSync.reload()
        done()
      })
      gulp.watch(["src/**/*.pug"], gulp.series("watchPug"))

      gulp.task("watchImage", gulp.series("imageDebug"), function (done) {
        browserSync.reload()
        done()
      })
      gulp.watch(["src/**/*.+(png|gif|jpg|jpeg|svg)"], gulp.series("watchImage"))

      gulp.watch(["static/**/*"], gulp.series("staticDebug"))
    }
  )
  )
)

// default
gulp.task("default", gulp.series("watch"), function (done) {
  done();
})
