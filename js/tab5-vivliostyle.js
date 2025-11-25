/**
 * Tab 5 - Vivliostyle Print Integration
 * Handles preview loading and PDF generation using Vivliostyle Print library
 */

(function ($) {
  "use strict";

  let previewLoaded = false;
  let currentHtmlDoc = null;

  // Initialize when document ready
  $(document).ready(function () {
    // Load Preview Button
    $("#el-load-preview-btn").on("click", function (e) {
      e.preventDefault();
      loadVivliostylePreview();
    });

    // Print/Download PDF Button
    $("#el-print-pdf-btn").on("click", function (e) {
      e.preventDefault();
      printVivliostylePDF();
    });

    // Back to Cart Button
    $("#el-back-to-cart-btn").on("click", function (e) {
      e.preventDefault();
      // Switch to tab 3 (cart)
      $('.el-tab-nav[data-tab="3"]').trigger("click");
    });
  });

  /**
   * Load preview from server and display using Vivliostyle
   */
  function loadVivliostylePreview() {
    const $loadBtn = $("#el-load-preview-btn");
    const $printBtn = $("#el-print-pdf-btn");
    const $previewContainer = $("#el-vivliostyle-preview");

    // Show loading state
    $loadBtn.prop("disabled", true).text("Loading...");
    $previewContainer.html(
      '<div class="el-loading" style="padding: 60px; text-align: center;"><p style="font-size: 18px; color: #6b7280;">⏳ Generating paginated preview...</p><p style="font-size: 14px; color: #9ca3af; margin-top: 10px;">This may take a few seconds</p></div>'
    );

    // Fetch HTML from server
    $.ajax({
      url: elVivliostyleData.ajaxUrl,
      type: "POST",
      data: {
        action: "el_get_vivliostyle_html",
        nonce: elVivliostyleData.nonce,
      },
      success: function (response) {
        if (response.success && response.data.html) {
          currentHtmlDoc = response.data.html;

          // Create preview using Vivliostyle Print
          renderVivliostylePreview(currentHtmlDoc, response.data.clientName);

          // Update UI
          $loadBtn.text("✓ Preview Loaded").css("background", "#10b981");
          $printBtn.show();
          previewLoaded = true;
        } else {
          // Error handling
          const errorMsg = response.data || "Unknown error occurred";
          $previewContainer.html(
            '<div style="padding: 40px; text-align: center;">' +
              '<p style="color: #ef4444; font-size: 18px; font-weight: 600;">⚠️ Error Loading Preview</p>' +
              '<p style="color: #6b7280; margin-top: 10px;">' +
              errorMsg +
              "</p>" +
              '<p style="margin-top: 20px;"><button class="el-btn" onclick="location.reload();">Reload Page</button></p>' +
              "</div>"
          );
          $loadBtn.prop("disabled", false).text("Try Again");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);

        $previewContainer.html(
          '<div style="padding: 40px; text-align: center;">' +
            '<p style="color: #ef4444; font-size: 18px; font-weight: 600;">⚠️ Server Error</p>' +
            '<p style="color: #6b7280; margin-top: 10px;">Failed to load preview. Please check your internet connection and try again.</p>' +
            '<p style="margin-top: 20px;"><button class="el-btn" onclick="location.reload();">Reload Page</button></p>' +
            "</div>"
        );

        $loadBtn.prop("disabled", false).text("Try Again");
      },
    });
  }

  /**
   * Render preview using Vivliostyle Print in iframe
   * This provides CSS isolation from WordPress styles
   */
  function renderVivliostylePreview(htmlDoc, clientName) {
    const $previewContainer = $("#el-vivliostyle-preview");

    // Clear container
    $previewContainer.html(
      '<div id="vivliostyle-preview-iframe-wrapper" style="background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;"></div>'
    );

    // Use Vivliostyle Print to create isolated iframe preview
    // Note: This doesn't trigger printing, just renders the preview
    if (typeof printHTML === "function") {
      try {
        // Create a temporary container for preview
        const previewWrapper = document.getElementById(
          "vivliostyle-preview-iframe-wrapper"
        );

        // Vivliostyle Print creates an isolated iframe
        // We'll use it for preview without triggering print
        printHTML(htmlDoc, {
          title: "Engagement Letter - " + clientName,

          // Custom callback that shows preview without printing
          printCallback: function (iframeWindow) {
            // Get the iframe element
            const iframe = iframeWindow.frameElement;

            // Style the iframe for preview
            iframe.style.width = "100%";
            iframe.style.height = "800px";
            iframe.style.border = "none";
            iframe.style.background = "white";

            // Move iframe to our preview container
            previewWrapper.appendChild(iframe);

            // Add scroll info
            const scrollInfo = document.createElement("p");
            scrollInfo.style.cssText =
              "text-align: center; color: #6b7280; font-size: 14px; padding: 15px; background: #f9fafb; border-top: 1px solid #e5e7eb;";
            scrollInfo.textContent =
              '📄 Scroll to view all pages. Click "Print / Download PDF" when ready.';
            previewWrapper.appendChild(scrollInfo);

            // Don't actually print - this is just preview
            // User will click the Print button to trigger actual printing

            console.log("✓ Vivliostyle preview rendered successfully");
          },

          errorCallback: function (message) {
            console.error("Vivliostyle Error:", message);
            $previewContainer.html(
              '<div style="padding: 40px; text-align: center;">' +
                '<p style="color: #ef4444; font-size: 16px;">⚠️ Preview Rendering Failed</p>' +
                '<p style="color: #6b7280; margin-top: 10px;">' +
                message +
                "</p>" +
                "</div>"
            );
          },
        });
      } catch (error) {
        console.error("Vivliostyle rendering error:", error);

        $previewContainer.html(
          '<div style="padding: 40px; text-align: center;">' +
            '<p style="color: #ef4444; font-size: 16px;">⚠️ Vivliostyle Error</p>' +
            '<p style="color: #6b7280; margin-top: 10px;">Could not render preview. Please try again.</p>' +
            '<pre style="text-align: left; margin-top: 15px; padding: 15px; background: #f3f4f6; border-radius: 4px; font-size: 12px; overflow: auto;">' +
            error.toString() +
            "</pre>" +
            "</div>"
        );
      }
    } else {
      // Vivliostyle Print not loaded
      console.error("Vivliostyle Print library not loaded");

      $previewContainer.html(
        '<div style="padding: 40px; text-align: center;">' +
          '<p style="color: #ef4444; font-size: 16px;">⚠️ Vivliostyle Library Not Loaded</p>' +
          '<p style="color: #6b7280; margin-top: 10px;">The Vivliostyle Print library failed to load. Please refresh the page.</p>' +
          '<p style="margin-top: 20px;"><button class="el-btn" onclick="location.reload();">Reload Page</button></p>' +
          "</div>"
      );
    }
  }

  /**
   * Print PDF using Vivliostyle Print
   * Creates a clean isolated iframe and triggers browser print dialog
   */
  function printVivliostylePDF() {
    if (!previewLoaded || !currentHtmlDoc) {
      alert("Please load the preview first");
      return;
    }

    if (typeof printHTML !== "function") {
      alert(
        "Vivliostyle Print library not available. Please refresh the page."
      );
      return;
    }

    // Show loading indicator
    const $printBtn = $("#el-print-pdf-btn");
    const originalText = $printBtn.text();
    $printBtn.prop("disabled", true).text("Preparing PDF...");

    try {
      // Use Vivliostyle Print to generate and print PDF
      printHTML(currentHtmlDoc, {
        title: "Engagement Letter - " + new Date().toISOString().split("T")[0],

        // Trigger browser print dialog
        printCallback: function (iframeWindow) {
          console.log("✓ Opening print dialog...");

          // Small delay to ensure rendering is complete
          setTimeout(function () {
            // Trigger print
            iframeWindow.print();

            // Reset button after print dialog closes
            setTimeout(function () {
              $printBtn.prop("disabled", false).text(originalText);
            }, 1000);
          }, 500);
        },

        errorCallback: function (message) {
          console.error("Print Error:", message);

          alert("Error generating PDF: " + message);
          $printBtn.prop("disabled", false).text(originalText);
        },
      });
    } catch (error) {
      console.error("Print error:", error);

      alert("Error printing PDF: " + error.message);
      $printBtn.prop("disabled", false).text(originalText);
    }
  }
})(jQuery);
