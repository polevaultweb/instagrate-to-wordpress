<?php

namespace Polevaultweb\IntagrateLite\WPOAuth2;

class AccessToken extends AbstractAccessToken {

	const OPTION_KEY = 'wp-oauth2-tokens';

	/**
	 * @return array
	 */
	protected function get_tokens() {
		return get_site_option( self::OPTION_KEY, array() );
	}

	/**
	 * @param array $tokens
	 *
	 * @return mixed
	 */
	protected function save_tokens( $tokens ) {
		return update_site_option( self::OPTION_KEY, $tokens );
	}

	public function get_token() {
		return $this->get( 'token' );
	}

	public function get_refresh_token() {
		return $this->get( 'refresh_token' );
	}

	/**
	 * @param string $type
	 *
	 * @return bool|mixed
	 */
	public function get( $type = 'token' ) {
		$tokens = $this->get_tokens();

		if ( isset( $tokens[ $this->provider ] ) ) {
			$data = $tokens[ $this->provider ];

			if ( empty( $type ) ) {
				return $data;
			}

			if ( ! is_array( $data ) ) {
				return $type == 'token' ? $data : false;
			}

			return isset( $data[ $type ] ) ? $data[ $type ] : false;
		}

		return false;
	}

	public function save() {
		$tokens = $this->get_tokens();

		$data = array( 'token' => $this->token );
		if ( ! empty( $this->refresh_token ) ) {
			$data['refresh_token'] = $this->refresh_token;
		}
		if ( ! empty( $this->expires ) ) {
			$data['expires'] = $this->expires;
		}
		if ( ! empty( $this->values ) ) {
			$data['values'] = $this->values;
		}

		$tokens[ $this->provider ] = $data;

		$this->save_tokens( $tokens );
	}

	public function delete() {
		$tokens = $this->get_tokens();

		unset( $tokens[ $this->provider ] );
		$this->save_tokens( $tokens );
	}
}