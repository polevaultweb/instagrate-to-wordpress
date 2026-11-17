<?php

use PHPUnit\Framework\TestCase;

/**
 * instagrate_to_wordpress::attach_image() returns array( attachment ID, debug ),
 * with an ID of 0 when the image couldn't be attached.
 *
 * GitHub #19: when download_url() failed (a timeout on a big video, a 403 from an
 * expired Instagram CDN URL) it passed the WP_Error to wp_delete_file(), which on
 * PHP 8 throws a TypeError, and then to media_handle_sideload().
 */
class AttachImageTest extends TestCase {

	const URL = 'https://cdn.test/1.jpg?stp=dst-jpg';

	const POST_ID = 55;

	protected function setUp(): void {
		Media_Stubs::reset();
	}

	public function test_failed_download_returns_zero_without_sideloading() {
		Media_Stubs::$download_result = new WP_Error( 'http_404', 'Forbidden' );

		$attach = instagrate_to_wordpress::attach_image( self::URL, self::POST_ID );

		$this->assertSame( 0, $attach[0] );
		$this->assertSame( array(), Media_Stubs::$sideloads, 'Sideloaded a file that was never downloaded.' );
		$this->assertSame( array(), Media_Stubs::$deleted, 'Deleted a temp file that download_url() never made.' );
		$this->assertStringContainsString( 'Download Error: Forbidden', $attach[1] );
	}

	public function test_downloaded_image_is_sideloaded_and_its_id_returned() {
		Media_Stubs::$download_result = '/tmp/1.tmp';

		$attach = instagrate_to_wordpress::attach_image( self::URL, self::POST_ID );

		$this->assertSame( 100, $attach[0] );
		$this->assertSame(
			array( array( array( 'name' => '1.jpg', 'tmp_name' => '/tmp/1.tmp' ), self::POST_ID ) ),
			Media_Stubs::$sideloads
		);
		$this->assertStringContainsString( 'media_handle_sideload success - ID: 100', $attach[1] );
	}

	public function test_failed_sideload_deletes_the_downloaded_file_and_returns_zero() {
		$tmp = tempnam( sys_get_temp_dir(), 'itw' );
		Media_Stubs::$download_result = $tmp;
		Media_Stubs::$sideload_result = new WP_Error( 'upload_error', 'Sorry, you are not allowed to upload this file type.' );

		$attach = instagrate_to_wordpress::attach_image( self::URL, self::POST_ID );

		$this->assertSame( 0, $attach[0] );
		$this->assertSame( array( $tmp ), Media_Stubs::$deleted );
		$this->assertFileDoesNotExist( $tmp );
		$this->assertStringContainsString( 'media_handle_sideload Error: Sorry, you are not allowed to upload this file type.', $attach[1] );
	}
}
