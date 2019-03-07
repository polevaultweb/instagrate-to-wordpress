<?php

class itw_Instagram {

	private $apiUrl = 'https://api.instagram.com/v1/';

	protected $client_id = '483189bb620d4cfb8cd13b5a15e9f3d4';
	protected $access_token;

	public function __construct( $access_token = '' ) {
		$this->access_token = $access_token;
	}

	public static function load_admin() {
		$admin_handler = new \Polevaultweb\IG\WP_OAuth2\Admin_Handler( ITW_RETURN_URI );
		$admin_handler->init();
	}

	public function authorizeUrl( $redirect_uri ) {
		$oauth = new \Polevaultweb\IG\WP_OAuth2\Instagram_Client( $this->client_id );

		return $oauth->get_authorize_url( $redirect_uri, array( 'scope' => 'basic' ) );
	}

	public static function logout_url() {
		return Polevaultweb\IG\WP_OAuth2\WP_OAuth2::get_disconnect_url( 'instagram', ITW_RETURN_URI );
	}

	private function urlEncodeParams( $params ) {
		$postdata = '';
		if ( ! empty( $params ) ) {
			foreach ( $params as $key => $value ) {
				$postdata .= '&' . $key . '=' . urlencode( $value );
			}
		}

		return $postdata;
	}

	public function http( $url, $params, $method ) {
		$c = curl_init();


		// If they are authenticated and there is a access token passed, send it along with the request
		// If the access token is invalid, an error will be raised upon the request
		if ( $this->access_token ) {
			$url = $url . '?access_token=' . $this->access_token;
		}


		// If the request is a GET and we need to pass along more params, "URL Encode" them.
		if ( $method == 'GET' ) {
			$url = $url . $this->urlEncodeParams( $params );

		}

		curl_setopt( $c, CURLOPT_URL, $url );

		if ( $method == 'POST' ) {

			//var_dump( $params);
			curl_setopt( $c, CURLOPT_POST, true );
			curl_setopt( $c, CURLOPT_POSTFIELDS, $params );
		}

		if ( $method == 'DELETE' ) {
			curl_setopt( $c, CURLOPT_CUSTOMREQUEST, 'DELETE' );
		}

		// Withtout the next line I get cURL errors
		curl_setopt( $c, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $c, CURLOPT_SSL_VERIFYHOST, 2 );    // 2 is the default so this is not required

		curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );


		$r = json_decode( curl_exec( $c ) );

		//check for NULL response
		if ( $r == null ) {

			throw new InstagramApiError( 'Error: Instagram Servers Down' );

		}

		// Throw an error if maybe an access token expired or wasn't right
		// or if an ID doesn't exist or something
		if ( isset( $r->meta->error_type ) ) {
			throw new InstagramApiError( 'Error: ' . $r->meta->error_message );
		}

		return $r;

		// close cURL resource, and free up system resources
		curl_close( $c );
	}

	// Giving you some easy functions (get, post, delete)
	public function get( $endpoint, $params = array(), $method = 'GET' ) {
		return $this->http( $this->apiUrl . $endpoint, $params, $method );
	}

	public function post( $endpoint, $params = array(), $method = 'POST' ) {
		return $this->http( $this->apiUrl . $endpoint, $params, $method );
	}

	public function delete( $endpoint, $params = array(), $method = 'DELETE' ) {
		return $this->http( $this->apiUrl . $endpoint, $params, $method );
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