'use strict';

var gulp = require('gulp'),
    browserSync = require('browser-sync'),
    runSequence = require('run-sequence'),
    requireDir = require('require-dir')('./gulp_tasks');

var $ = require('gulp-load-plugins')();

gulp.task('mockups', [
  'mockups:build',
  'browsersync'
]);

gulp.task('watch', [
  'styles:watch',
  'js:watch',
  'images:watch',
  'mockups:watch'
]);

gulp.task('default', function(done) {
  runSequence(
    ['build', 'mockups'],
    'watch',
    done);
});

gulp.task('build', function(done) {
  runSequence(
    ['images', 'styles', 'js'],
    done);
});
