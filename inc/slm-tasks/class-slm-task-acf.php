<?php
/**
 * SLM Task ACF Fields
 * 
 * Registers Advanced Custom Fields for task system CPTs.
 * Uses ACF Pro's local JSON feature with PHP fallback.
 * 
 * @package SLM_Tasks
 */

defined('ABSPATH') || exit;

class SLM_Task_ACF {
    
    private static $initialized = false;
    
    public static function init() {
        if (self::$initialized) return;
        self::$initialized = true;
        
        add_action('acf/init', [__CLASS__, 'register_field_groups']);
    }
    
    public static function register_field_groups() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        
        self::register_task_list_fields();
        self::register_task_template_fields();
        self::register_task_instance_fields();
        self::register_case_task_fields();
    }
    
    /**
     * Task List (SOP) Fields
     */
    private static function register_task_list_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_task_list',
            'title' => __('Task List Configuration', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_tl_tasks',
                    'label' => __('Tasks', 'flavor'),
                    'name' => 'tasks',
                    'type' => 'repeater',
                    'instructions' => __('Add tasks to this list in order of execution.', 'flavor'),
                    'required' => 0,
                    'layout' => 'block',
                    'button_label' => __('Add Task', 'flavor'),
                    'sub_fields' => [
                        [
                            'key' => 'field_slm_tl_task_template',
                            'label' => __('Task Template', 'flavor'),
                            'name' => 'task_template',
                            'type' => 'post_object',
                            'post_type' => ['slm_task_template'],
                            'return_format' => 'id',
                            'allow_null' => 0,
                            'required' => 1,
                            'wrapper' => ['width' => '40']
                        ],
                        [
                            'key' => 'field_slm_tl_sequence',
                            'label' => __('Order', 'flavor'),
                            'name' => 'sequence_order',
                            'type' => 'number',
                            'default_value' => 10,
                            'min' => 1,
                            'step' => 10,
                            'wrapper' => ['width' => '10']
                        ],
                        [
                            'key' => 'field_slm_tl_dependencies',
                            'label' => __('Depends On', 'flavor'),
                            'name' => 'dependencies',
                            'type' => 'post_object',
                            'post_type' => ['slm_task_template'],
                            'return_format' => 'id',
                            'allow_null' => 1,
                            'multiple' => 1,
                            'wrapper' => ['width' => '25']
                        ],
                        [
                            'key' => 'field_slm_tl_conditional',
                            'label' => __('Conditional', 'flavor'),
                            'name' => 'is_conditional',
                            'type' => 'true_false',
                            'default_value' => 0,
                            'ui' => 1,
                            'wrapper' => ['width' => '10']
                        ],
                        [
                            'key' => 'field_slm_tl_cond_rules',
                            'label' => __('Condition Rules', 'flavor'),
                            'name' => 'conditional_rules',
                            'type' => 'textarea',
                            'instructions' => __('JSON format conditions', 'flavor'),
                            'rows' => 2,
                            'conditional_logic' => [
                                [['field' => 'field_slm_tl_conditional', 'operator' => '==', 'value' => '1']]
                            ],
                            'wrapper' => ['width' => '15']
                        ],
                    ],
                ],
                [
                    'key' => 'field_slm_tl_description',
                    'label' => __('Description', 'flavor'),
                    'name' => '_slm_task_list_description',
                    'type' => 'textarea',
                    'instructions' => __('Internal description of this task list.', 'flavor'),
                    'rows' => 3
                ],
                [
                    'key' => 'field_slm_tl_applicable_case_types',
                    'label' => __('Applicable Case Types', 'flavor'),
                    'name' => '_slm_applicable_case_types',
                    'type' => 'checkbox',
                    'instructions' => __('Which case types can use this list? Leave empty for all.', 'flavor'),
                    'choices' => self::get_case_type_choices(),
                    'layout' => 'horizontal',
                    'return_format' => 'value'
                ],
            ],
            'location' => [
                [['param' => 'post_type', 'operator' => '==', 'value' => 'slm_task_list']]
            ],
            'style' => 'seamless',
            'position' => 'normal',
            'menu_order' => 0
        ]);
    }
    
    /**
     * Task Template Fields
     */
    private static function register_task_template_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_task_template',
            'title' => __('Task Template Settings', 'flavor'),
            'fields' => [
                // Core Tab
                [
                    'key' => 'field_slm_tt_tab_core',
                    'label' => __('Core Settings', 'flavor'),
                    'type' => 'tab'
                ],
                [
                    'key' => 'field_slm_tt_type',
                    'label' => __('Task Type', 'flavor'),
                    'name' => '_slm_task_type',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => [
                        'checkbox' => __('Checkbox (Simple)', 'flavor'),
                        'upload' => __('Document Upload', 'flavor'),
                        'form' => __('Form Submission', 'flavor'),
                        'payment' => __('Payment', 'flavor'),
                        'signature' => __('Signature', 'flavor'),
                        'external' => __('External Action', 'flavor'),
                    ],
                    'default_value' => 'checkbox',
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_tt_assigned_role',
                    'label' => __('Default Assigned To', 'flavor'),
                    'name' => '_slm_default_assigned_role',
                    'type' => 'select',
                    'choices' => [
                        'client' => __('Client', 'flavor'),
                        'lawyer' => __('Lawyer', 'flavor'),
                    ],
                    'default_value' => 'client',
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_tt_description',
                    'label' => __('Instructions', 'flavor'),
                    'name' => '_slm_task_description',
                    'type' => 'wysiwyg',
                    'instructions' => __('Instructions shown to the user when completing this task.', 'flavor'),
                    'media_upload' => 0,
                    'tabs' => 'visual',
                    'toolbar' => 'basic'
                ],
                
                // Timeline Tab
                [
                    'key' => 'field_slm_tt_tab_timeline',
                    'label' => __('Timeline', 'flavor'),
                    'type' => 'tab'
                ],
                [
                    'key' => 'field_slm_tt_working_days',
                    'label' => __('Working Days (Standard)', 'flavor'),
                    'name' => '_slm_working_days_standard',
                    'type' => 'number',
                    'instructions' => __('Number of working days allowed for this task.', 'flavor'),
                    'default_value' => 5,
                    'min' => 1,
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_tt_fast_override',
                    'label' => __('Fast Tier Override', 'flavor'),
                    'name' => '_slm_working_days_fast_override',
                    'type' => 'number',
                    'instructions' => __('Override for fast tier (optional).', 'flavor'),
                    'min' => 1,
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_tt_expedited_override',
                    'label' => __('Expedited Override', 'flavor'),
                    'name' => '_slm_working_days_expedited_override',
                    'type' => 'number',
                    'instructions' => __('Override for expedited tier (optional).', 'flavor'),
                    'min' => 1,
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_tt_timing_anchor',
                    'label' => __('Calculate Due Date From', 'flavor'),
                    'name' => '_slm_timing_anchor',
                    'type' => 'select',
                    'choices' => [
                        'case_creation' => __('Case Creation', 'flavor'),
                        'previous_task' => __('Previous Task Completion', 'flavor'),
                        'critical_deadline' => __('Critical Deadline (minus days)', 'flavor'),
                        'manual' => __('Manual Date Entry', 'flavor'),
                    ],
                    'default_value' => 'case_creation'
                ],
                
                // Upload Type Settings
                [
                    'key' => 'field_slm_tt_tab_upload',
                    'label' => __('Upload Settings', 'flavor'),
                    'type' => 'tab',
                    'conditional_logic' => [
                        [['field' => 'field_slm_tt_type', 'operator' => '==', 'value' => 'upload']]
                    ]
                ],
                [
                    'key' => 'field_slm_tt_allowed_types',
                    'label' => __('Allowed File Types', 'flavor'),
                    'name' => '_slm_allowed_file_types',
                    'type' => 'checkbox',
                    'choices' => [
                        'pdf' => 'PDF',
                        'jpg' => 'JPG',
                        'jpeg' => 'JPEG',
                        'png' => 'PNG',
                        'docx' => 'DOCX',
                        'doc' => 'DOC',
                        'xlsx' => 'XLSX',
                        'xls' => 'XLS',
                    ],
                    'default_value' => ['pdf', 'jpg', 'png'],
                    'layout' => 'horizontal',
                    'wrapper' => ['width' => '60']
                ],
                [
                    'key' => 'field_slm_tt_max_size',
                    'label' => __('Max File Size (MB)', 'flavor'),
                    'name' => '_slm_max_file_size_mb',
                    'type' => 'number',
                    'default_value' => 10,
                    'min' => 1,
                    'max' => 100,
                    'wrapper' => ['width' => '20']
                ],
                [
                    'key' => 'field_slm_tt_doc_category',
                    'label' => __('Document Category', 'flavor'),
                    'name' => '_slm_document_category',
                    'type' => 'text',
                    'instructions' => __('e.g., "Passport", "Birth Certificate"', 'flavor'),
                    'wrapper' => ['width' => '20']
                ],
                
                // Form Type Settings
                [
                    'key' => 'field_slm_tt_tab_form',
                    'label' => __('Form Settings', 'flavor'),
                    'type' => 'tab',
                    'conditional_logic' => [
                        [['field' => 'field_slm_tt_type', 'operator' => '==', 'value' => 'form']]
                    ]
                ],
                [
                    'key' => 'field_slm_tt_gravity_form',
                    'label' => __('Gravity Form', 'flavor'),
                    'name' => '_slm_gravity_form_id',
                    'type' => 'select',
                    'choices' => self::get_gravity_forms_choices(),
                    'allow_null' => 1,
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_tt_completion_trigger',
                    'label' => __('Task Completes', 'flavor'),
                    'name' => '_slm_completion_trigger',
                    'type' => 'select',
                    'choices' => [
                        'on_submit' => __('On Form Submit', 'flavor'),
                        'on_approval' => __('On Lawyer Approval', 'flavor'),
                    ],
                    'default_value' => 'on_submit',
                    'wrapper' => ['width' => '50']
                ],
                
                // Payment Type Settings
                [
                    'key' => 'field_slm_tt_tab_payment',
                    'label' => __('Payment Settings', 'flavor'),
                    'type' => 'tab',
                    'conditional_logic' => [
                        [['field' => 'field_slm_tt_type', 'operator' => '==', 'value' => 'payment']]
                    ]
                ],
                [
                    'key' => 'field_slm_tt_payment_type',
                    'label' => __('Payment Type', 'flavor'),
                    'name' => '_slm_payment_type',
                    'type' => 'select',
                    'choices' => [
                        'invoice' => __('From Invoice', 'flavor'),
                        'fixed' => __('Fixed Amount', 'flavor'),
                        'variable' => __('Variable (set at creation)', 'flavor'),
                    ],
                    'default_value' => 'fixed',
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_tt_fixed_amount',
                    'label' => __('Fixed Amount', 'flavor'),
                    'name' => '_slm_fixed_amount',
                    'type' => 'number',
                    'min' => 0,
                    'step' => 0.01,
                    'prepend' => '€',
                    'conditional_logic' => [
                        [['field' => 'field_slm_tt_payment_type', 'operator' => '==', 'value' => 'fixed']]
                    ],
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_tt_currency',
                    'label' => __('Currency', 'flavor'),
                    'name' => '_slm_currency',
                    'type' => 'select',
                    'choices' => ['EUR' => 'EUR €', 'GBP' => 'GBP £', 'USD' => 'USD $'],
                    'default_value' => 'EUR',
                    'wrapper' => ['width' => '34']
                ],
                [
                    'key' => 'field_slm_tt_stripe_enabled',
                    'label' => __('Online Payment', 'flavor'),
                    'name' => '_slm_stripe_enabled',
                    'type' => 'true_false',
                    'message' => __('Enable Stripe payment', 'flavor'),
                    'default_value' => 1,
                    'ui' => 1
                ],
                [
                    'key' => 'field_slm_tt_payment_instructions',
                    'label' => __('Bank Transfer Instructions', 'flavor'),
                    'name' => '_slm_payment_instructions',
                    'type' => 'textarea',
                    'rows' => 4
                ],
                
                // Signature Type Settings
                [
                    'key' => 'field_slm_tt_tab_signature',
                    'label' => __('Signature Settings', 'flavor'),
                    'type' => 'tab',
                    'conditional_logic' => [
                        [['field' => 'field_slm_tt_type', 'operator' => '==', 'value' => 'signature']]
                    ]
                ],
                [
                    'key' => 'field_slm_tt_doc_template',
                    'label' => __('Document Template', 'flavor'),
                    'name' => '_slm_document_template_id',
                    'type' => 'post_object',
                    'post_type' => ['slm_doc_template'],
                    'return_format' => 'id',
                    'allow_null' => 1
                ],
                [
                    'key' => 'field_slm_tt_require_initials',
                    'label' => __('Require Initials', 'flavor'),
                    'name' => '_slm_require_initials',
                    'type' => 'true_false',
                    'message' => __('Require initials on each page', 'flavor'),
                    'ui' => 1,
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_tt_witness_required',
                    'label' => __('Witness Required', 'flavor'),
                    'name' => '_slm_witness_required',
                    'type' => 'true_false',
                    'message' => __('Witness signature required', 'flavor'),
                    'ui' => 1,
                    'wrapper' => ['width' => '50']
                ],
                
                // External Type Settings
                [
                    'key' => 'field_slm_tt_tab_external',
                    'label' => __('External Settings', 'flavor'),
                    'type' => 'tab',
                    'conditional_logic' => [
                        [['field' => 'field_slm_tt_type', 'operator' => '==', 'value' => 'external']]
                    ]
                ],
                [
                    'key' => 'field_slm_tt_external_instructions',
                    'label' => __('External Instructions', 'flavor'),
                    'name' => '_slm_external_instructions',
                    'type' => 'wysiwyg',
                    'media_upload' => 0,
                    'tabs' => 'visual',
                    'toolbar' => 'basic'
                ],
                [
                    'key' => 'field_slm_tt_external_url',
                    'label' => __('External URL', 'flavor'),
                    'name' => '_slm_external_url',
                    'type' => 'url'
                ],
                [
                    'key' => 'field_slm_tt_require_proof',
                    'label' => __('Require Proof', 'flavor'),
                    'name' => '_slm_require_proof',
                    'type' => 'true_false',
                    'message' => __('Require proof upload (receipt, screenshot)', 'flavor'),
                    'ui' => 1,
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_tt_require_verification',
                    'label' => __('Lawyer Verification', 'flavor'),
                    'name' => '_slm_require_lawyer_verification',
                    'type' => 'true_false',
                    'message' => __('Require lawyer to verify completion', 'flavor'),
                    'ui' => 1,
                    'wrapper' => ['width' => '50']
                ],
            ],
            'location' => [
                [['param' => 'post_type', 'operator' => '==', 'value' => 'slm_task_template']]
            ],
            'style' => 'seamless',
            'position' => 'normal',
            'menu_order' => 0
        ]);
    }
    
    /**
     * Task Instance Fields
     */
    private static function register_task_instance_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_task_instance',
            'title' => __('Task Instance Details', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_ti_case',
                    'label' => __('Case', 'flavor'),
                    'name' => '_slm_case_id',
                    'type' => 'post_object',
                    'post_type' => ['slm_case'],
                    'return_format' => 'id',
                    'required' => 1,
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_ti_assigned',
                    'label' => __('Assigned To', 'flavor'),
                    'name' => '_slm_assigned_user',
                    'type' => 'user',
                    'return_format' => 'id',
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_ti_status',
                    'label' => __('Status', 'flavor'),
                    'name' => '_slm_task_status',
                    'type' => 'select',
                    'choices' => [
                        'locked' => __('Locked', 'flavor'),
                        'available' => __('Available', 'flavor'),
                        'in_progress' => __('In Progress', 'flavor'),
                        'pending_review' => __('Pending Review', 'flavor'),
                        'complete' => __('Complete', 'flavor'),
                        'cancelled' => __('Cancelled', 'flavor'),
                    ],
                    'default_value' => 'locked',
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_ti_type',
                    'label' => __('Task Type', 'flavor'),
                    'name' => '_slm_task_type',
                    'type' => 'select',
                    'choices' => [
                        'checkbox' => __('Checkbox', 'flavor'),
                        'upload' => __('Upload', 'flavor'),
                        'form' => __('Form', 'flavor'),
                        'payment' => __('Payment', 'flavor'),
                        'signature' => __('Signature', 'flavor'),
                        'external' => __('External', 'flavor'),
                    ],
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_ti_due_date',
                    'label' => __('Due Date', 'flavor'),
                    'name' => '_slm_due_date',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper' => ['width' => '34']
                ],
                [
                    'key' => 'field_slm_ti_description',
                    'label' => __('Instructions', 'flavor'),
                    'name' => '_slm_task_description',
                    'type' => 'wysiwyg',
                    'media_upload' => 0,
                    'tabs' => 'visual',
                    'toolbar' => 'basic'
                ],
                [
                    'key' => 'field_slm_ti_completed_date',
                    'label' => __('Completed Date', 'flavor'),
                    'name' => '_slm_completed_date',
                    'type' => 'date_time_picker',
                    'display_format' => 'd/m/Y H:i',
                    'return_format' => 'Y-m-d H:i:s',
                    'wrapper' => ['width' => '50']
                ],
                [
                    'key' => 'field_slm_ti_completed_by',
                    'label' => __('Completed By', 'flavor'),
                    'name' => '_slm_completed_by',
                    'type' => 'user',
                    'return_format' => 'id',
                    'wrapper' => ['width' => '50']
                ],
            ],
            'location' => [
                [['param' => 'post_type', 'operator' => '==', 'value' => 'slm_task_instance']]
            ],
            'style' => 'default',
            'position' => 'normal',
            'menu_order' => 0
        ]);
    }
    
    /**
     * Case - Task Related Fields
     */
    private static function register_case_task_fields() {
        acf_add_local_field_group([
            'key' => 'group_slm_case_tasks',
            'title' => __('Task Management', 'flavor'),
            'fields' => [
                [
                    'key' => 'field_slm_case_service_tier',
                    'label' => __('Service Tier', 'flavor'),
                    'name' => '_slm_service_tier',
                    'type' => 'select',
                    'instructions' => __('Affects task timeline calculations.', 'flavor'),
                    'choices' => [
                        'standard' => __('Standard', 'flavor'),
                        'fast' => __('Fast Track', 'flavor'),
                        'expedited' => __('Expedited', 'flavor'),
                    ],
                    'default_value' => 'standard',
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_case_critical_date',
                    'label' => __('Critical Deadline', 'flavor'),
                    'name' => '_slm_critical_deadline',
                    'type' => 'date_picker',
                    'instructions' => __('For tasks calculated from deadline.', 'flavor'),
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                    'wrapper' => ['width' => '33']
                ],
                [
                    'key' => 'field_slm_case_applied_lists',
                    'label' => __('Applied Task Lists', 'flavor'),
                    'name' => '_slm_applied_task_lists',
                    'type' => 'repeater',
                    'instructions' => __('Task lists that have been applied to this case.', 'flavor'),
                    'layout' => 'table',
                    'button_label' => '',
                    'sub_fields' => [
                        [
                            'key' => 'field_slm_atl_id',
                            'label' => __('Task List', 'flavor'),
                            'name' => 'task_list_id',
                            'type' => 'post_object',
                            'post_type' => ['slm_task_list'],
                            'return_format' => 'id'
                        ],
                        [
                            'key' => 'field_slm_atl_applied_date',
                            'label' => __('Applied Date', 'flavor'),
                            'name' => 'applied_date',
                            'type' => 'date_time_picker',
                            'display_format' => 'd/m/Y H:i',
                            'return_format' => 'Y-m-d H:i:s'
                        ],
                        [
                            'key' => 'field_slm_atl_applied_by',
                            'label' => __('Applied By', 'flavor'),
                            'name' => 'applied_by',
                            'type' => 'user',
                            'return_format' => 'id'
                        ]
                    ]
                ],
            ],
            'location' => [
                [['param' => 'post_type', 'operator' => '==', 'value' => 'slm_case']]
            ],
            'style' => 'default',
            'position' => 'normal',
            'menu_order' => 10
        ]);
    }
    
    /**
     * Get case type choices from config
     */
    private static function get_case_type_choices() {
        $config = get_option('slm_case_type_config');
        $choices = [];
        
        if (is_array($config)) {
            foreach ($config as $key => $data) {
                $choices[$key] = $data['label'] ?? $key;
            }
        }
        
        return $choices ?: [
            'CIT' => __('Citizenship', 'flavor'),
            'VISA' => __('Visa', 'flavor'),
            'IMM' => __('Immigration', 'flavor'),
        ];
    }
    
    /**
     * Get Gravity Forms choices
     */
    private static function get_gravity_forms_choices() {
        $choices = [];
        
        if (class_exists('GFAPI')) {
            $forms = GFAPI::get_forms();
            foreach ($forms as $form) {
                $choices[$form['id']] = $form['title'] . ' (ID: ' . $form['id'] . ')';
            }
        }
        
        return $choices;
    }
}
