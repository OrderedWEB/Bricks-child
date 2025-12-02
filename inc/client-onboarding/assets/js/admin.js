/**
 * SLM Client Onboarding - Admin JavaScript
 * 
 * Handles client search, details panel, and magic link sending.
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main Application Object
     */
    const SLMClientOnboarding = {
        
        /**
         * Current state
         */
        state: {
            selectedClientId: null,
            selectedClientEmail: null,
            searchTimeout: null,
            isLoading: false
        },

        /**
         * DOM Elements
         */
        elements: {},

        /**
         * Initialize the application
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.elements = {
                searchInput: $('#slm-client-search-input'),
                searchBtn: $('#slm-search-btn'),
                searchResults: $('#slm-search-results'),
                detailsPanel: $('#slm-client-details-panel'),
                clientName: $('#slm-client-name'),
                closePanel: $('#slm-close-panel'),
                onboardingStatus: $('#slm-onboarding-status'),
                actionButtons: $('#slm-action-buttons'),
                detailsAccordion: $('#slm-details-accordion'),
                sendLinkModal: $('#slm-send-link-modal'),
                modalEmail: $('#slm-modal-email'),
                modalWarning: $('#slm-modal-warning'),
                warningText: $('#slm-warning-text'),
                confirmSendLink: $('#slm-confirm-send-link'),
                loadingOverlay: $('#slm-loading-overlay'),
                loadingText: $('#slm-loading-text')
            };
        },
/**
 * View client documents
 */
viewClientDocuments: function() {
    const self = this;

    if (!this.state.selectedClientId) {
        this.showToast('No client selected', 'error');
        return;
    }

    this.showLoading('Loading documents...');

    $.ajax({
        url: slmOnboarding.ajaxUrl,
        type: 'POST',
        data: {
            action: 'slm_get_client_documents',
            nonce: slmOnboarding.nonce,
            user_id: this.state.selectedClientId
        },
        success: function(response) {
            self.hideLoading();
            if (response.success) {
                self.showDocumentsModal(response.data.documents);
            } else {
                self.showToast(response.data.message || 'Error loading documents', 'error');
            }
        },
        error: function() {
            self.hideLoading();
            self.showToast('Error loading documents', 'error');
        }
    });
},

/**
 * Show documents modal
 */
showDocumentsModal: function(documents) {
    const self = this;
    
    // Remove existing modal
    $('#slm-docs-modal').remove();
    
    let html = '<div id="slm-docs-modal" class="slm-modal">';
    html += '  <div class="slm-modal-overlay"></div>';
    html += '  <div class="slm-modal-content" style="max-width: 600px;">';
    html += '    <button type="button" class="slm-modal-close">&times;</button>';
    html += '    <h3><span class="dashicons dashicons-portfolio"></span> Client Documents</h3>';
    
    if (!documents || documents.length === 0) {
        html += '<p class="slm-no-results">No documents found for this client.</p>';
    } else {
        html += '<div class="slm-documents-list">';
        documents.forEach(function(doc) {
            html += '<div class="slm-document-row">';
            html += '  <div class="slm-doc-icon"><span class="dashicons dashicons-' + self.getDocIcon(doc.type) + '"></span></div>';
            html += '  <div class="slm-doc-info">';
            html += '    <div class="slm-doc-name">' + self.escapeHtml(doc.name) + '</div>';
            html += '    <div class="slm-doc-meta">' + doc.date + ' &bull; ' + doc.size + '</div>';
            html += '  </div>';
            html += '  <div class="slm-doc-actions">';
            html += '    <a href="' + doc.download_url + '" class="slm-btn-icon" title="Download" target="_blank">';
            html += '      <span class="dashicons dashicons-download"></span>';
            html += '    </a>';
            if (doc.view_url) {
                html += '    <a href="' + doc.view_url + '" class="slm-btn-icon" title="View" target="_blank">';
                html += '      <span class="dashicons dashicons-visibility"></span>';
                html += '    </a>';
            }
            html += '  </div>';
            html += '</div>';
        });
        html += '</div>';
    }
    
    html += '  </div>';
    html += '</div>';
    
    $('body').append(html);
    
    // Bind close events
    $('#slm-docs-modal').find('.slm-modal-overlay, .slm-modal-close').on('click', function() {
        $('#slm-docs-modal').remove();
    });
},

/**
 * Get document icon based on type
 */
getDocIcon: function(type) {
    const icons = {
        'pdf': 'pdf',
        'terms': 'media-document',
        'contract': 'media-text',
        'default': 'media-default'
    };
    return icons[type] || icons['default'];
},
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            const self = this;

            // Search input
            this.elements.searchInput.on('input', function() {
                self.handleSearchInput($(this).val());
            });

            this.elements.searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.performSearch($(this).val());
                }
            });

            // Search button
            this.elements.searchBtn.on('click', function() {
                self.performSearch(self.elements.searchInput.val());
            });

            // Close panel
            this.elements.closePanel.on('click', function() {
                self.closeDetailsPanel();
            });

            // Modal events
            this.elements.sendLinkModal.find('.slm-modal-overlay, .slm-modal-close, .slm-modal-cancel').on('click', function() {
                self.closeModal();
            });

            this.elements.confirmSendLink.on('click', function() {
                self.sendMagicLink();
            });

            // Escape key to close modal/panel
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (self.elements.sendLinkModal.is(':visible')) {
                        self.closeModal();
                    } else if (self.elements.detailsPanel.is(':visible')) {
                        self.closeDetailsPanel();
                    }
                }
            });
        },

        /**
         * Handle search input with debounce
         */
        handleSearchInput: function(value) {
            const self = this;

            clearTimeout(this.state.searchTimeout);

            if (value.length < 2) {
                this.elements.searchResults.html(
                    '<p class="slm-no-search">' + slmOnboarding.strings.selectClient + '</p>'
                );
                return;
            }

            this.state.searchTimeout = setTimeout(function() {
                self.performSearch(value);
            }, 300);
        },

        /**
         * Perform search
         */
        performSearch: function(term) {
            const self = this;

            if (term.length < 2) {
                return;
            }

            this.elements.searchResults.html(
                '<p class="slm-no-search">' + slmOnboarding.strings.searching + '</p>'
            );

            $.ajax({
                url: slmOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_search_clients',
                    nonce: slmOnboarding.nonce,
                    search: term
                },
                success: function(response) {
                    if (response.success) {
                        self.renderSearchResults(response.data.clients);
                    } else {
                        self.showToast(response.data.message || slmOnboarding.strings.error, 'error');
                    }
                },
                error: function() {
                    self.showToast(slmOnboarding.strings.error, 'error');
                }
            });
        },

        /**
         * Render search results
         */
        renderSearchResults: function(clients) {
            const self = this;

            if (!clients || clients.length === 0) {
                this.elements.searchResults.html(
                    '<p class="slm-no-results">' + slmOnboarding.strings.noResults + '</p>'
                );
                return;
            }

            let html = '<div class="slm-results-header">' + clients.length + ' client(s) found</div>';
            html += '<div class="slm-results-list">';

            clients.forEach(function(client) {
                const initials = self.getInitials(client.full_name);
                const statusClass = self.getStatusClass(client);
                const statusLabel = self.getStatusLabel(client);

                html += '<div class="slm-client-row" data-user-id="' + client.id + '">';
                html += '  <div class="slm-client-avatar">' + initials + '</div>';
                html += '  <div class="slm-client-info">';
                html += '    <div class="slm-client-name">' + self.escapeHtml(client.full_name) + '</div>';
                html += '    <div class="slm-client-email">' + self.escapeHtml(client.email) + '</div>';
                html += '  </div>';
                html += '  <div class="slm-client-status">';
                html += '    <span class="slm-status-badge ' + statusClass + '">' + statusLabel + '</span>';
                html += '  </div>';
                html += '</div>';
            });

            html += '</div>';

            this.elements.searchResults.html(html);

            // Bind click events to rows
            this.elements.searchResults.find('.slm-client-row').on('click', function() {
                const userId = $(this).data('user-id');
                self.selectClient(userId, $(this));
            });
        },

        /**
         * Get initials from name
         */
        getInitials: function(name) {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            if (parts.length === 1) {
                return parts[0].charAt(0).toUpperCase();
            }
            return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
        },

        /**
         * Get status class for client
         */
        getStatusClass: function(client) {
            if (client.onboarding_complete) {
                return 'status-complete';
            }
            if (client.terms_signed) {
                return 'status-partial';
            }
            return 'status-not-started';
        },

        /**
         * Get status label for client
         */
        getStatusLabel: function(client) {
            if (client.onboarding_complete) {
                return 'Complete';
            }
            if (client.terms_signed) {
                return 'In Progress';
            }
            return 'Not Started';
        },

        /**
         * Select a client and load details
         */
        selectClient: function(userId, $row) {
            const self = this;

            // Update UI
            this.elements.searchResults.find('.slm-client-row').removeClass('selected');
            $row.addClass('selected');

            this.state.selectedClientId = userId;
            this.showLoading(slmOnboarding.strings.searching);

            $.ajax({
                url: slmOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_get_client_details',
                    nonce: slmOnboarding.nonce,
                    user_id: userId
                },
                success: function(response) {
                    self.hideLoading();
                    if (response.success) {
                        self.renderClientDetails(response.data);
                        self.openDetailsPanel();
                    } else {
                        self.showToast(response.data.message || slmOnboarding.strings.error, 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showToast(slmOnboarding.strings.error, 'error');
                }
            });
        },

        /**
         * Render client details panel
         */
        renderClientDetails: function(data) {
            this.state.selectedClientEmail = data.email;
            this.elements.clientName.text(data.full_name);

            // Render onboarding status
            this.renderOnboardingStatus(data.onboarding_status);

            // Render action buttons
            this.renderActionButtons(data.onboarding_status);

            // Render field groups accordion
            this.renderFieldGroups(data.field_groups, data.magic_link_history, data.woocommerce_status);
        },

        /**
         * Render onboarding status section
         */
        renderOnboardingStatus: function(status) {
            const self = this;

            let html = '<div class="slm-status-header">';
            html += '  <h3>Onboarding Status</h3>';
            html += '  <span class="slm-status-badge ' + status.status_class + '">' + status.status_label + '</span>';
            html += '</div>';
            html += '<div class="slm-status-steps">';

            // Step 1: Link Sent
            const linkIcon = status.pending_link ? 'yes' : 'minus';
            const linkClass = status.pending_link ? 'complete' : 'incomplete';
            const linkText = status.pending_link 
                ? 'Link sent - expires ' + status.pending_link.expires_at
                : 'No active link';
            html += this.renderStatusStep(linkIcon, linkClass, 'Magic Link', linkText);

            // Step 2: Terms Signed
            const termsIcon = status.terms_signed ? 'yes' : 'minus';
            const termsClass = status.terms_signed ? 'complete' : 'incomplete';
            const termsText = status.terms_signed 
                ? 'Signed on ' + status.terms_signed_date
                : 'Not signed';
            html += this.renderStatusStep(termsIcon, termsClass, 'Terms Agreement', termsText);

            // Step 3: Password Set
            const passIcon = status.password_set ? 'yes' : 'minus';
            const passClass = status.password_set ? 'complete' : 'incomplete';
            const passText = status.password_set ? 'Password set' : 'Not set';
            html += this.renderStatusStep(passIcon, passClass, 'Password', passText);

            // Step 4: Complete
            const completeIcon = status.onboarding_complete ? 'yes' : 'minus';
            const completeClass = status.onboarding_complete ? 'complete' : 'incomplete';
            const completeText = status.onboarding_complete ? 'Account active' : 'Pending';
            html += this.renderStatusStep(completeIcon, completeClass, 'Account Active', completeText);

            html += '</div>';

            this.elements.onboardingStatus.html(html);
        },

        /**
         * Render a single status step
         */
        renderStatusStep: function(icon, iconClass, label, subtext) {
            return '<div class="slm-status-step">' +
                '  <div class="slm-step-icon ' + iconClass + '">' +
                '    <span class="dashicons dashicons-' + icon + '"></span>' +
                '  </div>' +
                '  <div class="slm-step-label">' + label + '<span>' + subtext + '</span></div>' +
                '</div>';
        },

        /**
         * Render action buttons
         */
        renderActionButtons: function(status) {
            const self = this;
            let html = '';

            if (!status.onboarding_complete) {
                // Show send link button
                const btnText = status.pending_link ? 'Resend Onboarding Link' : 'Send Onboarding Link';
                const btnIcon = 'email';
                
                html += '<button type="button" class="slm-action-btn primary" id="slm-send-link-btn">';
                html += '  <span class="dashicons dashicons-' + btnIcon + '"></span>';
                html += '  ' + btnText;
                html += '</button>';
            }

            // View documents button (if folder exists)
            if (status.has_folder) {
                html += '<button type="button" class="slm-action-btn secondary" id="slm-view-docs-btn">';
                html += '  <span class="dashicons dashicons-portfolio"></span>';
                html += '  View Documents';
                html += '</button>';
            }

            // Edit user button
            html += '<a href="' + this.getUserEditUrl() + '" class="slm-action-btn secondary" target="_blank">';
            html += '  <span class="dashicons dashicons-admin-users"></span>';
            html += '  Edit User Profile';
            html += '</a>';

            this.elements.actionButtons.html(html);

            // Bind send link button
            this.elements.actionButtons.find('#slm-send-link-btn').on('click', function() {
                self.openSendLinkModal();
            });
        },
// Bind view documents button
this.elements.actionButtons.find('#slm-view-docs-btn').on('click', function() {
    self.viewClientDocuments();
});
        /**
         * Get user edit URL
         */
        getUserEditUrl: function() {
            return '/wp-admin/user-edit.php?user_id=' + this.state.selectedClientId;
        },

        /**
         * Render field groups accordion
         */
        renderFieldGroups: function(fieldGroups, linkHistory, wooStatus) {
            const self = this;
            let html = '';

            // Field groups
            Object.keys(fieldGroups).forEach(function(groupKey, index) {
                const group = fieldGroups[groupKey];
                const isOpen = index === 0 ? ' open' : '';

                html += '<div class="slm-accordion-item' + isOpen + '" data-group="' + groupKey + '">';
                html += '  <button type="button" class="slm-accordion-header">';
                html += '    <h4>' + group.label + '</h4>';
                html += '    <span class="dashicons dashicons-arrow-down"></span>';
                html += '  </button>';
                html += '  <div class="slm-accordion-content">';

                Object.keys(group.fields).forEach(function(fieldKey) {
                    const field = group.fields[fieldKey];
                    const valueClass = field.has_value ? '' : ' empty';
                    
                    html += '<div class="slm-field-row">';
                    html += '  <div class="slm-field-label">' + field.label + '</div>';
                    html += '  <div class="slm-field-value' + valueClass + '">' + self.escapeHtml(field.value) + '</div>';
                    html += '</div>';
                });

                html += '  </div>';
                html += '</div>';
            });

            // Magic Link History
            if (linkHistory && linkHistory.length > 0) {
                html += '<div class="slm-accordion-item" data-group="link-history">';
                html += '  <button type="button" class="slm-accordion-header">';
                html += '    <h4>Magic Link History</h4>';
                html += '    <span class="dashicons dashicons-arrow-down"></span>';
                html += '  </button>';
                html += '  <div class="slm-accordion-content">';
                html += '    <div class="slm-link-history-list">';

                linkHistory.forEach(function(link) {
                    html += '<div class="slm-link-history-item">';
                    html += '  <span class="link-date">' + link.created_at + ' by ' + link.created_by + '</span>';
                    html += '  <span class="link-status ' + link.status + '">' + link.status_label + '</span>';
                    html += '</div>';
                });

                html += '    </div>';
                html += '  </div>';
                html += '</div>';
            }

            // WooCommerce Status
            if (wooStatus && wooStatus.woocommerce_active) {
                html += '<div class="slm-accordion-item" data-group="woocommerce">';
                html += '  <button type="button" class="slm-accordion-header">';
                html += '    <h4>WooCommerce</h4>';
                html += '    <span class="dashicons dashicons-arrow-down"></span>';
                html += '  </button>';
                html += '  <div class="slm-accordion-content">';
                html += '    <div class="slm-woo-stats">';
                html += '      <div class="slm-woo-stat">';
                html += '        <div class="slm-woo-stat-value">' + wooStatus.order_count + '</div>';
                html += '        <div class="slm-woo-stat-label">Orders</div>';
                html += '      </div>';
                html += '      <div class="slm-woo-stat">';
                html += '        <div class="slm-woo-stat-value">' + wooStatus.total_spent + '</div>';
                html += '        <div class="slm-woo-stat-label">Total Spent</div>';
                html += '      </div>';
                html += '      <div class="slm-woo-stat">';
                html += '        <div class="slm-woo-stat-value">' + wooStatus.pending_orders + '</div>';
                html += '        <div class="slm-woo-stat-label">Pending</div>';
                html += '      </div>';
                html += '      <div class="slm-woo-stat">';
                html += '        <div class="slm-woo-stat-value">' + (wooStatus.has_billing_address ? 'Yes' : 'No') + '</div>';
                html += '        <div class="slm-woo-stat-label">Billing Address</div>';
                html += '      </div>';
                html += '    </div>';
                
                if (wooStatus.has_billing_address) {
                    html += '<div class="slm-woo-address" style="margin-top: 16px;">';
                    html += '  <div class="slm-field-row"><div class="slm-field-label">Address</div><div class="slm-field-value">' + self.escapeHtml(wooStatus.billing_address || '—') + '</div></div>';
                    html += '  <div class="slm-field-row"><div class="slm-field-label">City</div><div class="slm-field-value">' + self.escapeHtml(wooStatus.billing_city || '—') + '</div></div>';
                    html += '  <div class="slm-field-row"><div class="slm-field-label">Country</div><div class="slm-field-value">' + self.escapeHtml(wooStatus.billing_country || '—') + '</div></div>';
                    html += '</div>';
                }
                
                html += '  </div>';
                html += '</div>';
            }

            this.elements.detailsAccordion.html(html);

            // Bind accordion events
            this.elements.detailsAccordion.find('.slm-accordion-header').on('click', function() {
                $(this).closest('.slm-accordion-item').toggleClass('open');
            });
        },

        /**
         * Open details panel
         */
        openDetailsPanel: function() {
            this.elements.detailsPanel.show().addClass('active');
        },

        /**
         * Close details panel
         */
        closeDetailsPanel: function() {
            this.elements.detailsPanel.hide().removeClass('active');
            this.elements.searchResults.find('.slm-client-row').removeClass('selected');
            this.state.selectedClientId = null;
            this.state.selectedClientEmail = null;
        },

        /**
         * Open send link modal
         */
        openSendLinkModal: function() {
            this.elements.modalEmail.text(this.state.selectedClientEmail);
            this.elements.modalWarning.hide();
            this.elements.sendLinkModal.show();
        },

        /**
         * Close modal
         */
        closeModal: function() {
            this.elements.sendLinkModal.hide();
        },

        /**
         * Send magic link
         */
        sendMagicLink: function() {
            const self = this;

            if (!this.state.selectedClientId) {
                this.showToast('No client selected', 'error');
                return;
            }

            this.closeModal();
            this.showLoading(slmOnboarding.strings.sendingLink);

            $.ajax({
                url: slmOnboarding.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'slm_send_magic_link',
                    nonce: slmOnboarding.nonce,
                    user_id: this.state.selectedClientId
                },
                success: function(response) {
                    self.hideLoading();
                    if (response.success) {
                        self.showToast(slmOnboarding.strings.linkSent, 'success');
                        // Refresh client details
                        self.selectClient(self.state.selectedClientId, 
                            self.elements.searchResults.find('.slm-client-row[data-user-id="' + self.state.selectedClientId + '"]')
                        );
                    } else {
                        self.showToast(response.data.message || slmOnboarding.strings.error, 'error');
                    }
                },
                error: function() {
                    self.hideLoading();
                    self.showToast(slmOnboarding.strings.error, 'error');
                }
            });
        },

        /**
         * Show loading overlay
         */
        showLoading: function(text) {
            this.elements.loadingText.text(text || 'Loading...');
            this.elements.loadingOverlay.show();
            this.state.isLoading = true;
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            this.elements.loadingOverlay.hide();
            this.state.isLoading = false;
        },

        /**
         * Show toast notification
         */
        showToast: function(message, type) {
            type = type || 'info';
            
            const icon = type === 'success' ? 'yes' : (type === 'error' ? 'no' : 'info');
            
            const $toast = $('<div class="slm-toast ' + type + '">' +
                '<span class="dashicons dashicons-' + icon + '"></span>' +
                '<span>' + this.escapeHtml(message) + '</span>' +
                '</div>');
            
            $('body').append($toast);
            
            setTimeout(function() {
                $toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        SLMClientOnboarding.init();
    });

})(jQuery);
