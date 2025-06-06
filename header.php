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
			<?php if ( ! function_exists( 'digifusion_header' ) || ! digifusion_header() ) : ?>
				<header class="site-header" <?php echo wp_kses_post( digifusion_get_schema_markup( 'header' ) ); ?>>
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
									$custom_logo_id = get_theme_mod( 'custom_logo' );
									$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
									if ( has_custom_logo() ) {
										echo '<img src="' . esc_url( $logo[0] ) . '" width="200" class="digi-logo-img" alt="' . get_bloginfo( 'name' ) . '" '. wp_kses_post( digifusion_get_schema_markup( 'organization' ) ) .'>';
									} else {
										echo '<span class="digi-site-name" '. wp_kses_post( digifusion_get_schema_property( 'name' ) ) .'>' . get_bloginfo('name') . '</span>';
									}
									?>
								</a>
							</div>

							<?php
							/**
							 * DigiFusion before header nav.
							 */
							do_action( 'digifusion_before_header_nav' );
							?>

							<nav class="digi-header-nav" <?php echo wp_kses_post( digifusion_get_schema_markup( 'navigation' ) ); ?>>
								<ul class="digi-nav-menu">
									<?php
									wp_nav_menu(
										array(
											'container'       => '',
											'menu_class'      => '',
											'theme_location'  => 'primary',
											'items_wrap'      => '%3$s',
											'li_class'        => '',
											'fallback_cb'     => false,
										)
									);
									?>
								</ul>
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

						<div class="digi-menu-toggle">
							<div class="digi-menu-bars">
								<span></span>
								<span></span>
								<span></span>
							</div>
						</div>
					</div>
				</header>
			<?php endif; ?>
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
				?>
                <main class="site-main" <?php echo wp_kses_post( digifusion_get_schema_markup('main-content') ); ?>>
					<?php
					if ( !is_front_page() ) {
						get_template_part( 'template-parts/page-header' );
					}
					?>
					<?php
					/**
					 * DigiFusion before main container.
					 */
					do_action( 'digifusion_before_main_container' );
					?>
					<div class="container">