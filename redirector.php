<?php
/*
Plugin Name: Redirector
Plugin URL: http://horttcore.de/plugin/redirector
Description: Redirect any page to an internal or external URL
Version: 2.0.3
Author: Ralf Hortt
Author URL: http://horttcore.de/
*/



/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists( 'add_action' ) ) :
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
endif;



/**
 *
 * Plugin Definitions
 *
 */
define( 'RH_RD_BASENAME', plugin_basename( __FILE__ ) );
define( 'RH_RD_BASEDIR', dirname( plugin_basename( __FILE__ ) ) );



/**
 * Redirector Class
 *
 * @author Ralf Hortt
 */
class Redirector {



	/**
	 * Constructor
	 *
	 * @author Ralf Hortt
	 **/
	function __construct()
	{
		if ( is_admin() ) :
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_print_scripts-post.php', array( $this, 'enqueue_script' ) );
			add_action( 'admin_print_scripts-post-new.php', array( $this, 'enqueue_script' ) );
			add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_style' ) );
			add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue_style' ) );
			add_action( 'save_post', array( $this, 'save_post' ) );

			register_activation_hook( __FILE__, array( $this, 'install' ) );
			register_uninstall_hook( __FILE__, array( 'Redirector', 'uninstall' ) );
		else :
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		endif;

		add_action( 'init', array( $this, 'init' ) );
	}



	/**
	 * Plugin initialisation
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function admin_init()
	{
		$post_types = get_post_types();

		if ( $post_types ) :

			foreach ( $post_types as $post_type ) :

				if ( post_type_supports( $post_type, 'redirector' ) ) :

					add_meta_box( 'redirect', __( 'Redirect', 'redirector' ), array( $this, 'metabox' ), $post_type, 'side' );

				endif;

			endforeach;

		endif;
	}



	/**
	 * Enqueue Javascript
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function enqueue_script()
	{
		wp_enqueue_script( 'redirector', WP_PLUGIN_URL . '/' . RH_RD_BASEDIR . '/javascript/redirector.js', array( 'jquery' ) );
	}



	/**
	 * Enqueue CSS
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function enqueue_style()
	{
		wp_enqueue_style( 'redirector', WP_PLUGIN_URL . '/' . RH_RD_BASEDIR . '/css/redirector.css' );
	}



	/**
	 * Init
	 *
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function init()
	{
		load_plugin_textdomain( 'redirector', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'  );

		add_post_type_support( 'page', 'redirector' );
	}



	/**
	 * Backwards compability
	 *
	 * With this query old entries of the redirector will be rewritten to the newer format
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	public function install()
	{
		global $wpdb;
		$sql = "UPDATE $wpdb->postmeta SET meta_key = '_redirector' WHERE meta_key = 'redirector'";
		$wpdb->query($sql);
	}



	/**
	 * Redirector Meta Box
	 *
	 * @access public
	 * @param obj $post Post Object
	 * @return output html
	 * @author Ralf Hortt
	 **/
	public function metabox( $post )
	{
		$redirect = get_post_meta( $post->ID, '_redirector', TRUE );

		if ( 'child' == $redirect ) :
			$checked_child = true;
		elseif ( 'https' == $redirect ) :
			$checked_https = true;
		elseif ( $redirect && is_numeric( $redirect ) ) :
			$checked_page = true;
			$redirect_id = $redirect;
		elseif ( $redirect ) :
			$checked_url = true;
			$redirect_url = $redirect;
		else :
			$checked_none = true;
		endif;
		?>

		<?php wp_nonce_field( 'redirector', 'redirector_nonce' ); ?>

		<?php do_action( 'redirector_metabox_begin', $post ); ?>

		<p id="redirect_type">
			<input type="radio" id="redirect_none" data-type="none" name="redirect_type" value="" <?php if ( $checked_none) echo 'checked="checked"' ?>> <label for="redirect_none"><?php _e( 'None', 'redirector' ); ?></label><br>
			<input type="radio" id="redirect_page" data-type="page" name="redirect_type" value="<?php echo $redirect_id ?>" <?php if ( $checked_page) echo 'checked="checked"' ?>> <label for="redirect_page"><?php _e( 'Redirect to Page', 'redirector' ); ?></label><br>
			<input type="radio" id="redirect_url" data-type="url" name="redirect_type" value="<?php echo $redirect_url ?>" <?php if ( $checked_url) echo 'checked="checked"' ?>> <label for="redirect_url"><?php _e( 'Redirect to Website', 'redirector' ); ?></label><br>
			<input type="radio" id="redirect_child" data-type="child" name="redirect_type" value="child" <?php if ( $checked_child) echo 'checked="checked"' ?>> <label for="redirect_child"><?php _e( 'Redirect to First Child', 'redirector' ); ?></label><br>
			<input type="radio" id="redirect_https" data-type="https" name="redirect_type" value="https" <?php if ( $checked_https) echo 'checked="checked"' ?>> <label for="redirect_https"><?php _e( 'Redirect to HTTPS', 'redirector' ); ?></label>
			<?php do_action( 'redirector_types', $post ) ?>
		</p>

		<p id="redirect_settings_page">
			<label for="redirector_tree"><?php _e( 'Redirect to:','redirector' ); ?></label><br />
			<select id="redirector_tree" name="redirector">
				<option value=""><?php _e( 'No redirection','redirector' ); ?></option>
				<?php echo apply_filters( 'redirector_dropdown', walk_page_dropdown_tree( get_pages(array( 'depth' => 0)), '0', array( 'depth' => 0, 'child_of' => 0,'selected' => $redirect_id ) ), $redirect_id ); ?>
			</select>
		</p>

		<p id="redirect_settings_url">
			<label for="redirector_url"><?php _e( 'URL:', 'redirector' ); ?></label><br />
			<input id="redirector_url" name="redirector_url" value="<?php echo apply_filters( 'redirector_url', $redirect_url ); ?>" type="text" size="35" />
		</p>

		<?php
		do_action( 'redirector_metabox_end', $post );
	}



