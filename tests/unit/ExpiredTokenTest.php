<?php

use PHPUnit\Framework\TestCase;

/**
 * Once the Instagram token has expired, Instagram won't refresh it, so the plugin
 * has to be reconnected.
 *
 * itw_Instagram::get_access_token() returns false for an expired token without
 * asking l.intagrate.io to refresh it. Every admin screen but the settings page
 * shows a notice linking to the settings page, and the settings page says the
 * connection expired beside its Log in link.
 *
 * GitHub #6: auto posting stopped without telling anyone, and every later call
 * tried the refresh again and failed again.
 */
class ExpiredTokenTest extends TestCase {

	const SETTINGS_URL = 'https://example.test/wp-admin/options-general.php?page=instagratetowordpress';

	/** @var object Stands in for WPOAuth2, recording refresh requests. */
	private $wpoauth;

	/** @var ReflectionProperty itw_Instagram::$wpoauth */
	private $wpoauth_property;

	/** @var Polevaultweb\WPOAuth2\WPOAuth2 The instance the plugin set up as it loaded. */
	private $original_wpoauth;

	protected function setUp(): void {
		WP_Stubs::reset();
		WP_Stubs::$capabilities = array( 'manage_options' );
		WP_Stubs::$options      = array( 'date_format' => 'j F Y' );

		$this->wpoauth = new class() {
			public $token_manager;

			public $refreshes = 0;

			public $new_token = false;

			public function __construct() {
				$this->token_manager = new Polevaultweb\WPOAuth2\TokenManager( Intagrate_Lite_Instagram_Access_Token::class );
			}

			public function refresh_access_token( $client_id, $provider ) {
				$this->refreshes++;

				return $this->new_token;
			}
		};

		$this->wpoauth_property = new ReflectionProperty( itw_Instagram::class, 'wpoauth' );
		$this->wpoauth_property->setAccessible( true );
		$this->original_wpoauth = $this->wpoauth_property->getValue();
		$this->wpoauth_property->setValue( null, $this->wpoauth );
	}

	protected function tearDown(): void {
		$this->wpoauth_property->setValue( null, $this->original_wpoauth );
		unset( $_GET['page'] );
	}

	public function test_expired_token_is_not_refreshed() {
		$this->connect( time() - DAY_IN_SECONDS );

		$this->assertFalse( ( new itw_Instagram() )->get_access_token() );
		$this->assertSame( 0, $this->wpoauth->refreshes );
	}

	public function test_token_expiring_now_counts_as_expired() {
		$this->connect( time() );

		$this->assertTrue( itw_Instagram::is_token_expired() );
	}

	public function test_valid_token_is_returned_without_refreshing() {
		$this->connect( time() + 30 * DAY_IN_SECONDS );

		$this->assertSame( 'token', ( new itw_Instagram() )->get_access_token() );
		$this->assertSame( 0, $this->wpoauth->refreshes );
	}

	public function test_token_in_its_last_week_is_refreshed() {
		$this->connect( time() + DAY_IN_SECONDS );
		$this->wpoauth->new_token = 'new-token';

		$this->assertSame( 'new-token', ( new itw_Instagram() )->get_access_token() );
		$this->assertSame( 1, $this->wpoauth->refreshes );
	}

	public function test_failed_refresh_keeps_a_token_that_is_still_valid() {
		$this->connect( time() + DAY_IN_SECONDS );

		$this->assertSame( 'token', ( new itw_Instagram() )->get_access_token() );
		$this->assertSame( 1, $this->wpoauth->refreshes );
	}

	/**
	 * @dataProvider not_expired
	 */
	public function test_not_expired( $options ) {
		WP_Stubs::$options = $options;

		$this->assertFalse( itw_Instagram::is_token_expired() );
	}

	public function not_expired() {
		return array(
			'valid token'                  => array( array( 'itw_accesstoken' => 'token', 'itw_accesstoken_expires' => time() + DAY_IN_SECONDS ) ),
			'logged out'                   => array( array( 'itw_accesstoken' => '', 'itw_accesstoken_expires' => time() - DAY_IN_SECONDS ) ),
			'Basic Display API, no expiry' => array( array( 'itw_accesstoken' => 'token', 'itw_accesstoken_expires' => '' ) ),
			'never connected'              => array( array() ),
		);
	}

