/**
 * Engagement Letter Wizard JavaScript
 */

(function ($) {
  "use strict";

  // Check if we're on the wizard page
  if (!$(".el-wizard-container").length && !$(".brxe-tabs-nested").length) {
    console.log("EL Wizard: Container not found");
    return;
  }

  console.log("🚀 EL Wizard 2.0: Initializing...");

  /**
   * =================================================================
   * GLOBAL WIZARD STATE MANAGEMENT
   * =================================================================
   */

  window.ELWizard = {
    currentTab: 1,
    maxTabs: 4,
    clientData: {},
    cartData: {},
    pdfData: null,

    // Initialize the wizard
    init: function () {
      this.setupEventHandlers();
      this.detectCurrentTab();
      this.addGlobalControls();
      console.log("✅ EL Wizard initialized");
    },

    // Detect which tab is currently active
    detectCurrentTab: function () {
      // Check Bricks tabs
      $(".brxe-tabs-pane").each(function (index) {
        if ($(this).hasClass("active") || $(this).is(":visible")) {
          ELWizard.currentTab = index + 1;
          return false;
        }
      });

      // Check custom tabs
      $(".el-tab-content").each(function (index) {
        if ($(this).hasClass("active")) {
          ELWizard.currentTab = index + 1;
          return false;
        }
      });

      console.log("Current tab detected:", this.currentTab);
    },

    // Add global controls (Start Again button)
    addGlobalControls: function () {
      // Remove existing button if present
      $(".el-global-controls").remove();

      // Add control panel
      var controlsHtml = `
                <div class="el-global-controls" style="position: fixed; top: 100px; right: 20px; z-index: 9999;">
                    <button class="el-start-again-btn" title="Start New Engagement Letter">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                        </svg>
                        <span>Start New Letter</span>
                    </button>
                </div>
            `;

      $("body").append(controlsHtml);
    },

    // Setup all event handlers
    setupEventHandlers: function () {
      var self = this;

      // Start Again button
      $(document).on("click", ".el-start-again-btn", function (e) {
        e.preventDefault();
        self.startAgain();
      });

      // Tab navigation clicks
      $(document).on(
        "click",
        ".el-tab-nav, .brxe-tab, .tab-title",
        function () {
          var tabNumber = $(this).data("tab") || $(this).index() + 1;
          self.switchToTab(tabNumber);
        }
      );

      // Form submission handler (Tab 1)
      $(document).on("submit", "#gform_1", function (e) {
        e.preventDefault();
        self.handleClientFormSubmit($(this));
      });

      // Template selection (Tab 2)
      $(document).on("click", ".el-select-template-btn", function (e) {
        e.preventDefault();
        self.handleTemplateSelection($(this));
      });

      // Cart quantity updates (Tab 3)
      $(document).on("change", ".el-qty-update", function () {
        self.updateCartQuantity($(this));
      });

      // Remove cart item (Tab 3)
      $(document).on("click", ".el-remove-item", function (e) {
        e.preventDefault();
        self.removeCartItem($(this).data("key"));
      });

      // Bundle component toggle
      $(document).on("change", ".el-bundle-component", function () {
        self.updateBundleComponent($(this));
      });

      // Preview PDF button
      $(document).on("click", "#el-preview-pdf-btn", function (e) {
        e.preventDefault();
        self.switchToTab(4);
      });

      // Download PDF button
      $(document).on("click", "#el-download-pdf", function (e) {
        e.preventDefault();
        self.downloadPDF();
      });

      // Send for signature button
      $(document).on("click", "#el-send-for-signature", function (e) {
        e.preventDefault();
        self.sendForSignature();
      });

      // Practice area filter
      $(document).on("change", "#practice-area-filter", function () {
        self.filterTemplates($(this).val());
      });

      // No Client button
      $(document).on("click", "#el-no-client-btn", function (e) {
        e.preventDefault();
        self.handleNoClientStart($(this));
      });
    },

    /**
     * =================================================================
     * TAB NAVIGATION
     * =================================================================
     */
    switchToTab: function (tabNumber) {
      console.log("🎯 switchToTab called:", tabNumber);

      // Save current tab position
      if (typeof el_ajax !== "undefined" && el_ajax.ajax_url) {
        $.ajax({
          url: el_ajax.ajax_url,
          type: "POST",
          data: {
            action: "el_save_current_tab",
            tab_number: tabNumber,
            nonce: el_ajax.nonce,
          },
        });
      }

      var $targetTab = $(
        `.el-tab-${tabNumber}, [data-tab="${tabNumber}"]`
      ).first();

      // Check if tab exists
      if (!$targetTab.length) {
        console.warn("Tab not found:", tabNumber);
        return;
      }

      // Click the tab
      $targetTab.trigger("click");

      // Update state
      var oldTab = this.currentTab;
      this.currentTab = tabNumber;

      // Update custom navigation
      $(".el-tab-nav").removeClass("active");
      $('.el-tab-nav[data-tab="' + tabNumber + '"]').addClass("active");

      // Update progress bar
      this.updateProgressBar(tabNumber);

      // Tab-specific initialization
      this.onTabActivated(tabNumber, oldTab);

      // Scroll to tab content if it exists
      var $tabContent = $(`.tab-pane:visible`).first();
      if ($tabContent.length && $tabContent.offset()) {
        $("html, body").animate(
          {
            scrollTop: $tabContent.offset().top - 100,
          },
          500
        );
      }

      console.log("✅ Switched to tab:", tabNumber);
    },

    updateProgressBar: function (step) {
      var progress = (step / this.maxTabs) * 100;
      $(".el-progress-bar").css("width", progress + "%");
      $(".el-progress-text").text("Step " + step + " of " + this.maxTabs);
    },

    onTabActivated: function (tabNumber, fromTab) {
      console.log("Tab " + tabNumber + " activated (from Tab " + fromTab + ")");

      switch (tabNumber) {
        case 1:
          this.initTab1();
          break;
        case 2:
          this.initTab2();
          break;
        case 3:
          this.initTab3();
          break;
        case 4:
          this.initTab4();
          break;
      }

      // Trigger custom event
      $(document).trigger("el-tab-switched", [tabNumber, fromTab]);
    },

    /**
     * =================================================================
     * TAB 1: CLIENT DETAILS
     * =================================================================
     */

    initTab1: function () {
      console.log("📋 Tab 1: Client Details Ready");
      // Client search functionality is handled separately
    },

    handleClientFormSubmit: function ($form) {
      var self = this;
      var $submitBtn = $form.find('.gform_button, input[type="submit"]');

      // Show loading state
      $submitBtn.val("Saving...").prop("disabled", true);

      // Collect form data
      var formData = $form.serialize();

      // Submit via AJAX
      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_save_client_ajax",
          form_data: formData,
          nonce: el_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            // Update button state
            $submitBtn.val("Saved!").addClass("el-btn-success");

            // Show success message
            self.showNotification(
              "Client details saved successfully",
              "success"
            );

            // Auto-switch to Tab 2
            setTimeout(function () {
              self.switchToTab(2);
            }, 500);
          } else {
            self.showNotification(
              "Error: " + (response.data.message || "Could not save details"),
              "error"
            );
            $submitBtn.val("Save Client Details").prop("disabled", false);
          }
        },
        error: function (xhr, status, error) {
          self.showNotification("Connection error. Please try again.", "error");
          $submitBtn.val("Save Client Details").prop("disabled", false);
        },
      });
    },

    handleNoClientStart: function ($button) {
      var self = this;

      console.log("🔵 handleNoClientStart called");

      if (
        !confirm(
          "Start without client details?\n\nYou can add client information later before finalizing."
        )
      ) {
        console.log("❌ User cancelled");
        return;
      }

      var originalHtml = $button.html();
      $button.html("<span>Processing...</span>").prop("disabled", true);

      console.log("🟡 Sending AJAX request...");

      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_start_no_client",
          nonce: el_ajax.nonce,
        },
        success: function (response) {
          console.log("🟢 AJAX SUCCESS:", response);

          if (response.success) {
            self.clientData = {
              client_id: 0,
              client_name: "No Client",
              no_client_mode: true,
            };

            console.log("✅ Client data stored:", self.clientData);
            self.showNotification("✓ No client mode activated", "success");

            // Switch to Tab 2
            setTimeout(function () {
              self.switchToTab(2);
            }, 500);
          } else {
            console.log("❌ AJAX failed:", response.data.message);
            self.showNotification(
              "Error: " + (response.data.message || "Could not start"),
              "error"
            );
            $button.html(originalHtml).prop("disabled", false);
          }
        },
        error: function (xhr, status, error) {
          console.log("🔴 AJAX ERROR:", error);
          self.showNotification("Connection error. Please try again.", "error");
          $button.html(originalHtml).prop("disabled", false);
        },
      });
    },

    /**
     * =================================================================
     * TAB 2: TEMPLATE SELECTION
     * =================================================================
     */

    initTab2: function () {
      console.log("📄 Tab 2: Template Selection Ready");
      this.loadTemplates();
    },

    loadTemplates: function () {
      $(".el-loading").fadeOut();

      // Initialize template hover effects
      $(".el-template-item")
        .on("mouseenter", function () {
          $(this).find(".el-template-description").slideDown(200);
        })
        .on("mouseleave", function () {
          $(this).find(".el-template-description").slideUp(200);
        });
    },

    filterTemplates: function (practiceArea) {
      if (!practiceArea) {
        $(".el-template-item").show();
        $("#el-no-filtered-results").hide();
      } else {
        $(".el-template-item").hide();
        $(
          '.el-template-item[data-practice-area="' + practiceArea + '"]'
        ).show();

        var visibleCount = $(".el-template-item:visible").length;
        $("#el-no-filtered-results").toggle(visibleCount === 0);
      }
    },

    handleTemplateSelection: function ($button) {
      var self = this;
      var productId = $button.data("product-id");
      var productName = $button.data("product-name") || "Template";

      if (!productId) {
        self.showNotification("Invalid product selection", "error");
        return;
      }

      // Show loading state
      var originalText = $button.text();
      $button.text("Adding to cart...").prop("disabled", true);

      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_setup_engagement_cart",
          product_id: productId,
          nonce: el_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            // Update button state
            $button.text("✓ Added").addClass("el-btn-success");

            // Show success message
            self.showNotification(
              "✓ " + productName + " added to engagement letter",
              "success"
            );

            // Trigger WooCommerce cart refresh
            $(document.body).trigger("wc_fragment_refresh");
            $(document.body).trigger("added_to_cart");

            // Auto-switch to Tab 3
            setTimeout(function () {
              self.switchToTab(3);
            }, 500);
          } else {
            self.showNotification(
              "Error: " + (response.data.message || "Could not add to cart"),
              "error"
            );
            $button.text(originalText).prop("disabled", false);
          }
        },
        error: function (xhr, status, error) {
          self.showNotification("Connection error. Please try again.", "error");
          $button.text(originalText).prop("disabled", false);
        },
      });
    },

    /**
     * =================================================================
     * TAB 3: CART CUSTOMIZATION
     * =================================================================
     */

    initTab3: function () {
      console.log("⚙️ Tab 3: Service Configuration Ready");
      this.refreshCartDisplay();
    },

    refreshCartDisplay: function () {
      $(document.body).trigger("wc_fragment_refresh");

      // Calculate and display totals
      this.calculateCartTotals();
    },

    calculateCartTotals: function () {
      var totalEngagement = 0;
      var totalExpected = 0;

      $(".el-cart-item").each(function () {
        var engagement = parseFloat(
          $(this).find(".el-engagement-amount").data("amount") || 0
        );
        var expected = parseFloat(
          $(this).find(".el-expected-amount").data("amount") || 0
        );
        totalEngagement += engagement;
        totalExpected += expected;
      });

      $(".el-total-engagement").text(this.formatPrice(totalEngagement));
      $(".el-total-expected").text(this.formatPrice(totalExpected));
    },

    updateCartQuantity: function ($input) {
      var self = this;
      var cartKey = $input.data("key");
      var quantity = parseInt($input.val()) || 0;

      if (quantity < 0) {
        $input.val(0);
        return;
      }

      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_update_cart_quantity",
          cart_item_key: cartKey,
          quantity: quantity,
          nonce: el_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            if (quantity === 0) {
              $input.closest(".el-cart-item").fadeOut(function () {
                $(this).remove();
                self.calculateCartTotals();
              });
            } else {
              self.calculateCartTotals();
            }

            self.showNotification("Cart updated", "success");
          } else {
            self.showNotification("Error updating cart", "error");
          }
        },
      });
    },

    removeCartItem: function (cartKey) {
      if (confirm("Remove this item from the engagement letter?")) {
        $('.el-qty-update[data-key="' + cartKey + '"]')
          .val(0)
          .trigger("change");
      }
    },

    updateBundleComponent: function ($checkbox) {
      var self = this;
      var bundleId = $checkbox.data("bundle-id");
      var itemId = $checkbox.data("item-id");
      var isChecked = $checkbox.is(":checked");

      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_update_bundle_selection",
          bundle_id: bundleId,
          item_id: itemId,
          selected: isChecked,
          nonce: el_ajax.nonce,
        },
        success: function (response) {
          if (response.success) {
            self.calculateCartTotals();
            self.showNotification("Selection updated", "success");
          } else {
            self.showNotification("Error updating selection", "error");
            $checkbox.prop("checked", !isChecked);
          }
        },
      });
    },

    /**
     * =================================================================
     * TAB 4: PDF PREVIEW & GENERATION
     * =================================================================
     */

    initTab4: function () {
      console.log("🎨 Tab 4: PDF Preview Ready");
      this.generatePDFPreview();
    },

    generatePDFPreview: function () {
      var self = this;
      var $container = $("#el-pdf-preview-container");

      if (!$container.length) {
        console.error("PDF preview container not found");
        return;
      }

      // Show loading spinner
      $container.html(`
                <div class="el-pdf-loading">
                    <div class="el-spinner"></div>
                    <p style="margin-top: 20px; color: #6b7280;">Generating your engagement letter...</p>
                </div>
            `);

      // Request PDF preview generation
      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_generate_pdf_preview",
          nonce: el_ajax.nonce,
        },
        success: function (response) {
          if (response.success && response.data.html) {
            // Insert HTML directly (no iframe)
            $container.hide().html(response.data.html).fadeIn(500);

            // Store PDF data
            self.pdfData = response.data;

            // Enable action buttons
            $("#el-download-pdf, #el-send-for-signature").prop(
              "disabled",
              false
            );

            self.showNotification(
              "✓ Preview generated successfully",
              "success"
            );
          } else {
            $container.html(`
                            <div class="el-pdf-error" style="padding: 40px; text-align: center;">
                                <p style="color: #dc2626; font-size: 18px;">⚠️ Could not generate preview</p>
                                <p style="color: #6b7280; margin-top: 10px;">
                                    ${
                                      response.data.message ||
                                      "Please try again or contact support"
                                    }
                                </p>
                                <button onclick="ELWizard.generatePDFPreview()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer;">
                                    Try Again
                                </button>
                            </div>
                        `);
          }
        },
        error: function (xhr, status, error) {
          $container.html(`
                        <div class="el-pdf-error" style="padding: 40px; text-align: center;">
                            <p style="color: #dc2626; font-size: 18px;">⚠️ Connection error</p>
                            <p style="color: #6b7280; margin-top: 10px;">Please check your connection and try again</p>
                            <button onclick="ELWizard.generatePDFPreview()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer;">
                                Try Again
                            </button>
                        </div>
                    `);
        },
      });
    },

    downloadPDF: function () {
      if (this.pdfData && this.pdfData.pdf_url) {
        window.open(this.pdfData.pdf_url, "_blank");
      } else {
        this.showNotification("Please wait for preview to generate", "warning");
      }
    },

    sendForSignature: function () {
      var self = this;
      var $button = $("#el-send-for-signature");

      $button.text("Sending...").prop("disabled", true);

      // Simulate sending (replace with actual implementation)
      setTimeout(function () {
        self.showNotification(
          "✓ Engagement letter sent for signature!",
          "success"
        );
        $button.text("✓ Sent").addClass("el-btn-success");
      }, 1500);
    },

    /**
     * =================================================================
     * UTILITY FUNCTIONS
     * =================================================================
     */

    startAgain: function () {
      var self = this;

      if (
        !confirm(
          "Are you sure you want to start a new engagement letter? This will clear all current data."
        )
      ) {
        return;
      }

      // Clear session data
      $.ajax({
        url: el_ajax.ajax_url,
        type: "POST",
        data: {
          action: "el_clear_session",
          nonce: el_ajax.nonce,
        },
        success: function () {
          // Clear cart
          $.ajax({
            url: el_ajax.ajax_url,
            type: "POST",
            data: {
              action: "el_clear_cart",
              nonce: el_ajax.nonce,
            },
            success: function () {
              // Reset form
              if ($("#gform_1").length) {
                $("#gform_1")[0].reset();
              }

              // Clear stored data
              self.clientData = {};
              self.cartData = {};
              self.pdfData = null;

              // Switch to Tab 1
              self.switchToTab(1);

              self.showNotification(
                "Ready to start a new engagement letter",
                "info"
              );
            },
          });
        },
      });
    },

    showNotification: function (message, type) {
      type = type || "info";

      // Remove existing notifications
      $(".el-notification").remove();

      var typeStyles = {
        success: "background: #10b981;",
        error: "background: #dc2626;",
        warning: "background: #f59e0b;",
        info: "background: #3b82f6;",
      };

      var $notification = $(`
                <div class="el-notification" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 16px 24px;
                    color: white;
                    border-radius: 8px;
                    z-index: 10000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    font-size: 15px;
                    font-weight: 500;
                    display: none;
                    ${typeStyles[type]}
                ">
                    ${message}
                    <span style="margin-left: 20px; cursor: pointer; font-size: 20px;" onclick="jQuery(this).parent().fadeOut()">×</span>
                </div>
            `);

      $("body").append($notification);
      $notification.fadeIn(300);

      setTimeout(function () {
        $notification.fadeOut(300, function () {
          $(this).remove();
        });
      }, 5000);
    },

    formatPrice: function (price) {
      return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
      }).format(price);
    },
  };

  // Initialize on document ready
  $(document).ready(function () {
    window.ELWizard.init();
  });

  // Make functions globally accessible
  window.switchToTab = function (tab) {
    return window.ELWizard.switchToTab(tab);
  };

  window.showNotification = function (message, type) {
    return window.ELWizard.showNotification(message, type);
  };
})(jQuery);

