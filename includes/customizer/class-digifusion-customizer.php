<?php
/**
 * DigiFusion Theme Customizer
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Customizer Class
 * 
 * Handles all customizer functionality for the DigiFusion theme.
 */
class DigiFusion_Customizer {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add customizer settings and controls.
		add_action( 'customize_register', array( $this, 'register_customizer_settings' ) );
		
		// Add scripts and styles for Customizer controls.
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_controls_scripts' ) );
		
		// Add scripts for Customizer preview.
		add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview_scripts' ) );
	}

	/**
	 * Get instance of this class.
	 *
	 * @return DigiFusion_Customizer
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register customizer settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function register_customizer_settings( $wp_customize ) {
		// Add panels, sections, settings, and controls here.
		$this->add_general_panel( $wp_customize );
	}

	/**
	 * Add General Panel with various controls
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_general_panel( $wp_customize ) {
		// Add General Panel.
		$wp_customize->add_panel(
			'digifusion_general_panel',
			array(
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'title'          => __( 'General Options', 'digifusion' ),
				'description'    => __( 'Customize general theme settings', 'digifusion' ),
			)
		);

		// Basic Settings Section.
		$wp_customize->add_section(
			'digifusion_basic_settings',
			array(
				'title'       => __( 'Basic Settings', 'digifusion' ),
				'panel'       => 'digifusion_general_panel',
				'priority'    => 10,
			)
		);

		// Text Control.
		$wp_customize->add_setting(
			'digifusion_custom_text_setting',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Text_Control(
				$wp_customize,
				'digifusion_custom_text_setting',
				array(
					'label'       => __( 'Custom Text Setting', 'digifusion' ),
					'section'     => 'digifusion_basic_settings',
				)
			)
		);

		// Select Control.
		$wp_customize->add_setting(
			'digifusion_select_setting',
			array(
				'default'           => 'option1',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Select_Control(
				$wp_customize,
				'digifusion_select_setting',
				array(
					'label'       => __( 'Select Setting', 'digifusion' ),
					'section'     => 'digifusion_basic_settings',
					'choices'     => array(
						array( 'value' => 'option1', 'label' => __( 'Option 1', 'digifusion' ) ),
						array( 'value' => 'option2', 'label' => __( 'Option 2', 'digifusion' ) ),
						array( 'value' => 'option3', 'label' => __( 'Option 3', 'digifusion' ) ),
					),
				)
			)
		);

		// Button Group Control.
		$wp_customize->add_setting(
			'digifusion_button_group_setting',
			array(
				'default'           => json_encode(
					array(
						'desktop' => 'left',
						'tablet'  => 'center',
						'mobile'  => 'center',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_responsive_value' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Button_Group_Control(
				$wp_customize,
				'digifusion_button_group_setting',
				array(
					'label'         => __( 'Text Alignment', 'digifusion' ),
					'section'       => 'digifusion_basic_settings',
					'is_responsive' => true,
					'choices'       => array(
						array( 'value' => 'left', 'label' => __( 'Left', 'digifusion' ) ),
						array( 'value' => 'center', 'label' => __( 'Center', 'digifusion' ) ),
						array( 'value' => 'right', 'label' => __( 'Right', 'digifusion' ) ),
					),
					'default_value' => 'left',
					'default_values' => array(
						'desktop' => 'left',
						'tablet'  => 'center',
						'mobile'  => 'center',
					),
				)
			)
		);

		// Toggle Control.
		$wp_customize->add_setting(
			'digifusion_toggle_setting',
			array(
				'default'           => false,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'digifusion_toggle_setting',
				array(
					'label'       => __( 'Toggle Setting', 'digifusion' ),
					'section'     => 'digifusion_basic_settings',
				)
			)
		);

		// Rich Text Control.
		$wp_customize->add_setting(
			'digifusion_rich_text_setting',
			array(
				'default'           => '',
				'sanitize_callback' => 'wp_kses_post',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Rich_Text_Control(
				$wp_customize,
				'digifusion_rich_text_setting',
				array(
					'label'       => __( 'Rich Text Setting', 'digifusion' ),
					'section'     => 'digifusion_basic_settings',
				)
			)
		);

		// Image Upload Control.
		$wp_customize->add_setting(
			'digifusion_image_upload',
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Image_Control(
				$wp_customize,
				'digifusion_image_upload',
				array(
					'label'       => __( 'Upload Image', 'digifusion' ),
					'section'     => 'digifusion_basic_settings',
				)
			)
		);

		// Dimensions Control - Responsive.
		$wp_customize->add_setting(
			'digifusion_dimensions_setting',
			array(
				'default'           => json_encode(
					array(
						'desktop' => array(
							'top'    => '',
							'right'  => '',
							'bottom' => '',
							'left'   => '',
							'unit'   => 'px',
						),
						'tablet'  => array(
							'top'    => '',
							'right'  => '',
							'bottom' => '',
							'left'   => '',
							'unit'   => 'px',
						),
						'mobile'  => array(
							'top'    => '',
							'right'  => '',
							'bottom' => '',
							'left'   => '',
							'unit'   => 'px',
						),
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_dimensions' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Dimensions_Control(
				$wp_customize,
				'digifusion_dimensions_setting',
				array(
					'label'        => __( 'Padding', 'digifusion' ),
					'section'      => 'digifusion_basic_settings',
					'is_responsive' => true,
					'units'        => array(
						array( 'value' => 'px', 'label' => 'px' ),
						array( 'value' => 'rem', 'label' => 'rem' ),
						array( 'value' => 'em', 'label' => 'em' ),
						array( 'value' => '%', 'label' => '%' ),
					),
				)
			)
		);

		// Range Control - Responsive.
		$wp_customize->add_setting(
			'digifusion_range_setting',
			array(
				'default'           => json_encode(
					array(
						'desktop' => array(
							'value' => '',
							'unit'  => 'px',
						),
						'tablet'  => array(
							'value' => '',
							'unit'  => 'px',
						),
						'mobile'  => array(
							'value' => '',
							'unit'  => 'px',
						),
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_range' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Range_Control(
				$wp_customize,
				'digifusion_range_setting',
				array(
					'label'        => __( 'Font Size', 'digifusion' ),
					'section'      => 'digifusion_basic_settings',
					'is_responsive' => true,
					'min'          => 0,
					'max'          => 100,
					'step'         => 1,
					'units'        => array(
						array( 'value' => 'px', 'label' => 'px' ),
						array( 'value' => 'rem', 'label' => 'rem' ),
						array( 'value' => 'em', 'label' => 'em' ),
						array( 'value' => '%', 'label' => '%' ),
					),
				)
			)
		);

		// Color Picker Control.
		$wp_customize->add_setting(
			'digifusion_color_setting',
			array(
				'default'           => '#000000',
				'sanitize_callback' => array( $this, 'sanitize_color' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_color_setting',
				array(
					'label'      => __( 'Text Color', 'digifusion' ),
					'section'    => 'digifusion_basic_settings',
					'alpha'      => true,
				)
			)
		);

		// Box Shadow Control.
		$wp_customize->add_setting(
			'digifusion_box_shadow_setting',
			array(
				'default'           => json_encode(
					array(
						'normal' => array(
							'enable'     => false,
							'color'      => 'rgba(0, 0, 0, 0.2)',
							'horizontal' => 0,
							'vertical'   => 0,
							'blur'       => 0,
							'spread'     => 0,
							'position'   => 'outset',
						),
						'hover'  => array(
							'enable'     => false,
							'color'      => 'rgba(0, 0, 0, 0.2)',
							'horizontal' => 0,
							'vertical'   => 0,
							'blur'       => 0,
							'spread'     => 0,
							'position'   => 'outset',
						),
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_box_shadow' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Box_Shadow_Control(
				$wp_customize,
				'digifusion_box_shadow_setting',
				array(
					'label'       => __( 'Box Shadow', 'digifusion' ),
					'section'     => 'digifusion_basic_settings',
				)
			)
		);
	}

	/**
	 * Sanitize color value allowing for hex or rgba.
	 *
	 * @param string $color Color to sanitize.
	 * @return string Sanitized color.
	 */
	public function sanitize_color( $color ) {
		if ( empty( $color ) ) {
			return '';
		}

		// Return hex colors as is
		if ( sanitize_hex_color( $color ) ) {
			return $color;
		}

		// Sanitize rgba colors
		if ( strpos( $color, 'rgba' ) !== false ) {
			$color = str_replace( ' ', '', $color );
			if ( preg_match( '/^rgba\((\d{1,3}),(\d{1,3}),(\d{1,3}),([0-9\.]{1,4})\)$/', $color ) ) {
				return $color;
			}
		}

		// If we get here, sanitize as a regular text field
		return sanitize_text_field( $color );
	}

	/**
	 * Sanitize dimensions setting value.
	 *
	 * @param string $value JSON string of dimensions.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_dimensions( $value ) {
		$values = json_decode( $value, true );
		$sanitized = array();

		if ( ! is_array( $values ) ) {
			return json_encode( array() );
		}

		foreach ( $values as $device => $dims ) {
			$sanitized[ $device ] = array(
				'top'    => isset( $dims['top'] ) ? sanitize_text_field( $dims['top'] ) : '',
				'right'  => isset( $dims['right'] ) ? sanitize_text_field( $dims['right'] ) : '',
				'bottom' => isset( $dims['bottom'] ) ? sanitize_text_field( $dims['bottom'] ) : '',
				'left'   => isset( $dims['left'] ) ? sanitize_text_field( $dims['left'] ) : '',
				'unit'   => isset( $dims['unit'] ) ? sanitize_text_field( $dims['unit'] ) : 'px',
			);
		}

		return json_encode( $sanitized );
	}

	/**
	 * Sanitize range setting value.
	 *
	 * @param string $value JSON string of range values.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_range( $value ) {
		$values = json_decode( $value, true );
		$sanitized = array();

		if ( ! is_array( $values ) ) {
			return json_encode( array() );
		}

		foreach ( $values as $device => $setting ) {
			$sanitized[ $device ] = array(
				'value' => isset( $setting['value'] ) ? sanitize_text_field( $setting['value'] ) : '',
				'unit'  => isset( $setting['unit'] ) ? sanitize_text_field( $setting['unit'] ) : 'px',
			);
		}

		return json_encode( $sanitized );
	}

	/**
	 * Sanitize box shadow setting value.
	 *
	 * @param string $value JSON string of box shadow properties.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_box_shadow( $value ) {
		$values = json_decode( $value, true );
		$sanitized = array();

		if ( ! is_array( $values ) ) {
			return json_encode( array() );
		}

		foreach ( $values as $state => $props ) {
			$sanitized[ $state ] = array(
				'enable'     => isset( $props['enable'] ) ? (bool) $props['enable'] : false,
				'color'      => isset( $props['color'] ) ? sanitize_text_field( $props['color'] ) : 'rgba(0, 0, 0, 0.2)',
				'horizontal' => isset( $props['horizontal'] ) ? intval( $props['horizontal'] ) : 0,
				'vertical'   => isset( $props['vertical'] ) ? intval( $props['vertical'] ) : 0,
				'blur'       => isset( $props['blur'] ) ? absint( $props['blur'] ) : 0,
				'spread'     => isset( $props['spread'] ) ? intval( $props['spread'] ) : 0,
				'position'   => isset( $props['position'] ) && in_array( $props['position'], array( 'outset', 'inset' ), true ) ? $props['position'] : 'outset',
			);
		}

		return json_encode( $sanitized );
	}

	/**
	 * Sanitize boolean value.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return bool
	 */
	public function sanitize_boolean( $value ) {
		return (bool) $value;
	}

	/**
	 * Sanitize responsive value.
	 *
	 * @param string $value JSON string of responsive values.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_responsive_value( $value ) {
		$responsive_values = json_decode( $value, true );
		
		if ( ! is_array( $responsive_values ) ) {
			return json_encode( array() );
		}
		
		$sanitized = array();
		foreach ( $responsive_values as $device => $val ) {
			$sanitized[ $device ] = sanitize_text_field( $val );
		}
		
		return json_encode( $sanitized );
	}

	/**
	 * Enqueue scripts and styles for Customizer controls.
	 */
	public function enqueue_customizer_controls_scripts() {
		// Enqueue the control scripts.
		wp_enqueue_script(
			'digifusion-customizer-controls',
			get_template_directory_uri() . '/assets/js/customizer/customizer-controls.js',
			array( 'customize-controls', 'wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
			DIGIFUSION_VERSION,
			true
		);

		// Enqueue the control styles.
		wp_enqueue_style(
			'digifusion-customizer-controls',
			get_template_directory_uri() . '/assets/css/customizer/customizer-controls.css',
			array(),
			DIGIFUSION_VERSION
		);

		// Pass data to the script.
		wp_localize_script(
			'digifusion-customizer-controls',
			'digifusionCustomizer',
			array(
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'digifusion_customizer_nonce' ),
				'settings' => array(
				),
			)
		);
	}

	/**
	 * Enqueue scripts for Customizer preview.
	 */
	public function enqueue_customizer_preview_scripts() {
		wp_enqueue_script(
			'digifusion-customizer-preview',
			get_template_directory_uri() . '/assets/js/customizer/customizer-preview.js',
			array( 'customize-preview' ),
			DIGIFUSION_VERSION,
			true
		);
	}
}

// Initialize the customizer.
DigiFusion_Customizer::get_instance();