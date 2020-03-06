<?php

namespace Polevaultweb\IntagrateLite\WPOAuth2;

abstract class AbstractAccessToken implements AccessTokenInterface {

	/**
	 * @var string
	 */
	protected $provider;

	/**
	 * @var null|string
	 */
	protected $token;

	/**
	 * @var null|string
	 */
	protected $refresh_token;

	/**
	 * @var int|null
	 */
	protected $expires;

	/**
	 * @var null|mixed
	 */
	protected $values;

	/**
	 * AccessToken constructor.
	 *
	 * @param string      $provider
	 * @param null|string $token
	 * @param null|string $refresh_token
	 * @param null|int    $expires
	 * @param null|string $values
	 */
	public function __construct( $provider, $token = null, $refresh_token = null, $expires = null, $values = null ) {
		$this->provider = $provider;
		if ( $token ) {
			$this->token = $token;
		}
		if ( $refresh_token ) {
			$this->refresh_token = $refresh_token;
		}
		if ( $expires ) {
			$this->expires = $expires;
		}
		if ( $values ) {
			$this->values = $values;
		}
	}
}