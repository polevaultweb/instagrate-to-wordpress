<div id="ipp_content_right" class="postbox-container">
	<div class="metabox-holder">
		<div class="meta-box-sortables ui-sortable">
			<div id="images" class="postbox">
				<h3 class='hndle'><span>Instagram Feed -<small> Most recent at top</small></span>
				</h3>
				<div class="inside">
					<?php foreach ( $feed->data as $item ): ?>
						<?php
						$title = ( isset( $item->caption ) ? $item->caption : "" );
						$title       = instagrate_to_wordpress::strip_title( $title );
						$title       = itw_truncateString( $title, 80 );

						?>

						<div class="image_left">
							<a class="feed_image" href="#">
														<span class="overlay">
															<span class="caption">
																<?php echo $title ?><br />

															</span>
														</span>
								<img src="<?php echo $item->media_url; ?>" alt="<?php echo $title ?>" /><br />
							</a>
						</div>

					<?php endforeach; ?>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>