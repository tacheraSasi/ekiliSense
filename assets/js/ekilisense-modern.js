/**
 * ekiliSense Modern JavaScript Utilities
 * Provides toast notifications, form handling, and UI enhancements
 */

// Toast Notification System
const EkiliToast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'success', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icon = this.getIcon(type);
        
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, duration);
        
        return toast;
    },
    
    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    },
    
    success(message, duration) {
        return this.show(message, 'success', duration);
    },
    
    error(message, duration) {
        return this.show(message, 'error', duration);
    },
    
    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },
    
    info(message, duration) {
        return this.show(message, 'info', duration);
    }
};

// Form Handler with AJAX and validation
class FormHandler {
    constructor(formSelector, options = {}) {
        this.form = document.querySelector(formSelector);
        this.options = {
            onSuccess: options.onSuccess || null,
            onError: options.onError || null,
            beforeSubmit: options.beforeSubmit || null,
            validateOnBlur: options.validateOnBlur !== false,
            showToasts: options.showToasts !== false,
            resetOnSuccess: options.resetOnSuccess !== false,
            ...options
        };
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        
        if (this.options.validateOnBlur) {
            this.form.querySelectorAll('input, textarea, select').forEach(field => {
                field.addEventListener('blur', () => this.validateField(field));
            });
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        // Clear previous errors
        this.clearErrors();
        
        // Validate all fields
        if (!this.validateForm()) {
            return;
        }
        
        // Call beforeSubmit hook
        if (this.options.beforeSubmit) {
            const shouldContinue = await this.options.beforeSubmit(this.form);
            if (shouldContinue === false) {
                return;
            }
        }
        
        // Show loading state
        this.setLoading(true);
        
        try {
            const formData = new FormData(this.form);
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.handleSuccess(data);
            } else {
                this.handleError(data);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.handleError({ message: 'An unexpected error occurred' });
        } finally {
            this.setLoading(false);
        }
    }
    
    validateForm() {
        let isValid = true;
        const fields = this.form.querySelectorAll('input[required], textarea[required], select[required]');
        
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let error = null;
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            error = `${this.getFieldLabel(field)} is required`;
        }
        
        // Email validation
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            error = 'Please enter a valid email address';
        }
        
        // Min length validation
        if (field.hasAttribute('minlength') && value.length < field.minLength) {
            error = `${this.getFieldLabel(field)} must be at least ${field.minLength} characters`;
        }
        
        // Custom pattern validation
        if (field.hasAttribute('pattern') && value && !new RegExp(field.pattern).test(value)) {
            error = field.getAttribute('data-pattern-error') || 'Invalid format';
        }
        
        if (error) {
            this.showFieldError(field, error);
            return false;
        } else {
            this.clearFieldError(field);
            return true;
        }
    }
    
    showFieldError(field, message) {
        field.classList.add('error');
        
        let errorElement = field.parentElement.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            field.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    }
    
    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentElement.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
    
    clearErrors() {
        this.form.querySelectorAll('.form-error').forEach(el => el.remove());
        this.form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }
    
    getFieldLabel(field) {
        const label = this.form.querySelector(`label[for="${field.id}"]`);
        return label ? label.textContent.replace('*', '').trim() : field.name;
    }
    
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    setLoading(loading) {
        const submitBtn = this.form.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = loading;
            
            if (loading) {
                submitBtn.dataset.originalText = submitBtn.textContent;
                submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
            } else if (submitBtn.dataset.originalText) {
                submitBtn.textContent = submitBtn.dataset.originalText;
            }
        }
    }
    
    handleSuccess(data) {
        if (this.options.showToasts) {
            EkiliToast.success(data.message || 'Success!');
        }
        
        if (this.options.resetOnSuccess) {
            this.form.reset();
        }
        
        if (this.options.onSuccess) {
            this.options.onSuccess(data);
        }
        
        if (data.redirect) {
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
        }
    }
    
    handleError(data) {
        if (this.options.showToasts) {
            EkiliToast.error(data.message || 'An error occurred');
        }
        
        if (data.errors) {
            Object.keys(data.errors).forEach(fieldName => {
                const field = this.form.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    this.showFieldError(field, data.errors[fieldName]);
                }
            });
        }
        
        if (this.options.onError) {
            this.options.onError(data);
        }
    }
}

// Loading Overlay
const LoadingOverlay = {
    show(message = 'Loading...') {
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-content">
                    <div class="spinner" style="width: 40px; height: 40px; border-width: 4px;"></div>
                    <p class="loading-message">${message}</p>
                </div>
            `;
            document.body.appendChild(overlay);
        } else {
            overlay.querySelector('.loading-message').textContent = message;
        }
        overlay.style.display = 'flex';
    },
    
    hide() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
};

// Confirmation Dialog
function confirmDialog(message, onConfirm, onCancel) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    overlay.innerHTML = `
        <div class="confirm-dialog card-modern">
            <h3 class="card-title">Confirm Action</h3>
            <p>${message}</p>
            <div class="confirm-actions" style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button class="btn-modern btn-secondary" onclick="this.closest('.confirm-overlay').remove();">Cancel</button>
                <button class="btn-modern btn-primary confirm-yes">Confirm</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    overlay.querySelector('.confirm-yes').onclick = () => {
        if (onConfirm) onConfirm();
        overlay.remove();
    };
    
    overlay.onclick = (e) => {
        if (e.target === overlay) {
            if (onCancel) onCancel();
            overlay.remove();
        }
    };
}

// Data Table Enhancement
class DataTable {
    constructor(tableSelector, options = {}) {
        this.table = document.querySelector(tableSelector);
        this.options = {
            searchable: options.searchable !== false,
            sortable: options.sortable !== false,
            pagination: options.pagination || 10,
            ...options
        };
        
        if (this.table) {
            this.init();
        }
    }
    
    init() {
        if (this.options.searchable) {
            this.addSearch();
        }
        
        if (this.options.sortable) {
            this.addSorting();
        }
        
        if (this.options.pagination) {
            this.addPagination();
        }
    }
    
    addSearch() {
        const searchBox = document.createElement('input');
        searchBox.type = 'text';
        searchBox.className = 'form-input-modern';
        searchBox.placeholder = 'Search...';
        searchBox.style.marginBottom = '1rem';
        
        this.table.parentElement.insertBefore(searchBox, this.table);
        
        searchBox.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const rows = this.table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
    
    addSorting() {
        const headers = this.table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => this.sortTable(index));
        });
    }
    
    sortTable(columnIndex) {
        const tbody = this.table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aValue = a.cells[columnIndex].textContent;
            const bValue = b.cells[columnIndex].textContent;
            return aValue.localeCompare(bValue);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    }
    
    addPagination() {
        // Pagination implementation
        // TODO: Add pagination controls
    }
}

// Utility Functions
const Utils = {
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            EkiliToast.success('Copied to clipboard!');
        }).catch(() => {
            EkiliToast.error('Failed to copy');
        });
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        EkiliToast,
        FormHandler,
        LoadingOverlay,
        confirmDialog,
        DataTable,
        Utils
    };
}
