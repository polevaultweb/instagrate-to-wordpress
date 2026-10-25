<?php

use PHPUnit\Framework\TestCase;

/**
 * Intagrate_Lite_Optin_Notice: when the coupon notice is shown, and how Lite
 * knows it has created its first post, for new sites and for sites that posted
 * before the itw_first_post_at option existed.
 */
class OptinNoticeTest extends TestCase {

	/** @var Fake_Wpdb */
	private $wpdb;

	protected function setUp(): void {
		$this->wpdb      = new Fake_Wpdb();
		$GLOBALS['wpdb'] = $this->wpdb;

		Fake_Options::$options = array();
	}

	public function test_shown_to_an_admin_on_the_dashboard_plugins_and_posts_screens_after_the_first_post() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		foreach ( array( 'dashboard', 'plugins', 'edit-post' ) as $screen_id ) {
			$this->assertTrue( $this->should_show( true, $screen_id ), $screen_id );
		}
	}

	public function test_posts_screen_is_the_post_type_lite_posts_to() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		$this->assertTrue( $this->should_show( true, 'edit-page', 'page' ) );
		$this->assertFalse( $this->should_show( true, 'edit-post', 'page' ) );
	}

	public function test_not_shown_on_lite_settings_other_plugins_pages_or_the_network_admin() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		$screens = array(
			'settings_page_instagratetowordpress',
			'toplevel_page_woocommerce',
			'settings_page_some-other-plugin',
			'edit-product',
			'post',
			'dashboard-network',
			'plugins-network',
			'dashboard-user',
		);
		foreach ( $screens as $screen_id ) {
			$this->assertFalse( $this->should_show( true, $screen_id ), $screen_id );
		}
	}

	public function test_not_shown_without_a_screen() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		$this->assertFalse( Intagrate_Lite_Optin_Notice::should_show( true, null, 'post', '' ) );
	}

	public function test_not_shown_to_users_who_cannot_manage_options() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		$this->assertFalse( $this->should_show( false, 'dashboard' ) );
	}

	public function test_not_shown_once_the_user_has_dismissed_it() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		$this->assertFalse( $this->should_show( true, 'dashboard', 'post', '1700000500' ) );
	}

	public function test_not_shown_before_the_first_post_and_the_lookup_runs_only_once() {
		$this->assertFalse( $this->should_show( true, 'dashboard' ) );
		$this->assertSame( 0, Fake_Options::$options['itw_first_post_at'] );

		$this->assertFalse( $this->should_show( true, 'plugins' ) );
		$this->assertCount( 1, $this->wpdb->queries, 'Looked for an earlier post again.' );
	}

	public function test_site_that_posted_before_this_release_is_found_by_its_post_meta() {
		$this->wpdb->posted = array( '17841400000000001' );

		$this->assertTrue( $this->should_show( true, 'dashboard' ) );
		$this->assertGreaterThan( 0, Fake_Options::$options['itw_first_post_at'] );
	}

	public function test_lookup_skipped_for_users_and_screens_that_would_not_see_the_notice() {
		$this->should_show( false, 'dashboard' );
		$this->should_show( true, 'settings_page_instagratetowordpress' );
		$this->should_show( true, 'dashboard', 'post', '1700000500' );

		$this->assertSame( array(), $this->wpdb->queries );
		$this->assertArrayNotHasKey( 'itw_first_post_at', Fake_Options::$options );
	}

	public function test_first_post_is_recorded() {
		Intagrate_Lite_Optin_Notice::record_first_post();

		$this->assertGreaterThan( 0, Fake_Options::$options['itw_first_post_at'] );
		$this->assertTrue( $this->should_show( true, 'dashboard' ) );
	}

	public function test_first_post_is_recorded_after_a_lookup_found_none() {
		$this->should_show( true, 'dashboard' );

		Intagrate_Lite_Optin_Notice::record_first_post();

		$this->assertTrue( $this->should_show( true, 'dashboard' ) );
	}

	public function test_later_posts_keep_the_first_post_time() {
		Fake_Options::$options['itw_first_post_at'] = 1700000000;

		Intagrate_Lite_Optin_Notice::record_first_post();

		$this->assertSame( 1700000000, Fake_Options::$options['itw_first_post_at'] );
	}

	private function should_show( $can_manage, $screen_id, $lite_post_type = 'post', $dismissed = '' ) {
		$screen     = new stdClass();
		$screen->id = $screen_id;

		return Intagrate_Lite_Optin_Notice::should_show( $can_manage, $screen, $lite_post_type, $dismissed );
	}
}
