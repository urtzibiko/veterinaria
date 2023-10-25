var gulp = require('gulp');
var imagemin = require('gulp-imagemin');
var browserSync = require('browser-sync');
var reload = browserSync.reload;
var config = require('../config.js');

gulp.task('images', function() {
  return gulp.src(config.images.src)
      .pipe(imagemin())
      .pipe(gulp.dest(config.images.dest));
});

gulp.task('images:watch', function() {
  gulp.watch(config.images.src, ['images'], reload);
});
