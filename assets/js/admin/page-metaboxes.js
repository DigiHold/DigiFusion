(() => {
  // resources/js/admin/page-metaboxes.js
  (function() {
    "use strict";
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", initPageSettings);
    } else {
      initPageSettings();
    }
    function initPageSettings() {
      initPanelToggles();
      initDisableToggles();
      initLogoUpload();
      initColorPickers();
      initCustomToggles();
    }
    function initPanelToggles() {
      const panelTitles = document.querySelectorAll(".digifusion-panel-title");
      panelTitles.forEach(function(title) {
        title.addEventListener("click", function() {
          const panel = this.closest(".digifusion-panel");
          const content = panel.querySelector(".digifusion-panel-content");
          const toggle = this.querySelector(".digifusion-panel-toggle");
          if (content.style.display === "none" || !content.style.display) {
            slideDown(content, 300);
            toggle.classList.remove("dashicons-arrow-down");
            toggle.classList.add("dashicons-arrow-up");
          } else {
            slideUp(content, 300);
            toggle.classList.remove("dashicons-arrow-up");
            toggle.classList.add("dashicons-arrow-down");
          }
        });
      });
      const disablePanel = document.querySelector(".digifusion-disable-panel");
      if (disablePanel) {
        const content = disablePanel.querySelector(".digifusion-panel-content");
        const toggle = disablePanel.querySelector(".digifusion-panel-toggle");
        if (content && toggle) {
          content.style.display = "block";
          toggle.classList.remove("dashicons-arrow-down");
          toggle.classList.add("dashicons-arrow-up");
        }
      }
    }
    function initDisableToggles() {
      const headerDisableToggle = document.querySelector('input[name="digifusion_disable_header"]');
      if (headerDisableToggle) {
        headerDisableToggle.addEventListener("change", function() {
          const headerPanel = document.querySelector(".digifusion-header-panel");
          if (headerPanel) {
            headerPanel.style.display = this.checked ? "none" : "block";
          }
        });
      }
      const pageHeaderDisableToggle = document.querySelector('input[name="digifusion_disable_page_header"]');
      if (pageHeaderDisableToggle) {
        pageHeaderDisableToggle.addEventListener("change", function() {
          const pageHeaderPanel = document.querySelector(".digifusion-page-header-panel");
          if (pageHeaderPanel) {
            pageHeaderPanel.style.display = this.checked ? "none" : "block";
          }
        });
      }
    }
    function initLogoUpload() {
      let logoUploader;
      document.addEventListener("click", function(e) {
        if (e.target.classList.contains("digifusion-upload-logo")) {
          e.preventDefault();
          if (logoUploader) {
            logoUploader.open();
            return;
          }
          logoUploader = wp.media({
            title: digifusionPageMetaboxes.selectImage,
            button: {
              text: digifusionPageMetaboxes.useImage
            },
            multiple: false,
            library: {
              type: "image"
            }
          });
          logoUploader.on("select", function() {
            const attachment = logoUploader.state().get("selection").first().toJSON();
            updateLogoDisplay(attachment);
          });
          logoUploader.open();
        }
      });
      document.addEventListener("click", function(e) {
        if (e.target.classList.contains("digifusion-remove-logo")) {
          e.preventDefault();
          if (confirm(digifusionPageMetaboxes.removeConfirm)) {
            removeLogo();
          }
        }
      });
    }
    function updateLogoDisplay(attachment) {
      const logoInput = document.getElementById("digifusion_custom_logo");
      const logoPreview = document.querySelector(".digifusion-logo-preview");
      const logoActions = document.querySelector(".digifusion-logo-actions");
      const uploadBtn = document.querySelector(".digifusion-upload-logo");
      if (!logoInput || !logoPreview || !logoActions || !uploadBtn) {
        return;
      }
      logoInput.value = attachment.id;
      const thumbnailUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
      logoPreview.innerHTML = '<img src="' + thumbnailUrl + '" alt="Custom Logo" style="max-width: 150px; height: auto;" />';
      uploadBtn.textContent = digifusionPageMetaboxes.changeLogo;
      if (!logoActions.querySelector(".digifusion-remove-logo")) {
        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "button digifusion-remove-logo";
        removeBtn.textContent = digifusionPageMetaboxes.remove;
        logoActions.appendChild(removeBtn);
      }
    }
    function removeLogo() {
      const logoInput = document.getElementById("digifusion_custom_logo");
      const logoPreview = document.querySelector(".digifusion-logo-preview");
      const uploadBtn = document.querySelector(".digifusion-upload-logo");
      const removeBtn = document.querySelector(".digifusion-remove-logo");
      if (!logoInput || !logoPreview || !uploadBtn) {
        return;
      }
      logoInput.value = "";
      logoPreview.innerHTML = "";
      uploadBtn.textContent = digifusionPageMetaboxes.uploadLogo;
      if (removeBtn) {
        removeBtn.remove();
      }
    }
    function initColorPickers() {
      const colorInputs = document.querySelectorAll(".digifusion-color-picker");
      colorInputs.forEach(function(input) {
        if (typeof jQuery !== "undefined" && jQuery.fn.wpColorPicker) {
          jQuery(input).wpColorPicker({
            defaultColor: false,
            hide: true,
            palettes: true,
            change: function(event, ui) {
              jQuery(this).val(ui.color.toString()).trigger("change");
            },
            clear: function() {
              jQuery(this).val("").trigger("change");
            }
          });
        }
      });
    }
    function initCustomToggles() {
      const toggleInputs = document.querySelectorAll('.digifusion-toggle-switch input[type="checkbox"]');
      toggleInputs.forEach(function(input) {
        input.addEventListener("change", function() {
          const toggle = this.closest(".digifusion-toggle-switch");
          if (this.checked) {
            toggle.classList.add("active");
          } else {
            toggle.classList.remove("active");
          }
        });
        if (input.checked) {
          const toggle = input.closest(".digifusion-toggle-switch");
          if (toggle) {
            toggle.classList.add("active");
          }
        }
      });
    }
    function updatePanelVisibility() {
      const headerDisabled = document.querySelector('input[name="digifusion_disable_header"]');
      const pageHeaderDisabled = document.querySelector('input[name="digifusion_disable_page_header"]');
      const headerPanel = document.querySelector(".digifusion-header-panel");
      const pageHeaderPanel = document.querySelector(".digifusion-page-header-panel");
      if (headerDisabled && headerPanel) {
        headerPanel.style.display = headerDisabled.checked ? "none" : "block";
      }
      if (pageHeaderDisabled && pageHeaderPanel) {
        pageHeaderPanel.style.display = pageHeaderDisabled.checked ? "none" : "block";
      }
    }
    function slideDown(element, duration) {
      element.style.display = "block";
      element.style.height = "0";
      element.style.overflow = "hidden";
      element.style.transition = `height ${duration}ms ease`;
      element.offsetHeight;
      element.style.height = element.scrollHeight + "px";
      setTimeout(function() {
        element.style.height = "";
        element.style.overflow = "";
        element.style.transition = "";
      }, duration);
    }
    function slideUp(element, duration) {
      element.style.height = element.scrollHeight + "px";
      element.style.overflow = "hidden";
      element.style.transition = `height ${duration}ms ease`;
      element.offsetHeight;
      element.style.height = "0";
      setTimeout(function() {
        element.style.display = "none";
        element.style.height = "";
        element.style.overflow = "";
        element.style.transition = "";
      }, duration);
    }
    document.addEventListener("DOMContentLoaded", function() {
      updatePanelVisibility();
    });
  })();
})();