	/**
	 * Save as post meta
	 *
	 * @access public
	 * @param int $post_id Post ID
	 * @author Ralf Hortt
	 **/
	public function save_post( $post_id )
	{
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( !isset($_POST['redirector_nonce']) || !wp_verify_nonce( $_POST['redirector_nonce'], 'redirector' ) )
			return;

		if ( $_POST['redirect_type'] ) :
			update_post_meta( $post_id, '_redirector', $_POST['redirect_type'] );
		else :
			delete_post_meta( $post_id, '_redirector' );
		endif;
	}



	/**
	 * Redirect
	 *
	 * @access public
	 * @author Ralf Hortt
	 **/
	public function template_redirect()
	{
		global $post;

		if ( post_type_supports( $post->post_type, 'redirector' ) ) :

			$redirect = get_post_meta($post->ID, '_redirector', true);

			if ( is_numeric($redirect) ) :
				$redirect_url = get_permalink($redirect);
			elseif ( 'child' == $redirect ) :
				$children = get_posts( 'numberposts=1&post_type=' . $post->post_type . '&post_parent=' . $post->ID . '&orderby=menu_order&order=ASC' );
				if ( 0 < count($children) ) :
					$redirect_url = get_permalink($children[0]->ID);
				endif;
			elseif ( 'https' == $redirect ) :
				$redirect_url = str_replace( 'http:', 'https:', get_permalink($post->ID) );
			elseif ( '' != $redirect ) :
				$redirect_url = $redirect;
			else :
				$redirect_url = FALSE;
			endif;

			if ( ( isset( $redirect_url ) && 'https' != $redirect ) || ( 'https' == $redirect && 'HTTP/' == substr($_SERVER['SERVER_PROTOCOL'], 0, 5) ) ) :

				if ( $_SERVER['QUERY_STRING'] )
					$redirect_url .= '?' . $_SERVER['QUERY_STRING'];

				wp_redirect( apply_filters( 'redirector_redirect', $redirect_url, $redirect, $post ) );
				header( apply_filters( 'redirector_status', 'Status: 302' ) );
				exit;
			endif;

		endif;
	}



	/**
	 * Remove all postmeta
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
	 **/
	static function uninstall()
	{
		global $wpdb;
		$sql = "DELETE FROM $wpdb->postmeta WHERE meta_key = '_redirector'";
		$wpdb->query($sql);

		do_action( 'redirector_uninstall' );
	}



}

new Redirector();
