<?php
/**
 * Standalone PDF Preview for Tab 5
 * Loads engagement letter data from transient and renders with Paged.js
 */

// Load WordPress
require_once('../../../wp-load.php');

// Debug logging
error_log('🔍 STANDALONE: Script loaded');
error_log('🔍 STANDALONE: Reference from URL: ' . ($_GET['ref'] ?? 'NOT SET'));

$pdf_reference = isset($_GET['ref']) ? sanitize_text_field($_GET['ref']) : '';

if (empty($pdf_reference)) {
    error_log('❌ STANDALONE: No reference provided');
    die('No reference provided');
}

error_log('🔍 STANDALONE: Looking for transient: el_pdf_data_' . $pdf_reference);
$pdf_data = get_transient('el_pdf_data_' . $pdf_reference);

if (!$pdf_data) {
    error_log('❌ STANDALONE: Transient NOT FOUND for: el_pdf_data_' . $pdf_reference);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Document Not Found</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; text-align: center; }
            .error { color: #dc2626; font-size: 18px; margin: 20px 0; }
            .ref { font-family: monospace; background: #f3f4f6; padding: 5px 10px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <h1>❌ Document Not Found</h1>
        <p class="error">The engagement letter you're looking for has expired or doesn't exist.</p>
        <p>Reference: <span class="ref"><?php echo esc_html($pdf_reference); ?></span></p>
        <p><a href="<?php echo home_url('/create-engagement-letter/'); ?>">← Back to Wizard</a></p>
    </body>
    </html>
    <?php
    exit;
}

error_log('✅ STANDALONE: Transient FOUND for: el_pdf_data_' . $pdf_reference);

// Load the preview rendering function
$preview_file = get_stylesheet_directory() . '/preview-inline.php';

if (!file_exists($preview_file)) {
    error_log('❌ STANDALONE: preview-inline.php not found at: ' . $preview_file);
    die('Preview template not found');
}

require_once $preview_file;

if (!function_exists('el_render_engagement_letter_html')) {
    error_log('❌ STANDALONE: el_render_engagement_letter_html function not found');
    die('Preview function not available');
}

// Generate HTML
$html_content = el_render_engagement_letter_html($pdf_data);

if (empty($html_content)) {
    error_log('❌ STANDALONE: Generated HTML is empty');
    die('Could not generate document preview');
}

error_log('✅ STANDALONE: HTML generated successfully, length: ' . strlen($html_content));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engagement Letter - <?php echo esc_attr($pdf_reference); ?></title>
    
    <!-- Paged.js for pagination -->
    <script src="https://unpkg.com/pagedjs@0.4.3/dist/paged.polyfill.js"></script>
    
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: #525659;
            padding: 20px;
        }
        
        /* Page setup for Paged.js */
        @page {
            size: A4 portrait;
            margin: 2cm;
        }
        
        /* Content wrapper */
        .pagedjs_pages {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        
        .pagedjs_page {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Print-specific styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .pagedjs_pages {
                gap: 0;
            }
            
            .pagedjs_page {
                box-shadow: none;
            }
        }
        
        /* Just fix image sizes - don't break anything else */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Loading indicator */
        .loading {
            text-align: center;
            padding: 60px;
            font-size: 18px;
            color: #fff;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading">
        <div class="spinner"></div>
        <p>✓ Paged.js initializing...</p>
    </div>
    
    <!-- Document content -->
    <div id="paper-document">
        <?php echo $html_content; ?>
    </div>
    
    <script>
        console.log('🔵 Standalone file loaded');
        console.log('🔵 Reference:', <?php echo json_encode($pdf_reference); ?>);
        // Clean up HTML before Paged.js processes it
const doc = document.querySelector('#paper-document');
if (doc) {
    // Remove problematic inline styles
    doc.querySelectorAll('[style*="inline-block"]').forEach(el => {
        el.style.display = 'block';
    });
    
    // Remove WordPress alignment classes  
    doc.querySelectorAll('.alignleft, .alignright, .aligncenter').forEach(el => {
        el.style.float = 'none';
        el.style.display = 'block';
        el.style.margin = '10px auto';
    });
    
    // Remove empty paragraphs
    doc.querySelectorAll('p').forEach(p => {
        if (p.innerHTML.trim() === '&nbsp;' || p.innerHTML.trim() === '') {
            p.remove();
        }
    });
}
        // Wait for Paged.js to be ready
        class PagedHandler extends Paged.Handler {
            constructor(chunker, polisher, caller) {
                super(chunker, polisher, caller);
            }

            beforeParsed(content) {
                console.log('🔵 Paged.js: beforeParsed');
            }

            afterParsed(parsed) {
                console.log('🔵 Paged.js: afterParsed');
            }

            afterPageLayout(pageElement, page, breakToken, chunker) {
                console.log('🔵 Paged.js: Page', page.position + 1, 'rendered');
            }

            afterRendered(pages) {
                console.log('✅ Paged.js: All', pages.length, 'pages rendered');
                
                // Hide loading indicator
                const loading = document.querySelector('.loading');
                if (loading) {
                    loading.style.display = 'none';
                }
                
                // Calculate total height of all pages
                const pagedPages = document.querySelector('.pagedjs_pages');
                let totalHeight = 0;
                if (pagedPages) {
                    totalHeight = pagedPages.scrollHeight;
                }
                
                // Notify parent window that we're ready
                if (window.parent && window.parent !== window) {
                    console.log('📤 Sending pagedjs-ready message to parent');
                    window.parent.postMessage({
                        type: 'pagedjs-ready',
                        pages: pages.length,
                        height: totalHeight
                    }, '*');
                }
                
                console.log('✅ PDF ready:', pages.length, 'pages, height:', totalHeight + 'px');
            }
        }

        // Register the handler
        Paged.registerHandlers(PagedHandler);
        
        console.log('🔵 Paged.js handler registered');
    </script>
</body>
</html>