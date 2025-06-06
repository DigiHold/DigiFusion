<?php
/**
 * DigiFusion Customizer Loader
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load customizer files only when the customizer is initialized.
 */
function digifusion_customizer_loader() {
    // Include the main customizer class
    require_once get_template_directory() . '/includes/customizer/class-digifusion-customizer.php';
}
add_action( 'customize_register', 'digifusion_customizer_loader', 1 );

/**
 * Load the control classes when WordPress loads the customizer.
 */
function digifusion_load_customizer_controls() {
    // Only load if WP_Customize_Control exists
    if ( class_exists( 'WP_Customize_Control' ) ) {
        // Include control classes
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-control-base.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-image-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-dimensions-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-range-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-color-picker-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-box-shadow-control.php';
		require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-rich-text-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-toggle-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-text-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-select-control.php';
        require_once get_template_directory() . '/includes/customizer/controls/class-digifusion-button-group-control.php';
    }
}
add_action( 'customize_register', 'digifusion_load_customizer_controls', 0 );