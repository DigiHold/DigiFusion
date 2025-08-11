<?php
/**
 * DigiFusion Dynamic CSS Generator
 *
 * Generates and manages dynamic CSS from customizer settings using wp_add_inline_style().
 *
 * @package DigiFusion
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Dynamic CSS Class
 */
class DigiFusion_Dynamic_CSS {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * CSS rules collector
	 *
	 * @var array
	 */
	private $css_rules = array(
		'base'    => array(),
		'mobile'  => array(),
		'tablet'  => array(),
		'desktop' => array(),
	);

	/**
	 * Default color values
	 *
	 * @var array
	 */
	private $default_colors = array(
		'digifusion_global_colors' => array(
			'primary'    => '#7091e6',
			'secondary'  => '#3d52a0',
			'dark'       => '#27293b',
			'neutral'    => '#ecf0f1',
			'dark-gray'  => '#716c80',
			'gray'       => '#e5e5e5',
			'green'      => '#16a34a',
			'red'        => '#fe5252',
		),
		'digifusion_body_colors' => array(
			'background' => '#ffffff',
			'headings'   => '#27293b',
			'text'       => '#716c80',
		),
		'digifusion_button_colors' => array(
			'background'       => '#7091e6',
			'background_hover' => '#3d52a0',
			'text'             => '#ffffff',
			'text_hover'       => '#ffffff',
		),
		'digifusion_link_colors' => array(
			'normal' => '#7091e6',
			'hover'  => '#3d52a0',
		),
		'digifusion_header_colors' => array(
			'background' => '#ffffff',
		),
		'digifusion_menu_colors' => array(
			'normal'  => '#27293b',
			'hover'   => '#7091e6',
			'current' => '#7091e6',
		),
		'digifusion_mobile_icon_colors' => array(
			'normal' => '#27293b',
			'hover'  => '#27293b',
			'active' => '#27293b',
		),
		'digifusion_mobile_submenu_colors' => array(
			'background' => '#ffffff',
			'normal'     => '#27293b',
			'hover'      => '#7091e6',
			'active'     => '#7091e6',
		),
		'digifusion_footer_colors' => array(
			'background' => '#ffffff',
			'heading'    => '#27293b',
			'text'       => '#716c80',
			'link'       => '#7091e6',
			'link_hover' => '#27293b',
		),
	);

