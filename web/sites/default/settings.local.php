<?php

// @codingStandardsIgnoreFile

$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

/**
* Show all error messages, with backtrace information.
*
* In case the error level could not be fetched from the database, as for
* example the database connection failed, we rely only on this value.
*/
$config['system.logging']['error_level'] = 'verbose';

/**
* Disable CSS and JS aggregation.
*/
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

/**
* Disable the render cache.
*
* Note: you should test with the render cache enabled, to ensure the correct
* cacheability metadata is present. However, in the early stages of
* development, you may want to disable it.
*
* This setting disables the render cache by using the Null cache back-end
* defined by the development.services.yml file above.
*
* Only use this setting once the site has been installed.
*/
$settings['cache']['bins']['render'] = 'cache.backend.null';

/**
* Disable caching for migrations.
*
* Uncomment the code below to only store migrations in memory and not in the
* database. This makes it easier to develop custom migrations.
*/
# $settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';

/**
* Disable Internal Page Cache.
*
* Note: you should test with Internal Page Cache enabled, to ensure the correct
* cacheability metadata is present. However, in the early stages of
* development, you may want to disable it.
*
* This setting disables the page cache by using the Null cache back-end
* defined by the development.services.yml file above.
*
* Only use this setting once the site has been installed.
*/
$settings['cache']['bins']['page'] = 'cache.backend.null';

/**
* Disable Dynamic Page Cache.
*
* Note: you should test with Dynamic Page Cache enabled, to ensure the correct
* cacheability metadata is present (and hence the expected behavior). However,
* in the early stages of development, you may want to disable it.
*/
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
