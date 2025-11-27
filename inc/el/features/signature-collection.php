<?php
/**
 * Engagement Letter System - Signature Collection
 * 
 * Handles electronic signature collection:
 * - Token-based secure document viewing
 * - HTML5 signature pad integration
 * - Signature capture and storage
 * - PDF finalization with signatures
 * - Time-limited access control
 * 
 * LOAD ORDER: Feature module (after core modules)
 * DEPENDENCIES: constants.php, session.php, helpers.php
 * 
 * @package Engagement_Letter_System
 * @version 2.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================
// SECURE VIEWING TOKEN SYSTEM
// ============================================

/**
 * Generates secure viewing token for engagement letter
 * 
 * Creates time-limited token for client to view/sign document.
 * 
 * @param int   $engagement_id Engagement letter post ID
 * @param int   $expiry_hours  Token expiry in hours (default: 72)
 * @return string Secure token
 */
function el_generate_view_token($engagement_id, $expiry_hours = 72) {
    // Generate random token
    $token = bin2hex(random_bytes(32));
    
    // Store token data in transient
    $token_data = [
        'engagement_id' => $engagement_id,
        'created' => current_time('mysql'),
        'expires' => date('Y-m-d H:i:s', strtotime('+' . $expiry_hours . ' hours')),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'used' => false,
    ];
    
    $expiry_seconds = $expiry_hours * HOUR_IN_SECONDS;
    
    set_transient(EL_VIEW_TOKEN_PREFIX . $token, $token_data, $expiry_seconds);
    
    // Store token reference in engagement meta
    el_set_meta($engagement_id, 'view_token', $token);
    el_set_meta($engagement_id, 'view_token_expiry', $token_data['expires']);
    
    if (EL_DEBUG_MODE) {
        el_log('Generated view token for engagement ' . $engagement_id . ' (expires: ' . $token_data['expires'] . ')', 'info');
    }
    
    return $token;
}

/**
 * Validates viewing token
 * 
 * @param string $token View token
 * @return array|false Token data or false if invalid
 */
function el_validate_view_token($token) {
    $token_data = get_transient(EL_VIEW_TOKEN_PREFIX . $token);
    
    if (!$token_data) {
        if (EL_DEBUG_MODE) {
            el_log('View token invalid or expired: ' . $token, 'warning');
        }
        return false;
    }
    
    // Check expiry
    if (strtotime($token_data['expires']) < time()) {
        delete_transient(EL_VIEW_TOKEN_PREFIX . $token);
        return false;
    }
    
    return $token_data;
}

/**
 * Marks token as used
 * 
 * @param string $token View token
 * @return bool True if marked successfully
 */
function el_mark_token_used($token) {
    $token_data = get_transient(EL_VIEW_TOKEN_PREFIX . $token);
    
    if (!$token_data) {
        return false;
    }
    
    $token_data['used'] = true;
    $token_data['used_at'] = current_time('mysql');
    
    // Update transient with remaining TTL
    $expiry_seconds = strtotime($token_data['expires']) - time();
    
    if ($expiry_seconds > 0) {
        set_transient(EL_VIEW_TOKEN_PREFIX . $token, $token_data, $expiry_seconds);
        return true;
    }
    
    return false;
}

/**
 * Generates secure viewing URL
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return string Secure viewing URL
 */
function el_generate_view_url($engagement_id) {
    $token = el_generate_view_token($engagement_id);
    
    // Get base URL (adjust to your view page slug)
    $base_url = home_url('/view-engagement-letter/');
    
    return add_query_arg([
        'token' => $token,
        'ref' => el_get_meta($engagement_id, 'reference'),
    ], $base_url);
}

// ============================================
// SIGNATURE CAPTURE
// ============================================

/**
 * Processes signature submission
 * 
 * Stores signature data and updates engagement status.
 * 
 * @param int    $engagement_id  Engagement letter post ID
 * @param string $signature_data Base64 signature image data
 * @param array  $metadata       Additional signature metadata
 * @return bool True if processed successfully
 */
