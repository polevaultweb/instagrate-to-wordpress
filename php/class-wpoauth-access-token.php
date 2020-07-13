<?php

use Polevaultweb\IntagrateLite\WPOAuth2\AbstractAccessToken;

class Intagrate_Lite_Instagram_Access_Token extends AbstractAccessToken {

	public function save() {
		if ( ! empty( $this->values ) ) {
			// Only for new connections, not refreshing tokens
			// Extract the user_id from the serialized data using a manual method
			// as unserialize breaks the 64 bit integer on 32 bit systems
			$start_string = '"user_id";i:';
			$start = strpos($this->values, $start_string);
			$userid = substr($this->values, $start + strlen( $start_string ) , - 2 );

			if ( empty( $userid ) ) {
				error_log( 'Intagrate Lite: Error getting user_id from access token values ' );
				error_log( print_r( $this->values, true ) );
			}

			$user     = ( new itw_Instagram() )->get_user( $this->token, $userid );
			$username = '';
			if ( isset ( $user->username ) ) {
				$username = $user->username;
			}

			update_option( 'itw_username', $username );
			update_option( 'itw_userid', $userid );
			update_option( 'itw_manuallstid', '' );
		}

		update_option( 'itw_accesstoken', $this->token );
		update_option( 'itw_accesstoken_expires', $this->expires );
	}

	public function delete() {
		update_option( 'itw_accesstoken', '' );
		update_option( 'itw_accesstoken_expires', '' );
		update_option( 'itw_username', '' );
		update_option( 'itw_userid', '' );
		update_option( 'itw_manuallstid', '' );
	}

	public function get_token() {
		return get_option( 'itw_accesstoken' );
	}

	public function get_refresh_token() {
		return $this->get_token();
	}

}