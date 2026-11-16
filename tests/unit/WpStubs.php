<?php
/**
 * WordPress functions the plugin calls while it runs rather than as it loads:
 * options, the current user's capabilities, translation and escaping. Tests set
 * WP_Stubs::$options and WP_Stubs::$capabilities, and call WP_Stubs::reset().
 */
class WP_Stubs {

	/** @var array Options get_option() returns, name => value. */
	public static $options = array();

	/** @var string[] Capabilities current_user_can() grants. */
	public static $capabilities = array();

	public static function reset() {
		self::$options      = array();
		self::$capabilities = array();
	}
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default_value = false ) {
		return array_key_exists( $option, WP_Stubs::$options ) ? WP_Stubs::$options[ $option ] : $default_value;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value, $autoload = null ) {
		WP_Stubs::$options[ $option ] = $value;

		return true;
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	function current_user_can( $capability, ...$args ) {
		return in_array( $capability, WP_Stubs::$capabilities, true );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return esc_html( $text );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		// Like core, ampersands come out as &#038; whether or not they were already encoded
		return str_replace( array( '&', '"', "'" ), array( '&#038;', '&quot;', '&#039;' ), str_replace( '&amp;', '&', (string) $url ) );
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	function date_i18n( $format, $timestamp = false ) {
		return gmdate( $format, (int) $timestamp );
	}
}
