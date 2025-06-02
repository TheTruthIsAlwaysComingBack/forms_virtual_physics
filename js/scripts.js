// VirtualPhysics Login Scripts

document.addEventListener('DOMContentLoaded', function() {
    // Initialize login form
    initializeLoginForm();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize form validation
    initializeFormValidation();
});

/**
 * Initialize login form functionality
 */
function initializeLoginForm() {
    const loginForm = document.querySelector('.login-form');
    const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
    const loginBtn = document.querySelector('.login-btn');
    
    // Add input event listeners for better UX
    inputs.forEach(input => {
        input.addEventListener('focus', handleInputFocus);
        input.addEventListener('blur', handleInputBlur);
        input.addEventListener('input', handleInputChange);
    });
    
    // Add form submit handler
    if (loginForm) {
        loginForm.addEventListener('submit', handleFormSubmit);
    }
    
    // Add login button click animation
    if (loginBtn) {
        loginBtn.addEventListener('click', handleLoginButtonClick);
    }
}

/**
 * Handle input focus events
 */
function handleInputFocus(event) {
    const input = event.target;
    const formGroup = input.closest('.form-group');
    
    if (formGroup) {
        formGroup.classList.add('focused');
    }
    
    // Add subtle animation
    input.style.transform = 'translateY(-2px)';
}

/**
 * Handle input blur events
 */
function handleInputBlur(event) {
    const input = event.target;
    const formGroup = input.closest('.form-group');
    
    if (formGroup) {
        formGroup.classList.remove('focused');
    }
    
    // Reset animation
    input.style.transform = 'translateY(0)';
}

/**
 * Handle input change events
 */
function handleInputChange(event) {
    const input = event.target;
    
    // Remove error state when user starts typing
    if (input.classList.contains('error')) {
        input.classList.remove('error');
    }
    
    // Validate input in real-time
    validateInput(input);
}

/**
 * Handle form submission
 */
function handleFormSubmit(event) {
    const form = event.target;
    const submitBtn = form.querySelector('.login-btn');
    
    // Add loading state
    if (submitBtn) {
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Change button text temporarily
        const btnText = submitBtn.querySelector('span');
        const originalText = btnText.textContent;
        btnText.textContent = 'INGRESANDO...';
        
        // Reset after a short delay if form doesn't actually submit
        setTimeout(() => {
            if (submitBtn.classList.contains('loading')) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                btnText.textContent = originalText;
            }
        }, 3000);
    }
}

/**
 * Handle login button click animation
 */
function handleLoginButtonClick(event) {
    const button = event.currentTarget;
    
    // Create ripple effect
    const ripple = document.createElement('span');
    ripple.classList.add('ripple');
    
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    
    button.appendChild(ripple);
    
    // Remove ripple after animation
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

/**
 * Initialize animations
 */
function initializeAnimations() {
    // Stagger animation for form elements
    const formElements = document.querySelectorAll('.form-group, .login-btn, .divider, .google-btn, .footer-links');
    
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add hover effects to interactive elements
    addHoverEffects();
}

/**
 * Add hover effects to interactive elements
 */
function addHoverEffects() {
    const googleBtn = document.querySelector('.google-btn');
    const links = document.querySelectorAll('.link, .link-primary');
    
    // Google button hover effect
    if (googleBtn) {
        googleBtn.addEventListener('mouseenter', () => {
            googleBtn.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        googleBtn.addEventListener('mouseleave', () => {
            googleBtn.style.transform = 'translateY(0) scale(1)';
        });
    }
    
    // Link hover effects
    links.forEach(link => {
        link.addEventListener('mouseenter', () => {
            link.style.transform = 'translateY(-1px)';
        });
        
        link.addEventListener('mouseleave', () => {
            link.style.transform = 'translateY(0)';
        });
    });
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const emailInput = document.querySelector('input[type="email"]');
    const passwordInput = document.querySelector('input[type="password"]');
    
    if (emailInput) {
        emailInput.addEventListener('blur', () => validateEmail(emailInput));
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('blur', () => validatePassword(passwordInput));
    }
}

/**
 * Validate individual input
 */
function validateInput(input) {
    if (input.type === 'email') {
        return validateEmail(input);
    } else if (input.type === 'password') {
        return validatePassword(input);
    }
    return true;
}

/**
 * Validate email input
 */
function validateEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email === '') {
        setInputError(input, 'El correo electrónico es requerido');
        return false;
    } else if (!emailRegex.test(email)) {
        setInputError(input, 'Por favor ingresa un correo electrónico válido');
        return false;
    } else {
        clearInputError(input);
        return true;
    }
}

/**
 * Validate password input
 */
function validatePassword(input) {
    const password = input.value;
    
    if (password === '') {
        setInputError(input, 'La contraseña es requerida');
        return false;
    } else if (password.length < 6) {
        setInputError(input, 'La contraseña debe tener al menos 6 caracteres');
        return false;
    } else {
        clearInputError(input);
        return true;
    }
}

/**
 * Set input error state
 */
function setInputError(input, message) {
    input.classList.add('error');
    
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.error-text');
    if (existingError) {
        existingError.remove();
    }
    
    // Add new error message
    const errorElement = document.createElement('span');
    errorElement.className = 'error-text';
    errorElement.textContent = message;
    errorElement.style.color = '#dc2626';
    errorElement.style.fontSize = '12px';
    errorElement.style.marginTop = '4px';
    errorElement.style.display = 'block';
    
    input.parentNode.appendChild(errorElement);
}

/**
 * Clear input error state
 */
function clearInputError(input) {
    input.classList.remove('error');
    
    const errorElement = input.parentNode.querySelector('.error-text');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Add ripple effect CSS if not already present
 */
function addRippleEffect() {
    if (!document.querySelector('#ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .login-btn {
                position: relative;
                overflow: hidden;
            }
            
            .form-group input.error {
                border-color: #dc2626;
                background-color: #fef2f2;
            }
            
            .login-btn.loading {
                opacity: 0.8;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(style);
    }
}

// Initialize ripple effect styles
addRippleEffect();

/**
 * Utility function to show notifications
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        ${type === 'error' ? 'background: #dc2626;' : 
          type === 'success' ? 'background: #059669;' : 
          'background: #3b82f6;'}
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Hide notification after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}