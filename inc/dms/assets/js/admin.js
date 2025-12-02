/**
 * SLM DMS Admin JavaScript
 * 
 * Handles:
 * - Document uploads with drag/drop
 * - Folder navigation
 * - Modal dialogs
 * - Share link management
 * - Version viewing
 * - Toast notifications
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Global namespace
    window.SLMDMS = window.SLMDMS || {};
    
    /**
     * Configuration
     */
    const config = {
        ajaxUrl: slmDMS?.ajaxUrl || ajaxurl,
        restUrl: slmDMS?.restUrl || '/wp-json/slm/v1/',
        nonce: slmDMS?.nonce || '',
        maxFileSize: 52428800, // 50MB
        allowedTypes: [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'text/plain'
        ]
    };
    
    /**
     * State
     */
    const state = {
        currentFolder: 0,
        currentCase: 0,
        selectedDocuments: [],
        uploadQueue: [],
        isUploading: false
    };
    
    /**
     * Initialize
     */
    function init() {
        initUploadModal();
        initDropzone();
        initFolderNavigation();
        initDocumentSelection();
        initShareModal();
        initVersionViewer();
        initToasts();
        initKeyboardShortcuts();
    }
    
    /**
     * Upload Modal
     */
    function initUploadModal() {
        // Open upload modal
        $(document).on('click', '[data-action="upload-document"]', function(e) {
            e.preventDefault();
            openModal('upload-modal');
        });
        
        // Close modal
        $(document).on('click', '.slm-modal-close, .slm-modal-overlay', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // File input change
        $(document).on('change', '#slm-file-input', function() {
            handleFileSelect(this.files);
        });
        
        // Upload form submit
        $(document).on('submit', '#slm-upload-form', function(e) {
            e.preventDefault();
            processUpload();
        });
        
        // Remove selected file
        $(document).on('click', '.slm-file-preview-remove', function() {
            clearFilePreview();
        });
    }
    
    /**
     * Dropzone
     */
    function initDropzone() {
        const dropzone = document.querySelector('.slm-dropzone');
        
        if (!dropzone) return;
        
        // Click to select
        dropzone.addEventListener('click', function() {
            document.getElementById('slm-file-input')?.click();
        });
        
        // Drag events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'));
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'));
        });
        
        // Handle drop
        dropzone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            handleFileSelect(files);
        });
    }
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    /**
     * Handle file selection
     */
    function handleFileSelect(files) {
        if (!files || files.length === 0) return;
        
        const file = files[0];
        
        // Validate file
        if (file.size > config.maxFileSize) {
            showToast('File is too large. Maximum size is 50MB.', 'error');
            return;
        }
        
        if (!config.allowedTypes.includes(file.type)) {
            showToast('File type not allowed.', 'error');
            return;
        }
        
        // Show preview
        showFilePreview(file);
        
        // Store file for upload
        state.uploadQueue = [file];
    }
    
    /**
     * Show file preview
     */
    function showFilePreview(file) {
        const preview = document.querySelector('.slm-file-preview');
        const dropzone = document.querySelector('.slm-dropzone');
        
        if (!preview) return;
        
        const icon = getFileIcon(file.type);
        const size = formatFileSize(file.size);
        
        preview.innerHTML = `
            <div class="slm-file-preview-icon">${icon}</div>
            <div class="slm-file-preview-info">
                <div class="slm-file-preview-name">${escapeHtml(file.name)}</div>
                <div class="slm-file-preview-size">${size}</div>
            </div>
            <button type="button" class="slm-file-preview-remove">×</button>
        `;
        
        preview.style.display = 'flex';
        if (dropzone) dropzone.style.display = 'none';
    }
    
    /**
     * Clear file preview
     */
    function clearFilePreview() {
        const preview = document.querySelector('.slm-file-preview');
        const dropzone = document.querySelector('.slm-dropzone');
        const fileInput = document.getElementById('slm-file-input');
        
        if (preview) {
            preview.innerHTML = '';
            preview.style.display = 'none';
        }
        
        if (dropzone) dropzone.style.display = 'block';
        if (fileInput) fileInput.value = '';
        
        state.uploadQueue = [];
    }
    
    /**
     * Process upload
     */
    function processUpload() {
        if (state.uploadQueue.length === 0) {
            showToast('Please select a file to upload.', 'warning');
            return;
        }
        
        if (state.isUploading) return;
        
        state.isUploading = true;
        
        const file = state.uploadQueue[0];
        const formData = new FormData();
        
        formData.append('action', 'slm_upload_document');
        formData.append('nonce', config.nonce);
        formData.append('document', file);
        formData.append('title', document.getElementById('slm-doc-title')?.value || '');
        formData.append('description', document.getElementById('slm-doc-description')?.value || '');
        formData.append('folder_id', state.currentFolder);
        formData.append('case_id', state.currentCase);
        formData.append('category', document.getElementById('slm-doc-category')?.value || '');
        
        // Show loading
        const submitBtn = document.querySelector('#slm-upload-form button[type="submit"]');
        const originalText = submitBtn?.textContent;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="slm-spinner"></span> Uploading...';
        }
        
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('Document uploaded successfully!', 'success');
                    closeModal();
                    refreshDocumentList();
                } else {
                    showToast(response.data?.message || 'Upload failed.', 'error');
                }
            },
            error: function() {
                showToast('Upload failed. Please try again.', 'error');
            },
            complete: function() {
                state.isUploading = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            }
        });
    }
    
    /**
     * Folder Navigation
     */
    function initFolderNavigation() {
        // Folder click
        $(document).on('click', '.slm-folder-item', function(e) {
            e.preventDefault();
            
            const folderId = $(this).data('folder-id');
            navigateToFolder(folderId);
        });
        
        // Create folder
        $(document).on('click', '[data-action="create-folder"]', function(e) {
            e.preventDefault();
            
            const name = prompt('Enter folder name:');
            if (name) {
                createFolder(name);
            }
        });
        
        // Rename folder
        $(document).on('click', '[data-action="rename-folder"]', function(e) {
            e.preventDefault();
            
            const folderId = $(this).data('folder-id');
            const currentName = $(this).data('folder-name');
            const newName = prompt('Enter new name:', currentName);
            
            if (newName && newName !== currentName) {
                renameFolder(folderId, newName);
            }
        });
        
        // Delete folder
        $(document).on('click', '[data-action="delete-folder"]', function(e) {
            e.preventDefault();
            
            const folderId = $(this).data('folder-id');
            
            if (confirm('Are you sure you want to delete this folder?')) {
                deleteFolder(folderId);
            }
        });
    }
    
    function navigateToFolder(folderId) {
        state.currentFolder = folderId;
        
        // Update active state
        $('.slm-folder-item').removeClass('active');
        $(`.slm-folder-item[data-folder-id="${folderId}"]`).addClass('active');
        
        // Load folder contents
        loadFolderContents(folderId);
    }
    
    function loadFolderContents(folderId) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_get_folder_contents',
                nonce: config.nonce,
                folder_id: folderId,
                case_id: state.currentCase
            },
            beforeSend: function() {
                $('.slm-document-list').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    renderFolderContents(response.data);
                }
            },
            complete: function() {
                $('.slm-document-list').removeClass('loading');
            }
        });
    }
    
    function renderFolderContents(data) {
        // Render implementation would go here
        // This would populate the document grid/table with folder contents
    }
    
    function createFolder(name) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_create_folder',
                nonce: config.nonce,
                name: name,
                parent_id: state.currentFolder,
                case_id: state.currentCase
            },
            success: function(response) {
                if (response.success) {
                    showToast('Folder created successfully!', 'success');
                    refreshFolderTree();
                } else {
                    showToast(response.data?.message || 'Failed to create folder.', 'error');
                }
            }
        });
    }
    
    function renameFolder(folderId, newName) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_rename_folder',
                nonce: config.nonce,
                folder_id: folderId,
                name: newName
            },
            success: function(response) {
                if (response.success) {
                    showToast('Folder renamed!', 'success');
                    refreshFolderTree();
                } else {
                    showToast(response.data?.message || 'Failed to rename folder.', 'error');
                }
            }
        });
    }
    
    function deleteFolder(folderId) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_delete_folder',
                nonce: config.nonce,
                folder_id: folderId
            },
            success: function(response) {
                if (response.success) {
                    showToast('Folder deleted!', 'success');
                    refreshFolderTree();
                } else {
                    showToast(response.data?.message || 'Failed to delete folder.', 'error');
                }
            }
        });
    }
    
    function refreshFolderTree() {
        // Reload folder tree
        loadFolderTree();
    }
    
    function loadFolderTree() {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_get_folder_tree',
                nonce: config.nonce,
                case_id: state.currentCase
            },
            success: function(response) {
                if (response.success) {
                    renderFolderTree(response.data.tree);
                }
            }
        });
    }
    
    function renderFolderTree(tree, container = '.slm-folder-tree', level = 0) {
        // Tree rendering implementation
    }
    
    /**
     * Document Selection
     */
    function initDocumentSelection() {
        // Single click to select
        $(document).on('click', '.slm-document-card', function(e) {
            if (e.ctrlKey || e.metaKey) {
                // Multi-select
                $(this).toggleClass('selected');
                updateSelection();
            } else {
                // Single select
                $('.slm-document-card').removeClass('selected');
                $(this).addClass('selected');
                updateSelection();
            }
        });
        
        // Double click to view
        $(document).on('dblclick', '.slm-document-card', function() {
            const docId = $(this).data('document-id');
            viewDocument(docId);
        });
        
        // Bulk actions
        $(document).on('click', '[data-action="bulk-delete"]', function() {
            if (state.selectedDocuments.length === 0) {
                showToast('Please select documents first.', 'warning');
                return;
            }
            
            if (confirm(`Delete ${state.selectedDocuments.length} document(s)?`)) {
                bulkDelete(state.selectedDocuments);
            }
        });
        
        $(document).on('click', '[data-action="bulk-move"]', function() {
            if (state.selectedDocuments.length === 0) {
                showToast('Please select documents first.', 'warning');
                return;
            }
            
            openMoveModal();
        });
    }
    
    function updateSelection() {
        state.selectedDocuments = [];
        
        $('.slm-document-card.selected').each(function() {
            state.selectedDocuments.push($(this).data('document-id'));
        });
        
        // Update UI based on selection
        const hasSelection = state.selectedDocuments.length > 0;
        $('[data-requires-selection]').prop('disabled', !hasSelection);
    }
    
    function viewDocument(docId) {
        // Create viewing session and open viewer
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_create_view_session',
                nonce: config.nonce,
                document_id: docId
            },
            success: function(response) {
                if (response.success) {
                    window.open(response.data.viewer_url, '_blank');
                } else {
                    showToast(response.data?.message || 'Failed to open document.', 'error');
                }
            }
        });
    }
    
    function bulkDelete(docIds) {
        // Implementation for bulk delete
        let completed = 0;
        const total = docIds.length;
        
        docIds.forEach(function(docId) {
            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_delete_document',
                    nonce: config.nonce,
                    document_id: docId
                },
                success: function() {
                    completed++;
                    if (completed === total) {
                        showToast(`${total} document(s) deleted.`, 'success');
                        refreshDocumentList();
                    }
                }
            });
        });
    }
    
    /**
     * Share Modal
     */
    function initShareModal() {
        // Open share modal
        $(document).on('click', '[data-action="share-document"]', function(e) {
            e.preventDefault();
            
            const docId = $(this).data('document-id');
            openShareModal(docId);
        });
        
        // Create share link
        $(document).on('submit', '#slm-share-form', function(e) {
            e.preventDefault();
            createShareLink();
        });
        
        // Copy share URL
        $(document).on('click', '[data-action="copy-share-url"]', function() {
            const url = $(this).siblings('input').val();
            copyToClipboard(url);
            showToast('Link copied to clipboard!', 'success');
        });
        
        // Revoke share link
        $(document).on('click', '[data-action="revoke-share"]', function() {
            const linkId = $(this).data('link-id');
            
            if (confirm('Revoke this share link?')) {
                revokeShareLink(linkId);
            }
        });
    }
    
    function openShareModal(docId) {
        state.currentDocument = docId;
        
        // Load existing share links
        loadShareLinks(docId);
        
        openModal('share-modal');
    }
    
    function loadShareLinks(docId) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_get_share_links',
                nonce: config.nonce,
                document_id: docId
            },
            success: function(response) {
                if (response.success) {
                    renderShareLinks(response.data.links);
                }
            }
        });
    }
    
    function renderShareLinks(links) {
        const container = document.getElementById('slm-share-links-list');
        if (!container) return;
        
        if (links.length === 0) {
            container.innerHTML = '<p class="slm-empty-text">No active share links.</p>';
            return;
        }
        
        let html = '';
        links.forEach(function(link) {
            const statusClass = `status-${link.status}`;
            html += `
                <div class="slm-share-link-item">
                    <div class="slm-share-link-info">
                        <div class="slm-share-link-url">${escapeHtml(link.url)}</div>
                        <div class="slm-share-link-meta">
                            <span class="${statusClass}">${link.status}</span>
                            <span>Expires: ${link.expiry_date}</span>
                            <span>Views: ${link.view_count}${link.max_views > 0 ? '/' + link.max_views : ''}</span>
                        </div>
                    </div>
                    <div class="slm-share-link-actions">
                        <button type="button" class="slm-btn slm-btn-sm slm-btn-secondary" data-action="copy-share-url">Copy</button>
                        ${link.status === 'active' ? `<button type="button" class="slm-btn slm-btn-sm slm-btn-danger" data-action="revoke-share" data-link-id="${link.id}">Revoke</button>` : ''}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function createShareLink() {
        const form = document.getElementById('slm-share-form');
        if (!form) return;
        
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_create_share_link',
                nonce: config.nonce,
                document_id: state.currentDocument,
                expiry_hours: form.querySelector('[name="expiry_hours"]')?.value || 168,
                password: form.querySelector('[name="password"]')?.value || '',
                download_allowed: form.querySelector('[name="download_allowed"]')?.checked ? 'true' : 'false',
                max_views: form.querySelector('[name="max_views"]')?.value || 0,
                recipient_name: form.querySelector('[name="recipient_name"]')?.value || '',
                recipient_email: form.querySelector('[name="recipient_email"]')?.value || ''
            },
            success: function(response) {
                if (response.success) {
                    showToast('Share link created!', 'success');
                    loadShareLinks(state.currentDocument);
                    
                    // Show the new URL
                    copyToClipboard(response.data.url);
                    showToast('Link copied to clipboard!', 'info');
                } else {
                    showToast(response.data?.message || 'Failed to create share link.', 'error');
                }
            }
        });
    }
    
    function revokeShareLink(linkId) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_revoke_share_link',
                nonce: config.nonce,
                link_id: linkId
            },
            success: function(response) {
                if (response.success) {
                    showToast('Share link revoked.', 'success');
                    loadShareLinks(state.currentDocument);
                } else {
                    showToast(response.data?.message || 'Failed to revoke link.', 'error');
                }
            }
        });
    }
    
    /**
     * Version Viewer
     */
    function initVersionViewer() {
        // Load versions
        $(document).on('click', '[data-action="view-versions"]', function(e) {
            e.preventDefault();
            
            const docId = $(this).data('document-id');
            loadVersions(docId);
        });
        
        // View specific version
        $(document).on('click', '[data-action="view-version"]', function(e) {
            e.preventDefault();
            
            const docId = $(this).data('document-id');
            const version = $(this).data('version');
            viewDocumentVersion(docId, version);
        });
    }
    
    function loadVersions(docId) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_get_document_versions',
                nonce: config.nonce,
                document_id: docId
            },
            success: function(response) {
                if (response.success) {
                    renderVersions(response.data.versions);
                    openModal('versions-modal');
                }
            }
        });
    }
    
    function renderVersions(versions) {
        const container = document.getElementById('slm-versions-list');
        if (!container) return;
        
        let html = '';
        versions.forEach(function(version) {
            const currentClass = version.is_current ? 'current' : '';
            html += `
                <div class="slm-version-item ${currentClass}">
                    <div class="slm-version-number">v${version.version_number}</div>
                    <div class="slm-version-info">
                        <div class="slm-version-date">${version.created_at}</div>
                        <div class="slm-version-author">by ${escapeHtml(version.uploaded_by_name)}</div>
                        ${version.upload_note ? `<div class="slm-version-note">${escapeHtml(version.upload_note)}</div>` : ''}
                    </div>
                    <div class="slm-version-actions">
                        <button type="button" class="slm-btn slm-btn-sm slm-btn-secondary" 
                                data-action="view-version" 
                                data-document-id="${version.document_id}" 
                                data-version="${version.version_number}">
                            View
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function viewDocumentVersion(docId, version) {
        $.ajax({
            url: config.ajaxUrl,
            type: 'POST',
            data: {
                action: 'slm_create_view_session',
                nonce: config.nonce,
                document_id: docId,
                version: version
            },
            success: function(response) {
                if (response.success) {
                    window.open(response.data.viewer_url, '_blank');
                }
            }
        });
    }
    
    /**
     * Modal Management
     */
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal() {
        document.querySelectorAll('.slm-modal-overlay.active').forEach(function(modal) {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
        
        // Reset upload form
        clearFilePreview();
        document.getElementById('slm-upload-form')?.reset();
    }
    
    /**
     * Toast Notifications
     */
    function initToasts() {
        // Create toast container if it doesn't exist
        if (!document.querySelector('.slm-toast-container')) {
            const container = document.createElement('div');
            container.className = 'slm-toast-container';
            document.body.appendChild(container);
        }
    }
    
    function showToast(message, type = 'info') {
        const container = document.querySelector('.slm-toast-container');
        if (!container) return;
        
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        const toast = document.createElement('div');
        toast.className = `slm-toast ${type}`;
        toast.innerHTML = `
            <span class="slm-toast-icon">${icons[type] || icons.info}</span>
            <span class="slm-toast-message">${escapeHtml(message)}</span>
            <button type="button" class="slm-toast-close">×</button>
        `;
        
        container.appendChild(toast);
        
        // Close button
        toast.querySelector('.slm-toast-close').addEventListener('click', function() {
            removeToast(toast);
        });
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            removeToast(toast);
        }, 5000);
    }
    
    function removeToast(toast) {
        toast.classList.add('hiding');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }
    
    /**
     * Keyboard Shortcuts
     */
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Escape to close modal
            if (e.key === 'Escape') {
                closeModal();
            }
            
            // Ctrl+U to upload
            if (e.ctrlKey && e.key === 'u') {
                e.preventDefault();
                openModal('upload-modal');
            }
            
            // Delete selected
            if (e.key === 'Delete' && state.selectedDocuments.length > 0) {
                e.preventDefault();
                if (confirm(`Delete ${state.selectedDocuments.length} document(s)?`)) {
                    bulkDelete(state.selectedDocuments);
                }
            }
        });
    }
    
    /**
     * Utility Functions
     */
    function refreshDocumentList() {
        loadFolderContents(state.currentFolder);
    }
    
    function getFileIcon(mimeType) {
        const icons = {
            'application/pdf': '📄',
            'application/msword': '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '📝',
            'application/vnd.ms-excel': '📊',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': '📊',
            'image/jpeg': '🖼️',
            'image/png': '🖼️',
            'text/plain': '📃'
        };
        
        return icons[mimeType] || '📁';
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }
    
    /**
     * Expose public API
     */
    SLMDMS.showToast = showToast;
    SLMDMS.openModal = openModal;
    SLMDMS.closeModal = closeModal;
    SLMDMS.viewDocument = viewDocument;
    SLMDMS.refreshDocuments = refreshDocumentList;
    SLMDMS.state = state;
    
    /**
     * Initialize on DOM ready
     */
    $(document).ready(init);
    
})(jQuery);
