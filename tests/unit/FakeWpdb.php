<?php
/**
 * Stands in for $wpdb, answering only the postmeta count that
 * instagrate_to_wordpress::instagrate_id_exists() runs, and the lookup for any
 * post Lite created that Intagrate_Lite_Optin_Notice::first_post_at() runs.
 */
class Fake_Wpdb {

	public $postmeta = 'wp_postmeta';

	/** @var string[] instagrate_id meta values, one per image already posted. */
	public $posted = array();

	/** @var string[] Queries run. */
	public $queries = array();

	private $prepared_args = array();

	public function prepare( $query, ...$args ) {
		$this->prepared_args = $args;

		return $query;
	}

	/**
	 * Counts posted images the way the query does: meta_key = %s AND meta_value LIKE '%id%'.
	 */
	public function get_var( $query ) {
		$this->queries[] = $query;

		if ( 1 === count( $this->prepared_args ) ) {
			// meta_key = %s LIMIT 1: the post ID of any image posted
			if ( 'instagrate_id' !== $this->prepared_args[0] || empty( $this->posted ) ) {
				return null;
			}

			return '1';
		}

		list( $meta_key, $pattern ) = $this->prepared_args;
		if ( 'instagrate_id' !== $meta_key ) {
			return '0';
		}

		$needle = trim( $pattern, '%' );
		$count  = 0;
		foreach ( $this->posted as $value ) {
			if ( false !== strpos( $value, $needle ) ) {
				$count++;
			}
		}

		return (string) $count;
	}
}
