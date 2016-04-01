=== Instagrate to WordPress ===
Contributors: polevaultweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R6BY3QARRQP2Q
Plugin URI: http://www.polevaultweb.com/plugins/instagrate-to-wordpress/
Author URI: http://www.polevaultweb.com/
Tags: instagram, posts, integration, automatic, post, wordpress, posting, images
Requires at least: 3.0
Tested up to: 4.1
Stable tag: 1.2.5

Integrate your Instagram images and your WordPress blog with automatic posting of new images into blog posts.

== Description ==

**[Instagrate Pro](http://www.instagrate.co.uk/)** The pro version of this plugin with many more features can be found [here](http://www.instagrate.co.uk/)

The Instagrate to WordPress plugin allows you to automatically integrate your Instagram account with your WordPress blog.  

No more manual embedding Instagram images into your posts, let this plugin take care of it all.

Install the plugin. Log in to Instagram, pick your default WordPress post settings, and you are done. Take a photo or lots on Instagram. The next time someone visits your site, a new post will be created with your each photo from Instagram. 

This plugin requires the cURL PHP extension to be installed.

Full list of features:

* Simple connection to Instagram. Login securely to Instagram to authorise this plugin to access your image data. **This plugin does not ask or store your Instagram username and password, you only log into Instagram.**
* Helpful feed of images in the admin screen.
* Option to manually set the last image in the feed, so all later images will be posted.
* Configurable post settings:
	*	Post title - default as Instagram image title. Custom title text before Instagram title, or embed the Instagram title using %%title%%.
	*	Post body text - default as Instagram image. Custom body text before Instagram image, or embed the Instagram image using %%image%%. You can also embed the %%title%%.
	* 	NEW 1.1.0: Post date can be either Instagram image date or the date at posting.
	* 	NEW 1.1.0: Image can be either saved to media library within WordPress or linked to Instagram image.
	*	NEW 1.1.0: If you save images to the media library you can now set the image as Featured.
	* 	NEW 1.1.0: Link to image setting.
	*	Image size.
	*	Image CSS class.
	* 	NEW 1.1.0: Post Format.
	*	NEW 1.1.2: Post Status. You can set posts as published or as draft.
	*	Post Category (selected from dropdown of available categories).
	*	Post Author (selected from dropdown of available authors).
	* 	Plugin link at the end of the post body text. Can be turned off.
	*	NEW 1.1.0: Debug mode setting to enable us to troubleshoot further problems with the plugin. Off by default.
	*	NEW 1.1.3: You can now set the post type, eg. post, page or custom post types, where the image will be created in. Default is Post.
	*	NEW 1.1.4: Default post title for images that have no title. Can be overridden by custom post title.
* Advanced settings:
	*	NEW 1.2: Option to override is_home() check setting on automatic posting if themes do not have a set blog page.
	
If you have any issues or feature requests please visit and use the [Support Forum](http://www.polevaultweb.com/support/forum/instagrate-to-wordpress-plugin/)

**[Instagrate Pro](http://www.instagrate.co.uk/)** The pro version of this plugin with many more features can be found [here](http://www.instagrate.co.uk/)

[Plugin Page](http://www.polevaultweb.com/plugins/instagrate-to-wordpress/) | [@polevaultweb](http://www.twitter.com/polevaultweb/) | [Donate with PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R6BY3QARRQP2Q)

== Installation ==

This plugin requires the cURL PHP extension to be installed.

This section describes how to install the plugin and get it working.

You can use the built in installer and upgrader, or you can install the plugin manually.

1. Delete any existing `instagrate-to-wordpress` folder from the `/wp-content/plugins/` directory
2. Upload `instagrate-to-wordpress` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the options panel under the 'Settings' menu and add your Instagram account details and set the rest of configuration you want.

If you have to upgrade manually simply repeat the installation steps and re-enable the plugin.

**Please note this plugin supersedes InstaPost Press, which has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin. Instagrate to WordPress has new features and will continue to be developed**

== Changelog ==

= 1.2.5 =

* Removes all HTML comment credit links from created posts

= 1.2.4 =

* Remove HTML comment credit link

= 1.2.3 =

* WordPress 4.0 compatible
* Fix - Session notice fix
* Fix - Other notices

= 1.2.2 = 

* WordPress 3.5 compatible
* Fix - Media attaching images handled better
* Improvement - Plugin posts on a category page so is_home() override isn't needed.


= 1.2.1 = 

* Further Fix - Post is only published once image is set. This is a fix for users with auto social posting plugins who weren't seeing images in their social posts. Thanks Dutch Doscher!

= 1.2 = 

* New - Option to override is_home() check setting on automatic posting if themes do not have a set blog page.
* Fix - Post is only published once image is set. This is a fix for users with auto social posting plugins who weren't seeing images in their social posts.

= 1.1.8 =

* Improvement - Check for the cURL PHP extension. This is a prerequisite of the plugin.

= 1.1.7 =

* Improvement - Custom body text now allows HTML content.

= 1.1.6 =

* Bug fix - The plugin's settings are now only visible to administrators.

= 1.1.5 =

* Bug fix - The plugin now correctly strips emojis from the Image title so they don't break the WordPress post title, but leaves alone foreign characters.

= 1.1.4 =

* New feature - Every post with an image stores the Instagram image id in the post meta. This will help stop duplicate posts. if you want to repost an image the original post needs to be deleted and removed from trash.
* Improvement - Default post title for images that have no title. Can be overridden by custom post title.
* Improvement - New method of handling Instagram authorisation to fix those users in the infinite login loop.
* Bug fix - Strips emoticons and other special characters from the Instagram image title so it won't break the post title.
* Bug fix - Better handling of Instagram API downtime.
* Bug fix - PHP notices removed.

= 1.1.3 =

* New feature - You can now set the post type, eg. post, page or custom post types, where the image will be created in. Default is Post.
* [Instagrate Pro](http://www.instagrate.co.uk) released.

= 1.1.2 =

* New feature - You can set the default post status for posts created, eg. set to 'publish' or 'draft'. Default set to 'publish'.
* New feature - Alert if blog has a static page for the homepage but doesn't have a page selected to display posts. This is needed for the plugin to work.
* Bug fix - Warning: array_multisort() [function.array-multisort] error fixed.
* Bug fix - Images that are added to media library are now automatically attached to the post in the media library.
* Bug fix - Images posted with date at time of posting now use the timezone defined in the blog's general settings.
* Bug fix - HTTPS fix - thanks [@alexbilbie](https://twitter.com/alexbilbie).
* Bug fix - Authenticating when using localhost:8888.

= 1.1.1 =

* Small release to fix readme.txt issues and links.

= 1.1.0 =

* Bug fix - resolved multiple posting issues. The plugin will only post an image from Instagram 2 minutes after creation to stop any duplicates coming through on the API. Many thanks to testers [@onlineheld](http://www.twitter.com/onlineheld), [@travelhappy](http://www.twitter.com/travelhappy), Tyler Conlon
* Bug fix - issues with logging in for some users.
* You can now set how the Instagram image is used by the plugin. New settings for saving to the media library and making featured image.
* You can now set Post Format.
* Post date can now be selected to be the Instagram image's created date or the date at posting on WordPress.
* Can now control if the image is wrapped in a link	to the image. On by default for blogs using plugins such as Lightbox and Fancybox.
* Debug mode added for troubleshooting issues with the plugin.
* Donate Link added.

= 1.0.4 =

* Bug fix - resolved WordPress forcing a re-login after trying to authenticate plugin, and never fully activating Instagrate

= 1.0.3 =

* Bug fix - resolved multiple posts for one image.
* Bug fix - resolved issues for authenticating plugin with Instagram for blogs not in root directory, eg. /blog/
* Bug fix - resolved issues where users were receiving unhandled exceptions for the plugin on their blog
* Log out button to allow you to change which Instagram account the plugin uses.
* When a custom post title is added without the %%title%% text, it no longer adds the Instagram image title as well.
* You can now use the %%title%% text within the post body.

= 1.0.2 = 

* Category dropdown in WordPress post settings now shows all categories even if no posts exist for the category. Also order by name.

= 1.0.1 =

* Change to ensure on enable all images aren't posted.

= 1.0 =

* First release, bugs expected.

== Frequently Asked Questions ==

= I have an issue with the plugin =

Please visit the [Support Forum](http://www.polevaultweb.com/support/forum/instagrate-to-wordpress-plugin/) and see what has been raised before, if not raise a new topic.

= What about the InstaPost Press plugin? =

This is the newer version of that plugin. It has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin.

= Does the plugin support WordPress Multisite? =

No, currently the plugin does not support Multisite.

= Can I use more than one Instagram account? =

No, not at the moment. The plugin only allows one Instagram account at a time.

= I have a feature request =

Please visit and add to the [Feature Requests topic](http://www.polevaultweb.com/support/topic/feature-requests/) on the support forum.

== Screenshots ==

1. Screenshot of the Instagram settings of manual last image.
2. Screenshot of the WordPress blog post settings.
3. Screenshot of the admin feed of images from Instagram.
4. Screenshot of the plugin link setting.

== Upgrade Notice ==

Please note this plugin supersedes InstaPost Press, which has been discontinued because of a naming conflict. If you installed this plugin you will need to deactivate it before you can use this new plugin. Instagrate to WordPress has new features and will continue to be developed.

== Disclaimer ==

This plugin uses the Instagram(tm) API and is not endorsed or certified by Instagram or Burbn, inc. All Instagram(tm) logoes and trademarks displayed on this website are property of Burbn, inc.