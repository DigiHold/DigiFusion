<?php
/**
 * The template for displaying the footer.
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
					<?php
					/**
					 * DigiFusion after main container.
					 */
					do_action( 'digifusion_after_main_container' );
					?>
					</div>
					<?php
					/**
					 * DigiFusion after main.
					 */
					do_action( 'digifusion_after_main' );
					?>
				</main>
			</div>

			<?php
			/**
			 * DigiFusion before footer.
			 */
			do_action( 'digifusion_before_footer' );
			?>

			<?php if ( ! function_exists( 'digifusion_footer' ) || ! digifusion_footer() ) :
				// Check if footer is disabled
				$disable_footer = get_the_ID() ? get_post_meta(get_the_ID(), 'digifusion_disable_footer', true) : false;
		
				if (!$disable_footer) :
					?>
					<footer id="colophon" class="site-footer" <?php echo wp_kses_post( digifusion_get_schema_markup('footer') ); ?>>
						<?php
						/**
						 * DigiFusion before footer container.
						 */
						do_action( 'digifusion_before_footer_container' );
						?>
						<div class="container">
							<?php
							/**
							 * DigiFusion before footer widgets.
							 */
							do_action( 'digifusion_before_footer_widgets' );
							?>
							<div class="site-footer-container">
								<div class="site-footer-col">
									<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
										<?php dynamic_sidebar( 'footer-1' ); ?>
									<?php endif; ?>
								</div>

								<div class="site-footer-col">
									<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
										<?php dynamic_sidebar( 'footer-2' ); ?>
									<?php endif; ?>
								</div>

								<div class="site-footer-col">
									<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
										<?php dynamic_sidebar( 'footer-3' ); ?>
									<?php endif; ?>
								</div>

								<div class="site-footer-col">
									<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
										<?php dynamic_sidebar( 'footer-4' ); ?>
									<?php endif; ?>
								</div>
							</div>
							<?php
							/**
							 * DigiFusion after footer widgets.
							 */
							do_action( 'digifusion_after_footer_widgets' );
							?>

							<?php
							/**
							 * DigiFusion before footer bottom.
							 */
							do_action( 'digifusion_before_footer_bottom' );
							?>
							<div class="site-footer-bottom">
								<?php
								/**
								 * DigiFusion before footer copyright.
								 */
								do_action( 'digifusion_before_footer_copyright' );
								?>
								<div class="site-footer-copyright">
									<div class="digi-copyright" <?php echo wp_kses_post( digifusion_get_schema_markup('organization') ); ?>>
										<?php
										echo wp_kses_post(
											get_theme_mod(
												'digifusion_copyright_text',
												sprintf(
													/* translators: %1$s: Current year, %2$s: Site name */
													esc_html__( '© %1$s %2$s. All rights reserved.', 'digifusion' ),
													date_i18n( 'Y' ),
													'<span ' . wp_kses_post( digifusion_get_schema_property('name' ) ) . '>' . get_bloginfo( 'name' ) . '</span>'
												)
											)
										);
										?>
									</div>
								</div>
								<?php
								/**
								 * DigiFusion after footer copyright.
								 */
								do_action( 'digifusion_after_footer_copyright' );
								?>

								<nav class="site-footer-nav">
									<ul class="digi-nav-menu">
										<?php
										wp_nav_menu(
											array(
												'container'       => '',
												'menu_class'      => '',
												'theme_location'  => 'footer',
												'items_wrap'      => '%3$s',
												'li_class'        => '',
												'fallback_cb'     => false,
											)
										);
										?>
									</ul>
								</nav>
							</div>
							<?php
							/**
							 * DigiFusion after footer bottom.
							 */
							do_action( 'digifusion_after_footer_bottom' );
							?>
						</div>
						<?php
						/**
						 * DigiFusion after footer container.
						 */
						do_action( 'digifusion_after_footer_container' );
						?>
					</footer><!-- #colophon -->
					<?php
				endif;
			endif; ?>
			
			<?php
			/**
			 * DigiFusion after footer.
			 */
			do_action( 'digifusion_after_footer' );
			?>
		</div>
		<?php
		/**
		 * DigiFusion after wrapper.
		 */
		do_action( 'digifusion_after_wrapper' );
		?>
		<?php wp_footer(); ?>
	</body>
</html>