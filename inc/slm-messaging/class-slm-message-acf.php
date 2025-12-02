<?php
/**
 * SLM Message ACF Fields
 *
 * Registers ACF fields for the message CPT.
 * Read-only display for immutability.
 *
 * @package SLM_Messaging
 */

defined('ABSPATH') || exit;

class SLM_Message_ACF {

    private static $initialized = false;

    /**
     * Initialize.
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        add_action('acf/init', [__CLASS__, 'register_field_groups']);
    }

    /**
     * Register ACF field groups.
     */
    public static function register_field_groups() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        // Message Details
        acf_add_local_field_group([
            'key' => 'group_slm_message_details',
            'title' => __('Message Details', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_msg_sender',
                    'label' => __('Sender', 'flavor'),
                    'name' => '_slm_sender',
                    'type' => 'user',
                    'instructions' => '',
                    'required' => 0,
                    'readonly' => 1,
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_slm_msg_related_case',
                    'label' => __('Related Case', 'flavor'),
                    'name' => '_slm_related_case',
                    'type' => 'post_object',
                    'instructions' => '',
                    'required' => 1,
                    'readonly' => 1,
                    'post_type' => ['slm_case'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_slm_msg_related_task',
                    'label' => __('Related Task', 'flavor'),
                    'name' => '_slm_related_task',
                    'type' => 'post_object',
                    'instructions' => __('Optional: Task this message relates to', 'flavor'),
                    'required' => 0,
                    'readonly' => 1,
                    'post_type' => ['slm_task_instance'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_slm_msg_related_document',
                    'label' => __('Related Document', 'flavor'),
                    'name' => '_slm_related_document',
                    'type' => 'post_object',
                    'instructions' => __('Optional: Document this message relates to', 'flavor'),
                    'required' => 0,
                    'readonly' => 1,
                    'post_type' => ['slm_document'],
                    'return_format' => 'id',
                ],
                [
                    'key' => 'field_slm_msg_content',
                    'label' => __('Message Content', 'flavor'),
                    'name' => '_slm_message_content',
                    'type' => 'wysiwyg',
                    'instructions' => '',
                    'required' => 1,
                    'readonly' => 1,
                    'tabs' => 'visual',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                ],
                [
                    'key' => 'field_slm_msg_timestamp',
                    'label' => __('Timestamp', 'flavor'),
                    'name' => '_slm_message_timestamp',
                    'type' => 'date_time_picker',
                    'instructions' => '',
                    'required' => 0,
                    'readonly' => 1,
                    'display_format' => 'F j, Y g:i a',
                    'return_format' => 'Y-m-d H:i:s',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_message',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ]);

        // Message Attachments
        acf_add_local_field_group([
            'key' => 'group_slm_message_attachments',
            'title' => __('Attachments', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_msg_attachment_type',
                    'label' => __('Attachment Type', 'flavor'),
                    'name' => '_slm_attachment_type',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'readonly' => 1,
                    'choices' => [
                        'none' => __('None', 'flavor'),
                        'upload' => __('Uploaded Files', 'flavor'),
                        'link' => __('Linked Documents', 'flavor'),
                    ],
                    'default_value' => 'none',
                ],
                [
                    'key' => 'field_slm_msg_uploaded_attachments',
                    'label' => __('Uploaded Attachments', 'flavor'),
                    'name' => '_slm_uploaded_attachments',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'readonly' => 1,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'field_slm_msg_attachment_type',
                                'operator' => '==',
                                'value' => 'upload',
                            ],
                        ],
                    ],
                    'layout' => 'table',
                    'button_label' => '',
                    'sub_fields' => [
                        [
                            'key' => 'field_slm_msg_att_doc_id',
                            'label' => __('Document', 'flavor'),
                            'name' => 'document_id',
                            'type' => 'post_object',
                            'post_type' => ['slm_document', 'attachment'],
                            'return_format' => 'id',
                        ],
                        [
                            'key' => 'field_slm_msg_att_filename',
                            'label' => __('Original Filename', 'flavor'),
                            'name' => 'original_filename',
                            'type' => 'text',
                        ],
                    ],
                ],
                [
                    'key' => 'field_slm_msg_linked_documents',
                    'label' => __('Linked Documents', 'flavor'),
                    'name' => '_slm_linked_documents',
                    'type' => 'relationship',
                    'instructions' => '',
                    'required' => 0,
                    'readonly' => 1,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'field_slm_msg_attachment_type',
                                'operator' => '==',
                                'value' => 'link',
                            ],
                        ],
                    ],
                    'post_type' => ['slm_document'],
                    'return_format' => 'id',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_message',
                    ],
                ],
            ],
            'menu_order' => 1,
            'position' => 'normal',
            'style' => 'default',
        ]);

        // Read Status
        acf_add_local_field_group([
            'key' => 'group_slm_message_read_status',
            'title' => __('Read Status', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_msg_read_by',
                    'label' => __('Read By', 'flavor'),
                    'name' => '_slm_read_by',
                    'type' => 'repeater',
                    'instructions' => __('Users who have read this message', 'flavor'),
                    'required' => 0,
                    'readonly' => 1,
                    'layout' => 'table',
                    'button_label' => '',
                    'sub_fields' => [
                        [
                            'key' => 'field_slm_msg_read_user',
                            'label' => __('User', 'flavor'),
                            'name' => 'user_id',
                            'type' => 'user',
                            'return_format' => 'id',
                        ],
                        [
                            'key' => 'field_slm_msg_read_timestamp',
                            'label' => __('Read At', 'flavor'),
                            'name' => 'read_timestamp',
                            'type' => 'date_time_picker',
                            'display_format' => 'F j, Y g:i a',
                            'return_format' => 'Y-m-d H:i:s',
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_message',
                    ],
                ],
            ],
            'menu_order' => 2,
            'position' => 'side',
            'style' => 'default',
        ]);

        // Email Tracking
        acf_add_local_field_group([
            'key' => 'group_slm_message_email_tracking',
            'title' => __('Email Notifications', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_msg_email_sent_to',
                    'label' => __('Emails Sent', 'flavor'),
                    'name' => '_slm_email_sent_to',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'readonly' => 1,
                    'layout' => 'table',
                    'button_label' => '',
                    'sub_fields' => [
                        [
                            'key' => 'field_slm_msg_email_user',
                            'label' => __('Recipient', 'flavor'),
                            'name' => 'user_id',
                            'type' => 'user',
                            'return_format' => 'id',
                        ],
                        [
                            'key' => 'field_slm_msg_email_sent_at',
                            'label' => __('Sent At', 'flavor'),
                            'name' => 'sent_at',
                            'type' => 'date_time_picker',
                            'display_format' => 'F j, Y g:i a',
                            'return_format' => 'Y-m-d H:i:s',
                        ],
                        [
                            'key' => 'field_slm_msg_email_type',
                            'label' => __('Type', 'flavor'),
                            'name' => 'type',
                            'type' => 'select',
                            'choices' => [
                                'full' => __('Full Content', 'flavor'),
                                'link_only' => __('Link Only', 'flavor'),
                            ],
                        ],
                    ],
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_message',
                    ],
                ],
            ],
            'menu_order' => 3,
            'position' => 'side',
            'style' => 'default',
        ]);

        // System Message Flag
        acf_add_local_field_group([
            'key' => 'group_slm_message_flags',
            'title' => __('Flags', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_msg_is_system',
                    'label' => __('System Message', 'flavor'),
                    'name' => '_slm_is_system_message',
                    'type' => 'true_false',
                    'instructions' => __('Auto-generated by system actions', 'flavor'),
                    'required' => 0,
                    'readonly' => 1,
                    'ui' => 1,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'slm_message',
                    ],
                ],
            ],
            'menu_order' => 4,
            'position' => 'side',
            'style' => 'default',
        ]);
    }
}
