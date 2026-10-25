<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A one-time notice offering the 20% Intagrate coupon, shown once Intagrate Lite
 * has created its first post from Instagram.
 *
 * Shown only to users who can manage options, and only on the Dashboard, the
 * Plugins screen and the list of the post type Lite posts to. Each user can
 * dismiss it for good, and submitting the form dismisses it too. Nothing is sent
 * to the mailing list unless the user submits the form.
 */
class Intagrate_Lite_Optin_Notice {

	/**
	 * The mailing list the coupon forms subscribe to, here and in the settings
	 * page sidebar. Double opt-in: Sendy emails the coupon once the address is confirmed.
	 */
	const SUBSCRIBE_URL  = 'https://sendy.polevaultweb.com/subscribe';
	const SUBSCRIBE_LIST = 'gSSwmSVrDx7maZb9763bd8cg';

	/**
	 * Option: when Lite created its first post (a timestamp), or 0 once checked if it hasn't.
	 */
	const FIRST_POST_OPTION = 'itw_first_post_at';

	/**
	 * User meta: when the user dismissed the notice.
	 */
	const DISMISSED_META = 'itw_optin_dismissed';

	const DISMISS_ACTION = 'itw_dismiss_optin';

	/**
	 * Whether to show the notice on this request, worked out once.
	 *
	 * @var bool|null
	 */
	protected static $display;

	public static function load() {
		add_action( 'admin_notices', __CLASS__ . '::render' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_script' );
		add_action( 'wp_ajax_' . self::DISMISS_ACTION, __CLASS__ . '::dismiss' );
		add_action( 'admin_post_' . self::DISMISS_ACTION, __CLASS__ . '::dismiss' );
	}

	/**
	 * Record that Lite has created a post, the first time it does.
	 */
	public static function record_first_post() {
		if ( get_option( self::FIRST_POST_OPTION ) ) {
			return;
		}

		update_option( self::FIRST_POST_OPTION, time() );
	}

	/**
	 * When Lite created its first post, or 0 if it hasn't.
	 *
	 * Sites that posted before the option existed have no value yet, so look once
	 * for a post Lite created (blog_post() gives each one instagrate_id post meta)
	 * and save the answer, so the query never runs again. A post found this way is
	 * recorded with the time it was found.
	 *
	 * @return int
	 */
	public static function first_post_at() {
		$first_post_at = get_option( self::FIRST_POST_OPTION, false );
		if ( false !== $first_post_at ) {
			return (int) $first_post_at;
		}

		global $wpdb;
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT 1", 'instagrate_id' ) );

		$first_post_at = $post_id ? time() : 0;
		update_option( self::FIRST_POST_OPTION, $first_post_at );

		return $first_post_at;
	}

	/**
	 * Is this a screen the notice may appear on?
	 *
	 * The Dashboard, Plugins and the list of the post type Lite posts to, in the
	 * site admin only. Never Lite's settings page, which has the offer in its
	 * sidebar, and never another plugin's page.
	 *
	 * @param WP_Screen|object|null $screen
	 * @param string                $lite_post_type
	 *
	 * @return bool
	 */
	public static function is_allowed_screen( $screen, $lite_post_type ) {
		if ( empty( $screen ) || ! isset( $screen->id ) ) {
			return false;
		}

		return in_array( $screen->id, array( 'dashboard', 'plugins', 'edit-' . $lite_post_type ), true );
	}

	/**
	 * Should the notice be shown?
	 *
	 * Checks the cheap conditions first, so the one-off lookup in first_post_at()
	 * only runs for a user and a screen that would see the notice.
	 *
	 * @param bool                  $can_manage     Can the user manage options?
	 * @param WP_Screen|object|null $screen
	 * @param string                $lite_post_type The post type Lite posts to.
	 * @param mixed                 $dismissed      The user's dismissed meta.
	 *
	 * @return bool
	 */
	public static function should_show( $can_manage, $screen, $lite_post_type, $dismissed ) {
		if ( ! $can_manage || ! empty( $dismissed ) ) {
			return false;
		}

		if ( ! self::is_allowed_screen( $screen, $lite_post_type ) ) {
			return false;
		}

		return self::first_post_at() > 0;
	}

	protected static function should_display() {
		if ( null !== self::$display ) {
			return self::$display;
		}

		$post_type = get_option( 'itw_posttype' );
		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}

		self::$display = self::should_show(
			current_user_can( 'manage_options' ),
			function_exists( 'get_current_screen' ) ? get_current_screen() : null,
			$post_type,
			get_user_meta( get_current_user_id(), self::DISMISSED_META, true )
		);

		return self::$display;
	}

