<?php
/**
 * Template part for displaying the page header.
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * DigiFusion before page header.
 */
do_action( 'digifusion_before_page_header' );

if ( ! function_exists( 'digifusion_page_header' ) || ! digifusion_page_header() ) :
	?>
	<div class="site-page-header">
		<div class="container">
			<?php
			/**
			 * DigiFusion before page header content.
			 */
			do_action( 'digifusion_before_page_header_content' );
			?>
			<div class="digi-page-header-content">
				<h1 class="digi-page-title">
					<?php
					if ( is_search() ) {
						esc_html_e( 'Results for : ', 'digifusion' );
						echo get_search_query();
					} else if ( is_404() ) {
						esc_html_e( '404 Error', 'digifusion' );
					} else if ( is_author() ) {
						?>
						<div class="digi-author-header">
							<?php
							echo get_avatar( get_the_author_meta('ID'), 200, '', '', array('class' => 'digi-author-avatar') );
							echo get_the_author_meta( 'display_name', get_the_author_meta( 'ID' )  );
							?>
						</div>
						<?php
					} else if ( is_archive() ) {
						?>
						<?php echo single_cat_title( '', false ); ?>
						<?php
					} else if (is_home()) {
						esc_html_e( 'Blog', 'digifusion' );
					} else {
						echo get_the_title();
					}
					?>
				</h1>
			</div>
			<?php
			/**
			 * DigiFusion after page header content.
			 */
			do_action( 'digifusion_after_page_header_content' );
			?>
		</div>
	</div>
	<?php
endif;

/**
 * DigiFusion after page header.
 */
do_action( 'digifusion_after_page_header' );