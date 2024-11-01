<?php
/*
Plugin Name: Display Custom Fields
Description: Displays custom fields and taxonomies on the WordPress front end 
Author: Benjamin J. Balter
Version: 0.1
Author URI: http://ben.balter.com
*/

class Display_Custom_Fields {
	
	public $css_class = 'meta';
	
	/**
	 * Register hooks with WP API
	 */
	function __construct() {
		
		add_filter( 'the_content', array( &$this, 'content_filter' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_css' ) );
		add_filter( 'dcf_postmeta_key', array( &$this, 'format_postmeta_key' ) );
		add_filter( 'display_custom_fields', array( &$this, 'post_type_filter' ), 10, 2 );
		add_filter( 'dcf_postmeta_key', array( &$this, 'hidden_postmeta_filter'), 5 );
		add_filter( 'dcf_postmeta_value', array( &$this, 'implode_array_filter' ) );
	}
	
	/**
	 * Returns an array of taxonomies and terms
	 * @return array array of taxonomies in the form of label => list of terms, linked to term page
	 * @uses dcf_taxonomies
	 */
	function get_taxonomies( ) {
		global $post;

		$output = array();
		
 		$taxs = get_taxonomies( array( 'object_type' => array( $post->post_type ), 'public' => true ) );
		$taxs = apply_filters( 'dcf_taxonomies', $taxs, $post );
		
		foreach ( $taxs as $tax ) { 
 			$tax = get_taxonomy( $tax );
			$terms = get_the_term_list( $post->ID, $tax->name, null, ', ' );
			
			if ( empty( $terms ) )
				continue;
				
			$output[ $tax->labels->singular_name ] = $terms;
 		}
 	
 		return $output;
	
	}
	
	/**
	 * Returns array of postmeta keys and values
	 * @return array array in the format of key => value
	 * @uses dcf_postmeta
	 * @uses dcf_postmeta_key
	 * @uses dcf_postmeta_value
	 */
	function get_postmeta() {
		global $post;
		
		$output = array();
		
		$postmeta = get_post_custom( $post->ID );
		$postmeta = apply_filters( 'dcf_postmeta', $postmeta, $post );
		
		foreach ( $postmeta as $key => $value ) {
		
			//make it easy to format individual values
			$key = apply_filters( 'dcf_postmeta_key', $key, $post );
			$value = apply_filters( 'dcf_postmeta_value', $value[0], $key, $post );
			
			if ( empty( $value ) || empty( $key ) || !$key || !$value )
				continue;
								
			$output[ $key ] = $value;
		}
	
		return $output;
	}

	/**
	 * Main filter to add meta box to content
	 * @param string $content the content of the post
	 * @return string the modified content
	 * @uses get_taxonomies()
	 * @uses get_postmeta
	 * @uses dcf_sorter
	 * @uses dcf_data 
	 */
	function content_filter( $content ) {
 		global $post;
 		
 		//overrided, e.g., by post type
 		if ( !apply_filters( 'display_custom_fields', true, $post ) )
 			return $content;
 		
 		$data = array_merge( $this->get_taxonomies(), $this->get_postmeta() );
 		
		//allow developers to override the default sort mechanism
		//ksort will sort alphabetically by label
		$sorter = apply_filters( 'dcf_sorter', 'ksort' );
		call_user_func( $sorter, &$data );
		
		$data = apply_filters( 'dcf_data', $data );
		
		//no data, or filter killed, return original content
		if ( empty( $data ) )
			return $content;

		$output = "<div class=\"{$this->css_class}\">";

		$output .= $this->get_heading();
	
		foreach ( $data as $label => $value ) {
			$output .= "\t<div class=\"{$this->css_class}-row\">\n";
			$output .= "\t\t<div class=\"{$this->css_class}-label\">$label:</div>\n";
			$output .= "\t\t<div class=\"{$this->css_class}-value\">$value</div>\n";
			$output .= "\t</div>\n";
		}

		$output .= "\t<div class=\"clearfix\">&nbsp;</div>\n";
		$output .= "</div>\n";
		
		//should the metabox be inserted before or after the content? (default is before)
		$output = ( apply_filters( 'dcf_before_content', true ) ) ? $output . $content : $content . $output;

 		return $output;
 	}
 	
 	/**
 	 * Returns a heading describing the meta box
 	 * @return string heading the heading
 	 * @uses dcf_heading
 	 */
 	function get_heading() {
 		global $post;
 		
 		$pt = get_post_type_object( $post->post_type );
 		
		$heading = $pt->labels->singular_name . __( ' Information', 'display-custom-fields' );
		
		$heading = "<h2>" . $heading . '</h2>';

		//return empty string to prevent heading from being prepended
		$heading = apply_filters( 'dcf_heading', $heading, $post, $pt );
		
		return $heading;
 	}
 	
 	/**
 	 * Tells WP to serve our CSS file
 	 */
 	function enqueue_css() { 
 		wp_enqueue_style( 'display-custom-fields', plugins_url( 'style.css', __FILE__ ), null, filemtime( dirname( __FILE__ ) . '/style.css' ) );
 	}

	/**
	 * Default postmeta key filter
	 * Changes underscores to spaces and capitalizes words
	 * @param string $key the original key
	 * @return string the formatted key
	 */
	function format_postmeta_key( $key ) {
		return ucwords( str_replace( '_', ' ', $key ) );
	}
	
	/**
	 * Dont display custom fields on pages
	 */
	function post_type_filter( $true, $post ) {
		
		if ( $post->post_type == 'page' )
			return false;
			
		return true;
	}
	
	/**
	 * if postmeta starts with an underscore, don't display it on the front end
	 */
	function hidden_postmeta_filter( $key ) {

		if ( substr( $key, 0, 1 ) == '_' )
			return false;
		
		return $key;
	}
	
	/**
	 *  If metavalue is an array, convert to string
	 */
	function implode_array_filter( $value ) {
	
		if ( !is_array( $value ) )
			return $value;
						
		return implode (', ', $value );
	
	}
}

$dcf = new Display_Custom_Fields();