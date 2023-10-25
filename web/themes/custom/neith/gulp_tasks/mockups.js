'use strict';

var
  gulp = require('gulp'),
  config = require('../config.js'),
  mockupsPath = config.mockups.src;

gulp.task('mockups:build', function(done) {
  var
    spawn = require('child_process').spawn,
    nn = require('node-notifier');

  var cmd = (process.platform === "win32" ? "jekyll.bat" : "jekyll"),

  jekyll = spawn(cmd, ['build'], {
      stdio: 'inherit',
      cwd: mockupsPath
  });

  jekyll.on('close', function(code) {
    if (code && code > 0) {
      nn.notify({
        title: "Jekyll",
        message: "Error de compilación, revisa la consola"
      });
      return done('Error al ejecutar jekyll');
    }
    return done();
  });
});

gulp.task('mockups:watch', false, function() {
  var browserSync = require('browser-sync'),
      reload = browserSync.reload;
  gulp.watch([config.mockups.src + "/**/*.html"], ['mockups', reload]);
});
