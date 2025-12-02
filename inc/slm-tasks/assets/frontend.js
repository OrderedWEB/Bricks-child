/**
 * SLM Tasks - Frontend JavaScript
 * 
 * Handles client-facing task interactions:
 * - Task completion
 * - Progress display
 * - Notifications panel
 * - File uploads
 * 
 * @package SLM_Tasks
 */

(function($) {
    'use strict';

    // Configuration from localized script
    var config = window.slmTasksConfig || {};

    /**
     * Task Manager - Main controller
     */
    var TaskManager = {
        init: function() {
            this.bindEvents();
            this.initProgress();
            this.initNotifications();
            this.initUploadTasks();
        },

        bindEvents: function() {
            // Task completion
            $(document).on('click', '.slm-complete-task', this.handleComplete);
            $(document).on('change', '.slm-task-checkbox', this.handleCheckboxChange);
            
            // Task details modal
            $(document).on('click', '.slm-task-title', this.showTaskDetails);
            $(document).on('click', '.slm-task-modal-close', this.hideTaskDetails);
            
            // Upload tasks
            $(document).on('change', '.slm-task-file-input', this.handleFileSelect);
            $(document).on('click', '.slm-upload-submit', this.handleUploadSubmit);
            
            // View toggle
            $(document).on('click', '.slm-view-toggle button', this.handleViewToggle);
            
            // Refresh
            $(document).on('click', '.slm-refresh-tasks', this.refreshTasks);
        },

        /**
         * Handle task completion
         */
        handleComplete: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var taskId = $btn.data('task-id');
            var taskType = $btn.data('task-type');
            
            if ($btn.hasClass('slm-loading')) return;
            
            // For checkbox tasks, complete immediately
            if (taskType === 'checkbox') {
                TaskManager.completeTask(taskId, {}, $btn);
                return;
            }
            
            // For other types, show the appropriate interface
            switch (taskType) {
                case 'upload':
                    TaskManager.showUploadModal(taskId);
                    break;
                case 'form':
                    TaskManager.openFormTask(taskId);
                    break;
                case 'payment':
                    TaskManager.initiatePayment(taskId);
                    break;
                case 'signature':
                    TaskManager.openSignature(taskId);
                    break;
                case 'external':
                    TaskManager.showExternalConfirm(taskId);
                    break;
            }
        },

        /**
         * Handle checkbox change
         */
        handleCheckboxChange: function() {
            var $checkbox = $(this);
            var taskId = $checkbox.data('task-id');
            
            if ($checkbox.prop('checked')) {
                TaskManager.completeTask(taskId, {}, $checkbox);
            }
        },

        /**
         * Complete a task via AJAX
         */
        completeTask: function(taskId, completionData, $element) {
            var $container = $element.closest('.slm-task-item');
            
            $element.addClass('slm-loading');
            $container.addClass('slm-completing');
            
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_complete_task',
                    nonce: config.nonce,
                    task_id: taskId,
                    completion_data: completionData
                },
                success: function(response) {
                    if (response.success) {
                        $container
                            .removeClass('slm-completing')
                            .addClass('slm-completed')
                            .find('.slm-task-status')
                            .text(response.data.status_label || 'Complete')
                            .attr('data-status', 'complete');
                        
                        // Update progress
                        TaskManager.updateProgress(response.data.progress);
                        
                        // Show success message
                        TaskManager.showNotice('success', response.data.message || 'Task completed!');
                        
                        // Refresh to show unlocked tasks
                        if (response.data.unlocked && response.data.unlocked.length > 0) {
                            setTimeout(function() {
                                TaskManager.refreshTasks();
                            }, 1000);
                        }
                    } else {
                        $container.removeClass('slm-completing');
                        $element.removeClass('slm-loading');
                        
                        if ($element.is(':checkbox')) {
                            $element.prop('checked', false);
                        }
                        
                        TaskManager.showNotice('error', response.data.message || 'Failed to complete task');
                    }
                },
                error: function() {
                    $container.removeClass('slm-completing');
                    $element.removeClass('slm-loading');
                    TaskManager.showNotice('error', 'Network error. Please try again.');
                }
            });
        },

        /**
         * Show task details modal
         */
        showTaskDetails: function(e) {
            e.preventDefault();
            
            var taskId = $(this).closest('.slm-task-item').data('task-id');
            
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_task_details',
                    nonce: config.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success) {
                        TaskManager.renderTaskModal(response.data);
                    }
                }
            });
        },

        /**
         * Render task details modal
         */
        renderTaskModal: function(task) {
            var $modal = $('#slm-task-modal');
            
            if (!$modal.length) {
                $modal = $('<div id="slm-task-modal" class="slm-modal">' +
                    '<div class="slm-modal-overlay"></div>' +
                    '<div class="slm-modal-content">' +
                        '<button class="slm-task-modal-close">&times;</button>' +
                        '<div class="slm-modal-body"></div>' +
                    '</div>' +
                '</div>');
                $('body').append($modal);
            }
            
            var statusClass = 'slm-status-' + task.status;
            var html = '<h2>' + this.escapeHtml(task.title) + '</h2>' +
                '<p class="slm-task-modal-status ' + statusClass + '">' + 
                    this.escapeHtml(task.status_label || task.status) + 
                '</p>';
            
            if (task.description) {
                html += '<div class="slm-task-modal-desc">' + task.description + '</div>';
            }
            
            if (task.due_date) {
                html += '<p class="slm-task-modal-due">' +
                    '<strong>Due:</strong> ' + this.escapeHtml(task.due_date_formatted) +
                    ' <span class="slm-days-label">' + this.escapeHtml(task.days_label || '') + '</span>' +
                '</p>';
            }
            
            if (task.assigned_user_name) {
                html += '<p><strong>Assigned to:</strong> ' + this.escapeHtml(task.assigned_user_name) + '</p>';
            }
            
            // Type-specific info
            if (task.type_data) {
                html += '<div class="slm-task-type-info">';
                
                switch (task.type) {
                    case 'upload':
                        if (task.type_data.allowed_file_types) {
                            html += '<p><strong>Allowed files:</strong> ' + 
                                this.escapeHtml(task.type_data.allowed_file_types) + '</p>';
                        }
                        break;
                    case 'form':
                        if (task.type_data.form_url) {
                            html += '<p><a href="' + task.type_data.form_url + '" class="button">Open Form</a></p>';
                        }
                        break;
                    case 'payment':
                        if (task.type_data.amount) {
                            html += '<p><strong>Amount:</strong> ' + 
                                this.escapeHtml(task.type_data.currency || '€') + 
                                this.escapeHtml(task.type_data.amount) + '</p>';
                        }
                        break;
                    case 'external':
                        if (task.type_data.external_instructions) {
                            html += '<div class="slm-external-instructions">' + 
                                task.type_data.external_instructions + '</div>';
                        }
                        break;
                }
                
                html += '</div>';
            }
            
            // Action button
            if (task.status === 'available' || task.status === 'in_progress') {
                html += '<div class="slm-task-modal-actions">' +
                    '<button class="slm-complete-task button button-primary" ' +
                        'data-task-id="' + task.id + '" ' +
                        'data-task-type="' + task.type + '">' +
                        (task.type === 'checkbox' ? 'Mark Complete' : 'Complete Task') +
                    '</button>' +
                '</div>';
            }
            
            $modal.find('.slm-modal-body').html(html);
            $modal.addClass('slm-modal-open');
            $('body').addClass('slm-modal-active');
        },

        /**
         * Hide task modal
         */
        hideTaskDetails: function() {
            $('#slm-task-modal').removeClass('slm-modal-open');
            $('body').removeClass('slm-modal-active');
        },

        /**
         * Show upload modal
         */
        showUploadModal: function(taskId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_task_details',
                    nonce: config.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success) {
                        var task = response.data;
                        var allowedTypes = task.type_data.allowed_file_types || '*';
                        var maxSize = task.type_data.max_file_size_mb || 10;
                        
                        var html = '<h2>Upload Document</h2>' +
                            '<p>' + TaskManager.escapeHtml(task.title) + '</p>' +
                            '<div class="slm-upload-area">' +
                                '<input type="file" class="slm-task-file-input" ' +
                                    'id="slm-upload-' + taskId + '" ' +
                                    'data-task-id="' + taskId + '" ' +
                                    'accept="' + allowedTypes + '">' +
                                '<label for="slm-upload-' + taskId + '" class="slm-upload-label">' +
                                    '<span class="slm-upload-icon">📁</span>' +
                                    '<span class="slm-upload-text">Click or drag file here</span>' +
                                    '<span class="slm-upload-hint">Max size: ' + maxSize + 'MB</span>' +
                                '</label>' +
                                '<div class="slm-upload-preview" style="display:none;"></div>' +
                            '</div>' +
                            '<div class="slm-task-modal-actions">' +
                                '<button class="slm-upload-submit button button-primary" ' +
                                    'data-task-id="' + taskId + '" disabled>Upload & Complete</button>' +
                            '</div>';
                        
                        TaskManager.renderTaskModal({
                            id: taskId,
                            title: 'Upload Document',
                            status: 'in_progress',
                            status_label: 'In Progress',
                            description: html,
                            type: 'upload'
                        });
                        
                        // Replace modal body with our upload form
                        $('#slm-task-modal .slm-modal-body').html(html);
                    }
                }
            });
        },

        /**
         * Handle file selection
         */
        handleFileSelect: function() {
            var $input = $(this);
            var file = this.files[0];
            var taskId = $input.data('task-id');
            var $preview = $input.closest('.slm-upload-area').find('.slm-upload-preview');
            var $submit = $input.closest('.slm-modal-body').find('.slm-upload-submit');
            
            if (!file) {
                $preview.hide();
                $submit.prop('disabled', true);
                return;
            }
            
            // Show preview
            var html = '<div class="slm-file-preview">' +
                '<span class="slm-file-icon">📄</span>' +
                '<span class="slm-file-name">' + TaskManager.escapeHtml(file.name) + '</span>' +
                '<span class="slm-file-size">' + TaskManager.formatFileSize(file.size) + '</span>' +
                '<button type="button" class="slm-file-remove">&times;</button>' +
            '</div>';
            
            $preview.html(html).show();
            $submit.prop('disabled', false);
            
            // Handle remove
            $preview.find('.slm-file-remove').on('click', function() {
                $input.val('');
                $preview.hide();
                $submit.prop('disabled', true);
            });
        },

        /**
         * Handle upload submit
         */
        handleUploadSubmit: function() {
            var $btn = $(this);
            var taskId = $btn.data('task-id');
            var $input = $('#slm-upload-' + taskId);
            var file = $input[0].files[0];
            
            if (!file) return;
            
            $btn.prop('disabled', true).text('Uploading...');
            
            var formData = new FormData();
            formData.append('action', 'slm_upload_task_document');
            formData.append('nonce', config.nonce);
            formData.append('task_id', taskId);
            formData.append('file', file);
            
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Complete the task with document ID
                        TaskManager.completeTask(taskId, {
                            document_id: response.data.document_id
                        }, $btn);
                        
                        TaskManager.hideTaskDetails();
                    } else {
                        $btn.prop('disabled', false).text('Upload & Complete');
                        TaskManager.showNotice('error', response.data.message || 'Upload failed');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('Upload & Complete');
                    TaskManager.showNotice('error', 'Network error during upload');
                }
            });
        },

        /**
         * Open form task
         */
        openFormTask: function(taskId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_task_details',
                    nonce: config.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success && response.data.type_data.form_url) {
                        window.location.href = response.data.type_data.form_url;
                    }
                }
            });
        },

        /**
         * Initiate payment task
         */
        initiatePayment: function(taskId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_initiate_payment',
                    nonce: config.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success && response.data.checkout_url) {
                        window.location.href = response.data.checkout_url;
                    } else if (response.success && response.data.payment_instructions) {
                        TaskManager.showPaymentInstructions(taskId, response.data);
                    }
                }
            });
        },

        /**
         * Show payment instructions
         */
        showPaymentInstructions: function(taskId, data) {
            var html = '<h2>Payment Required</h2>' +
                '<p class="slm-payment-amount"><strong>' + 
                    (data.currency || '€') + data.amount + 
                '</strong></p>' +
                '<div class="slm-payment-instructions">' + data.payment_instructions + '</div>' +
                '<div class="slm-task-modal-actions">' +
                    '<button class="slm-mark-paid button button-primary" data-task-id="' + taskId + '">' +
                        'I Have Made This Payment' +
                    '</button>' +
                '</div>';
            
            TaskManager.renderTaskModal({
                id: taskId,
                title: 'Payment',
                status: 'in_progress',
                status_label: 'Awaiting Payment',
                description: '',
                type: 'payment'
            });
            
            $('#slm-task-modal .slm-modal-body').html(html);
            
            // Handle mark paid
            $(document).off('click', '.slm-mark-paid').on('click', '.slm-mark-paid', function() {
                TaskManager.completeTask(taskId, {
                    method: 'manual',
                    marked_by: config.userId
                }, $(this));
                TaskManager.hideTaskDetails();
            });
        },

        /**
         * Open signature task
         */
        openSignature: function(taskId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_signature_url',
                    nonce: config.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success && response.data.signing_url) {
                        window.location.href = response.data.signing_url;
                    } else {
                        TaskManager.showNotice('error', response.data.message || 'Signature not ready');
                    }
                }
            });
        },

        /**
         * Show external task confirmation
         */
        showExternalConfirm: function(taskId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_task_details',
                    nonce: config.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success) {
                        var task = response.data;
                        var html = '<h2>' + TaskManager.escapeHtml(task.title) + '</h2>';
                        
                        if (task.type_data.external_instructions) {
                            html += '<div class="slm-external-instructions">' + 
                                task.type_data.external_instructions + '</div>';
                        }
                        
                        if (task.type_data.external_url) {
                            html += '<p><a href="' + task.type_data.external_url + '" ' +
                                'target="_blank" class="button">Open External Link</a></p>';
                        }
                        
                        html += '<div class="slm-external-confirm">' +
                            '<label>' +
                                '<input type="checkbox" id="slm-external-confirm-check">' +
                                ' I confirm I have completed this task' +
                            '</label>' +
                        '</div>' +
                        '<div class="slm-task-modal-actions">' +
                            '<button class="slm-external-complete button button-primary" ' +
                                'data-task-id="' + taskId + '" disabled>Mark Complete</button>' +
                        '</div>';
                        
                        TaskManager.renderTaskModal({
                            id: taskId,
                            title: task.title,
                            status: 'in_progress',
                            status_label: 'In Progress',
                            description: '',
                            type: 'external'
                        });
                        
                        $('#slm-task-modal .slm-modal-body').html(html);
                        
                        // Handle checkbox
                        $('#slm-external-confirm-check').on('change', function() {
                            $('.slm-external-complete').prop('disabled', !this.checked);
                        });
                        
                        // Handle complete
                        $(document).off('click', '.slm-external-complete').on('click', '.slm-external-complete', function() {
                            TaskManager.completeTask(taskId, {}, $(this));
                            TaskManager.hideTaskDetails();
                        });
                    }
                }
            });
        },

        /**
         * Initialize progress display
         */
        initProgress: function() {
            this.refreshProgress();
        },

        /**
         * Refresh progress bars
         */
        refreshProgress: function() {
            var caseId = config.caseId;
            if (!caseId) return;
            
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_case_progress',
                    nonce: config.nonce,
                    case_id: caseId
                },
                success: function(response) {
                    if (response.success) {
                        TaskManager.updateProgress(response.data);
                    }
                }
            });
        },

        /**
         * Update progress display
         */
        updateProgress: function(progress) {
            if (!progress) return;
            
            // Overall progress
            var $overall = $('.slm-progress-overall');
            if ($overall.length && progress.overall) {
                $overall.find('.slm-progress-bar').css('width', progress.overall.percentage + '%');
                $overall.find('.slm-progress-text').text(
                    progress.overall.completed + ' / ' + progress.overall.total + ' tasks'
                );
                $overall.find('.slm-progress-percent').text(progress.overall.percentage + '%');
            }
            
            // Per-list progress
            if (progress.by_list) {
                progress.by_list.forEach(function(list) {
                    var $list = $('.slm-progress-list[data-list-id="' + list.id + '"]');
                    if ($list.length) {
                        $list.find('.slm-progress-bar').css('width', list.percentage + '%');
                        $list.find('.slm-progress-text').text(
                            list.completed + ' / ' + list.total
                        );
                    }
                });
            }
        },

        /**
         * Handle view toggle (My Tasks / All Tasks)
         */
        handleViewToggle: function() {
            var $btn = $(this);
            var view = $btn.data('view');
            
            $btn.addClass('active').siblings().removeClass('active');
            
            TaskManager.loadTasks(view);
        },

        /**
         * Load tasks via AJAX
         */
        loadTasks: function(view) {
            var $container = $('.slm-tasks-list');
            
            $container.addClass('slm-loading');
            
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_case_tasks',
                    nonce: config.nonce,
                    case_id: config.caseId,
                    view: view || 'my'
                },
                success: function(response) {
                    $container.removeClass('slm-loading');
                    
                    if (response.success) {
                        TaskManager.renderTasks(response.data.tasks);
                    }
                },
                error: function() {
                    $container.removeClass('slm-loading');
                }
            });
        },

        /**
         * Render tasks HTML
         */
        renderTasks: function(tasks) {
            var $container = $('.slm-tasks-list');
            var html = '';
            
            if (!tasks || tasks.length === 0) {
                html = '<p class="slm-no-tasks">No tasks to display</p>';
            } else {
                tasks.forEach(function(task) {
                    html += TaskManager.renderTaskItem(task);
                });
            }
            
            $container.html(html);
        },

        /**
         * Render single task item
         */
        renderTaskItem: function(task) {
            var statusClass = 'slm-task-' + task.status;
            var typeIcon = this.getTypeIcon(task.type);
            
            var html = '<div class="slm-task-item ' + statusClass + '" data-task-id="' + task.id + '">';
            
            // Checkbox for simple tasks
            if (task.type === 'checkbox' && (task.status === 'available' || task.status === 'in_progress')) {
                html += '<input type="checkbox" class="slm-task-checkbox" data-task-id="' + task.id + '">';
            } else {
                html += '<span class="slm-task-type-icon">' + typeIcon + '</span>';
            }
            
            html += '<div class="slm-task-content">' +
                '<span class="slm-task-title">' + this.escapeHtml(task.title) + '</span>';
            
            if (task.due_date && task.status !== 'complete') {
                var dueClass = task.is_overdue ? 'slm-overdue' : '';
                html += '<span class="slm-task-due ' + dueClass + '">' + 
                    this.escapeHtml(task.due_date_formatted || task.due_date) + '</span>';
            }
            
            html += '</div>';
            
            html += '<span class="slm-task-status" data-status="' + task.status + '">' +
                this.escapeHtml(task.status_label || task.status) + '</span>';
            
            // Action button
            if (task.status === 'available' || task.status === 'in_progress') {
                if (task.type !== 'checkbox') {
                    html += '<button class="slm-complete-task slm-btn-small" ' +
                        'data-task-id="' + task.id + '" ' +
                        'data-task-type="' + task.type + '">' +
                        this.getActionLabel(task.type) +
                    '</button>';
                }
            }
            
            html += '</div>';
            
            return html;
        },

        /**
         * Refresh tasks list
         */
        refreshTasks: function() {
            var view = $('.slm-view-toggle button.active').data('view') || 'my';
            TaskManager.loadTasks(view);
            TaskManager.refreshProgress();
        },

        /**
         * Initialize notifications panel
         */
        initNotifications: function() {
            this.loadNotifications();
            
            // Refresh every 2 minutes
            setInterval(function() {
                TaskManager.loadNotifications();
            }, 120000);
        },

        /**
         * Load notifications
         */
        loadNotifications: function() {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_notifications',
                    nonce: config.nonce
                },
                success: function(response) {
                    if (response.success) {
                        TaskManager.updateNotificationBadge(response.data.unread_count);
                        TaskManager.renderNotifications(response.data.notifications);
                    }
                }
            });
        },

        /**
         * Update notification badge
         */
        updateNotificationBadge: function(count) {
            var $badge = $('.slm-notification-badge');
            
            if (count > 0) {
                if (!$badge.length) {
                    $badge = $('<span class="slm-notification-badge"></span>');
                    $('.slm-notification-icon').append($badge);
                }
                $badge.text(count > 99 ? '99+' : count).show();
            } else {
                $badge.hide();
            }
        },

        /**
         * Render notifications list
         */
        renderNotifications: function(notifications) {
            var $list = $('.slm-notifications-list');
            if (!$list.length) return;
            
            var html = '';
            
            if (!notifications || notifications.length === 0) {
                html = '<p class="slm-no-notifications">No notifications</p>';
            } else {
                notifications.forEach(function(notif) {
                    var readClass = notif.is_read ? 'slm-notif-read' : 'slm-notif-unread';
                    html += '<div class="slm-notification-item ' + readClass + '" data-id="' + notif.id + '">' +
                        '<div class="slm-notif-content">' +
                            '<span class="slm-notif-title">' + TaskManager.escapeHtml(notif.title) + '</span>' +
                            '<span class="slm-notif-time">' + TaskManager.escapeHtml(notif.time_ago) + '</span>' +
                        '</div>' +
                    '</div>';
                });
            }
            
            $list.html(html);
        },

        /**
         * Initialize upload tasks
         */
        initUploadTasks: function() {
            // Drag and drop support
            $(document).on('dragover dragenter', '.slm-upload-area', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('slm-drag-over');
            });
            
            $(document).on('dragleave', '.slm-upload-area', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('slm-drag-over');
            });
            
            $(document).on('drop', '.slm-upload-area', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('slm-drag-over');
                
                var $input = $(this).find('.slm-task-file-input');
                var files = e.originalEvent.dataTransfer.files;
                
                if (files.length > 0) {
                    $input[0].files = files;
                    $input.trigger('change');
                }
            });
        },

        /**
         * Show notice message
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="slm-notice slm-notice-' + type + '">' +
                '<span>' + this.escapeHtml(message) + '</span>' +
                '<button class="slm-notice-close">&times;</button>' +
            '</div>');
            
            $('.slm-notices').append($notice);
            
            setTimeout(function() {
                $notice.addClass('slm-notice-visible');
            }, 10);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                $notice.removeClass('slm-notice-visible');
                setTimeout(function() {
                    $notice.remove();
                }, 300);
            }, 5000);
            
            $notice.find('.slm-notice-close').on('click', function() {
                $notice.remove();
            });
        },

        /**
         * Get type icon
         */
        getTypeIcon: function(type) {
            var icons = {
                checkbox: '☑️',
                upload: '📤',
                form: '📝',
                payment: '💳',
                signature: '✍️',
                external: '🔗'
            };
            return icons[type] || '📋';
        },

        /**
         * Get action label for task type
         */
        getActionLabel: function(type) {
            var labels = {
                checkbox: 'Complete',
                upload: 'Upload',
                form: 'Open Form',
                payment: 'Pay',
                signature: 'Sign',
                external: 'Complete'
            };
            return labels[type] || 'Complete';
        },

        /**
         * Format file size
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        TaskManager.init();
    });

    // Expose for external use
    window.SLMTasks = TaskManager;

})(jQuery);
