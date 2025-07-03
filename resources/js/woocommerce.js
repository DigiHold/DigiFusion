/**
 * DigiFusion Cart Icon JavaScript - Enhanced Mobile Support
 * 
 * @package DigiFusion
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get all cart icon wrappers
    const cartBlocks = document.querySelectorAll('.digifusion-cart-icon-wrapper');
    
    if (!cartBlocks.length) return;
    
    // Check if we have the localized data
    if (typeof digifusionCartData === 'undefined') {
        console.error('DigiFusion Cart Icon: Missing AJAX data');
        return;
    }
    
    // Mobile breakpoint (1024px and below)
    const MOBILE_BREAKPOINT = 1024;
    
    // Utility function to check if we're on mobile
    function isMobile() {
        return window.innerWidth <= MOBILE_BREAKPOINT;
    }

	// Slide down function (same as navigation)
	function slideDown(element, duration) {
		element.style.display = 'flex';
		element.style.height = '0px';
		element.style.overflow = 'hidden';
		element.style.transition = `height ${duration}ms ease-in-out`;
		
		// Get the natural height
		const naturalHeight = element.scrollHeight;
		
		// Trigger animation
		requestAnimationFrame(() => {
			element.style.height = naturalHeight + 'px';
		});
		
		// Clean up after animation
		setTimeout(() => {
			element.style.height = '';
			element.style.overflow = '';
			element.style.transition = '';
		}, duration);
	}

	// Slide up function (same as navigation)
	function slideUp(element, duration, callback) {
		const currentHeight = element.scrollHeight;
		element.style.height = currentHeight + 'px';
		element.style.overflow = 'hidden';
		element.style.transition = `height ${duration}ms ease-in-out`;
		
		// Trigger animation
		requestAnimationFrame(() => {
			element.style.height = '0px';
		});
		
		// Clean up after animation
		setTimeout(() => {
			element.style.height = '';
			element.style.overflow = '';
			element.style.transition = '';
			element.style.display = 'none';
			if (callback) callback();
		}, duration);
	}
    
    cartBlocks.forEach(function(cartBlock) {
        initCartIcon(cartBlock);
    });
    
    function initCartIcon(cartBlock) {
        // Cart elements
        const cartIcon = cartBlock.querySelector('.digifusion-cart-icon-icon');
        const cartCount = cartBlock.querySelector('.digifusion-cart-count');
        const cartTotal = cartBlock.querySelector('.digifusion-cart-total');
        const miniCart = cartBlock.querySelector('.digifusion-mini-cart');
        const miniCartContent = miniCart ? miniCart.querySelector('.digifusion-mini-cart-content') : null;
        const cartLink = cartBlock.querySelector('.digifusion-cart-icon-link');
        
        let isUpdating = false;
        let isOpen = false;
        
        // Configuration
        const hasMiniCart = cartLink ? cartLink.dataset.showMiniCart === 'true' : false;
        
        // Update cart elements
        function updateCartElements(count, total, totalHtml) {
            // Update count
            if (cartCount) {
                cartCount.textContent = count;
            }
            
            // Update total
            if (cartTotal) {
                cartTotal.innerHTML = totalHtml || total;
            }
            
            // Update block classes
            cartBlock.classList.toggle('cart-empty', count === 0);
            cartBlock.classList.toggle('cart-has-items', count > 0);
        }
        
        // Fetch cart items for mini cart
        function fetchCartItems() {
            return new Promise(function(resolve) {
                const formData = new FormData();
                formData.append('action', 'digifusion_get_cart_items');
                
                fetch(digifusionCartData.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success && data.data.items) {
                        updateMiniCart(data.data.items, data.data.total_html || data.data.total);
                    }
                    resolve();
                })
                .catch(function(error) {
                    console.error('DigiFusion Cart Icon: Error fetching cart items', error);
                    resolve();
                });
            });
        }
        
        // Update mini cart content
        function updateMiniCart(items, total) {
            if (!miniCartContent) return;
            
            let itemsHTML = '';
            
            if (items && items.length > 0) {
                items.forEach(function(item) {
                    itemsHTML += 
                        '<div class="digifusion-mini-cart-item" data-cart-item-key="' + item.key + '">' +
                        '<div class="digifusion-mini-cart-item-image">' +
                        (item.image ? '<img src="' + item.image + '" alt="' + item.name + '">' : '') +
                        '</div>' +
                        '<div class="digifusion-mini-cart-item-details">' +
                        '<div class="digifusion-mini-cart-item-name">' + 
                        (item.permalink ? '<a href="' + item.permalink + '">' + item.name + '</a>' : item.name) +
                        '</div>' +
                        '<div class="digifusion-mini-cart-item-price">' + item.price_html + '</div>' +
                        '</div>' +
                        '<input type="number" class="digifusion-mini-cart-item-quantity" value="' + item.quantity + '" min="0" data-cart-item-key="' + item.key + '" aria-label="' + digifusionCartData.strings.quantity + '">' +
                        '<button class="digifusion-mini-cart-item-remove" data-cart-item-key="' + item.key + '" title="' + digifusionCartData.strings.remove_item + '" aria-label="' + digifusionCartData.strings.remove_item + '">&times;</button>' +
                        '</div>';
                });
            } else {
                itemsHTML = '<div class="digifusion-mini-cart-empty">' + digifusionCartData.strings.empty_cart + '</div>';
            }
            
            const cartUrl = digifusionCartData.cart_url || '';
            const checkoutUrl = digifusionCartData.checkout_url || '';
            
            miniCartContent.innerHTML = 
                '<div class="digifusion-mini-cart-items">' + itemsHTML + '</div>' +
                (items && items.length > 0 ? 
                    '<div class="digifusion-mini-cart-total"><span>' + digifusionCartData.strings.total + '</span><span class="total-amount">' + total + '</span></div>' +
                    '<div class="digifusion-mini-cart-buttons">' +
                    '<a href="' + cartUrl + '" class="digifusion-mini-cart-button secondary">' + digifusionCartData.strings.view_cart + '</a>' +
                    '<a href="' + checkoutUrl + '" class="digifusion-mini-cart-button primary">' + digifusionCartData.strings.checkout + '</a>' +
                    '</div>'
                : '');
        }
        
        // Animate cart icon
        function animateCartIcon() {
            if (!cartIcon) return;
            
            cartIcon.classList.add('cart-item-added');
            
            setTimeout(function() {
                cartIcon.classList.remove('cart-item-added');
            }, 600);
        }
        
        // Fetch cart data
        function fetchCartData(justAdded) {
            if (isUpdating) return;
            isUpdating = true;
            
            const formData = new FormData();
            formData.append('action', 'digifusion_get_cart_data');
            
            fetch(digifusionCartData.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    updateCartElements(data.data.count, data.data.total, data.data.total_html);
                    
                    if (hasMiniCart) {
                        fetchCartItems().then(function() {
                            if (justAdded) {
                                animateCartIcon();
                            }
                        });
                    } else if (justAdded) {
                        animateCartIcon();
                    }
                }
                isUpdating = false;
            })
            .catch(function(error) {
                console.error('DigiFusion Cart Icon: Error fetching cart data', error);
                isUpdating = false;
            });
        }
        
        // Update cart item quantity
        function updateCartItemQuantity(itemKey, quantity) {
            const formData = new FormData();
            formData.append('action', 'digifusion_update_cart_item');
            formData.append('nonce', digifusionCartData.cart_nonce);
            formData.append('cart_item_key', itemKey);
            formData.append('quantity', quantity);
            
            fetch(digifusionCartData.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    setTimeout(function() {
                        fetchCartData();
                    }, 200);
                }
            })
            .catch(function(error) {
                console.error('DigiFusion Cart Icon: Error updating cart item', error);
            });
        }
        
        // Remove cart item
        function removeCartItem(itemKey) {
            const formData = new FormData();
            formData.append('action', 'digifusion_remove_cart_item');
            formData.append('nonce', digifusionCartData.cart_nonce);
            formData.append('cart_item_key', itemKey);
            
            fetch(digifusionCartData.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    setTimeout(function() {
                        fetchCartData();
                    }, 200);
                }
            })
            .catch(function(error) {
                console.error('DigiFusion Cart Icon: Error removing cart item', error);
            });
        }
        
        // Show mini cart (with slide down animation for mobile)
        function showMiniCart() {
			if (!miniCart || !hasMiniCart) return;
			
			isOpen = true;
			cartBlock.classList.add('mini-cart-open');
			
			if (isMobile()) {
				cartBlock.classList.add('submenu-open');
				slideDown(miniCart, 300);
			} else {
				miniCart.classList.add('visible');
			}
		}
        
        // Hide mini cart
        function hideMiniCart() {
			if (!miniCart || !hasMiniCart) return;
			
			isOpen = false;
			cartBlock.classList.remove('mini-cart-open');
			
			if (isMobile()) {
				slideUp(miniCart, 300, () => {
					cartBlock.classList.remove('submenu-open');
				});
			} else {
				miniCart.classList.remove('visible');
			}
		}
        
        // Toggle mini cart (for mobile click)
        function toggleMiniCart() {
            if (isOpen) {
                hideMiniCart();
            } else {
                showMiniCart();
            }
        }
        
        // Setup event listeners based on device type
        function setupEventListeners() {
            if (!cartLink) return;
            
            // Remove existing event listeners to avoid duplicates
            cartLink.removeEventListener('mouseenter', showMiniCart);
            cartLink.removeEventListener('mouseleave', handleMouseLeave);
            cartLink.removeEventListener('click', handleClick);
            
            if (hasMiniCart) {
                if (isMobile()) {
                    // Mobile: Click to toggle mini cart
                    cartLink.addEventListener('click', handleClick);
                } else {
                    // Desktop: Hover to show/hide mini cart
                    cartLink.addEventListener('mouseenter', showMiniCart);
                    cartLink.addEventListener('mouseleave', handleMouseLeave);
                    
                    // Also handle click for desktop
                    cartLink.addEventListener('click', handleClick);
                }
            }
        }
        
        // Handle mouse leave (desktop only)
        function handleMouseLeave() {
            if (isMobile()) return;
            
            setTimeout(function() {
                if (!miniCart.matches(':hover') && !cartLink.matches(':hover')) {
                    hideMiniCart();
                }
            }, 100);
        }
        
        // Handle click events
        function handleClick(e) {
            e.preventDefault();
            
            if (!hasMiniCart) {
                // If mini cart is disabled, allow normal navigation
                window.location.href = cartLink.href;
                return;
            }
            
            if (isMobile()) {
                // Mobile: Toggle mini cart
                toggleMiniCart();
            } else {
                // Desktop: Toggle mini cart
                toggleMiniCart();
            }
        }
        
        // Event listeners for mini cart interactions
        if (miniCart) {
            // Handle quantity changes
            miniCart.addEventListener('change', function(e) {
                if (e.target.matches('.digifusion-mini-cart-item-quantity')) {
                    const itemKey = e.target.dataset.cartItemKey;
                    const quantity = parseInt(e.target.value) || 0;
                    if (itemKey) {
                        updateCartItemQuantity(itemKey, quantity);
                    }
                }
            });
            
            // Handle remove item clicks
            miniCart.addEventListener('click', function(e) {
                if (e.target.matches('.digifusion-mini-cart-item-remove')) {
                    e.preventDefault();
                    const itemKey = e.target.dataset.cartItemKey;
                    if (itemKey) {
                        removeCartItem(itemKey);
                    }
                }
                e.stopPropagation();
            });
            
            // Handle mouse leave for desktop
            if (!isMobile()) {
                miniCart.addEventListener('mouseleave', function() {
                    setTimeout(function() {
                        if (!miniCart.matches(':hover') && !cartLink.matches(':hover')) {
                            hideMiniCart();
                        }
                    }, 100);
                });
            }
        }
        
        // Setup initial event listeners
        setupEventListeners();
        
        // Re-setup event listeners on window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                setupEventListeners();
                
                // Close mini cart if switching from mobile to desktop
                if (!isMobile() && isOpen) {
                    hideMiniCart();
                }
            }, 150);
        });
        
        // Hide mini cart when clicking outside (mobile)
        document.addEventListener('click', function(e) {
            if (isMobile() && !cartBlock.contains(e.target) && isOpen) {
                hideMiniCart();
            }
        });
        
        // Hide mini cart on escape key (mobile)
        document.addEventListener('keydown', function(e) {
            if (isMobile() && e.key === 'Escape' && isOpen) {
                hideMiniCart();
            }
        });
        
        // Initial fetch
        setTimeout(function() {
            fetchCartData();
        }, 100);
    }
    
    // Listen for ALL WooCommerce events
    document.body.addEventListener('added_to_cart', function(e) {
        setTimeout(function() {
            cartBlocks.forEach(function(cartBlock) {
                const cartIcon = cartBlock.querySelector('.digifusion-cart-icon-icon');
                if (cartIcon) {
                    cartIcon.classList.add('cart-item-added');
                    setTimeout(function() {
                        cartIcon.classList.remove('cart-item-added');
                    }, 600);
                }
            });
            refreshAllCarts(true);
        }, 200);
    });
    
    // WooCommerce fragments refresh
    document.body.addEventListener('wc_fragment_refresh', function(e) {
        setTimeout(function() {
            refreshAllCarts();
        }, 100);
    });
    
    // Listen for form submissions on add to cart forms
    document.addEventListener('submit', function(e) {
        if (e.target.matches('.cart form, form.cart')) {
            setTimeout(function() {
                refreshAllCarts(true);
            }, 1000);
        }
    });
    
    // Listen for AJAX form submissions and button clicks
    document.addEventListener('click', function(e) {
        if (e.target.matches('.add_to_cart_button, .single_add_to_cart_button, .ajax_add_to_cart') || 
            e.target.closest('.add_to_cart_button, .single_add_to_cart_button, .ajax_add_to_cart')) {
            setTimeout(function() {
                refreshAllCarts(true);
            }, 1000);
        }
    });
    
    // Helper function to refresh all cart icons
    function refreshAllCarts(justAdded) {
        cartBlocks.forEach(function(cartBlock) {
            const cartIcon = cartBlock.querySelector('.digifusion-cart-icon-icon');
            const cartCount = cartBlock.querySelector('.digifusion-cart-count');
            const cartTotal = cartBlock.querySelector('.digifusion-cart-total');
            const miniCart = cartBlock.querySelector('.digifusion-mini-cart');
            const miniCartContent = miniCart ? miniCart.querySelector('.digifusion-mini-cart-content') : null;
            const cartLink = cartBlock.querySelector('.digifusion-cart-icon-link');
            
            const hasMiniCartForBlock = cartLink ? cartLink.dataset.showMiniCart === 'true' : false;
            
            const formData = new FormData();
            formData.append('action', 'digifusion_get_cart_data');
            
            fetch(digifusionCartData.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    if (cartCount) {
                        cartCount.textContent = data.data.count;
                    }
                    
                    if (cartTotal) {
                        cartTotal.innerHTML = data.data.total_html || data.data.total;
                    }
                    
                    cartBlock.classList.toggle('cart-empty', data.data.count === 0);
                    cartBlock.classList.toggle('cart-has-items', data.data.count > 0);
                    
                    if (hasMiniCartForBlock && miniCartContent) {
                        const itemsFormData = new FormData();
                        itemsFormData.append('action', 'digifusion_get_cart_items');
                        
                        fetch(digifusionCartData.ajax_url, {
                            method: 'POST',
                            body: itemsFormData,
                            credentials: 'same-origin'
                        })
                        .then(function(response) {
                            return response.json();
                        })
                        .then(function(itemsData) {
                            if (itemsData.success && itemsData.data.items) {
                                let itemsHTML = '';
                                
                                if (itemsData.data.items && itemsData.data.items.length > 0) {
                                    itemsData.data.items.forEach(function(item) {
                                        itemsHTML += 
                                            '<div class="digifusion-mini-cart-item" data-cart-item-key="' + item.key + '">' +
                                            '<div class="digifusion-mini-cart-item-image">' +
                                            (item.image ? '<img src="' + item.image + '" alt="' + item.name + '">' : '') +
                                            '</div>' +
                                            '<div class="digifusion-mini-cart-item-details">' +
                                            '<div class="digifusion-mini-cart-item-name">' + 
                                            (item.permalink ? '<a href="' + item.permalink + '">' + item.name + '</a>' : item.name) +
                                            '</div>' +
                                            '<div class="digifusion-mini-cart-item-price">' + item.price_html + '</div>' +
                                            '</div>' +
                                            '<input type="number" class="digifusion-mini-cart-item-quantity" value="' + item.quantity + '" min="0" data-cart-item-key="' + item.key + '" aria-label="' + digifusionCartData.strings.quantity + '">' +
                                            '<button class="digifusion-mini-cart-item-remove" data-cart-item-key="' + item.key + '" title="' + digifusionCartData.strings.remove_item + '" aria-label="' + digifusionCartData.strings.remove_item + '">&times;</button>' +
                                            '</div>';
                                    });
                                } else {
                                    itemsHTML = '<div class="digifusion-mini-cart-empty">' + digifusionCartData.strings.empty_cart + '</div>';
                                }
                                
                                const cartUrl = digifusionCartData.cart_url || '';
                                const checkoutUrl = digifusionCartData.checkout_url || '';
                                
                                miniCartContent.innerHTML = 
                                    '<div class="digifusion-mini-cart-items">' + itemsHTML + '</div>' +
                                    (itemsData.data.items && itemsData.data.items.length > 0 ? 
                                        '<div class="digifusion-mini-cart-total"><span>' + digifusionCartData.strings.total + '</span><span class="total-amount">' + (itemsData.data.total_html || itemsData.data.total) + '</span></div>' +
                                        '<div class="digifusion-mini-cart-buttons">' +
                                        '<a href="' + cartUrl + '" class="digifusion-mini-cart-button secondary">' + digifusionCartData.strings.view_cart + '</a>' +
                                        '<a href="' + checkoutUrl + '" class="digifusion-mini-cart-button primary">' + digifusionCartData.strings.checkout + '</a>' +
                                        '</div>'
                                    : '');
                            }
                        });
                    }
                }
            });
        });
    }
    
    // Periodic refresh (as backup)
    setInterval(function() {
        refreshAllCarts();
    }, 30000);
});

// jQuery support for WooCommerce events (if jQuery is available)
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        $(document.body).on('updated_wc_div', function() {
            setTimeout(function() {
                const cartBlocks = document.querySelectorAll('.digifusion-cart-icon-wrapper');
                cartBlocks.forEach(function(cartBlock) {
                    // Simplified refresh for each cart block
                    const formData = new FormData();
                    formData.append('action', 'digifusion_get_cart_data');
                    
                    fetch(digifusionCartData.ajax_url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const cartCount = cartBlock.querySelector('.digifusion-cart-count');
                            const cartTotal = cartBlock.querySelector('.digifusion-cart-total');
                            
                            if (cartCount) cartCount.textContent = data.data.count;
                            if (cartTotal) cartTotal.innerHTML = data.data.total_html || data.data.total;
                        }
                    });
                });
            }, 100);
        });
        
        $(document.body).on('updated_cart_totals', function() {
            setTimeout(function() {
                const event = new CustomEvent('wc_cart_updated');
                document.body.dispatchEvent(event);
            }, 100);
        });
    });
}