/**
 * Enhanced Print Editor JavaScript
 * Drop-in replacement maintaining all existing functionality
 *
 * @package Starne_Consulting_EL
 * @since 1.0.0
 */

(function ($) {
  "use strict";

  window.ELPrintEditorEnhanced = {
    // State management
    state: {
      editorLoaded: false,
      currentReference: "",
      canEdit: false,
      isPaperOnly: false,
      totalPages: 0,
      isDirty: false,
      viewMode: "edit", // 'edit' or 'preview'
    },

    // Initialize
    init: function () {
      // Get configuration from localized script
      this.state.canEdit =
        el_print_config.can_edit === "1" || el_print_config.can_edit === true;
      this.state.isPaperOnly = el_print_config.paper_only_default === "1";

      // Bind events
      this.bindEvents();

      // Initialize UI state
      this.initializeUI();
    },

    // Bind all event handlers
    bindEvents: function () {
      var self = this;

      // Load document button
      $("#el-load-print-content").on("click", function () {
        self.loadDocument();
      });

      // Save button (if can edit)
      if (this.state.canEdit) {
        $("#el-save-print-content").on("click", function () {
          self.saveDocument();
        });

        // Format buttons
        $(".el-format-btn").on("click", function (e) {
          e.preventDefault();
          var command = $(this).data("command");
          document.execCommand(command, false, null);
          $("#el-print-editor-content").focus();
        });

        // Track changes in contenteditable
        $("#el-print-editor-content").on("input", function () {
          self.state.isDirty = true;
          self.updateStatus("Editing...", "info");
        });

        // Prevent navigation if unsaved changes
        $(window).on("beforeunload", function () {
          if (self.state.isDirty) {
            return "You have unsaved changes. Are you sure you want to leave?";
          }
        });
      }

      // Paper-only toggle
      $("#el-paper-only-toggle").on("change", function () {
        self.togglePaperOnly($(this).prop("checked"));
      });

      // Copy share link
      $("#el-copy-link").on("click", function () {
        self.copyShareLink();
      });

      // View mode toggle
      $(".el-view-btn").on("click", function () {
        var mode = $(this).data("view");
        self.switchViewMode(mode);
      });

      // Print preview actions
      $(".el-close-preview").on("click", function () {
        self.closePreview();
      });

      $("#el-print-now").on("click", function () {
        self.printDocument();
      });

      $("#el-download-pdf-preview, #el-download-final-pdf").on(
        "click",
        function () {
          self.downloadPDF();
        }
      );

      // Keyboard shortcuts
      if (this.state.canEdit) {
        $(document).on("keydown", function (e) {
          // Ctrl/Cmd + S to save
          if ((e.ctrlKey || e.metaKey) && e.key === "s") {
            e.preventDefault();
            if (self.state.editorLoaded && self.state.isDirty) {
              self.saveDocument();
            }
          }

          // Ctrl/Cmd + P to print
          if ((e.ctrlKey || e.metaKey) && e.key === "p") {
            e.preventDefault();
            if (self.state.editorLoaded) {
              self.printDocument();
            }
          }
        });
      }
    },

    // Initialize UI
    initializeUI: function () {
      // Set initial paper-only state
      $("#el-paper-only-toggle").prop("checked", this.state.isPaperOnly);

      // Update mode indicator
      this.updateModeIndicator();

      // Hide elements initially
      $(".el-page-info-bar").hide();
      $("#el-print-editor-content").hide();
      $("#el-print-preview-readonly").hide();
      $(".el-editor-actions").hide();
    },

    // Load document
    loadDocument: function () {
      var self = this;
      var $btn = $("#el-load-print-content");

      // Disable button and show loading
      $btn.prop("disabled", true);
      $("#el-print-editor-loading").show();
      this.updateStatus("Loading document...", "info");

      $.ajax({
        url: el_print_config.ajax_url,
        type: "POST",
        data: {
          action: "el_load_print_editor",
          nonce: el_print_config.nonce,
        },
        success: function (response) {
          if (response.success) {
            self.handleLoadSuccess(response.data);
          } else {
            self.handleLoadError(response.data.message);
          }
        },
        error: function () {
          self.handleLoadError("Connection error. Please try again.");
        },
        complete: function () {
          $("#el-print-editor-loading").hide();
          $btn.prop("disabled", false);
        },
      });
    },

    // Handle successful document load
    handleLoadSuccess: function (data) {
      this.state.currentReference = data.reference;
      this.state.editorLoaded = true;
      this.state.totalPages = data.total_pages || 1;
      this.state.isPaperOnly = data.paper_only || false;

      // Update UI
      $("#el-load-print-content").hide();
      $(".el-print-options").show();
      $(".el-page-info-bar").show();

      // Update page stats
      this.updatePageStats();

      if (this.state.canEdit) {
        // Load into editable area
        $("#el-print-editor-content")
          .html(data.html)
          .show()
          .attr("data-paper-only", this.state.isPaperOnly);

        $("#el-save-print-content").show();
        $(".el-edit-toolbar").show();

        // Apply enhanced styles to content
        this.enhanceEditableContent();

        this.updateStatus("✓ Document loaded - Ready to edit", "success");
      } else {
        // Load as read-only
        $("#el-print-preview-readonly").html(data.html).show();

        $(".el-editor-actions").show();

        this.updateStatus("✓ Document loaded - Read only", "success");
      }
    },

    // Handle load error
    handleLoadError: function (message) {
      this.updateStatus("Error: " + message, "error");
      $("#el-load-print-content").prop("disabled", false);
    },

    // Save document
    saveDocument: function () {
      if (!this.state.editorLoaded) {
        alert("Please load the document first");
        return;
      }

      var self = this;
      var $btn = $("#el-save-print-content");

      $btn.prop("disabled", true);
      this.updateStatus("Saving...", "info");

      // Get complete HTML from contenteditable div
      var bodyContent = $("#el-print-editor-content").html();

      // Check if content already has full HTML structure
      var hasFullStructure = bodyContent.indexOf("<!DOCTYPE") !== -1;

      var fullHtml;
      if (hasFullStructure) {
        fullHtml = bodyContent;
      } else {
        // Extract styles and rebuild document
        var tempDiv = document.createElement("div");
        tempDiv.innerHTML = bodyContent;
        var styleTags = tempDiv.querySelectorAll("style");
        var inlineStyles = "";
        styleTags.forEach(function (style) {
          inlineStyles += style.outerHTML;
        });

        // Build complete HTML document
        fullHtml =
          "<!DOCTYPE html>\n" +
          '<html lang="en">\n' +
          "<head>\n" +
          '<meta charset="UTF-8">\n' +
          '<meta name="viewport" content="width=device-width, initial-scale=1.0">\n' +
          "<title>Engagement Letter - " +
          this.state.currentReference +
          "</title>\n" +
          inlineStyles +
          "\n" +
          "</head>\n" +
          "<body>\n" +
          bodyContent +
          "\n" +
          "</body>\n" +
          "</html>";
      }

      $.ajax({
        url: el_print_config.ajax_url,
        type: "POST",
        data: {
          action: "el_save_edited_pdf",
          nonce: el_print_config.nonce,
          reference: this.state.currentReference,
          html: fullHtml,
          paper_only: this.state.isPaperOnly,
        },
        success: function (response) {
          if (response.success) {
            self.handleSaveSuccess(response.data);
          } else {
            self.handleSaveError(response.data.message);
          }
        },
        error: function () {
          self.handleSaveError("Connection error. Please try again.");
        },
        complete: function () {
          $btn.prop("disabled", false);
        },
      });
    },

    // Handle successful save
    handleSaveSuccess: function (data) {
      this.state.isDirty = false;

      // Show share link
      $("#el-share-link").val(data.share_url);
      $("#el-share-link-container").show();

      // Update page stats if changed
      if (data.total_pages) {
        this.state.totalPages = data.total_pages;
        this.updatePageStats();
      }

      this.updateStatus("✓ Document saved successfully", "success");

      // Auto-copy link to clipboard
      this.copyShareLink(true);
    },

    // Handle save error
    handleSaveError: function (message) {
      this.updateStatus("Save failed: " + message, "error");
    },

    // Toggle paper-only mode
    togglePaperOnly: function (enabled) {
      var self = this;

      if (!this.state.editorLoaded) {
        return;
      }

      this.state.isPaperOnly = enabled;
      this.updateModeIndicator();

      // Show loading indicator
      this.updateStatus("Updating pagination...", "info");

      // Get current content
      var currentContent = this.state.canEdit
        ? $("#el-print-editor-content").html()
        : $("#el-print-preview-readonly").html();

      $.ajax({
        url: el_print_config.ajax_url,
        type: "POST",
        data: {
          action: "el_toggle_paper_only",
          nonce: el_print_config.nonce,
          reference: this.state.currentReference,
          paper_only: enabled,
          content: currentContent,
        },
        success: function (response) {
          if (response.success) {
            self.handlePaperOnlyToggle(response.data);
          } else {
            self.updateStatus("Failed to update mode", "error");
          }
        },
        error: function () {
          self.updateStatus("Connection error", "error");
        },
      });
    },

    // Handle paper-only toggle response
    handlePaperOnlyToggle: function (data) {
      // Update content with repaginated HTML
      if (this.state.canEdit) {
        $("#el-print-editor-content").html(data.html);
      } else {
        $("#el-print-preview-readonly").html(data.html);
      }

      // Update stats
      this.state.totalPages = data.total_pages;
      this.updatePageStats();

      // Mark as dirty if editing
      if (this.state.canEdit) {
        this.state.isDirty = true;
      }

      this.updateStatus(data.message, "success");
    },

    // Switch view mode
    switchViewMode: function (mode) {
      if (!this.state.editorLoaded) {
        return;
      }

      this.state.viewMode = mode;

      $(".el-view-btn").removeClass("active");
      $('.el-view-btn[data-view="' + mode + '"]').addClass("active");

      if (mode === "preview") {
        this.showPrintPreview();
      } else {
        this.closePreview();
      }
    },

    // Show print preview
    showPrintPreview: function () {
      var content = this.state.canEdit
        ? $("#el-print-editor-content").html()
        : $("#el-print-preview-readonly").html();

      // Apply print-specific styles to preview
      var previewHtml =
        '<div class="print-preview-wrapper">' + content + "</div>";

      $(".el-preview-content").html(previewHtml);
      $("#el-print-preview-overlay").fadeIn(300);

      // Prevent body scroll
      $("body").css("overflow", "hidden");
    },

    // Close preview
    closePreview: function () {
      $("#el-print-preview-overlay").fadeOut(300);
      $("body").css("overflow", "auto");

      $(".el-view-btn").removeClass("active");
      $('.el-view-btn[data-view="edit"]').addClass("active");
      this.state.viewMode = "edit";
    },

    // Print document
    printDocument: function () {
      if (!this.state.editorLoaded) {
        alert("Please load the document first");
        return;
      }

      // Create hidden iframe for printing
      var $iframe = $("<iframe>", {
        id: "el-print-frame",
        style: "position: absolute; left: -9999px;",
      }).appendTo("body");

      var content = this.state.canEdit
        ? $("#el-print-editor-content").html()
        : $("#el-print-preview-readonly").html();

      // Build complete print document
      var printDoc =
        "<!DOCTYPE html>" +
        "<html><head>" +
        '<meta charset="UTF-8">' +
        "<title>Engagement Letter - " +
        this.state.currentReference +
        "</title>" +
        this.getPrintStyles() +
        "</head><body>" +
        content +
        "</body></html>";

      // Write to iframe and print
      var doc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
      doc.open();
      doc.write(printDoc);
      doc.close();

      // Wait for content to load then print
      $iframe[0].contentWindow.focus();
      setTimeout(function () {
        $iframe[0].contentWindow.print();
        setTimeout(function () {
          $iframe.remove();
        }, 100);
      }, 250);
    },

    // Download PDF
    downloadPDF: function () {
      if (!this.state.currentReference) {
        alert("Please load the document first");
        return;
      }

      // Create download URL
      var downloadUrl =
        el_print_config.ajax_url +
        "?" +
        $.param({
          action: "el_download_pdf",
          ref: this.state.currentReference,
          nonce: el_print_config.nonce,
        });

      // Trigger download
      window.location.href = downloadUrl;

      this.updateStatus("Downloading PDF...", "info");
    },

    // Copy share link
    copyShareLink: function (silent) {
      var $input = $("#el-share-link");
      var link = $input.val();

      if (!link) {
        return;
      }

      // Select and copy
      $input.select();
      document.execCommand("copy");

      // Visual feedback
      $("#el-copy-link").text("Copied!");
      setTimeout(function () {
        $("#el-copy-link").html(
          '<span class="dashicons dashicons-clipboard"></span> Copy'
        );
      }, 2000);

      if (!silent) {
        this.updateStatus("Link copied to clipboard", "success");
      }
    },

    // Enhance editable content with visual aids
    enhanceEditableContent: function () {
      if (!this.state.canEdit) {
        return;
      }

      var $content = $("#el-print-editor-content");

      // Add page break indicators
      $content.find(".print-page").each(function (index) {
        if (index > 0) {
          $(this).before(
            '<div class="el-page-break-indicator">— Page Break —</div>'
          );
        }
      });

      // Highlight page signatures if in paper-only mode
      if (this.state.isPaperOnly) {
        $content
          .find(".page-signature-line")
          .addClass("el-signature-highlight");
      }
    },

    // Update page statistics
    updatePageStats: function () {
      $("#el-total-pages").text(this.state.totalPages);
      $("#el-print-mode").text(
        this.state.isPaperOnly ? "Paper-Only" : "Digital"
      );

      if (this.state.isPaperOnly) {
        $("#el-signature-indicator").show();
      } else {
        $("#el-signature-indicator").hide();
      }
    },

    // Update mode indicator
    updateModeIndicator: function () {
      $("#el-print-mode").text(
        this.state.isPaperOnly ? "Paper-Only" : "Digital"
      );
      $("#el-paper-only-toggle").prop("checked", this.state.isPaperOnly);

      if (this.state.isPaperOnly) {
        $("#el-signature-indicator").show();
        $("#el-print-editor-content").attr("data-paper-only", "true");
      } else {
        $("#el-signature-indicator").hide();
        $("#el-print-editor-content").attr("data-paper-only", "false");
      }
    },

    // Update status message
    updateStatus: function (message, type) {
      var $status = $(".el-editor-status");

      $status.removeClass("success error info").addClass(type).text(message);

      // Auto-hide success messages
      if (type === "success") {
        setTimeout(function () {
          $status.fadeOut(function () {
            $(this).text("").show();
          });
        }, 3000);
      }
    },

    // Get print styles for printing
    getPrintStyles: function () {
      return (
        "<style>" +
        "@page { size: A4 portrait; margin: 0; }" +
        'body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.5; margin: 0; padding: 0; }' +
        ".print-page { width: 210mm; height: 297mm; padding: 20mm 15mm 25mm 15mm; page-break-after: always; position: relative; }" +
        ".print-page:last-child { page-break-after: avoid; }" +
        ".page-signature-line { position: absolute; bottom: 20mm; right: 15mm; left: 15mm; text-align: right; font-size: 9pt; padding-top: 5mm; border-top: 1px dotted #666; }" +
        ".el-page-break-indicator { display: none; }" +
        ".el-signature-highlight { display: none; }" +
        "@media screen { .page-signature-line { background: #fffbf0; border: 1px dashed #ffc107; padding: 5px; } }" +
        "</style>"
      );
    },
  };

  // Initialize when document is ready
  $(document).ready(function () {
    if ($("#el-print-editor-wrapper").length > 0) {
      ELPrintEditorEnhanced.init();
    }
  });

  // Make available globally for debugging
  window.ELPrintEditor = window.ELPrintEditorEnhanced;
})(jQuery);
