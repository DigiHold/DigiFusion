<?php
/**
 * The template for displaying all pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-page
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * DigiFusion before page content.
 */
do_action( 'digifusion_before_page_content' );
?>
<div class="digi-page-content">
	<?php
	/**
	 * DigiFusion before page inner.
	 */
	do_action( 'digifusion_before_page_inner' );
	
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
			the_content();
		endwhile;
	endif;
	
	/**
	 * DigiFusion after page inner.
	 */
	do_action( 'digifusion_after_page_inner' );
	?>
</div>

<?php
/**
 * DigiFusion after page content.
 */
do_action( 'digifusion_after_page_content' );

get_footer();