/**
 * WooCommerce JavaScript for DigiFusion Theme
 *
 * @package DigiFusion
 * @since 1.0.0
 */

class DigiFusionWooCommerce {
	constructor() {
		this.cartIconLink = document.querySelector('.digi-cart-icon-link');
		this.cartIcon = document.querySelector('.digi-cart-icon');
		this.cartIconWrapper = document.querySelector('.digi-cart-icon-wrapper');
		this.cartCount = document.querySelector('.digi-cart-count');
		this.cartTotal = document.querySelector('.digi-cart-total');
		this.miniCart = document.querySelector('.digi-mini-cart');
		this.miniCartItems = document.querySelector('.digi-mini-cart-items');
		this.miniCartClose = document.querySelector('.digi-mini-cart-close');
		
		this.isMobile = window.innerWidth <= 768;
		this.isOpen = false;
		this.isRemoving = false; // Prevent multiple simultaneous removals
		
		this.init();
	}

	/**
	 * Initialize WooCommerce functionality
	 */
	init() {
		// Update cart on page load
		this.updateCartDisplay();

		// Listen for cart updates
		this.bindEvents();

		// Initialize mini cart
		if (this.miniCart && digifusionWoo.showMiniCart) {
			this.initMiniCart();
		}

		// Handle window resize
		window.addEventListener('resize', () => {
			this.isMobile = window.innerWidth <= 768;
		});

		// Listen for WooCommerce fragments update
		this.bindWooCommerceEvents();
	}

	/**
	 * Bind WooCommerce specific events
	 */
	bindWooCommerceEvents() {
		// Listen for WooCommerce cart fragments
		document.body.addEventListener('wc_fragments_loaded', () => {
			this.handleCartFragmentsUpdate();
		});

		document.body.addEventListener('wc_fragments_refreshed', () => {
			this.handleCartFragmentsUpdate();
		});

		// Listen for add to cart events
		document.body.addEventListener('added_to_cart', (event) => {
			this.handleAddToCart(event);
		});

		// Listen for WooCommerce cart updates
		document.body.addEventListener('updated_wc_div', () => {
			this.handleCartUpdate();
		});

		// Listen for cart item quantity changes
		document.addEventListener('change', (event) => {
			if (event.target.matches('.qty, input[name*="cart["][name*="][qty]"]')) {
				this.handleCartUpdate();
			}
		});
	}

	/**
	 * Handle cart fragments update
	 */
	handleCartFragmentsUpdate() {
		// Update cart display elements
		this.updateCartElements();
		// Update mini cart content
		this.updateMiniCart();
	}

	/**
	 * Handle add to cart event
	 */
	handleAddToCart(event) {
		this.updateCartDisplay();
		this.updateMiniCart();
		this.showCartNotification(digifusionWoo.strings.added);
		
		// Auto-open mini cart on mobile when item is added
		if (this.isMobile && this.miniCart && !this.isOpen) {
			setTimeout(() => {
				this.openMiniCart();
			}, 500);
		}
	}

	/**
	 * Handle general cart update
	 */
	handleCartUpdate() {
		this.updateCartDisplay();
		this.updateMiniCart();
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		// Handle cart form submissions
		const cartForms = document.querySelectorAll('form.woocommerce-cart-form');
		cartForms.forEach(form => {
			form.addEventListener('submit', () => {
				setTimeout(() => {
					this.updateCartDisplay();
					this.updateMiniCart();
				}, 1000);
			});
		});

		// Handle remove from cart links on cart page
		document.addEventListener('click', (event) => {
			if (event.target.matches('.remove_from_cart_button, .product-remove a')) {
				setTimeout(() => {
					this.updateCartDisplay();
					this.updateMiniCart();
				}, 1000);
			}
		});
	}

