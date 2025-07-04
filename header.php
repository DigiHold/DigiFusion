<?php
/**
 * The template for displaying the header.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$post_id = get_the_ID();

// Get page-specific header settings
$page_header_type = $post_id ? get_post_meta($post_id, 'digifusion_header_type', true) : '';
$custom_logo_id = $post_id ? get_post_meta($post_id, 'digifusion_custom_logo', true) : 0;
$custom_logo_id = apply_filters( 'digifusion_logo_id', $custom_logo_id );
$menu_colors = $post_id ? get_post_meta($post_id, 'digifusion_menu_colors', true) : array();

// Determine header type (page-specific overrides theme default)
$header_type = $page_header_type ? $page_header_type : get_theme_mod('digifusion_header_type', 'minimal');
$header_type = apply_filters( 'digifusion_header_classes', $header_type );
$header_classes = array('site-header');
if ('transparent' === $header_type) {
    $header_classes[] = 'transparent';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <?php wp_head(); ?>
    </head>

    <body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
		<a class="skip-link screen-reader-text" href="#content" title="<?php esc_attr_e( 'Skip to content', 'digifusion' ); ?>">
			<?php esc_html_e( 'Skip to content', 'digifusion' ); ?>
		</a>
		<?php
		/**
		 * DigiFusion before wrapper hook.
		 */
		do_action( 'digifusion_before_wrapper' );
		?>
        <div id="page" class="digi-page-wrapper">
			<?php
			/**
			 * DigiFusion before header.
			 */
			do_action( 'digifusion_before_header' );
			?>
			<?php if ( ! function_exists( 'digifusion_header' ) || ! digifusion_header() ) :
				// Check if header is disabled for this page
				$disable_header = $post_id ? get_post_meta( $post_id, 'digifusion_disable_header', true ) : false;
				$disable_header = apply_filters( 'digifusion_show_header', $disable_header );

				if ( ! $disable_header ) :
					?>
					<header class="<?php echo esc_attr( implode( ' ', $header_classes ) ); ?>" <?php echo wp_kses_post( digifusion_get_schema_markup( 'header' ) ); ?>>
						<?php
						/**
						 * DigiFusion before header container.
						 */
						do_action( 'digifusion_before_header_container' );
						?>
						<div class="container">
							<?php
							/**
							 * DigiFusion before header inner.
							 */
							do_action( 'digifusion_before_header_inner' );
							?>
							<div class="site-header-inner">
								<?php
								/**
								 * DigiFusion before header logo.
								 */
								do_action( 'digifusion_before_header_logo' );
								?>
								<div class="site-branding" <?php echo wp_kses_post( digifusion_get_schema_markup( 'organization' ) ); ?>>
									<a href="<?php echo esc_url( home_url() ); ?>" class="digi-site-logo" aria-label="<?php echo get_bloginfo(); ?>" <?php echo wp_kses_post( digifusion_get_schema_property( 'url' ) ); ?>>
										<?php
										// Check for page-specific custom logo first, then theme logo
										$logo_id = $custom_logo_id ? $custom_logo_id : get_theme_mod('custom_logo');
										
										if ($logo_id) {
											$logo = wp_get_attachment_image_src($logo_id, 'full');
											if ($logo) {
												echo '<img src="' . esc_url($logo[0]) . '" width="200" class="digi-logo-img" alt="' . get_bloginfo('name') . '" ' . wp_kses_post(digifusion_get_schema_markup('organization')) . '>';
											} else {
												echo '<span class="digi-site-name" ' . wp_kses_post(digifusion_get_schema_property('name')) . '>' . get_bloginfo('name') . '</span>';
											}
										} else {
											echo '<span class="digi-site-name" ' . wp_kses_post(digifusion_get_schema_property('name')) . '>' . get_bloginfo('name') . '</span>';
										}
										?>
									</a>
								</div>

								<button class="digi-menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'digifusion' ); ?>">
									<div class="digi-menu-bars">
										<span></span>
										<span></span>
										<span></span>
									</div>
								</button>

								<?php
								/**
								 * DigiFusion before header nav.
								 */
								do_action( 'digifusion_before_header_nav' );
								?>

								<nav class="digi-header-nav" <?php echo wp_kses_post( digifusion_get_schema_markup( 'navigation' ) ); ?>>
									<?php
									/**
									 * DigiFusion before header menu.
									 */
									do_action( 'digifusion_before_header_menu' );
									?>
									<ul class="digi-nav-menu">
										<?php
										/**
										 * DigiFusion before header menu links.
										 */
										do_action( 'digifusion_before_header_menu_links' );

										wp_nav_menu(
											array(
												'container'       => '',
												'menu_class'      => '',
												'theme_location'  => 'primary',
												'items_wrap'      => '%3$s',
												'li_class'        => '',
												'fallback_cb'     => false,
												'walker'          => new DigiFusion_Nav_Walker(),
											)
										);

										/**
										 * DigiFusion after header menu links.
										 */
										do_action( 'digifusion_after_header_menu_links' );
										?>
									</ul>
									<?php
									/**
									 * DigiFusion after header menu.
									 */
									do_action( 'digifusion_after_header_menu' );
									?>
								</nav>

								<?php
								/**
								 * DigiFusion after header inner.
								 */
								do_action( 'digifusion_after_header_nav' );
								?>
							</div>
							<?php
							/**
							 * DigiFusion after header inner.
							 */
							do_action( 'digifusion_after_header_inner' );
							?>
						</div>
						<?php
						/**
						 * DigiFusion after header container.
						 */
						do_action( 'digifusion_after_header_container' );
						?>
					</header>
					<?php
				endif;
			endif; ?>
			<?php
			/**
			 * DigiFusion after header.
			 */
			do_action( 'digifusion_after_header' );
			?>

            <div id="content" class="site-content">
				<?php
				/**
				 * DigiFusion before main.
				 */
				do_action( 'digifusion_before_main' );

				$show_page_header = ! is_front_page() && get_theme_mod( 'enable_page_header', true );
				$show_page_header = apply_filters( 'digifusion_show_page_header', $show_page_header );

				if ( $show_page_header ) {
					get_template_part( 'template-parts/page-header' );
				}
				?>
                <main class="site-main" <?php echo wp_kses_post( digifusion_get_schema_markup('main-content') ); ?>>
					<?php
					/**
					 * DigiFusion before main container.
					 */
					do_action( 'digifusion_before_main_container' );

					// Check if padding is disabled for this page
					$disable_padding   = $post_id ? get_post_meta( $post_id, 'digifusion_disable_padding', true ) : false;
					$disable_padding   = apply_filters( 'digifusion_show_padding', $disable_padding );
					$container_classes = array('container');

					if ( ! $disable_padding ) :
						$container_classes[] = 'sp';
					endif;
					?>
					<div class="<?php echo esc_attr( implode( ' ', $container_classes ) ); ?>">