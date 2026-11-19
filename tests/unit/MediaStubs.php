<?php
/**
 * WordPress media functions instagrate_to_wordpress::attach_image() calls.
 *
 * Tests set Media_Stubs::$download_result to what download_url() returns (a
 * temp file path or a WP_Error), and read the calls made to
 * media_handle_sideload() and wp_delete_file(). Call Media_Stubs::reset() in
 * setUp().
 *
 * attach_image() requires wp-admin/includes/{image,file,media}.php from
 * ABSPATH, so empty copies of those sit in tests/unit/wp-admin/includes/.
 */
class Media_Stubs {

	/** @var string|WP_Error What download_url() returns. */
	public static $download_result = '';

	/** @var array The arguments of each media_handle_sideload() call. */
	public static $sideloads = array();

	/** @var int|WP_Error What media_handle_sideload() returns. */
	public static $sideload_result = 100;

	/** @var array The argument of each wp_delete_file() call. */
	public static $deleted = array();

	public static function reset() {
		self::$download_result = '';
		self::$sideloads       = array();
		self::$sideload_result = 100;
		self::$deleted         = array();
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {

		private $code;

		private $message;

		public function __construct( $code = '', $message = '' ) {
			$this->code    = $code;
			$this->message = $message;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! function_exists( 'download_url' ) ) {
	function download_url( $url ) {
		return Media_Stubs::$download_result;
	}
}

if ( ! function_exists( 'media_handle_sideload' ) ) {
	function media_handle_sideload( $file_array, $post_id = 0 ) {
		Media_Stubs::$sideloads[] = array( $file_array, $post_id );

		return Media_Stubs::$sideload_result;
	}
}

if ( ! function_exists( 'wp_delete_file' ) ) {
	function wp_delete_file( $file ) {
		Media_Stubs::$deleted[] = $file;

		// Like core: on PHP 8 a WP_Error here throws a TypeError the @ doesn't stop.
		@unlink( $file );
	}
}
