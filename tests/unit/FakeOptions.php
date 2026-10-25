<?php
/**
 * Stands in for the WordPress options API, backed by Fake_Options::$options.
 * Values come back as they were saved, so tests can check what was stored.
 */
class Fake_Options {

	/** @var array Saved options, by name. */
	public static $options = array();
}

function get_option( $name, $default = false ) {
	return array_key_exists( $name, Fake_Options::$options ) ? Fake_Options::$options[ $name ] : $default;
}

function update_option( $name, $value ) {
	Fake_Options::$options[ $name ] = $value;

	return true;
}
