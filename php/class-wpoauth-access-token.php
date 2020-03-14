<?php

use Polevaultweb\IntagrateLite\WPOAuth2\AbstractAccessToken;

class Intagrate_Lite_Instagram_Access_Token extends AbstractAccessToken {

	public function save() {
		if ( ! empty( $this->values ) ) {
			// Only for new connections, not refreshing tokens
			$values = maybe_unserialize( $this->values );
			$userid = $values['user_id'];

			$user     = ( new itw_Instagram() )->get_user( $this->token, $values['user_id'] );
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
		session_destroy();

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