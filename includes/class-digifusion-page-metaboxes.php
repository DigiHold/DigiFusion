<?php
/**
 * DigiFusion Page Metaboxes for Classic Editor
 *
 * @package DigiFusion
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * DigiFusion_Page_Metaboxes class
 *
 * Handles metaboxes for classic editor
 */
class DigiFusion_Page_Metaboxes {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Get instance of the class
	 *
	 * @return DigiFusion_Page_Metaboxes
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
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add meta boxes for page settings
	 *
	 * @param string $post_type Current post type.
	 */
	public function add_meta_boxes( $post_type ) {
		$supported_post_types = apply_filters( 'digifusion_page_settings_post_types', array( 'post', 'page' ) );

		if ( ! in_array( $post_type, $supported_post_types, true ) ) {
			return;
		}

		add_meta_box(
			'digifusion_page_settings',
			__( 'DigiFusion Settings', 'digifusion' ),
			array( $this, 'render_page_settings_metabox' ),
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Render page settings metabox
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_page_settings_metabox( $post ) {
		wp_nonce_field( 'digifusion_page_settings_metabox', 'digifusion_page_settings_nonce' );

		// Get current values
		$disable_header = get_post_meta( $post->ID, 'digifusion_disable_header', true );
		$disable_page_header = get_post_meta( $post->ID, 'digifusion_disable_page_header', true );
		$disable_footer = get_post_meta( $post->ID, 'digifusion_disable_footer', true );
		$header_type = get_post_meta( $post->ID, 'digifusion_header_type', true );
		$custom_logo = get_post_meta( $post->ID, 'digifusion_custom_logo', true );
		$menu_colors = get_post_meta( $post->ID, 'digifusion_menu_colors', true );
		$custom_page_title = get_post_meta( $post->ID, 'digifusion_custom_page_title', true );
		$page_description = get_post_meta( $post->ID, 'digifusion_page_description', true );

		// Ensure menu_colors is an array
		if ( ! is_array( $menu_colors ) ) {
			$menu_colors = array();
		}
		?>
		<div class="digifusion-page-settings-wrap">
			<!-- Disable Elements Panel -->
			<div class="digifusion-panel digifusion-disable-panel" data-panel="disable">
				<h3 class="digifusion-panel-title">
					<span class="dashicons dashicons-visibility"></span>
					<?php esc_html_e( 'Disable Elements', 'digifusion' ); ?>
					<span class="digifusion-panel-toggle dashicons dashicons-arrow-down"></span>
				</h3>
				<div class="digifusion-panel-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Disable Header', 'digifusion' ); ?></th>
							<td>
								<label class="digifusion-toggle-switch">
									<input type="checkbox" name="digifusion_disable_header" value="1" <?php checked( $disable_header, 1 ); ?> />
									<span class="digifusion-toggle-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Disable Page Header', 'digifusion' ); ?></th>
							<td>
								<label class="digifusion-toggle-switch">
									<input type="checkbox" name="digifusion_disable_page_header" value="1" <?php checked( $disable_page_header, 1 ); ?> />
									<span class="digifusion-toggle-slider"></span>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Disable Footer', 'digifusion' ); ?></th>
							<td>
								<label class="digifusion-toggle-switch">
									<input type="checkbox" name="digifusion_disable_footer" value="1" <?php checked( $disable_footer, 1 ); ?> />
									<span class="digifusion-toggle-slider"></span>
								</label>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- Header Panel -->
			<div class="digifusion-panel digifusion-header-panel" data-panel="header" style="<?php echo $disable_header ? 'display: none;' : ''; ?>">
				<h3 class="digifusion-panel-title">
					<span class="dashicons dashicons-admin-appearance"></span>
					<?php esc_html_e( 'Header Settings', 'digifusion' ); ?>
					<span class="digifusion-panel-toggle dashicons dashicons-arrow-down"></span>
				</h3>
				<div class="digifusion-panel-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Header Type', 'digifusion' ); ?></th>
							<td>
								<select name="digifusion_header_type">
									<option value=""><?php esc_html_e( 'Default', 'digifusion' ); ?></option>
									<option value="minimal" <?php selected( $header_type, 'minimal' ); ?>><?php esc_html_e( 'Minimal', 'digifusion' ); ?></option>
									<option value="transparent" <?php selected( $header_type, 'transparent' ); ?>><?php esc_html_e( 'Transparent', 'digifusion' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Custom Logo', 'digifusion' ); ?></th>
							<td>
								<div class="digifusion-logo-upload">
									<input type="hidden" name="digifusion_custom_logo" id="digifusion_custom_logo" value="<?php echo esc_attr( $custom_logo ); ?>" />
									<div class="digifusion-logo-preview">
										<?php if ( $custom_logo ) : ?>
											<?php echo wp_get_attachment_image( $custom_logo, 'thumbnail' ); ?>
										<?php endif; ?>
									</div>
									<div class="digifusion-logo-actions">
										<button type="button" class="button digifusion-upload-logo">
											<?php echo $custom_logo ? esc_html__( 'Change Logo', 'digifusion' ) : esc_html__( 'Upload Logo', 'digifusion' ); ?>
										</button>
										<?php if ( $custom_logo ) : ?>
											<button type="button" class="button digifusion-remove-logo"><?php esc_html_e( 'Remove', 'digifusion' ); ?></button>
										<?php endif; ?>
									</div>
								</div>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Menu Colors', 'digifusion' ); ?></th>
							<td>
							<div class="digifusion-color-controls">
								<div class="digifusion-color-field">
									<label><?php esc_html_e( 'Normal', 'digifusion' ); ?></label>
									<input type="text" name="digifusion_menu_colors[normal]" value="<?php echo esc_attr( $menu_colors['normal'] ?? '' ); ?>" class="digifusion-color-picker" data-default-color="" />
								</div>
								<div class="digifusion-color-field">
									<label><?php esc_html_e( 'Hover', 'digifusion' ); ?></label>
									<input type="text" name="digifusion_menu_colors[hover]" value="<?php echo esc_attr( $menu_colors['hover'] ?? '' ); ?>" class="digifusion-color-picker" data-default-color="" />
								</div>
								<div class="digifusion-color-field">
									<label><?php esc_html_e( 'Current', 'digifusion' ); ?></label>
									<input type="text" name="digifusion_menu_colors[current]" value="<?php echo esc_attr( $menu_colors['current'] ?? '' ); ?>" class="digifusion-color-picker" data-default-color="" />
								</div>
							</div>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<!-- Page Header Panel -->
			<div class="digifusion-panel digifusion-page-header-panel" data-panel="page-header" style="<?php echo $disable_page_header ? 'display: none;' : ''; ?>">
				<h3 class="digifusion-panel-title">
					<span class="dashicons dashicons-admin-page"></span>
					<?php esc_html_e( 'Page Header Settings', 'digifusion' ); ?>
					<span class="digifusion-panel-toggle dashicons dashicons-arrow-down"></span>
				</h3>
				<div class="digifusion-panel-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Custom Title', 'digifusion' ); ?></th>
							<td>
								<input type="text" name="digifusion_custom_page_title" value="<?php echo esc_attr( $custom_page_title ); ?>" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Leave empty to use default page title.', 'digifusion' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Description', 'digifusion' ); ?></th>
							<td>
								<textarea name="digifusion_page_description" class="large-text" rows="3"><?php echo esc_textarea( $page_description ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Optional description to display below the title.', 'digifusion' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta boxes
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		// Verify nonce
		if ( ! isset( $_POST['digifusion_page_settings_nonce'] ) || ! wp_verify_nonce( $_POST['digifusion_page_settings_nonce'], 'digifusion_page_settings_metabox' ) ) {
			return;
		}

		// Check if user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Skip autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Only save for supported post types
		$supported_post_types = apply_filters( 'digifusion_page_settings_post_types', array( 'post', 'page' ) );
		if ( ! in_array( $post->post_type, $supported_post_types, true ) ) {
			return;
		}

		// Save disable settings
		update_post_meta( $post_id, 'digifusion_disable_header', isset( $_POST['digifusion_disable_header'] ) ? 1 : 0 );
		update_post_meta( $post_id, 'digifusion_disable_page_header', isset( $_POST['digifusion_disable_page_header'] ) ? 1 : 0 );
		update_post_meta( $post_id, 'digifusion_disable_footer', isset( $_POST['digifusion_disable_footer'] ) ? 1 : 0 );

		// Save header settings
		if ( isset( $_POST['digifusion_header_type'] ) ) {
			update_post_meta( $post_id, 'digifusion_header_type', sanitize_text_field( $_POST['digifusion_header_type'] ) );
		}

		if ( isset( $_POST['digifusion_custom_logo'] ) ) {
			update_post_meta( $post_id, 'digifusion_custom_logo', intval( $_POST['digifusion_custom_logo'] ) );
		}

		if ( isset( $_POST['digifusion_menu_colors'] ) && is_array( $_POST['digifusion_menu_colors'] ) ) {
			$menu_colors = array();
			foreach ( $_POST['digifusion_menu_colors'] as $key => $color ) {
				if ( in_array( $key, array( 'normal', 'hover', 'current' ), true ) ) {
					$menu_colors[ $key ] = sanitize_hex_color( $color );
				}
			}
			update_post_meta( $post_id, 'digifusion_menu_colors', $menu_colors );
		}

		// Save page header settings
		if ( isset( $_POST['digifusion_custom_page_title'] ) ) {
			update_post_meta( $post_id, 'digifusion_custom_page_title', sanitize_text_field( $_POST['digifusion_custom_page_title'] ) );
		}

		if ( isset( $_POST['digifusion_page_description'] ) ) {
			update_post_meta( $post_id, 'digifusion_page_description', sanitize_textarea_field( $_POST['digifusion_page_description'] ) );
		}
	}

	/**
	 * Enqueue scripts for metaboxes
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		$screen = get_current_screen();
		$supported_post_types = apply_filters( 'digifusion_page_settings_post_types', array( 'post', 'page' ) );

		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || ! in_array( $screen->post_type, $supported_post_types, true ) ) {
			return;
		}

		// Enqueue media scripts
		wp_enqueue_media();

		// Enqueue color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style(
			'digifusion-page-metaboxes',
			get_template_directory_uri() . '/assets/css/admin/page-metaboxes.css',
			array(),
			wp_get_theme()->get( 'Version' )
		);

		wp_enqueue_script(
			'digifusion-page-metaboxes',
			get_template_directory_uri() . '/assets/js/admin/page-metaboxes.js',
			array( 'jquery', 'wp-color-picker' ),
			wp_get_theme()->get( 'Version' ),
			true
		);

		wp_localize_script(
			'digifusion-page-metaboxes',
			'digifusionPageMetaboxes',
			array(
				'nonce'           => wp_create_nonce( 'digifusion_page_settings' ),
				'selectImage'     => __( 'Select Logo', 'digifusion' ),
				'useImage'        => __( 'Use This Logo', 'digifusion' ),
				'uploadLogo'      => __( 'Upload Logo', 'digifusion' ),
				'changeLogo'      => __( 'Change Logo', 'digifusion' ),
				'remove'          => __( 'Remove', 'digifusion' ),
				'removeConfirm'   => __( 'Are you sure you want to remove this logo?', 'digifusion' ),
			)
		);
	}
}

DigiFusion_Page_Metaboxes::get_instance();