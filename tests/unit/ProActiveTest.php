<?php

use PHPUnit\Framework\TestCase;
use Polevaultweb\WPOAuth2\V3\WPOAuth2;

/**
 * With Intagrate (Pro) active too, Lite still reads its own Instagram token.
 *
 * Both plugins bundle polevaultweb/wp-oauth2. Its WPOAuth2::instance() used to
 * return one instance for every plugin, with the token class of whichever
 * plugin asked first. Pro loads first, so Lite read its token through Pro's
 * token class, found nothing and showed as logged out (GitHub
 * polevaultweb/intagrate#368). Now each plugin has its own instance, login key
 * and filters.
 *
 * Pro's instance here is the one Pro sets up as it loads, with a stand-in for
 * its token class. Uses the real library from vendor/.
 */
class ProActiveTest extends TestCase {

	/** @var ReflectionProperty WPOAuth2::$instances */
	private $instances_property;

	/** @var array The instances set up as the plugin loaded */
	private $original_instances;

	/** @var ReflectionProperty itw_Instagram::$wpoauth */
	private $wpoauth_property;

	/** @var WPOAuth2 Lite's instance set up as the plugin loaded */
	private $original_wpoauth;

	/** @var array */
	private $original_hooks;

	/** @var WPOAuth2 Pro's instance */
	private $pro;

	protected function setUp(): void {
		WP_Stubs::reset();

		$this->instances_property = new ReflectionProperty( WPOAuth2::class, 'instances' );
		$this->instances_property->setAccessible( true );
		$this->original_instances = $this->instances_property->getValue();

		$this->wpoauth_property = new ReflectionProperty( itw_Instagram::class, 'wpoauth' );
		$this->wpoauth_property->setAccessible( true );
		$this->original_wpoauth = $this->wpoauth_property->getValue();

		// Only the hooks load_admin() adds below
		$this->original_hooks = WP_Stubs::$hooks;
		WP_Stubs::$hooks      = array();

		// Load the plugins in the order WordPress does: Pro, then Lite
		$this->instances_property->setValue( null, array() );
		$this->pro = WPOAuth2::instance( 'https://l.intagrate.io/', Pro_Active_Fake_Pro_Token::class, 'instagrate_pro' );
		itw_Instagram::load_admin();
	}

	protected function tearDown(): void {
		$this->instances_property->setValue( null, $this->original_instances );
		$this->wpoauth_property->setValue( null, $this->original_wpoauth );
		WP_Stubs::$hooks = $this->original_hooks;
	}

	public function test_lite_has_its_own_instance() {
		$lite = $this->wpoauth_property->getValue();

		$this->assertNotSame( $this->pro, $lite );
		$this->assertSame( itw_Instagram::OAUTH_INSTANCE, $lite->get_instance_key() );
	}

	public function test_lite_reads_its_own_token_with_pro_active() {
		WP_Stubs::$options['itw_accesstoken']         = 'lite-token';
		WP_Stubs::$options['itw_accesstoken_expires'] = time() + 30 * DAY_IN_SECONDS;

		$this->assertSame( 'lite-token', ( new itw_Instagram() )->get_access_token() );
	}

	public function test_saving_lites_token_leaves_pros_alone() {
		WP_Stubs::$options['pro_token'] = 'pro-token';

		$this->wpoauth_property->getValue()->token_manager->set_access_token( 'instagram-facebook', 'lite-token', null, 1800000000 );

		$this->assertSame( 'lite-token', get_option( 'itw_accesstoken' ) );
		$this->assertSame( 1800000000, get_option( 'itw_accesstoken_expires' ) );
		$this->assertSame( 'pro-token', $this->pro->token_manager->get_access_token( 'instagram-facebook' ) );
	}

	public function test_pro_reads_its_own_token_with_lite_active() {
		WP_Stubs::$options['itw_accesstoken'] = 'lite-token';

		$this->assertFalse( $this->pro->token_manager->get_access_token( 'instagram-facebook' ) );
	}

	public function test_login_keys_are_kept_separate() {
		$pro_login  = $this->pro->get_authorize_url( 'instagram-facebook', '827131036205995', 'https://example.test/wp-admin/post.php?post=42&action=edit' );
		$lite_login = ( new itw_Instagram() )->authorizeUrl( ITW_RETURN_URI );

		parse_str( parse_url( $pro_login, PHP_URL_QUERY ), $pro_params );
		parse_str( parse_url( $lite_login, PHP_URL_QUERY ), $lite_params );

		$this->assertSame( array( 'instagram-facebook' => $pro_params['key'] ), WP_Stubs::$site_transients['wp-oauth2-key-instagrate_pro'] );
		$this->assertSame( array( 'instagram-facebook' => $lite_params['key'] ), WP_Stubs::$site_transients['wp-oauth2-key-intagrate_lite'] );
		$this->assertStringContainsString( 'wp-oauth2-instance=intagrate_lite', $lite_params['redirect_uri'] );
		$this->assertStringStartsWith( ITW_RETURN_URI . '&', $lite_params['redirect_uri'] );
	}

	public function test_lites_display_name_filter_only_applies_to_lite() {
		$this->assertContains( 'itw_Instagram::pvw_wp_oauth2_provider_display_name', WP_Stubs::$hooks['pvw_wp_oauth2_intagrate_lite_provider_display_name'] );
		$this->assertArrayNotHasKey( 'pvw_wp_oauth2_provider_display_name', WP_Stubs::$hooks );
	}

	public function test_lite_registers_one_admin_handler() {
		$this->assertCount( 3, WP_Stubs::$hooks['admin_init'] );
		$this->assertInstanceOf( Polevaultweb\WPOAuth2\V3\AdminHandler::class, WP_Stubs::$hooks['admin_init'][0][0] );
	}
}

/**
 * Stands in for Pro's Instagrate_Pro_Instagram_Access_Token, which keeps each
 * account's token in its post meta.
 */
class Pro_Active_Fake_Pro_Token extends Polevaultweb\WPOAuth2\V3\AbstractAccessToken {

	public function save() {
		update_option( 'pro_token', $this->token );
	}

	public function delete() {
		update_option( 'pro_token', '' );
	}

	public function get_token() {
		return get_option( 'pro_token' );
	}

	public function get_refresh_token() {
		return $this->get_token();
	}
}
