/**
 * Tab 5 Diagnostic Script
 * Add this temporarily to check what's loading
 */

console.log("=== TAB 5 DIAGNOSTIC START ===");

// Check if jQuery is loaded
if (typeof jQuery !== "undefined") {
  console.log("✓ jQuery loaded:", jQuery.fn.jquery);
} else {
  console.error("✗ jQuery NOT loaded");
}

// Check if Vivliostyle Print is loaded
if (typeof printHTML === "function") {
  console.log("✓ Vivliostyle Print loaded");
} else {
  console.error("✗ Vivliostyle Print NOT loaded");
}

// Check if elVivliostyleData is available
if (typeof elVivliostyleData !== "undefined") {
  console.log("✓ elVivliostyleData available:", elVivliostyleData);
} else {
  console.error("✗ elVivliostyleData NOT available");
}

// Check if Tab 5 elements exist
jQuery(document).ready(function ($) {
  console.log("=== CHECKING TAB 5 ELEMENTS ===");

  const $loadBtn = $("#el-load-preview-btn");
  const $printBtn = $("#el-print-pdf-btn");
  const $previewContainer = $("#el-vivliostyle-preview");

  console.log("Load Preview Button exists:", $loadBtn.length > 0);
  console.log("Print Button exists:", $printBtn.length > 0);
  console.log("Preview Container exists:", $previewContainer.length > 0);

  if ($loadBtn.length > 0) {
    console.log("✓ Attaching click handler to Load Preview button");
    $loadBtn.on("click", function (e) {
      e.preventDefault();
      console.log("🔵 LOAD PREVIEW CLICKED!");
      alert("Load Preview button works!");
    });
  } else {
    console.error("✗ Cannot find #el-load-preview-btn element");
  }

  console.log("=== TAB 5 DIAGNOSTIC END ===");
});
