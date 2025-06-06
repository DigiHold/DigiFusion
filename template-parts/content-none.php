<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php
/**
 * DigiFusion before no results.
 */
do_action( 'digifusion_before_no_results' );
?>

<section class="digi-no-results">
	<div class="digi-no-results-content">
		<?php
		/**
		 * DigiFusion before no results content.
		 */
		do_action( 'digifusion_before_no_results_content' );
		?>
		
		<?php
		if ( is_search() ) :
			?>
			<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'digifusion' ); ?></p>
			
			<div class="digi-search-again">
				<?php get_search_form(); ?>
			</div>
			<?php
		elseif ( is_home() && current_user_can( 'publish_posts' ) ) :
			?>
			<p>
				<?php
				printf(
					wp_kses(
						/* translators: %1$s: link to new post page */
						__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'digifusion' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url( admin_url( 'post-new.php' ) )
				);
				?>
			</p>
			<?php
		else :
			?>
			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'digifusion' ); ?></p>
			
			<div class="digi-search-again">
				<?php get_search_form(); ?>
			</div>
			<?php
		endif;
		?>
		
		<?php
		/**
		 * DigiFusion after no results content.
		 */
		do_action( 'digifusion_after_no_results_content' );
		?>
	</div>
</section>

<?php
/**
 * DigiFusion after no results.
 */
do_action( 'digifusion_after_no_results' );
?>