/**
 * DFCMS Modern UI Framework
 * Modular JavaScript for professional SaaS interactions
 * Version: 2.0
 */

(function(global) {
    'use strict';

    /**
     * DFCMS UI Namespace
     */
    const DFCMS = {
        version: '2.0',
        initialized: false
    };

    /**
     * Utility Functions
     */
    const Utils = {
        /**
         * Debounce function execution
         */
        debounce(func, wait, immediate) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    timeout = null;
                    if (!immediate) func(...args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func(...args);
            };
        },

        /**
         * Throttle function execution
         */
        throttle(func, limit) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        /**
         * Generate unique ID
         */
        generateId(prefix = 'dfcms') {
            return `${prefix}-${Math.random().toString(36).substr(2, 9)}`;
        },

        /**
         * Format date relative to now
         */
        timeAgo(date) {
            const seconds = Math.floor((new Date() - new Date(date)) / 1000);
            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60,
                second: 1
            };

            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit);
                if (interval >= 1) {
                    return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`;
                }
            }
            return 'Just now';
        },

        /**
         * Check if element is in viewport
         */
        isInViewport(element, threshold = 0) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= -threshold &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) + threshold &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Deep merge objects
         */
        deepMerge(target, source) {
            const output = Object.assign({}, target);
            if (isObject(target) && isObject(source)) {
                Object.keys(source).forEach(key => {
                    if (isObject(source[key])) {
                        if (!(key in target)) Object.assign(output, { [key]: source[key] });
                        else output[key] = this.deepMerge(target[key], source[key]);
                    } else {
                        Object.assign(output, { [key]: source[key] });
                    }
                });
            }
            return output;
        }
    };

    function isObject(item) {
        return (item && typeof item === 'object' && !Array.isArray(item));
    }

    /**
     * Toast Notification System
     */
    class ToastManager {
        constructor() {
            this.container = null;
            this.toasts = [];
            this.defaultOptions = {
                duration: 5000,
                showClose: true,
                position: 'top-right'
            };
            this.init();
        }

        init() {
            // Create container if not exists
            if (!document.querySelector('.toast-container')) {
                this.container = document.createElement('div');
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            } else {
                this.container = document.querySelector('.toast-container');
            }
        }

        /**
         * Show toast notification
         */
        show(options) {
            const config = { ...this.defaultOptions, ...options };
            const id = Utils.generateId('toast');
            
            const toast = document.createElement('div');
            toast.className = `toast toast-${config.type || 'info'}`;
            toast.id = id;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'polite');

            const iconMap = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };

            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fas fa-${iconMap[config.type] || iconMap.info}"></i>
                </div>
                <div class="toast-content">
                    ${config.title ? `<div class="toast-title">${config.title}</div>` : ''}
                    <div class="toast-message">${config.message}</div>
                </div>
                ${config.showClose ? `
                    <button class="toast-close" aria-label="Close notification">
                        <i class="fas fa-times"></i>
                    </button>
                ` : ''}
            `;

            // Add close handler
            if (config.showClose) {
                toast.querySelector('.toast-close').addEventListener('click', () => {
                    this.dismiss(id);
                });
            }

            // Auto dismiss
            let dismissTimeout;
            if (config.duration > 0) {
                dismissTimeout = setTimeout(() => {
                    this.dismiss(id);
                }, config.duration);
            }

            // Pause on hover
            toast.addEventListener('mouseenter', () => {
                clearTimeout(dismissTimeout);
            });

            toast.addEventListener('mouseleave', () => {
                if (config.duration > 0) {
                    dismissTimeout = setTimeout(() => {
                        this.dismiss(id);
                    }, config.duration);
                }
            });

            this.container.appendChild(toast);
            this.toasts.push({ id, element: toast, timeout: dismissTimeout });

            // Accessibility: Announce to screen readers
            this.announce(config.message);

            return id;
        }

        /**
         * Dismiss specific toast
         */
        dismiss(id) {
            const toastIndex = this.toasts.findIndex(t => t.id === id);
            if (toastIndex === -1) return;

            const toast = this.toasts[toastIndex];
            clearTimeout(toast.timeout);
            
            toast.element.classList.add('removing');
            toast.element.addEventListener('animationend', () => {
                toast.element.remove();
                this.toasts.splice(toastIndex, 1);
            });
        }

        /**
         * Dismiss all toasts
         */
        dismissAll() {
            [...this.toasts].forEach(toast => this.dismiss(toast.id));
        }

        /**
         * Shorthand methods
         */
        success(message, title, options = {}) {
            return this.show({ type: 'success', message, title, ...options });
        }

        error(message, title, options = {}) {
            return this.show({ type: 'danger', message, title, ...options });
        }

        warning(message, title, options = {}) {
            return this.show({ type: 'warning', message, title, ...options });
        }

        info(message, title, options = {}) {
            return this.show({ type: 'info', message, title, ...options });
        }

        /**
         * Announce message to screen readers
         */
        announce(message) {
            const announcer = document.createElement('div');
            announcer.setAttribute('role', 'status');
            announcer.setAttribute('aria-live', 'polite');
            announcer.className = 'sr-only';
            announcer.textContent = message;
            document.body.appendChild(announcer);
            setTimeout(() => announcer.remove(), 1000);
        }
    }

    /**
     * Form Validation System
     */
    class FormValidator {
        constructor(formElement, options = {}) {
            this.form = formElement;
            this.options = {
                validateOnBlur: true,
                validateOnInput: false,
                showInlineErrors: true,
                scrollToError: true,
                ...options
            };
            this.errors = {};
            this.fields = {};
            this.init();
        }

        init() {
            if (!this.form) return;

            // Collect all form fields with validation
            const inputs = this.form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                const fieldName = input.name || input.id;
                if (fieldName) {
                    this.fields[fieldName] = input;
                    this.attachListeners(input);
                }
            });

            // Form submit handler
            this.form.addEventListener('submit', (e) => {
                if (!this.validate()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }

        attachListeners(input) {
            if (this.options.validateOnBlur) {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            }

            if (this.options.validateOnInput) {
                input.addEventListener('input', Utils.debounce(() => {
                    this.validateField(input);
                }, 300));
            }

            // Clear error on focus
            input.addEventListener('focus', () => {
                this.clearFieldError(input);
            });
        }

        /**
         * Define validation rules
         */
        rules(validationRules) {
            this.validationRules = validationRules;
            return this;
        }

        /**
         * Validate entire form
         */
        validate() {
            this.errors = {};
            let isValid = true;

            Object.keys(this.fields).forEach(fieldName => {
                const input = this.fields[fieldName];
                if (!this.validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid && this.options.scrollToError) {
                this.scrollToFirstError();
            }

            return isValid;
        }

        /**
         * Validate single field
         */
        validateField(input) {
            const fieldName = input.name || input.id;
            const rules = this.validationRules?.[fieldName];
            
            if (!rules) return true;

            const value = input.value.trim();
            let error = null;

            // Required check
            if (rules.required && !value) {
                error = rules.requiredMessage || 'This field is required';
            }

            // Email validation
            if (!error && value && rules.email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    error = rules.emailMessage || 'Please enter a valid email address';
                }
            }

            // Min length
            if (!error && value && rules.minLength && value.length < rules.minLength) {
                error = rules.minLengthMessage || `Must be at least ${rules.minLength} characters`;
            }

            // Max length
            if (!error && value && rules.maxLength && value.length > rules.maxLength) {
                error = rules.maxLengthMessage || `Must be no more than ${rules.maxLength} characters`;
            }

            // Pattern match
            if (!error && value && rules.pattern) {
                const regex = new RegExp(rules.pattern);
                if (!regex.test(value)) {
                    error = rules.patternMessage || 'Invalid format';
                }
            }

            // Custom validator
            if (!error && value && rules.custom) {
                const customError = rules.custom(value, input);
                if (customError) {
                    error = customError;
                }
            }

            // Match another field
            if (!error && value && rules.match) {
                const matchField = this.form.querySelector(`[name="${rules.match}"]`);
                if (matchField && value !== matchField.value) {
                    error = rules.matchMessage || 'Fields do not match';
                }
            }

            if (error) {
                this.errors[fieldName] = error;
                this.showFieldError(input, error);
                return false;
            } else {
                this.showFieldSuccess(input);
                return true;
            }
        }

        /**
         * Show error on field
         */
        showFieldError(input, message) {
            input.classList.add('error');
            input.classList.remove('success');
            input.setAttribute('aria-invalid', 'true');

            if (this.options.showInlineErrors) {
                this.removeFeedback(input);
                
                const feedback = document.createElement('div');
                feedback.className = 'form-feedback error show';
                feedback.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                feedback.id = `error-${input.name || input.id}`;
                input.setAttribute('aria-describedby', feedback.id);

                const formGroup = input.closest('.form-group') || input.parentElement;
                formGroup.appendChild(feedback);
            }
        }

        /**
         * Show success on field
         */
        showFieldSuccess(input) {
            input.classList.add('success');
            input.classList.remove('error');
            input.setAttribute('aria-invalid', 'false');
            this.removeFeedback(input);
        }

        /**
         * Clear error from field
         */
        clearFieldError(input) {
            input.classList.remove('error');
            input.removeAttribute('aria-invalid');
            this.removeFeedback(input);
            delete this.errors[input.name || input.id];
        }

        /**
         * Remove feedback element
         */
        removeFeedback(input) {
            const formGroup = input.closest('.form-group') || input.parentElement;
            const existingFeedback = formGroup.querySelector('.form-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }
        }

        /**
         * Scroll to first error
         */
        scrollToFirstError() {
            const firstError = this.form.querySelector('.error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        }

        /**
         * Get all errors
         */
        getErrors() {
            return this.errors;
        }

        /**
         * Reset form validation
         */
        reset() {
            this.errors = {};
            Object.values(this.fields).forEach(input => {
                input.classList.remove('error', 'success');
                input.removeAttribute('aria-invalid');
                this.removeFeedback(input);
            });
        }
    }

    /**
     * Loading State Manager
     */
    class LoadingManager {
        /**
         * Show button loading state
         */
        static button(button, text = 'Loading...') {
            const originalText = button.innerHTML;
            button.dataset.originalText = originalText;
            button.disabled = true;
            button.classList.add('btn-loading');
            
            if (text) {
                const textSpan = document.createElement('span');
                textSpan.textContent = text;
                button.innerHTML = '';
                button.appendChild(textSpan);
            }

            return () => {
                button.disabled = false;
                button.classList.remove('btn-loading');
                button.innerHTML = button.dataset.originalText;
            };
        }

        /**
         * Show skeleton loading for container
         */
        static skeleton(container, template = 'card') {
            const templates = {
                card: `
                    <div class="skeleton skeleton-card"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text" style="width: 75%"></div>
                `,
                text: `
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text" style="width: 60%"></div>
                `,
                avatar: `
                    <div class="skeleton skeleton-circle" style="width: 48px; height: 48px;"></div>
                    <div style="flex: 1;">
                        <div class="skeleton skeleton-text" style="width: 120px;"></div>
                        <div class="skeleton skeleton-text" style="width: 80px;"></div>
                    </div>
                `,
                table: `
                    <tr>
                        <td><div class="skeleton skeleton-text"></div></td>
                        <td><div class="skeleton skeleton-text"></div></td>
                        <td><div class="skeleton skeleton-text" style="width: 60%"></div></td>
                    </tr>
                `.repeat(5)
            };

            container.dataset.originalContent = container.innerHTML;
            container.innerHTML = templates[template] || template;
            container.classList.add('skeleton-active');

            return () => {
                container.innerHTML = container.dataset.originalContent;
                container.classList.remove('skeleton-active');
            };
        }

        /**
         * Show full page loader
         */
        static page(show = true) {
            let loader = document.querySelector('.page-loader');
            
            if (!loader) {
                loader = document.createElement('div');
                loader.className = 'page-loader';
                loader.innerHTML = `
                    <div class="page-loader-content">
                        <div class="page-loader-spinner"></div>
                        <div class="page-loader-text">Loading...</div>
                    </div>
                `;
                loader.style.cssText = `
                    position: fixed;
                    inset: 0;
                    background: rgba(12, 13, 14, 0.9);
                    backdrop-filter: blur(8px);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity 0.3s, visibility 0.3s;
                `;
                document.body.appendChild(loader);
            }

            if (show) {
                loader.style.opacity = '1';
                loader.style.visibility = 'visible';
            } else {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
            }
        }
    }

    /**
     * Sidebar Manager
     */
    class SidebarManager {
        constructor() {
            this.sidebar = document.querySelector('.sidebar-modern');
            this.toggle = document.querySelector('.sidebar-toggle');
            this.overlay = document.querySelector('.sidebar-overlay');
            this.isOpen = false;
            
            if (this.sidebar && this.toggle) {
                this.init();
            }
        }

        init() {
            // Toggle button click
            this.toggle.addEventListener('click', () => {
                this.toggleSidebar();
            });

            // Overlay click
            if (this.overlay) {
                this.overlay.addEventListener('click', () => {
                    this.closeSidebar();
                });
            }

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeSidebar();
                }
            });

            // Handle window resize
            window.addEventListener('resize', Utils.debounce(() => {
                if (window.innerWidth > 991 && this.isOpen) {
                    this.closeSidebar();
                }
            }, 150));
        }

        toggleSidebar() {
            if (this.isOpen) {
                this.closeSidebar();
            } else {
                this.openSidebar();
            }
        }

        openSidebar() {
            this.sidebar.classList.add('open');
            if (this.overlay) this.overlay.classList.add('show');
            this.toggle.setAttribute('aria-expanded', 'true');
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        }

        closeSidebar() {
            this.sidebar.classList.remove('open');
            if (this.overlay) this.overlay.classList.remove('show');
            this.toggle.setAttribute('aria-expanded', 'false');
            this.isOpen = false;
            document.body.style.overflow = '';
        }
    }

    /**
     * Smooth Scroll & Anchor Links
     */
    class SmoothScroll {
        static init() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const target = document.querySelector(targetId);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }
    }

    /**
     * Intersection Observer for Animations
     */
    class ScrollAnimations {
        constructor() {
            this.observer = null;
            this.init();
        }

        init() {
            const animatedElements = document.querySelectorAll('[data-animate]');
            if (animatedElements.length === 0) return;

            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const animation = entry.target.dataset.animate;
                        entry.target.classList.add(`animate-${animation}`);
                        entry.target.style.opacity = '1';
                        this.observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            animatedElements.forEach(el => {
                el.style.opacity = '0';
                this.observer.observe(el);
            });
        }
    }

    /**
     * Tooltip Manager
     */
    class TooltipManager {
        static init() {
            // Using CSS-based tooltips with data-tooltip attribute
            // Additional JS functionality can be added here if needed
        }
    }

    /**
     * Main Initialization
     */
    function init() {
        if (DFCMS.initialized) return;

        // Initialize components
        DFCMS.toast = new ToastManager();
        DFCMS.sidebar = new SidebarManager();
        DFCMS.scrollAnimations = new ScrollAnimations();
        
        // Initialize features
        SmoothScroll.init();
        TooltipManager.init();

        // Expose utilities
        DFCMS.utils = Utils;
        DFCMS.FormValidator = FormValidator;
        DFCMS.LoadingManager = LoadingManager;

        DFCMS.initialized = true;
        console.log('DFCMS UI v2.0 initialized');
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose to global scope
    global.DFCMS = DFCMS;

})(window);

/**
 * Add CSS for page loader
 */
(function() {
    const style = document.createElement('style');
    style.textContent = `
        .page-loader-spinner {
            width: 48px;
            height: 48px;
            border: 3px solid rgba(16, 185, 129, 0.2);
            border-top-color: #10b981;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .page-loader-text {
            margin-top: 1rem;
            color: #94a3b8;
            font-size: 0.875rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
})();
