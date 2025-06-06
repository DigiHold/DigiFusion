/**
 * DigiFusion Dashboard JavaScript
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {

        /**
         * Handle plugin action buttons
         */
        document.addEventListener('click', function(e) {
            if (e.target.closest('.digifusion-plugin-action')) {
                e.preventDefault();

                const button = e.target.closest('.digifusion-plugin-action');
                const plugin = button.dataset.plugin;
                const action = button.dataset.action;
                
                // Don't process if it's a learn more button (has URL)
                if (button.dataset.url) {
                    window.open(button.dataset.url, '_blank');
                    return;
                }

                // Disable button and show loading state
                button.disabled = true;
                const spanElement = button.querySelector('span');
                const originalText = spanElement.textContent;
                
                if (action === 'install') {
                    spanElement.textContent = digifusionVars.strings.installing;
                    installPlugin(plugin, button, originalText);
                } else if (action === 'activate') {
                    spanElement.textContent = digifusionVars.strings.activating;
                    activatePlugin(plugin, button, originalText);
                }
            }
        });

        /**
         * Install plugin via AJAX
         */
        function installPlugin(plugin, button, originalText) {
            const formData = new FormData();
            formData.append('action', 'digifusion_install_plugin');
            formData.append('plugin', plugin);
            formData.append('nonce', digifusionVars.nonce);

            fetch(digifusionVars.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.data.message, 'success');
                    updateButton(button, data.data.status, plugin);
                } else {
                    showNotification(data.data || digifusionVars.strings.error, 'error');
                    resetButton(button, originalText);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(digifusionVars.strings.error, 'error');
                resetButton(button, originalText);
            });
        }

        /**
         * Activate plugin via AJAX
         */
        function activatePlugin(plugin, button, originalText) {
            const formData = new FormData();
            formData.append('action', 'digifusion_activate_plugin');
            formData.append('plugin', plugin);
            formData.append('nonce', digifusionVars.nonce);

            fetch(digifusionVars.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.data.message, 'success');
                    updateButton(button, data.data.status, plugin);
                } else {
                    showNotification(data.data || digifusionVars.strings.error, 'error');
                    resetButton(button, originalText);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(digifusionVars.strings.error, 'error');
                resetButton(button, originalText);
            });
        }

        /**
         * Update button based on plugin status
         */
        function updateButton(button, status, plugin) {
            button.disabled = false;
            button.classList.remove('button-primary', 'button-secondary');
            button.classList.add(status.button_class);
            button.querySelector('span').textContent = status.button_text;

            // Update data attributes based on status
            if (status.status === 'active') {
                button.dataset.action = 'learn_more';
                button.dataset.url = status.url;
            } else if (status.status === 'inactive') {
                button.dataset.action = 'activate';
                delete button.dataset.url;
            } else {
                button.dataset.action = 'install';
                delete button.dataset.url;
            }
        }

        /**
         * Reset button to original state
         */
        function resetButton(button, originalText) {
            button.disabled = false;
            button.querySelector('span').textContent = originalText;
        }

        /**
         * Show notification message
         */
        function showNotification(message, type) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.digifusion-notification');
            existingNotifications.forEach(notification => notification.remove());

            const notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notificationHTML = `
                <div class="notice ${notificationClass} is-dismissible digifusion-notification" style="margin: 1rem 0;">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `;

            // Insert notification before the admin content
            const adminContent = document.querySelector('.digifusion-admin-content');
            if (adminContent) {
                adminContent.insertAdjacentHTML('beforebegin', notificationHTML);
                
                // Handle dismiss button - get the notification that was just inserted
                const newNotification = adminContent.previousElementSibling;
                if (newNotification && newNotification.classList.contains('digifusion-notification')) {
                    const dismissButton = newNotification.querySelector('.notice-dismiss');
                    
                    dismissButton.addEventListener('click', function() {
                        fadeOut(newNotification, 300, function() {
                            newNotification.remove();
                        });
                    });

                    // Auto-dismiss success notifications after 5 seconds
                    if (type === 'success') {
                        setTimeout(function() {
                            if (newNotification && newNotification.parentNode) {
                                fadeOut(newNotification, 300, function() {
                                    newNotification.remove();
                                });
                            }
                        }, 5000);
                    }
                }
            }
        }

        /**
         * Fade out animation helper
         */
        function fadeOut(element, duration, callback) {
            element.style.transition = `opacity ${duration}ms`;
            element.style.opacity = '0';
            
            setTimeout(function() {
                if (callback) callback();
            }, duration);
        }

        /**
         * Check plugin status on page load
         */
        function checkPluginStatus() {
            const pluginButtons = document.querySelectorAll('.digifusion-plugin-action');
            
            pluginButtons.forEach(function(button) {
                const plugin = button.dataset.plugin;
                
                const formData = new FormData();
                formData.append('action', 'digifusion_get_plugin_status');
                formData.append('plugin', plugin);
                formData.append('nonce', digifusionVars.nonce);

                fetch(digifusionVars.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateButton(button, data.data.status, plugin);
                    }
                })
                .catch(error => {
                    console.error('Error checking plugin status:', error);
                });
            });
        }

        // Check plugin status on page load
        checkPluginStatus();
    });

})();