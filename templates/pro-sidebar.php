<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="ipp_sidebar" class="postbox-container">

	<div class="metabox-holder">

		<div class="meta-box-sortables ui-sortable">

			<div id="images" class="postbox">

				<h3 class='hndle'><span><?php esc_html_e( 'Upgrade', 'instagrate-to-wordpress' ); ?></span>
				</h3>

				<div class="igp-sidebar">

					<a class="igp-banner" href="https://intagrate.io/?utm_source=insideplugin&amp;utm_medium=web&amp;utm_content=sidebar&amp;utm_campaign=intagrate-lite-plugin">
						<img src="<?php echo esc_url( instagrate_to_wordpress::image('pro-banner.png' ) ); ?>">
					</a>

					<div class="inside">
						<div class="igp-upgrade-details">

							<ul>
								<li><?php esc_html_e( 'Videos and Reels, with a video player', 'instagrate-to-wordpress' ); ?></li>
								<li><?php esc_html_e( 'Carousels, with every photo and video', 'instagrate-to-wordpress' ); ?></li>
								<li><?php esc_html_e( 'Your full Instagram history, not just recent posts', 'instagrate-to-wordpress' ); ?></li>
								<li><?php esc_html_e( 'Multiple Instagram accounts', 'instagrate-to-wordpress' ); ?></li>
								<li><?php esc_html_e( 'Moderation, hashtag filters and scheduled posting', 'instagrate-to-wordpress' ); ?></li>
								<li><?php esc_html_e( 'Priority email support', 'instagrate-to-wordpress' ); ?></li>
							</ul>

							<p>
								<a href="https://intagrate.io/features/?utm_source=insideplugin&amp;utm_medium=web&amp;utm_content=sidebar&amp;utm_campaign=intagrate-lite-plugin"><?php esc_html_e( 'See all features', 'instagrate-to-wordpress' ); ?></a> |
								<a href="https://intagrate.io/pricing/?utm_source=insideplugin&amp;utm_medium=web&amp;utm_content=sidebar&amp;utm_campaign=intagrate-lite-plugin"><?php esc_html_e( 'Pricing', 'instagrate-to-wordpress' ); ?></a>
							</p>

						</div>

						<form method="post" action="<?php echo esc_url( Intagrate_Lite_Optin_Notice::SUBSCRIBE_URL ); ?>" target="_blank" class="subscribe block">

							<h2><?php esc_html_e( 'Get 20% Off!', 'instagrate-to-wordpress' ); ?></h2>

							<p class="intro">
								<?php esc_html_e( 'Submit your name and email and we’ll send you a coupon for 20% off your upgrade.', 'instagrate-to-wordpress' ); ?> </p>

							<div class="field">
								<input type="email" name="email" value="" placeholder="<?php esc_attr_e( 'Your Email', 'instagrate-to-wordpress' ); ?>">
							</div>

							<div class="field">
								<input type="text" name="name" value="" placeholder="<?php esc_attr_e( 'First Name', 'instagrate-to-wordpress' ); ?>">
							</div>

							<div class="field">
								<input type="text" name="LastName" value="" placeholder="<?php esc_attr_e( 'Last Name', 'instagrate-to-wordpress' ); ?>">
							</div>

							<input type="hidden" name="list" value="<?php echo esc_attr( Intagrate_Lite_Optin_Notice::SUBSCRIBE_LIST ); ?>"/>
							<input type="hidden" name="subform" value="yes"/>

							<div class="field submit-button">
								<input type="submit" class="button" value="<?php esc_attr_e( 'Send me the coupon', 'instagrate-to-wordpress' ); ?>">
							</div>

							<p class="promise">
								<?php esc_html_e( "We don't do spam and you can unsubscribe with 1-click anytime.", 'instagrate-to-wordpress' ); ?> </p>
						</form>
					</div>
				</div>

			</div>

		</div>

	</div>

</div>