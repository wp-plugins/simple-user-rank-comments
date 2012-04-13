<?php

/*
Plugin Name: Simple User Rank Comments
Plugin URI: http://www.geekpress.fr/wordpress/extension/simple-user-ranks-comments-attribuer-rangs-membres-1144/
Description: Create and display user rank titles based on there comment count.
Version: 1.0
Author: GeekPress
Author URI: http://www.geekpress.fr/
Text Domain: simple-user-rank-comments
Domain Path: /languages/

Copyright 2011 Jonathan Buttigieg
	
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

class Simple_User_Rank_Comments {
	
	private $options 			= array(); // Set $options in array
	private $fields 			= array(); // Set $fields in array
	private $filter				= ''; // Set $filter in array
	private $settings 			= array(); // Set $setting in array
	
	
	function Simple_User_Rank_Comments()
	{
		
		// Add translations
		if (function_exists('load_plugin_textdomain'))
			load_plugin_textdomain('simple-user-rank-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
		
		
		// Add menu page
		add_action('admin_menu', array(&$this, 'add_submenu'));
		
		// Add link "Settins" in plugin list
		add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );
		
		// Settings API
		add_action('admin_init', array(&$this, 'register_setting'));
		
		// Set cache
		add_action('comment_post', array(&$this, 'delete_cache_user_rank'), 10, 2);
		
		// Check if they empty fields
		$this->check_empty_fields();
		
		// load the values recorded
		$this->fields = get_option('_user_rank_comments_fields');
		$this->filter = get_option('_user_rank_comments_filter');
		
		if( intval($this->filter) == 1 ) {
			add_filter( 'get_comment_author_link', array(&$this, 'attach_rank_to_author') );
		}
		
		//tell wp what to do when plugin is activated and deactivated
		if (function_exists('register_activation_hook') && !$this->fields)
			register_activation_hook(__FILE__, array(&$this, 'activate'));
		
	}
	
	
	/**
	 * method plugin_action_links
	 *
	 * This function add link "Settins" in plugin list.
	 *
	 * @since 1.0
	**/
	function plugin_action_links( $links, $file )
	{
		if ( strstr( __FILE__, $file ) != '' )
			array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=simple_user_rank_comments_settings' ) . '">' . __( 'Settings' ) . '</a>' );
		
		return $links;
	}
	
	/**
	 * method deactivate
	 *
	 * This function is called when plugin is desactivated.
	 *
	 * @since 1.0
	**/
	function activate() 
	{
		$this->fields = array(
							array(
								'name'		=> 'Reader',
								'count'		=> 0
							),
							array(
								'name'		=> 'Commentator',
								'count'		=> 10
							)
		);
		
		add_option('_user_rank_comments_fields', $this->fields);	
	}
	
	
	/**
	 * method attach_rank_to_author
	 *
	 * This function is called to attach rank in author
	 *
	 * @since 1.0
	**/
	function attach_rank_to_author( $author ) 
	{
		return $author . ' - <small>' . __('Rank', 'simple-user-rank-comments') .' : ' . esc_html( get_user_rank() ) . '</small>';
	}
	
	
	/*
	 * method get_settings
	 *
	 * @since 1.0
	*/
	function get_settings()
	{
		
		// Check if $this->fields is not empty
		if( !$this->fields ) return;
		
		foreach( $this->fields as $key => $row )
		{

			$this->settings[$key] = array(
				'name'     	=> $row['name'],
				'count'		=> $row['count']
			);
		}
		
	}
	
	
	/**
	 * method display_settings
	 *
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	 function display_settings( $args = array() ) {
		
		extract( $args );
		
		global $i;
		$i = (int)$i;
		$disabled = ($i==0) ? 'disabled="disabled"' : '';
		
 		echo '<div><label for="title_' . $i . '">' . __('Title', 'simple-user-rank-comments') . '</label> <input class="regular-text" type="text" id="title_' . $id . '" name="_user_rank_comments_fields[' . $i . '][name]" value="' . esc_attr( $name ) . '" style="width:200px; margin: 0 10px 10px 0" />
 			  <label for="count_' . $i . '">' . __('Comments Count', 'simple-user-rank-comments') . '</label> <input class="regular-text" type="text" id="count_' . $id . '" name="_user_rank_comments_fields[' . $i . '][count]" value="' . intval( $count ) . '" style="width:100px; margin: 0 10px 10px 0" ' . $disabled . ' />
 			';
 			
 		if( $i >= 1 )
 			echo '<a href="#" class="help deleteRow">' . __('Remove', 'simple-user-rank-comments') . '</a><br/></div>';
 		
 		$i++;
	}

	
	/**
	 * method register_setting
	 *
	 * Register settings with the WP Settings API
	 *
	 * @since 1.0
	 */	
	function register_setting() 
	{
		register_setting('_user_rank_comments', '_user_rank_comments_fields', array(&$this, 'validate_settings'));
		register_setting('_user_rank_comments', '_user_rank_comments_filter');	
	}
	
	
	/**
	*  method validate_settings
	*
	* @since 1.0
	*/
	function validate_settings( $input ) {
		
		for ( $i=0; $i <= count($input)-1; $i++ ) {
			$input[$i]['name'] = wp_strip_all_tags($input[$i]['name']);
			$input[$i]['count'] = absint($input[$i]['count']);
		}
		
		uasort ($input, array(&$this, 'compare'));
		
		return $input;
	}
	
	
	/**
	*  method validate_settings
	*
	* @since 1.0
	*/
	function compare($t1,$t2)
	{
	     return $t1['count'] - $t2['count'];
	}
	
	
	/**
	*  method check_empty_fields
	*
	* @since 1.0
	*/
	function check_empty_fields() {
		
		$fields = get_option('_user_rank_comments_fields');
				 
		if( !$fields ) return;
		
		foreach( $fields as $key => $row ) {
			if( empty($row['name']) || $row['count'] < 0 )
				unset($fields[$key]);
		}
		
		uasort ($fields, array(&$this, 'compare'));
		
		// Update the new values
		update_option('_user_rank_comments_fields', $fields);
	}
	
	
	/**
	*  method add_submenu
	*
	* @since 1.0
	*/	
	function add_submenu() 
	{
		
		// Add submenu in menu "Settings"
		add_comments_page( 'Simple User Rank Comments', __('All Ranks', 'simple-user-rank-comments'), 'manage_options', 'simple_user_rank_comments_settings', array(&$this, 'display_page') );
	}
	
	/**
	*  method display_page
	*
	* @since 1.O
	*/
	function display_page() 
	{ 
		?>
		
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2>Simple User Rank Comments</h2>
			
			<form method="post" action="options.php">
				
				<h3><?php _e('General Settings', 'simple-user-rank-comments'); ?></h3>
				
				<?php settings_fields('_user_rank_comments'); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Check to auto display rank in comments', 'simple-user-rank-comments') ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e('Check to auto display rank in comments', 'simple-user-rank-comments') ?></span></legend>
								<label for="_user_rank_comments_filter">
									<input type="checkbox" <?php checked(1, (int)$this->filter, true); ?> value="1" id="_user_rank_comments_filter" name="_user_rank_comments_filter"> <?php _e('Add rank after the user pseudo in comments', 'simple-user-rank-comments') ?><br/>
									<span class="description"><?php _e('If not checked, you can add <strong><code>&lt;?php if( function_exists(\'get_user_rank\') ) echo get_user_rank(); ?&gt;</code></strong> in your template file.', 'simple-user-rank-comments'); ?></span>
								</label>
								</br>
								
							</fieldset>
						</td>
					</tr>
				</table>
				
		    	<table class="form-table">
		    		<tr valign="top">
		    			<th scope="row"><label for="default"><?php _e('List of Ranks', 'simple-user-rank-comments'); ?></label></th>
			    		<td>	
			    		<?php
			    		if( $this->fields ) {
			    		
				    		// Get the configuration of fields
							$this->get_settings();
							
							// Generate fields
							foreach ( $this->settings as $key => $setting ) {
								$this->display_settings( $setting );
							}
						}
						?>
			    		</td>
		    		</tr>
		    	</table>
  				<p>
  					<button id="addRow"  class="button button-secondary button-highlighted"><?php _e('Add a new rank', 'simple-user-rank-comments'); ?></button>
  				</p>
  				<?php submit_button( __('Save Changes') ); ?>
			</form>
		</div>
		
		<script type="text/javascript">
			jQuery(function(){
	
				/* Add field */
				jQuery('#addRow').click(function() {
					
					var length = jQuery('.form-table:last div').length;
					
					/* Clone last input */
					jQuery('.form-table:last tr td').append('<div><label for="title_'+length+'"><?php _e('Title', 'simple-user-rank-comments'); ?></label> <input class="regular-text" id="title_'+length+'" name="_user_rank_comments_fields['+length+'][name]" value="" type="text" style="width:200px; margin: 0 10px 10px 0"> <label for="count_'+length+'"><?php _e('Comments Count', 'simple-user-rank-comments'); ?></label> <input class="regular-text" id="count_'+length+'" name="_user_rank_comments_fields['+length+'][count]" value="" type="text" style="width:100px; margin: 0 10px 10px 0"> <a href="#" class="help deleteRow"><?php _e('Remove', 'simple-user-rank-comments'); ?></a><br/></div>' );
					
					return false;
				});
				
				/* Delete Field */
			 	 jQuery('.deleteRow').live('click', function() {
			 		jQuery(this).parent('div').remove();
			 		return false;
			 	 });
			});
		</script>
		
	<?php
	}
	
	
	/**
	*  method delete_cache_user_rank
	*
	* @since 1.0
	*/
	
	function delete_cache_user_rank( $comment_id, $comment_approved )
	{
		if( $comment_approved == 1 ) {
			delete_transient( 'user_rank_comments_' . substr(md5( strtolower(get_comment_author_email( $comment_id )) ),0,8) );
		}
	}
}

// Start this plugin once all other plugins are fully loaded
global $Simple_User_Rank_Comments; $Simple_User_Rank_Comments = new Simple_User_Rank_Comments();


if( !function_exists('get_user_rank') ) {

	function get_user_rank() 
	{	
		if( !$ranks = get_option('_user_rank_comments_fields') ) return false;
		
		$count = get_transient( 'user_rank_comments_' . substr(md5( strtolower(get_comment_author_email( $comment_id )) ),0,8) );
		
		if ( false === $count ) {
			
			global $wpdb;
			$count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(comment_ID) 
													  FROM ' . $wpdb->comments. ' 
													  WHERE comment_author_email = %s
													  	AND comment_approved = 1'
													  	, 
													  	get_comment_author_email()
													  	
													) 
											);
			set_transient( 'user_rank_comments_' . substr(md5( strtolower(get_comment_author_email( $comment_id )) ),0,8), $count );
		} 
		
		
		foreach( $ranks as $row ) {
			if( $count >= (int)$row['count'] )
				$rank = $row['name'];
			else break;
		}
		return $rank;
	}
}