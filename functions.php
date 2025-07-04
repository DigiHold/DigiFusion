<?php
/**
 * Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package DigiFusion
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define theme version
if ( ! defined( 'DIGIFUSION_VERSION' ) ) {
	define( 'DIGIFUSION_VERSION', '1.0.2' );
}

// Define theme directory path
if ( ! defined( 'DIGIFUSION_DIR' ) ) {
	define( 'DIGIFUSION_DIR', trailingslashit( get_template_directory() ) );
}

// Define theme directory URI
if ( ! defined( 'DIGIFUSION_URI' ) ) {
	define( 'DIGIFUSION_URI', trailingslashit( get_template_directory_uri() ) );
}

/**
 * Theme setup.
 */
function digifusion_setup() {
	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails on posts and pages.
	add_theme_support( 'post-thumbnails' );

	// Enable support for HTML5 markup.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	// Add support for core custom logo.
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Add support for full and wide align images.
	add_theme_support( 'align-wide' );

	// Add support for responsive embeds.
	add_theme_support( 'responsive-embeds' );

	// Add support for custom line height controls.
	add_theme_support( 'custom-line-height' );

	// Add support for custom units.
	add_theme_support( 'custom-units' );

	// Add support for experimental link color control.
	add_theme_support( 'experimental-link-color' );

	// Add support for experimental cover block spacing.
	add_theme_support( 'custom-spacing' );

	// Add support for custom units.
	add_theme_support( 'custom-units' );

	// Add support for block styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// Add support for WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register navigation menus.
	register_nav_menus(
		array(
			'primary'   => esc_html__( 'Primary Menu', 'digifusion' ),
			'footer'    => esc_html__( 'Footer Menu', 'digifusion' ),
		)
	);

	// Set the content width
	if ( ! isset( $content_width ) ) {
		$content_width = 1200;
	}
}
add_action( 'after_setup_theme', 'digifusion_setup' );

/**
 * Register widget areas.
 */
