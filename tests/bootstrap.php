<?php
require dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );
$_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit';
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__ ) . '/wp/wp-content/plugins/juanto-core/juanto-core.php';
	}
);
tests_add_filter(
	'setup_theme',
	static function () {
		register_theme_directory( dirname( __DIR__ ) . '/wp/wp-content/themes' );
		switch_theme( 'juanto' );
	}
);
require $_tests_dir . '/includes/bootstrap.php';
