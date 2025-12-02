<?php
/**
 * SLM DMS Email Templates
 * 
 * Additional email templates for DMS functionality.
 * These should be merged into SLM_Email_Templates class.
 * 
 * Add these templates to the get_template() switch statement:
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

/**
 * INSTRUCTIONS:
 * 
 * Add these cases to the get_template() method in class-slm-email-templates.php
 * inside the switch($template_name) statement.
 */

// ============================================================================
// TEMPLATE: signing-request
// Sent when a signer is requested to sign a document
// ============================================================================

/*
case 'signing-request':
    return [
        'subject' => sprintf(__('Please Sign: %s', 'flavor'), $variables['document_name'] ?? __('Document', 'flavor')),
        'body' => self::wrap_template(
            '<h1 style="color: #1e3a5f; font-size: 24px; margin: 0 0 16px;">Signature Requested</h1>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Hello ' . esc_html($variables['first_name'] ?? __('there', 'flavor')) . ',
            </p>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                You have been requested to sign the following document:
            </p>
            
            ' . self::info_box([
                __('Document', 'flavor') => $variables['document_name'] ?? '',
                __('From', 'flavor') => $variables['sender_name'] ?? get_option('slm_firm_name', 'Studio Legale Metta'),
                __('Expires', 'flavor') => isset($variables['expiry_date']) ? date_i18n(get_option('date_format'), strtotime($variables['expiry_date'])) : '',
            ]) . '
            
            ' . (!empty($variables['message']) ? '
            <div style="background: #f9fafb; border-left: 4px solid #2563eb; padding: 16px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                <p style="margin: 0; font-style: italic; color: #4b5563;">"' . esc_html($variables['message']) . '"</p>
            </div>
            ' : '') . '
            
            <p style="font-size: 16px; color: #374151; margin: 20px 0;">
                Please click the button below to review and sign the document:
            </p>
            
            ' . self::button(__('Review & Sign Document', 'flavor'), $variables['sign_url'] ?? '#') . '
            
            ' . self::warning_box(__('This signing request will expire on %s. Please complete your signature before this date.', 'flavor'), isset($variables['expiry_date']) ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($variables['expiry_date'])) : __('the expiry date', 'flavor')) . '
            
            <p style="font-size: 14px; color: #6b7280; margin: 20px 0 0;">
                ' . __('If you have questions about this document, please contact the sender directly.', 'flavor') . '
            </p>',
            __('If the button above doesn\'t work, copy and paste this link into your browser:', 'flavor') . ' ' . ($variables['sign_url'] ?? '')
        ),
    ];
*/

// ============================================================================
// TEMPLATE: signing-complete
// Sent to all parties when envelope is fully signed
// ============================================================================

/*
case 'signing-complete':
    return [
        'subject' => sprintf(__('Signing Complete: %s', 'flavor'), $variables['document_name'] ?? __('Document', 'flavor')),
        'body' => self::wrap_template(
            '<h1 style="color: #1e3a5f; font-size: 24px; margin: 0 0 16px;">All Signatures Complete</h1>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Hello ' . esc_html($variables['first_name'] ?? __('there', 'flavor')) . ',
            </p>
            
            ' . self::success_badge(__('All parties have signed the document.', 'flavor')) . '
            
            <p style="font-size: 16px; color: #374151; margin: 20px 0;">
                The document <strong>"' . esc_html($variables['document_name'] ?? '') . '"</strong> has been signed by all parties.
            </p>
            
            ' . self::info_box([
                __('Document', 'flavor') => $variables['document_name'] ?? '',
                __('Completed', 'flavor') => isset($variables['completed_date']) ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($variables['completed_date'])) : date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
                __('Signers', 'flavor') => $variables['signer_count'] ?? '',
            ]) . '
            
            <p style="font-size: 16px; color: #374151; margin: 20px 0;">
                A certificate of signing has been attached to the document for your records.
            </p>
            
            ' . (!empty($variables['portal_url']) ? self::button(__('View in Portal', 'flavor'), $variables['portal_url']) : '') . '
            
            <p style="font-size: 14px; color: #6b7280; margin: 20px 0 0;">
                ' . __('Please retain a copy of this signed document for your records.', 'flavor') . '
            </p>'
        ),
    ];
*/

// ============================================================================
// TEMPLATE: signing-reminder
// Reminder sent to signers before expiry
// ============================================================================

