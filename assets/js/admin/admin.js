(() => {
  // resources/js/admin/admin.js
  (function() {
    "use strict";
    document.addEventListener("DOMContentLoaded", function() {
      document.addEventListener("click", function(e) {
        if (e.target.closest(".digifusion-plugin-action")) {
          e.preventDefault();
          const button = e.target.closest(".digifusion-plugin-action");
          const plugin = button.dataset.plugin;
          const action = button.dataset.action;
          if (button.dataset.url) {
            window.open(button.dataset.url, "_blank");
            return;
          }
          button.disabled = true;
          const spanElement = button.querySelector("span");
          const originalText = spanElement.textContent;
          if (action === "install") {
            spanElement.textContent = digifusionVars.strings.installing;
            installPlugin(plugin, button, originalText);
          } else if (action === "activate") {
            spanElement.textContent = digifusionVars.strings.activating;
            activatePlugin(plugin, button, originalText);
          }
        }
      });
      function installPlugin(plugin, button, originalText) {
        const formData = new FormData();
        formData.append("action", "digifusion_install_plugin");
        formData.append("plugin", plugin);
        formData.append("nonce", digifusionVars.nonce);
        fetch(digifusionVars.ajax_url, {
          method: "POST",
          body: formData
        }).then((response) => response.json()).then((data) => {
          if (data.success) {
            showNotification(data.data.message, "success");
            updateButton(button, data.data.status, plugin);
          } else {
            showNotification(data.data || digifusionVars.strings.error, "error");
            resetButton(button, originalText);
          }
        }).catch((error) => {
          console.error("Error:", error);
          showNotification(digifusionVars.strings.error, "error");
          resetButton(button, originalText);
        });
      }
      function activatePlugin(plugin, button, originalText) {
        const formData = new FormData();
        formData.append("action", "digifusion_activate_plugin");
        formData.append("plugin", plugin);
        formData.append("nonce", digifusionVars.nonce);
        fetch(digifusionVars.ajax_url, {
          method: "POST",
          body: formData
        }).then((response) => response.json()).then((data) => {
          if (data.success) {
            showNotification(data.data.message, "success");
            updateButton(button, data.data.status, plugin);
          } else {
            showNotification(data.data || digifusionVars.strings.error, "error");
            resetButton(button, originalText);
          }
        }).catch((error) => {
          console.error("Error:", error);
          showNotification(digifusionVars.strings.error, "error");
          resetButton(button, originalText);
        });
      }
      function updateButton(button, status, plugin) {
        button.disabled = false;
        button.classList.remove("button-primary", "button-secondary");
        button.classList.add(status.button_class);
        button.querySelector("span").textContent = status.button_text;
        if (status.status === "active") {
          button.dataset.action = "learn_more";
          button.dataset.url = status.url;
        } else if (status.status === "inactive") {
          button.dataset.action = "activate";
          delete button.dataset.url;
        } else {
          button.dataset.action = "install";
          delete button.dataset.url;
        }
      }
      function resetButton(button, originalText) {
        button.disabled = false;
        button.querySelector("span").textContent = originalText;
      }
      function showNotification(message, type) {
        const existingNotifications = document.querySelectorAll(".digifusion-notification");
        existingNotifications.forEach((notification) => notification.remove());
        const notificationClass = type === "success" ? "notice-success" : "notice-error";
        const notificationHTML = `
                <div class="notice ${notificationClass} is-dismissible digifusion-notification" style="margin: 1rem 0;">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `;
        const adminContent = document.querySelector(".digifusion-admin-content");
        if (adminContent) {
          adminContent.insertAdjacentHTML("beforebegin", notificationHTML);
          const newNotification = adminContent.previousElementSibling;
          if (newNotification && newNotification.classList.contains("digifusion-notification")) {
            const dismissButton = newNotification.querySelector(".notice-dismiss");
            dismissButton.addEventListener("click", function() {
              fadeOut(newNotification, 300, function() {
                newNotification.remove();
              });
            });
            if (type === "success") {
              setTimeout(function() {
                if (newNotification && newNotification.parentNode) {
                  fadeOut(newNotification, 300, function() {
                    newNotification.remove();
                  });
                }
              }, 5e3);
            }
          }
        }
      }
      function fadeOut(element, duration, callback) {
        element.style.transition = `opacity ${duration}ms`;
        element.style.opacity = "0";
        setTimeout(function() {
          if (callback) callback();
        }, duration);
      }
      function checkPluginStatus() {
        const pluginButtons = document.querySelectorAll(".digifusion-plugin-action");
        pluginButtons.forEach(function(button) {
          const plugin = button.dataset.plugin;
          const formData = new FormData();
          formData.append("action", "digifusion_get_plugin_status");
          formData.append("plugin", plugin);
          formData.append("nonce", digifusionVars.nonce);
          fetch(digifusionVars.ajax_url, {
            method: "POST",
            body: formData
          }).then((response) => response.json()).then((data) => {
            if (data.success) {
              updateButton(button, data.data.status, plugin);
            }
          }).catch((error) => {
            console.error("Error checking plugin status:", error);
          });
        });
      }
      checkPluginStatus();
    });
  })();
})();
