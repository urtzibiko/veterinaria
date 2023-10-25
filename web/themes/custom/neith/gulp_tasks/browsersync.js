'use strict';

var
  gulp = require('gulp'),
  config = require('../config.js');

const browserSync = require('browser-sync');

gulp.task('browsersync', function() {
  browserSync({
    notify: true,
    port: 9000,
    online: false,
    open: true,
    server: {
      baseDir: [config.server.baseDir]
    },
    startPath: "/mockups"
  });
});
