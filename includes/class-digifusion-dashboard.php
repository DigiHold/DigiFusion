<?php
/**
 * DigiFusion Dashboard Class Methods - Enhanced with Plugin Management
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion Dashboard Class
 */
class DigiFusion_Dashboard {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Plugin configurations
	 *
	 * @var array
	 */
	private $plugins = array(
		'digiblocks' => array(
			'name' => 'DigiBlocks',
			'slug' => 'digiblocks/digiblocks.php',
			'repo_slug' => 'digiblocks',
			'learn_more_url' => 'https://digihold.click/digiblocks-site',
		),
		'digicommerce' => array(
			'name' => 'DigiCommerce',
			'slug' => 'digicommerce/digicommerce.php',
			'repo_slug' => 'digicommerce',
			'learn_more_url' => 'https://digihold.click/store-digicommerce',
		),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer_text' ), 99 );
		add_filter( 'update_footer', array( $this, 'update_footer' ), 99 );

		// AJAX handlers
		add_action( 'wp_ajax_digifusion_install_plugin', array( $this, 'ajax_install_plugin' ) );
		add_action( 'wp_ajax_digifusion_activate_plugin', array( $this, 'ajax_activate_plugin' ) );
		add_action( 'wp_ajax_digifusion_get_plugin_status', array( $this, 'ajax_get_plugin_status' ) );
	}

	/**
	 * Get instance of this class.
	 *
	 * @return DigiFusion_Dashboard
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Add admin menu.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'themes.php',
			__( 'DigiFusion', 'digifusion' ),
			__( 'DigiFusion', 'digifusion' ),
			'manage_options',
			'digifusion',
			array( $this, 'render_dashboard_page' )
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Only enqueue on our dashboard pages
		if ( 'appearance_page_digifusion' !== $hook_suffix &&
			'admin_page_digifusion-updates' !== $hook_suffix &&
			'admin_page_digifusion-ai' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'digifusion-dashboard',
			DIGIFUSION_URI . 'assets/css/admin/admin.css',
			array(),
			DIGIFUSION_VERSION
		);

		wp_enqueue_script(
			'digifusion-dashboard',
			DIGIFUSION_URI . 'assets/js/admin/admin.js',
			array(),
			DIGIFUSION_VERSION,
			true
		);

		wp_localize_script(
			'digifusion-dashboard',
			'digifusionVars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'digifusion_plugin_action' ),
				'strings' => array(
					'installing' => __( 'Installing...', 'digifusion' ),
					'activating' => __( 'Activating...', 'digifusion' ),
					'install_plugin' => __( 'Install Plugin', 'digifusion' ),
					'activate_plugin' => __( 'Activate Plugin', 'digifusion' ),
					'learn_more' => __( 'Learn More', 'digifusion' ),
					'error' => __( 'An error occurred. Please try again.', 'digifusion' ),
				),
			)
		);
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return bool
	 */
	public function is_plugin_installed( $plugin_slug ) {
		$installed_plugins = get_plugins();
		return isset( $installed_plugins[ $plugin_slug ] );
	}

	/**
	 * Check if plugin is active.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return bool
	 */
	public function is_plugin_active( $plugin_slug ) {
		return is_plugin_active( $plugin_slug );
	}

	/**
	 * Get plugin status.
	 *
	 * @param string $plugin_key Plugin key.
	 * @return array
	 */
	public function get_plugin_status( $plugin_key ) {
		if ( ! isset( $this->plugins[ $plugin_key ] ) ) {
			return array(
				'status' => 'unknown',
				'button_text' => __( 'Unknown', 'digifusion' ),
				'button_class' => 'button-secondary',
			);
		}

		$plugin = $this->plugins[ $plugin_key ];
		$is_installed = $this->is_plugin_installed( $plugin['slug'] );
		$is_active = $is_installed && $this->is_plugin_active( $plugin['slug'] );

		if ( $is_active ) {
			return array(
				'status' => 'active',
				'button_text' => __( 'Learn More', 'digifusion' ),
				'button_class' => 'button-primary',
				'url' => $plugin['learn_more_url'],
			);
		} elseif ( $is_installed ) {
			return array(
				'status' => 'inactive',
				'button_text' => __( 'Activate Plugin', 'digifusion' ),
				'button_class' => 'button-secondary',
			);
		} else {
			return array(
				'status' => 'not_installed',
				'button_text' => __( 'Install Plugin', 'digifusion' ),
				'button_class' => 'button-primary',
			);
		}
	}

	/**
	 * AJAX handler for installing plugin.
	 */
	public function ajax_install_plugin() {
		// Check nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'digifusion_plugin_action' ) ) {
			wp_die( __( 'Security check failed.', 'digifusion' ) );
		}

