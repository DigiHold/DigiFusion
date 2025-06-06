<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package DigiFusion
 */

get_header();

/**
 * DigiFusion before index content.
 */
do_action( 'digifusion_before_index_content' );

if ( ! function_exists( 'digifusion_archive' ) || ! digifusion_archive() ) :
	if ( have_posts() ) :
		/**
		 * DigiFusion before posts grid.
		 */
		do_action( 'digifusion_before_posts_grid' );
		?>
		<div class="posts-grid">
			<?php
			/* Start the Loop */
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', get_post_type() );

			endwhile;
			?>
		</div>
		<?php
		/**
		 * DigiFusion after posts grid.
		 */
		do_action( 'digifusion_after_posts_grid' );

		digifusion_the_posts_navigation();
	else :
		get_template_part( 'template-parts/content', 'none' );
	endif;
endif;

/**
 * DigiFusion after index content.
 */
do_action( 'digifusion_after_index_content' );

get_footer();