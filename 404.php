<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

/**
 * DigiFusion before 404 content.
 */
do_action( 'digifusion_before_404_content' );

if ( ! function_exists( 'digifusion_not_found' ) || ! digifusion_not_found() ) :
	?>
	<div class="digi-error-content">
		<?php
		/**
		 * DigiFusion before 404 inner.
		 */
		do_action( 'digifusion_before_404_inner' );
		?>
		<h2 class="digi-error-title"><?php esc_html_e( '404', 'digifusion' ); ?></h2>
		<hr class="digi-error-divider" />
		<p class="digi-error-message"><?php esc_html_e( 'Sorry, the page you are looking for could not be found.', 'digifusion' ); ?></p>
		<div class="digi-error-action">
			<a class="digi-button" href="<?php echo esc_attr( home_url() ); ?>">
				<span class="text"><?php esc_html_e( 'Back to homepage', 'digifusion' ); ?></span>
			</a>
		</div>
		<?php
		/**
		 * DigiFusion after 404 inner.
		 */
		do_action( 'digifusion_after_404_inner' );
		?>
	</div>
	<?php
endif;

/**
 * DigiFusion after 404 content.
 */
do_action( 'digifusion_after_404_content' );

get_footer();