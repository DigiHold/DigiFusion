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

$post_id = get_the_ID();

// Get custom page title and description
$custom_title = $post_id ? get_post_meta($post_id, 'digifusion_custom_page_title', true) : '';
$page_description = $post_id ? get_post_meta($post_id, 'digifusion_page_description', true) : '';

/**
 * DigiFusion before page header.
 */
do_action( 'digifusion_before_page_header' );

if ( ! function_exists( 'digifusion_page_header' ) || ! digifusion_page_header() ) :
	// Check if page header is disabled
    $disable_page_header = $post_id ? get_post_meta($post_id, 'digifusion_disable_page_header', true) : false;
    
    if (!$disable_page_header) :
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
					<?php
					$is_woo = class_exists( 'WooCommerce' );
					$is_digi = class_exists( 'DigiCommerce' );

					if ( ( $is_woo && is_product() ) ||
						 ( $is_digi && is_singular( 'digi_product' ) ) || 
					     ( is_singular( 'post' ) ) ) {
						$tag = 'span';
					} else {
						$tag = 'h1';
					}

					if ( ! empty( $page_description ) ) {
						echo '<div class="digi-page-header-title">';
					}
					?>

					<<?php echo esc_attr( $tag ); ?> class="digi-page-title">
						<?php
						if ( $is_woo && ( is_shop() || is_product_category() || is_product_tag() || is_product() ) ) {
							if ( is_shop() ) {
								echo wp_kses_post( woocommerce_page_title( false ) );
							} elseif ( is_product_category() || is_product_tag() ) {
								echo wp_kses_post( single_term_title( '', false ) );
							} elseif ( is_product() ) {
								$shop_page_id = wc_get_page_id( 'shop' );
								$shop_title = $shop_page_id ? get_the_title( $shop_page_id ) : __( 'Shop', 'digifusion' );
								echo wp_kses_post( $shop_title );
							}
						} elseif ( $is_digi && ( is_post_type_archive( 'digi_product' ) || is_singular( 'digi_product' ) ) ) {
							if ( is_post_type_archive( 'digi_product' ) ) {
								echo esc_html__( 'Shop', 'digifusion' );
							} elseif ( is_singular( 'digi_product' ) ) {
								// Get DigiCommerce shop page title or fallback
								$shop_title = __( 'Shop', 'digifusion' );
								echo apply_filters( 'digifusion_digicommerce_single_product_page_title', esc_html( $shop_title ) );
							}
						} else if ( ! empty( $custom_title ) ) {
							echo wp_kses_post( $custom_title );
						} else {
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
							} else if (is_home() || is_singular( 'post' )) {
								esc_html_e( 'Blog', 'digifusion' );
							} else {
								echo wp_kses_post( get_the_title() );
							}
						}
						?>
					</<?php echo esc_attr( $tag ); ?>>

					<?php
					if ( ! empty( $page_description ) ) {
						echo '<div class="digi-page-description">';
							echo wp_kses_post( wpautop( $page_description ) );
						echo '</div>';

						echo '</div>';
					}

					// Add breadcrumbss
					DigiFusion_Breadcrumbs::get_instance()->display_breadcrumbs();
					?>
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
endif;

/**
 * DigiFusion after page header.
 */
do_action( 'digifusion_after_page_header' );