/*
case 'signing-reminder':
    return [
        'subject' => sprintf(__('Reminder: Please Sign %s (%d days remaining)', 'flavor'), $variables['document_name'] ?? __('Document', 'flavor'), $variables['days_remaining'] ?? 0),
        'body' => self::wrap_template(
            '<h1 style="color: #1e3a5f; font-size: 24px; margin: 0 0 16px;">Signature Reminder</h1>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Hello ' . esc_html($variables['first_name'] ?? __('there', 'flavor')) . ',
            </p>
            
            ' . self::warning_box(sprintf(__('You have a document waiting for your signature that expires in %d day(s).', 'flavor'), $variables['days_remaining'] ?? 0)) . '
            
            ' . self::info_box([
                __('Document', 'flavor') => $variables['document_name'] ?? '',
                __('Expires', 'flavor') => isset($variables['expiry_date']) ? date_i18n(get_option('date_format'), strtotime($variables['expiry_date'])) : '',
            ]) . '
            
            <p style="font-size: 16px; color: #374151; margin: 20px 0;">
                Please complete your signature before the expiry date.
            </p>
            
            ' . self::button(__('Sign Now', 'flavor'), $variables['sign_url'] ?? '#') . '
            
            <p style="font-size: 14px; color: #6b7280; margin: 20px 0 0;">
                ' . __('If you have already signed or believe you received this in error, please disregard this reminder.', 'flavor') . '
            </p>',
            __('If the button above doesn\'t work, copy and paste this link into your browser:', 'flavor') . ' ' . ($variables['sign_url'] ?? '')
        ),
    ];
*/

// ============================================================================
// TEMPLATE: signing-declined
// Sent when a signer declines to sign
// ============================================================================

/*
case 'signing-declined':
    return [
        'subject' => sprintf(__('Signing Declined: %s', 'flavor'), $variables['document_name'] ?? __('Document', 'flavor')),
        'body' => self::wrap_template(
            '<h1 style="color: #1e3a5f; font-size: 24px; margin: 0 0 16px;">Signing Declined</h1>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Hello ' . esc_html($variables['first_name'] ?? __('there', 'flavor')) . ',
            </p>
            
            ' . self::danger_box(__('A signer has declined to sign the document.', 'flavor')) . '
            
            ' . self::info_box([
                __('Document', 'flavor') => $variables['document_name'] ?? '',
                __('Declined By', 'flavor') => $variables['decliner_name'] ?? '',
                __('Reason', 'flavor') => $variables['decline_reason'] ?? __('No reason provided', 'flavor'),
            ]) . '
            
            <p style="font-size: 16px; color: #374151; margin: 20px 0;">
                ' . __('Please contact the signer directly if you need to discuss this matter.', 'flavor') . '
            </p>'
        ),
    ];
*/

// ============================================================================
// TEMPLATE: document-uploaded
// Sent when a new document is uploaded to a case
// ============================================================================

/*
case 'document-uploaded':
    return [
        'subject' => sprintf(__('New Document: %s', 'flavor'), $variables['document_name'] ?? __('Document', 'flavor')),
        'body' => self::wrap_template(
            '<h1 style="color: #1e3a5f; font-size: 24px; margin: 0 0 16px;">New Document Added</h1>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Hello ' . esc_html($variables['first_name'] ?? __('there', 'flavor')) . ',
            </p>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                A new document has been added to your case:
            </p>
            
            ' . self::info_box([
                __('Document', 'flavor') => $variables['document_name'] ?? '',
                __('Category', 'flavor') => $variables['category'] ?? '',
                __('Uploaded By', 'flavor') => $variables['uploaded_by'] ?? '',
                __('Date', 'flavor') => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            ]) . '
            
            ' . self::button(__('View Document', 'flavor'), $variables['portal_url'] ?? '#') . '
            
            <p style="font-size: 14px; color: #6b7280; margin: 20px 0 0;">
                ' . __('Log in to your client portal to view and download this document.', 'flavor') . '
            </p>',
            __('If the button above doesn\'t work, copy and paste this link into your browser:', 'flavor') . ' ' . ($variables['portal_url'] ?? '')
        ),
    ];
*/

// ============================================================================
// TEMPLATE: document-viewed
// Sent to document owner when someone views their shared document
// ============================================================================

/*
case 'document-viewed':
    return [
        'subject' => sprintf(__('Document Viewed: %s', 'flavor'), $variables['document_name'] ?? __('Document', 'flavor')),
        'body' => self::wrap_template(
            '<h1 style="color: #1e3a5f; font-size: 24px; margin: 0 0 16px;">Document Viewed</h1>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Hello ' . esc_html($variables['first_name'] ?? __('there', 'flavor')) . ',
            </p>
            
            <p style="font-size: 16px; color: #374151; margin: 0 0 20px;">
                Your shared document has been viewed:
            </p>
            
            ' . self::info_box([
                __('Document', 'flavor') => $variables['document_name'] ?? '',
                __('Viewed By', 'flavor') => $variables['viewer_name'] ?? __('Recipient', 'flavor'),
                __('Date', 'flavor') => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            ]) . '
            
            <p style="font-size: 14px; color: #6b7280; margin: 20px 0 0;">
                ' . __('This notification was sent because you enabled view notifications for this share link.', 'flavor') . '
            </p>'
        ),
    ];
*/
