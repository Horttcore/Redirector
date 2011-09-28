<?php
/*
Plugin Name: Redirector
Plugin URL: http://www.horttcore.de/wordpress/redirector
Description: Redirect any page to an internal or external URL
Version: 2
Author: Ralf Hortt
Author URL: http://www.horttcore.de/
*/



/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists('add_action') ) :
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
else :
	// WordPress definitions
	if ( !defined('WP_CONTENT_URL') )
		define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	if ( !defined('WP_CONTENT_DIR') )
		define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	if ( !defined('WP_PLUGIN_URL') )
		define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
	if ( !defined('WP_PLUGIN_DIR') )
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
	if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
	if ( !defined('WP_LANG_DIR') )
		define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
	
	// Plugin definitions
	define( 'RH_RD_BASENAME', plugin_basename(__FILE__) );
	define( 'RH_RD_BASEDIR', dirname( plugin_basename(__FILE__) ) );
	define( 'RH_RD_TEXTDOMAIN', 'redirector' );
endif;



class Redirector {
	
	
	
	/**
	 * Constructor
	 *
	 * @author Ralf Hortt
	 **/
	function Redirector()
	{
		if ( is_admin() ) :
			add_action( 'admin_init', array(&$this, 'admin_init') );						
			add_action( 'admin_print_scripts-post.php', array(&$this, 'enqueue_script') );
			add_action( 'admin_print_styles-post.php', array(&$this, 'enqueue_style') );
			add_action( 'save_post', array(&$this, 'save_post') );
			
			register_activation_hook( __FILE__, array(&$this, 'install') );
			register_uninstall_hook( __FILE__, array(&$this, 'uninstall') );
		else :
			add_action( 'template_redirect', array(&$this, 'template_redirect') );
		endif;
	}
	
	
	
	/**
	 * Plugin initialisation
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function admin_init()
	{
		add_meta_box( 'redirect', __( 'Redirect', RH_RD_TEXTDOMAIN ), array(&$this, 'metabox'), 'page', 'side' );
	}
	
	
	
	/**
	 * Enqueue Javascript
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function enqueue_script()
	{
		wp_enqueue_script( 'redirector', WP_PLUGIN_URL . '/' . RH_RD_BASEDIR . '/javascript/redirector.js', array('jquery') );
	}
	
	
	
	/**
	 * Enqueue CSS
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function enqueue_style()
	{
		wp_enqueue_style( 'redirector', WP_PLUGIN_URL . '/' . RH_RD_BASEDIR . '/css/redirector.css' );
	}
	
	
	
	/**
	 * Backwards compability
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function install()
	{
		global $wpdb;
		$sql = "UPDATE $wpdb->postmeta SET meta_key = '_redirector' WHERE meta_key = 'redirector'";
		$wpdb->query($sql);
	}
	
	
	
	/**
	 * Redirector Meta Box
	 *
	 * @return output html
	 * @author Ralf Hortt
	 **/
	function metabox()
	{
		$redirect = get_post_meta($_GET['post'], '_redirector', TRUE);
		$redirect_url = (!is_numeric($redirect)) ? $redirect : '';
		$redirect_url = ($redirect_url != 'child') ? $redirect_url : '';
		$checked_child = ($redirect == 'child') ? 'checked="checked"' : '';
		$checked_random = ($redirect == 'random') ? 'checked="checked"' : '';
		$cecked_url = (preg_match('&http://|https://&', $redirect)) ? 'checked="checked"' : '';
		$redirect_page = (is_numeric($redirect)) ? 'checked="checked"' : '';
		$redirect_id = (is_numeric($redirect)) ? $redirect : '';
		?>
		
		<h4><?php _e('Redirect Type', RH_RD_TEXTDOMAIN); ?></h4>
		
		<div id="redirect_type">
			<input type="radio" id="no_redirection" name="redirect_type" value="none" onChange="redirecttoggle('none')" checked="checked"> <label for="no_redirection"><?php _e('None', RH_RD_TEXTDOMAIN); ?></label> <span>|</span> 
			<input type="radio" id="redirect_page" name="redirect_type" value="redirect_page" onChange="redirecttoggle('#redirect_settings_page')" <?php echo $redirect_page ?>> <label for="redirect_page"><?php _e('Redirect to a page', RH_RD_TEXTDOMAIN); ?></label> <span>|</span> 
			<input type="radio" id="redirect_url" name="redirect_type" value="redirect_url" onChange="redirecttoggle('#redirect_settings_url')" <?php echo $cecked_url ?>> <label for="redirect_url"><?php _e('Redirect to a URL', RH_RD_TEXTDOMAIN); ?></label> <span>|</span> 
			<input type="radio" id="redirect_child" name="redirect_type" value="redirect_child" onChange="redirecttoggle('#redirect_settings_child')" <?php echo $checked_child ?>> <label for="redirect_child"><?php _e('Redirect to the first child page', RH_RD_TEXTDOMAIN); ?></label>
			<!-- <input type="radio" id="redirect_random" name="redirect_type" value="redirect_random" onChange="redirecttoggle('#redirect_settings_random')" <?php echo $checked_random ?>> <label for="redirect_random"><?php _e('Redirect to a random child page', RH_RD_TEXTDOMAIN); ?></label> -->
			<input type="hidden" id="redirector_type_set" name="redirector_type_set" value="<?php echo $redirect; ?>" />
		</div>

		<div class="redirect_settings">
			<p class="redirect_settings" id="redirect_settings_page">
				<label for="redirector"><?php _e( 'Redirect to:',RH_RD_TEXTDOMAIN ); ?></label><br />
				<select id="redirector" name="redirector">
					<option value=""><?php _e( 'No redirection',RH_RD_TEXTDOMAIN ); ?></option>
					<?php echo walk_page_dropdown_tree(get_pages(array('depth' => 0)), '0', array('depth' => 0, 'child_of' => 0,'selected' => $redirect_id)); ?>
				</select>

			</p>

			<p class="redirect_settings" id="redirect_settings_url">
				<label for="redirector_url"><?php _e('URL:', 'redirector'); ?></label><br />
				<input id="redirector_url" name="redirector_url" value="<?php echo $redirect_url; ?>" type="text" size="35" />
			</p>

		</div>
		<br clear="all" />
		<?php
	}
	
	
	
