<?php
/*  
Plugin Name: Intagrate Lite
Plugin URI: https://intagrate.io
Description: Plugin for automatic posting of Instagram images into a WordPress blog.
Author: polevaultweb
Version: 1.3.5
Author URI: https://polevaultweb.com/

Copyright 2012  polevaultweb  (email : info@polevaultweb.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

//plugin version
define( 'ITW_PLUGIN_VERSION', '1.3.5' );
define( 'ITW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ITW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ITW_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'ITW_PLUGIN_SETTINGS', 'instagratetowordpress' );
define( 'ITW_RETURN_URI', strtolower( admin_url() . 'options-general.php?page=' . ITW_PLUGIN_SETTINGS ) );

if ( ! class_exists( "instagrate_to_wordpress" ) ) {


	class instagrate_to_wordpress {

		/* Plugin loading method */
		public static function load_plugin() {
						//settings menu
			add_action( 'admin_menu', get_class() . '::register_settings_menu' );
			//settings link
			add_filter( 'plugin_action_links', get_class() . '::register_settings_link', 10, 2 );
			//styles and scripts
			add_action( 'admin_init', get_class() . '::register_styles' );
			//register upgrade check function
			add_action( 'admin_init', get_class() . '::upgrade_check' );
			//register uninstall hook
			register_uninstall_hook( __FILE__, get_class() . '::plugin_uninstall' );

			//add notices for prechecks
			add_action( 'admin_notices', get_class() . '::plugin_admin_notice' );

			//register the listener function
			add_action( 'template_redirect', get_class() . '::auto_post_images' );

			itw_Instagram::load_admin();
		}

		/* Add menu item for plugin to Settings Menu */
		public static function register_settings_menu() {

			add_options_page( 'Intagrate Lite', 'Intagrate Lite', 'manage_options', ITW_PLUGIN_SETTINGS, get_class() . '::settings_page' );

		}

		public static function image( $file ) {
			return plugin_dir_url( __FILE__ ) . '/images/' . $file;
		}

		/* Add settings link to Plugin page */
		public static function register_settings_link( $links, $file ) {

			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = ITW_PLUGIN_BASE;
			}

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="options-general.php?page=' . ITW_PLUGIN_SETTINGS . '">' . __( 'Settings', ITW_PLUGIN_SETTINGS ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;

		}

		/* Register custom stylesheets and script files */
		public static function register_styles() {

			if ( isset( $_GET['page'] ) && $_GET['page'] == ITW_PLUGIN_SETTINGS ) {

				//register styles
				wp_register_style( 'itw_style', ITW_PLUGIN_URL . 'css/style.css' );

				//enqueue styles	
				wp_enqueue_style( 'itw_style' );
				wp_enqueue_style( 'dashboard' );
				//enqueue scripts
				wp_enqueue_script( 'dashboard' );

			}

		}

		/* Register custom upgrade check function */
		public static function upgrade_check() {

			$saved_version   = get_option( 'itw_version' );
			$current_version = isset( $saved_version ) ? $saved_version : 0;

			if ( version_compare( $current_version, ITW_PLUGIN_VERSION, '==' ) ) {
				return;
			}

			//specific version checks on upgrade
			if ( version_compare( $current_version, '1.1.0', '<' ) ) {

				//set new defaults

				//set image link to true
				update_option( 'itw_imagelink', true );
				//set debug mode to false
				update_option( 'itw_debugmode', false );
				//set image saving
				update_option( 'itw_imagesave', 'link' );
				//set image featured option
				update_option( 'itw_imagefeat', 'nofeat' );
				//set image date
				update_option( 'itw_post_date', 'now' );
				//set post format
				update_option( 'itw_postformat', 'Standard' );
				//make sure the processing transient is set and reset for new and beta testers
				set_transient( 'itw_posting', 'done', 60 * 5 );
			}

			//specific version checks on upgrade
			if ( version_compare( $current_version, '1.1.1', '<' ) ) {

				//new defaults

				//set post status
				update_option( 'itw_poststatus', 'publish' );

			}

			if ( version_compare( $current_version, '1.1.3', '<' ) ) {

				//new defaults

				//repair post status from last release
				if ( get_option( 'itw_poststatus' ) != 'draft' ) {

					update_option( 'itw_poststatus', 'publish' );

				}
				//post type
				update_option( 'itw_posttype', 'post' );

			}
			if ( version_compare( $current_version, '1.1.4', '<' ) ) {

				//new defaults

				//set default title
				update_option( 'itw_defaulttitle', 'Instagram Image' );

			}
			if ( version_compare( $current_version, '1.2', '<' ) ) {

				//set default is_home override
				update_option( 'itw_ishome', false );

			}

			if ( version_compare( $current_version, '1.2.5', '<' ) ) {
				// remove old links in posts
				self::remove_links();
			}


			//update the database version
			update_option( 'itw_version', ITW_PLUGIN_VERSION );

		}

		public static function remove_links() {
			global $wpdb;
			$link             = '<!-- This post is created by Intagrate Lite, a WordPress Plugin by polevaultweb.com - http://www.polevaultweb.com/plugins/instagrate-to-wordpress/ -->';
			$post_content_sql = "UPDATE $wpdb->posts SET `post_content` = replace(post_content, '{$link}', '');";
			// run the sql
			$wpdb->query( $post_content_sql );
		}

		/* Register custom uninstall function */
		public static function plugin_uninstall() {

			//delete options
			delete_option( 'itw_version' );
			delete_option( 'itw_last_run' );
			delete_option( 'itw_accesstoken' );
			delete_option( 'itw_username' );
			delete_option( 'itw_userid' );
			delete_option( 'itw_manuallstid' );
			delete_option( 'itw_configured' );
			delete_option( 'itw_manuallstid' );
			delete_option( 'itw_imagesize' );
			delete_option( 'itw_imageclass' );
			delete_option( 'itw_imagelink' );
			delete_option( 'itw_postcats' );
			delete_option( 'itw_postauthor' );
			delete_option( 'itw_postformat' );
			delete_option( 'itw_post_date' );
			delete_option( 'itw_customtitle' );
			delete_option( 'itw_customtext' );
			delete_option( 'itw_pluginlink' );
			delete_option( 'itw_imagesave' );
			delete_option( 'itw_imagefeat' );
			delete_option( 'itw_debugmode' );
			delete_option( 'itw_poststatus' );
			delete_option( 'itw_posttype' );
			delete_option( 'itw_defaulttitle' );
			delete_option( 'itw_ishome' );

			//remove hooks
			remove_action( 'template_redirect', get_class() . '::auto_post_images' );

		}

		protected static function maybe_display_basic_display_reconnect_notice() {
			if ( isset( $_GET['page'] ) && $_GET['page'] == ITW_PLUGIN_SETTINGS ) {
				// No need to show on the plugin page
				return;
			}

			$expires = get_option( 'itw_accesstoken_expires');
			if ( $expires ) {
				// Connected to the Basic Display API
				return;
			}

			$token = get_option( 'itw_accesstoken');
			if ( ! $token ) {
				// Not even connected
				return;
			}

			$settings_link = '<a href="options-general.php?page=' . ITW_PLUGIN_SETTINGS . '">' . __( 'reconnecting', ITW_PLUGIN_SETTINGS ) . '</a>';
			?>
			<div class="notice notice-error">
				<p><strong>Intagrate Lite</strong> &mdash; <?php printf( __( 'Instagram have updated their API and the plugin needs %s to continue to post images.' ), $settings_link );  ?></p>
			</div>
			<?php
		}

		/* Display check for user to make sure a blog page is selected */
		public static function plugin_admin_notice() {
			self::maybe_display_basic_display_reconnect_notice();
			if ( isset( $_GET['page'] ) && $_GET['page'] == ITW_PLUGIN_SETTINGS ) {

				if ( 'page' == get_option( 'show_on_front' ) ) {

					if ( 0 == get_option( 'page_for_posts' ) ) {

						echo '<div class="updated">
								<p>You must select a page to display your posts in <a href="' . home_url() . '/wp-admin/options-reading.php">Settings -> Reading</a></p>
							</div>';

					}

				}

				/* Display check to see if cURL exists */
				if ( ! function_exists( 'curl_init' ) ) {

					echo '<div class="error">
								<p>This plugin requires the cURL PHP extension to be installed.</p>
							</div>';

				}

			}
		}

		/* Plugin debug functions */
		public static function plugin_debug_write( $string ) {

			//Set the filepath and filename for the WP Hook Sniff text report */
			$itw_debug_path_file = ITW_PLUGIN_PATH . "debug.txt";

			$fh = fopen( $itw_debug_path_file, "a" ) or die( "can't open file" );
			fwrite( $fh, $string );
			fclose( $fh );

		}

		/* Register default options for plugin link, author, category, post type */
		public static function set_default_options( $lastid ) {


			//update manual last id
			//$manuallstid= '';
			//$manuallstid= get_option('itw_manuallstid');

			if ( ! get_option( 'itw_manuallstid' ) ) {
				update_option( 'itw_manuallstid', $lastid );
			}

			$configured = '';
			$configured = get_option( 'itw_configured' );

			if ( $configured != 'Installed' ) {


				//update_option('itw_manuallstid', $lastid);
				update_option( 'itw_ishome', false );
				update_option( 'itw_defaulttitle', 'Instagram Image' );
				//Set plugin link to false
				update_option( 'itw_pluginlink', false );
				//set image link to true
				update_option( 'itw_imagelink', false );
				//set debug mode as off by default
				update_option( 'itw_debugmode', false );
				//set image saving
				update_option( 'itw_imagesave', 'link' );
				//set image featured option
				update_option( 'itw_imagefeat', 'nofeat' );
				//set post format
				update_option( 'itw_postformat', 'Standard' );
				//set post status
				update_option( 'itw_poststatus', 'publish' );
				//set post type
				update_option( 'itw_posttype', 'post' );
				//Set author
				$current_user = wp_get_current_user();
				$username     = $current_user->ID;
				//print $username;
				update_option( 'itw_postauthor', $username );

				$cat = 1;

				//set cats as earliest cat id
				$args = array(
					'type'         => 'post',
					'child_of'     => 0,
					'parent'       => '',
					'orderby'      => 'id',
					'order'        => 'ASC',
					'hide_empty'   => 1,
					'hierarchical' => 1,
					'exclude'      => '',
					'include'      => '',
					'number'       => 1,
					'taxonomy'     => 'category',
					'pad_counts'   => false,
				);

				$categories = get_categories( $args );
				foreach ( $categories as $cats ) {

					$cat = $cats->cat_ID;

				}
				update_option( 'itw_postcats', $cat );
				//set post date 
				update_option( 'itw_post_date', 'now' );

			}

		}

		/**
		 * @param itw_Instagram $instagram
		 * @param $token
		 * @param $user_id
		 * @param $starting_id
		 *
		 * @return array
		 */
		protected static function get_user_media( $instagram, $token, $user_id, $starting_id ) {
			$images = array();

			$data = $instagram->get_user_media($token, $user_id );
			if ( empty( $data ) ) {
				return $images;
			}

			foreach ( $data->data as $item ) {
				if ( $item->id === $starting_id ) {
					return $images;
				}

				$images[] = $item;
			}

			$url = isset( $data->paging->next ) ? $data->paging->next : null;

			while ( ! empty( $url ) ) {
				$data = $instagram->http()->do_http_request( $token, '', '', $url );
				if ( empty( $data ) ) {
					return $images;
				}

				foreach ( $data->data as $item ) {
					if ( $item->id === $starting_id ) {
						return $images;
					}

					$images[] = $item;
				}

				$url = isset( $data->paging->next ) ? $data->paging->next : null;
			}

			return $images;
		}

		protected static function get_access_token() {
			return ( new itw_Instagram() )->get_access_token();
		}

		/* Instagram post feed array */
		public static function get_images() {

			$images = array();

			//get access token
			$access_token = self::get_access_token();

			if ( $access_token ) {

				//get current last id
				$manuallstid = get_option( 'itw_manuallstid' );

				//get userid
				$userid = get_option( 'itw_userid' );

				$instagram = new itw_Instagram( $access_token );

				try {


					$ig_images = self::get_user_media( $instagram, $access_token, $userid, $manuallstid );

					if ( empty( $ig_images ) ) {
						return array();
					}

					foreach ( $ig_images as $image ):
						$images[] = array(
							"id"           => $image->id,
							"title"        => ( isset( $image->caption ) ? self::strip_title( $image->caption ) : "" ),
							"image_small"  => $image->media_url,
							"image_middle" => $image->media_url,
							"image_large"  => $image->media_url,
							"created"      => $image->timestamp,
						);

					endforeach;


					$orderByDate = array();

					//order array by earliest image
					foreach ( $images as $key => $row ) {
						$orderByDate[ $key ] = strtotime( $row['created'] );
					}

					array_multisort( $orderByDate, SORT_ASC, $images );

				} catch ( InstagramApiError $e ) {
					error_log( 'Intagrate Lite: ' . 'get_images - ' . $e->getMessage() . ' (' . $e->getCode() . ')' );
				}

			}

			return $images;

		}

		/* Get last ID of image array */
		public static function get_last_id( $data ) {

			$images = array();

			foreach ( $data->data as $item ):
				$images[] = array(
					"id"           => $item->id,
				);

			endforeach;

			return $images[0]["id"];

		}

		public static function instagrate_id_exists( $instagrate_id ) {

			global $wpdb;
			$result = false;

			$meta_key = 'instagrate_id';

			$meta_value = $instagrate_id;

			$count = 0;

			$count = $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value like %s ", $meta_key, '%' . $meta_value . '%' ) );


			if ( $count > 0 ) {
				$result = true;
			}

			return $result;

		}

		public static function strip_title( $title ) {


			$clean = '';

			$clean = filter_var( $title, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );

			$clean = emoji_html_stripped( $clean );
			$clean = trim( $clean );


			return $clean;

		}


		/* Main function to post Instagram images */
		public static function auto_post_images() {


			//check if date is Instagram or not
			$date_check = get_option( 'itw_post_date' );
			$debugmode  = ( get_option( 'itw_debugmode' ) ) ? get_option( 'itw_debugmode' ) : false;
			$is_home    = ( get_option( 'itw_ishome' ) ) ? get_option( 'itw_ishome' ) : false;

			if ( $date_check == false ) {
				$date_check = 'now';
			}

			//debug
			$debug = "------------------------------------------------------------------------------------------------------------------------------------------\n";
			$debug .= "Intagrate Lite - Plugin Debug Output: " . Date( DATE_RFC822 ) . "\n";
			$debug .= "PAGE LOAD " . Date( DATE_RFC822 ) . "\n";
			$debug .= "------------------------------------------------------------------------------------------------------------------------------------------\n";

			$debug .= "Home page is: " . get_home_template() . "\n";
			$debug .= "Current page is: " . get_page_template() . "\n";
			$debug .= "------------------------------------------------------------------------------------------------------------------------------------------\n";

			//check if page is blog page - only run
			if ( $is_home == true || ( $is_home == false && ( is_home() || is_category( get_option( 'itw_postcats' ) ) ) ) ) {
				//if (is_home()) {

				$ishomecheck = ( $is_home ? "set" : "not set" );
				$ishome      = ( is_home() ? 'TRUE' : 'FALSE' );
				$debug .= "--START Blog is_home() check " . $ishome . " " . Date( DATE_RFC822 ) . "\n";
				$debug .= "--CHECK is_home() override " . $ishomecheck . " " . Date( DATE_RFC822 ) . "\n";
				$debug .= "--START Auto post function START " . Date( DATE_RFC822 ) . "\n";
				$debug .= "--Marker: " . get_transient( 'itw_posting' ) . "\n";

				// Check if auto_post_process has NOT already been 
				$marker = get_transient( 'itw_posting' );

				$last_run = get_option( 'itw_last_run' );

				if ( $last_run === false ) {

					$last_run = 0;
				}

				if ( ( false === $marker || $marker != 'processing' ) && ( time() - $last_run > 60 ) ) {


					try {

						//set cache transient to mark as processing
						set_transient( 'itw_posting', 'processing', 60 * 5 );

						$manuallstid = get_option( 'itw_manuallstid' );

						//debug
						$debug .= "----START Auto post function: " . Date( DATE_RFC822 ) . "\n";
						$debug .= "----Marker: " . get_transient( 'itw_posting' ) . "\n";
						$debug .= "----Last ID:" . $manuallstid . "\n";


						$images = self::get_images();
						//$images = array_reverse($images_orig);
						//get count of array of images
						if ( ! empty( $images ) ) {

							$count = sizeof( $images );

							//debug
							$debug .= "----Count of Images: " . $count . "\n";


							//set counter
							$last_id = 0;

							//debug
							$debug .= "------START Auto post function Image Loop:  " . Date( DATE_RFC822 ) . "\n";

							//loop through array to get image data
							for ( $i = 0; $i < $count; $i++ ) {

								//debug
								$debug .= "--------" . $i . ": Loop:  " . Date( DATE_RFC822 ) . "\n";

								$img_exists       = self::instagrate_id_exists( $images[ $i ]["id"] );
								$img_exists_check = $img_exists ? 'TRUE' : 'FALSE';
								$debug .= "--------CHECK If image exists:  " . $img_exists_check . " " . Date( DATE_RFC822 ) . "\n";


								//Don't include image of $manuallstid
								if ( $images[ $i ]["id"] != $manuallstid && ! $img_exists ) {

									//debug
									$debug .= "--------Image Id:" . $images[ $i ]["id"] . " Does not equal Last Id:" . $manuallstid . "\n";

									// only allow the posting to happen if image timestamp is 2 minutes ago, to stop double posting through API
									if ( ( time() - strtotime( $images[ $i ]["created"] ) ) > 120 ) {

										//get image variables
										$title = $images[ $i ]["title"];
										$image = $images[ $i ]["image_large"];

										$last_id  = $images[ $i ]["id"];
										$image_id = $images[ $i ]["id"];

										//debug
										$debug .= "----------Auto post function Ready to Post:  " . Date( DATE_RFC822 ) . "\n";
										$debug .= "----------Title: " . $title . "\n";
										$debug .= "----------Image: " . $image . "\n";


										if ( $date_check == 'instagram' ) {

											$post_date     = strtotime( $images[ $i ]["created"] );
											$post_date     = date( 'Y-m-d H:i:s', $post_date );
											$post_date_gmt = $post_date;

										} else {

											$post_date_gmt = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) - ( ( $count - $i ) * 20 ) );
											$post_date     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) - ( ( $count - $i ) * 20 ) );

										}

										//post new images to wordpress
										$debug .= self::blog_post( $title, $image, $image_id, $post_date, $post_date_gmt );

									} else {

										//debug
										$debug .= "------END Auto post function: " . Date( DATE_RFC822 ) . "\n";
										$debug .= "------Image created within 2 minutes of posting loop\n";
										$debug .= "------Image Created:" . $images[ $i ]["created"] . "\n";
										$debug .= "------Posting Time:" . time() . "\n";

										//break out of image loop
										break;
									}

								} else {

									//transient exists already posting ignore
									//debug
									//debug
									$debug .= "--------" . $images[ $i ]["id"] . " == " . $manuallstid . "\n";
									$debug .= "--------Image Id already exists " . $img_exists_check . "\n";
									$debug .= "--------END Auto post function STOP as last ID already posted " . Date( DATE_RFC822 ) . "\n";

								}

								//set last run timestamp
								update_option( 'itw_last_run', time() );

							}


							if ( $last_id != 0 ) {
								//update last id field in database with last id of image added

								//echo '<h1>'.$images[0]["id"].'</h1>';
								//debug
								$debug .= "----------START End loop write Last Image ID: " . Date( DATE_RFC822 ) . "\n";
								$debug .= "----------First Image ID of Loop: " . $images[0]["id"] . "\n";
								$debug .= "----------Current Last ID: " . get_option( 'itw_manuallstid' ) . "\n";
								$debug .= "----------Writing Last ID " . Date( DATE_RFC822 ) . "\n";
								//update_option('itw_manuallstid', $images[0]["id"]);
								update_option( 'itw_manuallstid', $last_id );
								$debug .= "----------Written Last ID: " . get_option( 'itw_manuallstid' ) . "\n";
							}

						}

					} catch ( Exception $e ) {

						//var_dump $e;
						$debug .= "------EXCEPTION - " . $e->getMessage() . " " . Date( DATE_RFC822 ) . "\n";

					}

					//clear processing marker
					set_transient( 'itw_posting', 'done', 60 * 5 );

					//debug
					$debug .= "------END Auto post function Image Loop:  " . Date( DATE_RFC822 ) . "\n";
					$debug .= "------Marker:  " . get_transient( 'itw_posting' ) . "\n";

				} else {

					//transient exists already posting ignore
					//debug

					$debug .= "----END Auto post function failed as Transient Exists: " . $marker . " (Already posting) " . Date( DATE_RFC822 ) . "\n";
					$debug .= "----END Auto post function failed as started less than a minute since last run - " . $last_run . " Now - " . time() . "\n";

				}


				//debug
				$debug .= "--END Auto post function END " . Date( DATE_RFC822 ) . "\n";

			} else {

				//not blog page so don't run
				//debug
				$debug .= "--END Blog is_home() check FALSE " . Date( DATE_RFC822 ) . "\n";

			}

			if ( $debugmode ) {

				self::plugin_debug_write( $debug );

			}

		}

		/* Log out of instagram */
		public static function log_out() {

			update_option( 'itw_accesstoken', '' );
			update_option( 'itw_username', '' );
			update_option( 'itw_userid', '' );
			update_option( 'itw_manuallstid', '' );

		}

		/* Attach an image to the media library */
		public static function attach_image( $url, $postid ) {

			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/media.php' );


			$attach = array();

			$debug = "----------------Attaching Image:  -- " . Date( DATE_RFC822 ) . "\n";

			try {

				$debug .= '------------------URL: ' . $url . '-- ' . Date( DATE_RFC822 ) . "\n";

				$clean_url  = self::strip_querysting( $url );
				$tmp        = download_url( $url );
				$file_array = array(
					'name'     => basename( $clean_url ),
					'tmp_name' => $tmp,
				);

				// Check for download errors
				if ( is_wp_error( $tmp ) ) {
					$debug .= '------------------Download Error: ' . $url . '-- ' . Date( DATE_RFC822 ) . "\n";
					$debug .= '------------------Download Error: ' . $tmp->get_error_message() . '-- ' . Date( DATE_RFC822 ) . "\n";
					@unlink( $file_array['tmp_name'] );
					$attach[0] = 0;
				}

				$id = media_handle_sideload( $file_array, $postid );
				// Check for handle sideload errors.

				if ( is_wp_error( $id ) ) {
					$debug .= '------------------media_handle_sideload Error: ' . $url . '-- ' . Date( DATE_RFC822 ) . "\n";
					$debug .= '------------------media_handle_sideload Error: ' . $id->get_error_message() . '-- ' . Date( DATE_RFC822 ) . "\n";
					@unlink( $file_array['tmp_name'] );
					$attach[0] = 0;
				} else {

					$attach[0] = $id;
					$debug .= '------------------media_handle_sideload success - ID: ' . $id . '-- ' . Date( DATE_RFC822 ) . "\n";
				}

			} catch ( Exception $e ) {


				$debug .= '------------------CATCH media_handle_sideload ERROR: -- ' . Date( DATE_RFC822 ) . "\n";

			}

			$attach[1] = $debug;

			return $attach;
		}

		/* Remove the querystring from a URL */
		public static function strip_querysting( $url ) {

			if ( strpos( $url, '?' ) !== false ) {
				$url = substr( $url, 0, strpos( $url, '?' ) );
			}

			return $url;

		}

		/* Posting to WordPress */
		public static function blog_post( $post_title, $post_image, $image_id, $post_date, $post_date_gmt ) {


			$debug = "------------START Blog_post " . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Title: " . $post_title . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Image: " . $post_image . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Date: " . $post_date . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Date GMT: " . $post_date_gmt . ' -- ' . Date( DATE_RFC822 ) . "\n";

			$orig_title = $post_title;

			$imagesize    = get_option( 'itw_imagesize' );
			$imageclass   = get_option( 'itw_imageclass' );
			$postcats     = get_option( 'itw_postcats' );
			$postauthor   = get_option( 'itw_postauthor' );
			$postformat   = get_option( 'itw_postformat' );
			$customtitle  = get_option( 'itw_customtitle' );
			$customtext   = get_option( 'itw_customtext' );
			$pluginlink   = get_option( 'itw_pluginlink' );
			$imagelink    = get_option( 'itw_imagelink' );
			$imagesave    = get_option( 'itw_imagesave' );
			$imagefeat    = get_option( 'itw_imagefeat' );
			$poststatus   = get_option( 'itw_poststatus' );
			$posttype     = get_option( 'itw_posttype' );
			$defaulttitle = get_option( 'itw_defaulttitle' );

			//Image class
			if ( $imageclass != '' ) {
				$imageclass = 'class="' . $imageclass . '" ';
			}

			$debug .= "--------------Image Class: " . $imageclass . ' -- ' . Date( DATE_RFC822 ) . "\n";

			//Image size
			if ( $imagesize != '' ) {
				$imagesize = 'width="' . $imagesize . '" height="' . $imagesize . '" ';
			}

			$debug .= "--------------Image Size: " . $imagesize . ' -- ' . Date( DATE_RFC822 ) . "\n";

			//Custom Post Title
			if ( $customtitle != '' ) {

				$pos = strpos( strtolower( $customtitle ), '%%title%%' );
				if ( $pos === false ) {

					//no %%title%% found so put instagram title after custom title
					$post_title = $customtitle;

				} else {

					//%%title%% found so replace it with instagram title
					$post_title = str_replace( "%%title%%", $post_title, $customtitle );
				}

				$debug .= "--------------Custom Ttle: " . $post_title . ' -- ' . Date( DATE_RFC822 ) . "\n";

			} else {

				if ( $post_title == '' || $post_title == null ) {

					$post_title = $defaulttitle;
				}

			}


			$debug .= "--------------Post Author: " . $postauthor . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Category: " . $postcats . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Status: " . $poststatus . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Type: " . $posttype . ' -- ' . Date( DATE_RFC822 ) . "\n";

			// Create post object
			$my_post = array(
				'post_title'    => $post_title,
				'post_content'  => '',
				'post_author'   => $postauthor,
				'post_category' => array( $postcats ),
				'post_status'   => 'draft', //$poststatus,
				'post_type'     => $posttype,
				'post_date'     => $post_date, //The time post was made.
				'post_date_gmt' => $post_date_gmt //[ Y-m-d H:i:s ] //The time post was made, in GMT.
			);


			// Insert the post into the database
			$new_post = wp_insert_post( $my_post );

			//image settings
			if ( $imagesave == 'link' ) {
				//link to instagram image
				$image = '<img src="' . $post_image . '" ' . $imageclass . ' alt="' . $post_title . '" ' . $imagesize . ' />';

			} else {

				//put image from instagram into wordpress media library and link to it.
				//load into media library
				$attach = self::attach_image( $post_image, $new_post );

				$debug .= $attach[1];

				if ( $attach[0] != 0 ) {
					$attach_id = $attach[0];

					$debug .= "--------------Attach Id: " . $attach_id . ' -- ' . Date( DATE_RFC822 ) . "\n";

					//get new shot image url from media attachment
					$post_image = wp_get_attachment_url( $attach_id );

					$debug .= "--------------Attach Post Image: " . $post_image . ' -- ' . Date( DATE_RFC822 ) . "\n";

					$image = '<img src="' . $post_image . '" ' . $imageclass . ' alt="' . $post_title . '" ' . $imagesize . ' />';

					//featured image settings
					if ( $imagefeat == 'featonly' ) {

						//featured only - only set as featured
						$image = '';

					}
				} else {
					$image = '<img src="' . $post_image . '" ' . $imageclass . ' alt="' . $post_title . '" ' . $imagesize . ' />';
				}

			}

			//image link
			if ( $imagelink && $image != '' ) {
				//add link to instagram shot
				$image = '<a href="' . $post_image . '" title="' . $post_title . '" >' . $image . '</a>';
			}


			if ( $customtext != '' ) {


				$customtext = stripslashes( htmlspecialchars_decode( $customtext ) );

				//check if %%image%% has been used 
				$pos = strpos( strtolower( $customtext ), '%%image%%' );
				if ( $pos === false ) {

					//no %%image%% found so put instagram image after custom text
					$post_body = $customtext . '<br/>' . $image;

				} else {

					//%%image%% found so replace it with instagram title
					$post_body = str_replace( "%%image%%", '<br/>' . $image . '<br/>', $customtext );
				}

				//check if %%title%% has been used
				$pos = strpos( strtolower( $customtext ), '%%title%%' );
				if ( $pos === false ) {

					//no %%title%% found so put instagram title after custom title
					$post_body = $post_body;

				} else {

					//%%title%% found so replace it with instagram title
					$post_body = str_replace( "%%title%%", $orig_title, $post_body );

				}

			} else {

				//no custom text just plain old image
				$post_body = $image;
			}

			//Plugin link credit
			if ( $pluginlink == true ) {

				$post_body = $post_body . ' <br/><small>Posted by <a href="http://wordpress.org/extend/plugins/instagrate-to-wordpress/">Intagrate Lite</a></small>';
			}

			$debug .= "--------------Post Content: " . $post_body . ' -- ' . Date( DATE_RFC822 ) . "\n";
			$debug .= "--------------Post Format: " . $postformat . ' -- ' . Date( DATE_RFC822 ) . "\n";

			$debug .= "--------------START wp_insert_post " . Date( DATE_RFC822 ) . "\n";


			//apply custom meta to make sure the image won't get duplicated 
			add_post_meta( $new_post, 'instagrate_id', $image_id );

			//apply format if not standard
			if ( $postformat != 'Standard' ) {
				set_post_format( $new_post, $postformat );
			}

			//apply featured image if needed
			if ( $imagefeat != 'nofeat' && $imagesave != 'link' ) {

				add_post_meta( $new_post, '_thumbnail_id', $attach_id );

			}

			// Update post with content
			$update_post                 = array();
			$update_post['ID']           = $new_post;
			$update_post['post_status']  = $poststatus;
			$update_post['post_content'] = $post_body;

			// Update the post into the database
			wp_update_post( $update_post );


			$debug .= "--------------END wp_insert_post " . Date( DATE_RFC822 ) . "\n";


			$debug .= "------------END blog_post" . Date( DATE_RFC822 ) . "\n";


			return $debug;

		}


		/* Plugin Settings page and settings data */
		public static function settings_page() {


			$oldplugin = 'instapost-press/instapost-press.php';


			if ( is_plugin_active( $oldplugin ) ) {
				$oldplugintest = 1;

			} elseif ( ! function_exists( 'curl_init' ) ) {

				$curltest = 1;

			} else {

				$msg_class    = 'notice updated';

				if ( $msg_class != 'itw_disconnected' ) {


					$access_token = self::get_access_token();
					$instagram    = new itw_Instagram( $access_token );

					//echo $access_token;

					if ( ! $access_token ) {
						// no access token in db

						$msg       = 'Please login securely to Instagram to authorise the plugin - ';
						$msg_class = 'notice-info notice';
						$loginUrl  = $instagram->authorizeUrl( ITW_RETURN_URI );

					} else {

						//logged in


						try {

							$username  = get_option( 'itw_username' );
							$userid    = get_option( 'itw_userid' );
							$msg       = $username;
							$msg_class = 'itw_connected notice updated';


							$feed = $instagram->get_user_media( $access_token, $userid );

							if ( $feed && isset( $feed->data ) ) {
								foreach( $feed->data as $key => $media ) {
								if ('VIDEO' === $media->media_type ) {
										$media->media_url = $media->thumbnail_url;
									}

									$feed->data[$key] = $media;
								}
							}

							if ( $feed != null ) {

									if ( isset( $_POST['itw_hidden'] ) && $_POST['itw_hidden'] == 'Y' ) {

										update_option( 'itw_configured', 'Installed' );

										$manuallstid = $_POST['itw_manuallstid'];
										update_option( 'itw_manuallstid', $manuallstid );

										$imagesize = $_POST['itw_imagesize'];
										update_option( 'itw_imagesize', $imagesize );

										$imageclass = $_POST['itw_imageclass'];
										update_option( 'itw_imageclass', $imageclass );

										if ( isset( $_POST['itw_imagelink'] ) ) {
											$imagelink = $_POST['itw_imagelink'];
											update_option( 'itw_imagelink', $imagelink );
										} else {
											delete_option( 'itw_imagelink' );
											$imagelink = false;
										}

										$postcats = $_POST['itw_postcats'];
										update_option( 'itw_postcats', $postcats );

										$postauthor = $_POST['itw_postauthor'];
										update_option( 'itw_postauthor', $postauthor );

										$postformat = $_POST['itw_postformat'];
										update_option( 'itw_postformat', $postformat );

										$postdate = $_POST['itw_post_date'];
										update_option( 'itw_post_date', $postdate );

										$customtitle = $_POST['itw_customtitle'];
										update_option( 'itw_customtitle', $customtitle );

										$customtext = htmlspecialchars( $_POST['itw_customtext'] );
										update_option( 'itw_customtext', $customtext );

										$imagesave = $_POST['itw_imagesave'];
										update_option( 'itw_imagesave', $imagesave );

										$imagefeat = $_POST['itw_imagefeat'];
										update_option( 'itw_imagefeat', $imagefeat );

										if ( isset( $_POST['itw_pluginlink'] ) ) {
											$pluginlink = $_POST['itw_pluginlink'];
											update_option( 'itw_pluginlink', $pluginlink );
										} else {
											delete_option( 'itw_pluginlink' );
											$pluginlink = false;
										}

										if ( isset( $_POST['itw_debugmode'] ) ) {

											$debugmode = $_POST['itw_debugmode'];
											update_option( 'itw_debugmode', $debugmode );

										} else {
											delete_option( 'itw_debugmode' );
											$debugmode = false;
										}

										$poststatus = $_POST['itw_poststatus'];
										update_option( 'itw_poststatus', $poststatus );

										$posttype = $_POST['itw_posttype'];
										update_option( 'itw_posttype', $posttype );

										$defaulttitle = $_POST['itw_defaulttitle'];
										update_option( 'itw_defaulttitle', $defaulttitle );

										if ( isset( $_POST['itw_ishome'] ) ) {
											$is_home = $_POST['itw_ishome'];
											update_option( 'itw_ishome', $is_home );
										} else {
											$is_home = false;
											delete_option( 'itw_ishome' );
										}


										?>

										<div class="itw_saved"><p><?php _e( 'Plugin settings saved!' ); ?></p></div>
										<div class="clear"></div>
										<?php
									} else {


										//set defaults if need
										$lastid = self::get_last_id( $feed );
										self::set_default_options( $lastid );

										$manuallstid = get_option( 'itw_manuallstid' );
										$imagesize   = get_option( 'itw_imagesize' );
										$imageclass  = get_option( 'itw_imageclass' );
										$imagelink   = get_option( 'itw_imagelink' );
										$postcats    = get_option( 'itw_postcats' );
										$postauthor  = get_option( 'itw_postauthor' );
										$postformat  = get_option( 'itw_postformat' );
										$postdate    = get_option( 'itw_post_date' );
										$customtitle = get_option( 'itw_customtitle' );
										$customtext  = get_option( 'itw_customtext' );
										$pluginlink  = get_option( 'itw_pluginlink' );
										$imagesave   = get_option( 'itw_imagesave' );
										$imagefeat   = get_option( 'itw_imagefeat' );

										$debugmode    = get_option( 'itw_debugmode' );
										$poststatus   = get_option( 'itw_poststatus' );
										$posttype     = get_option( 'itw_posttype' );
										$defaulttitle = get_option( 'itw_defaulttitle' );
										$is_home      = get_option( 'itw_ishome', false );

									}


							} else {


								$msg       = 'Error: Instagram Servers Down';
								$msg_class = 'itw_disconnected';
								$loginUrl  = 'hide';

							}

							//update_option('itw_configured', '');


						} catch ( InstagramApiError $e ) {
							error_log( 'Intagrate Lite: ' . 'settings - ' . $e->getMessage() . ' (' .$e->getCode() . ')' );
							if ( $e->getCode() == 10 && $e->getMessage() == 'Application does not have permission for this action' ) {
								update_option( 'itw_accesstoken', '' );
								update_option( 'itw_username', '' );
								update_option( 'itw_userid', '' );
								update_option( 'itw_manuallstid', '' );

								$msg       = 'You did not approve the necessary API permissions when connecting to Instagram. Please reconnect.';
								$msg_class = 'itw_disconnected';
								$loginUrl  = $instagram->authorizeUrl( ITW_RETURN_URI );

							} else if ( $e->getCode() == 190 ) {
								update_option( 'itw_accesstoken', '' );
								update_option( 'itw_username', '' );
								update_option( 'itw_userid', '' );
								update_option( 'itw_manuallstid', '' );

								$msg       = 'You did not authorize the plugin to access your Instagram account. Please reconnect.';
								$msg_class = 'itw_disconnected';
								$loginUrl  = $instagram->authorizeUrl( ITW_RETURN_URI );
							} else if ( $e->getMessage() != 'Error: Instagram Servers Down' ) {

								update_option( 'itw_accesstoken', '' );
								update_option( 'itw_username', '' );
								update_option( 'itw_userid', '' );
								update_option( 'itw_manuallstid', '' );

								$msg       = 'The Instagram Authorisation token has expired - ';
								$msg_class = 'itw_disconnected';
								$loginUrl  = $instagram->authorizeUrl( ITW_RETURN_URI );

							} else {

								$msg       = $e->getMessage();
								$msg_class = 'itw_disconnected';
								$loginUrl  = 'hide';

							}
							//die($e->getMessage())
						}
					}
				}

			}


			?>

			<!-- BEGIN Wrap -->
			<div class="wrap">
			<div class="h2_left">
				<h1 class="instagrate-icon dashicons-before dashicons-camera">Intagrate Lite</h1>
			</div>

			<?php if ( isset( $oldplugintest ) ): ?>
				<div class="clear"></div>
				<div class="itw_issue">
					<p>
						This plugin is a newer version of <b>InstaPost Press</b> which has been discontinued.</p>
					<p>Please deactivate and delete <b>InstaPost Press</b>
						<a href="<?php echo itw_pluginsURL() . '#instapost-press' ?>">here</a>.
					</p>
					<p> Once done you can configure the settings of this plugin and begin to use it!
					</p>
				</div>
			<?php elseif ( isset( $curltest ) ): ?>

			<?php else: ?>
				<?php if ( isset( $loginUrl ) ): ?>
					<div class="<?php echo $msg_class ?>">
						<p>
							<?php echo $msg ?>
							<?php if ( $loginUrl != 'hide' ): ?>
								<a href="<?php echo $loginUrl; ?>">Log in</a>
								<iframe id="logoutframe" src="https://instagram.com/accounts/
logout/" width="0" height="0"></iframe>
							<?php endif; ?>
						</p>
					</div>

					<?php include dirname( __FILE__ ) . '/templates/pro-sidebar.php'; ?>

				<?php else: ?>
					<div class="loggedin">
						<div class="itw_connected notice updated inline">
							<p>
								Connected to Instagram as <span><?php echo $msg; ?></span>

							</p>
						</div>
						<div class="logout">
							<a href="<?php echo itw_Instagram::logout_url(); ?>">Log out</a>
						</div>
					</div>
					<div class="clear"></div>

					<?php include dirname( __FILE__ ) . '/templates/pro-sidebar.php'; ?>
					<div id="ipp_content_wrap">
						<?php include dirname( __FILE__ ) . '/templates/images.php'; ?>
						<?php include dirname( __FILE__ ) . '/templates/settings.php'; ?>
					</div>
				<?php endif; ?>

				<div class="clear"></div>
				<!-- BEGIN Footer -->
				<div id="itw_footer">

					<div id="links">
						<b>Intagrate Lite</b> | We hope you enjoy the plugin |
						<a href="https://intagrate.io/?utm_source=insideplugin&amp;utm_medium=web&amp;utm_content=sidebar&amp;utm_campaign=intagrate-lite-plugin">Intagrate - more features</a> |
						<a title="Follow on Twitter" href="http://twitter.com/polevaultweb">@polevaultweb</a> |
						<a href="http://wordpress.org/extend/plugins/instagrate-to-wordpress/" title="Rate the Plugin on WordPress">Rate the Plugin ★★★★★</a> |
						<a href="http://led24.de/iconset/">16px Icons</a>
					</div>

				</div>
				<!-- END Footer -->
				<div class="clear"></div>

			<?php endif; ?>

			<!-- END Wrap -->
			</div>
			<?php

		}

	}

}

