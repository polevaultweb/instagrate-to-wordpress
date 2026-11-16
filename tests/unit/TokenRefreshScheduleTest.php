<?php

use PHPUnit\Framework\TestCase;
use Polevaultweb\WPOAuth2\TokenManager;

/**
 * A daily WP-Cron event refreshes the access token (GitHub #5), so a token in
 * its last week is refreshed even if nothing posts before it expires.
 * Activation and init schedule it, deactivation and uninstall clear it.
 *
 * refresh_access_token() runs the real itw_Instagram::get_access_token() and
 * token class, with the OAuth library replaced by a fake that records each
 * refresh and saves the new token the way WPOAuth2::refresh_access_token() does.
 */
class TokenRefreshScheduleTest extends TestCase {

	const HOOK = 'itw_refresh_access_token';

	/** @var Token_Refresh_Fake_WPOAuth2 */
	private $wpoauth;

	/** @var ReflectionProperty itw_Instagram::$wpoauth */
	private $wpoauth_property;

	/** @var object The library instance the plugin loaded with, put back after each test. */
	private $loaded_wpoauth;

	protected function setUp(): void {
		WP_Stubs::reset();

		$this->wpoauth = new Token_Refresh_Fake_WPOAuth2();

		$this->wpoauth_property = new ReflectionProperty( 'itw_Instagram', 'wpoauth' );
		$this->wpoauth_property->setAccessible( true );
		$this->loaded_wpoauth = $this->wpoauth_property->getValue();
		$this->wpoauth_property->setValue( null, $this->wpoauth );
	}

	protected function tearDown(): void {
		$this->wpoauth_property->setValue( null, $this->loaded_wpoauth );
	}

	public function test_init_and_the_cron_event_are_hooked() {
		$this->assertContains( 'instagrate_to_wordpress::schedule_token_refresh', WP_Stubs::$hooks['init'] );
		$this->assertSame( array( 'instagrate_to_wordpress::refresh_access_token' ), WP_Stubs::$hooks[ self::HOOK ] );
	}

	public function test_activation_schedules_and_deactivation_clears() {
		$this->assertSame( array( 'instagrate_to_wordpress::schedule_token_refresh' ), WP_Stubs::$hooks['activate_instagrate-to-wordpress/instagrate-to-wordpress.php'] );
		$this->assertSame( array( 'instagrate_to_wordpress::clear_token_refresh' ), WP_Stubs::$hooks['deactivate_instagrate-to-wordpress/instagrate-to-wordpress.php'] );
	}

	public function test_schedules_a_daily_event_when_missing() {
		instagrate_to_wordpress::schedule_token_refresh();

		$this->assertCount( 1, WP_Stubs::$cron_events );
		$this->assertSame( self::HOOK, WP_Stubs::$cron_events[0]['hook'] );
		$this->assertSame( 'daily', WP_Stubs::$cron_events[0]['recurrence'] );
	}

	public function test_does_not_schedule_a_second_event() {
		instagrate_to_wordpress::schedule_token_refresh();
		instagrate_to_wordpress::schedule_token_refresh();

		$this->assertCount( 1, WP_Stubs::$cron_events );
	}

	public function test_clear_removes_the_event() {
		instagrate_to_wordpress::schedule_token_refresh();

		instagrate_to_wordpress::clear_token_refresh();

		$this->assertFalse( wp_next_scheduled( self::HOOK ) );
	}

	public function test_uninstall_removes_the_event() {
		instagrate_to_wordpress::schedule_token_refresh();

		instagrate_to_wordpress::plugin_uninstall();

		$this->assertFalse( wp_next_scheduled( self::HOOK ) );
	}

	public function test_refreshes_a_token_in_its_last_week_and_saves_it() {
		$this->connect( time() + 3 * DAY_IN_SECONDS );

		instagrate_to_wordpress::refresh_access_token();

		$this->assertSame( array( 'old-token' ), $this->wpoauth->refresh_tokens );
		$this->assertSame( 'new-token', get_option( 'itw_accesstoken' ) );
		$this->assertSame( Token_Refresh_Fake_WPOAuth2::EXPIRES, get_option( 'itw_accesstoken_expires' ) );
	}

	public function test_does_not_refresh_a_token_with_more_than_a_week_left() {
		$this->connect( time() + 30 * DAY_IN_SECONDS );

		instagrate_to_wordpress::refresh_access_token();

		$this->assertSame( array(), $this->wpoauth->refresh_tokens );
		$this->assertSame( 'old-token', get_option( 'itw_accesstoken' ) );
	}

	/**
	 * Instagram only refreshes a token that hasn't expired.
	 */
	public function test_does_not_refresh_an_expired_token() {
		$this->connect( time() - 1 );

		instagrate_to_wordpress::refresh_access_token();

		$this->assertSame( array(), $this->wpoauth->refresh_tokens );
		$this->assertFalse( ( new itw_Instagram() )->get_access_token() );
	}

	public function test_does_nothing_when_not_connected() {
		instagrate_to_wordpress::refresh_access_token();

		$this->assertSame( array(), $this->wpoauth->refresh_tokens );
	}

	public function test_failed_refresh_keeps_the_token_while_it_is_valid() {
		$this->wpoauth->new_token = false;
		$this->connect( time() + DAY_IN_SECONDS );

		$this->assertSame( 'old-token', ( new itw_Instagram() )->get_access_token() );
		$this->assertSame( array( 'old-token' ), $this->wpoauth->refresh_tokens );
		$this->assertSame( 'old-token', get_option( 'itw_accesstoken' ) );
	}

	private function connect( $expires ) {
		update_option( 'itw_accesstoken', 'old-token' );
		update_option( 'itw_accesstoken_expires', $expires );
	}
}

/**
 * Stands in for WPOAuth2: like refresh_access_token(), reads the refresh token
 * through the token manager and saves the new token with it.
 */
class Token_Refresh_Fake_WPOAuth2 {

	const EXPIRES = 1900000000;

	/** @var TokenManager */
	public $token_manager;

	/** @var string|false The token l.intagrate.io returns, false for a failed refresh. */
	public $new_token = 'new-token';

	/** @var string[] The refresh token sent for each refresh, in order. */
	public $refresh_tokens = array();

	public function __construct() {
		$this->token_manager = new TokenManager( Intagrate_Lite_Instagram_Access_Token::class );
	}

	public function refresh_access_token( $client_id, $provider ) {
		$refresh_token          = $this->token_manager->get_refresh_token( $provider );
		$this->refresh_tokens[] = $refresh_token;

		if ( ! $this->new_token ) {
			return false;
		}

		$this->token_manager->set_access_token( $provider, $this->new_token, $refresh_token, self::EXPIRES );

		return $this->new_token;
	}
}
