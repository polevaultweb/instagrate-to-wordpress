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

// On PHP 8.3, get_class() with no argument, used where the plugin registers its
// hooks, is deprecated. These tests don't cover it, so keep it out of the output.
// Deprecations while the tests run still fail them (phpunit.xml).
$error_reporting = error_reporting( E_ALL & ~E_DEPRECATED );
require_once dirname( __DIR__, 2 ) . '/instagrate-to-wordpress.php';
error_reporting( $error_reporting );
