<?php
/**
 * Loads instagrate-to-wordpress.php without WordPress.
 *
 * Stubs only the WordPress functions the plugin calls as it loads (constants,
 * hooks), and sets a $wpdb whose postmeta count tests control through
 * Fake_Wpdb::$posted. The plugin requires vendor/autoload.php, so run
 * `composer install` first.
 */

define( 'ABSPATH', __DIR__ . '/' );

function plugin_dir_path( $file ) {
	return dirname( $file ) . '/';
}

function plugin_dir_url( $file ) {
	return 'https://example.test/wp-content/plugins/instagrate-to-wordpress/';
}

function plugin_basename( $file ) {
	return 'instagrate-to-wordpress/' . basename( $file );
}

function admin_url( $path = '' ) {
	return 'https://example.test/wp-admin/' . ltrim( $path, '/' );
}

function add_action() {
}

function add_filter() {
}

function register_uninstall_hook() {
}

require __DIR__ . '/FakeInstagram.php';
require __DIR__ . '/FakeWpdb.php';

$GLOBALS['wpdb'] = new Fake_Wpdb();

// Stop the run if loading the plugin raises any error, deprecations included:
// phpunit.xml only turns them into failures while the tests run.
set_error_handler(
	function ( $errno, $errstr, $errfile, $errline ) {
		throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
	}
);
require_once dirname( __DIR__, 2 ) . '/instagrate-to-wordpress.php';
restore_error_handler();
