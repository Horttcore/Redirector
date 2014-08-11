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
 * Redirector class
 *
 * @package Redirector
 * @since v3.0.0
 * @author Ralf Hortt
 **/
final class Redirector {



	/**
	 * Constructor
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 **/
	public function __construct()
	{

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'cachify_skip_cache', array( $this, 'skip_cachify' ) ); // compability for cachify
		add_post_type_support( 'page', 'redirector' );

	} // end __constructor



	/**
	 * Skip Caching
	 *
	 * Prevent site from being cached by Cachify
	 * http://playground.ebiene.de/cachify-wordpress-cache/
	 *
	 * @return bool
	 * @author Ralf Hortt
	 **/
	public function skip_cachify()
	{

		$redirector = get_post_meta( get_the_ID(), '_redirector', TRUE );

		if ( isset( $redirector['type'] ) )
			return TRUE;
		else
			return FALSE;

	} // end skip_cachify



	/**
	 * Handle redirect
	 *
	 * @access public
	 * @since v3.0.0
	 * @author Ralf Hortt
	 * @TODO Append query string if redirected
	 **/
	public function template_redirect()
	{

		if ( !post_type_supports( get_post_type(), 'redirector' ) )
			return;

		global $post;

		$redirect = get_post_meta( get_the_ID(), '_redirector', TRUE);
		$redirect_url = FALSE;

		// Exit if none redirect type is set
		if ( !isset( $redirect['type'] ) || '' == $redirect['type'] )
			return;

		// First child
		if ( 'first-child' == $redirect['type'] ) :

			$children = get_posts( 'numberposts=1&post_type=' . get_post_type() . '&post_parent=' . get_the_ID() . '&orderby=menu_order&order=ASC' );

			if ( 0 < count($children) ) :

				$redirect_url = get_permalink( $children[0]->ID );

			endif;

		// Post ID
		elseif ( 'post' == $redirect['type'] ) :

			$redirect_url = get_permalink( $redirect['post_id'] );

		// URL
		elseif ( 'url' == $redirect['type'] ) :

			$redirect_url = $redirect['url'];

		// HTTPS
		elseif ( 'https' == $redirect['type'] ) :

			$redirect_url = str_replace( 'http:', 'https:', get_permalink( get_the_ID() ) );

		endif;

		// Exit if no redirect url is set
		if ( FALSE === $redirect_url ) :
			delete_post_meta( get_the_ID(), '_redirector' );
			return;
		endif;

		// Append query string
		/* if ( $_SERVER['QUERY_STRING'] )
			$redirect_url .= apply_filters( 'redirector-query-string', '?' . $_SERVER['QUERY_STRING'] );
		*/

		// Redirect
		if ( !$redirect_url )
			return;

		if ( 'https' == $redirect['type'] && is_ssl() )
			return;

		wp_redirect( apply_filters( 'redirector-redirect-url', $redirect_url, $redirect, $post ), apply_filters( 'redirector-status-code', 301 ) );

	} // end template_redirect



} // end Redirector

new Redirector;
