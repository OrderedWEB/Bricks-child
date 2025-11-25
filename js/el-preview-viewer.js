/**
 * Engagement Letter Preview Viewer - Frontend JavaScript
 *
 * Handles preview generation, page navigation, and print/download actions
 * for the lawyer preview viewer.
 *
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  /**
   * Preview Viewer Controller
   */
  var PreviewViewer = {
    /**
     * Current state
     */
    state: {
      mode: "paginated",
      currentPage: 1,
      totalPages: 1,
      reference: "",
      loading: false,
      generated: false,
    },

    /**
     * DOM elements
     */
    $container: null,
    $toolbar: null,
    $statusBar: null,
    $content: null,
    $nav: null,

    /**
     * Initialize
     */
    init: function () {
      this.$container = $(".el-preview-viewer");

      if (!this.$container.length) {
        return;
      }

      // Get elements
      this.$toolbar = this.$container.find(".el-preview-toolbar");
      this.$statusBar = this.$container.find(".el-preview-status");
      this.$content = this.$container.find("#elPreviewContainer");
      this.$nav = this.$container.find("#elPreviewNav");

      // Get initial state
      this.state.mode = this.$container.data("mode");

      // Bind events
      this.bindEvents();

      // Auto-load if configured
      if (this.$container.data("auto-load") === 1) {
        this.generate();
      }
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      var self = this;

      // Generate button
      $("#elPreviewGenerate").on("click", function () {
        self.generate();
      });

      // Mode toggle
      $('input[name="preview_mode"]').on("change", function () {
        self.state.mode = $(this).val();
        if (self.state.generated) {
          self.generate(); // Regenerate with new mode
        }
      });

      // Include user data toggle
      $("#elIncludeUserData").on("change", function () {
        if (self.state.generated) {
          self.generate(); // Regenerate with new setting
        }
      });

      // Print/download buttons
      $("#elPreviewPrintView").on("click", function () {
        self.printView();
      });

      $("#elPreviewDownload").on("click", function () {
        self.download();
      });

      $("#elPreviewPrint").on("click", function () {
        self.print();
      });

      // Security info button
      $("#elSecurityInfo").on("click", function () {
        self.showSecurityModal();
      });

      $("#elSecurityModalClose, #elSecurityModalOk").on("click", function () {
        self.hideSecurityModal();
      });

      // Close modal on overlay click
      $(document).on("click", ".security-modal-overlay", function () {
        self.hideSecurityModal();
      });

      // ESC key to close modal
      $(document).on("keydown", function (e) {
        if (e.key === "Escape" && $("#elSecurityModal").is(":visible")) {
          self.hideSecurityModal();
        }
      });

      // Navigation
      $("#elNavFirst").on("click", function () {
        self.goToPage(1);
      });

      $("#elNavPrev").on("click", function () {
        self.goToPage(self.state.currentPage - 1);
      });

      $("#elNavNext").on("click", function () {
        self.goToPage(self.state.currentPage + 1);
      });

      $("#elNavLast").on("click", function () {
        self.goToPage(self.state.totalPages);
      });

      $("#elPageInput").on("change", function () {
        var page = parseInt($(this).val());
        self.goToPage(page);
      });

      // Keyboard shortcuts
      $(document).on("keydown", function (e) {
        if (!self.state.generated || self.state.mode !== "paginated") {
          return;
        }

        // Only handle if preview is in view
        if (!self.$container.is(":visible")) {
          return;
        }

        switch (e.key) {
          case "ArrowLeft":
          case "PageUp":
            e.preventDefault();
            self.goToPage(self.state.currentPage - 1);
            break;
          case "ArrowRight":
          case "PageDown":
            e.preventDefault();
            self.goToPage(self.state.currentPage + 1);
            break;
          case "Home":
            e.preventDefault();
            self.goToPage(1);
            break;
          case "End":
            e.preventDefault();
            self.goToPage(self.state.totalPages);
            break;
        }
      });
    },

    /**
     * Generate preview
     */
    generate: function () {
      var self = this;

      if (this.state.loading) {
        return;
      }

      this.state.loading = true;
      this.state.generated = false;

      // Show loading state
      this.$content.html(
        '<div class="preview-loading">' +
          '<div class="preview-spinner"></div>' +
          "<p>Generating preview...</p>" +
          "</div>"
      );

      this.showStatus("Generating PDF document...", "info");
      this.disableButtons();

      // Get options
      var includeUserData = $("#elIncludeUserData").is(":checked");
      var paperOnly = false; // Could be from a checkbox

      // Make request
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "el_preview_generate",
          nonce: $("#elPreviewNonce").val(),
          mode: self.state.mode,
          include_user_data: includeUserData ? "true" : "false",
          paper_only: paperOnly ? "true" : "false",
        },
        success: function (response) {
          self.state.loading = false;

          if (response.success) {
            self.state.totalPages = response.data.total_pages;
            self.state.reference = response.data.reference;
            self.state.generated = true;

            $("#elPreviewReference").val(response.data.reference);
            $("#elTotalPages").text(response.data.total_pages);

            // Show success
            self.showStatus(
              "Preview generated successfully. " +
                "Reference: " +
                response.data.reference +
                " | " +
                "Pages: " +
                response.data.total_pages,
              "success"
            );

            // Render content based on mode
            if (self.state.mode === "paginated") {
              self.renderPaginated();
            } else {
              self.renderContinuous();
            }

            self.enableButtons();
          } else {
            self.showStatus(
              "Error: " +
                (response.data.message || "Failed to generate preview"),
              "error"
            );
            self.$content.html(
              '<div class="preview-empty">' +
                '<p style="color: #dc2626;">Failed to generate preview</p>' +
                "</div>"
            );
          }
        },
        error: function () {
          self.state.loading = false;
          self.showStatus("An error occurred. Please try again.", "error");
          self.$content.html(
            '<div class="preview-empty">' +
              '<p style="color: #dc2626;">An error occurred</p>' +
              "</div>"
          );
        },
      });
    },

    /**
     * Render paginated view
     */
    renderPaginated: function () {
      this.$nav.show();
      this.$nav.find("input, button").prop("disabled", false);
      $("#elPageInput").attr("max", this.state.totalPages);

      this.goToPage(1);
    },

    /**
     * Render continuous view
     */
    renderContinuous: function () {
      var self = this;

      this.$nav.hide();

      this.$content.html(
        '<div class="preview-loading">' +
          '<div class="preview-spinner"></div>' +
          "<p>Rendering all pages...</p>" +
          "</div>"
      );

      var html = '<div class="preview-continuous">';
      var pagesRendered = 0;

      // Render all pages with page break indicators
      function renderNextPage(pageNum) {
        self.loadPage(pageNum, function (imageData) {
          if (pageNum > 1) {
            html += '<div class="page-break-indicator"></div>';
          }
          html += '<img src="' + imageData + '" alt="Page ' + pageNum + '">';

          pagesRendered++;

          if (pagesRendered < self.state.totalPages) {
            renderNextPage(pagesRendered + 1);
          } else {
            html += "</div>";
            self.$content.html(html);
            self.$content.scrollTop(0);
          }
        });
      }

      renderNextPage(1);
    },

    /**
     * Go to specific page (paginated mode)
     */
    goToPage: function (pageNum) {
      var self = this;

      if (!this.state.generated || this.state.mode !== "paginated") {
        return;
      }

      pageNum = parseInt(pageNum);

      if (pageNum < 1 || pageNum > this.state.totalPages) {
        return;
      }

      this.state.currentPage = pageNum;

      // Show loading
      this.$content.html(
        '<div class="preview-loading">' +
          '<div class="preview-spinner"></div>' +
          "<p>Loading page " +
          pageNum +
          "...</p>" +
          "</div>"
      );

      // Load page image
      this.loadPage(pageNum, function (imageData) {
        self.$content.html(
          '<div class="preview-page">' +
            '<img src="' +
            imageData +
            '" alt="Page ' +
            pageNum +
            '">' +
            "</div>"
        );

        // Update navigation
        self.updateNavigation();

        // Scroll to top
        self.$content.scrollTop(0);
      });
    },

    /**
     * Load page image via AJAX
     */
    loadPage: function (pageNum, callback) {
      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: {
          action: "el_preview_render_page",
          nonce: $("#elPreviewNonce").val(),
          page: pageNum,
        },
        success: function (response) {
          if (response.success) {
            callback(response.data.image);
          } else {
            console.error("Failed to load page:", response.data.message);
          }
        },
        error: function () {
          console.error("AJAX error loading page");
        },
      });
    },

    /**
     * Update navigation state
     */
    updateNavigation: function () {
      $("#elPageInput").val(this.state.currentPage);

      $("#elNavFirst, #elNavPrev").prop(
        "disabled",
        this.state.currentPage <= 1
      );
      $("#elNavNext, #elNavLast").prop(
        "disabled",
        this.state.currentPage >= this.state.totalPages
      );
    },

    /**
     * Print view (open PDF in new tab)
     */
    printView: function () {
      var url =
        ajaxurl +
        "?" +
        $.param({
          action: "el_preview_print",
          nonce: $("#elPreviewNonce").val(),
        });

      window.open(url, "_blank");
    },

    /**
     * Download PDF
     */
    download: function () {
      var url =
        ajaxurl +
        "?" +
        $.param({
          action: "el_preview_download",
          nonce: $("#elPreviewNonce").val(),
        });

      window.location.href = url;
    },

    /**
     * Print PDF (browser print dialog)
     */
    print: function () {
      var url =
        ajaxurl +
        "?" +
        $.param({
          action: "el_preview_print",
          nonce: $("#elPreviewNonce").val(),
        });

      var printWindow = window.open(url, "_blank");
      printWindow.onload = function () {
        printWindow.print();
      };
    },

    /**
     * Show status message
     */
    showStatus: function (message, type) {
      type = type || "info";

      this.$statusBar
        .removeClass("success error")
        .addClass(type)
        .find(".status-content")
        .html(message);

      this.$statusBar.show();

      // Auto-hide success messages after 5 seconds
      if (type === "success") {
        setTimeout(
          function () {
            this.$statusBar.fadeOut();
          }.bind(this),
          5000
        );
      }
    },

    /**
     * Enable action buttons
     */
    enableButtons: function () {
      $("#elPreviewPrintView, #elPreviewDownload, #elPreviewPrint").prop(
        "disabled",
        false
      );
    },

    /**
     * Disable action buttons
     */
    disableButtons: function () {
      $("#elPreviewPrintView, #elPreviewDownload, #elPreviewPrint").prop(
        "disabled",
        true
      );
    },

    /**
     * Show security modal
     */
    showSecurityModal: function () {
      $("#elSecurityModal").fadeIn(200);
    },

    /**
     * Hide security modal
     */
    hideSecurityModal: function () {
      $("#elSecurityModal").fadeOut(200);
    },
  };

  /**
   * Initialize on document ready
   */
  $(document).ready(function () {
    PreviewViewer.init();
  });

  /**
   * Expose for external use
   */
  window.ELPreviewViewer = PreviewViewer;
})(jQuery);
