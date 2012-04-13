<?php

// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();
	
// Delete option from option table
delete_option('_user_rank_comments_fields');	
delete_option('_user_rank_comments_filter');	