	/**
	 * Reconnecting saves the new token and expiry through the token class, with no
	 * other flag to clear.
	 */
	public function test_saving_a_new_token_clears_the_expired_state() {
		$this->connect( time() - DAY_IN_SECONDS );
		$this->assertTrue( itw_Instagram::is_token_expired() );

		$this->wpoauth->token_manager->set_access_token( 'instagram-facebook', 'new-token', 'new-token', time() + 60 * DAY_IN_SECONDS );

		$this->assertFalse( itw_Instagram::is_token_expired() );
		$this->assertSame( 'new-token', ( new itw_Instagram() )->get_access_token() );
		$this->assertSame( '', $this->notice() );
	}

	public function test_notice_links_to_the_settings_page() {
		$this->connect( gmmktime( 13, 55, 24, 3, 6, 2026 ) );

		$notice = $this->notice();

		$this->assertStringContainsString( '<div class="notice notice-error">', $notice );
		$this->assertStringContainsString( 'The connection to Instagram expired on 6 March 2026, so no new images will be posted.', $notice );
		$this->assertStringContainsString( '<a href="' . self::SETTINGS_URL . '">Log in to Instagram again</a> to reconnect.', $notice );
	}

	public function test_notice_is_part_of_the_admin_notices() {
		$this->connect( time() - DAY_IN_SECONDS );

		ob_start();
		instagrate_to_wordpress::plugin_admin_notice();
		$notices = ob_get_clean();

		$this->assertStringContainsString( 'The connection to Instagram expired on', $notices );
	}

	public function test_notice_escapes_the_date() {
		WP_Stubs::$options['date_format'] = 'j F Y \<\b\>';
		$this->connect( gmmktime( 13, 55, 24, 3, 6, 2026 ) );

		$notice = $this->notice();

		$this->assertStringContainsString( 'expired on 6 March 2026 &lt;b&gt;,', $notice );
		$this->assertStringNotContainsString( '<b>', $notice );
	}

	public function test_no_notice_on_the_settings_page() {
		$this->connect( time() - DAY_IN_SECONDS );
		$_GET['page'] = 'instagratetowordpress';

		$this->assertSame( '', $this->notice() );
	}

	public function test_no_notice_when_the_token_is_valid() {
		$this->connect( time() + 30 * DAY_IN_SECONDS );

		$this->assertSame( '', $this->notice() );
	}

	public function test_no_notice_when_not_connected() {
		$this->assertSame( '', $this->notice() );
	}

	public function test_no_notice_for_users_who_cannot_manage_options() {
		WP_Stubs::$capabilities = array();
		$this->connect( time() - DAY_IN_SECONDS );

		$this->assertSame( '', $this->notice() );
	}

	public function test_settings_page_says_when_the_connection_expired() {
		$this->connect( gmmktime( 13, 55, 24, 3, 6, 2026 ) );

		$this->assertSame( 'The connection to Instagram expired on 6 March 2026, so no new images will be posted. Please log in again to reconnect - ', $this->login_message() );
	}

	public function test_settings_page_asks_to_log_in_when_not_connected() {
		$this->assertSame( 'Please login securely to Instagram to authorise the plugin - ', $this->login_message() );
	}

	private function connect( $expires ) {
		WP_Stubs::$options['itw_accesstoken']         = 'token';
		WP_Stubs::$options['itw_accesstoken_expires'] = $expires;
	}

	private function notice() {
		$method = new ReflectionMethod( 'instagrate_to_wordpress', 'maybe_display_expired_token_notice' );
		$method->setAccessible( true );

		ob_start();
		$method->invoke( null );

		return ob_get_clean();
	}

	private function login_message() {
		$method = new ReflectionMethod( 'instagrate_to_wordpress', 'login_message' );
		$method->setAccessible( true );

		return $method->invoke( null );
	}
}
