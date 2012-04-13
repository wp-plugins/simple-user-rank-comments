=== Simple User Rank Comments ===
Contributors: GeekPress
Tags: paginate, users, user, rank, user rank, comments, comment
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TSGWZURGHBRCA
Requires at least: 2.7
Tested up to: 3.3.1
Stable tag: 1.0

Create and display user rank titles based on there comment count.

== Description ==

Simple User Rank Comments allow to create and display user rank based on there comment count. 

The rank is auto display after the pseudo in comments.

You can add manually the user rank in your comment's template file with this code:

	<?php if(function_exists('get_user_rank')) {
		echo get_user_rank();
	} ?> 

Translation: English, French

== Installation ==

1. Upload the complete `simple-user-rank-comments` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'All Ranks' under the 'Comments' tab and configure the plugin


= Usage for developpers =

You can add manually the user rank in your comment's template file with this code:

	<?php if(function_exists('get_user_rank')) {
		echo get_user_rank();
	} ?> 


== Screenshots ==

1. Admin Section

== Changelog ==

= 1.0 =
* Initial release.
