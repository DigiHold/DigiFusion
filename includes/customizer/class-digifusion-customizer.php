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
	 * Global colors mapping.
	 *
	 * @var array
	 */
	private $global_colors = array(
		'crimson'    => '#e74c3c',
		'ruby'       => '#c0392b',
		'yellow'     => '#ffd83b',
		'slate'      => '#34495e',
		'charcoal'   => '#2c3e50',
		'silver'     => '#ecf0f1',
		'dark-gray'  => '#716c80',
		'gray'       => '#e5e5e5',
		'green'      => '#16a34a',
		'red'        => '#fe5252',
	);

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

		// Add AJAX handler for global color updates.
		add_action( 'wp_ajax_digifusion_update_global_colors', array( $this, 'ajax_update_global_colors' ) );
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
		// Add pro notice if pro version is not active.
		if ( ! digifusion_has_pro_access() ) {
			$this->add_pro_notice_panel( $wp_customize );
		}

		// Add panels, sections, settings, and controls here.
		$this->add_general_panel( $wp_customize );
		$this->add_colors_panel( $wp_customize );
		$this->add_typography_panel( $wp_customize );
	}

	/**
	 * Add Pro Notice Panel
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_pro_notice_panel( $wp_customize ) {
		// Add Pro Notice Section.
		$wp_customize->add_section(
			new DigiFusion_Pro_Section(
				$wp_customize,
				'digifusion_pro_section',
				array(
					'title' => esc_html__( '🚀 Build Your Entire Website with DigiFusion Pro', 'digifusion' ),
					'description' => esc_html__( 'Create and customize every part of your site - headers, footers, pages, and templates with our powerful site builder.', 'digifusion' ),
					'button_text' => esc_html__( 'Upgrade Now', 'digifusion' ),
					'button_url' => 'https://digifusion.me/pricing/?utm_source=digifusion-customize&utm_campaign=gopro&utm_medium=wp-dash',
					'priority' => 999999,
				)
			)
		);
	}

	/**
	 * Add General Panel with various controls
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_general_panel( $wp_customize ) {
		// Add General section.
		$wp_customize->add_section(
			'digifusion_general_settings',
			array(
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'title'          => __( 'General', 'digifusion' ),
				'description'    => __( 'Customize general theme settings', 'digifusion' ),
			)
		);

		// Header Type Setting.
		$wp_customize->add_setting(
			'digifusion_header_type',
			array(
				'default'           => 'minimal',
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Select_Control(
				$wp_customize,
				'digifusion_header_type',
				array(
					'label'         => __( 'Header Type', 'digifusion' ),
					'section'       => 'digifusion_general_settings',
					'is_responsive' => false,
					'choices'       => array(
						array( 'value' => 'minimal', 'label' => __( 'Minimal', 'digifusion' ) ),
						array( 'value' => 'transparent', 'label' => __( 'Transparent', 'digifusion' ) ),
					),
					'default_value' => 'minimal',
				)
			)
		);

		// Enable Schema Markup Setting.
		$wp_customize->add_setting(
			'enable_schema_markup',
			array(
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'enable_schema_markup',
				array(
					'label'       => __( 'Enable Schema Markup', 'digifusion' ),
					'description' => __( 'Add structured data to improve SEO and search engine understanding', 'digifusion' ),
					'section'     => 'digifusion_general_settings',
				)
			)
		);

		// Enable Page Header Setting.
		$wp_customize->add_setting(
			'enable_page_header',
			array(
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'enable_page_header',
				array(
					'label'       => __( 'Enable Page Header', 'digifusion' ),
					'description' => __( 'Show page header with title and breadcrumbs on pages and posts', 'digifusion' ),
					'section'     => 'digifusion_general_settings',
				)
			)
		);
	}

	/**
	 * Add all color settings and controls
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_colors_panel( $wp_customize ) {
		// Add Colors section.
		$wp_customize->add_section(
			'digifusion_colors_settings',
			array(
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'title'          => __( 'Colors', 'digifusion' ),
			)
		);

		// Global Colors
		$wp_customize->add_setting(
			'digifusion_global_colors',
			array(
				'default'           => json_encode( $this->global_colors ),
				'sanitize_callback' => array( $this, 'sanitize_global_colors' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_global_colors',
				array(
					'label'   => __( 'Global Colors', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => false,
					'colors'  => array(
						array(
							'key'     => 'crimson',
							'label'   => __( 'Crimson', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'ruby',
							'label'   => __( 'Ruby', 'digifusion' ),
							'default' => '#c0392b',
						),
						array(
							'key'     => 'yellow',
							'label'   => __( 'Yellow', 'digifusion' ),
							'default' => '#ffd83b',
						),
						array(
							'key'     => 'slate',
							'label'   => __( 'Slate', 'digifusion' ),
							'default' => '#34495e',
						),
						array(
							'key'     => 'charcoal',
							'label'   => __( 'Charcoal', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'silver',
							'label'   => __( 'Silver', 'digifusion' ),
							'default' => '#ecf0f1',
						),
						array(
							'key'     => 'dark-gray',
							'label'   => __( 'Dark Gray', 'digifusion' ),
							'default' => '#716c80',
						),
						array(
							'key'     => 'gray',
							'label'   => __( 'Gray', 'digifusion' ),
							'default' => '#e5e5e5',
						),
						array(
							'key'     => 'green',
							'label'   => __( 'Green', 'digifusion' ),
							'default' => '#16a34a',
						),
						array(
							'key'     => 'red',
							'label'   => __( 'Red', 'digifusion' ),
							'default' => '#fe5252',
						),
					),
				)
			)
		);

		// Body Colors
		$wp_customize->add_setting(
			'digifusion_body_colors',
			array(
				'default'           => json_encode(
					array(
						'background' => '#ffffff',
						'headings'   => '#2c3e50',
						'text'       => '#716c80',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_body_colors',
				array(
					'label'   => __( 'Body', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'background',
							'label'   => __( 'Background Color', 'digifusion' ),
							'default' => '#ffffff',
						),
						array(
							'key'     => 'headings',
							'label'   => __( 'Headings Color', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'text',
							'label'   => __( 'Text Color', 'digifusion' ),
							'default' => '#716c80',
						),
					),
				)
			)
		);

		// Button Colors
		$wp_customize->add_setting(
			'digifusion_button_colors',
			array(
				'default'           => json_encode(
					array(
						'background'       => '#e74c3c',
						'background_hover' => '#c0392b',
						'text'            => '#ffffff',
						'text_hover'      => '#ffffff',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_button_colors',
				array(
					'label'   => __( 'Buttons', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'background',
							'label'   => __( 'Background Color', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'background_hover',
							'label'   => __( 'Hover Background', 'digifusion' ),
							'default' => '#c0392b',
						),
						array(
							'key'     => 'text',
							'label'   => __( 'Text Color', 'digifusion' ),
							'default' => '#ffffff',
						),
						array(
							'key'     => 'text_hover',
							'label'   => __( 'Hover Text Color', 'digifusion' ),
							'default' => '#ffffff',
						),
					),
				)
			)
		);

		// Link Colors
		$wp_customize->add_setting(
			'digifusion_link_colors',
			array(
				'default'           => json_encode(
					array(
						'normal' => '#e74c3c',
						'hover'  => '#c0392b',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_link_colors',
				array(
					'label'   => __( 'Links', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'normal',
							'label'   => __( 'Normal', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'hover',
							'label'   => __( 'Hover', 'digifusion' ),
							'default' => '#c0392b',
						),
					),
				)
			)
		);

		// Header Colors
		$wp_customize->add_setting(
			'digifusion_header_colors',
			array(
				'default'           => json_encode(
					array(
						'background' => '#ffffff',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_header_colors',
				array(
					'label'   => __( 'Header', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'background',
							'label'   => __( 'Background Color', 'digifusion' ),
							'default' => '#ffffff',
						),
					),
				)
			)
		);

		// Menu Colors
		$wp_customize->add_setting(
			'digifusion_menu_colors',
			array(
				'default'           => json_encode(
					array(
						'normal'  => '#2c3e50',
						'hover'   => '#e74c3c',
						'current' => '#e74c3c',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_menu_colors',
				array(
					'label'   => __( 'Menu', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'normal',
							'label'   => __( 'Normal', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'hover',
							'label'   => __( 'Hover', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'current',
							'label'   => __( 'Current', 'digifusion' ),
							'default' => '#e74c3c',
						),
					),
				)
			)
		);

		// Mobile Menu Icon Colors
		$wp_customize->add_setting(
			'digifusion_mobile_icon_colors',
			array(
				'default'           => json_encode(
					array(
						'normal' => '#2c3e50',
						'hover'  => '#2c3e50',
						'active' => '#2c3e50',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_mobile_icon_colors',
				array(
					'label'   => __( 'Mobile Icon', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'normal',
							'label'   => __( 'Normal', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'hover',
							'label'   => __( 'Hover', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'active',
							'label'   => __( 'Active', 'digifusion' ),
							'default' => '#2c3e50',
						),
					),
				)
			)
		);

		// Mobile Submenu Colors
		$wp_customize->add_setting(
			'digifusion_mobile_submenu_colors',
			array(
				'default'           => json_encode(
					array(
						'background' => '#ffffff',
						'normal' => '#2c3e50',
						'hover'  => '#e74c3c',
						'active' => '#e74c3c',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_mobile_submenu_colors',
				array(
					'label'   => __( 'Mobile Submenu', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'background',
							'label'   => __( 'Background', 'digifusion' ),
							'default' => '#ffffff',
						),
						array(
							'key'     => 'normal',
							'label'   => __( 'Links Color', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'hover',
							'label'   => __( 'Hover Links Color', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'active',
							'label'   => __( 'Active Links Color', 'digifusion' ),
							'default' => '#e74c3c',
						),
					),
				)
			)
		);

		// Footer Colors
		$wp_customize->add_setting(
			'digifusion_footer_colors',
			array(
				'default'           => json_encode(
					array(
						'background'   => '#ffffff',
						'heading'      => '#2c3e50',
						'text'         => '#716c80',
						'link'         => '#e74c3c',
						'link_hover'   => '#2c3e50',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_footer_colors',
				array(
					'label'   => __( 'Footer', 'digifusion' ),
					'section' => 'digifusion_colors_settings',
					'alpha'   => true,
					'colors'  => array(
						array(
							'key'     => 'background',
							'label'   => __( 'Background Color', 'digifusion' ),
							'default' => '#ffffff',
						),
						array(
							'key'     => 'heading',
							'label'   => __( 'Headings Color', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'text',
							'label'   => __( 'Text Color', 'digifusion' ),
							'default' => '#716c80',
						),
						array(
							'key'     => 'link',
							'label'   => __( 'Links Color', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'link_hover',
							'label'   => __( 'Hover Links', 'digifusion' ),
							'default' => '#2c3e50',
						),
					),
				)
			)
		);
	}

	/**
	 * Add Typography Panel with various typography controls
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function add_typography_panel( $wp_customize ) {
		// Add Typography section.
		$wp_customize->add_section(
			'digifusion_typography_settings',
			array(
				'priority'       => 10,
				'capability'     => 'edit_theme_options',
				'title'          => __( 'Typography', 'digifusion' ),
				'description'    => __( 'Customize typography settings for your website', 'digifusion' ),
			)
		);

		// Local Fonts Toggle Setting
		$wp_customize->add_setting(
			'digifusion_typography_local_fonts',
			array(
				'default'           => false,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'digifusion_typography_local_fonts',
				array(
					'label'       => __( 'Download Google Fonts Locally', 'digifusion' ),
					'description' => __( 'Download and host Google Fonts locally for better performance and privacy', 'digifusion' ),
					'section'     => 'digifusion_typography_settings',
				)
			)
		);

		// Body Typography
		$wp_customize->add_setting(
			'digifusion_body_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1,
							'tablet'  => 1,
							'mobile'  => 1,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => 1.5,
							'tablet'  => 1.5,
							'mobile'  => 1.5,
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_body_typography',
				array(
					'label'   => __( 'Body', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// H1 Typography
		$wp_customize->add_setting(
			'digifusion_h1_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 2.25,
							'tablet'  => 2.25,
							'mobile'  => 2.25,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '700',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => 1.2,
							'tablet'  => 1.2,
							'mobile'  => 1.2,
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_h1_typography',
				array(
					'label'   => __( 'H1', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// H2 Typography
		$wp_customize->add_setting(
			'digifusion_h2_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1.5,
							'tablet'  => 1.5,
							'mobile'  => 1.5,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '700',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => 1.3,
							'tablet'  => 1.3,
							'mobile'  => 1.3,
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_h2_typography',
				array(
					'label'   => __( 'H2', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// H3 Typography
		$wp_customize->add_setting(
			'digifusion_h3_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1.25,
							'tablet'  => 1.25,
							'mobile'  => 1.25,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '700',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_h3_typography',
				array(
					'label'   => __( 'H3', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// H4 Typography
		$wp_customize->add_setting(
			'digifusion_h4_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1.125,
							'tablet'  => 1.125,
							'mobile'  => 1.125,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '700',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_h4_typography',
				array(
					'label'   => __( 'H4', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// H5 Typography
		$wp_customize->add_setting(
			'digifusion_h5_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1.125,
							'tablet'  => 1.125,
							'mobile'  => 1.125,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '700',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_h5_typography',
				array(
					'label'   => __( 'H5', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// H6 Typography
		$wp_customize->add_setting(
			'digifusion_h6_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1.125,
							'tablet'  => 1.125,
							'mobile'  => 1.125,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '700',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_h6_typography',
				array(
					'label'   => __( 'H6', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// Menu Typography
		$wp_customize->add_setting(
			'digifusion_menu_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => 1,
							'tablet'  => 1,
							'mobile'  => 1,
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_menu_typography',
				array(
					'label'   => __( 'Menu', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);

		// Footer Typography
		$wp_customize->add_setting(
			'digifusion_footer_typography',
			array(
				'default'           => json_encode(
					array(
						'fontFamily'         => '',
						'fontSize'           => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'fontSizeUnit'       => 'rem',
						'fontWeight'         => '',
						'fontStyle'          => '',
						'textTransform'      => '',
						'textDecoration'     => '',
						'lineHeight'         => array(
							'desktop' => '',
							'tablet'  => '',
							'mobile'  => '',
						),
						'lineHeightUnit'     => 'em',
						'letterSpacing'      => array(
							'desktop' => 0,
							'tablet'  => 0,
							'mobile'  => 0,
						),
						'letterSpacingUnit'  => 'px',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_typography' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Typography_Control(
				$wp_customize,
				'digifusion_footer_typography',
				array(
					'label'   => __( 'Footer', 'digifusion' ),
					'section' => 'digifusion_typography_settings',
				)
			)
		);
	}

	/**
	 * Sanitize typography setting value.
	 *
	 * @param string $value JSON string of typography values.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_typography( $value ) {
		$typography = json_decode( $value, true );
		$sanitized = array();

		if ( ! is_array( $typography ) ) {
			return json_encode( array() );
		}

		// Sanitize typography properties
		$sanitized['fontFamily'] = isset( $typography['fontFamily'] ) ? sanitize_text_field( $typography['fontFamily'] ) : '';
		$sanitized['fontSizeUnit'] = isset( $typography['fontSizeUnit'] ) ? sanitize_text_field( $typography['fontSizeUnit'] ) : 'px';
		$sanitized['fontWeight'] = isset( $typography['fontWeight'] ) ? sanitize_text_field( $typography['fontWeight'] ) : '';
		$sanitized['fontStyle'] = isset( $typography['fontStyle'] ) ? sanitize_text_field( $typography['fontStyle'] ) : 'normal';
		$sanitized['textTransform'] = isset( $typography['textTransform'] ) ? sanitize_text_field( $typography['textTransform'] ) : '';
		$sanitized['textDecoration'] = isset( $typography['textDecoration'] ) ? sanitize_text_field( $typography['textDecoration'] ) : '';
		$sanitized['lineHeightUnit'] = isset( $typography['lineHeightUnit'] ) ? sanitize_text_field( $typography['lineHeightUnit'] ) : 'em';
		$sanitized['letterSpacingUnit'] = isset( $typography['letterSpacingUnit'] ) ? sanitize_text_field( $typography['letterSpacingUnit'] ) : 'px';

		// Sanitize responsive values
		$responsive_props = array( 'fontSize', 'lineHeight', 'letterSpacing' );
		foreach ( $responsive_props as $prop ) {
			$sanitized[ $prop ] = array();
			if ( isset( $typography[ $prop ] ) && is_array( $typography[ $prop ] ) ) {
				foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
					$sanitized[ $prop ][ $device ] = isset( $typography[ $prop ][ $device ] ) ? 
						floatval( $typography[ $prop ][ $device ] ) : 0;
				}
			}
		}

		return json_encode( $sanitized );
	}

	/**
	 * Get all color-related settings.
	 *
	 * @return array
	 */
	private function get_color_settings() {
		return array(
			'digifusion_body_colors',
			'digifusion_button_colors',
			'digifusion_link_colors',
			'digifusion_header_colors',
			'digifusion_menu_colors',
			'digifusion_mobile_icon_colors',
			'digifusion_mobile_submenu_colors',
			'digifusion_footer_colors',
		);
	}

	/**
	 * Update dependent colors when global colors change.
	 *
	 * @param array $old_global_colors Previous global colors.
	 * @param array $new_global_colors New global colors.
	 */
	private function update_dependent_colors( $old_global_colors, $new_global_colors ) {
		$color_settings = $this->get_color_settings();

		foreach ( $color_settings as $setting_id ) {
			$current_value = get_theme_mod( $setting_id, '' );
			if ( empty( $current_value ) ) {
				continue;
			}

			$colors = json_decode( $current_value, true );
			if ( ! is_array( $colors ) ) {
				continue;
			}

			$updated = false;
			foreach ( $colors as $color_key => $color_value ) {
				// Check if this color matches any of the old global colors
				foreach ( $old_global_colors as $global_key => $old_color ) {
					if ( strtolower( $color_value ) === strtolower( $old_color ) ) {
						// Update with the new global color
						if ( isset( $new_global_colors[ $global_key ] ) ) {
							$colors[ $color_key ] = $new_global_colors[ $global_key ];
							$updated = true;
						}
					}
				}
			}

			// Save the updated colors if any changes were made
			if ( $updated ) {
				set_theme_mod( $setting_id, json_encode( $colors ) );
			}
		}
	}

	/**
	 * Sanitize global colors and update dependent colors.
	 *
	 * @param string $value JSON string of global colors.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_global_colors( $value ) {
		$new_colors = json_decode( $value, true );
		$sanitized = array();

		if ( ! is_array( $new_colors ) ) {
			return json_encode( $this->global_colors );
		}

		// Get the current global colors to compare
		$current_global_colors = json_decode( get_theme_mod( 'digifusion_global_colors', json_encode( $this->global_colors ) ), true );

		// Sanitize the new colors
		foreach ( $this->global_colors as $key => $default_color ) {
			if ( isset( $new_colors[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_color( $new_colors[ $key ] );
			} else {
				$sanitized[ $key ] = $default_color;
			}
		}

		// Update dependent colors if we're not in the initial load
		if ( ! empty( $current_global_colors ) && $current_global_colors !== $sanitized ) {
			$this->update_dependent_colors( $current_global_colors, $sanitized );
		}

		return json_encode( $sanitized );
	}

	/**
	 * Handle AJAX request to update global colors.
	 */
	public function ajax_update_global_colors() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'digifusion_customizer_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$global_colors = isset( $_POST['global_colors'] ) ? json_decode( stripslashes( $_POST['global_colors'] ), true ) : array();
		$affected_settings = isset( $_POST['affected_settings'] ) ? (array) $_POST['affected_settings'] : array();

		// Update the affected settings
		foreach ( $affected_settings as $setting_id => $setting_value ) {
			set_theme_mod( $setting_id, $setting_value );
		}

		wp_send_json_success( array(
			'message' => 'Global colors updated successfully',
		) );
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
	 * Sanitize color group setting value.
	 *
	 * @param string $value JSON string of color group values.
	 * @return string Sanitized JSON string.
	 */
	public function sanitize_color_group( $value ) {
		$colors = json_decode( $value, true );
		$sanitized = array();

		if ( ! is_array( $colors ) ) {
			return json_encode( array() );
		}

		foreach ( $colors as $key => $color ) {
			$sanitized[ $key ] = $this->sanitize_color( $color );
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
	 * Sanitize boolean value.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return bool
	 */
	public function sanitize_boolean( $value ) {
		// Handle various boolean representations
		if ( $value === '1' || $value === 1 || $value === 'true' || $value === true ) {
			return true;
		}
		
		if ( $value === '0' || $value === 0 || $value === 'false' || $value === false ) {
			return false;
		}
		
		return (bool) $value;
	}

	/**
	 * Enqueue scripts and styles for Customizer controls.
	 */
	public function enqueue_customizer_controls_scripts() {
		// Enqueue Google Fonts data first
		wp_enqueue_script(
			'digifusion-google-fonts',
			get_template_directory_uri() . '/assets/js/customizer/google-fonts.js',
			array(),
			DIGIFUSION_VERSION,
			true
		);

		// Enqueue the control scripts.
		wp_enqueue_script(
			'digifusion-customizer-controls',
			get_template_directory_uri() . '/assets/js/customizer/customizer-controls.js',
			array( 'customize-controls', 'wp-element', 'wp-components', 'wp-i18n', 'wp-api-fetch', 'digifusion-google-fonts' ),
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
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'digifusion_customizer_nonce' ),
				'globalColors' => $this->global_colors,
				'colorSettings' => $this->get_color_settings(),
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