	/**
	 * Save as post meta 
	 *
	 * @author Ralf Hortt
	 **/
	function save_post()
	{
		// Redirect to a WordPress page
		if ($_POST['redirect_type'] == 'redirect_page' && is_numeric($_POST['redirector'])) :
			$redirect_to = $_POST['redirector'];
		// Redirect to any url
		elseif ($_POST['redirect_type'] == 'redirect_url' && $_POST['redirector_url']) :
			$redirect_to = $_POST['redirector_url'];
		// Redirect to first child
		elseif ($_POST['redirect_type'] == 'redirect_child') :
			$redirect_to = 'child';
		// Redirect to a random chil page
		elseif ($_POST['redirect_type'] == 'random') :
			$redirect_to = 'random';
		endif;

		// Save as a meta_key value
		if ($redirect_to) :
			update_post_meta($_POST['ID'], '_redirector', $redirect_to);
		// Delete if it isn't set
		else :
			delete_post_meta($_POST['ID'], '_redirector');
		endif;
	}
	
	
	
	/**
	 * Redirect
	 *
	 * @author Ralf Hortt
	 **/
	function template_redirect()
	{
		global $wp_query, $wpdb, $post;
		if (is_page()) :
			$redirect = get_post_meta($wp_query->post->ID, '_redirector', true);
			$redirect = (is_numeric($redirect)) ? get_permalink($redirect) : $redirect;
			if ($redirect == 'child') :
				$sql = "SELECT ID FROM $wpdb->posts WHERE post_parent = '$post->ID' AND post_type = 'page' AND post_status = 'publish' ORDER BY menu_order LIMIT 1";
				$child = $wpdb->get_var($sql);
				if ($child) :
					$redirect = get_permalink($child);
				endif;
			endif;
			if ($redirect != '') :
				wp_redirect($redirect);
				header("Status: 302");
				exit;
			endif;
		endif;
	}
	
	
	
	/**
	 * Load language file
	 *
	 * @author Ralf Hortt
	 **/
	function textdomain()
	{
		if ( function_exists('load_plugin_textdomain') )
			load_plugin_textdomain( RH_RD_TEXTDOMAIN, false, dirname( RH_RD_BASENAME ) . '/languages' );
	}
	
	
	
	/**
	 * Remove all postmeta
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	function uninstall()
	{
		global $wpdb;
		$sql = "DELETE FROM $wpdb->postmeta WHERE meta_key = '_redirector'";
		$wpdb->query($sql);
	}
}

$Redirector = new Redirector();
?>