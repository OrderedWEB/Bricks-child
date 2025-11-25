<?php
/**
 * Tab 5 - Print Preview with Isolated Paged.js (Option C)
 * Uses iframe to completely isolate paged.js from WordPress CSS
 * Buttons in both parent and iframe
 */

// Tab 5 Shortcode
function el_tab5_iframe_print() {
    
    if (!session_id()) {
        session_start();
    }
    
    $pdf_reference = isset($_SESSION['el_pdf_reference']) ? $_SESSION['el_pdf_reference'] : '';
    
    if (empty($pdf_reference)) {
        return '<div style="padding: 40px; text-align: center; color: #666;">
            <p style="font-size: 18px; margin-bottom: 10px;">⚠️ No engagement letter found</p>
            <p>Please complete Steps 1-4 first to generate the engagement letter.</p>
        </div>';
    }
    
    // Check if data exists
    $pdf_data = get_transient('el_pdf_data_' . $pdf_reference);
    
    if (!$pdf_data) {
        return '<div style="padding: 40px; text-align: center; color: #dc2626;">
            <p style="font-size: 18px; margin-bottom: 10px;">⚠️ Engagement letter expired</p>
            <p>Please go back to Step 4 and regenerate the document.</p>
        </div>';
    }
    
    // Build iframe URL
    $iframe_url = get_stylesheet_directory_uri() . '/print-preview-standalone.php?ref=' . urlencode($pdf_reference);
    
    ob_start();
    ?>
    
    <div id="el-tab5-container" class="el-tab-content">
        
        <div class="el-tab-header" style="margin-bottom: 30px;">
            <h2 style="font-size: 24px; font-weight: 600; margin-bottom: 10px;">Step 5: Print & Download</h2>
            <p style="color: #6b7280; font-size: 16px;">Review your paginated engagement letter and download PDF</p>
        </div>
        
        <!-- Parent-Level Controls (Always Visible) -->
        <div id="el-print-controls-parent" style="background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; gap: 15px; justify-content: center; align-items: center;">
            
            <button id="el-print-iframe-btn" class="el-btn el-btn-primary" style="background: #10b981; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                🖨️ Print / Download PDF
            </button>
            
            <button id="el-open-fullscreen-btn" class="el-btn el-btn-secondary" style="background: #3b82f6; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                🔲 Open in New Window
            </button>
            
            <button id="el-back-to-cart-btn" class="el-btn el-btn-secondary" style="background: #6b7280; color: white; border: none; padding: 15px 30px; font-size: 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                ← Back to Cart
            </button>
            
            <div id="el-page-count" style="margin-left: auto; padding: 10px 20px; background: white; border-radius: 6px; font-weight: 600; color: #374151; display: none;">
                <span id="el-page-count-text">Loading...</span>
            </div>
            
        </div>
        
        <!-- Loading State -->
        <div id="el-iframe-loading" style="text-align: center; padding: 60px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px;">
            <div style="border: 4px solid #f3f3f3; border-top: 4px solid #3b82f6; border-radius: 50%; width: 60px; height: 60px; animation: el-spin 1s linear infinite; margin: 0 auto;"></div>
            <p style="margin-top: 20px; color: #6b7280; font-size: 16px;">⏳ Generating paginated preview...</p>
            <p style="margin-top: 10px; color: #9ca3af; font-size: 14px;">This may take a few seconds</p>
        </div>
        
        <!-- Iframe Container -->
        <div id="el-iframe-container" style="display: none; border: 2px solid #e5e7eb; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <iframe 
                id="el-print-iframe" 
                src="<?php echo esc_url($iframe_url); ?>" 
                style="width: 100%; border: none; min-height: 600px;"
                title="Engagement Letter Preview"
            ></iframe>
        </div>
        
        <!-- Info Box -->
        <div style="margin-top: 30px; padding: 20px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;">
            <h4 style="margin: 0 0 10px 0; color: #1e40af; font-size: 16px; font-weight: 600;">ℹ️ How to Download PDF</h4>
            <ol style="margin: 0; padding-left: 20px; color: #1e40af;">
                <li>Review the paginated preview above</li>
                <li>Click "Print / Download PDF" button</li>
                <li>In the print dialog, select "Save as PDF" as destination</li>
                <li>Click "Save" to download your engagement letter</li>
                <li>Alternatively, click "Open in New Window" for full-screen view</li>
            </ol>
        </div>
        
    </div>
    
    <style>
        @keyframes el-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        #el-print-controls-parent button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            transition: all 0.2s;
        }
        
        #el-print-controls-parent button:active {
            transform: translateY(0);
        }
    </style>
    
    <script>
    (function() {
        'use strict';
        
        const iframe = document.getElementById('el-print-iframe');
        const loading = document.getElementById('el-iframe-loading');
        const container = document.getElementById('el-iframe-container');
        const pageCountDiv = document.getElementById('el-page-count');
        const pageCountText = document.getElementById('el-page-count-text');
        
        // Listen for messages from iframe (paged.js ready + height)
        window.addEventListener('message', function(e) {
            
            if (e.data.type === 'pagedjs-ready') {
                console.log('✓ Paged.js ready in iframe');
                console.log('Pages:', e.data.pages);
                console.log('Height:', e.data.height);
                
                // Hide loading
                loading.style.display = 'none';
                
                // Show iframe
                container.style.display = 'block';
                
                // Set iframe height (add padding for safety)
                iframe.style.height = (e.data.height + 100) + 'px';
                
                // Show page count
                pageCountDiv.style.display = 'block';
                pageCountText.textContent = e.data.pages + ' pages';
            }
            
        });
        
        // Print button (triggers print in iframe)
        document.getElementById('el-print-iframe-btn').addEventListener('click', function() {
            try {
                iframe.contentWindow.print();
            } catch (err) {
                console.error('Print error:', err);
                alert('Could not trigger print. Please use the button inside the preview.');
            }
        });
        
        // Open fullscreen button
        document.getElementById('el-open-fullscreen-btn').addEventListener('click', function() {
            window.open(iframe.src, '_blank', 'width=1200,height=800');
        });
        
        // Back to cart button
        document.getElementById('el-back-to-cart-btn').addEventListener('click', function() {
            const cartTab = document.querySelector('.el-tab-nav[data-tab="3"]');
            if (cartTab) {
                cartTab.click();
            }
        });
        
        // Fallback: if paged.js doesn't respond in 15 seconds, show iframe anyway
        setTimeout(function() {
            if (loading.style.display !== 'none') {
                console.warn('Paged.js timeout - showing iframe anyway');
                loading.innerHTML = '<p style="color: #f59e0b;">⚠️ Preview is loading slowly. You may see content below.</p>';
                container.style.display = 'block';
                iframe.style.height = '1200px';
            }
        }, 15000);
        
    })();
    </script>
    
    <?php
    return ob_get_clean();
}
add_shortcode('el_tab5_iframe_print', 'el_tab5_iframe_print');