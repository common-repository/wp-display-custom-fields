=== Plugin Name ===
Contributors: benbalter
Donate link: http://ben.balter.com/donate
Tags: post meta, postmeta, custom fields, taxonomy, taxonomies, terms, term, wikipedia, meta, metabox, cms, front end
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 1.0

Inserts a Wikipedia-like metabox into posts with all the post's associated custom fields and taxonomy terms.

== Description ==

This plugin will work, "as is", out of the box, but assumes some level of CSS and developer customization to adapt to your site's specific needs.

Styles can be overridden via your theme's style.css

Functionality can be overridden via your theme's functions.php

''Examples''

Don't display metabox on "product" post type:

	add_filter( 'display_custom_fields', 'bb_no_product_metabox' );

	function bb_no_product_metabox( $true, $post ) {
	  if ( $post->post_type == 'product' )
	    return false;
	    
	  return true;
	 
	}
	

Don't display the product_id metakey

	add_filter( 'dcf_postmeta', 'bb_no_product_id' );
	
	function bb_no_product_id( $postmeta ) {
	  unset( $postmeta['product_id'] );
	  return $postmeta;
	}
	

Don't display metabox title

`add_filter( 'dcf_heading', '__return_false' );`

Display metabox below content (rather than floated to the right)

`add_filter( 'dcf_before_content', '__return_false' );`



== Changelog ==

= 0.1 =
* Initial Alpha Release