	/**
	 * Initialize mini cart functionality
	 */
	initMiniCart() {
		if (!this.cartIconWrapper || !this.miniCart) return;

		// Cart icon click handler
		if (this.cartIconLink) {
			this.cartIconLink.addEventListener('click', (event) => {
				if (this.isMobile || this.cartIconWrapper.dataset.showMiniCart === 'true') {
					event.preventDefault();
					this.toggleMiniCart();
				}
			});
		}

		// Close button
		if (this.miniCartClose) {
			this.miniCartClose.addEventListener('click', () => {
				this.closeMiniCart();
			});
		}

		// Click outside to close
		document.addEventListener('click', (event) => {
			if (this.isOpen && 
				!this.cartIconWrapper.contains(event.target) && 
				!this.miniCart.contains(event.target)) {
				this.closeMiniCart();
			}
		});

		// ESC key to close
		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && this.isOpen) {
				this.closeMiniCart();
			}
		});

		// Handle remove item clicks
		this.bindMiniCartItemEvents();

		// Desktop hover (if not mobile)
		if (!this.isMobile) {
			this.cartIconWrapper.addEventListener('mouseenter', () => {
				this.openMiniCart();
			});

			this.cartIconWrapper.addEventListener('mouseleave', () => {
				this.closeMiniCart();
			});
		}
	}

	/**
	 * Bind mini cart item events
	 */
	bindMiniCartItemEvents() {
		if (!this.miniCartItems) return;

		// Use event delegation for dynamically added items
		this.miniCartItems.addEventListener('click', (event) => {
			const removeButton = event.target.closest('.digi-mini-cart-item-remove');
			if (removeButton && !this.isRemoving) {
				event.preventDefault();
				const cartItemKey = removeButton.dataset.cartItemKey;
				if (cartItemKey) {
					this.removeCartItem(cartItemKey, removeButton);
				}
			}
		});
	}

	/**
	 * Toggle mini cart
	 */
	toggleMiniCart() {
		if (this.isOpen) {
			this.closeMiniCart();
		} else {
			this.openMiniCart();
		}
	}

	/**
	 * Open mini cart
	 */
	openMiniCart() {
		if (!this.miniCart) return;

		this.isOpen = true;
		this.miniCart.classList.add('digi-mini-cart--open');
		this.miniCart.setAttribute('aria-hidden', 'false');
		
		// Update content when opening
		this.updateMiniCart();

		// Focus management for accessibility
		if (this.isMobile && this.miniCartClose) {
			this.miniCartClose.focus();
		}

		// Prevent body scroll on mobile
		if (this.isMobile) {
			document.body.style.overflow = 'hidden';
		}
	}

	/**
	 * Close mini cart
	 */
	closeMiniCart() {
		if (!this.miniCart) return;

		// Remove focus from any focused elements inside mini cart before hiding
		const focusedElement = this.miniCart.querySelector(':focus');
		if (focusedElement) {
			focusedElement.blur();
		}

		this.isOpen = false;
		this.miniCart.classList.remove('digi-mini-cart--open');
		this.miniCart.setAttribute('aria-hidden', 'true');

		// Restore body scroll
		if (this.isMobile) {
			document.body.style.overflow = '';
		}
	}

	/**
	 * Update mini cart content
	 */
	async updateMiniCart() {
		if (!this.miniCartItems || !digifusionWoo.showMiniCart) return;

		try {
			const formData = new FormData();
			formData.append('action', 'digifusion_get_mini_cart');
			formData.append('nonce', digifusionWoo.nonce);

			const response = await fetch(digifusionWoo.ajaxUrl, {
				method: 'POST',
				body: formData
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json();

			if (data.success) {
				this.miniCartItems.innerHTML = data.data.mini_cart_html;
				// Re-bind events after content update
				this.bindMiniCartItemEvents();
			} else {
				console.error('Mini cart update failed:', data.data || 'Unknown error');
			}
		} catch (error) {
			console.error('Update mini cart error:', error);
		}
	}

	/**
	 * Remove item from cart
	 */
	async removeCartItem(cartItemKey, buttonElement) {
		if (!cartItemKey || this.isRemoving) return;

		// Prevent multiple simultaneous removals
		this.isRemoving = true;

		// Add loading state
		if (buttonElement) {
			buttonElement.disabled = true;
			buttonElement.style.opacity = '0.5';
		}

		try {
			const formData = new FormData();
			formData.append('action', 'digifusion_remove_cart_item');
			formData.append('cart_item_key', cartItemKey);
			formData.append('nonce', digifusionWoo.nonce);

			const response = await fetch(digifusionWoo.ajaxUrl, {
				method: 'POST',
				body: formData
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json();

			if (data.success) {
				// Remove the item element with animation
				const itemElement = buttonElement ? buttonElement.closest('.digi-mini-cart-item') : null;
				if (itemElement) {
					itemElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
					itemElement.style.opacity = '0';
					itemElement.style.transform = 'translateX(100%)';
					setTimeout(() => {
						if (itemElement.parentNode) {
							itemElement.remove();
						}
					}, 300);
				}

				// Update cart display
				this.updateCartDisplay();
				this.updateMiniCart();
				this.showCartNotification(digifusionWoo.strings.removed);
				
				// Trigger WooCommerce event
				document.body.dispatchEvent(new CustomEvent('removed_from_cart', {
					detail: { cartItemKey: cartItemKey }
				}));

				// Update cart fragments
				if (typeof wc_cart_fragments_params !== 'undefined') {
					document.body.dispatchEvent(new CustomEvent('wc_fragment_refresh'));
				}

			} else {
				// Handle specific error messages
				const errorMessage = data.data || digifusionWoo.strings.failed;
				console.error('Remove cart item failed:', errorMessage);
				this.showCartNotification(errorMessage, 'error');
			}
		} catch (error) {
			console.error('Remove cart item error:', error);
			this.showCartNotification(digifusionWoo.strings.failed, 'error');
		} finally {
			// Reset loading state
			this.isRemoving = false;
			if (buttonElement) {
				buttonElement.disabled = false;
				buttonElement.style.opacity = '1';
			}
		}
	}

	/**
	 * Update cart display (count and total)
	 */
	async updateCartDisplay() {
		if (!this.cartIconLink) return;

		try {
			const formData = new FormData();
			formData.append('action', 'digifusion_get_cart_data');
			formData.append('nonce', digifusionWoo.nonce);

			const response = await fetch(digifusionWoo.ajaxUrl, {
				method: 'POST',
				body: formData
			});

			if (!response.ok) {
				throw new Error(`HTTP error! status: ${response.status}`);
			}

			const data = await response.json();

			if (data.success) {
				this.updateCartCount(data.data.count);
				this.updateCartTotal(data.data.total);
			} else {
				console.error('Cart display update failed:', data.data || 'Unknown error');
			}
		} catch (error) {
			console.error('Update cart display error:', error);
		}
	}

	/**
	 * Update cart elements from existing DOM
	 */
	updateCartElements() {
		// Re-query elements in case they were replaced by fragments
		this.cartCount = document.querySelector('.digi-cart-count');
		this.cartTotal = document.querySelector('.digi-cart-total');
		this.miniCartItems = document.querySelector('.digi-mini-cart-items');

		// Re-bind events if mini cart items were updated
		if (this.miniCartItems) {
			this.bindMiniCartItemEvents();
		}
	}

	/**
	 * Update cart count badge
	 */
	updateCartCount(count) {
		if (!this.cartCount) {
			// Try to find the element again
			this.cartCount = document.querySelector('.digi-cart-count');
			if (!this.cartCount) return;
		}

		this.cartCount.textContent = count;
		
		if (count > 0) {
			this.cartCount.style.display = 'flex';
			// Add bounce animation
			this.cartCount.style.animation = 'none';
			requestAnimationFrame(() => {
				this.cartCount.style.animation = 'cartBounce 0.3s ease';
			});
		} else {
			this.cartCount.style.display = 'none';
		}
	}

	/**
	 * Update cart total price
	 */
	updateCartTotal(total) {
		if (!this.cartTotal) {
			// Try to find the element again
			this.cartTotal = document.querySelector('.digi-cart-total');
			if (!this.cartTotal) return;
		}

		this.cartTotal.innerHTML = total;
		
		if (total && total !== '' && total !== '0') {
			this.cartTotal.style.display = 'block';
		} else {
			this.cartTotal.style.display = 'none';
		}
	}

	/**
	 * Show cart notification
	 */
	showCartNotification(message, type = 'success') {
		// Remove any existing notifications
		const existingNotifications = document.querySelectorAll('.digi-cart-notification');
		existingNotifications.forEach(notification => {
			if (notification.parentNode) {
				notification.parentNode.removeChild(notification);
			}
		});

		// Create notification element
		const notification = document.createElement('div');
		notification.className = `digi-cart-notification digi-cart-notification--${type}`;
		notification.textContent = message;
		notification.setAttribute('role', type === 'error' ? 'alert' : 'status');
		notification.setAttribute('aria-live', 'polite');

		// Add to DOM
		document.body.appendChild(notification);

		// Animate in
		requestAnimationFrame(() => {
			notification.style.opacity = '1';
			notification.style.transform = 'translateX(0)';
		});

		// Remove after 4 seconds
		setTimeout(() => {
			notification.style.opacity = '0';
			notification.style.transform = 'translateX(100%)';
			setTimeout(() => {
				if (notification.parentNode) {
					notification.parentNode.removeChild(notification);
				}
			}, 300);
		}, 4000);
	}

	/**
	 * Refresh cart data and mini cart
	 */
	async refreshCart() {
		await this.updateCartDisplay();
		await this.updateMiniCart();
	}
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	// Only initialize if WooCommerce elements are present
	if (document.querySelector('.digi-cart-icon-link') || document.querySelector('.woocommerce')) {
		window.digiFusionWoo = new DigiFusionWooCommerce();
	}
});

// Re-initialize if cart elements are added dynamically
if (typeof MutationObserver !== 'undefined') {
	const observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.type === 'childList') {
				const addedNodes = Array.from(mutation.addedNodes);
				const hasCartElements = addedNodes.some(node => 
					node.nodeType === Node.ELEMENT_NODE && 
					(node.querySelector && node.querySelector('.digi-cart-icon-link'))
				);
				
				if (hasCartElements && !window.digiFusionWoo) {
					window.digiFusionWoo = new DigiFusionWooCommerce();
				}
			}
		});
	});

	observer.observe(document.body, {
		childList: true,
		subtree: true
	});
}

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
	module.exports = DigiFusionWooCommerce;
}