// Wait for page to fully load
window.addEventListener("load", function () {
  // Wait a tiny bit more
  setTimeout(function () {
    // Get ALL elements with class="dotty"
    var elements = document.querySelectorAll(".dotty");
    var dotString = "..............."; // 15 dots

    console.log("Found " + elements.length + ' elements with class="dotty"');

    // Process each one
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];

      // Get the text content
      var text = element.textContent || element.innerText || "";

      // Remove whitespace
      var cleaned = text.replace(/\s/g, "");

      console.log(
        "Element " +
          i +
          ': "' +
          text +
          '" -> cleaned: "' +
          cleaned +
          '" -> empty: ' +
          (cleaned === "")
      );

      // If empty, add dots
      if (cleaned === "") {
        element.textContent = dotString;
        element.style.color = "#999";
        element.style.fontFamily = "monospace";
        console.log("✅ Added dots to element " + i);
      } else {
        console.log("ℹ️ Element " + i + " has content, skipping");
      }
    }

    // Also check for id="dotty"
    var idElement = document.getElementById("dotty");
    if (idElement) {
      var idText = (idElement.textContent || idElement.innerText || "").replace(
        /\s/g,
        ""
      );
      if (idText === "") {
        idElement.textContent = dotString;
        idElement.style.color = "#999";
        idElement.style.fontFamily = "monospace";
        console.log('✅ Added dots to id="dotty"');
      }
    }

    console.log("✅ DottyFields complete!");
  }, 500); // Wait 500ms after page loads
});
