/**
 * SLM Client Onboarding - Frontend JavaScript
 * 
 * Handles signature capture, form validation, and step progression.
 * 
 * @package Studio_Legale_Metta
 * @since 1.0.0
 */

(function() {
    'use strict';

    /**
     * Main Onboarding Application
     */
    const SLMOnboarding = {
        
        /**
         * State
         */
        state: {
            signaturePad: null,
            signatureType: 'draw', // 'draw' or 'type'
            hasScrolledTerms: false,
            isSubmitting: false
        },

        /**
         * Elements
         */
        el: {},

        /**
         * Initialize
         */
        init: function() {
            this.cacheElements();
            this.initSignaturePad();
            this.bindEvents();
            this.checkTermsScroll();
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.el = {
                // Hidden data
                token: document.getElementById('slm-token'),
                userId: document.getElementById('slm-user-id'),
                userName: document.getElementById('slm-user-name'),
                userEmail: document.getElementById('slm-user-email'),
                
                // Steps
                stepTerms: document.getElementById('step-terms'),
                stepPassword: document.getElementById('step-password'),
                stepComplete: document.getElementById('step-complete'),
                
                // Terms
                termsScroll: document.getElementById('terms-scroll'),
                scrollIndicator: document.getElementById('scroll-indicator'),
                
                // Signature
                signatureCanvas: document.getElementById('signature-pad'),
                clearSignature: document.getElementById('clear-signature'),
                drawSignatureArea: document.getElementById('draw-signature-area'),
                typeSignatureArea: document.getElementById('type-signature-area'),
                typedSignature: document.getElementById('typed-signature'),
                typedPreview: document.getElementById('typed-preview'),
                sigTypeBtns: document.querySelectorAll('.sig-type-btn'),
                
                // Form fields
                fullName: document.getElementById('full-name'),
                agreeTerms: document.getElementById('agree-terms'),
                submitSignature: document.getElementById('submit-signature'),
                
                // Password
                newPassword: document.getElementById('new-password'),
                confirmPassword: document.getElementById('confirm-password'),
                submitPassword: document.getElementById('submit-password'),
                passwordStrength: document.getElementById('password-strength'),
                passwordMatch: document.getElementById('password-match'),
                togglePasswordBtns: document.querySelectorAll('.toggle-password'),
                
                // Requirements
                reqLength: document.getElementById('req-length'),
                reqUpper: document.getElementById('req-upper'),
                reqLower: document.getElementById('req-lower'),
                reqNumber: document.getElementById('req-number'),
                
                // Progress
                progressSteps: document.querySelectorAll('.slm-progress-steps .step'),
                progressLines: document.querySelectorAll('.slm-progress-steps .step-line')
            };
        },

        /**
         * Initialize Signature Pad
         */
        initSignaturePad: function() {
            if (!this.el.signatureCanvas) return;
            
            this.state.signaturePad = new SignaturePad(this.el.signatureCanvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(30, 58, 95)', // Navy color
                minWidth: 1,
                maxWidth: 3
            });
            
            // Resize canvas to fit container
            this.resizeCanvas();
            window.addEventListener('resize', this.resizeCanvas.bind(this));
        },

        /**
         * Resize canvas to fit container
         */
        resizeCanvas: function() {
            if (!this.el.signatureCanvas || !this.state.signaturePad) return;
            
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const canvas = this.el.signatureCanvas;
            const container = canvas.parentElement;
            
            canvas.width = container.offsetWidth * ratio;
            canvas.height = 200 * ratio;
            canvas.style.width = container.offsetWidth + 'px';
            canvas.style.height = '200px';
            
            canvas.getContext('2d').scale(ratio, ratio);
            this.state.signaturePad.clear();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;
            
            // Terms scroll
            if (this.el.termsScroll) {
                this.el.termsScroll.addEventListener('scroll', function() {
                    self.handleTermsScroll();
                });
            }
            
            // Signature type toggle
            this.el.sigTypeBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    self.switchSignatureType(this.dataset.type);
                });
            });
            
            // Clear signature
            if (this.el.clearSignature) {
                this.el.clearSignature.addEventListener('click', function() {
                    if (self.state.signaturePad) {
                        self.state.signaturePad.clear();
                        self.validateSignatureForm();
                    }
                });
            }
            
            // Typed signature
            if (this.el.typedSignature) {
                this.el.typedSignature.addEventListener('input', function() {
                    self.updateTypedPreview();
                    self.validateSignatureForm();
                });
            }
            
            // Form fields
            if (this.el.fullName) {
                this.el.fullName.addEventListener('input', function() {
                    self.validateSignatureForm();
                });
            }
            
            if (this.el.agreeTerms) {
                this.el.agreeTerms.addEventListener('change', function() {
                    self.validateSignatureForm();
                });
            }
            
            // Signature pad change
            if (this.state.signaturePad) {
                this.state.signaturePad.addEventListener('endStroke', function() {
                    self.validateSignatureForm();
                });
            }
            
            // Submit signature
            if (this.el.submitSignature) {
                this.el.submitSignature.addEventListener('click', function() {
                    self.submitSignature();
                });
            }
            
            // Password fields
            if (this.el.newPassword) {
                this.el.newPassword.addEventListener('input', function() {
                    self.checkPasswordStrength();
                    self.checkPasswordMatch();
                    self.validatePasswordForm();
                });
            }
            
            if (this.el.confirmPassword) {
                this.el.confirmPassword.addEventListener('input', function() {
                    self.checkPasswordMatch();
                    self.validatePasswordForm();
                });
            }
            
            // Toggle password visibility
            this.el.togglePasswordBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    self.togglePasswordVisibility(this.dataset.target);
                });
            });
            
            // Submit password
            if (this.el.submitPassword) {
                this.el.submitPassword.addEventListener('click', function() {
                    self.submitPassword();
                });
            }
        },

        /**
         * Check if terms are scrolled
         */
        checkTermsScroll: function() {
            if (!this.el.termsScroll) return;
            
            const scrollHeight = this.el.termsScroll.scrollHeight;
            const clientHeight = this.el.termsScroll.clientHeight;
            
            // If content doesn't need scrolling, hide indicator
            if (scrollHeight <= clientHeight + 10) {
                this.state.hasScrolledTerms = true;
                if (this.el.scrollIndicator) {
                    this.el.scrollIndicator.classList.add('hidden');
                }
            }
        },

        /**
         * Handle terms scroll
         */
        handleTermsScroll: function() {
            if (!this.el.termsScroll || this.state.hasScrolledTerms) return;
            
            const scrollTop = this.el.termsScroll.scrollTop;
            const scrollHeight = this.el.termsScroll.scrollHeight;
            const clientHeight = this.el.termsScroll.clientHeight;
            
            // Check if scrolled to bottom (with 50px threshold)
            if (scrollTop + clientHeight >= scrollHeight - 50) {
                this.state.hasScrolledTerms = true;
                if (this.el.scrollIndicator) {
                    this.el.scrollIndicator.classList.add('hidden');
                }
            }
        },

        /**
         * Switch signature type
         */
        switchSignatureType: function(type) {
            this.state.signatureType = type;
            
            // Update buttons
            this.el.sigTypeBtns.forEach(function(btn) {
                btn.classList.toggle('active', btn.dataset.type === type);
            });
            
            // Show/hide areas
            if (this.el.drawSignatureArea) {
                this.el.drawSignatureArea.style.display = type === 'draw' ? 'block' : 'none';
            }
            if (this.el.typeSignatureArea) {
                this.el.typeSignatureArea.style.display = type === 'type' ? 'block' : 'none';
            }
            
            this.validateSignatureForm();
        },

        /**
         * Update typed signature preview
         */
        updateTypedPreview: function() {
            if (!this.el.typedPreview || !this.el.typedSignature) return;
            this.el.typedPreview.textContent = this.el.typedSignature.value;
        },

        /**
         * Validate signature form
         */
        validateSignatureForm: function() {
            if (!this.el.submitSignature) return;
            
            let isValid = true;
            
            // Check signature
            if (this.state.signatureType === 'draw') {
                if (!this.state.signaturePad || this.state.signaturePad.isEmpty()) {
                    isValid = false;
                }
            } else {
                if (!this.el.typedSignature || !this.el.typedSignature.value.trim()) {
                    isValid = false;
                }
            }
            
            // Check full name
            if (!this.el.fullName || !this.el.fullName.value.trim()) {
                isValid = false;
            }
            
            // Check agreement
            if (!this.el.agreeTerms || !this.el.agreeTerms.checked) {
                isValid = false;
            }
            
            this.el.submitSignature.disabled = !isValid;
        },

        /**
         * Submit signature
         */
        submitSignature: function() {
            if (this.state.isSubmitting) return;
            
            const self = this;
            
            // Get signature data
            let signatureData;
            if (this.state.signatureType === 'draw') {
                if (this.state.signaturePad.isEmpty()) {
                    this.showToast(slmOnboardingFront.strings.signatureRequired, 'error');
                    return;
                }
                signatureData = this.state.signaturePad.toDataURL('image/png');
            } else {
                if (!this.el.typedSignature.value.trim()) {
                    this.showToast(slmOnboardingFront.strings.signatureRequired, 'error');
                    return;
                }
                signatureData = this.el.typedSignature.value.trim();
            }
            
            // Get full name
            const fullName = this.el.fullName.value.trim();
            if (!fullName) {
                this.showToast(slmOnboardingFront.strings.nameRequired, 'error');
                return;
            }
            
            // Show loading
            this.setButtonLoading(this.el.submitSignature, true);
            this.state.isSubmitting = true;
            
            // Collect client info
            const clientInfo = {
                ip: '', // Will be captured server-side
                userAgent: navigator.userAgent,
                screenResolution: window.screen.width + 'x' + window.screen.height,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                language: navigator.language,
                timestamp: new Date().toISOString()
            };
            
            // Submit
            this.ajax('slm_submit_terms_signature', {
                token: this.el.token.value,
                signature_type: this.state.signatureType,
                signature_data: signatureData,
                full_name: fullName,
                client_info: JSON.stringify(clientInfo)
            }, function(response) {
                self.setButtonLoading(self.el.submitSignature, false);
                self.state.isSubmitting = false;
                
                if (response.success) {
                    self.showToast(slmOnboardingFront.strings.signed, 'success');
                    self.goToStep('password');
                } else {
                    self.showToast(response.data.message || slmOnboardingFront.strings.error, 'error');
                }
            }, function() {
                self.setButtonLoading(self.el.submitSignature, false);
                self.state.isSubmitting = false;
                self.showToast(slmOnboardingFront.strings.error, 'error');
            });
        },

        /**
         * Check password strength
         */
        checkPasswordStrength: function() {
            if (!this.el.newPassword || !this.el.passwordStrength) return;
            
            const password = this.el.newPassword.value;
            const fill = this.el.passwordStrength.querySelector('.strength-fill');
            const text = this.el.passwordStrength.querySelector('.strength-text');
            
            // Check requirements
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            // Update requirement indicators
            this.updateRequirement(this.el.reqLength, hasLength);
            this.updateRequirement(this.el.reqUpper, hasUpper);
            this.updateRequirement(this.el.reqLower, hasLower);
            this.updateRequirement(this.el.reqNumber, hasNumber);
            
            // Calculate strength
            let strength = 0;
            if (hasLength) strength++;
            if (hasUpper) strength++;
            if (hasLower) strength++;
            if (hasNumber) strength++;
            if (password.length >= 12) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update UI
            fill.className = 'strength-fill';
            text.className = 'strength-text';
            
            if (password.length === 0) {
                text.textContent = '';
            } else if (strength <= 2) {
                fill.classList.add('weak');
                text.classList.add('weak');
                text.textContent = 'Weak';
            } else if (strength <= 3) {
                fill.classList.add('fair');
                text.classList.add('fair');
                text.textContent = 'Fair';
            } else if (strength <= 4) {
                fill.classList.add('good');
                text.classList.add('good');
                text.textContent = 'Good';
            } else {
                fill.classList.add('strong');
                text.classList.add('strong');
                text.textContent = 'Strong';
            }
        },

        /**
         * Update requirement indicator
         */
        updateRequirement: function(element, met) {
            if (!element) return;
            element.classList.toggle('met', met);
        },

        /**
         * Check password match
         */
        checkPasswordMatch: function() {
            if (!this.el.newPassword || !this.el.confirmPassword || !this.el.passwordMatch) return;
            
            const password = this.el.newPassword.value;
            const confirm = this.el.confirmPassword.value;
            
            if (confirm.length === 0) {
                this.el.passwordMatch.style.display = 'none';
                return;
            }
            
            this.el.passwordMatch.style.display = 'block';
            
            if (password === confirm) {
                this.el.passwordMatch.textContent = '✓ Passwords match';
                this.el.passwordMatch.className = 'match-indicator match';
            } else {
                this.el.passwordMatch.textContent = '✗ Passwords do not match';
                this.el.passwordMatch.className = 'match-indicator no-match';
            }
        },

        /**
         * Validate password form
         */
        validatePasswordForm: function() {
            if (!this.el.submitPassword) return;
            
            const password = this.el.newPassword ? this.el.newPassword.value : '';
            const confirm = this.el.confirmPassword ? this.el.confirmPassword.value : '';
            
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasLower = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const passwordsMatch = password === confirm && confirm.length > 0;
            
            const isValid = hasLength && hasUpper && hasLower && hasNumber && passwordsMatch;
            
            this.el.submitPassword.disabled = !isValid;
        },

        /**
         * Toggle password visibility
         */
        togglePasswordVisibility: function(targetId) {
            const input = document.getElementById(targetId);
            if (!input) return;
            
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            
            // Update icon
            const btn = document.querySelector('[data-target="' + targetId + '"]');
            if (btn) {
                const icon = btn.querySelector('.dashicons');
                if (icon) {
                    icon.classList.toggle('dashicons-visibility', !isPassword);
                    icon.classList.toggle('dashicons-hidden', isPassword);
                }
            }
        },

        /**
         * Submit password
         */
        submitPassword: function() {
            if (this.state.isSubmitting) return;
            
            const self = this;
            const password = this.el.newPassword.value;
            const confirm = this.el.confirmPassword.value;
            
            // Validate
            if (password !== confirm) {
                this.showToast(slmOnboardingFront.strings.passwordMismatch, 'error');
                return;
            }
            
            if (password.length < 8) {
                this.showToast(slmOnboardingFront.strings.passwordWeak, 'error');
                return;
            }
            
            // Show loading
            this.setButtonLoading(this.el.submitPassword, true);
            this.state.isSubmitting = true;
            
            // Submit
            this.ajax('slm_set_password', {
                token: this.el.token.value,
                password: password
            }, function(response) {
                self.setButtonLoading(self.el.submitPassword, false);
                self.state.isSubmitting = false;
                
                if (response.success) {
                    self.showToast(slmOnboardingFront.strings.complete, 'success');
                    self.goToStep('complete');
                    
                    // Redirect after delay
                    if (response.data.redirect) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 2000);
                    }
                } else {
                    self.showToast(response.data.message || slmOnboardingFront.strings.error, 'error');
                }
            }, function() {
                self.setButtonLoading(self.el.submitPassword, false);
                self.state.isSubmitting = false;
                self.showToast(slmOnboardingFront.strings.error, 'error');
            });
        },

        /**
         * Go to step
         */
        goToStep: function(step) {
            // Hide all steps
            if (this.el.stepTerms) this.el.stepTerms.style.display = 'none';
            if (this.el.stepPassword) this.el.stepPassword.style.display = 'none';
            if (this.el.stepComplete) this.el.stepComplete.style.display = 'none';
            
            // Show target step
            switch (step) {
                case 'terms':
                    if (this.el.stepTerms) this.el.stepTerms.style.display = 'block';
                    this.updateProgress(0);
                    break;
                case 'password':
                    if (this.el.stepPassword) this.el.stepPassword.style.display = 'block';
                    this.updateProgress(1);
                    break;
                case 'complete':
                    if (this.el.stepComplete) this.el.stepComplete.style.display = 'block';
                    this.updateProgress(2);
                    break;
            }
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        /**
         * Update progress indicator
         */
        updateProgress: function(activeIndex) {
            this.el.progressSteps.forEach(function(step, index) {
                step.classList.remove('active', 'complete');
                if (index < activeIndex) {
                    step.classList.add('complete');
                } else if (index === activeIndex) {
                    step.classList.add('active');
                }
            });
            
            this.el.progressLines.forEach(function(line, index) {
                line.classList.toggle('complete', index < activeIndex);
            });
        },

        /**
         * Set button loading state
         */
        setButtonLoading: function(button, loading) {
            if (!button) return;
            
            const btnText = button.querySelector('.btn-text');
            const btnLoading = button.querySelector('.btn-loading');
            
            if (loading) {
                button.disabled = true;
                if (btnText) btnText.style.display = 'none';
                if (btnLoading) btnLoading.style.display = 'flex';
            } else {
                button.disabled = false;
                if (btnText) btnText.style.display = 'inline';
                if (btnLoading) btnLoading.style.display = 'none';
            }
        },

        /**
         * AJAX helper
         */
        ajax: function(action, data, success, error) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', slmOnboardingFront.nonce);
            
            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    formData.append(key, data[key]);
                }
            }
            
            fetch(slmOnboardingFront.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (typeof success === 'function') {
                    success(data);
                }
            })
            .catch(function(err) {
                console.error('AJAX Error:', err);
                if (typeof error === 'function') {
                    error(err);
                }
            });
        },

        /**
         * Show toast notification
         */
        showToast: function(message, type) {
            type = type || 'info';
            
            // Remove existing toasts
            const existing = document.querySelectorAll('.slm-toast');
            existing.forEach(function(toast) {
                toast.remove();
            });
            
            // Create toast
            const toast = document.createElement('div');
            toast.className = 'slm-toast ' + type;
            
            const icon = type === 'success' ? 'yes' : (type === 'error' ? 'no' : 'info');
            toast.innerHTML = '<span class="dashicons dashicons-' + icon + '"></span><span>' + this.escapeHtml(message) + '</span>';
            
            document.body.appendChild(toast);
            
            // Auto remove
            setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(function() {
                    toast.remove();
                }, 300);
            }, 4000);
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            SLMOnboarding.init();
        });
    } else {
        SLMOnboarding.init();
    }

})();
