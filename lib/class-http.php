<?php

class Instagrate_Lite_Http {

	public $api_base;
	public $http_timeout    = 60;
	public $http_user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

	function __construct() {
		$this->api_base = 'https://graph.instagram.com/';
	}

	/**
	 * Main HTTP request
	 *
	 * @param        $access
	 * @param        $url
	 * @param string $params
	 * @param string $full_url
	 *
	 * @return array|bool|mixed
	 */
	public function do_http_request( $access, $url, $params = '', $full_url = '' ) {
		if ( $full_url == '' ) {
			$url = $this->api_base . $url;
			$url = $url . '?access_token=' . $access;
			$url = $url . $this->encode_params( $params );
		} else {
			$url = $full_url;
		}
		$contents = wp_remote_get(
			$url,
			array(
				'sslverify'  => false,
				'user-agent' => $this->http_user_agent,
				'timeout'    => $this->http_timeout,
			)
		);

		if ( defined( 'INTAGRATE_LITE_DEBUG' ) && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( print_r( wp_remote_retrieve_body( $contents ), true ) );
		}
		if ( is_wp_error( $contents ) ) {
			return false;
		}
		if ( 200 == $contents['response']['code'] ) {
			if ( is_wp_error( $contents ) || ! isset( $contents['body'] ) ) {
				return false;
			}
			$contents = $contents['body'];
			if ( $contents == '' ) {
				return false;
			}
			if ( empty( $contents ) ) {
				return false;
			}
			$data = json_decode( $contents );

			return $data;
		}

		$body = json_decode( wp_remote_retrieve_body( $contents ) );
		if ( $body && isset( $body->error ) ) {
			throw new InstagramApiError( $body->error->message, $body->error->code );
		}

		return false;
	}

	/**
	 * Encode url parameters
	 *
	 * @param $params
	 *
	 * @return string
	 */
	private function encode_params( $params ) {
		$postdata = '';
		if ( empty( $params ) ) {
			return $postdata;
		}
		foreach ( $params as $key => $value ) {
			$postdata .= '&' . $key . '=' . urlencode( $value );
		}

		return $postdata;
	}
}
