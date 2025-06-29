<?php
/**
 * DigiFusion Page Settings
 *
 * Handles page-specific settings for posts and pages
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * DigiFusion_Page_Settings class
 */
class DigiFusion_Page_Settings {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Get instance of the class
	 *
	 * @return DigiFusion_Page_Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'admin_init', array( $this, 'load_editor_interface' ), 20 );
		add_action( 'digifusion_dynamic_css_generate', array( $this, 'add_page_colors_to_dynamic_css' ) );
	}

	/**
	 * Register meta fields for page settings
	 */
	public function register_meta() {
		// Get supported post types
		$post_types = $this->get_supported_post_types();

		foreach ( $post_types as $post_type ) {
			// Disable elements
			register_post_meta(
				$post_type,
				'digifusion_disable_header',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'boolean',
					'default'       => false,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			register_post_meta(
				$post_type,
				'digifusion_disable_page_header',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'boolean',
					'default'       => false,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			register_post_meta(
				$post_type,
				'digifusion_disable_footer',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'boolean',
					'default'       => false,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			// Header settings
			register_post_meta(
				$post_type,
				'digifusion_header_type',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'string',
					'default'       => '',
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			register_post_meta(
				$post_type,
				'digifusion_custom_logo',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'number',
					'default'       => 0,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			register_post_meta(
				$post_type,
				'digifusion_menu_colors',
				array(
					'show_in_rest'  => array(
						'schema' => array(
							'type'       => 'object',
							'properties' => array(
								'normal'  => array( 'type' => 'string' ),
								'hover'   => array( 'type' => 'string' ),
								'current' => array( 'type' => 'string' ),
							),
						),
					),
					'single'        => true,
					'type'          => 'object',
					'default'       => array(),
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			// Page Header settings
			register_post_meta(
				$post_type,
				'digifusion_custom_page_title',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'string',
					'default'       => '',
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			register_post_meta(
				$post_type,
				'digifusion_page_description',
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'string',
					'default'       => '',
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Load appropriate editor interface
	 */
	public function load_editor_interface() {
		if ( $this->is_using_gutenberg() ) {
			// Load Gutenberg sidebar
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		} else {
			// Load metaboxes for classic editor
			require_once get_template_directory() . '/includes/class-digifusion-page-metaboxes.php';
		}
	}

	/**
	 * Check if Gutenberg editor is being used
	 * 
	 * @return bool True if using Gutenberg, false if using Classic Editor
	 */
	public function is_using_gutenberg() {
		// Only check in admin
		if ( ! is_admin() ) {
			return false;
		}

		// Check if block editor functions exist
		if ( ! function_exists( 'use_block_editor_for_post_type' ) ) {
			return false;
		}
	
		// Check if Classic Editor plugin is installed
		if ( class_exists( 'Classic_Editor' ) ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Get supported post types
	 *
	 * @return array
	 */
	public function get_supported_post_types() {
		return apply_filters( 'digifusion_page_settings_post_types', array( 'post', 'page' ) );
	}

	/**
	 * Enqueue block editor assets
	 */
	public function enqueue_block_editor_assets() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, $this->get_supported_post_types(), true ) ) {
			return;
		}

		wp_enqueue_script(
			'digifusion-page-settings',
			get_template_directory_uri() . '/assets/js/admin/page-settings.js',
			array(
				'wp-i18n',
				'wp-plugins',
				'wp-element',
				'wp-components',
				'wp-data',
				'wp-editor',
				'wp-block-editor',
			),
			wp_get_theme()->get( 'Version' ),
			true
		);

		wp_localize_script(
			'digifusion-page-settings',
			'digifusionPageSettings',
			array(
				'nonce' => wp_create_nonce( 'digifusion_page_settings' ),
			)
		);
	}

	/**
	 * Enqueue admin styles
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_admin_styles( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->post_type, $this->get_supported_post_types(), true ) ) {
			return;
		}

		wp_enqueue_style(
			'digifusion-page-settings',
			get_template_directory_uri() . '/assets/css/admin/page-settings.css',
			array(),
			wp_get_theme()->get( 'Version' )
		);
	}

	/**
	 * Check if header is disabled for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return bool
	 */
	public static function is_header_disabled( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return false;
		}

		return (bool) get_post_meta( $post_id, 'digifusion_disable_header', true );
	}

	/**
	 * Check if page header is disabled for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return bool
	 */
	public static function is_page_header_disabled( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return false;
		}

		return (bool) get_post_meta( $post_id, 'digifusion_disable_page_header', true );
	}

	/**
	 * Check if footer is disabled for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return bool
	 */
	public static function is_footer_disabled( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return false;
		}

		return (bool) get_post_meta( $post_id, 'digifusion_disable_footer', true );
	}

	/**
	 * Get header type for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return string
	 */
	public static function get_header_type( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return '';
		}

		$header_type = get_post_meta( $post_id, 'digifusion_header_type', true );
		return $header_type ? $header_type : '';
	}

	/**
	 * Get custom logo for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return int Logo attachment ID
	 */
	public static function get_custom_logo( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return 0;
		}

		return (int) get_post_meta( $post_id, 'digifusion_custom_logo', true );
	}

	/**
	 * Get menu colors for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return array
	 */
	public static function get_menu_colors( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return array();
		}

		$colors = get_post_meta( $post_id, 'digifusion_menu_colors', true );
		return is_array( $colors ) ? $colors : array();
	}

	/**
	 * Get custom page title for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return string
	 */
	public static function get_custom_page_title( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return '';
		}

		return get_post_meta( $post_id, 'digifusion_custom_page_title', true );
	}

	/**
	 * Get page description for current post
	 *
	 * @param int $post_id Post ID. If not provided, uses current post.
	 * @return string
	 */
	public static function get_page_description( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		if ( ! $post_id ) {
			return '';
		}

		return get_post_meta( $post_id, 'digifusion_page_description', true );
	}

	/**
	 * Add page-specific colors to dynamic CSS
	 *
	 * @param DigiFusion_Dynamic_CSS $dynamic_css Dynamic CSS instance
	 */
	public function add_page_colors_to_dynamic_css( $dynamic_css ) {
		// Get all posts with custom menu colors
		$posts_with_colors = get_posts(
			array(
				'post_type'      => $this->get_supported_post_types(),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => 'digifusion_menu_colors',
						'value'   => '',
						'compare' => '!=',
					),
				),
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts_with_colors ) ) {
			return;
		}

		foreach ( $posts_with_colors as $post_id ) {
			$menu_colors = get_post_meta( $post_id, 'digifusion_menu_colors', true );
			
			if ( empty( $menu_colors ) ) {
				continue;
			}

			// Parse colors
			if ( is_string( $menu_colors ) ) {
				$menu_colors = json_decode( $menu_colors, true );
			}

			if ( ! is_array( $menu_colors ) ) {
				continue;
			}

			// Get post type to determine correct body class
			$post_type = get_post_type( $post_id );
			$body_class = ( 'page' === $post_type ) ? "page-id-{$post_id}" : "postid-{$post_id}";

			// Add normal color
			if ( ! empty( $menu_colors['normal'] ) ) {
				$dynamic_css->add_css_rule(
					".{$body_class} .digi-header-nav > ul > li > a",
					'color',
					$menu_colors['normal']
				);
				$dynamic_css->add_css_rule(
					".{$body_class} .digi-menu-bars span",
					'background-color',
					$menu_colors['normal']
				);
			}

			// Add hover color
			if ( ! empty( $menu_colors['hover'] ) ) {
				$dynamic_css->add_css_rule(
					".{$body_class} .digi-header-nav > ul > li > a:hover, .{$body_class} .digi-header-nav > ul > li:hover > a",
					'color',
					$menu_colors['hover']
				);
				$dynamic_css->add_css_rule(
					".{$body_class} .digi-menu-bars:hover span",
					'background-color',
					$menu_colors['hover']
				);
			}

			// Add current color
			if ( ! empty( $menu_colors['current'] ) ) {
				$dynamic_css->add_css_rule(
					".{$body_class} .digi-header-nav > ul > li.current-menu-item > a, .{$body_class} .digi-header-nav > ul > li.current-menu-ancestor > a",
					'color',
					$menu_colors['current']
				);
				$dynamic_css->add_css_rule(
					".{$body_class}.mopen .digi-menu-bars span",
					'background-color',
					$menu_colors['current']
				);
			}
		}
	}
}

DigiFusion_Page_Settings::get_instance();