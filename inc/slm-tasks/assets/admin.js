/**
 * SLM Tasks Admin Scripts
 */

(function($) {
    'use strict';
    
    var SLMTasksAdmin = {
        
        init: function() {
            this.bindTaskListBuilder();
            this.bindCaseTasksPanel();
            this.bindModals();
            this.initSortable();
        },
        
        /**
         * Task List Builder
         */
        bindTaskListBuilder: function() {
            var self = this;
            var $builder = $('.slm-task-list-builder');
            
            if (!$builder.length) return;
            
            // Add task row
            $builder.on('click', '.slm-add-task-row', function() {
                var template = $('#slm-task-row-template').html();
                var index = $('#slm-task-list-rows tr').length;
                var html = template.replace(/\{\{INDEX\}\}/g, index);
                $('#slm-task-list-rows').append(html);
                self.updateTaskCount();
            });
            
            // Remove task row
            $builder.on('click', '.slm-remove-task', function() {
                $(this).closest('tr').remove();
                self.updateTaskCount();
            });
            
            // Update type badge when template changes
            $builder.on('change', '.slm-template-select', function() {
                var $row = $(this).closest('tr');
                var $option = $(this).find(':selected');
                var type = $option.data('type') || '-';
                var role = $option.data('role') || '-';
                
                $row.find('.slm-type-badge').text(type);
                $row.find('.slm-role-badge').text(role);
            });
            
            // Toggle conditional rules
            $builder.on('change', '.slm-conditional-toggle', function() {
                var $row = $(this).closest('tr');
                var isChecked = $(this).is(':checked');
                
                if (isChecked) {
                    var index = $row.data('index');
                    var rulesHtml = '<tr class="slm-conditional-row">' +
                        '<td colspan="8">' +
                        '<div class="slm-conditional-rules active">' +
                        '<label>Conditional Rules (JSON):</label>' +
                        '<textarea name="slm_tasks[' + index + '][conditional_rules]" rows="3" style="width:100%"></textarea>' +
                        '<p class="description">Example: [{"field":"_slm_case_type","operator":"=","value":"CIT"}]</p>' +
                        '</div></td></tr>';
                    $row.after(rulesHtml);
                } else {
                    $row.next('.slm-conditional-row').remove();
                }
            });
        },
        
        updateTaskCount: function() {
            var count = $('#slm-task-list-rows tr.slm-task-row').length;
            $('.slm-task-count').text(count + ' tasks');
        },
        
        initSortable: function() {
            if ($.fn.sortable) {
                $('#slm-task-list-rows').sortable({
                    handle: '.slm-col-drag',
                    axis: 'y',
                    placeholder: 'slm-task-row ui-sortable-placeholder',
                    update: function(event, ui) {
                        SLMTasksAdmin.reorderSequence();
                    }
                });
            }
        },
        
        reorderSequence: function() {
            $('#slm-task-list-rows tr.slm-task-row').each(function(index) {
                $(this).find('input[name*="sequence_order"]').val((index + 1) * 10);
            });
        },
        
        /**
         * Case Tasks Panel
         */
        bindCaseTasksPanel: function() {
            var self = this;
            
            // Apply task list button
            $(document).on('click', '.slm-apply-task-list-btn', function(e) {
                e.preventDefault();
                self.openApplyTaskListModal();
            });
            
            // Task actions
            $(document).on('click', '.slm-task-action-complete', function() {
                var taskId = $(this).data('task-id');
                self.completeTask(taskId);
            });
            
            $(document).on('click', '.slm-task-action-edit', function() {
                var taskId = $(this).data('task-id');
                self.openEditTaskModal(taskId);
            });
        },
        
        openApplyTaskListModal: function() {
            var caseId = $('#post_ID').val() || slmTasksConfig.caseId;
            
            $.ajax({
                url: slmTasksConfig.ajaxUrl,
                type: 'GET',
                data: {
                    action: 'slm_get_available_task_lists',
                    nonce: slmTasksConfig.nonce,
                    case_id: caseId
                },
                success: function(response) {
                    if (response.success) {
                        SLMTasksAdmin.showApplyModal(response.data.lists);
                    }
                }
            });
        },
        
        showApplyModal: function(lists) {
            var html = '<div class="slm-modal-overlay">' +
                '<div class="slm-modal-content">' +
                '<div class="slm-modal-header">' +
                '<h2>Apply Task List</h2>' +
                '<button type="button" class="slm-modal-close">&times;</button>' +
                '</div>' +
                '<div class="slm-modal-body">';
            
            if (lists.length === 0) {
                html += '<p>No task lists available.</p>';
            } else {
                html += '<p>Select a task list to apply to this case:</p>';
                lists.forEach(function(list) {
                    html += '<label class="slm-task-list-option">' +
                        '<input type="radio" name="slm_task_list_id" value="' + list.id + '">' +
                        '<span class="slm-task-list-name">' + SLMTasksAdmin.escapeHtml(list.title) + '</span>' +
                        '<div class="slm-task-list-info">' + list.task_count + ' tasks • ' + list.category + '</div>' +
                        '</label>';
                });
            }
            
            html += '</div>' +
                '<div class="slm-modal-footer">' +
                '<button type="button" class="button slm-modal-cancel">Cancel</button>' +
                '<button type="button" class="button button-primary slm-apply-list-confirm" disabled>Apply</button>' +
                '</div></div></div>';
            
            $('body').append(html);
            
            // Enable confirm when selection made
            $('.slm-task-list-option input').on('change', function() {
                $('.slm-apply-list-confirm').prop('disabled', false);
                $('.slm-task-list-option').removeClass('selected');
                $(this).closest('.slm-task-list-option').addClass('selected');
            });
        },
        
        completeTask: function(taskId) {
            if (!confirm('Mark this task as complete?')) return;
            
            $.ajax({
                url: slmTasksConfig.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_complete_task',
                    nonce: slmTasksConfig.nonce,
                    task_id: taskId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error completing task');
                    }
                }
            });
        },
        
        /**
         * Modals
         */
        bindModals: function() {
            // Close modal
            $(document).on('click', '.slm-modal-close, .slm-modal-cancel', function() {
                $('.slm-modal-overlay').remove();
            });
            
            // Click outside to close
            $(document).on('click', '.slm-modal-overlay', function(e) {
                if ($(e.target).hasClass('slm-modal-overlay')) {
                    $(this).remove();
                }
            });
            
            // Apply task list confirm
            $(document).on('click', '.slm-apply-list-confirm', function() {
                var listId = $('input[name="slm_task_list_id"]:checked').val();
                var caseId = $('#post_ID').val() || slmTasksConfig.caseId;
                
                if (!listId) return;
                
                $(this).prop('disabled', true).text('Applying...');
                
                $.ajax({
                    url: slmTasksConfig.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'slm_apply_task_list',
                        nonce: slmTasksConfig.nonce,
                        task_list_id: listId,
                        case_id: caseId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.tasks_created + ' tasks created');
                            location.reload();
                        } else {
                            alert(response.data.message || 'Error applying task list');
                            $('.slm-apply-list-confirm').prop('disabled', false).text('Apply');
                        }
                    }
                });
            });
            
            // Duplicate task list
            $(document).on('click', '.slm-duplicate-list', function() {
                var listId = $(this).data('id');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'slm_duplicate_task_list',
                        nonce: slmTasksConfig.nonce,
                        list_id: listId
                    },
                    success: function(response) {
                        if (response.success) {
                            if (confirm('Task list duplicated! Edit the new copy?')) {
                                window.location.href = response.data.edit_url;
                            }
                        } else {
                            alert(response.data.message || 'Error duplicating task list');
                        }
                    }
                });
            });
            
            // Export task list
            $(document).on('click', '.slm-export-list', function() {
                var listId = $(this).data('id');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'GET',
                    data: {
                        action: 'slm_export_task_list',
                        nonce: slmTasksConfig.nonce,
                        list_id: listId
                    },
                    success: function(response) {
                        if (response.success) {
                            SLMTasksAdmin.downloadCSV(response.data.data, response.data.filename);
                        }
                    }
                });
            });
        },
        
        /**
         * Utilities
         */
        escapeHtml: function(text) {
            if (!text) return '';
            return $('<div>').text(text).html();
        },
        
        downloadCSV: function(data, filename) {
            var csv = data.map(function(row) {
                return row.map(function(cell) {
                    return '"' + String(cell).replace(/"/g, '""') + '"';
                }).join(',');
            }).join('\n');
            
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.click();
        }
    };
    
    $(document).ready(function() {
        SLMTasksAdmin.init();
    });
    
})(jQuery);
