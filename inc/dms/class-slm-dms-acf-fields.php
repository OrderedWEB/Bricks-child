<?php
/**
 * SLM DMS ACF Fields
 * 
 * Register ACF field groups for:
 * - Document metadata
 * - Folder settings
 * - Envelope configuration
 * - Share link settings
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class SLM_DMS_ACF_Fields {
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('acf/init', [__CLASS__, 'register_fields']);
    }
    
    /**
     * Register all field groups
     */
    public static function register_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        self::register_document_fields();
        self::register_folder_fields();
        self::register_envelope_fields();
        self::register_share_link_fields();
    }
    
    /**
     * Document fields
     */
    private static function register_document_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_document',
            'title' => __('Document Details', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_doc_description',
                    'label' => __('Description', 'flavor'),
                    'name' => '_slm_doc_description',
                    'type' => 'textarea',
                    'instructions' => __('Brief description of the document content.', 'flavor'),
                    'rows' => 3,
                ],
                [
                    'key' => 'field_slm_doc_case',
                    'label' => __('Associated Case', 'flavor'),
                    'name' => '_slm_case_id',
                    'type' => 'post_object',
                    'instructions' => __('Link this document to a client case.', 'flavor'),
                    'post_type' => ['slm_case'],
                    'return_format' => 'id',
                    'allow_null' => 1,
                ],
                [
                    'key' => 'field_slm_doc_folder',
                    'label' => __('Folder', 'flavor'),
                    'name' => '_slm_folder_id',
                    'type' => 'post_object',
                    'instructions' => __('Organize document into a folder.', 'flavor'),
                    'post_type' => ['slm_folder'],
                    'return_format' => 'id',
                    'allow_null' => 1,
                ],
                [
                    'key' => 'field_slm_doc_client_visible',
                    'label' => __('Client Visibility', 'flavor'),
                    'name' => '_slm_client_visible',
                    'type' => 'true_false',
                    'instructions' => __('Make visible in client portal.', 'flavor'),
                    'default_value' => 1,
                    'ui' => 1,
                ],
                [
                    'key' => 'field_slm_doc_download_allowed',
                    'label' => __('Allow Download', 'flavor'),
                    'name' => '_slm_download_allowed',
                    'type' => 'true_false',
                    'instructions' => __('Allow clients to download this document.', 'flavor'),
                    'default_value' => 0,
                    'ui' => 1,
                ],
                [
                    'key' => 'field_slm_doc_requires_signature',
                    'label' => __('Requires Signature', 'flavor'),
                    'name' => '_slm_requires_signature',
                    'type' => 'true_false',
                    'instructions' => __('This document requires client signature.', 'flavor'),
                    'default_value' => 0,
                    'ui' => 1,
                ],
                [
                    'key' => 'field_slm_doc_internal_notes',
                    'label' => __('Internal Notes', 'flavor'),
                    'name' => '_slm_internal_notes',
                    'type' => 'textarea',
                    'instructions' => __('Notes visible only to staff (not shown to clients).', 'flavor'),
                    'rows' => 2,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_document',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
        ]);
        
        // Document file info (read-only display)
        acf_add_local_field_group([
            'key' => 'group_slm_document_file',
            'title' => __('File Information', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_doc_file_info',
                    'label' => '',
                    'name' => '_slm_file_info_display',
                    'type' => 'message',
                    'message' => __('File information is managed by the system and displayed in the Document Preview meta box.', 'flavor'),
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_document',
                    ],
                ],
            ],
            'menu_order' => 10,
            'position' => 'side',
            'style' => 'default',
        ]);
    }
    
    /**
     * Folder fields
     */
    private static function register_folder_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_folder',
            'title' => __('Folder Settings', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_folder_case',
                    'label' => __('Associated Case', 'flavor'),
                    'name' => '_slm_case_id',
                    'type' => 'post_object',
                    'instructions' => __('Link this folder to a specific case.', 'flavor'),
                    'post_type' => ['slm_case'],
                    'return_format' => 'id',
                    'allow_null' => 1,
                ],
                [
                    'key' => 'field_slm_folder_parent',
                    'label' => __('Parent Folder', 'flavor'),
                    'name' => '_slm_parent_folder',
                    'type' => 'post_object',
                    'instructions' => __('Nest this folder inside another folder.', 'flavor'),
                    'post_type' => ['slm_folder'],
                    'return_format' => 'id',
                    'allow_null' => 1,
                ],
                [
                    'key' => 'field_slm_folder_color',
                    'label' => __('Folder Color', 'flavor'),
                    'name' => '_slm_folder_color',
                    'type' => 'color_picker',
                    'instructions' => __('Visual color for folder icon.', 'flavor'),
                    'default_value' => '#f59e0b',
                ],
                [
                    'key' => 'field_slm_folder_client_visible',
                    'label' => __('Client Visibility', 'flavor'),
                    'name' => '_slm_client_visible',
                    'type' => 'true_false',
                    'instructions' => __('Show folder in client portal.', 'flavor'),
                    'default_value' => 1,
                    'ui' => 1,
                ],
                [
                    'key' => 'field_slm_folder_sort_order',
                    'label' => __('Sort Order', 'flavor'),
                    'name' => '_slm_sort_order',
                    'type' => 'number',
                    'instructions' => __('Lower numbers appear first.', 'flavor'),
                    'default_value' => 10,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_folder',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
        ]);
    }
    
    /**
     * Envelope fields
     */
    private static function register_envelope_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_envelope',
            'title' => __('Envelope Settings', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_env_case',
                    'label' => __('Associated Case', 'flavor'),
                    'name' => '_slm_case_id',
                    'type' => 'post_object',
                    'post_type' => ['slm_case'],
                    'return_format' => 'id',
                    'allow_null' => 1,
                ],
                [
                    'key' => 'field_slm_env_document',
                    'label' => __('Document', 'flavor'),
                    'name' => '_slm_document_id',
                    'type' => 'post_object',
                    'instructions' => __('Document to be signed.', 'flavor'),
                    'post_type' => ['slm_document'],
                    'return_format' => 'id',
                    'required' => 1,
                ],
                [
                    'key' => 'field_slm_env_signing_mode',
                    'label' => __('Signing Mode', 'flavor'),
                    'name' => '_slm_signing_mode',
                    'type' => 'select',
                    'choices' => [
                        'sequential' => __('Sequential (one at a time)', 'flavor'),
                        'parallel' => __('Parallel (all at once)', 'flavor'),
                    ],
                    'default_value' => 'sequential',
                ],
                [
                    'key' => 'field_slm_env_expiry',
                    'label' => __('Expiry Date', 'flavor'),
                    'name' => '_slm_expiry_date',
                    'type' => 'date_time_picker',
                    'instructions' => __('When the signing request expires.', 'flavor'),
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                ],
                [
                    'key' => 'field_slm_env_message',
                    'label' => __('Message to Signers', 'flavor'),
                    'name' => '_slm_message',
                    'type' => 'textarea',
                    'instructions' => __('Optional message included in signing request emails.', 'flavor'),
                    'rows' => 3,
                ],
                [
                    'key' => 'field_slm_env_send_reminders',
                    'label' => __('Send Reminders', 'flavor'),
                    'name' => '_slm_send_reminders',
                    'type' => 'true_false',
                    'instructions' => __('Send automatic reminders at 7, 3, and 1 day before expiry.', 'flavor'),
                    'default_value' => 1,
                    'ui' => 1,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_envelope',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
        ]);
        
        // Signers repeater
        acf_add_local_field_group([
            'key' => 'group_slm_envelope_signers',
            'title' => __('Signers', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_env_signers',
                    'label' => __('Signers', 'flavor'),
                    'name' => '_slm_signers',
                    'type' => 'repeater',
                    'instructions' => __('Add signers in the order they should sign (for sequential mode).', 'flavor'),
                    'min' => 1,
                    'max' => 10,
                    'layout' => 'block',
                    'button_label' => __('Add Signer', 'flavor'),
                    'sub_fields' => [
                        [
                            'key' => 'field_slm_signer_type',
                            'label' => __('Signer Type', 'flavor'),
                            'name' => 'type',
                            'type' => 'select',
                            'choices' => [
                                'client' => __('Client (WordPress User)', 'flavor'),
                                'external' => __('External (Email Only)', 'flavor'),
                            ],
                            'default_value' => 'client',
                            'wrapper' => ['width' => '30'],
                        ],
                        [
                            'key' => 'field_slm_signer_user',
                            'label' => __('Client User', 'flavor'),
                            'name' => 'user_id',
                            'type' => 'user',
                            'role' => ['customer', 'subscriber'],
                            'return_format' => 'id',
                            'allow_null' => 1,
                            'conditional_logic' => [
                                [
                                    [
                                        'field' => 'field_slm_signer_type',
                                        'operator' => '==',
                                        'value' => 'client',
                                    ],
                                ],
                            ],
                            'wrapper' => ['width' => '70'],
                        ],
                        [
                            'key' => 'field_slm_signer_name',
                            'label' => __('Full Name', 'flavor'),
                            'name' => 'name',
                            'type' => 'text',
                            'conditional_logic' => [
                                [
                                    [
                                        'field' => 'field_slm_signer_type',
                                        'operator' => '==',
                                        'value' => 'external',
                                    ],
                                ],
                            ],
                            'wrapper' => ['width' => '35'],
                        ],
                        [
                            'key' => 'field_slm_signer_email',
                            'label' => __('Email', 'flavor'),
                            'name' => 'email',
                            'type' => 'email',
                            'conditional_logic' => [
                                [
                                    [
                                        'field' => 'field_slm_signer_type',
                                        'operator' => '==',
                                        'value' => 'external',
                                    ],
                                ],
                            ],
                            'wrapper' => ['width' => '35'],
                        ],
                        [
                            'key' => 'field_slm_signer_role',
                            'label' => __('Role/Title', 'flavor'),
                            'name' => 'role',
                            'type' => 'text',
                            'placeholder' => __('e.g., Client, Witness, Notary', 'flavor'),
                            'wrapper' => ['width' => '30'],
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_envelope',
                    ],
                ],
            ],
            'menu_order' => 5,
            'position' => 'normal',
            'style' => 'default',
        ]);
    }
    
    /**
     * Share link fields
     */
    private static function register_share_link_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_share_link',
            'title' => __('Share Link Settings', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_share_document',
                    'label' => __('Document', 'flavor'),
                    'name' => '_slm_document_id',
                    'type' => 'post_object',
                    'post_type' => ['slm_document'],
                    'return_format' => 'id',
                    'required' => 1,
                ],
                [
                    'key' => 'field_slm_share_expiry',
                    'label' => __('Expiry', 'flavor'),
                    'name' => '_slm_expiry_date',
                    'type' => 'date_time_picker',
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                ],
                [
                    'key' => 'field_slm_share_max_views',
                    'label' => __('Max Views', 'flavor'),
                    'name' => '_slm_max_views',
                    'type' => 'number',
                    'instructions' => __('Leave empty for unlimited.', 'flavor'),
                    'min' => 1,
                ],
                [
                    'key' => 'field_slm_share_password',
                    'label' => __('Password Protected', 'flavor'),
                    'name' => '_slm_password_protected',
                    'type' => 'true_false',
                    'ui' => 1,
                ],
                [
                    'key' => 'field_slm_share_download',
                    'label' => __('Allow Download', 'flavor'),
                    'name' => '_slm_download_allowed',
                    'type' => 'true_false',
                    'ui' => 1,
                ],
                [
                    'key' => 'field_slm_share_notify_view',
                    'label' => __('Notify on View', 'flavor'),
                    'name' => '_slm_notify_on_view',
                    'type' => 'true_false',
                    'instructions' => __('Send email when document is viewed.', 'flavor'),
                    'ui' => 1,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_share_link',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
        ]);
    }
}
