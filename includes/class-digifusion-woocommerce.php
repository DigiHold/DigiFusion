<?php
/**
 * DigiFusion WooCommerce Compatibility
 *
 * @package DigiFusion
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DigiFusion WooCommerce Class
 * 
 * Handles all WooCommerce compatibility and features for the DigiFusion theme.
 */
class DigiFusion_WooCommerce {

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
		// Remove/Add WooCommerce elements
		add_action( 'init', array( $this, 'init' ) );
		
		// Add WooCommerce customizer settings
		add_action( 'customize_register', array( $this, 'customizer_settings' ), 15 );
		
		// Enqueue WooCommerce assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
		// Add cart icon to header
		add_action( 'digifusion_after_header_menu_links', array( $this, 'add_cart_icon' ) );
		
		// AJAX handlers
		add_action( 'wp_ajax_digifusion_get_cart_data', array( $this, 'ajax_get_cart_data' ) );
		add_action( 'wp_ajax_nopriv_digifusion_get_cart_data', array( $this, 'ajax_get_cart_data' ) );
		add_action( 'wp_ajax_digifusion_remove_cart_item', array( $this, 'ajax_remove_cart_item' ) );
		add_action( 'wp_ajax_nopriv_digifusion_remove_cart_item', array( $this, 'ajax_remove_cart_item' ) );
		add_action( 'wp_ajax_digifusion_get_mini_cart', array( $this, 'ajax_get_mini_cart' ) );
		add_action( 'wp_ajax_nopriv_digifusion_get_mini_cart', array( $this, 'ajax_get_mini_cart' ) );