function el_process_signature($engagement_id, $signature_data, $metadata = []) {
    if (!el_validate_engagement_post($engagement_id)) {
        return false;
    }
    
    // Validate signature data (should be base64 image)
    if (!preg_match('/^data:image\/(png|jpeg);base64,/', $signature_data)) {
        el_log('Invalid signature data format', 'error');
        return false;
    }
    
    // Store signature data
    el_set_meta($engagement_id, 'signature_data', $signature_data);
    el_set_meta($engagement_id, 'signature_date', current_time('mysql'));
    el_set_meta($engagement_id, 'signature_ip', $_SERVER['REMOTE_ADDR'] ?? '');
    
    // Store additional metadata
    if (!empty($metadata)) {
        el_set_meta($engagement_id, 'signature_metadata', $metadata);
    }
    
    // Update engagement status to signed
    el_set_meta($engagement_id, 'status', EL_STATUS_SIGNED);
    
    // Generate signed PDF reference
    $signed_reference = el_generate_unique_reference();
    el_set_meta($engagement_id, 'signed_reference', $signed_reference);
    
    if (EL_DEBUG_MODE) {
        el_log('Signature processed for engagement ' . $engagement_id, 'info');
    }
    
    // Trigger action for further processing (PDF generation, emails, etc.)
    do_action('el_signature_processed', $engagement_id, $signature_data, $metadata);
    
    return true;
}

/**
 * Retrieves signature data
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return array|false Signature data or false if not signed
 */
function el_get_signature($engagement_id) {
    $signature_data = el_get_meta($engagement_id, 'signature_data');
    
    if (!$signature_data) {
        return false;
    }
    
    return [
        'data' => $signature_data,
        'date' => el_get_meta($engagement_id, 'signature_date'),
        'ip' => el_get_meta($engagement_id, 'signature_ip'),
        'metadata' => el_get_meta($engagement_id, 'signature_metadata', []),
    ];
}

/**
 * Checks if engagement is signed
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return bool True if signed
 */
function el_is_signed($engagement_id) {
    return el_get_signature($engagement_id) !== false;
}

// ============================================
// SIGNATURE VALIDATION
// ============================================

/**
 * Validates signature requirements
 * 
 * Ensures signature meets legal/technical requirements.
 * 
 * @param string $signature_data Base64 signature image
 * @return array Validation result
 */
function el_validate_signature_data($signature_data) {
    $validation = [
        'valid' => true,
        'errors' => [],
    ];
    
    // Check format
    if (!preg_match('/^data:image\/(png|jpeg);base64,/', $signature_data)) {
        $validation['valid'] = false;
        $validation['errors'][] = 'Invalid image format. Must be PNG or JPEG.';
    }
    
    // Check if actually contains data (not empty canvas)
    $image_data = preg_replace('/^data:image\/(png|jpeg);base64,/', '', $signature_data);
    $decoded = base64_decode($image_data);
    
    if (strlen($decoded) < 100) {
        $validation['valid'] = false;
        $validation['errors'][] = 'Signature appears to be empty.';
    }
    
    // Check size (max 500KB)
    if (strlen($decoded) > 500000) {
        $validation['valid'] = false;
        $validation['errors'][] = 'Signature image too large (max 500KB).';
    }
    
    return $validation;
}

// ============================================
// SIGNATURE PAD RENDERING
// ============================================

/**
 * Renders HTML5 signature pad
 * 
 * @param array $args Display arguments
 * @return string HTML signature pad
 */
