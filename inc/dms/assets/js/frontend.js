/**
 * SLM DMS Frontend JavaScript
 * 
 * Client-facing document interactions:
 * - Document viewing
 * - Folder navigation
 * - Search/filter
 * - Download handling
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

(function() {
    'use strict';
    
    // Global namespace
    window.SLMDMSPortal = window.SLMDMSPortal || {};
    
    /**
     * Configuration - set from PHP
     */
    const config = {
        ajaxUrl: window.slmDMSConfig?.ajaxUrl || '/wp-admin/admin-ajax.php',
        restUrl: window.slmDMSConfig?.restUrl || '/wp-json/slm/v1/',
        nonce: window.slmDMSConfig?.nonce || '',
        caseId: window.slmDMSConfig?.caseId || 0,
        userId: window.slmDMSConfig?.userId || 0
    };
    
    /**
     * State
     */
    const state = {
        currentFolder: 0,
        documents: [],
        folders: [],
        searchQuery: '',
        loading: false
    };
    
    /**
     * Initialize
     */
    function init() {
        // Only init if we have the portal container
        if (!document.querySelector('.slm-portal-documents')) {
            return;
        }
        
        initSearch();
        initFolderNav();
        initDocumentActions();
        initTabs();
        initKeyboardNav();
        
        // Load initial data
        if (config.caseId) {
            loadDocuments();
        }
    }
    
    /**
     * Search functionality
     */
    function initSearch() {
        const searchInput = document.querySelector('.slm-portal-search input');
        if (!searchInput) return;
        
        let debounceTimer;
        
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                state.searchQuery = e.target.value.trim();
                filterDocuments();
            }, 300);
        });
        
        // Clear search on escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                state.searchQuery = '';
                filterDocuments();
            }
        });
    }
    
    /**
     * Filter documents based on search query
     */
    function filterDocuments() {
        const cards = document.querySelectorAll('.slm-doc-card');
        const query = state.searchQuery.toLowerCase();
        
        cards.forEach(function(card) {
            const title = card.querySelector('.slm-doc-card-title')?.textContent.toLowerCase() || '';
            const desc = card.querySelector('.slm-doc-card-desc')?.textContent.toLowerCase() || '';
            const tags = card.querySelector('.slm-doc-card-tags')?.textContent.toLowerCase() || '';
            
            const matches = !query || 
                title.includes(query) || 
                desc.includes(query) || 
                tags.includes(query);
            
            card.style.display = matches ? '' : 'none';
        });
        
        // Show empty state if no results
        updateEmptyState();
    }
    
    /**
     * Update empty state visibility
     */
    function updateEmptyState() {
        const container = document.querySelector('.slm-document-cards');
        const emptyState = document.querySelector('.slm-empty-state');
        
        if (!container) return;
        
        const visibleCards = container.querySelectorAll('.slm-doc-card:not([style*="display: none"])');
        
        if (emptyState) {
            emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
        }
    }
    
    /**
     * Folder navigation
     */
    function initFolderNav() {
        // Folder card clicks
        document.addEventListener('click', function(e) {
            const folderCard = e.target.closest('.slm-folder-card');
            if (folderCard) {
                e.preventDefault();
                const folderId = folderCard.dataset.folderId;
                navigateToFolder(folderId);
            }
        });
        
        // Breadcrumb clicks
        document.addEventListener('click', function(e) {
            const breadcrumb = e.target.closest('.slm-breadcrumb-item');
            if (breadcrumb && !breadcrumb.classList.contains('current')) {
                e.preventDefault();
                const folderId = breadcrumb.dataset.folderId || 0;
                navigateToFolder(folderId);
            }
        });
    }
    
    /**
     * Navigate to folder
     */
    function navigateToFolder(folderId) {
        state.currentFolder = parseInt(folderId) || 0;
        loadFolderContents();
    }
    
    /**
     * Load folder contents
     */
    function loadFolderContents() {
        if (state.loading) return;
        
        state.loading = true;
        showLoading();
        
        fetch(config.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'slm_get_folder_contents',
                nonce: config.nonce,
                folder_id: state.currentFolder,
                case_id: config.caseId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderFolderContents(data.data);
            }
        })
        .catch(error => {
            console.error('Failed to load folder:', error);
            showNotification('Failed to load folder contents', 'error');
        })
        .finally(() => {
            state.loading = false;
            hideLoading();
        });
    }
    
    /**
     * Render folder contents
     */
    function renderFolderContents(data) {
        // Update breadcrumb
        updateBreadcrumb(data.breadcrumb || []);
        
        // Render folders
        renderFolders(data.folders || []);
        
        // Render documents
        renderDocuments(data.documents || []);
    }
    
    /**
     * Update breadcrumb
     */
    function updateBreadcrumb(path) {
        const container = document.querySelector('.slm-breadcrumb');
        if (!container) return;
        
        let html = '<a href="#" class="slm-breadcrumb-item" data-folder-id="0">Home</a>';
        
        path.forEach(function(item, index) {
            html += '<span class="slm-breadcrumb-separator">/</span>';
            
            if (index === path.length - 1) {
                html += `<span class="slm-breadcrumb-item current">${escapeHtml(item.name)}</span>`;
            } else {
                html += `<a href="#" class="slm-breadcrumb-item" data-folder-id="${item.id}">${escapeHtml(item.name)}</a>`;
            }
        });
        
        container.innerHTML = html;
    }
    
    /**
     * Render folders
     */
    function renderFolders(folders) {
        const container = document.querySelector('.slm-folder-grid');
        if (!container) return;
        
        if (folders.length === 0) {
            container.style.display = 'none';
            return;
        }
        
        container.style.display = '';
        
        let html = '';
        folders.forEach(function(folder) {
            html += `
                <a href="#" class="slm-folder-card" data-folder-id="${folder.id}">
                    <span class="slm-folder-card-icon">📁</span>
                    <span class="slm-folder-card-name">${escapeHtml(folder.name)}</span>
                </a>
            `;
        });
        
        container.innerHTML = html;
    }
    
    /**
     * Render documents
     */
    function renderDocuments(documents) {
        const container = document.querySelector('.slm-document-cards');
        if (!container) return;
        
        if (documents.length === 0) {
            container.innerHTML = `
                <div class="slm-empty-state">
                    <div class="slm-empty-icon">📄</div>
                    <h3 class="slm-empty-title">No documents</h3>
                    <p class="slm-empty-text">This folder is empty.</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        documents.forEach(function(doc) {
            const icon = getFileIcon(doc.mime_type);
            const iconClass = getFileIconClass(doc.mime_type);
            
            html += `
                <div class="slm-doc-card" data-document-id="${doc.id}">
                    <div class="slm-doc-card-header">
                        <div class="slm-doc-card-icon ${iconClass}">${icon}</div>
                        <div>
                            <h3 class="slm-doc-card-title">${escapeHtml(doc.title)}</h3>
                            <div class="slm-doc-card-meta">${formatDate(doc.created_at)}</div>
                        </div>
                    </div>
                    ${doc.description ? `
                    <div class="slm-doc-card-body">
                        <p class="slm-doc-card-desc">${escapeHtml(doc.description)}</p>
                    </div>
                    ` : ''}
                    ${doc.tags && doc.tags.length ? `
                    <div class="slm-doc-card-tags">
                        ${doc.tags.map(tag => `<span class="slm-doc-tag">${escapeHtml(tag)}</span>`).join('')}
                    </div>
                    ` : ''}
                    <div class="slm-doc-card-actions">
                        <button type="button" class="slm-doc-btn slm-doc-btn-primary" data-action="view" data-document-id="${doc.id}">
                            View
                        </button>
                        ${doc.download_allowed ? `
                        <button type="button" class="slm-doc-btn slm-doc-btn-secondary" data-action="download" data-document-id="${doc.id}">
                            Download
                        </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    /**
     * Document actions
     */
    function initDocumentActions() {
        // View document
        document.addEventListener('click', function(e) {
            const viewBtn = e.target.closest('[data-action="view"]');
            if (viewBtn) {
                e.preventDefault();
                const docId = viewBtn.dataset.documentId;
                viewDocument(docId);
            }
        });
        
        // Download document
        document.addEventListener('click', function(e) {
            const downloadBtn = e.target.closest('[data-action="download"]');
            if (downloadBtn) {
                e.preventDefault();
                const docId = downloadBtn.dataset.documentId;
                downloadDocument(docId);
            }
        });
        
        // Sign document (from signing request cards)
        document.addEventListener('click', function(e) {
            const signBtn = e.target.closest('[data-action="sign"]');
            if (signBtn) {
                const signUrl = signBtn.dataset.signUrl;
                if (signUrl) {
                    window.location.href = signUrl;
                }
            }
        });
    }
    
    /**
     * View document
     */
    function viewDocument(docId) {
        // Create viewing session
        fetch(config.ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'slm_create_view_session',
                nonce: config.nonce,
                document_id: docId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.viewer_url) {
                window.open(data.data.viewer_url, '_blank');
            } else {
                showNotification(data.data?.message || 'Failed to open document', 'error');
            }
        })
        .catch(error => {
            console.error('View error:', error);
            showNotification('Failed to open document', 'error');
        });
    }
    
    /**
     * Download document
     */
    function downloadDocument(docId) {
        const downloadUrl = config.ajaxUrl + '?' + new URLSearchParams({
            action: 'slm_download_document',
            document_id: docId,
            nonce: config.nonce
        });
        
        window.location.href = downloadUrl;
    }
    
    /**
     * Load documents
     */
    function loadDocuments() {
        loadFolderContents();
    }
    
    /**
     * Tabs functionality
     */
    function initTabs() {
        document.addEventListener('click', function(e) {
            const tab = e.target.closest('.slm-detail-tab');
            if (!tab) return;
            
            e.preventDefault();
            
            const tabGroup = tab.closest('.slm-detail-tabs');
            const panelContainer = tabGroup?.parentElement;
            
            if (!panelContainer) return;
            
            // Update active tab
            tabGroup.querySelectorAll('.slm-detail-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            
            // Show corresponding panel
            const targetPanel = tab.dataset.tab;
            panelContainer.querySelectorAll('.slm-detail-panel').forEach(p => p.classList.remove('active'));
            
            const panel = panelContainer.querySelector(`[data-panel="${targetPanel}"]`);
            if (panel) {
                panel.classList.add('active');
            }
        });
    }
    
    /**
     * Keyboard navigation
     */
    function initKeyboardNav() {
        document.addEventListener('keydown', function(e) {
            // Focus search on /
            if (e.key === '/' && !isInputFocused()) {
                e.preventDefault();
                const searchInput = document.querySelector('.slm-portal-search input');
                if (searchInput) searchInput.focus();
            }
            
            // Navigate up on backspace (when not in input)
            if (e.key === 'Backspace' && !isInputFocused() && state.currentFolder > 0) {
                e.preventDefault();
                // Would need parent folder ID - simplified version
                navigateToFolder(0);
            }
        });
    }
    
    /**
     * Check if an input is focused
     */
    function isInputFocused() {
        const active = document.activeElement;
        return active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable);
    }
    
    /**
     * Show loading state
     */
    function showLoading() {
        const container = document.querySelector('.slm-document-cards');
        if (!container) return;
        
        // Add loading overlay or skeleton
        const existing = container.querySelector('.slm-loading');
        if (!existing) {
            const loading = document.createElement('div');
            loading.className = 'slm-loading';
            loading.innerHTML = `
                <div class="slm-loading-spinner"></div>
                <span class="slm-loading-text">Loading...</span>
            `;
            container.prepend(loading);
        }
    }
    
    /**
     * Hide loading state
     */
    function hideLoading() {
        const loading = document.querySelector('.slm-loading');
        if (loading) {
            loading.remove();
        }
    }
    
    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        // Check if notification container exists
        let container = document.querySelector('.slm-notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'slm-notification-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:10000;';
            document.body.appendChild(container);
        }
        
        const notification = document.createElement('div');
        notification.className = `slm-notification slm-notification-${type}`;
        notification.innerHTML = `
            <span class="slm-notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
            <div class="slm-notification-content">
                <span class="slm-notification-text">${escapeHtml(message)}</span>
            </div>
        `;
        
        container.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
    
    /**
     * Utility: Get file icon
     */
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
    
    /**
     * Utility: Get file icon class
     */
    function getFileIconClass(mimeType) {
        if (mimeType?.includes('pdf')) return 'pdf';
        if (mimeType?.includes('word') || mimeType?.includes('document')) return 'doc';
        if (mimeType?.includes('excel') || mimeType?.includes('sheet')) return 'xls';
        if (mimeType?.includes('image')) return 'img';
        return '';
    }
    
    /**
     * Utility: Format date
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    /**
     * Utility: Escape HTML
     */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Public API
     */
    SLMDMSPortal.viewDocument = viewDocument;
    SLMDMSPortal.downloadDocument = downloadDocument;
    SLMDMSPortal.navigateToFolder = navigateToFolder;
    SLMDMSPortal.showNotification = showNotification;
    SLMDMSPortal.refresh = loadDocuments;
    SLMDMSPortal.state = state;
    
    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