function digifusion_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'digifusion' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'digifusion' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer 1', 'digifusion' ),
			'id'            => 'footer-1',
			'description'   => esc_html__( 'Add footer widgets here.', 'digifusion' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer 2', 'digifusion' ),
			'id'            => 'footer-2',
			'description'   => esc_html__( 'Add footer widgets here.', 'digifusion' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer 3', 'digifusion' ),
			'id'            => 'footer-3',
			'description'   => esc_html__( 'Add footer widgets here.', 'digifusion' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Footer 4', 'digifusion' ),
			'id'            => 'footer-4',
			'description'   => esc_html__( 'Add footer widgets here.', 'digifusion' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'digifusion_widgets_init' );

/**
 * Enqueue theme assets.
 */
function digifusion_enqueue_scripts() {
	// Enqueue main stylesheet
	wp_enqueue_style(
		'digifusion-style',
		get_stylesheet_uri(),
		array(),
		DIGIFUSION_VERSION
	);

	// Enqueue compiled CSS
	wp_enqueue_style(
		'digifusion-main',
		DIGIFUSION_URI . 'assets/css/app.css',
		array(),
		DIGIFUSION_VERSION
	);

	// Enqueue main JavaScript
	wp_enqueue_script(
		'digifusion-app',
		DIGIFUSION_URI . 'assets/js/app.js',
		array(),
		DIGIFUSION_VERSION,
		true
	);

	// If DigiCommerce is active, enqueue its styles
	if ( class_exists( 'DigiCommerce' ) ) {
		wp_enqueue_style(
			'digicommerce-style',
			DIGIFUSION_URI . 'assets/css/digicommerce.css',
			array(),
			DIGIFUSION_VERSION
		);
	}

	// Add comment-reply script if needed
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'digifusion_enqueue_scripts' );

/**
 * Enqueue editor assets.
 */
function enqueue_block_editor_styles() {
	wp_enqueue_style( 'legit-editor-styles', get_template_directory_uri() . '/assets/css/editor-style.css', false, '1.0', 'all' );
}
add_action( 'enqueue_block_editor_assets', 'enqueue_block_editor_styles' );

/**
 * Check if has pro access
 *
 * @return bool
 */
function digifusion_has_pro_access() {
	// Fast checks first
	if ( ! class_exists( 'DigiFusion_Pro' ) ) {
		return false;
	}
	
	if ( ! function_exists( 'digifusion_pro_has_access' ) ) {
		return false;
	}
	
	// Use pro plugin's cached function
	return digifusion_pro_has_access();
}

if ( ! function_exists( 'digifusion_excerpt_length' ) ) {
	/**
	 * Set the excerpt length.
	 *
	 * @param int $length The default excerpt length.
	 * @return int The new excerpt length.
	 */
	function digifusion_excerpt_length( $length ) {
		$length = apply_filters( 'digifusion_excerpt_length', 30 );
		return $length;
	}
	add_action( 'excerpt_length', 'digifusion_excerpt_length', 500 );
}

if ( ! function_exists( 'digifusion_excerpt_more' ) ) {
	/**
	 * Set the excerpt more string.
	 *
	 * @param string $more The default excerpt more string.
	 * @return string The new excerpt more string.
	 */
	function digifusion_excerpt_more( $more ) {
		$more = apply_filters( 'digifusion_excerpt_more', '...' );
		return $more;
	}
	add_filter( 'excerpt_more', 'digifusion_excerpt_more' );
}

// Include dashboard
require_once DIGIFUSION_DIR . 'includes/class-digifusion-dashboard.php';

// Include schema markup
require_once DIGIFUSION_DIR . 'includes/class-digifusion-schema-markup.php';

// Include breadcrumbs
require_once DIGIFUSION_DIR . 'includes/class-digifusion-breadcrumbs.php';

// Include custom navigation walker
require_once DIGIFUSION_DIR . 'includes/class-digifusion-nav-walker.php';

// Include page settings
require_once DIGIFUSION_DIR . 'includes/class-digifusion-page-settings.php';

// Include Customizer
require_once DIGIFUSION_DIR . 'includes/customizer/customizer-loader.php';

// Include dynamic CSS
require_once DIGIFUSION_DIR . 'includes/class-digifusion-dynamic-css.php';

// Include fonts
require_once DIGIFUSION_DIR . 'includes/class-digifusion-fonts.php';

// Initialize fonts handler
DigiFusion_Fonts::get_instance();

// If WooCommerce active
if ( class_exists( 'WooCommerce' ) ) {
	// Include WooCommerce compatibility
	require_once DIGIFUSION_DIR . 'includes/class-digifusion-woocommerce.php';
}

/**
 * Add custom body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function digifusion_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'digifusion_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function digifusion_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'digifusion_pingback_header' );

/**
 * Custom posts navigation function
 * 
 * Displays pagination for blog posts with more visual appeal
 */
function digifusion_the_posts_navigation() {
	$prev_text = '<svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9.66667 5H1M4.33333 1L0.804734 4.5286C0.544369 4.78894 0.544369 5.21106 0.804734 5.4714L4.33333 9" stroke="currentColor" stroke-width="1.06667" stroke-linecap="round"/></svg><span class="screen-reader-text">' . esc_html__( 'Previous', 'digifusion' ) . '</span>';
	$next_text = '<span class="screen-reader-text">' . esc_html__( 'Next', 'digifusion' ) . '</span><svg width="11" height="10" viewBox="0 0 11 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 5H9.66667M6.33333 1L9.86193 4.5286C10.1223 4.78894 10.1223 5.21106 9.86193 5.4714L6.33333 9" stroke="currentColor" stroke-width="1.06667" stroke-linecap="round"/></svg>';

	$total_pages = $GLOBALS['wp_query']->max_num_pages;

	if ( $total_pages > 1 ) {
		$current_page = max( 1, get_query_var( 'paged' ) );

		echo '<div class="digi-pagination">';

		echo paginate_links(
			array(
				'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
				'format'       => '?paged=%#%',
				'current'      => $current_page,
				'total'        => $total_pages,
				'mid_size'     => 1,
				'prev_text'    => $prev_text,
				'next_text'    => $next_text,
				'type'         => 'list',
				'add_args'     => false,
				'add_fragment' => '',
			)
		);

		echo '</div>';
	}
}