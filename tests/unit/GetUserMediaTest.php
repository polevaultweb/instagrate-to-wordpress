<?php

use PHPUnit\Framework\TestCase;

/**
 * instagrate_to_wordpress::get_user_media() fetches the media posted since the
 * last image posted, newest first, from a three-page feed (ids 9 to 1).
 *
 * Help Scout #25264: when the last image had been deleted on Instagram, or there
 * was no last image, it crawled the account's whole history and posted every post.
 */
class GetUserMediaTest extends TestCase {

	/** @var Fake_Instagram */
	private $instagram;

	/** @var Fake_Wpdb */
	private $wpdb;

	protected function setUp(): void {
		$this->instagram = Fake_Instagram::three_pages();
		$this->wpdb      = new Fake_Wpdb();

		$GLOBALS['wpdb'] = $this->wpdb;
	}

	public function test_last_id_on_the_first_page_returns_the_newer_posts_without_paging() {
		$images = $this->get_user_media( '8' );

		$this->assertSame( array( '9' ), $this->ids( $images ) );
		$this->assertSame( array(), $this->instagram->page_requests );
	}

	public function test_last_id_on_the_second_page_returns_the_newer_posts_and_stops_paging() {
		$images = $this->get_user_media( '5' );

		$this->assertSame( array( '9', '8', '7', '6' ), $this->ids( $images ) );
		$this->assertSame( array( Fake_Instagram::PAGE_2_URL ), $this->instagram->page_requests );
	}

	public function test_deleted_last_id_stops_at_the_first_image_already_posted() {
		$this->wpdb->posted = array( '4', '3', '2', '1' );

		$images = $this->get_user_media( 'deleted' );

		$this->assertSame( array( '9', '8', '7', '6', '5' ), $this->ids( $images ) );
		$this->assertSame( array( Fake_Instagram::PAGE_2_URL ), $this->instagram->page_requests, 'Crawled past the posted image.' );
	}

	public function test_deleted_last_id_with_nothing_posted_returns_the_first_page_only() {
		$images = $this->get_user_media( 'deleted' );

		$this->assertSame( array( '9', '8', '7' ), $this->ids( $images ) );
	}

	/**
	 * The itw_manuallstid option is empty before the first post, or after a reset.
	 */
	public function test_no_last_id_returns_the_first_page_without_paging_or_checking_posted_images() {
		$images = $this->get_user_media( '' );

		$this->assertSame( array( '9', '8', '7' ), $this->ids( $images ) );
		$this->assertSame( array(), $this->instagram->page_requests );
		$this->assertSame( array(), $this->wpdb->queries );
	}

	public function test_failed_page_request_returns_nothing_so_the_next_run_retries() {
		$this->instagram->failing_urls[] = Fake_Instagram::PAGE_2_URL;

		$images = $this->get_user_media( '2' );

		$this->assertSame( array(), $images );
	}

	private function get_user_media( $starting_id ) {
		$method = new ReflectionMethod( 'instagrate_to_wordpress', 'get_user_media' );
		$method->setAccessible( true );

		return $method->invoke( null, $this->instagram, 'token', 'user-id', $starting_id );
	}

	private function ids( array $images ) {
		return array_map(
			function ( $image ) {
				return $image->id;
			},
			$images
		);
	}
}