		// Update cart fragments for AJAX cart updates
		add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
	}

	/**
	 * Get instance of this class.
	 *
	 * @return DigiFusion_WooCommerce
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Remove/Add WooCommerce elements
	 */
	public function init() {
		// Disable sidebar
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		// header wrapper
		remove_action( 'woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header' );

		// Remove shop page title
		add_filter( 'woocommerce_show_page_title', '__return_false' );
		
		// Remove breadcrumbs from shop and single product pages
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		// Add our custom wrapper
		add_action( 'woocommerce_before_shop_loop', array( $this, 'shop_header_wrapper_start' ), 15 );
		add_action( 'woocommerce_before_shop_loop', array( $this, 'shop_header_wrapper_end' ), 35 );

		// Add our custom checkout wrapper
		add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'before_checkout' ) );
		add_action( 'woocommerce_checkout_after_order_review', array( $this, 'after_checkout' ) );

		// Add our custom order review wrapper
		add_action( 'woocommerce_checkout_before_order_review_heading', array( $this, 'before_order_review' ) );
		add_action( 'woocommerce_checkout_after_order_review', array( $this, 'after_order_review' ) );
	}

	/**
	 * Output the opening wrapper div
	 */
	public function shop_header_wrapper_start() {
		echo '<div class="digi-shop-header-wrapper">';
	}

	/**
	 * Output the closing wrapper div
	 */
	public function shop_header_wrapper_end() {
		echo '</div>';
	}

	/**
	 * Output the opening checkout wrapper div
	 */
	public function before_checkout() {
		echo '<div class="digi-checkout-wrapper">';
	}

	/**
	 * Output the closing checkout wrapper div
	 */
	public function after_checkout() {
		echo '</div>';
	}

	/**
	 * Output the opening order review wrapper div
	 */
	public function before_order_review() {
		echo '<div class="digi-order-review-wrapper">';
	}

	/**
	 * Output the closing order review wrapper div
	 */
	public function after_order_review() {
		echo '</div>';
	}

	/**
	 * Add WooCommerce customizer settings.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customizer_settings( $wp_customize ) {
		// Create WooCommerce Panel
		$wp_customize->add_panel(
			'woocommerce',
			array(
				'priority'       => 11,
				'capability'     => 'edit_theme_options',
				'title'          => __( 'WooCommerce', 'digifusion' ),
				'description'    => __( 'Customize your WooCommerce store settings', 'digifusion' ),
			)
		);

		// Add Cart Icon section
		$wp_customize->add_section(
			'digifusion_woocommerce_cart_icon',
			array(
				'priority'    => 10,
				'capability'  => 'edit_theme_options',
				'title'       => __( 'Cart Icon', 'digifusion' ),
				'description' => __( 'Customize the cart icon in your header', 'digifusion' ),
				'panel'       => 'woocommerce',
			)
		);

		// Cart Icon Toggle Setting
		$wp_customize->add_setting(
			'digifusion_woocommerce_cart_icon',
			array(
				'default'           => false,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'digifusion_woocommerce_cart_icon',
				array(
					'label'       => __( 'Enable Cart Icon', 'digifusion' ),
					'description' => __( 'Display cart icon in the header', 'digifusion' ),
					'section'     => 'digifusion_woocommerce_cart_icon',
				)
			)
		);

		// Cart Counter Toggle Setting
		$wp_customize->add_setting(
			'digifusion_woocommerce_cart_counter',
			array(
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'digifusion_woocommerce_cart_counter',
				array(
					'label'           => __( 'Enable Counter', 'digifusion' ),
					'description'     => __( 'Show item count on cart icon', 'digifusion' ),
					'section'         => 'digifusion_woocommerce_cart_icon',
					'active_callback' => array( $this, 'is_cart_icon_enabled' ),
				)
			)
		);

		// Cart Price Toggle Setting
		$wp_customize->add_setting(
			'digifusion_woocommerce_cart_price',
			array(
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'digifusion_woocommerce_cart_price',
				array(
					'label'           => __( 'Enable Price', 'digifusion' ),
					'description'     => __( 'Show total price on cart icon', 'digifusion' ),
					'section'         => 'digifusion_woocommerce_cart_icon',
					'active_callback' => array( $this, 'is_cart_icon_enabled' ),
				)
			)
		);

		// Mini Cart Toggle Setting
		$wp_customize->add_setting(
			'digifusion_woocommerce_mini_cart',
			array(
				'default'           => true,
				'sanitize_callback' => array( $this, 'sanitize_boolean' ),
				'transport'         => 'refresh',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Toggle_Control(
				$wp_customize,
				'digifusion_woocommerce_mini_cart',
				array(
					'label'           => __( 'Enable Mini Cart', 'digifusion' ),
					'description'     => __( 'Show mini cart dropdown on cart icon hover/click', 'digifusion' ),
					'section'         => 'digifusion_woocommerce_cart_icon',
					'active_callback' => array( $this, 'is_cart_icon_enabled' ),
				)
			)
		);

		// Cart Colors
		$wp_customize->add_setting(
			'digifusion_woocommerce_cart_colors',
			array(
				'default'           => json_encode(
					array(
						'icon'         => '#2c3e50',
						'counter'      => '#e74c3c',
						'counter_text' => '#ffffff',
						'price'        => '#716c80',
					)
				),
				'sanitize_callback' => array( $this, 'sanitize_color_group' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new DigiFusion_Color_Picker_Control(
				$wp_customize,
				'digifusion_woocommerce_cart_colors',
				array(
					'label'           => __( 'Cart Icon Colors', 'digifusion' ),
					'section'         => 'digifusion_woocommerce_cart_icon',
					'alpha'           => true,
					'active_callback' => array( $this, 'is_cart_icon_enabled' ),
					'colors'          => array(
						array(
							'key'     => 'icon',
							'label'   => __( 'Cart Icon', 'digifusion' ),
							'default' => '#2c3e50',
						),
						array(
							'key'     => 'counter',
							'label'   => __( 'Counter Background', 'digifusion' ),
							'default' => '#e74c3c',
						),
						array(
							'key'     => 'counter_text',
							'label'   => __( 'Counter Text', 'digifusion' ),
							'default' => '#ffffff',
						),
						array(
							'key'     => 'price',
							'label'   => __( 'Price Text', 'digifusion' ),
							'default' => '#716c80',
						),
					),
				)
			)
		);

		// Move existing WooCommerce sections to our panel
		$this->move_woocommerce_sections_to_panel( $wp_customize );
	}

	/**
	 * Move existing WooCommerce sections to our main WooCommerce panel.
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	private function move_woocommerce_sections_to_panel( $wp_customize ) {
		// List of default WooCommerce sections
		$woocommerce_sections = array(
			'woocommerce_store_notice',
			'woocommerce_product_catalog',
			'woocommerce_product_images',
			'woocommerce_checkout',
		);

		// Move each section to our panel with proper priority
		$priority = 20;
		foreach ( $woocommerce_sections as $section_id ) {
			$section = $wp_customize->get_section( $section_id );
			if ( $section ) {
				$section->panel = 'woocommerce';
				$section->priority = $priority;
				$priority += 10;
			}
		}

		// Remove the default WooCommerce panel if it exists and is empty
		$default_panel = $wp_customize->get_panel( 'woocommerce' );
		if ( $default_panel ) {
			// Get all sections in the panel
			$sections_in_panel = array();
			foreach ( $wp_customize->sections() as $section ) {
				if ( $section->panel === 'woocommerce' ) {
					$sections_in_panel[] = $section->id;
				}
			}
		}
	}

	/**
	 * Active callback for cart icon dependent settings.
	 *
	 * @return bool
	 */
	public function is_cart_icon_enabled() {
		return get_theme_mod( 'digifusion_woocommerce_cart_icon', false );
	}

	/**
	 * Enqueue WooCommerce assets.
	 */
	public function enqueue_assets() {
		// Only enqueue if cart icon is enabled
		if ( ! get_theme_mod( 'digifusion_woocommerce_cart_icon', false ) ) {
			return;
		}

		// Enqueue WooCommerce CSS
		wp_enqueue_style(
			'digifusion-woocommerce',
			DIGIFUSION_URI . 'assets/css/woocommerce.css',
			array(),
			DIGIFUSION_VERSION
		);

		// Enqueue WooCommerce JavaScript
		wp_enqueue_script(
			'digifusion-woocommerce',
			DIGIFUSION_URI . 'assets/js/woocommerce.js',
			array(),
			DIGIFUSION_VERSION,
			true
		);

		// Pass cart data to JavaScript
		wp_localize_script(
			'digifusion-woocommerce',
			'digifusionWoo',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'cartUrl'        => wc_get_cart_url(),
				'checkoutUrl'    => wc_get_checkout_url(),
				'shopUrl'        => wc_get_page_permalink( 'shop' ),
				'nonce'          => wp_create_nonce( 'digifusion_woo_nonce' ),
				'showMiniCart'   => ! ( is_cart() || is_checkout() ),
				'isCartPage'     => is_cart(),
				'isCheckoutPage' => is_checkout(),
				'strings'        => array(
					'added'              => __( 'Product added to cart!', 'digifusion' ),
					'removed'            => __( 'Item removed from cart', 'digifusion' ),
					'failed'             => __( 'Failed to remove item', 'digifusion' ),
					'failed_product'     => __( 'Failed to add product to cart', 'digifusion' ),
					'cart_updated'       => __( 'Cart updated', 'digifusion' ),
					'security_failed'    => __( 'Security check failed', 'digifusion' ),
					'invalid_cart_item'  => __( 'Invalid cart item', 'digifusion' ),
				),
			)
		);
	}

	/**
	 * Add cart icon to header.
	 */
	public function add_cart_icon() {
		// Check if cart icon is enabled
		if ( ! get_theme_mod( 'digifusion_woocommerce_cart_icon', false ) ) {
			return;
		}

		// Don't show mini cart on cart and checkout pages
		$show_mini_cart = ! ( is_cart() || is_checkout() );

		$cart_count = WC()->cart->get_cart_contents_count();
		$cart_total = WC()->cart->get_cart_total();
		$show_counter = get_theme_mod( 'digifusion_woocommerce_cart_counter', true );
		$show_price = get_theme_mod( 'digifusion_woocommerce_cart_price', true );
		?>
		<li class="digi-menu-item digi-cart-icon-wrapper" data-show-mini-cart="<?php echo esc_attr( $show_mini_cart ? 'true' : 'false' ); ?>">
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="digi-cart-icon-link" aria-label="<?php esc_attr_e( 'View your shopping cart', 'digifusion' ); ?>">
				<span class="digi-cart-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="27" height="24" fill="currentColor"><path d="M16 0C7.2 0 0 7.2 0 16s7.2 16 16 16l37.9 0c7.6 0 14.2 5.3 15.7 12.8l58.9 288c6.1 29.8 32.3 51.2 62.7 51.2L496 384c8.8 0 16-7.2 16-16s-7.2-16-16-16l-304.8 0c-15.2 0-28.3-10.7-31.4-25.6L152 288l314.6 0c29.4 0 55-20 62.1-48.5L570.6 71.8c5-20.2-10.2-39.8-31-39.8L99.1 32C92.5 13 74.4 0 53.9 0L16 0zm90.1 64l433.4 0L497.6 231.8C494 246 481.2 256 466.5 256l-321.1 0L106.1 64zM168 456a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm80 0a56 56 0 1 0 -112 0 56 56 0 1 0 112 0zm200-24a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm0 80a56 56 0 1 0 0-112 56 56 0 1 0 0 112z"/></svg>
					
					<?php if ( $show_counter && $cart_count > 0 ) : ?>
						<span class="digi-cart-count"><?php echo esc_html( $cart_count ); ?></span>
					<?php endif; ?>
				</span>
				
				<?php if ( $show_price && $cart_count > 0 ) : ?>
					<span class="digi-cart-total"><?php echo wp_kses_post( $cart_total ); ?></span>
				<?php endif; ?>
			</a>

			<?php if ( $show_mini_cart ) : ?>
				<div class="digi-mini-cart" aria-hidden="true">
					<div class="digi-mini-cart-content">
						<div class="digi-mini-cart-header">
							<h3><?php esc_html_e( 'Shopping Cart', 'digifusion' ); ?></h3>
							<button class="digi-mini-cart-close" aria-label="<?php esc_attr_e( 'Close cart', 'digifusion' ); ?>">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</div>
						<div class="digi-mini-cart-items">
							<?php $this->get_mini_cart_content(); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * Get mini cart content.
	 */
	public function get_mini_cart_content() {
		if ( WC()->cart->is_empty() ) {
			?>
			<div class="digi-mini-cart-empty">
				<p><?php esc_html_e( 'Your cart is currently empty.', 'digifusion' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="digi-mini-cart-shop-link">
					<?php esc_html_e( 'Continue Shopping', 'digifusion' ); ?>
				</a>
			</div>
			<?php
			return;
		}

		?>
		<div class="digi-mini-cart-products">
			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<div class="digi-mini-cart-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
						<div class="digi-mini-cart-item-image">
							<?php if ( empty( $product_permalink ) ) : ?>
								<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>
						</div>
						<div class="digi-mini-cart-item-details">
							<div class="digi-mini-cart-item-name">
								<?php if ( empty( $product_permalink ) ) : ?>
									<?php echo wp_kses_post( $product_name ); ?>
								<?php else : ?>
									<a href="<?php echo esc_url( $product_permalink ); ?>">
										<?php echo wp_kses_post( $product_name ); ?>
									</a>
								<?php endif; ?>
							</div>
							<div class="digi-mini-cart-item-quantity">
								<?php echo esc_html( $cart_item['quantity'] ); ?> × <?php echo wp_kses_post( $product_price ); ?>
							</div>
						</div>
						<button class="digi-mini-cart-item-remove" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Remove this item', 'digifusion' ); ?>">
							<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M10.5 3.5L3.5 10.5M3.5 3.5L10.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</button>
					</div>
					<?php
				}
			}
			?>
		</div>

		<div class="digi-mini-cart-footer">
			<div class="digi-mini-cart-total">
				<strong><?php esc_html_e( 'Subtotal:', 'digifusion' ); ?> <?php echo WC()->cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
			</div>
			<div class="digi-mini-cart-buttons">
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="digi-mini-cart-view-cart">
					<?php esc_html_e( 'View Cart', 'digifusion' ); ?>
				</a>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="digi-mini-cart-checkout">
					<?php esc_html_e( 'Checkout', 'digifusion' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler to get cart data.
	 */
	public function ajax_get_cart_data() {
		// Check if request is from valid AJAX call
		if ( ! wp_doing_ajax() ) {
			wp_die( __( 'Invalid request', 'digifusion' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'digifusion_woo_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'digifusion' ) );
		}

		// Ensure WooCommerce cart is loaded
		if ( ! WC()->cart ) {
			wp_send_json_error( __( 'Cart not available', 'digifusion' ) );
		}

		$cart_count = WC()->cart->get_cart_contents_count();
		$cart_total = WC()->cart->get_cart_total();

		wp_send_json_success(
			array(
				'count' => $cart_count,
				'total' => $cart_total,
			)
		);
	}

	/**
	 * AJAX handler to remove cart item.
	 */
	public function ajax_remove_cart_item() {
		// Check if request is from valid AJAX call
		if ( ! wp_doing_ajax() ) {
			wp_die( __( 'Invalid request', 'digifusion' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'digifusion_woo_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'digifusion' ) );
		}

		// Validate cart item key
		if ( ! isset( $_POST['cart_item_key'] ) ) {
			wp_send_json_error( __( 'Cart item key is required', 'digifusion' ) );
		}

		$cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );

		// Validate cart item key format
		if ( empty( $cart_item_key ) || ! is_string( $cart_item_key ) ) {
			wp_send_json_error( __( 'Invalid cart item key', 'digifusion' ) );
		}

		// Ensure WooCommerce cart is loaded
		if ( ! WC()->cart ) {
			wp_send_json_error( __( 'Cart not available', 'digifusion' ) );
		}

		// Check if cart item exists
		$cart_contents = WC()->cart->get_cart();
		if ( ! isset( $cart_contents[ $cart_item_key ] ) ) {
			wp_send_json_error( __( 'Cart item not found', 'digifusion' ) );
		}

		// Remove item from cart
		$removed = WC()->cart->remove_cart_item( $cart_item_key );

		if ( $removed ) {
			// Calculate totals after removal
			WC()->cart->calculate_totals();

			// Trigger WooCommerce actions
			do_action( 'woocommerce_cart_item_removed', $cart_item_key, WC()->cart );

			wp_send_json_success(
				array(
					'message'    => __( 'Item removed from cart', 'digifusion' ),
					'cart_count' => WC()->cart->get_cart_contents_count(),
					'cart_total' => WC()->cart->get_cart_total(),
					'removed'    => true,
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to remove item from cart', 'digifusion' ) );
		}
	}

	/**
	 * AJAX handler to get mini cart content.
	 */
	public function ajax_get_mini_cart() {
		// Check if request is from valid AJAX call
		if ( ! wp_doing_ajax() ) {
			wp_die( __( 'Invalid request', 'digifusion' ) );
		}

		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'digifusion_woo_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'digifusion' ) );
		}

		// Ensure WooCommerce cart is loaded
		if ( ! WC()->cart ) {
			wp_send_json_error( __( 'Cart not available', 'digifusion' ) );
		}

		ob_start();
		$this->get_mini_cart_content();
		$mini_cart_html = ob_get_clean();

		wp_send_json_success(
			array(
				'mini_cart_html' => $mini_cart_html,
				'cart_count'     => WC()->cart->get_cart_contents_count(),
				'cart_total'     => WC()->cart->get_cart_total(),
			)
		);
	}

	/**
	 * Add cart fragments for AJAX updates.
	 *
	 * @param array $fragments WooCommerce cart fragments.
	 * @return array
	 */
	public function cart_fragments( $fragments ) {
		// Only add fragments if cart icon is enabled
		if ( ! get_theme_mod( 'digifusion_woocommerce_cart_icon', false ) ) {
			return $fragments;
		}

		$cart_count = WC()->cart->get_cart_contents_count();
		$cart_total = WC()->cart->get_cart_total();
		$show_counter = get_theme_mod( 'digifusion_woocommerce_cart_counter', true );
		$show_price = get_theme_mod( 'digifusion_woocommerce_cart_price', true );

		// Cart count fragment
		if ( $show_counter ) {
			ob_start();
			if ( $cart_count > 0 ) {
				echo '<span class="digi-cart-count">' . esc_html( $cart_count ) . '</span>';
			}
			$fragments['.digi-cart-count'] = ob_get_clean();
		}

		// Cart total fragment
		if ( $show_price ) {
			ob_start();
			if ( $cart_count > 0 ) {
				echo '<span class="digi-cart-total">' . wp_kses_post( $cart_total ) . '</span>';
			}
			$fragments['.digi-cart-total'] = ob_get_clean();
		}

		// Mini cart content fragment
		ob_start();
		$this->get_mini_cart_content();
		$fragments['.digi-mini-cart-items'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Sanitize boolean value.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return bool
	 */
	public function sanitize_boolean( $value ) {
		if ( $value === '1' || $value === 1 || $value === 'true' || $value === true ) {
			return true;
		}
		
		if ( $value === '0' || $value === 0 || $value === 'false' || $value === false ) {
			return false;
		}
		
		return (bool) $value;
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
}

// Initialize WooCommerce
DigiFusion_WooCommerce::get_instance();