		// Check capabilities
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( __( 'You do not have permission to install plugins.', 'digifusion' ) );
		}

		$plugin_key = sanitize_text_field( $_POST['plugin'] );

		if ( ! isset( $this->plugins[ $plugin_key ] ) ) {
			wp_send_json_error( __( 'Invalid plugin.', 'digifusion' ) );
		}

		$plugin = $this->plugins[ $plugin_key ];

		// Include necessary files
		if ( ! class_exists( 'Plugin_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		// Get plugin information
		$api = plugins_api( 'plugin_information', array( 'slug' => $plugin['repo_slug'] ) );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}

		// Install plugin
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install = $upgrader->install( $api->download_link );

		if ( is_wp_error( $install ) ) {
			wp_send_json_error( $install->get_error_message() );
		}

		// Activate plugin
		$activate = activate_plugin( $plugin['slug'] );

		if ( is_wp_error( $activate ) ) {
			wp_send_json_error( $activate->get_error_message() );
		}

		wp_send_json_success( array(
			'message' => sprintf( __( '%s has been installed and activated successfully.', 'digifusion' ), $plugin['name'] ),
			'status' => $this->get_plugin_status( $plugin_key ),
		) );
	}

	/**
	 * AJAX handler for activating plugin.
	 */
	public function ajax_activate_plugin() {
		// Check nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'digifusion_plugin_action' ) ) {
			wp_die( __( 'Security check failed.', 'digifusion' ) );
		}

		// Check capabilities
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( __( 'You do not have permission to activate plugins.', 'digifusion' ) );
		}

		$plugin_key = sanitize_text_field( $_POST['plugin'] );

		if ( ! isset( $this->plugins[ $plugin_key ] ) ) {
			wp_send_json_error( __( 'Invalid plugin.', 'digifusion' ) );
		}

		$plugin = $this->plugins[ $plugin_key ];

		// Activate plugin
		$activate = activate_plugin( $plugin['slug'] );

		if ( is_wp_error( $activate ) ) {
			wp_send_json_error( $activate->get_error_message() );
		}

		wp_send_json_success( array(
			'message' => sprintf( __( '%s has been activated successfully.', 'digifusion' ), $plugin['name'] ),
			'status' => $this->get_plugin_status( $plugin_key ),
		) );
	}

	/**
	 * AJAX handler for getting plugin status.
	 */
	public function ajax_get_plugin_status() {
		// Check nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'digifusion_plugin_action' ) ) {
			wp_die( __( 'Security check failed.', 'digifusion' ) );
		}

		$plugin_key = sanitize_text_field( $_POST['plugin'] );

		wp_send_json_success( array(
			'status' => $this->get_plugin_status( $plugin_key ),
		) );
	}

	/**
	 * Get plugins configuration.
	 *
	 * @return array
	 */
	public function get_plugins() {
		return $this->plugins;
	}

	/**
	 * Render dashboard page.
	 */
	public function render_dashboard_page() {
		include_once DIGIFUSION_DIR . 'includes/admin/dashboard.php';
	}

	/**
	 * Render dashboard page.
	 */
	public function render_get_pro_page() {
		include_once DIGIFUSION_DIR . 'includes/admin/get-pro.php';
	}

	/**
	 * Get menu icon for admin menu.
	 *
	 * @return string SVG icon.
	 */
	public function get_menu_icon() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 250 512" width="24" height="24"><polygon points="250 202.2535 126.5277 202.2535 188.0896 0 0 309.7465 123.4723 309.7465 61.9104 512 250 202.2535" fill="#ffd83b"/></svg>';
	}

	/**
	 * Customize admin footer text
	 *
	 * @param string $text Footer text.
	 * @return string
	 */
	public function footer_text( $text ) {
		$screen = get_current_screen();

		if ( 'appearance_page_digifusion' === $screen->id ) {
			$text = sprintf(
				/* translators: %1$s: Plugin review link */
				esc_html__( 'Please rate %2$sDigiFusion%3$s %4$s&#9733;&#9733;&#9733;&#9733;&#9733;%5$s on %6$sWordPress.org%7$s to help us spread the word.', 'digifusion' ),
				'https://wordpress.org/support/theme/digifusion/reviews/?filter=5#new-post',
				'<strong>',
				'</strong>',
				'<a href="https://wordpress.org/support/theme/digifusion/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">',
				'</a>',
				'<a href="https://wordpress.org/support/theme/digifusion/reviews/?filter=5#new-post" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
		}

		return $text;
	}

	/**
	 * Customize update footer version
	 *
	 * @param string $version Version text.
	 * @return string
	 */
	public function update_footer( $version ) {
		$screen = get_current_screen();

		if ( 'appearance_page_digifusion' === $screen->id ) {
			$version .= sprintf( ' | %1$s %2$s', 'DigiFusion', DIGIFUSION_VERSION );
		}

		return $version;
	}
}

// Initialize the dashboard
DigiFusion_Dashboard::get_instance();