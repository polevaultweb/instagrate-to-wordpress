<?php

class itw_Instagram {

	private $apiUrl = 'https://api.instagram.com/v1/';

	protected $client_id = '179980706756164';
	protected $redirect_uri = 'https://oauth.polevaultweb.com/v2/';
	protected $access_token;

	/**
	 * @var Polevaultweb\IntagrateLite\WPOAuth2\WPOAuth2
	 */
	protected static $wpoauth;

	protected static $http;

	public function __construct( $access_token = '' ) {
		$this->access_token = $access_token;
	}

	public static function http() {
		if ( empty( self::$http ) ) {
			self::$http = new Instagrate_Lite_Http();
		}

		return self::$http;
	}

	public static function load_admin() {
		add_filter( 'pvw_wp_oauth2_provider_display_name', get_class() . '::pvw_wp_oauth2_provider_display_name' );
		self::$wpoauth = Polevaultweb\IntagrateLite\WPOAuth2\WPOAuth2::instance( 'https://oauth.polevaultweb.com/v2/', Intagrate_Lite_Instagram_Access_Token::class );
		self::$wpoauth->register_admin_handler(  ITW_RETURN_URI );
	}

	public static function pvw_wp_oauth2_provider_display_name() {
		return 'Instagram';
	}

	public function authorizeUrl( $redirect_uri ) {
		return self::$wpoauth->get_authorize_url( 'instagram-facebook', $this->client_id, $redirect_uri, array( 'scope' => 'user_profile,user_media' ) );
	}

	public static function logout_url() {
		return self::$wpoauth->get_disconnect_url( 'instagram-facebook', ITW_RETURN_URI );
	}

	public function get_access_token( $account_id ) {
		$account_settings = get_post_meta( $account_id, '_instagrate_pro_settings', true );
		if ( empty( $account_settings ) || empty( $account_settings['token'] ) ) {
			return false;
		}

		if ( isset( $account_settings['token_expires'] ) && ( time() - HOUR_IN_SECONDS ) < $account_settings['token_expires'] ) {
			return $account_settings['token'];
		}

		global $igp_account_id;
		$igp_account_id = $account_id;

		$new_token = $this->wpoauth->refresh_access_token( $this->get_client_id(), 'instagram-facebook' );

		return $new_token;
	}

	/**
	 * Get Instagram User
	 *
	 * @param $access
	 * @param $user_id
	 *
	 * @return string|object
	 */
	public function get_user( $access, $user_id ) {
		$url  = $user_id . '/';
		$data = $this->http()->do_http_request( $access, $url, array('fields'=> 'id,username') );
		if ( ! $data ) {
			return '';
		}

		return $data;
	}

	/**
	 * Get Instagram User
	 *
	 * @param $access
	 * @param $user_id
	 *
	 * @return string|object
	 */
	public function get_user_media( $access, $user_id ) {
		$url  = $user_id . '/media';
		$data = $this->http()->do_http_request( $access, $url, array('fields'=> 'id,media_type,media_url,thumbnail_url,timestamp,username,children,caption,permalink' ) );
		if ( ! $data ) {
			return '';
		}

		return $data;
	}

	/**
	 * Get Instagram media
	 *
	 * @param      $access
	 * @param      $media_id
	 * @param bool $is_child
	 *
	 * @return string
	 */
	function get_media( $access, $media_id, $is_child = false ) {
		$url    = $media_id . '/';
		$fields = 'id,media_type,media_url,thumbnail_url,timestamp';
		if ( ! $is_child ) {
			$fields .= ',username,children,caption,permalink';
		}
		$data = $this->http()->do_http_request( $access, $url, array( 'fields' => $fields ) );
		if ( ! $data ) {
			return false;
		}

		return $data;
	}
}

class InstagramApiError extends Exception {

}


function itw_curPageURL() {

	$pageURL = 'http';
	if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ( $_SERVER["SERVER_PORT"] != "80" && $_SERVER['HTTP_HOST'] != 'localhost:8888' ) {
		$pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	}

	return strtolower( $pageURL );
}

function itw_adminOptionsURL( $url ) {

	$pageURL = substr( $url, 0, strrpos( $url, "/wp-content" ) );


	return $pageURL . '/wp-admin/options-general.php?page=instagratetowordpress';
}

function itw_pluginsURL() {

	$pageURL = 'http';
	if ( $_SERVER["HTTPS"] == "on" ) {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ( $_SERVER["SERVER_PORT"] != "80" ) {
		$pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"];
	} else {
		$pageURL .= $_SERVER["HTTP_HOST"];
	}

	return $pageURL . '/wp-admin/plugins.php';
}

function itw_truncateString( $str, $max, $rep = '...' ) {
	if ( strlen( $str ) > $max ) {
		$leave = $max - strlen( $rep );

		return substr_replace( $str, $rep, $leave );
	} else {
		return $str;
	}
}