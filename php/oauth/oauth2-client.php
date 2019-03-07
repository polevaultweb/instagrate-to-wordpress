<?php

namespace Polevaultweb\IG\WP_OAuth2;

class OAuth2_Client {

	protected $authorization_url;
	protected $client_key;
	protected $redirect_uri = 'https://oauth.polevaultweb.com/';

	public function __construct( $client_key ) {
		$this->client_key = $client_key;
	}

	public static function get_method() {
		$methods = openssl_get_cipher_methods();

		return $methods[0];
	}

	protected function get_key() {
		$key = wp_generate_password();

		set_site_transient( 'wp-oauth2-key', $key );

		return $key;
	}

	public function get_authorize_url( $callback_url, $args = array() ) {
		$data = array(
			'redirect'   => $callback_url,
			'client_key' => $this->client_key,
			'key'        => $this->get_key(),
			'method'     => self::get_method(),
		);

		$defaults = array(
			'response_type' => 'code',
			'client_id'     => $this->client_key,
			'redirect_uri'  => $this->redirect_uri,
			'state'         => base64_encode( serialize( $data ) ),
		);

		$args = array_merge( $defaults, $args );

		$url = $this->authorization_url . '?' . http_build_query( $args, '', '&' );

		return $url;
	}
}
