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

		// Add dynamic CSS generation hook
		add_action( 'digifusion_dynamic_css_generate', array( $this, 'generate_cart_colors_css' ) );

		// Add css to dynamic style
		add_action( 'customize_save_after', array( $this, 'maybe_regenerate_css_on_cart_colors' ) );
		
		// Enqueue WooCommerce assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
		// Add cart icon to header
		add_action( 'digifusion_after_header_menu_links', array( $this, 'render_cart_icon' ) );
		
		// AJAX handlers for cart functionality
		add_action( 'wp_ajax_digifusion_get_cart_data', array( $this, 'ajax_get_cart_data' ) );
		add_action( 'wp_ajax_nopriv_digifusion_get_cart_data', array( $this, 'ajax_get_cart_data' ) );
		
		add_action( 'wp_ajax_digifusion_get_cart_items', array( $this, 'ajax_get_cart_items' ) );
		add_action( 'wp_ajax_nopriv_digifusion_get_cart_items', array( $this, 'ajax_get_cart_items' ) );
		
		add_action( 'wp_ajax_digifusion_update_cart_item', array( $this, 'ajax_update_cart_item' ) );
		add_action( 'wp_ajax_nopriv_digifusion_update_cart_item', array( $this, 'ajax_update_cart_item' ) );
		
		add_action( 'wp_ajax_digifusion_remove_cart_item', array( $this, 'ajax_remove_cart_item' ) );
		add_action( 'wp_ajax_nopriv_digifusion_remove_cart_item', array( $this, 'ajax_remove_cart_item' ) );
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
						'icon'         => '#27293b',
						'counter'      => '#7091e6',
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
							'default' => '#27293b',
						),
						array(
							'key'     => 'counter',
							'label'   => __( 'Counter Background', 'digifusion' ),
							'default' => '#7091e6',
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
	 * Generate cart icon colors CSS for dynamic CSS system
	 *
	 * @param DigiFusion_Dynamic_CSS $css_generator Dynamic CSS generator instance
	 */
	public function generate_cart_colors_css( $css_generator ) {
		// Only generate if cart icon is enabled
		if ( ! get_theme_mod( 'digifusion_woocommerce_cart_icon', false ) ) {
			return;
		}

		// Get cart colors setting
		$cart_colors = get_theme_mod( 'digifusion_woocommerce_cart_colors' );
		$colors = json_decode( $cart_colors, true );
		
		// Default colors
		$defaults = array(
			'icon'         => '#27293b',
			'counter'      => '#7091e6',
			'counter_text' => '#ffffff',
			'price'        => '#716c80',
		);
		
		if ( ! is_array( $colors ) ) {
			return;
		}
		
		// Generate CSS rules only if colors differ from defaults
		if ( ! empty( $colors['icon'] ) && $colors['icon'] !== $defaults['icon'] ) {
			$css_generator->add_css_rule( 
				'.digifusion-cart-icon-link .digifusion-cart-icon-icon svg', 
				'fill', 
				$colors['icon'] 
			);
		}
		
		if ( ! empty( $colors['counter'] ) && $colors['counter'] !== $defaults['counter'] ) {
			$css_generator->add_css_rule( 
				'.digifusion-cart-count', 
				'background-color', 
				$colors['counter'] 
			);
		}
		
		if ( ! empty( $colors['counter_text'] ) && $colors['counter_text'] !== $defaults['counter_text'] ) {
			$css_generator->add_css_rule( 
				'.digifusion-cart-count', 
				'color', 
				$colors['counter_text'] 
			);
		}
		
		if ( ! empty( $colors['price'] ) && $colors['price'] !== $defaults['price'] ) {
			$css_generator->add_css_rule( 
				'.digifusion-cart-total', 
				'color', 
				$colors['price'] 
			);
		}
	}

	/**
	 * Regenerate CSS when cart colors are saved
	 */
	public function maybe_regenerate_css_on_cart_colors() {
		// Check if cart colors were modified
		$cart_colors_modified = isset( $_POST['customized'] ) && 
							strpos( $_POST['customized'], 'digifusion_woocommerce_cart_colors' ) !== false;
		
		if ( $cart_colors_modified ) {
			// Get dynamic CSS instance and regenerate
			if ( class_exists( 'DigiFusion_Dynamic_CSS' ) ) {
				$dynamic_css = DigiFusion_Dynamic_CSS::get_instance();
				$dynamic_css->generate_css_file();
			}
		}
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
			'digifusionCartData',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'cart_nonce' => wp_create_nonce( 'digifusion_cart_nonce' ),
				'cart_url' => wc_get_cart_url(),
				'checkout_url' => wc_get_checkout_url(),
				'strings' => array(
					'total' => __( 'Total:', 'digifusion' ),
					'view_cart' => __( 'View Cart', 'digifusion' ),
					'checkout' => __( 'Checkout', 'digifusion' ),
					'empty_cart' => __( 'Your cart is currently empty.', 'digifusion' ),
					'quantity' => __( 'Quantity', 'digifusion' ),
					'remove_item' => __( 'Remove this item', 'digifusion' ),
				),
			)
		);
	}

	/**
	 * Render cart icon in header
	 */
	public function render_cart_icon() {
		// Only show if enabled in customizer
		if ( ! get_theme_mod( 'digifusion_woocommerce_cart_icon', false ) ) {
			return;
		}

		// Get customizer settings
		$show_counter = get_theme_mod( 'digifusion_woocommerce_cart_counter', true );
		$show_price = get_theme_mod( 'digifusion_woocommerce_cart_price', true );
		$show_mini_cart = get_theme_mod( 'digifusion_woocommerce_mini_cart', true );
		$colors = json_decode( get_theme_mod( 'digifusion_woocommerce_cart_colors', json_encode( array(
			'icon'         => '#27293b',
			'counter'      => '#7091e6',
			'counter_text' => '#ffffff',
			'price'        => '#716c80',
		) ) ), true );

		// Get cart data
		$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		$cart_total = WC()->cart ? WC()->cart->get_cart_total() : wc_price( 0 );
		$cart_url = wc_get_cart_url();

		// Generate unique ID for this cart icon
		$cart_id = 'digifusion-cart-icon-' . uniqid();
		?>
		<li class="digifusion-cart-icon-wrapper" id="<?php echo esc_attr( $cart_id ); ?>">
			<a href="<?php echo esc_url( $cart_url ); ?>" 
				class="digifusion-cart-icon-link"
				data-show-mini-cart="<?php echo esc_attr( $show_mini_cart ? 'true' : 'false' ); ?>"
				aria-label="<?php echo esc_attr( sprintf( __( 'View cart (%d items)', 'digifusion' ), $cart_count ) ); ?>">
				
				<div class="digifusion-cart-icon-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="27" height="24"><path d="M16 0C7.2 0 0 7.2 0 16s7.2 16 16 16l37.9 0c7.6 0 14.2 5.3 15.7 12.8l58.9 288c6.1 29.8 32.3 51.2 62.7 51.2L496 384c8.8 0 16-7.2 16-16s-7.2-16-16-16l-304.8 0c-15.2 0-28.3-10.7-31.4-25.6L152 288l314.6 0c29.4 0 55-20 62.1-48.5L570.6 71.8c5-20.2-10.2-39.8-31-39.8L99.1 32C92.5 13 74.4 0 53.9 0L16 0zm90.1 64l433.4 0L497.6 231.8C494 246 481.2 256 466.5 256l-321.1 0L106.1 64zM168 456a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zm80 0a56 56 0 1 0 -112 0 56 56 0 1 0 112 0zm200-24a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm0 80a56 56 0 1 0 0-112 56 56 0 1 0 0 112z"/></svg>
					
					<?php if ( $show_counter ) : ?>
						<span class="digifusion-cart-count"><?php echo esc_html( $cart_count ); ?></span>
					<?php endif; ?>
				</div>
				
				<?php if ( $show_price ) : ?>
					<span class="digifusion-cart-total"><?php echo wp_kses_post( $cart_total ); ?></span>
				<?php endif; ?>
			</a>

			<?php if ( $show_mini_cart ) : ?>
				<div class="digifusion-mini-cart">
					<div class="digifusion-mini-cart-wrapper">
						<div class="digifusion-mini-cart-header">
							<h3 class="digifusion-mini-cart-title">
								<?php esc_html_e( 'Shopping Cart', 'digifusion' ); ?>
							</h3>
						</div>
						
						<div class="digifusion-mini-cart-content">
							<?php if ( $cart_count > 0 ) : ?>
								<div class="digifusion-mini-cart-items">
									<?php
									foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
										$product = $cart_item['data'];
										$product_id = $cart_item['product_id'];
										$quantity = $cart_item['quantity'];
										
										if ( ! $product || ! $product->exists() ) {
											continue;
										}
										
										$product_name = $product->get_name();
										$product_price = WC()->cart->get_product_price( $product );
										$product_image = $product->get_image( array( 50, 50 ) );
										$product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
										?>
										<div class="digifusion-mini-cart-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
											<?php if ( $product_image ) : ?>
												<div class="digifusion-mini-cart-item-image">
													<?php if ( $product_permalink ) : ?>
														<a href="<?php echo esc_url( $product_permalink ); ?>">
															<?php echo wp_kses_post( $product_image ); ?>
														</a>
													<?php else : ?>
														<?php echo wp_kses_post( $product_image ); ?>
													<?php endif; ?>
												</div>
											<?php endif; ?>
											
											<div class="digifusion-mini-cart-item-details">
												<h4 class="digifusion-mini-cart-item-name">
													<?php if ( $product_permalink ) : ?>
														<a href="<?php echo esc_url( $product_permalink ); ?>">
															<?php echo esc_html( $product_name ); ?>
														</a>
													<?php else : ?>
														<?php echo esc_html( $product_name ); ?>
													<?php endif; ?>
												</h4>
												<p class="digifusion-mini-cart-item-price">
													<?php echo wp_kses_post( $product_price ); ?>
												</p>
											</div>
											
											<input 
												type="number" 
												class="digifusion-mini-cart-item-quantity" 
												value="<?php echo esc_attr( $quantity ); ?>" 
												min="0"
												data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
												aria-label="<?php esc_attr_e( 'Quantity', 'digifusion' ); ?>"
											/>
											
											<button 
												class="digifusion-mini-cart-item-remove" 
												data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
												aria-label="<?php esc_attr_e( 'Remove this item', 'digifusion' ); ?>"
											>
												×
											</button>
										</div>
										<?php
									}
									?>
								</div>
								
								<div class="digifusion-mini-cart-total">
									<span><?php esc_html_e( 'Total:', 'digifusion' ); ?></span>
									<span class="total-amount"><?php echo wp_kses_post( WC()->cart->get_cart_total() ); ?></span>
								</div>
								
								<div class="digifusion-mini-cart-buttons">
									<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="digifusion-mini-cart-button secondary">
										<?php esc_html_e( 'View Cart', 'digifusion' ); ?>
									</a>
									<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="digifusion-mini-cart-button primary">
										<?php esc_html_e( 'Checkout', 'digifusion' ); ?>
									</a>
								</div>
							<?php else : ?>
								<div class="digifusion-mini-cart-empty">
									<p><?php esc_html_e( 'Your cart is currently empty.', 'digifusion' ); ?></p>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * AJAX handler to get cart data
	 */
	public function ajax_get_cart_data() {
		// Initialize WooCommerce cart if needed
		if ( ! WC()->cart ) {
			if ( function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
			}
		}

		$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
		
		// Get clean cart total without HTML tags and entities
		$cart_total_html = WC()->cart ? WC()->cart->get_cart_total() : wc_price( 0 );
		$cart_total_clean = html_entity_decode( strip_tags( $cart_total_html ), ENT_QUOTES, 'UTF-8' );

		wp_send_json_success( array(
			'count' => $cart_count,
			'total' => $cart_total_clean,
			'total_html' => $cart_total_html,
		) );
	}

	/**
	 * AJAX handler to get cart items for mini cart
	 */
	public function ajax_get_cart_items() {
		// Initialize WooCommerce cart if needed
		if ( ! WC()->cart ) {
			if ( function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
			}
		}

		$cart_items = array();

		if ( WC()->cart && ! WC()->cart->is_empty() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$product = $cart_item['data'];
				$product_id = $cart_item['product_id'];
				$quantity = $cart_item['quantity'];
				
				if ( ! $product || ! $product->exists() ) {
					continue;
				}
				
				$product_name = $product->get_name();
				
				// Get clean price without HTML tags and entities
				$product_price_html = WC()->cart->get_product_price( $product );
				$product_price_clean = html_entity_decode( strip_tags( $product_price_html ), ENT_QUOTES, 'UTF-8' );
				
				$product_image_id = $product->get_image_id();
				$product_image_url = $product_image_id ? wp_get_attachment_image_url( $product_image_id, 'thumbnail' ) : '';
				$product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
				
				$cart_items[] = array(
					'key' => $cart_item_key,
					'name' => $product_name,
					'price' => $product_price_clean,
					'price_html' => $product_price_html,
					'quantity' => $quantity,
					'image' => $product_image_url,
					'permalink' => $product_permalink,
				);
			}
		}

		// Get clean cart total
		$cart_total_html = WC()->cart ? WC()->cart->get_cart_total() : wc_price( 0 );
		$cart_total_clean = html_entity_decode( strip_tags( $cart_total_html ), ENT_QUOTES, 'UTF-8' );

		wp_send_json_success( array(
			'items' => $cart_items,
			'count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
			'total' => $cart_total_clean,
			'total_html' => $cart_total_html,
		) );
	}

	/**
	 * AJAX handler to update cart item quantity
	 */
	public function ajax_update_cart_item() {
		// Use check_ajax_referer with die=false for cart operations
		$nonce_check = check_ajax_referer( 'digifusion_cart_nonce', 'nonce', false );
		
		// For cart operations, we allow non-logged users but still verify the nonce when possible
		if ( ! $nonce_check && is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'digifusion' ) ) );
			return;
		}

		// Initialize WooCommerce cart if needed
		if ( ! WC()->cart ) {
			if ( function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
			}
		}

		$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
		$quantity = intval( $_POST['quantity'] );

		if ( empty( $cart_item_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cart item', 'digifusion' ) ) );
			return;
		}

		if ( ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Cart not available', 'digifusion' ) ) );
			return;
		}

		// Update cart item quantity
		if ( $quantity <= 0 ) {
			WC()->cart->remove_cart_item( $cart_item_key );
		} else {
			WC()->cart->set_quantity( $cart_item_key, $quantity );
		}

		// Get clean cart total
		$cart_total_html = WC()->cart->get_cart_total();
		$cart_total_clean = html_entity_decode( strip_tags( $cart_total_html ), ENT_QUOTES, 'UTF-8' );

		wp_send_json_success( array(
			'message' => __( 'Cart updated', 'digifusion' ),
			'count' => WC()->cart->get_cart_contents_count(),
			'total' => $cart_total_clean,
			'total_html' => $cart_total_html,
		) );
	}

	/**
	 * AJAX handler to remove cart item
	 */
	public function ajax_remove_cart_item() {
		// Use check_ajax_referer with die=false for cart operations
		$nonce_check = check_ajax_referer( 'digifusion_cart_nonce', 'nonce', false );
		
		// For cart operations, we allow non-logged users but still verify the nonce when possible
		if ( ! $nonce_check && is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed', 'digifusion' ) ) );
			return;
		}

		// Initialize WooCommerce cart if needed
		if ( ! WC()->cart ) {
			if ( function_exists( 'wc_load_cart' ) ) {
				wc_load_cart();
			}
		}

		$cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );

		if ( empty( $cart_item_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid cart item', 'digifusion' ) ) );
			return;
		}

		if ( ! WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Cart not available', 'digifusion' ) ) );
			return;
		}

		// Remove cart item
		WC()->cart->remove_cart_item( $cart_item_key );

		// Get clean cart total
		$cart_total_html = WC()->cart->get_cart_total();
		$cart_total_clean = html_entity_decode( strip_tags( $cart_total_html ), ENT_QUOTES, 'UTF-8' );

		wp_send_json_success( array(
			'message' => __( 'Item removed from cart', 'digifusion' ),
			'count' => WC()->cart->get_cart_contents_count(),
			'total' => $cart_total_clean,
			'total_html' => $cart_total_html,
		) );
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