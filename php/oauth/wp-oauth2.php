<?php

namespace Polevaultweb\IG\WP_OAuth2;

class WP_OAuth2 {

	public static function get_disconnect_url( $provider, $url ) {
		$url = add_query_arg( array( 'wp-oauth2' => $provider, 'action' => 'disconnect' ), $url );

		return $url;
	}

	public static function disconnect( $provider ) {
		$token = new Access_Token( $provider );
		$token->delete();
	}

	public static function get_access_token( $provider ) {
		$token = new Access_Token( $provider );

		return $token->get();
	}

	public static function set_access_token( $provider, $token ) {
		$token = new Access_Token( $provider, $token );
		$token->save();
	}

	public static function is_authorized( $provider ) {
		$token = self::get_access_token( $provider );

		return (bool) $token;
	}
}