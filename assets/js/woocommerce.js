(() => {
  var __getOwnPropNames = Object.getOwnPropertyNames;
  var __commonJS = (cb, mod) => function __require() {
    return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
  };

  // resources/js/woocommerce.js
  var require_woocommerce = __commonJS({
    "resources/js/woocommerce.js"(exports, module) {
      var DigiFusionWooCommerce = class {
        constructor() {
          this.cartIconLink = document.querySelector(".digi-cart-icon-link");
          this.cartIcon = document.querySelector(".digi-cart-icon");
          this.cartIconWrapper = document.querySelector(".digi-cart-icon-wrapper");
          this.cartCount = document.querySelector(".digi-cart-count");
          this.cartTotal = document.querySelector(".digi-cart-total");
          this.miniCart = document.querySelector(".digi-mini-cart");
          this.miniCartItems = document.querySelector(".digi-mini-cart-items");
          this.miniCartClose = document.querySelector(".digi-mini-cart-close");
          this.isMobile = window.innerWidth <= 768;
          this.isOpen = false;
          this.isRemoving = false;
          this.init();
        }
        /**
         * Initialize WooCommerce functionality
         */
        init() {
          this.updateCartDisplay();
          this.bindEvents();
          if (this.miniCart && digifusionWoo.showMiniCart) {
            this.initMiniCart();
          }
          window.addEventListener("resize", () => {
            this.isMobile = window.innerWidth <= 768;
          });
          this.bindWooCommerceEvents();
        }
        /**
         * Bind WooCommerce specific events
         */
        bindWooCommerceEvents() {
          document.body.addEventListener("wc_fragments_loaded", () => {
            this.handleCartFragmentsUpdate();
          });
          document.body.addEventListener("wc_fragments_refreshed", () => {
            this.handleCartFragmentsUpdate();
          });
          document.body.addEventListener("added_to_cart", (event) => {
            this.handleAddToCart(event);
          });
          document.body.addEventListener("updated_wc_div", () => {
            this.handleCartUpdate();
          });
          document.addEventListener("change", (event) => {
            if (event.target.matches('.qty, input[name*="cart["][name*="][qty]"]')) {
              this.handleCartUpdate();
            }
          });
        }
        /**
         * Handle cart fragments update
         */
        handleCartFragmentsUpdate() {
          this.updateCartElements();
          this.updateMiniCart();
        }
        /**
         * Handle add to cart event
         */
        handleAddToCart(event) {
          this.updateCartDisplay();
          this.updateMiniCart();
          this.showCartNotification(digifusionWoo.strings.added);
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
          const cartForms = document.querySelectorAll("form.woocommerce-cart-form");
          cartForms.forEach((form) => {
            form.addEventListener("submit", () => {
              setTimeout(() => {
                this.updateCartDisplay();
                this.updateMiniCart();
              }, 1e3);
            });
          });
          document.addEventListener("click", (event) => {
            if (event.target.matches(".remove_from_cart_button, .product-remove a")) {
              setTimeout(() => {
                this.updateCartDisplay();
                this.updateMiniCart();
              }, 1e3);
            }
          });
        }
        /**
         * Initialize mini cart functionality
         */
        initMiniCart() {
          if (!this.cartIconWrapper || !this.miniCart) return;
          if (this.cartIconLink) {
            this.cartIconLink.addEventListener("click", (event) => {
              if (this.isMobile || this.cartIconWrapper.dataset.showMiniCart === "true") {
                event.preventDefault();
                this.toggleMiniCart();
              }
            });
          }
          if (this.miniCartClose) {
            this.miniCartClose.addEventListener("click", () => {
              this.closeMiniCart();
            });
          }
          document.addEventListener("click", (event) => {
            if (this.isOpen && !this.cartIconWrapper.contains(event.target) && !this.miniCart.contains(event.target)) {
              this.closeMiniCart();
            }
          });
          document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && this.isOpen) {
              this.closeMiniCart();
            }
          });
          this.bindMiniCartItemEvents();
          if (!this.isMobile) {
            this.cartIconWrapper.addEventListener("mouseenter", () => {
              this.openMiniCart();
            });
            this.cartIconWrapper.addEventListener("mouseleave", () => {
              this.closeMiniCart();
            });
          }
        }
        /**
         * Bind mini cart item events
         */
        bindMiniCartItemEvents() {
          if (!this.miniCartItems) return;
          this.miniCartItems.addEventListener("click", (event) => {
            const removeButton = event.target.closest(".digi-mini-cart-item-remove");
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
          this.miniCart.classList.add("digi-mini-cart--open");
          this.miniCart.setAttribute("aria-hidden", "false");
          this.updateMiniCart();
          if (this.isMobile && this.miniCartClose) {
            this.miniCartClose.focus();
          }
          if (this.isMobile) {
            document.body.style.overflow = "hidden";
          }
        }
        /**
         * Close mini cart
         */
        closeMiniCart() {
          if (!this.miniCart) return;
          const focusedElement = this.miniCart.querySelector(":focus");
          if (focusedElement) {
            focusedElement.blur();
          }
          this.isOpen = false;
          this.miniCart.classList.remove("digi-mini-cart--open");
          this.miniCart.setAttribute("aria-hidden", "true");
          if (this.isMobile) {
            document.body.style.overflow = "";
          }
        }
        /**
         * Update mini cart content
         */
        async updateMiniCart() {
          if (!this.miniCartItems || !digifusionWoo.showMiniCart) return;
          try {
            const formData = new FormData();
            formData.append("action", "digifusion_get_mini_cart");
            formData.append("nonce", digifusionWoo.nonce);
            const response = await fetch(digifusionWoo.ajaxUrl, {
              method: "POST",
              body: formData
            });
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.success) {
              this.miniCartItems.innerHTML = data.data.mini_cart_html;
              this.bindMiniCartItemEvents();
            } else {
              console.error("Mini cart update failed:", data.data || "Unknown error");
            }
          } catch (error) {
            console.error("Update mini cart error:", error);
          }
        }
        /**
         * Remove item from cart
         */
        async removeCartItem(cartItemKey, buttonElement) {
          if (!cartItemKey || this.isRemoving) return;
          this.isRemoving = true;
          if (buttonElement) {
            buttonElement.disabled = true;
            buttonElement.style.opacity = "0.5";
          }
          try {
            const formData = new FormData();
            formData.append("action", "digifusion_remove_cart_item");
            formData.append("cart_item_key", cartItemKey);
            formData.append("nonce", digifusionWoo.nonce);
            const response = await fetch(digifusionWoo.ajaxUrl, {
              method: "POST",
              body: formData
            });
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            if (data.success) {
              const itemElement = buttonElement ? buttonElement.closest(".digi-mini-cart-item") : null;
              if (itemElement) {
                itemElement.style.transition = "opacity 0.3s ease, transform 0.3s ease";
                itemElement.style.opacity = "0";
                itemElement.style.transform = "translateX(100%)";
                setTimeout(() => {
                  if (itemElement.parentNode) {
                    itemElement.remove();
                  }
                }, 300);
              }
              this.updateCartDisplay();
              this.updateMiniCart();
              this.showCartNotification(digifusionWoo.strings.removed);
              document.body.dispatchEvent(new CustomEvent("removed_from_cart", {
                detail: { cartItemKey }
              }));
              if (typeof wc_cart_fragments_params !== "undefined") {
                document.body.dispatchEvent(new CustomEvent("wc_fragment_refresh"));
              }
            } else {
              const errorMessage = data.data || digifusionWoo.strings.failed;
              console.error("Remove cart item failed:", errorMessage);
              this.showCartNotification(errorMessage, "error");
            }
          } catch (error) {
            console.error("Remove cart item error:", error);
            this.showCartNotification(digifusionWoo.strings.failed, "error");
          } finally {
            this.isRemoving = false;
            if (buttonElement) {
              buttonElement.disabled = false;
              buttonElement.style.opacity = "1";
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
            formData.append("action", "digifusion_get_cart_data");
            formData.append("nonce", digifusionWoo.nonce);
            const response = await fetch(digifusionWoo.ajaxUrl, {
              method: "POST",
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
              console.error("Cart display update failed:", data.data || "Unknown error");
            }
          } catch (error) {
            console.error("Update cart display error:", error);
          }
        }
        /**
         * Update cart elements from existing DOM
         */
        updateCartElements() {
          this.cartCount = document.querySelector(".digi-cart-count");
          this.cartTotal = document.querySelector(".digi-cart-total");
          this.miniCartItems = document.querySelector(".digi-mini-cart-items");
          if (this.miniCartItems) {
            this.bindMiniCartItemEvents();
          }
        }
        /**
         * Update cart count badge
         */
        updateCartCount(count) {
          if (!this.cartCount) {
            this.cartCount = document.querySelector(".digi-cart-count");
            if (!this.cartCount) return;
          }
          this.cartCount.textContent = count;
          if (count > 0) {
            this.cartCount.style.display = "flex";
            this.cartCount.style.animation = "none";
            requestAnimationFrame(() => {
              this.cartCount.style.animation = "cartBounce 0.3s ease";
            });
          } else {
            this.cartCount.style.display = "none";
          }
        }
        /**
         * Update cart total price
         */
        updateCartTotal(total) {
          if (!this.cartTotal) {
            this.cartTotal = document.querySelector(".digi-cart-total");
            if (!this.cartTotal) return;
          }
          this.cartTotal.innerHTML = total;
          if (total && total !== "" && total !== "0") {
            this.cartTotal.style.display = "block";
          } else {
            this.cartTotal.style.display = "none";
          }
        }
        /**
         * Show cart notification
         */
        showCartNotification(message, type = "success") {
          const existingNotifications = document.querySelectorAll(".digi-cart-notification");
          existingNotifications.forEach((notification2) => {
            if (notification2.parentNode) {
              notification2.parentNode.removeChild(notification2);
            }
          });
          const notification = document.createElement("div");
          notification.className = `digi-cart-notification digi-cart-notification--${type}`;
          notification.textContent = message;
          notification.setAttribute("role", type === "error" ? "alert" : "status");
          notification.setAttribute("aria-live", "polite");
          document.body.appendChild(notification);
          requestAnimationFrame(() => {
            notification.style.opacity = "1";
            notification.style.transform = "translateX(0)";
          });
          setTimeout(() => {
            notification.style.opacity = "0";
            notification.style.transform = "translateX(100%)";
            setTimeout(() => {
              if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
              }
            }, 300);
          }, 4e3);
        }
        /**
         * Refresh cart data and mini cart
         */
        async refreshCart() {
          await this.updateCartDisplay();
          await this.updateMiniCart();
        }
      };
      document.addEventListener("DOMContentLoaded", () => {
        if (document.querySelector(".digi-cart-icon-link") || document.querySelector(".woocommerce")) {
          window.digiFusionWoo = new DigiFusionWooCommerce();
        }
      });
      if (typeof MutationObserver !== "undefined") {
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.type === "childList") {
              const addedNodes = Array.from(mutation.addedNodes);
              const hasCartElements = addedNodes.some(
                (node) => node.nodeType === Node.ELEMENT_NODE && (node.querySelector && node.querySelector(".digi-cart-icon-link"))
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
      if (typeof module !== "undefined" && module.exports) {
        module.exports = DigiFusionWooCommerce;
      }
    }
  });
  require_woocommerce();
})();
