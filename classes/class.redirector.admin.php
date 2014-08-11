<?php
/**
 * Security, checks if WordPress is running
 **/
if ( !function_exists( 'add_action' ) ) :
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
endif;



/**
 * Redirector Admin Class
 *
 * @package Redirector
 * @since 3.0.1
 * @author Ralf Hortt
 */
final class Redirector_Admin {



	/**
	 * Version
	 *
	 * @var string
	 **/
	protected $version = '3.0.1';



	/**
	 * Constructor
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function __construct()
	{

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_print_scripts-post.php', array( $this, 'enqueue_script' ) );
		add_action( 'admin_print_scripts-post-new.php', array( $this, 'enqueue_script' ) );
		add_action( 'admin_print_styles-post.php', array( $this, 'enqueue_style' ) );
		add_action( 'admin_print_styles-post-new.php', array( $this, 'enqueue_style' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'maybe_update' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'wp_ajax_redirector-search-posts', array( $this, 'search_posts' ) );

		add_post_type_support( 'page', 'redirector' );

	} // end __construct



	/**
	 * Plugin initialisation
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function admin_init()
	{

		$post_types = get_post_types();

		if ( !$post_types )
			return;

		foreach ( $post_types as $post_type ) :

			if ( !post_type_supports( $post_type, 'redirector' ) )
				continue;

			add_meta_box( 'redirect', __( 'Redirect', 'redirector' ), array( $this, 'metabox' ), $post_type, 'side' );

		endforeach;

	} // end admin_init



	/**
	 * Enqueue Javascript
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function enqueue_script()
	{

		wp_register_script( 'redirector', plugins_url( '../javascript/redirector.js', __FILE__ ), array( 'jquery', 'thickbox', 'underscore', 'backbone' ) );
		wp_localize_script( 'redirector', 'redirector', array(
			'searchNonce' => wp_create_nonce( 'redirector-search-nonce' ),
		) );
		wp_enqueue_script( 'redirector' );

	} // end enqueue_script



	/**
	 * Enqueue CSS
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function enqueue_style()
	{

		wp_enqueue_style( 'redirector', plugins_url( '../css/redirector.css', __FILE__ ) );

	} // end enqueue_style



	/**
	 * Returns page of the first child
	 *
	 * @access public
	 * @param int $post_id Post ID
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function get_first_child_title( $post_id )
	{

		$post_type = get_post_type( $post_id );

		$post = get_posts( array(
			'post_type' => $post_type,
			'post_parent' => $post_id,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'numberposts' => 1,
		) );

		if ( !empty( $post ) )
			return '<a href="' . get_permalink( $post[0]->ID ) . '" target="_blank">' . $post[0]->post_title . '</a>';
		else
			return FALSE;

	} // end get_first_child_title



	/**
	 * List posts
	 *
	 * @access protected
	 * @since v3.0.0
 	 * @author Ralf Hortt
	 **/
	protected function list_posts( $posts )
	{

		$post_types = array();

		?>

		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e( 'Title' ) ?></th>
					<th><?php _e( 'Type' ) ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 1;
				foreach ( $posts as $post ) :

					$class = ( 1 == $i % 2 ) ? 'alternate' : 'default';
					$title = ( '' == $post->post_title ) ? __( '( No title )', 'redirector' ) : $post->post_title;

					if ( !isset( $post_types[$post->post_type] ) ) :

						$post_type = get_post_type_object( $post->post_type );
						$post_types[$post->post_type] = $post_type->labels->singular_name;

					endif;

					?>

					<tr class="<?php echo $class ?>">
						<th class="item-title">
							<a href="<?php echo get_permalink( $post->ID ) ?>" target="_blank"><?php echo $title ?></a>
							<?php if ( 'post' == $post->post_type ) :
								echo '<br><i>' . date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ) . '</i>';
							endif;
							?>
						</th>
						<td class="item-info"><?php echo $post_types[$post->post_type] ?></td>
						<td><a class="button select-redirector-post-id" href="#" data-id="<?php echo $post->ID ?>"><?php _e( 'Select', 'redirector' ); ?></a></td>
					</tr>

					<?php

					$i++;

				endforeach;

				?>

			</tbody>
			<tfoot>
				<tr>
					<th><?php _e( 'Title' ) ?></th>
					<th><?php _e( 'Type' ) ?></th>
					<th>&nbsp;</th>
				</tr>
			</tfoot>
		</table>

