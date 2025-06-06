<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * DigiFusion before search results.
 */
do_action( 'digifusion_before_search_results' );

if ( ! function_exists( 'digifusion_search' ) || ! digifusion_search() ) :
	?>
	<div class="digi-search-results">
		<?php
		/**
		 * DigiFusion before search results inner.
		 */
		do_action( 'digifusion_before_search_results_inner' );

		if ( have_posts() ) :
			while ( have_posts() ) : the_post();
				echo '<a href="' . get_the_permalink() . '" class="digi-search-item">' . get_the_title() . '</a>';
			endwhile;
		else:
			echo '<p class="digi-no-results">' . esc_html__('No results found.', 'digifusion') . '</p>';
		endif;

		/**
		 * DigiFusion after search results inner.
		 */
		do_action( 'digifusion_after_search_results_inner' );
		?>
	</div>
	<?php
endif;

/**
 * DigiFusion after search results.
 */
do_action( 'digifusion_after_search_results' );

get_footer();