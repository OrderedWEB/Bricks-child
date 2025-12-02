/**
 * SLM Messaging - Frontend JavaScript
 *
 * Handles message sending, real-time updates, and attachments
 */

(function($) {
    'use strict';

    var config = window.slmMessagingConfig || {};

    /**
     * Messaging Controller
     */
    var Messaging = {
        lastTimestamp: null,
        pollTimer: null,
        pendingAttachments: [],

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initComposer();
            this.startPolling();
            this.handleReplyFocus();
            this.markVisibleAsRead();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Send message
            $(document).on('submit', '.slm-message-form', function(e) {
                e.preventDefault();
                self.sendMessage($(this));
            });

            $(document).on('click', '.slm-composer-btn-send', function() {
                $(this).closest('.slm-message-composer').find('.slm-message-form').submit();
            });

            // Attachment button
            $(document).on('click', '.slm-composer-btn-attach', function() {
                self.openAttachmentModal();
            });

            // Attachment modal tabs
            $(document).on('click', '.slm-attachment-tab', function() {
                var tab = $(this).data('tab');
                self.switchAttachmentTab(tab);
            });

            // File upload
            $(document).on('change', '.slm-upload-input', function() {
                self.handleFileSelect(this);
            });

            // Drag and drop
            $(document).on('dragover dragenter', '.slm-upload-dropzone', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });

            $(document).on('dragleave', '.slm-upload-dropzone', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
            });

            $(document).on('drop', '.slm-upload-dropzone', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');
                var files = e.originalEvent.dataTransfer.files;
                if (files.length) {
                    self.uploadFile(files[0]);
                }
            });

            // Document link selection
            $(document).on('click', '.slm-link-document', function() {
                $(this).toggleClass('selected');
                $(this).find('input[type="checkbox"]').prop('checked', $(this).hasClass('selected'));
            });

            // Link documents confirm
            $(document).on('click', '.slm-link-confirm', function() {
                self.confirmLinkedDocuments();
            });

            // Remove attachment
            $(document).on('click', '.slm-composer-attachment-remove', function() {
                var id = $(this).closest('.slm-composer-attachment').data('id');
                self.removeAttachment(id);
            });

            // Close attachment modal
            $(document).on('click', '.slm-attachment-modal-close, .slm-attachment-modal', function(e) {
                if (e.target === this) {
                    self.closeAttachmentModal();
                }
            });

            // Mark read on scroll into view
            $(document).on('scroll', '.slm-messages-list', function() {
                self.markVisibleAsRead();
            });

            // Context tabs
            $(document).on('click', '.slm-context-tab', function() {
                var context = $(this).data('context');
                self.switchContext(context);
            });

            // Search linked documents
            $(document).on('input', '.slm-link-search', function() {
                self.searchDocuments($(this).val());
            });

            // Mark all as read
            $(document).on('click', '.slm-mark-all-read', function() {
                self.markAllAsRead();
            });

            // Textarea auto-resize
            $(document).on('input', '.slm-composer-textarea', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 150) + 'px';
            });

            // Enter to send (Shift+Enter for new line)
            $(document).on('keydown', '.slm-composer-textarea', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    $(this).closest('.slm-message-composer').find('.slm-message-form').submit();
                }
            });
        },

        /**
         * Initialize composer
         */
        initComposer: function() {
            // Focus textarea if no messages
            if ($('.slm-messages-list').children().length === 0) {
                $('.slm-composer-textarea').focus();
            }
        },

        /**
         * Send message
         */
        sendMessage: function($form) {
            var self = this;
            var $textarea = $form.find('.slm-composer-textarea');
            var $sendBtn = $form.find('.slm-composer-btn-send');
            var content = $textarea.val().trim();

            if (!content && this.pendingAttachments.length === 0) {
                return;
            }

            // Disable while sending
            $sendBtn.prop('disabled', true).addClass('slm-sending');
            $textarea.prop('disabled', true);

            // Gather data
            var data = {
                action: 'slm_send_message',
                nonce: config.nonce,
                case_id: config.caseId,
                content: content,
                task_id: $form.data('task-id') || 0,
                document_id: $form.data('document-id') || 0,
            };

            // Add attachments
            if (this.pendingAttachments.length > 0) {
                var uploadedIds = [];
                var linkedIds = [];

                this.pendingAttachments.forEach(function(att) {
                    if (att.type === 'upload') {
                        uploadedIds.push(att.id);
                    } else {
                        linkedIds.push(att.id);
                    }
                });

                if (uploadedIds.length) {
                    data.uploaded_attachments = uploadedIds;
                }
                if (linkedIds.length) {
                    data.linked_documents = linkedIds;
                }
            }

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        // Append message
                        $('.slm-messages-list').append(response.data.html);
                        self.scrollToBottom();

                        // Clear form
                        $textarea.val('').css('height', 'auto');
                        self.pendingAttachments = [];
                        self.updateAttachmentPreview();

                        // Update timestamp for polling
                        self.lastTimestamp = response.data.timestamp;

                        // Show success
                        self.showNotice('success', config.strings.sent);
                    } else {
                        self.showNotice('error', response.data.message || config.strings.error);
                    }
                },
                error: function() {
                    self.showNotice('error', config.strings.error);
                },
                complete: function() {
                    $sendBtn.prop('disabled', false).removeClass('slm-sending');
                    $textarea.prop('disabled', false).focus();
                }
            });
        },

        /**
         * Start polling for new messages
         */
        startPolling: function() {
            var self = this;

            if (this.pollTimer) {
                clearInterval(this.pollTimer);
            }

            this.pollTimer = setInterval(function() {
                self.checkNewMessages();
            }, config.pollInterval || 30000);
        },

        /**
         * Check for new messages
         */
        checkNewMessages: function() {
            var self = this;

            if (!config.caseId) {
                return;
            }

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_messages',
                    nonce: config.nonce,
                    case_id: config.caseId,
                    since: this.lastTimestamp
                },
                success: function(response) {
                    if (response.success && response.data.count > 0) {
                        $('.slm-messages-list').append(response.data.html);
                        self.lastTimestamp = response.data.timestamp;

                        // Scroll if near bottom
                        var $list = $('.slm-messages-list');
                        var isNearBottom = $list[0].scrollHeight - $list.scrollTop() - $list.height() < 100;

                        if (isNearBottom) {
                            self.scrollToBottom();
                        }

                        // Update unread count
                        self.updateUnreadCount();
                    }
                }
            });
        },

        /**
         * Open attachment modal
         */
        openAttachmentModal: function() {
            var $modal = $('#slm-attachment-modal');

            if (!$modal.length) {
                this.createAttachmentModal();
                $modal = $('#slm-attachment-modal');
            }

            $modal.addClass('slm-modal-open');
            this.loadLinkableDocuments();
        },

        /**
         * Create attachment modal HTML
         */
        createAttachmentModal: function() {
            var html = `
                <div id="slm-attachment-modal" class="slm-attachment-modal">
                    <div class="slm-attachment-modal-content">
                        <div class="slm-attachment-modal-header">
                            <h3>Attach Files</h3>
                            <button class="slm-attachment-modal-close">&times;</button>
                        </div>
                        <div class="slm-attachment-modal-body">
                            <div class="slm-attachment-tabs">
                                <button class="slm-attachment-tab active" data-tab="upload">Upload New</button>
                                <button class="slm-attachment-tab" data-tab="link">Link Existing</button>
                            </div>
                            <div class="slm-attachment-tab-content" data-content="upload">
                                <div class="slm-upload-dropzone">
                                    <div class="slm-upload-dropzone-icon">📁</div>
                                    <div class="slm-upload-dropzone-text">Drag and drop files here or click to browse</div>
                                    <div class="slm-upload-dropzone-hint">PDF, DOC, DOCX, JPG, PNG • Max 10MB</div>
                                    <input type="file" class="slm-upload-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                                </div>
                            </div>
                            <div class="slm-attachment-tab-content" data-content="link" style="display:none;">
                                <input type="text" class="slm-link-search" placeholder="Search documents...">
                                <div class="slm-link-documents"></div>
                            </div>
                        </div>
                        <div class="slm-attachment-modal-footer">
                            <button class="button slm-attachment-modal-close">Cancel</button>
                            <button class="button button-primary slm-link-confirm" style="display:none;">Link Selected</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(html);
        },

        /**
         * Close attachment modal
         */
        closeAttachmentModal: function() {
            $('#slm-attachment-modal').removeClass('slm-modal-open');
        },

        /**
         * Switch attachment modal tab
         */
        switchAttachmentTab: function(tab) {
            $('.slm-attachment-tab').removeClass('active');
            $('.slm-attachment-tab[data-tab="' + tab + '"]').addClass('active');

            $('.slm-attachment-tab-content').hide();
            $('.slm-attachment-tab-content[data-content="' + tab + '"]').show();

            // Show/hide link confirm button
            if (tab === 'link') {
                $('.slm-link-confirm').show();
            } else {
                $('.slm-link-confirm').hide();
            }
        },

        /**
         * Handle file selection
         */
        handleFileSelect: function(input) {
            var file = input.files[0];
            if (file) {
                this.uploadFile(file);
            }
        },

        /**
         * Upload file
         */
        uploadFile: function(file) {
            var self = this;

            // Validate
            var maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                this.showNotice('error', 'File too large. Maximum 10MB.');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'slm_upload_message_attachment');
            formData.append('nonce', config.nonce);
            formData.append('case_id', config.caseId);
            formData.append('file', file);

            // Show uploading state
            var $dropzone = $('.slm-upload-dropzone');
            $dropzone.addClass('uploading');
            $dropzone.find('.slm-upload-dropzone-text').text(config.strings.uploading);

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.pendingAttachments.push({
                            id: response.data.document_id,
                            type: 'upload',
                            filename: response.data.filename,
                            size: response.data.size
                        });

                        self.updateAttachmentPreview();
                        self.closeAttachmentModal();
                    } else {
                        self.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    self.showNotice('error', 'Upload failed');
                },
                complete: function() {
                    $dropzone.removeClass('uploading');
                    $dropzone.find('.slm-upload-dropzone-text').text('Drag and drop files here or click to browse');
                }
            });
        },

        /**
         * Load linkable documents
         */
        loadLinkableDocuments: function() {
            var self = this;
            var $container = $('.slm-link-documents');

            $container.html('<div class="slm-messages-loading">Loading documents...</div>');

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_linkable_documents',
                    nonce: config.nonce,
                    case_id: config.caseId
                },
                success: function(response) {
                    if (response.success) {
                        self.renderLinkableDocuments(response.data.documents);
                    } else {
                        $container.html('<p class="slm-no-documents">No documents found</p>');
                    }
                }
            });
        },

        /**
         * Render linkable documents
         */
        renderLinkableDocuments: function(documents) {
            var $container = $('.slm-link-documents');

            if (!documents || documents.length === 0) {
                $container.html('<p class="slm-no-documents">No documents found</p>');
                return;
            }

            // Group by folder
            var folders = {};
            documents.forEach(function(doc) {
                var folder = doc.folder || 'Uncategorized';
                if (!folders[folder]) {
                    folders[folder] = [];
                }
                folders[folder].push(doc);
            });

            var html = '';
            for (var folder in folders) {
                html += '<div class="slm-link-folder">';
                html += '<div class="slm-link-folder-name">📁 ' + this.escapeHtml(folder) + '</div>';

                folders[folder].forEach(function(doc) {
                    var isSelected = this.pendingAttachments.some(function(att) {
                        return att.id === doc.id && att.type === 'link';
                    });

                    html += '<div class="slm-link-document' + (isSelected ? ' selected' : '') + '" data-id="' + doc.id + '">';
                    html += '<input type="checkbox"' + (isSelected ? ' checked' : '') + '>';
                    html += '<span class="slm-link-document-name">' + this.escapeHtml(doc.title) + '</span>';
                    html += '</div>';
                }.bind(this));

                html += '</div>';
            }

            $container.html(html);
        },

        /**
         * Search documents
         */
        searchDocuments: function(query) {
            var $docs = $('.slm-link-document');

            if (!query) {
                $docs.show();
                return;
            }

            query = query.toLowerCase();

            $docs.each(function() {
                var title = $(this).find('.slm-link-document-name').text().toLowerCase();
                $(this).toggle(title.indexOf(query) !== -1);
            });
        },

        /**
         * Confirm linked documents
         */
        confirmLinkedDocuments: function() {
            var self = this;

            // Remove existing linked attachments
            this.pendingAttachments = this.pendingAttachments.filter(function(att) {
                return att.type !== 'link';
            });

            // Add selected
            $('.slm-link-document.selected').each(function() {
                var id = $(this).data('id');
                var title = $(this).find('.slm-link-document-name').text();

                self.pendingAttachments.push({
                    id: id,
                    type: 'link',
                    filename: title
                });
            });

            this.updateAttachmentPreview();
            this.closeAttachmentModal();
        },

        /**
         * Remove attachment
         */
        removeAttachment: function(id) {
            this.pendingAttachments = this.pendingAttachments.filter(function(att) {
                return att.id !== id;
            });

            this.updateAttachmentPreview();
        },

        /**
         * Update attachment preview in composer
         */
        updateAttachmentPreview: function() {
            var $preview = $('.slm-composer-attachments');

            if (!$preview.length) {
                $preview = $('<div class="slm-composer-attachments"></div>');
                $('.slm-composer-inner').before($preview);
            }

            if (this.pendingAttachments.length === 0) {
                $preview.hide().empty();
                return;
            }

            var html = '';
            this.pendingAttachments.forEach(function(att) {
                html += '<div class="slm-composer-attachment" data-id="' + att.id + '">';
                html += '<span class="slm-attachment-icon">' + (att.type === 'upload' ? '📤' : '🔗') + '</span>';
                html += '<span class="slm-attachment-name">' + this.escapeHtml(att.filename) + '</span>';
                if (att.size) {
                    html += '<span class="slm-attachment-size">' + att.size + '</span>';
                }
                html += '<button type="button" class="slm-composer-attachment-remove">&times;</button>';
                html += '</div>';
            }.bind(this));

            $preview.html(html).show();
        },

        /**
         * Handle reply focus from email
         */
        handleReplyFocus: function() {
            var params = new URLSearchParams(window.location.search);
            var hash = window.location.hash;

            // Scroll to message if hash present
            if (hash.startsWith('#message-')) {
                var $message = $(hash);
                if ($message.length) {
                    setTimeout(function() {
                        $message[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        $message.addClass('highlight');

                        setTimeout(function() {
                            $message.removeClass('highlight');
                        }, 2000);
                    }, 300);
                }
            }

            // Focus reply input
            if (params.get('reply') === 'true') {
                setTimeout(function() {
                    $('.slm-composer-textarea').focus();
                }, 500);
            }
        },

        /**
         * Mark visible messages as read
         */
        markVisibleAsRead: function() {
            var self = this;
            var $list = $('.slm-messages-list');

            if (!$list.length) {
                return;
            }

            var listTop = $list.offset().top;
            var listBottom = listTop + $list.height();

            $('.slm-message-unread').each(function() {
                var $msg = $(this);
                var msgTop = $msg.offset().top;
                var msgBottom = msgTop + $msg.height();

                // Check if message is visible
                if (msgTop >= listTop && msgBottom <= listBottom) {
                    var messageId = $msg.data('message-id');
                    self.markAsRead(messageId);
                    $msg.removeClass('slm-message-unread');
                }
            });
        },

        /**
         * Mark single message as read
         */
        markAsRead: function(messageId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_mark_message_read',
                    nonce: config.nonce,
                    message_id: messageId
                }
            });
        },

        /**
         * Mark all messages as read
         */
        markAllAsRead: function() {
            var self = this;

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_mark_all_messages_read',
                    nonce: config.nonce,
                    case_id: config.caseId
                },
                success: function(response) {
                    if (response.success) {
                        $('.slm-message-unread').removeClass('slm-message-unread');
                        self.updateUnreadCount();
                    }
                }
            });
        },

        /**
         * Update unread count badge
         */
        updateUnreadCount: function() {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_unread_count',
                    nonce: config.nonce,
                    case_id: config.caseId
                },
                success: function(response) {
                    if (response.success) {
                        var count = response.data.count;
                        var $badge = $('.slm-unread-badge');

                        if (count > 0) {
                            var display = count > 99 ? '99+' : count;
                            if ($badge.length) {
                                $badge.text(display).show();
                            }
                        } else {
                            $badge.hide();
                        }
                    }
                }
            });
        },

        /**
         * Switch message context (all/case/task/document)
         */
        switchContext: function(context) {
            var self = this;

            $('.slm-context-tab').removeClass('active');
            $('.slm-context-tab[data-context="' + context + '"]').addClass('active');

            var $list = $('.slm-messages-list');
            $list.html('<div class="slm-messages-loading">Loading...</div>');

            var data = {
                action: 'slm_get_messages',
                nonce: config.nonce,
                case_id: config.caseId,
                context: context
            };

            // Add specific ID if needed
            var $activeTab = $('.slm-context-tab[data-context="' + context + '"]');
            if ($activeTab.data('task-id')) {
                data.task_id = $activeTab.data('task-id');
            }
            if ($activeTab.data('document-id')) {
                data.document_id = $activeTab.data('document-id');
            }

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $list.html(response.data.html);
                        self.lastTimestamp = response.data.timestamp;
                    } else {
                        $list.html('<p class="slm-no-messages">No messages</p>');
                    }
                }
            });
        },

        /**
         * Scroll to bottom of message list
         */
        scrollToBottom: function() {
            var $list = $('.slm-messages-list');
            if ($list.length) {
                $list.scrollTop($list[0].scrollHeight);
            }
        },

        /**
         * Show notice
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="slm-notice slm-notice-' + type + '">' +
                '<span>' + this.escapeHtml(message) + '</span>' +
                '<button class="slm-notice-close">&times;</button>' +
            '</div>');

            var $container = $('.slm-notices');
            if (!$container.length) {
                $container = $('<div class="slm-notices"></div>');
                $('body').append($container);
            }

            $container.append($notice);

            setTimeout(function() {
                $notice.addClass('slm-notice-visible');
            }, 10);

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
         * Escape HTML
         */
        escapeHtml: function(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize
    $(document).ready(function() {
        Messaging.init();
    });

    // Expose
    window.SLMMessaging = Messaging;

})(jQuery);
