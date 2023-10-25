var gulp = require('gulp'),
    $ = require('gulp-load-plugins')(),
    browserSync = require('browser-sync'),
    reload = browserSync.reload,
    config = require('../config.js');

gulp.task('js', ['js:src', 'js:vendor']);

gulp.task('js:src', function() {
  return gulp.src(config.js.srcFiles)
    .pipe($.concat('app.js'))
    .pipe(gulp.dest(config.js.dest));
});

gulp.task('js:vendor', function() {
  return gulp.src(config.js.vendorFiles)
    .pipe($.concat('vendor.js'))
    .pipe(gulp.dest(config.js.dest));
});

gulp.task('js:watch', function () {
  gulp.watch(config.js.vendorFiles, ['js:vendor', reload]);
  gulp.watch(config.js.srcFiles, ['js:src', reload]);
});
