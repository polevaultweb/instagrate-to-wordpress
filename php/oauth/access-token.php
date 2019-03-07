<?php

namespace Polevaultweb\IG\WP_OAuth2;

class Access_Token {

	protected $provider;
	protected $token;

	const OPTION_KEY = 'wp-oauth2-tokens';

	public function __construct( $provider, $token = null ) {
		$this->provider = $provider;
		if ( $token ) {
			$this->token = $token;
		}
	}

	protected function get_tokens() {
		return get_site_option( self::OPTION_KEY, array() );
	}

	protected function save_tokens( $tokens ) {
		return update_site_option( self::OPTION_KEY, $tokens );
	}

	public function get() {
		$tokens = $this->get_tokens();

		if ( isset( $tokens[ $this->provider ] ) ) {
			return $tokens[ $this->provider ];
		}

		return false;
	}

	public function save() {
		$tokens = $this->get_tokens();

		$tokens[ $this->provider ] = $this->token;
		$this->save_tokens( $tokens );
	}

	public function delete() {
		$tokens = $this->get_tokens();

		unset( $tokens[ $this->provider ] );
		$this->save_tokens( $tokens );
	}
}