	/**
	 * Default typography values - ACTUAL theme defaults from customizer
	 *
	 * @var array
	 */
	private $default_typography = array(
		'digifusion_body_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1, 'tablet' => 1, 'mobile' => 1 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => 1.5, 'tablet' => 1.5, 'mobile' => 1.5 ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_headings1_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 2.25, 'tablet' => 2.25, 'mobile' => 2.25 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '700',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => 1.2, 'tablet' => 1.2, 'mobile' => 1.2 ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_headings2_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1.5, 'tablet' => 1.5, 'mobile' => 1.5 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '700',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => 1.3, 'tablet' => 1.3, 'mobile' => 1.3 ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_headings3_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1.25, 'tablet' => 1.25, 'mobile' => 1.25 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '700',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_headings4_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1.125, 'tablet' => 1.125, 'mobile' => 1.125 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '700',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_headings5_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1.125, 'tablet' => 1.125, 'mobile' => 1.125 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '700',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_headings6_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1.125, 'tablet' => 1.125, 'mobile' => 1.125 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '700',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_menu_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => 1, 'tablet' => 1, 'mobile' => 1 ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
		'digifusion_footer_typo' => array(
			'fontFamily'         => '',
			'fontSize'           => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'fontSizeUnit'       => 'rem',
			'fontWeight'         => '',
			'fontStyle'          => '',
			'textTransform'      => '',
			'textDecoration'     => '',
			'lineHeight'         => array( 'desktop' => '', 'tablet' => '', 'mobile' => '' ),
			'lineHeightUnit'     => 'em',
			'letterSpacing'      => array( 'desktop' => 0, 'tablet' => 0, 'mobile' => 0 ),
			'letterSpacingUnit'  => 'px',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Get instance of this class.
	 *
	 * @return DigiFusion_Dynamic_CSS
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Enqueue dynamic CSS on frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dynamic_css' ), 20 );
	}

	/**
	 * Reset CSS rules collector
	 */
	private function reset_css_rules() {
		$this->css_rules = array(
			'base'    => array(),
			'mobile'  => array(),
			'tablet'  => array(),
			'desktop' => array(),
		);
	}

	/**
	 * Add CSS rule to collector
	 *
	 * @param string $selector CSS selector
	 * @param string $property CSS property
	 * @param string $value CSS value
	 * @param string $device Device type (base, mobile, tablet, desktop)
	 */
	public function add_css_rule( $selector, $property, $value, $device = 'base' ) {
		if ( empty( $value ) && $value !== '0' && $value !== 0 ) {
			return;
		}

		if ( ! isset( $this->css_rules[ $device ][ $selector ] ) ) {
			$this->css_rules[ $device ][ $selector ] = array();
		}

		$this->css_rules[ $device ][ $selector ][ $property ] = $value;
	}

	/**
	 * Generate external CSS rules from hooks
	 */
	private function generate_external_css_rules() {
		/**
		 * Hook to allow external code to add CSS rules
		 *
		 * @param DigiFusion_Dynamic_CSS $this Current instance
		 */
		do_action( 'digifusion_dynamic_css_generate', $this );
	}

	/**
	 * Check if any customizations exist
	 *
	 * @return bool
	 */
	private function has_customizations() {
		// Check color customizations
		foreach ( $this->default_colors as $setting_key => $default_values ) {
			$current_value = get_theme_mod( $setting_key );
			$current_colors = $this->parse_color_group( $current_value );
			
			if ( empty( $current_colors ) ) {
				continue;
			}
			
			foreach ( $default_values as $color_key => $default_color ) {
				if ( isset( $current_colors[ $color_key ] ) && $current_colors[ $color_key ] !== $default_color ) {
					return true;
				}
			}
		}
		
		// Check typography customizations
		foreach ( $this->default_typography as $setting_key => $default_values ) {
			$current_value = get_theme_mod( $setting_key );
			$current_typography = $this->parse_typography_setting( $current_value );
			
			if ( $this->is_typography_customized( $current_typography, $default_values ) ) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Check if typography is customized compared to defaults
	 *
	 * @param array $current Current typography values
	 * @param array $defaults Default typography values
	 * @return bool
	 */
	private function is_typography_customized( $current, $defaults ) {
		if ( empty( $current ) ) {
			return false;
		}

		// Check font family (only if it's a Google Font)
		if ( ! empty( $current['fontFamily'] ) && ! $this->is_system_font( $current['fontFamily'] ) ) {
			return true;
		}

		// Check simple properties against defaults
		$simple_props = array( 'fontWeight', 'fontStyle', 'textTransform', 'textDecoration', 'fontSizeUnit', 'lineHeightUnit', 'letterSpacingUnit' );
		foreach ( $simple_props as $prop ) {
			$current_val = isset( $current[ $prop ] ) ? $current[ $prop ] : '';
			$default_val = isset( $defaults[ $prop ] ) ? $defaults[ $prop ] : '';
			
			if ( $current_val !== $default_val ) {
				return true;
			}
		}

		// Check responsive properties
		$responsive_props = array( 'fontSize', 'lineHeight', 'letterSpacing' );
		foreach ( $responsive_props as $prop ) {
			if ( isset( $current[ $prop ] ) && is_array( $current[ $prop ] ) ) {
				foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
					$current_val = isset( $current[ $prop ][ $device ] ) ? $current[ $prop ][ $device ] : '';
					$default_val = isset( $defaults[ $prop ][ $device ] ) ? $defaults[ $prop ][ $device ] : '';
					
					// Convert values for comparison
					if ( $current_val !== '' ) {
						$current_val = (string) $current_val;
					}
					if ( $default_val !== '' ) {
						$default_val = (string) $default_val;
					}
					
					if ( $current_val !== $default_val ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if a font family is a system font.
	 *
	 * @param string $font_family Font family.
	 * @return bool True if system font, false otherwise.
	 */
	private function is_system_font( $font_family ) {
		// Check if the font family contains commas (indicating a font stack)
		if ( strpos( $font_family, ',' ) !== false ) {
			return true;
		}
		
		// Common system fonts
		$system_fonts = array(
			'serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui',
			'Arial', 'Helvetica', 'Times New Roman', 'Times', 'Courier New', 
			'Courier', 'Verdana', 'Georgia', 'Palatino', 'Garamond', 'Bookman', 
			'Tahoma', 'Trebuchet MS', 'Impact', 'Comic Sans MS',
		);
		
		return in_array( $font_family, $system_fonts, true );
	}

	/**
	 * Generate CSS content from customizer settings.
	 *
	 * @return string CSS content
	 */
	public function generate_css_content() {
		// Check if we have any customizations
		if ( ! $this->has_customizations() ) {
			return '';
		}

		// Reset CSS rules collector
		$this->reset_css_rules();

		// Generate all CSS rules
		$this->generate_color_rules();
		$this->generate_typography_rules();

		// Generate external CSS rules from hooks
		$this->generate_external_css_rules();

		// Build final CSS content
		$css_content = $this->build_css_from_rules();
		
		// Return minified CSS
		return $this->minify_css( $css_content );
	}

	/**
	 * Generate all color-related CSS rules
	 */
	private function generate_color_rules() {
		$this->generate_global_colors_rules();
		$this->generate_body_colors_rules();
		$this->generate_button_colors_rules();
		$this->generate_link_colors_rules();
		$this->generate_header_colors_rules();
		$this->generate_menu_colors_rules();
		$this->generate_mobile_icon_colors_rules();
		$this->generate_mobile_submenu_colors_rules();
		$this->generate_footer_colors_rules();
	}

	/**
	 * Generate all typography-related CSS rules
	 */
	private function generate_typography_rules() {
		$typography_mappings = array(
			'digifusion_body_typo'      => 'body',
			'digifusion_headings1_typo' => 'h1, .digi-page-title',
			'digifusion_headings2_typo' => 'h2',
			'digifusion_headings3_typo' => 'h3',
			'digifusion_headings4_typo' => 'h4',
			'digifusion_headings5_typo' => 'h5',
			'digifusion_headings6_typo' => 'h6',
			'digifusion_menu_typo'      => '.digi-header-nav a, .digi-nav-menu a',
			'digifusion_footer_typo'    => '.site-footer',
		);

		foreach ( $typography_mappings as $setting_key => $selector ) {
			$current_typography = $this->parse_typography_setting( get_theme_mod( $setting_key ) );
			$default_typography = $this->default_typography[ $setting_key ];

			if ( $this->is_typography_customized( $current_typography, $default_typography ) ) {
				$this->generate_typography_rules_for_selector( $selector, $current_typography, $default_typography );
			}
		}
	}

	/**
	 * Generate typography rules for a specific selector
	 *
	 * @param string $selector CSS selector
	 * @param array  $current Current typography values
	 * @param array  $defaults Default typography values
	 */
	private function generate_typography_rules_for_selector( $selector, $current, $defaults ) {
		// Base properties (non-responsive)
		$base_props = array( 'fontFamily', 'fontWeight', 'fontStyle', 'textTransform', 'textDecoration' );
		
		foreach ( $base_props as $prop ) {
			$current_val = isset( $current[ $prop ] ) ? $current[ $prop ] : '';
			$default_val = isset( $defaults[ $prop ] ) ? $defaults[ $prop ] : '';
			
			if ( $current_val !== $default_val && ! empty( $current_val ) ) {
				$css_prop = $this->get_css_property_name( $prop );
				
				if ( $prop === 'fontFamily' ) {
					// Simple font-family declaration without fallbacks
					$this->add_css_rule( $selector, $css_prop, "'{$current_val}'" );
				} else {
					$this->add_css_rule( $selector, $css_prop, $current_val );
				}
			}
		}

		// Responsive properties
		$responsive_props = array( 'fontSize', 'lineHeight', 'letterSpacing' );
		
		foreach ( $responsive_props as $prop ) {
			if ( isset( $current[ $prop ] ) && is_array( $current[ $prop ] ) ) {
				$unit_key = $prop . 'Unit';
				$unit = isset( $current[ $unit_key ] ) ? $current[ $unit_key ] : 'px';
				
				foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
					$current_val = isset( $current[ $prop ][ $device ] ) ? $current[ $prop ][ $device ] : '';
					$default_val = isset( $defaults[ $prop ][ $device ] ) ? $defaults[ $prop ][ $device ] : '';
					
					if ( $current_val !== $default_val && $current_val !== '' ) {
						$css_prop = $this->get_css_property_name( $prop );
						$device_key = $device === 'desktop' ? 'desktop' : $device;
						
						// Handle letter spacing 0 values
						if ( $prop === 'letterSpacing' && $current_val == 0 && $default_val != 0 ) {
							$this->add_css_rule( $selector, $css_prop, '0', $device_key );
						} elseif ( $current_val != 0 || ( $current_val == 0 && $prop === 'letterSpacing' && $default_val != 0 ) ) {
							$this->add_css_rule( $selector, $css_prop, $current_val . $unit, $device_key );
						}
					}
				}
			}
		}
	}

	/**
	 * Get CSS property name from setting key
	 *
	 * @param string $prop Property key
	 * @return string CSS property name
	 */
	private function get_css_property_name( $prop ) {
		$map = array(
			'fontFamily'     => 'font-family',
			'fontSize'       => 'font-size',
			'fontWeight'     => 'font-weight',
			'fontStyle'      => 'font-style',
			'textTransform'  => 'text-transform',
			'textDecoration' => 'text-decoration',
			'lineHeight'     => 'line-height',
			'letterSpacing'  => 'letter-spacing',
		);

		return isset( $map[ $prop ] ) ? $map[ $prop ] : $prop;
	}

	/**
	 * Build final CSS from collected rules
	 *
	 * @return string
	 */
	private function build_css_from_rules() {
		$css = '';

		// Base CSS (no media query)
		if ( ! empty( $this->css_rules['base'] ) ) {
			$css .= $this->rules_to_css( $this->css_rules['base'] );
		}

		// Desktop CSS
		if ( ! empty( $this->css_rules['desktop'] ) ) {
			$css .= $this->rules_to_css( $this->css_rules['desktop'] );
		}

		// Tablet CSS
		if ( ! empty( $this->css_rules['tablet'] ) ) {
			$css .= '@media (max-width: 991px) {' . $this->rules_to_css( $this->css_rules['tablet'] ) . '}';
		}

		// Mobile CSS
		if ( ! empty( $this->css_rules['mobile'] ) ) {
			$css .= '@media (max-width: 767px) {' . $this->rules_to_css( $this->css_rules['mobile'] ) . '}';
		}

		return $css;
	}

	/**
	 * Convert rules array to CSS string
	 *
	 * @param array $rules CSS rules array
	 * @return string
	 */
	private function rules_to_css( $rules ) {
		$css = '';

		foreach ( $rules as $selector => $properties ) {
			if ( empty( $properties ) ) {
				continue;
			}

			$css .= $selector . '{';
			foreach ( $properties as $property => $value ) {
				$css .= $property . ':' . $value . ';';
			}
			$css .= '}';
		}

		return $css;
	}

	/**
	 * Generate global colors CSS rules
	 */
	private function generate_global_colors_rules() {
		$global_colors = get_theme_mod( 'digifusion_global_colors' );
		$colors = $this->parse_color_group( $global_colors );
		$defaults = $this->default_colors['digifusion_global_colors'];
		
		if ( empty( $colors ) ) {
			return;
		}
		
		$custom_properties = array();
		
		foreach ( $colors as $key => $color ) {
			if ( ! empty( $color ) && isset( $defaults[ $key ] ) && $color !== $defaults[ $key ] ) {
				$custom_properties["--digi-{$key}"] = $color;
			}
		}
		
		if ( ! empty( $custom_properties ) ) {
			$this->css_rules['base'][':root'] = $custom_properties;
		}
	}

	/**
	 * Generate body colors CSS rules
	 */
	private function generate_body_colors_rules() {
		$body_colors = get_theme_mod( 'digifusion_body_colors' );
		$colors = $this->parse_color_group( $body_colors );
		$defaults = $this->default_colors['digifusion_body_colors'];
		
		if ( ! empty( $colors['background'] ) && $colors['background'] !== $defaults['background'] ) {
			$this->add_css_rule( 'body', 'background-color', $colors['background'] );
		}
		
		if ( ! empty( $colors['text'] ) && $colors['text'] !== $defaults['text'] ) {
			$this->add_css_rule( 'body', 'color', $colors['text'] );
		}
		
		if ( ! empty( $colors['headings'] ) && $colors['headings'] !== $defaults['headings'] ) {
			$this->add_css_rule( 'h1,.digi-page-title,h2,h3,h4,h5,h6,.digi-post-title,.digi-post-title-single,.digi-page-title,.digi-page-description p,.digi-related-title,.digi-related-post-title,.digi-author-name', 'color', $colors['headings'] );
		}
	}

	/**
	 * Generate button colors CSS rules
	 */
	private function generate_button_colors_rules() {
		$button_colors = get_theme_mod( 'digifusion_button_colors' );
		$colors = $this->parse_color_group( $button_colors );
		$defaults = $this->default_colors['digifusion_button_colors'];
		
		$normal_changed = ( ! empty( $colors['background'] ) && $colors['background'] !== $defaults['background'] ) || 
						  ( ! empty( $colors['text'] ) && $colors['text'] !== $defaults['text'] );
		
		if ( $normal_changed ) {
			$selector = 'button.digi,.digi-button,input[type="submit"],.digi-share-btn,.digi-author-social-link,.digi-search-submit,.woocommerce ul.products li.product .button';
			
			if ( ! empty( $colors['background'] ) && $colors['background'] !== $defaults['background'] ) {
				$this->add_css_rule( $selector, 'background-color', $colors['background'] );
			}
			if ( ! empty( $colors['text'] ) && $colors['text'] !== $defaults['text'] ) {
				$this->add_css_rule( $selector, 'color', $colors['text'] );
			}
		}
		
		$hover_changed = ( ! empty( $colors['background_hover'] ) && $colors['background_hover'] !== $defaults['background_hover'] ) || 
						 ( ! empty( $colors['text_hover'] ) && $colors['text_hover'] !== $defaults['text_hover'] );
		
		if ( $hover_changed ) {
			$selector = 'button.digi:hover,.digi-button:hover,input[type="submit"]:hover,.digi-share-btn:hover,.digi-author-social-link:hover,.digi-search-submit:hover,.woocommerce ul.products li.product .button:hover';
			
			if ( ! empty( $colors['background_hover'] ) && $colors['background_hover'] !== $defaults['background_hover'] ) {
				$this->add_css_rule( $selector, 'background-color', $colors['background_hover'] );
			}
			if ( ! empty( $colors['text_hover'] ) && $colors['text_hover'] !== $defaults['text_hover'] ) {
				$this->add_css_rule( $selector, 'color', $colors['text_hover'] );
			}
		}
	}

	/**
	 * Generate link colors CSS rules
	 */
	private function generate_link_colors_rules() {
		$link_colors = get_theme_mod( 'digifusion_link_colors' );
		$colors = $this->parse_color_group( $link_colors );
		$defaults = $this->default_colors['digifusion_link_colors'];
		
		if ( ! empty( $colors['normal'] ) && $colors['normal'] !== $defaults['normal'] ) {
			$this->add_css_rule( 'a,.digi-title-link,.digi-comments-link,.digi-author-name', 'color', $colors['normal'] );
		}
		
		if ( ! empty( $colors['hover'] ) && $colors['hover'] !== $defaults['hover'] ) {
			$this->add_css_rule( 'a:hover,.digi-title-link:hover,.digi-comments-link:hover,.digi-author-name:hover', 'color', $colors['hover'] );
		}
	}

	/**
	 * Generate header colors CSS rules
	 */
	private function generate_header_colors_rules() {
		$header_colors = get_theme_mod( 'digifusion_header_colors' );
		$colors = $this->parse_color_group( $header_colors );
		$defaults = $this->default_colors['digifusion_header_colors'];
		
		if ( ! empty( $colors['background'] ) && $colors['background'] !== $defaults['background'] ) {
			$this->add_css_rule( '.site-header', 'background-color', $colors['background'] );
		}
	}

	/**
	 * Generate menu colors CSS rules
	 */
	private function generate_menu_colors_rules() {
		$menu_colors = get_theme_mod( 'digifusion_menu_colors' );
		$colors = $this->parse_color_group( $menu_colors );
		$defaults = $this->default_colors['digifusion_menu_colors'];
		
		if ( ! empty( $colors['normal'] ) && $colors['normal'] !== $defaults['normal'] ) {
			$this->add_css_rule( '.digi-header-nav a,.digi-nav-menu a,.digi-site-name', 'color', $colors['normal'] );
		}
		
		if ( ! empty( $colors['hover'] ) && $colors['hover'] !== $defaults['hover'] ) {
			$this->add_css_rule( '.digi-header-nav a:hover,.digi-nav-menu a:hover', 'color', $colors['hover'] );
		}
		
		if ( ! empty( $colors['current'] ) && $colors['current'] !== $defaults['current'] ) {
			$this->add_css_rule( '.digi-header-nav .current-menu-item>a,.digi-header-nav .current-menu-ancestor>a,.digi-nav-menu .current-menu-item>a,.digi-nav-menu .current-menu-ancestor>a', 'color', $colors['current'] );
		}
	}

	/**
	 * Generate mobile icon colors CSS rules
	 */
	private function generate_mobile_icon_colors_rules() {
		$mobile_icon_colors = get_theme_mod( 'digifusion_mobile_icon_colors' );
		$colors = $this->parse_color_group( $mobile_icon_colors );
		$defaults = $this->default_colors['digifusion_mobile_icon_colors'];
		
		if ( ! empty( $colors['normal'] ) && $colors['normal'] !== $defaults['normal'] ) {
			$this->add_css_rule( '.digi-menu-bars span', 'background-color', $colors['normal'] );
		}
		
		if ( ! empty( $colors['hover'] ) && $colors['hover'] !== $defaults['hover'] ) {
			$this->add_css_rule( '.digi-menu-toggle:hover .digi-menu-bars span', 'background-color', $colors['hover'] );
		}
		
		if ( ! empty( $colors['active'] ) && $colors['active'] !== $defaults['active'] ) {
			$this->add_css_rule( 'body.mopen .digi-menu-bars span', 'background-color', $colors['active'] );
		}
	}

	/**
	 * Generate mobile submenu colors CSS rules
	 */
	private function generate_mobile_submenu_colors_rules() {
		$mobile_submenu_colors = get_theme_mod( 'digifusion_mobile_submenu_colors' );
		$colors = $this->parse_color_group( $mobile_submenu_colors );
		$defaults = $this->default_colors['digifusion_mobile_submenu_colors'];
		
		// Use tablet device for mobile styles (max-width: 1024px)
		if ( ! empty( $colors['background'] ) && $colors['background'] !== $defaults['background'] ) {
			$this->add_css_rule( '.digi-header-nav,.digi-header-nav .sub-menu', 'background-color', $colors['background'], 'tablet' );
		}
		
		if ( ! empty( $colors['normal'] ) && $colors['normal'] !== $defaults['normal'] ) {
			$this->add_css_rule( '.digi-header-nav a,.digi-nav-menu a', 'color', $colors['normal'], 'tablet' );
		}
		
		if ( ! empty( $colors['hover'] ) && $colors['hover'] !== $defaults['hover'] ) {
			$this->add_css_rule( '.digi-header-nav a:hover,.digi-nav-menu a:hover', 'color', $colors['hover'], 'tablet' );
		}
		
		if ( ! empty( $colors['active'] ) && $colors['active'] !== $defaults['active'] ) {
			$this->add_css_rule( '.digi-header-nav .current-menu-item>a,.digi-header-nav .current-menu-ancestor>a,.digi-nav-menu .current-menu-item>a,.digi-nav-menu .current-menu-ancestor>a', 'color', $colors['active'], 'tablet' );
		}
	}

	/**
	 * Generate footer colors CSS rules
	 */
	private function generate_footer_colors_rules() {
		$footer_colors = get_theme_mod( 'digifusion_footer_colors' );
		$colors = $this->parse_color_group( $footer_colors );
		$defaults = $this->default_colors['digifusion_footer_colors'];
		
		if ( ! empty( $colors['background'] ) && $colors['background'] !== $defaults['background'] ) {
			$this->add_css_rule( '.site-footer', 'background-color', $colors['background'] );
		}
		
		if ( ! empty( $colors['heading'] ) && $colors['heading'] !== $defaults['heading'] ) {
			$this->add_css_rule( '.site-footer h1,.site-footer h2,.site-footer h3,.site-footer h4,.site-footer h5,.site-footer h6,.site-footer .widget-title', 'color', $colors['heading'] );
		}
		
		if ( ! empty( $colors['text'] ) && $colors['text'] !== $defaults['text'] ) {
			$this->add_css_rule( '.site-footer,.site-footer p,.site-footer .widget', 'color', $colors['text'] );
		}
		
		if ( ! empty( $colors['link'] ) && $colors['link'] !== $defaults['link'] ) {
			$this->add_css_rule( '.site-footer a,.site-footer-nav a', 'color', $colors['link'] );
		}
		
		if ( ! empty( $colors['link_hover'] ) && $colors['link_hover'] !== $defaults['link_hover'] ) {
			$this->add_css_rule( '.site-footer a:hover,.site-footer-nav a:hover', 'color', $colors['link_hover'] );
		}
	}

	/**
	 * Parse color group JSON string.
	 *
	 * @param string $color_group JSON string of colors.
	 * @return array
	 */
	private function parse_color_group( $color_group ) {
		if ( empty( $color_group ) ) {
			return array();
		}
		
		$colors = json_decode( $color_group, true );
		return is_array( $colors ) ? $colors : array();
	}

	/**
	 * Parse typography setting value.
	 *
	 * @param string $typography_value JSON string of typography settings.
	 * @return array
	 */
	private function parse_typography_setting( $typography_value ) {
		if ( empty( $typography_value ) ) {
			return array();
		}
		
		$typography = json_decode( $typography_value, true );
		return is_array( $typography ) ? $typography : array();
	}

	/**
	 * Minify CSS content.
	 *
	 * @param string $css CSS content to minify.
	 * @return string
	 */
	private function minify_css( $css ) {
		// Remove comments
		$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );
		
		// Remove whitespace
		$css = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $css );
		
		// Remove extra spaces
		$css = preg_replace( '/\s+/', ' ', $css );
		
		// Remove space around specific characters
		$css = str_replace( array( ' {', '{ ', ' }', '} ', '; ', ' ;', ', ', ' ,', ': ', ' :' ), array( '{', '{', '}', '}', ';', ';', ',', ',', ':', ':' ), $css );
		
		return trim( $css );
	}

	/**
	 * Enqueue dynamic CSS using wp_add_inline_style.
	 */
	public function enqueue_dynamic_css() {
		// Generate CSS content
		$css_content = $this->generate_css_content();
		
		// Only add inline style if we have CSS content
		if ( ! empty( $css_content ) ) {
			wp_add_inline_style( 'digifusion-main', $css_content );
		}
	}
}

// Initialize the class
DigiFusion_Dynamic_CSS::get_instance();