		<?php

	} // end list_posts



	/**
	 * Load plugin textdomain
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain( 'redirector', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );

	} // end load_plugin_textdomain



	/**
	 * Check if database update is needed
	 *
	 * @access protected
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function maybe_update()
	{

		$options = get_option( 'redirector' );

		if ( isset( $options['version'] ) && version_compare( $this->version, $options['version'], '<=' )  )
			return;

		global $wpdb;

		// Update from v1.x.x to v2.x.x
		$sql = "UPDATE $wpdb->postmeta SET meta_key = '_redirector' WHERE meta_key = 'redirector'";
		$wpdb->query($sql);

		// Update from v2.x.x to v3.x.x
		$meta = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = '_redirector'" );

		if ( !$meta )
			return;

		foreach ( $meta as $m ) :

			// No need to do something
			if ( is_serialized( $m->meta_value ) )
				continue;

			// Post
			if ( is_numeric( $m->meta_value ) ) :

				update_post_meta( $m->post_id, '_redirector', array(
					'type' => 'post',
					'post_id' => $m->meta_value
				) );

			// First child
			elseif ( 'child' == $m->meta_value ) :

				update_post_meta( $m->post_id, '_redirector', array(
					'type' => 'first-child',
				) );

			// SSL
			elseif ( 'https'  == $m->meta_value ) :

				update_post_meta( $m->post_id, '_redirector', array(
					'type' => 'https',
				) );

			// URL
			elseif ( '' != $m->meta_value ) :

				update_post_meta( $m->post_id, '_redirector', array(
					'type' => 'url',
					'url' => $m->meta_value
				) );

			endif;

			do_action( 'redirector-update' );

		endforeach;

		// Cachify compability
		if ( class_exists( 'Cachify' ) ) :
			Cachify::flush_total_cache();
		endif;

		// Update version number
		update_option( 'redirector', array(
			'version' => $this->version
		));

	} // end maybe_update



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
		$redirect_id = ( isset( $redirect['post_id'] ) ) ? $redirect['post_id'] : '';
		$type = ( isset( $redirect['type'] ) ) ? $redirect['type'] : '';

		wp_nonce_field( 'redirector', 'redirector_nonce' );

		do_action( 'redirector_metabox_begin', $post ); ?>

		<div class="redirector-redirect-type">

			<label><input type="radio" name="redirect-type" value="" <?php if ( !$redirect ) echo 'checked="checked"' ?>> <?php _e( 'None', 'redirector' ); ?></label>

		</div><!-- .redirector-redirect-type -->

		<div class="redirector-redirect-type">

			<label><input type="radio" name="redirect-type" value="post" <?php checked( $type, 'post' ) ?>> <?php _e( 'Post object', 'redirector' ); ?></label>

			<div <?php if ( isset( $redirect['type'] ) && 'post' == $redirect['type'] ) echo 'style="display:block;"' ?>>

				<input type="hidden" id="redirector-post-id" name="redirector-post-id" value="<?php echo $redirect_id ?>">

				<div id="redirector-post-id-preview">
					<?php if ( $redirect_id ) : ?>
						<a href="<?php get_permalink( $redirect_id ) ?>"><?php echo get_the_title( $redirect_id ) ?></a>
					<?php endif; ?>
				</div>

				<a class="button thickbox" id="redirector-set-post-id" href="#TB_inline?width=640&amp;height=auto&amp;inlineId=redirector-post-search" title="<?php _e( 'Select redirection', 'redirector' ); ?>"><?php _e( 'Select', 'redirector' ); ?></a>

				<?php $this->modal_search( $redirect, $redirect_id ) ?>

			</div>

		</div><!-- .redirector-redirect-type -->

		<div class="redirector-redirect-type">

			<?php $url = ( isset( $redirect['url'] ) ) ? esc_url( apply_filters( 'redirector_url', $redirect['url'] ) ) : ''; ?>

			<label><input type="radio" name="redirect-type" value="url" <?php checked( $type, 'url' ) ?>> <?php _e( 'Website', 'redirector' ); ?></label>

			<div <?php if ( 'url' == $type ) echo 'style="display:block;"' ?>>

				<label>
					<?php _e( 'URL:', 'redirector' ); ?><br>
					<input name="redirector-url" value="<?php echo $url ?>" type="url" />
				</label><br>

				<a id="redirector-url-preview" href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a>

			</div>

		</div><!-- .redirector-redirect-type -->

		<div class="redirector-redirect-type">

			<input type="radio" id="redirect_child" data-type="child" name="redirect-type" value="first-child" <?php checked( $type, 'first-child' ) ?>> <label for="redirect_child"><?php _e( 'First child', 'redirector' ); ?></label>

			<div <?php if ( 'first-child' == $type ) echo 'style="display:block;"' ?>>

				<?php
				if ( FALSE !== ( $child_title = $this->get_first_child_title( $post->ID ) ) )
					echo $child_title;
				else
					echo '<i>' . __( 'No child', 'redirector' ) . '</i>';
				?>

			</div>

		</div><!-- .redirector-redirect-type -->

		<div class="redirector-redirect-type">

			<input type="radio" id="redirect_https" data-type="https" name="redirect-type" value="https" <?php checked( $type, 'https' ) ?>> <label for="redirect_https"><?php _e( 'SSL', 'redirector' ); ?></label><br>

			<div <?php if ( 'https' == $type ) echo 'style="display:block;"' ?>>

				<a href="<?php echo esc_url( str_replace( 'http:', 'https:', get_permalink( $post->ID ) ) ) ?>" target="_blank"><?php echo $post->post_title ?></a>

			</div>

		</div><!-- .redirector-redirect-type -->

		<?php

		do_action( 'redirector_metabox_end', $post );

	}



	/**
	 * Modal box
	 *
	 * @access protected
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	protected function modal_search( $redirect, $redirect_id )
	{

		do_action( 'redirector-modal-search-begin' );

		?>

		<div id="redirector-post-search">

			<p>
				<input type="search" value="" id="redirector-search" placeholder="<?php _e( 'Search' ); ?>"> <a href="#" class="button" id="redirector-search-post"><?php _e( 'Search' ); ?></a>
			</p>

			<div id="redirector-search-result"></div>

			<div id="redirector-recent-posts">

				<?php $this->recent_posts(); ?>

			</div>

		</div>

		<?php

		do_action( 'redirector-modal-search-end' );

	} // end modal_search



	/**
	 * Display recent posts
	 *
	 * @access protected
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	protected function recent_posts()
	{

		$posts = get_posts( apply_filters( 'redirector-recent-posts', array(
			'post_type' => 'any',
			'orderby' => 'post_date',
			'order' => 'ASC',
			'showposts' => 10,
		) ) );

		if ( !$posts )
			echo '<i>' . __( 'No recent posts', 'redirector' ) . '</i>';

		?>

		<h2><?php _e( 'Most recent posts', 'redirector' ); ?></h2>

		<?php $this->list_posts( $posts );

	} // end recent_posts



	/**
	 * Save as post meta
	 *
	 * @access public
	 * @param int $post_id Post ID
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function save_post( $post_id )
	{

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( !isset( $_POST['redirector_nonce'] ) || !wp_verify_nonce( $_POST['redirector_nonce'], 'redirector' ) )
			return;

		if ( isset( $_POST['redirect-type'] ) && '' != $_POST['redirect-type'] ) :

			$options = array(
				'type' => sanitize_text_field( $_POST['redirect-type'] ),
			);

			switch ( $_POST['redirect-type'] ) :

				// Save post id
				case 'post' :

					if ( '' != $_POST['redirector-post-id'] )
						$options['post_id'] = intval( $_POST['redirector-post-id'] );
					else
						unset( $options['type'] );

					break;

				// Save redirect url
				case 'url' :

					if ( '' != $_POST['redirector-url'] )
						$options['url'] = sanitize_url( $_POST['redirector-url'] );
					else
						unset( $options['type'] );

					break;

				// Unset redirect if no child exists
				case 'first-child' :

					if ( '' == $this->get_first_child_title( $post_id ) )
						unset( $options['type'] );

					break;

			endswitch;

			$options = apply_filters( 'redirector-meta', $options );

			if ( isset( $options['type'] ) )
				update_post_meta( $post_id, '_redirector', $options );
			else
				delete_post_meta( $post_id, '_redirector' );

		else :

			delete_post_meta( $post_id, '_redirector' );

		endif;

	} // end save_post



	/**
	 * Search posts
	 *
	 * @access public
	 * @since v.3.0.0
	 * @author Ralf Hortt
	 **/
	public function search_posts()
	{

		if ( !wp_verify_nonce( $_POST['nonce'], 'redirector-search-nonce' ) )
			return;

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$post_types = array_keys( $post_types );

		$query = apply_filters( 'redirector-search-query', array(
			'post_type' => $post_types,
			'suppress_filters' => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			's' => sanitize_text_field( $_REQUEST['search'] ),
		) );

		$query = new WP_Query( $query );

		$response = array();

		ob_start();

		printf( '<h2>' . __( 'Search Result for „%s“', 'redirector' ) . '</h2>', sanitize_text_field( $_REQUEST['search'] ) );

		$this->list_posts( $query->posts );

		$response['output'] = ob_get_contents();
		ob_end_clean();

		wp_reset_query();

		die( json_encode( $response ) );

	} // end search_posts



} // end Redirector_Admin

new Redirector_Admin();
