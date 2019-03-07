<?php

namespace Polevaultweb\IG\WP_OAuth2;

class Admin_Handler {

	protected $redirect;

	/**
	 * Admin_Handler constructor.
	 *
	 * @param $redirect
	 */
	public function __construct( $redirect ) {
		$this->redirect = $redirect;
	}

	public function init() {
		add_action( 'admin_init', array( $this, 'handle_redirect' ) );
		add_action( 'admin_init', array( $this, 'handle_disconnect' ) );
		add_action( 'admin_init', array( $this, 'handle_render_notice' ) );
	}

	public function handle_render_notice() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( ! $this->is_callback_page() ) {
			return;
		}

		$notice = filter_input( INPUT_GET, 'notice' );
		if ( empty( $notice ) ) {
			return;
		}

		add_action( 'admin_notices', array( $this, 'render_' . $notice . '_notice' ) );
	}

	public function handle_disconnect() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( ! $this->is_callback_page() ) {
			return;
		}

		$provider = filter_input( INPUT_GET, 'wp-oauth2' );
		if ( empty( $provider ) ) {
			return;
		}

		$action = filter_input( INPUT_GET, 'action' );
		if ( empty( $action ) || 'disconnect' !== $action ) {
			return;
		}

		session_destroy();

		update_option( 'itw_accesstoken', '' );
		update_option( 'itw_username', '' );
		update_option( 'itw_userid', '' );
		update_option( 'itw_manuallstid', '' );

		$this->redirect( 'disconnection' );
	}

	protected function redirect( $notice = null, $args = array()) {
		$post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

		$defaults = array(
			'post'   => $post_id,
			'action' => 'edit',
			'notice' => $notice,
		);

		$args = array_merge( $defaults, $args );

		$url = add_query_arg( $args, $this->redirect );
		wp_redirect( $url );
		exit;
	}

	protected function is_callback_page() {
		$parts = parse_url( $this->redirect );

		global $pagenow;
		if ( ! isset( $pagenow ) ) {
			return false;
		}
		if ( $pagenow !== basename( $parts['path'] ) ) {
			return false;
		}

		return true;
	}

	public function handle_redirect() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		if ( ! $this->is_callback_page() ) {
			return;
		}

		$provider = filter_input( INPUT_GET, 'wp-oauth2' );
		if ( empty( $provider ) ) {
			return;
		}

		$action = filter_input( INPUT_GET, 'action' );
		if ( empty( $action ) || 'connect' !== $action ) {
			return;
		}

		$error = filter_input( INPUT_GET, 'error' );
		if ( $error ) {
			// Show error notice
			$this->redirect( 'error' );
		}

		$token = filter_input( INPUT_GET, 'token' );
		$iv    = filter_input( INPUT_GET, 'iv' );
		$values = filter_input( INPUT_GET, 'values' );

		if ( empty( $token ) || empty( $iv ) || empty( $values ) ) {
			$this->redirect( 'error' );
		}

		$method = OAuth2_Client::get_method();
		$key    = get_site_transient( 'wp-oauth2-key' );
		$token  = openssl_decrypt( $token, $method, $key, 0, urldecode( $iv ) );
		if ( empty( $token ) ) {
			$this->redirect( 'error' );
		}
		$post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

		$values     = unserialize( openssl_decrypt( $values, $method, $key, 0, urldecode( $iv ) ) );
		$auth_token = json_decode( json_encode( $values ) );

		$auth_token->access_token = $token;

		$this->save_token( $auth_token, $post_id );

		$this->redirect( 'connection' );
	}

	protected function save_token( $auth_token, $post_id ) {
		$accesstkn = $auth_token->access_token;
		$username  = $auth_token->user->username;
		$userid    = $auth_token->user->id;

		update_option( 'itw_accesstoken', $accesstkn );
		update_option( 'itw_username', $username );
		update_option( 'itw_userid', $userid );
	}

	protected function get_provider_display_name() {
		return 'Instagram';
	}

	public function render_error_notice() {
		$provider = $this->get_provider_display_name();

		$error_description = filter_input( INPUT_GET, 'error_description' );
		$message           = $error_description ? $error_description : __( 'An unknown error occurred.' );
		printf( '<div class="error"><p><strong>' . $provider . ' %s</strong> &mdash; %s</p></div>', __( 'Connection Error' ), $message );
	}

	public function render_connection_notice() {
		$provider = $this->get_provider_display_name();

		$message = sprintf( __( 'You have successfully connected with your %s account.' ), $provider );
		printf( '<div class="updated"><p><strong>' . $provider . ' %s</strong> &mdash; %s</p></div>', __( 'Connected' ), $message );
	}

	public function render_disconnection_notice() {
		$provider = $this->get_provider_display_name();

		$message = sprintf( __( 'You have successfully disconnected your %s account.' ), $provider );
		printf( '<div class="updated"><p><strong>' . $provider . ' %s</strong> &mdash; %s</p></div>', __( 'Disconnected' ), $message );
	}
}