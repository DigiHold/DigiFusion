<?php
/**
 * The sidebar containing the main widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Don't display sidebar if no widgets are active.
if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}

/**
 * DigiFusion before sidebar.
 */
do_action( 'digifusion_before_sidebar' );
?>

<aside id="secondary" class="digi-sidebar widget-area" <?php echo wp_kses_post( digifusion_get_schema_markup( 'sidebar' ) ); ?>>
	<?php
	/**
	 * DigiFusion before sidebar inner.
	 */
	do_action( 'digifusion_before_sidebar_inner' );

	/**
	 * DigiFusion sidebar widgets.
	 */
	do_action( 'digifusion_sidebar_widgets' );

	// Display the main sidebar widgets.
	dynamic_sidebar( 'sidebar-1' );

	/**
	 * DigiFusion after sidebar inner.
	 */
	do_action( 'digifusion_after_sidebar_inner' );
	?>
</aside><!-- #secondary -->

<?php
/**
 * DigiFusion after sidebar.
 */
do_action( 'digifusion_after_sidebar' );