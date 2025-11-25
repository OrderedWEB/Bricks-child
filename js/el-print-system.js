/**
 * Engagement Letter Print System - Frontend JavaScript
 *
 * Handles PDF generation, preview, and download interactions.
 *
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  // Configuration from localized script
  var config = window.elPrintSystem || {
    ajaxUrl: "/wp-admin/admin-ajax.php",
    nonce: "",
    strings: {
      generating: "Generating PDF...",
      encrypting: "Securing document...",
      complete: "PDF ready!",
      error: "An error occurred. Please try again.",
    },
  };

  /**
   * Print System Controller
   */
  var PrintSystem = {
    /**
     * Current state
     */
    state: {
      generating: false,
      lastReference: null,
      lastFileId: null,
    },

    /**
     * Initialize
     */
    init: function () {
      this.bindEvents();
      this.initPreviewToggle();
    },

    /**
     * Bind event handlers
     */
    bindEvents: function () {
      var self = this;

      // Generate PDF button
      $(document).on("click", ".el-generate-print-pdf", function (e) {
        e.preventDefault();
        var $btn = $(this);
        var paperOnly =
          $btn.data("paper-only") === true ||
          $btn.data("paper-only") === "true";
        self.generatePDF(paperOnly, $btn);
      });

      // Download PDF button
      $(document).on("click", ".el-download-print-pdf", function (e) {
        e.preventDefault();
        self.downloadPDF();
      });

      // Preview PDF button
      $(document).on("click", ".el-preview-print-pdf", function (e) {
        e.preventDefault();
        var paperOnly =
          $(this).data("paper-only") === true ||
          $(this).data("paper-only") === "true";
        self.previewPDF(paperOnly);
      });

      // Create secure link button
      $(document).on("click", ".el-create-secure-link", function (e) {
        e.preventDefault();
        self.showSecureLinkModal();
      });

      // Paper-only toggle
      $(document).on("change", "#el-paper-only-toggle", function () {
        var paperOnly = $(this).is(":checked");
        self.previewPDF(paperOnly);
      });

      // Secure link form submission
      $(document).on("submit", "#el-secure-link-form", function (e) {
        e.preventDefault();
        self.createSecureLink($(this));
      });
    },

    /**
     * Initialize preview toggle
     */
    initPreviewToggle: function () {
      var $container = $("#el-print-preview-container");

      if ($container.length && $container.data("auto-load")) {
        this.previewPDF(false);
      }
    },

    /**
     * Generate PDF
     */
    generatePDF: function (paperOnly, $button) {
      var self = this;

      if (this.state.generating) {
        return;
      }

      this.state.generating = true;

      var $status = $button
        ? $button.siblings(".el-print-pdf-status")
        : $(".el-print-pdf-status");
      var originalText = $button ? $button.text() : "";

      // Update UI
      if ($button) {
        $button.prop("disabled", true).text(config.strings.generating);
      }

      $status
        .html(
          '<span class="spinner is-active" style="float:none;"></span> ' +
            config.strings.generating
        )
        .show();

      // Make request
      $.ajax({
        url: config.ajaxUrl,
        type: "POST",
        data: {
          action: "el_generate_print_pdf",
          nonce: config.nonce,
          paper_only: paperOnly ? "true" : "false",
          include_user_data: "true",
          encrypt: "true",
        },
        success: function (response) {
          self.state.generating = false;

          if (response.success) {
            self.state.lastReference = response.data.reference;
            self.state.lastFileId = response.data.file_id;

            var statusHtml =
              '<span style="color:green;">✓ ' +
              config.strings.complete +
              "</span>";
            statusHtml +=
              "<br><small>Reference: " + response.data.reference + "</small>";
            statusHtml +=
              "<br><small>Pages: " + response.data.total_pages + "</small>";

            if (response.data.encrypted) {
              statusHtml += "<br><small>🔒 Encrypted</small>";
            }

            statusHtml += '<div style="margin-top:10px;">';
            statusHtml +=
              '<button type="button" class="button el-download-print-pdf">Download PDF</button> ';
            statusHtml +=
              '<button type="button" class="button el-create-secure-link">Create Secure Link</button>';
            statusHtml += "</div>";

            $status.html(statusHtml);

            // Trigger event for other components
            $(document).trigger("el:pdfGenerated", [response.data]);
          } else {
            $status.html(
              '<span style="color:red;">✗ ' +
                (response.data.message || config.strings.error) +
                "</span>"
            );
          }

          if ($button) {
            $button.prop("disabled", false).text(originalText);
          }
        },
        error: function (xhr, status, error) {
          self.state.generating = false;
          $status.html(
            '<span style="color:red;">✗ ' + config.strings.error + "</span>"
          );

          if ($button) {
            $button.prop("disabled", false).text(originalText);
          }

          console.error("EL Print System Error:", error);
        },
      });
    },

    /**
     * Download PDF
     */
    downloadPDF: function () {
      if (!this.state.lastReference && !this.state.lastFileId) {
        alert("Please generate a PDF first.");
        return;
      }

      var url =
        config.ajaxUrl +
        "?" +
        $.param({
          action: "el_download_print_pdf",
          nonce: config.nonce,
          reference: this.state.lastReference || "",
          file_id: this.state.lastFileId || "",
        });

      window.location.href = url;
    },

    /**
     * Preview PDF
     */
    previewPDF: function (paperOnly) {
      var self = this;
      var $container = $("#el-print-preview-container");

      if (!$container.length) {
        console.warn("Preview container not found");
        return;
      }

      // Show loading
      $container.html(
        '<div style="text-align:center; padding:40px;"><span class="spinner is-active" style="float:none;"></span><p>Loading preview...</p></div>'
      );

      $.ajax({
        url: config.ajaxUrl,
        type: "POST",
        data: {
          action: "el_preview_print_pdf",
          nonce: config.nonce,
          paper_only: paperOnly ? "true" : "false",
          include_user_data: "true",
        },
        success: function (response) {
          if (response.success) {
            // Build preview HTML
            var html = "<style>" + response.data.css + "</style>";
            html +=
              '<div class="el-preview-info" style="background:#f0f0f0; padding:10px; margin-bottom:15px; border-radius:4px;">';
            html +=
              "<strong>Preview</strong> | Pages: " + response.data.total_pages;
            html += " | Reference: " + response.data.reference;
            html +=
              ' | <label><input type="checkbox" id="el-paper-only-toggle"' +
              (paperOnly ? " checked" : "") +
              "> Paper-only (with signature lines)</label>";
            html += "</div>";
            html +=
              '<div class="el-preview-content">' +
              response.data.html +
              "</div>";

            $container.html(html);

            // Store reference
            self.state.lastReference = response.data.reference;
          } else {
            $container.html(
              '<div style="color:red; padding:20px;">Error: ' +
                (response.data.message || "Failed to load preview") +
                "</div>"
            );
          }
        },
        error: function () {
          $container.html(
            '<div style="color:red; padding:20px;">Failed to load preview. Please try again.</div>'
          );
        },
      });
    },

    /**
     * Show secure link modal
     */
    showSecureLinkModal: function () {
      if (!this.state.lastFileId) {
        alert("Please generate an encrypted PDF first.");
        return;
      }

      // Create modal if doesn't exist
      var $modal = $("#el-secure-link-modal");

      if (!$modal.length) {
        var modalHtml =
          '<div id="el-secure-link-modal" class="el-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10000;">';
        modalHtml +=
          '<div class="el-modal-content" style="background:white; max-width:500px; margin:100px auto; padding:30px; border-radius:8px;">';
        modalHtml +=
          '<h3 style="margin-top:0;">Create Secure Viewing Link</h3>';
        modalHtml += '<form id="el-secure-link-form">';
        modalHtml +=
          '<p><label>Client Email:<br><input type="email" name="email" required style="width:100%; padding:8px;"></label></p>';
        modalHtml +=
          '<p><label>Expires in (days):<br><input type="number" name="expiry_days" value="14" min="1" max="90" style="width:100px; padding:8px;"></label></p>';
        modalHtml +=
          '<p><label><input type="checkbox" name="can_print" value="1" checked> Allow printing</label></p>';
        modalHtml +=
          '<p><label><input type="checkbox" name="can_download" value="1" checked> Allow download</label></p>';
        modalHtml +=
          '<div class="el-secure-link-result" style="display:none; background:#e8f5e9; padding:15px; margin:15px 0; border-radius:4px;"></div>';
        modalHtml +=
          '<p style="margin-bottom:0;"><button type="submit" class="button button-primary">Create Link</button> <button type="button" class="button el-modal-close">Cancel</button></p>';
        modalHtml += "</form>";
        modalHtml += "</div>";
        modalHtml += "</div>";

        $("body").append(modalHtml);
        $modal = $("#el-secure-link-modal");

        // Close modal handlers
        $modal.on("click", ".el-modal-close", function () {
          $modal.hide();
        });

        $modal.on("click", function (e) {
          if ($(e.target).is(".el-modal")) {
            $modal.hide();
          }
        });
      }

      // Reset and show
      $modal.find("form")[0].reset();
      $modal.find(".el-secure-link-result").hide().empty();
      $modal.show();
    },

    /**
     * Create secure link
     */
    createSecureLink: function ($form) {
      var self = this;
      var $result = $form.find(".el-secure-link-result");
      var $submit = $form.find('button[type="submit"]');

      $submit.prop("disabled", true).text("Creating...");

      $.ajax({
        url: config.ajaxUrl,
        type: "POST",
        data: {
          action: "el_create_secure_link",
          nonce: config.nonce,
          file_id: self.state.lastFileId,
          email: $form.find('[name="email"]').val(),
          expiry_days: $form.find('[name="expiry_days"]').val(),
          can_print: $form.find('[name="can_print"]').is(":checked")
            ? "true"
            : "false",
          can_download: $form.find('[name="can_download"]').is(":checked")
            ? "true"
            : "false",
        },
        success: function (response) {
          $submit.prop("disabled", false).text("Create Link");

          if (response.success) {
            var resultHtml = "<strong>✓ Secure link created!</strong><br>";
            resultHtml +=
              '<input type="text" value="' +
              response.data.url +
              '" readonly style="width:100%; margin:10px 0; padding:8px;" onclick="this.select();">';
            resultHtml +=
              "<br><small>Expires: " + response.data.expires_at + "</small>";
            resultHtml +=
              '<br><button type="button" class="button" onclick="navigator.clipboard.writeText(\'' +
              response.data.url +
              "'); alert('Copied!');\">Copy Link</button>";

            $result.html(resultHtml).show();
          } else {
            $result
              .html(
                '<span style="color:red;">Error: ' +
                  (response.data.message || "Failed to create link") +
                  "</span>"
              )
              .show();
          }
        },
        error: function () {
          $submit.prop("disabled", false).text("Create Link");
          $result
            .html(
              '<span style="color:red;">Failed to create link. Please try again.</span>'
            )
            .show();
        },
      });
    },
  };

  // Initialize on document ready
  $(document).ready(function () {
    PrintSystem.init();
  });

  // Expose for external use
  window.ELPrintSystem = PrintSystem;
})(jQuery);