	public static function enqueue_script() {
		if ( ! self::should_display() ) {
			return;
		}

		wp_enqueue_script( 'itw-optin-notice', ITW_PLUGIN_URL . 'assets/js/optin-notice.js', array( 'jquery' ), ITW_PLUGIN_VERSION, true );
	}

	public static function render() {
		if ( ! self::should_display() ) {
			return;
		}

		$user        = wp_get_current_user();
		$dismiss_url = wp_nonce_url( admin_url( 'admin-post.php?action=' . self::DISMISS_ACTION ), self::DISMISS_ACTION );
		?>
		<div class="notice notice-info is-dismissible itw-optin-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( self::DISMISS_ACTION ) ); ?>">
			<p><strong><?php esc_html_e( 'Intagrate Lite:', 'instagrate-to-wordpress' ); ?></strong> <?php esc_html_e( 'Your Instagram posts are now arriving in WordPress.', 'instagrate-to-wordpress' ); ?></p>
			<p><?php esc_html_e( 'Get 20% off Intagrate Pro: videos and Reels, your full post history, multiple accounts.', 'instagrate-to-wordpress' ); ?></p>
			<form method="post" action="<?php echo esc_url( self::SUBSCRIBE_URL ); ?>" target="_blank" class="itw-optin-form">
				<p>
					<label class="screen-reader-text" for="itw-optin-email"><?php esc_html_e( 'Your Email', 'instagrate-to-wordpress' ); ?></label>
					<input type="email" id="itw-optin-email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" placeholder="<?php esc_attr_e( 'Your Email', 'instagrate-to-wordpress' ); ?>" required>
					<label class="screen-reader-text" for="itw-optin-name"><?php esc_html_e( 'First Name', 'instagrate-to-wordpress' ); ?></label>
					<input type="text" id="itw-optin-name" name="name" value="<?php echo esc_attr( $user->first_name ); ?>" placeholder="<?php esc_attr_e( 'First Name', 'instagrate-to-wordpress' ); ?>">
					<input type="hidden" name="list" value="<?php echo esc_attr( self::SUBSCRIBE_LIST ); ?>"/>
					<input type="hidden" name="subform" value="yes"/>
					<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Send me the coupon', 'instagrate-to-wordpress' ); ?>">
					<a href="<?php echo esc_url( $dismiss_url ); ?>" class="itw-optin-dismiss"><?php esc_html_e( 'No thanks', 'instagrate-to-wordpress' ); ?></a>
				</p>
				<p class="description"><?php esc_html_e( "We don't do spam and you can unsubscribe with 1-click anytime.", 'instagrate-to-wordpress' ); ?></p>
			</form>
			<p class="itw-optin-thanks" hidden><?php esc_html_e( 'Thanks! Check your inbox and confirm your email address, then we’ll send your coupon.', 'instagrate-to-wordpress' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Dismiss the notice for the current user, for good.
	 *
	 * Called by admin-ajax from the notice's close button and form, and through
	 * admin-post.php from the "No thanks" link when JavaScript is off.
	 */
	public static function dismiss() {
		$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

		if ( $is_ajax ) {
			check_ajax_referer( self::DISMISS_ACTION );
		} else {
			check_admin_referer( self::DISMISS_ACTION );
		}

		update_user_meta( get_current_user_id(), self::DISMISSED_META, time() );

		if ( $is_ajax ) {
			wp_send_json_success();
		}

		$referer = wp_get_referer();
		wp_safe_redirect( $referer ? $referer : admin_url() );
		exit;
	}

	/**
	 * Remove the option and every user's dismissal, on uninstall.
	 */
	public static function uninstall() {
		delete_option( self::FIRST_POST_OPTION );
		delete_metadata( 'user', 0, self::DISMISSED_META, '', true );
	}
}
