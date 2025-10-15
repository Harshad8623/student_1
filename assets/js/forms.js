// Forms Management and Validation for Student Management System

class SMSForms {
    constructor() {
        this.init();
    }

    init() {
        this.initFormValidations();
        this.initAutoSave();
        this.initFileUploads();
        this.initDynamicForms();
        this.initSearchFilters();
    }

    // Initialize form validations
    initFormValidations() {
        // Password strength indicator
        this.initPasswordStrength();
        
        // Email validation
        this.initEmailValidation();
        
        // Phone number validation
        this.initPhoneValidation();
        
        // File validation
        this.initFileValidation();
        
        // Required field validation
        this.initRequiredFields();
    }

    // Password strength indicator
    initPasswordStrength() {
        const passwordInputs = document.querySelectorAll('input[type="password"]');
        
        passwordInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.checkPasswordStrength(e.target);
            });
        });
    }

    checkPasswordStrength(input) {
        const password = input.value;
        const strengthIndicator = input.parentNode.querySelector('.password-strength');
        
        if (!strengthIndicator) return;

        let strength = 0;
        let feedback = '';

        // Length check
        if (password.length >= 8) strength++;
        else feedback = 'Password should be at least 8 characters. ';

        // Lowercase check
        if (/[a-z]/.test(password)) strength++;
        else feedback += 'Include lowercase letters. ';

        // Uppercase check
        if (/[A-Z]/.test(password)) strength++;
        else feedback += 'Include uppercase letters. ';

        // Number check
        if (/[0-9]/.test(password)) strength++;
        else feedback += 'Include numbers. ';

        // Special character check
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else feedback += 'Include special characters. ';

        // Update UI
        const strengthClasses = ['weak', 'fair', 'good', 'strong', 'very-strong'];
        const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Very Strong'];
        const strengthColors = ['#dc3545', '#ffc107', '#17a2b8', '#28a745', '#20c997'];

        strengthIndicator.className = `password-strength strength-${strengthClasses[strength]}`;
        strengthIndicator.innerHTML = `
            <span style="color: ${strengthColors[strength]}">${strengthText[strength]}</span>
            ${feedback ? `<small>${feedback}</small>` : ''}
        `;
    }

    // Email validation
    initEmailValidation() {
        const emailInputs = document.querySelectorAll('input[type="email"]');
        
        emailInputs.forEach(input => {
            input.addEventListener('blur', (e) => {
                this.validateEmail(e.target);
            });
        });
    }

    validateEmail(input) {
        const email = input.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            this.showError(input, 'Please enter a valid email address');
            return false;
        } else {
            this.clearError(input);
            return true;
        }
    }

    // Phone number validation
    initPhoneValidation() {
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        
        phoneInputs.forEach(input => {
            input.addEventListener('blur', (e) => {
                this.validatePhone(e.target);
            });
            
            // Format phone number as user types
            input.addEventListener('input', (e) => {
                this.formatPhoneNumber(e.target);
            });
        });
    }

    validatePhone(input) {
        const phone = input.value.replace(/\D/g, '');
        
        if (phone && phone.length < 10) {
            this.showError(input, 'Please enter a valid phone number');
            return false;
        } else {
            this.clearError(input);
            return true;
        }
    }

    formatPhoneNumber(input) {
        let numbers = input.value.replace(/\D/g, '');
        let formatted = '';
        
        if (numbers.length > 0) {
            formatted = '(' + numbers.substring(0, 3);
        }
        if (numbers.length > 3) {
            formatted += ') ' + numbers.substring(3, 6);
        }
        if (numbers.length > 6) {
            formatted += '-' + numbers.substring(6, 10);
        }
        
        input.value = formatted;
    }

    // File validation
    initFileValidation() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.validateFile(e.target);
            });
        });
    }

    validateFile(input) {
        const file = input.files[0];
        if (!file) return true;

        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        
        if (file.size > maxSize) {
            this.showError(input, 'File size must be less than 5MB');
            return false;
        }
        
        if (!allowedTypes.includes(file.type)) {
            this.showError(input, 'Please select a valid file type (JPEG, PNG, GIF, PDF)');
            return false;
        }
        
        this.clearError(input);
        return true;
    }

    // Required field validation
    initRequiredFields() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    this.showFormErrors(form);
                }
            });
        });
    }

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showError(field, 'This field is required');
                isValid = false;
            } else {
                this.clearError(field);
            }
        });
        
        return isValid;
    }

    // Error handling utilities
    showError(input, message) {
        this.clearError(input);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.8rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        
        input.parentNode.appendChild(errorDiv);
        input.style.borderColor = '#dc3545';
    }

    clearError(input) {
        const errorDiv = input.parentNode.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
        input.style.borderColor = '';
    }

    showFormErrors(form) {
        const firstError = form.querySelector('.error-message');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Auto-save functionality
    initAutoSave() {
        const autoSaveForms = document.querySelectorAll('.auto-save');
        
        autoSaveForms.forEach(form => {
            let saveTimeout;
            
            form.addEventListener('input', (e) => {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    this.autoSaveForm(form);
                }, 1000);
            });
        });
    }

    autoSaveForm(form) {
        const formData = new FormData(form);
        console.log('Auto-saving form data:', Object.fromEntries(formData));
        
        // Show saving indicator
        this.showSaveIndicator(form, 'Saving...');
        
        // Simulate API call
        setTimeout(() => {
            this.showSaveIndicator(form, 'Saved successfully!');
        }, 500);
    }

    showSaveIndicator(form, message) {
        let indicator = form.querySelector('.save-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'save-indicator';
            indicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 10px 15px;
                border-radius: 5px;
                z-index: 1000;
            `;
            document.body.appendChild(indicator);
        }
        
        indicator.textContent = message;
        indicator.style.display = 'block';
        
        setTimeout(() => {
            indicator.style.display = 'none';
        }, 2000);
    }

    // File upload enhancements
    initFileUploads() {
        const fileUploads = document.querySelectorAll('.file-upload');
        
        fileUploads.forEach(upload => {
            const input = upload.querySelector('input[type="file"]');
            const label = upload.querySelector('.file-upload-label');
            const preview = upload.querySelector('.file-preview');
            
            if (input && label) {
                input.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        label.textContent = file.name;
                        this.showFilePreview(file, preview);
                    }
                });
            }
        });
    }

    showFilePreview(file, previewContainer) {
        if (!previewContainer) return;
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewContainer.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px;">`;
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.innerHTML = `
                <div class="file-info">
                    <i class="fas fa-file" style="font-size: 3rem; color: #666;"></i>
                    <div>${file.name}</div>
                    <small>${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                </div>
            `;
        }
    }

    // Dynamic form fields
    initDynamicForms() {
        // Add/remove form fields dynamically
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-field')) {
                this.addFormField(e.target);
            }
            if (e.target.classList.contains('remove-field')) {
                this.removeFormField(e.target);
            }
        });
    }

    addFormField(button) {
        const container = button.closest('.dynamic-fields');
        const template = container.querySelector('.field-template');
        const newField = template.cloneNode(true);
        
        newField.classList.remove('field-template');
        newField.style.display = 'block';
        
        // Clear values
        newField.querySelectorAll('input, select, textarea').forEach(field => {
            field.value = '';
        });
        
        container.insertBefore(newField, button);
    }

    removeFormField(button) {
        const field = button.closest('.dynamic-field');
        if (field) {
            field.remove();
        }
    }

    // Search and filter functionality
    initSearchFilters() {
        const searchInputs = document.querySelectorAll('.search-input');
        
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.filterTable(e.target);
            });
        });
    }

    filterTable(searchInput) {
        const filter = searchInput.value.toLowerCase();
        const table = searchInput.closest('.table-container').querySelector('table');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }

    // Form submission with loading state
    submitForm(form, callback) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        // Simulate form submission
        setTimeout(() => {
            if (callback) callback();
            
            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }, 1000);
    }
}

// Initialize forms when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const smsForms = new SMSForms();
    window.smsForms = smsForms;
});

// Utility functions
const FormUtils = {
    // Serialize form data to object
    serializeForm(form) {
        const formData = new FormData(form);
        const data = {};
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        return data;
    },

    // Reset form to initial state
    resetForm(form) {
        form.reset();
        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.style.borderColor = '';
        });
    },

    // Populate form with data
    populateForm(form, data) {
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = data[key];
            }
        });
    }
};