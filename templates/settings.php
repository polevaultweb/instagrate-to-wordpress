<!-- BEGIN ipp_content_left -->
<div id="ipp_content_left" class="postbox-container">

	<!-- BEGIN metabox-holder -->
	<div class="metabox-holder">

		<!-- BEGIN meta-box-sortables ui-sortable -->
		<div class="meta-box-sortables ui-sortable">

			<form name="itw_form" method="post" autocomplete="off" action="<?php echo str_replace( '%7E', '~', ITW_RETURN_URI ); ?>">
				<input type="hidden" name="itw_hidden" value="Y">

				<!-- BEGIN wordpress -->
				<div id="wordpress" class="postbox">

					<div class="handlediv" title="Click to toggle">
						<br>
					</div>

					<?php echo "<h3 class='hndle'><span>" . __( 'Settings', 'itw_trdom' ) . "</span></h3>"; ?>

					<!-- BEGIN inside -->
					<div class="inside">
						<h4>Last Instagram Image</h4>

						<p class="itw_info">All Images after this image will get auto posted. Select to retrospectively post images from your feed.</p>

						<p><label class="textinput">Last Image:</label>
							<?php

							if ( isset( $_POST['itw_manuallstid'] ) ) {
								$manuallstid = $_POST['itw_manuallstid'];
							}

							foreach ( $feed->data as $item ):

								$title = ( isset( $item->caption ) ? $item->caption : "" );
								$title = instagrate_to_wordpress::strip_title( $title );
								$title = itw_truncateString( $title, 80 );
								$id    = $item->id;

								$selected = '';

								if ( $manuallstid == $id ) {
									$selected = "selected='selected'";
								}

								$options[] = "<option value='{$id}' $selected >{$title}</option>";

							endforeach; ?>

							<select name="itw_manuallstid" class="img_select">
								<?php echo implode( "\n", $options ); ?>
							</select>
						</p>
						<h4>WordPress Post</h4>

						<p class="itw_info">Default WordPress post settings</p>

						<p>
							<label class="textinput">Image Size:</label><input type="text" name="itw_imagesize" value="<?php echo $imagesize; ?>">
						</p>

						<p>
							<label class="textinput">Image CSS Class:</label><input type="text" name="itw_imageclass" value="<?php echo $imageclass; ?>">
						</p>

						<p>
							<input type="checkbox" name="itw_imagelink" <?php if ( $imagelink == true ) {
								echo 'checked="checked"';
							} ?> /> Wrap Image in Link to Image
						</p>

						<p class="itw_info">Configure how the image is stored and presented</p>

						<p><label class="textinput">Select Image Saving:</label>
							<select name="itw_imagesave">
								<option <?php if ( $imagesave == 'link' ) {
									echo 'selected="selected"';
								} ?> value="link">Link to Instagram Image
								</option>
								<option <?php if ( $imagesave == 'save' ) {
									echo 'selected="selected"';
								} ?> value="save">Save Image to Media Library
								</option>
							</select>
						</p>

						<p><label class="textinput">Featured Image Config:</label>
							<select name="itw_imagefeat">
								<option <?php if ( $imagefeat == 'nofeat' ) {
									echo 'selected="selected"';
								} ?> value="nofeat">No Featured Image
								</option>
								<option <?php if ( $imagefeat == 'featand' ) {
									echo 'selected="selected"';
								} ?> value="featand">Featured and Post Image
								</option>
								<option <?php if ( $imagefeat == 'featonly' ) {
									echo 'selected="selected"';
								} ?> value="featonly">Featured Only
								</option>
							</select>
						</p>

						<p><label class="textinput">Post Category:</label>

							<?php $args = array(


								'selected'         => $postcats,
								'include_selected' => true,
								'hide_empty'       => 0,
								'orderby'          => 'name',
								'order'            => 'ASC',
								'name'             => 'itw_postcats',
							);

							wp_dropdown_categories( $args ); ?>
						</p>
						<p><label class="textinput">Post Author:</label>
							<?php $args = array(


								'selected'         => $postauthor,
								'include_selected' => true,
								'name'             => 'itw_postauthor',

							);
							wp_dropdown_users( $args ); ?> </p>

						<p><label class="textinput">Post Format:</label>
							<?php
							$output = '<select class="pvw-input" name="itw_postformat">';

							if ( current_theme_supports( 'post-formats' ) ) {

								$post_formats = get_theme_support( 'post-formats' );
								if ( is_array( $post_formats[0] ) ) {

									$output       .= '<option value="0">Standard</option>';
									$select_value = $postformat;

									foreach ( $post_formats[0] as $option ) {

										$selected = '';

										if ( $select_value != '' ) {
											if ( $select_value == $option ) {
												$selected = ' selected="selected"';
											}
										} else {
											if ( isset( $value['std'] ) ) {
												if ( $value['std'] == $option ) {
													$selected = ' selected="selected"';
												}
											}
										}

										$output .= '<option' . $selected . '>';
										$output .= $option;
										$output .= '</option>';

									}

								} else {

									$output .= '<option>';
									$output .= 'Standard';
									$output .= '</option>';

								}

							} else {

								$output .= '<option>';
								$output .= 'Standard';
								$output .= '</option>';

							}

							$output .= '</select></p>';

							echo $output;
							?>

						<p><label class="textinput">Post Date:</label>
							<select name="itw_post_date">
								<option <?php if ( $postdate == 'now' ) {
									echo 'selected="selected"';
								} ?> value="now">Date at Posting
								</option>
								<option <?php if ( $postdate == 'instagram' ) {
									echo 'selected="selected"';
								} ?> value="instagram">Instagram Image Created Date
								</option>
							</select>
						</p>

						<p><label class="textinput">Post Status:</label>
							<select name="itw_poststatus">
								<option <?php if ( $poststatus == 'publish' ) {
									echo 'selected="selected"';
								} ?> value="publish">Publish
								</option>
								<option <?php if ( $poststatus == 'draft' ) {
									echo 'selected="selected"';
								} ?> value="draft">Draft
								</option>
							</select>
						</p>

						<p><label class="textinput">Custom Post Type:</label>
							<?php $output = '<select class="pvw-input"  name="itw_posttype">';

							// prepare post type filter
							$args      = array(
								'public'  => true,
								'show_ui' => true,
							);
							$posttypes = get_post_types( $args, 'objects' );

							$select_value = $posttype;

							foreach ( $posttypes as $pt ) :
								if ( esc_attr( $pt->name ) == 'attachment' ) {
									continue;
								}
								$selected = '';

								if ( $select_value != '' ) {
									if ( $select_value == esc_attr( $pt->name ) ) {
										$selected = ' selected="selected"';
									}
								}

								$output .= '<option value="' . esc_attr( $pt->name ) . '"' . $selected . '>';
								$output .= $pt->labels->singular_name;
								$output .= '</option>';
							endforeach;

							$output .= '</select>';
							echo $output;
							?>

						</p>

						<p class="itw_info">Set the default post title if an Instagram image has no title. Can be overridden by the Custom Title Text.</p>
						<p>
							<label class="textinput">Default Title Text:</label><input type="text" class="body_title" name="itw_defaulttitle" value="<?php echo $defaulttitle; ?>">
							<small>eg. Instagram Image</small>
						</p>

						<p class="itw_info">If the below custom text fields are left blank, only the Instagram text and image will be used in your post. To position the Instagram data with your custom text use the syntax %%title%% and %%image%%. The %%image%% text cannot be used in the Custom Title Text, and if it doesn't appear in the Body Text the Image will appear at the end of the post body.</p>
						<p>
							<label class="textinput">Custom Title Text:</label><input type="text" class="body_title" name="itw_customtitle" value="<?php echo $customtitle; ?>">
							<small>eg. %%title%% - from Instagram</small>
						</p>

						<p>
							<label class="textinput">Custom Body Text:</label><textarea class="body_text" rows="10" name="itw_customtext"><?php echo stripslashes( $customtext ); ?></textarea>
							<small>eg. Check out this new image %%image%% from Instagram</small>
						</p>

						<h4>Advanced Settings</h4>

						<p class="itw_info">This is an advanced setting for sites using themes that do not have a separate page dedicated to posts. If in doubt do not switch on.</p>
						<p>
							<input type="checkbox" name="itw_ishome" <?php if ( isset( $is_home ) && $is_home == true ) {
								echo 'checked="checked"';
							} ?> /> Check this to bypass the is_home() check when the plugin auto posts.
						</p>

						<h4>Plugin Link</h4>

						<p class="itw_info">This will place a small link for the plugin at the bottom of the post content, eg.
							<small>Posted by
								<a href="http://wordpress.org/extend/plugins/instagrate-to-wordpress/">Instagrate to WordPress</a>
							</small>
						</p>
						<p>
							<input type="checkbox" name="itw_pluginlink" <?php if ( $pluginlink == true ) {
								echo 'checked="checked"';
							} ?> /> Show plugin link
						</p>

						<h4>Debug Mode</h4>

						<p class="itw_info">This is off by default and should only be turned on if you have a problem with the plugin and have contacted us via the
							<a href="http://www.polevaultweb.com/support/forum/instagrate-to-wordpress-plugin/">Support Forum.</a>
							We will ask you to send us the debug.txt file it creates in the plugin folder.
						</p>
						<p>
							<input type="checkbox" name="itw_debugmode" <?php if ( $debugmode == true ) {
								echo 'checked="checked"';
							} ?> /> Enable Debug Mode
						</p>

						<p class="submit">
							<input type="submit" class="button-primary" name="Submit" value="<?php _e( 'Update Options', 'ipp_trdom' ) ?>" />

						</p>
			</form>

			<!-- END inside -->
		</div>

		<!-- END wordpress -->
	</div>

	<!-- END meta-box-sortables ui-sortable -->
</div>

<!-- END metabox-holder -->
</div>

<!-- END ipp_content_left -->
</div>