if ( class_exists( "instagrate_to_wordpress" ) ) {

	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		add_action( 'admin_notices', 'intagrate_lite_compatibility' );
	} else {
		require_once ITW_PLUGIN_PATH . 'php/instagram.php';
		require_once ITW_PLUGIN_PATH . 'php/emoji.php';

		require_once ITW_PLUGIN_PATH . 'lib/WPOAuth2.php';
		require_once ITW_PLUGIN_PATH . 'lib/TokenManager.php';
		require_once ITW_PLUGIN_PATH . 'lib/AdminHandler.php';
		require_once ITW_PLUGIN_PATH . 'lib/AccessTokenInterface.php';
		require_once ITW_PLUGIN_PATH . 'lib/AbstractAccessToken.php';
		require_once ITW_PLUGIN_PATH . 'lib/AccessToken.php';
		require_once ITW_PLUGIN_PATH . 'lib/class-http.php';
		require_once ITW_PLUGIN_PATH . 'php/class-wpoauth-access-token.php';

		// Load plugin
		instagrate_to_wordpress::load_plugin();
	}

	/**
	 * Render a notice about plugin compatibility
	 */
	function intagrate_lite_compatibility() {
		$action       = 'deactivate';
		$basename     = plugin_basename( __FILE__ );
		$nonce_action = $action . '-plugin_' . $basename;
		$page         = 'plugins';

		$deactivate_url  = wp_nonce_url( network_admin_url( $page . '.php?action=' . $action . '&amp;plugin=' . $basename ), $nonce_action );
		$deactivate_link = sprintf( '<a style="text-decoration:none;" href="%s">%s</a>', $deactivate_url, __( 'deactivate' ) );
		$text            = sprintf( __( 'Intagrate Lite requires PHP version of 5.3 or higher. Please upgrade PHP or %s the plugin to remove this notice.' ), $deactivate_link );

		printf( '<div class="notice error"><p>%s</p></div>', $text );
	}

}