<?php

namespace Polevaultweb\IntagrateLite\WPOAuth2;

class TokenManager {

	/**
	 * @var AccessTokenInterface
	 */
	protected $access_token_class;

	/**
	 * TokenManager constructor.
	 *
	 * @param $access_token_class
	 */
	public function __construct( $access_token_class = null ) {
		if ( empty( $access_token_class ) ) {
			$access_token_class = AccessToken::class;
		}
		$this->access_token_class = $access_token_class;
	}

	/**
	 * @param      $provider
	 * @param null $token
	 * @param null $refresh_token
	 * @param null $expires
	 * @param null $values
	 *
	 * @return AccessTokenInterface
	 */
	protected function get_token( $provider, $token = null, $refresh_token = null, $expires = null, $values = null ) {
		$token_class = $this->access_token_class;

		return new $token_class( $provider, $token, $refresh_token, $expires, $values );
	}


	public function remove_access_token( $provider ) {
		$token = $this->get_token( $provider );
		$token->delete();
	}

	public function get_access_token( $provider ) {
		$token = $this->get_token( $provider );

		return $token->get_token();
	}

	public function get_refresh_token( $provider ) {
		$token = $this->get_token( $provider );

		return $token->get_refresh_token();
	}

	public function set_access_token( $provider, $token, $refresh_token = null, $expires = null, $values = null ) {
		$token = $this->get_token( $provider, $token, $refresh_token, $expires, $values );
		$token->save();
	}
}