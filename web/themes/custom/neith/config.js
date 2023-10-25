module.exports = {
  "styles": {
    "srcPath": "src/styles",
    "srcFiles": "src/styles/**/*",
    "srcMainFiles": ["src/styles/*.scss", "!src/styles/_*.scss"],
    "dest": "dist/css"
  },

  "js": {
    "srcFiles": [
      "src/js/utils.js",
      "src/js/popup.js",
      "src/js/popover.js",
      "src/js/fixable.js",
      "src/js/mobileCollapse.js",
      "src/js/enable-tab.js",
      "src/js/toggle-text.js",
      "src/js/share.js",
      "src/js/infinite-scroll-cache.js",
      "src/js/initParticles.js",
      "src/js/js-counter.js",
      'src/js/data-href-link-alter.js',
      'src/js/anchor-scroller.js',
      'src/js/carousel-fade-scroller.js',
    ],
    "vendorFiles": [
      'src/js/vendor/snapback_cache/snapback_cache.js',
      'node_modules/tether/dist/js/tether.min.js',
      'node_modules/popper.js/dist/umd/popper.min.js',
      'node_modules/bootstrap-last/dist/js/bootstrap.min.js',
      "node_modules/particles.js/particles.js",
      'node_modules/magnific-popup/dist/jquery.magnific-popup.min.js',
      'node_modules/slick-carousel/slick/slick.js'
    ],
    "dest": "dist/js"
  },

  "styleguide": {
    "srcFiles": "doc_assets/**/*",
    "styles": {
      "srcFiles": "src/styleguide/themes/cortana-theme/sass/cortana.scss",
      "dest": "src/styleguide/themes/cortana-theme/doc_assets/css"
    }
  },

  "mockups": {
    "src": "src/mockups",
    "dest": "dist/mockups"
  },

  "icons": {
    "src": "src/icons/*",
    "dest": "dist/icons"
  },

  "images": {
    "src": "src/images/*",
    "dest": "dist/images"
  },

  "server": {
    "baseDir": "dist"
  }
};