function el_render_signature_pad($args = []) {
    $defaults = [
        'width' => 600,
        'height' => 200,
        'border_color' => EL_COLOR_PRIMARY,
        'pen_color' => '#000000',
        'background_color' => '#ffffff',
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $output = '<div class="el-signature-wrapper" style="margin: 20px 0;">';
    
    // Canvas
    $output .= '<canvas id="elSignaturePad" width="' . esc_attr($args['width']) . '" height="' . esc_attr($args['height']) . '" style="
        border: 2px solid ' . esc_attr($args['border_color']) . ';
        border-radius: 8px;
        cursor: crosshair;
        display: block;
        background: ' . esc_attr($args['background_color']) . ';
        touch-action: none;
    "></canvas>';
    
    // Controls
    $output .= '<div class="el-signature-controls" style="margin-top: 15px; display: flex; gap: 10px;">';
    $output .= '<button type="button" id="elClearSignature" class="button" style="background: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">Clear Signature</button>';
    $output .= '<button type="button" id="elSubmitSignature" class="button button-primary" style="padding: 10px 20px; border-radius: 6px;">Submit Signature</button>';
    $output .= '</div>';
    
    // Instructions
    $output .= '<p class="el-signature-instructions" style="margin-top: 10px; font-size: 13px; color: #6b7280;">Please sign using your mouse or touchscreen in the box above.</p>';
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Shortcode: Signature pad
 * 
 * Usage: [el_signature_pad]
 */
function el_signature_pad_shortcode($atts) {
    $atts = shortcode_atts([
        'width' => 600,
        'height' => 200,
    ], $atts);
    
    return el_render_signature_pad($atts);
}
add_shortcode('el_signature_pad', 'el_signature_pad_shortcode');

// ============================================
// AJAX HANDLERS
// ============================================

/**
 * AJAX: Submit signature
 */
function el_ajax_submit_signature() {
    check_ajax_referer(EL_SIGNATURE_NONCE, 'nonce');
    
    $signature_data = $_POST['signature_data'] ?? '';
    $engagement_id = intval($_POST['engagement_id'] ?? 0);
    $token = sanitize_text_field($_POST['token'] ?? '');
    
    // Validate token if provided (for client-side signing)
    if ($token) {
        $token_data = el_validate_view_token($token);
        
        if (!$token_data) {
            wp_send_json_error(['message' => 'Invalid or expired access token']);
        }
        
        $engagement_id = $token_data['engagement_id'];
    }
    
    if (!$engagement_id) {
        wp_send_json_error(['message' => 'Invalid engagement ID']);
    }
    
    // Validate signature data
    $validation = el_validate_signature_data($signature_data);
    
    if (!$validation['valid']) {
        wp_send_json_error([
            'message' => 'Signature validation failed',
            'errors' => $validation['errors'],
        ]);
    }
    
    // Collect metadata
    $metadata = [
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => current_time('mysql'),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ];
    
    // Process signature
    $result = el_process_signature($engagement_id, $signature_data, $metadata);
    
    if ($result) {
        // Mark token as used
        if ($token) {
            el_mark_token_used($token);
        }
        
        wp_send_json_success([
            'message' => 'Signature submitted successfully',
            'engagement_id' => $engagement_id,
            'signed_date' => current_time('mysql'),
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to process signature']);
    }
}
add_action('wp_ajax_' . EL_AJAX_SUBMIT_SIGNATURE, 'el_ajax_submit_signature');
add_action('wp_ajax_nopriv_' . EL_AJAX_SUBMIT_SIGNATURE, 'el_ajax_submit_signature');

/**
 * AJAX: Validate view token
 */
function el_ajax_validate_view_token() {
    $token = sanitize_text_field($_POST['token'] ?? '');
    
    if (!$token) {
        wp_send_json_error(['message' => 'No token provided']);
    }
    
    $token_data = el_validate_view_token($token);
    
    if ($token_data) {
        $engagement = el_get_engagement_letter($token_data['engagement_id']);
        
        wp_send_json_success([
            'valid' => true,
            'engagement_id' => $token_data['engagement_id'],
            'reference' => $engagement['reference'] ?? '',
            'expires' => $token_data['expires'],
        ]);
    } else {
        wp_send_json_error(['message' => 'Invalid or expired token']);
    }
}
add_action('wp_ajax_el_validate_view_token', 'el_ajax_validate_view_token');
add_action('wp_ajax_nopriv_el_validate_view_token', 'el_ajax_validate_view_token');

// ============================================
// SIGNATURE PAD JAVASCRIPT
// ============================================

/**
 * Enqueues signature pad JavaScript
 */
function el_enqueue_signature_pad_script() {
    // Only on relevant pages
    if (!is_page('view-engagement-letter') && !is_page('engagement-letter-wizard')) {
        return;
    }
    
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            var canvas = document.getElementById('elSignaturePad');
            
            if (!canvas) {
                return;
            }
            
            var ctx = canvas.getContext('2d');
            var drawing = false;
            var lastX = 0;
            var lastY = 0;
            
            // Set up drawing context
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            
            // Get canvas position
            function getMousePos(canvas, evt) {
                var rect = canvas.getBoundingClientRect();
                return {
                    x: (evt.clientX || evt.touches[0].clientX) - rect.left,
                    y: (evt.clientY || evt.touches[0].clientY) - rect.top
                };
            }
            
            // Mouse events
            canvas.addEventListener('mousedown', function(e) {
                drawing = true;
                var pos = getMousePos(canvas, e);
                lastX = pos.x;
                lastY = pos.y;
            });
            
            canvas.addEventListener('mousemove', function(e) {
                if (!drawing) return;
                
                var pos = getMousePos(canvas, e);
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                
                lastX = pos.x;
                lastY = pos.y;
            });
            
            canvas.addEventListener('mouseup', function() {
                drawing = false;
            });
            
            canvas.addEventListener('mouseleave', function() {
                drawing = false;
            });
            
            // Touch events
            canvas.addEventListener('touchstart', function(e) {
                e.preventDefault();
                drawing = true;
                var pos = getMousePos(canvas, e);
                lastX = pos.x;
                lastY = pos.y;
            });
            
            canvas.addEventListener('touchmove', function(e) {
                e.preventDefault();
                if (!drawing) return;
                
                var pos = getMousePos(canvas, e);
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                
                lastX = pos.x;
                lastY = pos.y;
            });
            
            canvas.addEventListener('touchend', function(e) {
                e.preventDefault();
                drawing = false;
            });
            
            // Clear button
            $('#elClearSignature').on('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });
            
            // Submit button
            $('#elSubmitSignature').on('click', function() {
                var signatureData = canvas.toDataURL('image/png');
                
                // Check if canvas is empty
                var emptyCanvas = document.createElement('canvas');
                emptyCanvas.width = canvas.width;
                emptyCanvas.height = canvas.height;
                
                if (canvas.toDataURL() === emptyCanvas.toDataURL()) {
                    alert('Please provide a signature before submitting.');
                    return;
                }
                
                // Get token from URL or data attribute
                var token = new URLSearchParams(window.location.search).get('token');
                var engagementId = $('#elSubmitSignature').data('engagement-id');
                
                $(this).prop('disabled', true).text('Submitting...');
                
                $.ajax({
                    url: elAjax.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: '" . EL_AJAX_SUBMIT_SIGNATURE . "',
                        nonce: elAjax.nonce,
                        signature_data: signatureData,
                        engagement_id: engagementId,
                        token: token
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Signature submitted successfully!');
                            // Redirect or show confirmation
                            window.location.href = window.location.href + '&signed=1';
                        } else {
                            alert('Error: ' + response.data.message);
                            $('#elSubmitSignature').prop('disabled', false).text('Submit Signature');
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                        $('#elSubmitSignature').prop('disabled', false).text('Submit Signature');
                    }
                });
            });
        });
    ");
}
add_action('wp_enqueue_scripts', 'el_enqueue_signature_pad_script');

// ============================================
// SECURE VIEW ENDPOINT
// ============================================

/**
 * Handles secure document view requests
 * 
 * Called via custom page template or rewrite rule.
 */
function el_handle_secure_view() {
    // Check if this is a view request
    if (!is_page('view-engagement-letter')) {
        return;
    }
    
    $token = sanitize_text_field($_GET['token'] ?? '');
    
    if (!$token) {
        wp_die('Access denied: No token provided', 'Unauthorised', ['response' => 403]);
    }
    
    $token_data = el_validate_view_token($token);
    
    if (!$token_data) {
        wp_die('Access denied: Invalid or expired token', 'Unauthorised', ['response' => 403]);
    }
    
    // Load engagement data
    $engagement_id = $token_data['engagement_id'];
    $engagement = el_get_engagement_letter($engagement_id);
    
    if (!$engagement) {
        wp_die('Engagement letter not found', 'Not Found', ['response' => 404]);
    }
    
    // Log access
    el_log_document_access($engagement_id, $token);
    
    // Token is valid - allow page to render
    // Page template should check for this and display document
}
add_action('template_redirect', 'el_handle_secure_view');

// ============================================
// ACCESS LOGGING (GDPR/Audit Trail)
// ============================================

/**
 * Logs document access for audit trail
 * 
 * @param int    $engagement_id Engagement letter post ID
 * @param string $token         Access token used
 */
function el_log_document_access($engagement_id, $token) {
    $access_log = el_get_meta($engagement_id, 'access_log', []);
    
    $access_log[] = [
        'timestamp' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'token' => substr($token, 0, 10) . '...', // Partial token for security
    ];
    
    // Keep only last 50 entries
    if (count($access_log) > 50) {
        $access_log = array_slice($access_log, -50);
    }
    
    el_set_meta($engagement_id, 'access_log', $access_log);
}

/**
 * Retrieves access log
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return array Access log entries
 */
function el_get_access_log($engagement_id) {
    return el_get_meta($engagement_id, 'access_log', []);
}

// ============================================
// EMAIL NOTIFICATIONS
// ============================================

/**
 * Sends signature request email to client
 * 
 * @param int    $engagement_id Engagement letter post ID
 * @param string $client_email  Client email address
 * @return bool True if sent successfully
 */
function el_send_signature_request($engagement_id, $client_email) {
    $view_url = el_generate_view_url($engagement_id);
    $engagement = el_get_engagement_letter($engagement_id);
    
    $subject = 'Please review and sign your engagement letter - ' . $engagement['reference'];
    
    $message = '<html><body>';
    $message .= '<h2>Engagement Letter Ready for Signature</h2>';
    $message .= '<p>Your engagement letter is ready for review and signature.</p>';
    $message .= '<p><strong>Reference:</strong> ' . esc_html($engagement['reference']) . '</p>';
    $message .= '<p><a href="' . esc_url($view_url) . '" style="background: ' . EL_COLOR_PRIMARY . '; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin: 20px 0;">View & Sign Document</a></p>';
    $message .= '<p style="font-size: 13px; color: #666;">This link will expire in 72 hours.</p>';
    $message .= '</body></html>';
    
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    
    $sent = wp_mail($client_email, $subject, $message, $headers);
    
    if ($sent) {
        el_set_meta($engagement_id, 'signature_request_sent', current_time('mysql'));
        
        if (EL_DEBUG_MODE) {
            el_log('Signature request sent to ' . $client_email, 'info');
        }
    }
    
    return $sent;
}

/**
 * Sends signature confirmation email
 * 
 * @param int $engagement_id Engagement letter post ID
 * @return bool True if sent successfully
 */
function el_send_signature_confirmation($engagement_id) {
    $engagement = el_get_engagement_letter($engagement_id);
    $form_data = $engagement['form_data'];
    $client_email = $form_data['email'] ?? '';
    
    if (!$client_email) {
        return false;
    }
    
    $subject = 'Engagement letter signed - ' . $engagement['reference'];
    
    $message = '<html><body>';
    $message .= '<h2>Thank You for Signing</h2>';
    $message .= '<p>Your engagement letter has been successfully signed.</p>';
    $message .= '<p><strong>Reference:</strong> ' . esc_html($engagement['reference']) . '</p>';
    $message .= '<p>We will contact you shortly regarding next steps.</p>';
    $message .= '</body></html>';
    
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    
    return wp_mail($client_email, $subject, $message, $headers);
}

// Hook to send confirmation after signature
add_action('el_signature_processed', 'el_send_signature_confirmation', 10, 1);

// Log module loaded
if (EL_DEBUG_MODE) {
    el_log('Signature collection module loaded successfully', 'info');
}