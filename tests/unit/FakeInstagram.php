<?php
/**
 * Stands in for the plugin's Instagram API client: a three-page media feed,
 * newest first (ids 9 to 1), that records every request made to it.
 */
class Fake_Instagram {

	const PAGE_2_URL = 'https://graph.instagram.com/me/media?after=page-2';
	const PAGE_3_URL = 'https://graph.instagram.com/me/media?after=page-3';

	/** @var int Requests for the first page. */
	public $first_page_requests = 0;

	/** @var string[] URLs requested after the first page, in order. */
	public $page_requests = array();

	/** @var string[] URLs whose request fails. */
	public $failing_urls = array();

	private $first_page;

	private $pages = array();

	public static function three_pages() {
		$feed = new self();

		$feed->first_page                = self::page( array( '9', '8', '7' ), self::PAGE_2_URL );
		$feed->pages[ self::PAGE_2_URL ] = self::page( array( '6', '5', '4' ), self::PAGE_3_URL );
		$feed->pages[ self::PAGE_3_URL ] = self::page( array( '3', '2', '1' ), null );

		return $feed;
	}

	private static function page( array $ids, $next ) {
		$page = (object) array(
			'data'   => array(),
			'paging' => new stdClass(),
		);

		foreach ( $ids as $id ) {
			$page->data[] = (object) array( 'id' => $id );
		}

		if ( $next ) {
			$page->paging->next = $next;
		}

		return $page;
	}

	public function get_user_media( $token, $user_id ) {
		$this->first_page_requests++;

		return $this->first_page;
	}

	public function do_http_request( $token, $url, $params = '', $full_url = '' ) {
		$this->page_requests[] = $full_url;

		if ( in_array( $full_url, $this->failing_urls, true ) || ! isset( $this->pages[ $full_url ] ) ) {
			return false;
		}

		return $this->pages[ $full_url ];
	}

	/**
	 * The plugin requests later pages through itw_Instagram::http().
	 */
	public function http() {
		return $this;
	}
}
