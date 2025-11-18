/**
 * Engagement Letter Signature UI
 * Detects {{sig}} and {{sig2}} placeholders and converts them to signature pads
 */

(function ($) {
  "use strict";

  const SignatureUI = {
    canvasInstances: {},

    init: function () {
      this.detectSignaturePlaceholders();
    },

    detectSignaturePlaceholders: function () {
      const content = $("#el-pdf-preview-content, .el-pdf-content, body");

      // Find {{sig}} and {{sig2}} placeholders in HTML
      const html = content.html();

      if (!html) return;

      // Check if signatures are required - support {{sig}}, {{sig1}}, {{SIG}}, {{SIG1}}
      const hasSig1 =
        html.includes("{{sig}}") ||
        html.includes("{{SIG}}") ||
        html.includes("{{sig1}}") ||
        html.includes("{{SIG1}}");
      const hasSig2 = html.includes("{{sig2}}") || html.includes("{{SIG2}}");

      if (!hasSig1 && !hasSig2) {
        console.log("No signature placeholders found");
        return;
      }

      console.log(
        "Signature placeholders found - sig1:",
        hasSig1,
        "sig2:",
        hasSig2
      );

      // Replace placeholders with signature pads
      if (hasSig1) {
        this.replacePlaceholder(content, "{{sig}}", 1);
        this.replacePlaceholder(content, "{{SIG}}", 1);
        this.replacePlaceholder(content, "{{sig1}}", 1);
        this.replacePlaceholder(content, "{{SIG1}}", 1);
      }

      if (hasSig2) {
        this.replacePlaceholder(content, "{{sig2}}", 2);
        this.replacePlaceholder(content, "{{SIG2}}", 2);
      }

      // Add submit button after all signature fields
      if (hasSig1 || hasSig2) {
        this.addSubmitButton(content);
      }
    },

    replacePlaceholder: function (content, placeholder, sigNum) {
      let html = content.html();

      if (!html.includes(placeholder)) return;

      // Count how many times this placeholder appears
      const count = (html.match(new RegExp(placeholder, "g")) || []).length;

      console.log(`Found ${count} instances of ${placeholder}`);

      // If multiple instances, replace all with the SAME canvas ID
      // This way all instances point to the same signature
      const signatureHTML = this.createSignaturePad(sigNum, count > 1);

      // Replace FIRST instance with the full signature pad
      html = html.replace(placeholder, signatureHTML);

      // Replace remaining instances with just a reference/placeholder
      if (count > 1) {
        const referenceHTML = `<span class="el-signature-reference" data-sig-num="${sigNum}" style="color:#3b82f6; font-style:italic;">[Signature ${sigNum} - see above]</span>`;
        html = html.replace(new RegExp(placeholder, "g"), referenceHTML);
      }

      content.html(html);

      // Initialize canvas once
      this.initializeCanvas(sigNum);
    },

    createSignaturePad: function (sigNum, hasMultiple) {
      const label = sigNum === 1 ? "Client Signature" : "Co-signer Signature";
      const note = hasMultiple
        ? '<div style="color:#64748b; font-size:14px; margin-top:8px;">Note: This signature will appear in all designated locations in the document.</div>'
        : "";

      return `
                <div class="el-signature-container" data-sig-num="${sigNum}">
                    <div class="el-signature-label">${label} <span style="color:#dc2626;">*</span></div>
                    ${note}
                    <div class="el-signature-pad-wrapper">
                        <canvas id="el-signature-canvas-${sigNum}" 
                                class="el-signature-canvas"
                                width="1200" 
                                height="300" 
                                style="border:2px solid #cbd5e1; border-radius:8px; background:#ffffff; cursor:crosshair; touch-action:none; width:100%; max-width:100%; height:auto;">
                        </canvas>
                    </div>
                    <div class="el-signature-actions">
                        <button type="button" class="el-signature-clear" data-canvas-id="${sigNum}">
                            <span style="margin-right:5px;">🗑️</span> Clear Signature
                        </button>
                    </div>
                </div>
            `;
    },

    initializeCanvas: function (sigNum) {
      const canvas = document.getElementById(`el-signature-canvas-${sigNum}`);

      if (!canvas) {
        console.error("Canvas not found for signature", sigNum);
        return;
      }

      const ctx = canvas.getContext("2d");
      let isDrawing = false;
      let lastX = 0;
      let lastY = 0;

      // Set canvas resolution for crisp signatures
      const rect = canvas.getBoundingClientRect();
      canvas.width = 1200; // High resolution
      canvas.height = 300;

      // Store instance
      this.canvasInstances[sigNum] = { canvas, ctx, isEmpty: true };

      // Drawing functions with proper scaling
      const startDrawing = (e) => {
        isDrawing = true;
        const pos = this.getMousePos(canvas, e);
        [lastX, lastY] = [pos.x, pos.y];
        this.canvasInstances[sigNum].isEmpty = false;
      };

      const draw = (e) => {
        if (!isDrawing) return;
        e.preventDefault();

        const pos = this.getMousePos(canvas, e);

        ctx.strokeStyle = "#000000";
        ctx.lineWidth = 3;
        ctx.lineCap = "round";
        ctx.lineJoin = "round";

        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();

        [lastX, lastY] = [pos.x, pos.y];
      };

      const stopDrawing = () => {
        isDrawing = false;
      };

      // Mouse events
      canvas.addEventListener("mousedown", startDrawing);
      canvas.addEventListener("mousemove", draw);
      canvas.addEventListener("mouseup", stopDrawing);
      canvas.addEventListener("mouseout", stopDrawing);

      // Touch events
      canvas.addEventListener("touchstart", (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent("mousedown", {
          clientX: touch.clientX,
          clientY: touch.clientY,
        });
        canvas.dispatchEvent(mouseEvent);
      });

      canvas.addEventListener("touchmove", (e) => {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent("mousemove", {
          clientX: touch.clientX,
          clientY: touch.clientY,
        });
        canvas.dispatchEvent(mouseEvent);
      });

      canvas.addEventListener("touchend", (e) => {
        e.preventDefault();
        const mouseEvent = new MouseEvent("mouseup", {});
        canvas.dispatchEvent(mouseEvent);
      });

      // Clear button
      $(document).on(
        "click",
        `.el-signature-clear[data-canvas-id="${sigNum}"]`,
        () => {
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          this.canvasInstances[sigNum].isEmpty = true;
        }
      );
    },

    getMousePos: function (canvas, e) {
      const rect = canvas.getBoundingClientRect();
      const clientX = e.clientX || (e.touches && e.touches[0].clientX);
      const clientY = e.clientY || (e.touches && e.touches[0].clientY);

      return {
        x: (clientX - rect.left) * (canvas.width / rect.width),
        y: (clientY - rect.top) * (canvas.height / rect.height),
      };
    },

    addSubmitButton: function (content) {
      console.log("Adding submit button...");

      // Check if button already exists
      if ($("#el-submit-signature").length) {
        console.log("Submit button already exists, skipping");
        return;
      }

      const buttonHTML = `
                <div class="el-signature-submit-container" style="margin:40px 0; padding:30px; background:#f8fafc; border:2px solid #cbd5e1; border-radius:12px; text-align:center;">
                    <div class="el-signature-consent" style="margin-bottom:20px; text-align:left; max-width:700px; margin-left:auto; margin-right:auto;">
                        <label style="display:flex; align-items:start; gap:12px; cursor:pointer; background:white; padding:20px; border-radius:8px; border:2px solid #e5e7eb;">
                            <input type="checkbox" id="el-signature-consent" required style="margin-top:4px; width:24px; height:24px; cursor:pointer;">
                            <span style="font-size:16px; line-height:1.6;">
                                <strong style="font-size:18px; color:#1e293b;">I have read and agree to the terms of this engagement letter.</strong><br>
                                <small style="color:#64748b; margin-top:5px; display:block;">By checking this box and signing above, you agree to all terms and conditions outlined in this engagement letter.</small>
                            </span>
                        </label>
                    </div>
                    
                    <button type="button" id="el-submit-signature" class="el-signature-submit-btn" disabled style="background:#dc2626; color:white; border:none; padding:18px 50px; border-radius:8px; font-size:18px; font-weight:600; cursor:not-allowed; opacity:0.5; transition:all 0.3s; box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                        <span style="font-size:20px; margin-right:8px;">✍️</span>
                        Submit Signed Engagement Letter
                    </button>
                    
                    <div id="el-signature-status" style="margin-top:20px; font-size:16px; display:none;"></div>
                    
                    <p style="margin-top:15px; color:#64748b; font-size:14px;">
                        ⚠️ Please check the box above to enable the submit button
                    </p>
                </div>
            `;

      // Find a good place to insert - after last signature or at the end of content
      const lastSig = content.find(".el-signature-container").last();
      if (lastSig.length) {
        console.log("Inserting button after last signature container");
        lastSig.after(buttonHTML);
      } else {
        console.log("Appending button to content");
        content.append(buttonHTML);
      }

      console.log("Submit button HTML added to DOM");

      // Enable/disable submit button based on consent checkbox
      $(document).on("change", "#el-signature-consent", function () {
        const $btn = $("#el-submit-signature");
        console.log("Consent checkbox changed:", this.checked);
        if (this.checked) {
          $btn
            .prop("disabled", false)
            .css({ cursor: "pointer", opacity: "1", background: "#059669" });
        } else {
          $btn
            .prop("disabled", true)
            .css({
              cursor: "not-allowed",
              opacity: "0.5",
              background: "#dc2626",
            });
        }
      });

      // Handle submit
      $(document).on("click", "#el-submit-signature", () => {
        console.log("Submit button clicked");
        this.handleSubmit();
      });
    },

    handleSubmit: function () {
      const $btn = $("#el-submit-signature");
      const $status = $("#el-signature-status");

      // Validate consent
      if (!$("#el-signature-consent").is(":checked")) {
        $status
          .html(
            '<span style="color:#dc2626;">⚠️ Please confirm you agree to the terms</span>'
          )
          .show();
        return;
      }

      // Validate signature 1 (required)
      const sig1 = this.canvasInstances[1];
      if (!sig1 || sig1.isEmpty) {
        $status
          .html(
            '<span style="color:#dc2626;">⚠️ Please provide your signature</span>'
          )
          .show();
        return;
      }

      // Get signature data as base64
      const signature1Data = sig1.canvas.toDataURL("image/png");
      let signature2Data = "";

      // Check if signature 2 exists and is filled
      const sig2 = this.canvasInstances[2];
      if (sig2 && !sig2.isEmpty) {
        signature2Data = sig2.canvas.toDataURL("image/png");
      }

      // Get reference from URL or page
      const urlParams = new URLSearchParams(window.location.search);
      const reference =
        urlParams.get("ref") ||
        $("[data-el-reference]").data("el-reference") ||
        "";

      if (!reference) {
        $status
          .html(
            '<span style="color:#dc2626;">⚠️ Missing reference number</span>'
          )
          .show();
        return;
      }

      // Disable button and show loading
      $btn
        .prop("disabled", true)
        .html(
          '<span class="dashicons dashicons-update-alt spinning"></span> Submitting...'
        )
        .css("opacity", "0.7");
      $status
        .html(
          '<span style="color:#3b82f6;">⏳ Processing your signature...</span>'
        )
        .show();

      // Submit via AJAX
      $.ajax({
        url: ajaxurl || "/wp-admin/admin-ajax.php",
        method: "POST",
        data: {
          action: "el_submit_signature",
          nonce: elSignatureData.nonce,
          reference: reference,
          signature_1: signature1Data,
          signature_2: signature2Data,
        },
        success: (response) => {
          if (response.success) {
            $status
              .html(
                `
                            <div style="background:#d1fae5; border:2px solid #059669; border-radius:12px; padding:30px; text-align:center;">
                                <div style="font-size:48px; margin-bottom:15px;">✅</div>
                                <h3 style="color:#059669; font-size:24px; font-weight:700; margin:0 0 15px 0;">
                                    ${response.data.message}
                                </h3>
                                <p style="color:#047857; font-size:16px; margin-bottom:25px;">
                                    Your signed engagement letter is ready for download.
                                </p>
                                <a href="${response.data.download_url}" 
                                   class="el-download-signed-btn" 
                                   style="display:inline-block; background:#059669; color:white; padding:16px 40px; border-radius:8px; text-decoration:none; font-size:18px; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.1); transition:all 0.3s;"
                                   onmouseover="this.style.background='#047857'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';"
                                   onmouseout="this.style.background='#059669'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">
                                    <span style="font-size:20px; margin-right:8px;">📥</span> Download Signed PDF
                                </a>
                                <p style="margin-top:20px; color:#6b7280; font-size:14px; line-height:1.6;">
                                    <strong>Download link valid for ${response.data.expiry_days} days</strong><br>
                                    A copy has been sent to your lawyer<br>
                                    You can close this page now
                                </p>
                            </div>
                        `
              )
              .show();

            // Hide submit button and consent
            $btn.parent().hide();
            $("#el-signature-consent").parent().parent().hide();

            // Disable all signature pads
            $(".el-signature-container canvas").css({
              "pointer-events": "none",
              opacity: "0.6",
            });
            $(".el-signature-clear").prop("disabled", true).hide();

            // Scroll to success message
            $("html, body").animate(
              {
                scrollTop: $status.offset().top - 100,
              },
              500
            );
          } else {
            $status
              .html(
                `<span style="color:#dc2626; font-size:16px;">⚠️ Error: ${response.data.message}</span>`
              )
              .show();
            $btn
              .prop("disabled", false)
              .html(
                '<span class="dashicons dashicons-yes-alt"></span> Submit Signed Engagement Letter'
              )
              .css("opacity", "1");
          }
        },
        error: (xhr, status, error) => {
          $status
            .html(
              `<span style="color:#dc2626;">⚠️ Connection error. Please try again.</span>`
            )
            .show();
          $btn
            .prop("disabled", false)
            .html(
              '<span class="dashicons dashicons-yes-alt"></span> Submit Signed Engagement Letter'
            )
            .css("opacity", "1");
          console.error("Signature submission error:", error);
        },
      });
    },
  };

  // Initialize when document ready
  $(document).ready(function () {
    SignatureUI.init();
  });

  // Add CSS for spinning animation
  const style = document.createElement("style");
  style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spinning {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        .el-signature-container {
            margin: 30px 0;
            padding: 25px;
            background: #f8fafc;
            border: 2px solid #cbd5e1;
            border-radius: 12px;
        }
        .el-signature-label {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .el-signature-pad-wrapper {
            position: relative;
            width: 100%;
            margin: 15px 0;
        }
        .el-signature-canvas {
            display: block;
            width: 100% !important;
            height: auto !important;
            max-width: 100%;
            border: 2px solid #cbd5e1 !important;
            border-radius: 8px !important;
            background: #ffffff !important;
            cursor: crosshair;
            touch-action: none;
        }
        .el-signature-actions {
            margin-top: 12px;
            display: flex;
            gap: 10px;
        }
        .el-signature-clear {
            background: #64748b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: 500;
        }
        .el-signature-clear:hover {
            background: #475569;
        }
    `;
  document.head.appendChild(style);
})(jQuery);
