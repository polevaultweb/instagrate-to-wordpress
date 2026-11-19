<?php
/**
 * WordPress functions the plugin calls while it runs rather than as it loads:
 * options, the current user's capabilities, translation, escaping and cron. Tests set
 * WP_Stubs::$options and WP_Stubs::$capabilities, and call WP_Stubs::reset().
 */
class WP_Stubs {

	/** @var array Options get_option() returns, name => value. */
	public static $options = array();

	/** @var string[] Capabilities current_user_can() grants. */
	public static $capabilities = array();

	/** @var array Callbacks add_action() added, hook => callbacks. reset() keeps them, as the plugin adds them as it loads. */
	public static $hooks = array();

	/** @var array Scheduled cron events, as array( 'timestamp', 'recurrence', 'hook' ). */
	public static $cron_events = array();

	public static function reset() {
		self::$options      = array();
		self::$capabilities = array();
		self::$cron_events  = array();
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

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $option ) {
		unset( WP_Stubs::$options[ $option ] );

		return true;
	}
}

if ( ! function_exists( 'remove_action' ) ) {
	function remove_action( $hook, $callback, $priority = 10 ) {
		return true;
	}
}

if ( ! function_exists( 'wp_next_scheduled' ) ) {
	function wp_next_scheduled( $hook, $args = array() ) {
		foreach ( WP_Stubs::$cron_events as $event ) {
			if ( $event['hook'] === $hook ) {
				return $event['timestamp'];
			}
		}

		return false;
	}
}

if ( ! function_exists( 'wp_schedule_event' ) ) {
	function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
		WP_Stubs::$cron_events[] = array(
			'timestamp'  => $timestamp,
			'recurrence' => $recurrence,
			'hook'       => $hook,
		);

		return true;
	}
}

if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
	function wp_clear_scheduled_hook( $hook, $args = array() ) {
		$cleared = 0;
		foreach ( WP_Stubs::$cron_events as $key => $event ) {
			if ( $event['hook'] === $hook ) {
				unset( WP_Stubs::$cron_events[ $key ] );
				$cleared++;
			}
		}

		return $cleared;
	}
}
