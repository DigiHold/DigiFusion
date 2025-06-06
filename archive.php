<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * DigiFusion before taxonomy content.
 */
do_action( 'digifusion_before_taxonomy_content' );

if ( ! function_exists( 'digifusion_taxonomy' ) || ! digifusion_taxonomy() ) :
	if ( have_posts() ) : ?>
		<?php
		/**
		 * DigiFusion before posts grid.
		 */
		do_action( 'digifusion_before_posts_grid' );
		?>
		<div class="digi-posts-grid">
			<?php
			// Start the Loop
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'archive' );
			endwhile;
			?>
		</div>
		<?php
		/**
		 * DigiFusion after posts grid.
		 */
		do_action( 'digifusion_after_posts_grid' );
		?>
		<?php digifusion_the_posts_navigation(); ?>
	<?php else : ?>
		<div class="digi-no-results">
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		</div>
	<?php endif;
endif;

/**
 * DigiFusion after taxonomy content.
 */
do_action( 'digifusion_after_taxonomy_content' );

get_footer();