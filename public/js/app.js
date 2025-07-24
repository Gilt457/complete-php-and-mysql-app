/**
 * Advanced JavaScript functionality for the PHP MySQL Application
 * Handles dynamic interactions, form validation, AJAX requests, and UI enhancements
 * 
 * @author Professional PHP Team
 * @version 1.0.0
 */

// Global application configuration
const AppConfig = {
    apiUrl: '/api/',
    ajaxTimeout: 30000,
    debounceDelay: 300,
    animationSpeed: 300,
    maxFileSize: 10 * 1024 * 1024, // 10MB
    allowedImageTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

// Utility functions
const Utils = {
    // Debounce function for search and other frequent operations
    debounce: function(func, wait, immediate) {
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

    // Format currency
    formatCurrency: function(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    // Format date
    formatDate: function(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return new Intl.DateTimeFormat('en-US', {...defaultOptions, ...options}).format(new Date(date));
    },

    // Show toast notification
    showToast: function(message, type = 'info', duration = 5000) {
        const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0 show`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            toast.remove();
        }, duration);
    },

    // Create toast container if it doesn't exist
    createToastContainer: function() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
        return container;
    },

    // Loading spinner
    showLoading: function(element) {
        if (element) {
            element.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';
            element.disabled = true;
        }
    },

    hideLoading: function(element, originalText) {
        if (element) {
            element.innerHTML = originalText;
            element.disabled = false;
        }
    },

    // Local storage helpers
    setStorage: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) {
            console.warn('Could not save to localStorage:', e);
        }
    },

    getStorage: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.warn('Could not retrieve from localStorage:', e);
            return null;
        }
    }
};

// AJAX wrapper class
class AjaxManager {
    static async request(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': AppConfig.csrf_token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            timeout: AppConfig.ajaxTimeout
        };

        const config = { ...defaultOptions, ...options };

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), config.timeout);

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return await response.text();
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                throw new Error('Request timeout');
            }
            throw error;
        }
    }

    static async get(url, params = {}) {
        const urlParams = new URLSearchParams(params);
        const fullUrl = urlParams.toString() ? `${url}?${urlParams}` : url;
        return this.request(fullUrl);
    }

    static async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async postForm(url, formData) {
        return this.request(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': AppConfig.csrf_token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    }
}

// Form validation class
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
        this.rules = {};
    }

    addRule(field, rule, message) {
        if (!this.rules[field]) {
            this.rules[field] = [];
        }
        this.rules[field].push({ rule, message });
        return this;
    }

    validate() {
        this.errors = {};
        const formData = new FormData(this.form);

        for (const [field, rules] of Object.entries(this.rules)) {
            const value = formData.get(field) || '';
            
            for (const { rule, message } of rules) {
                if (typeof rule === 'function') {
                    if (!rule(value, formData)) {
                        this.addError(field, message);
                        break;
                    }
                } else if (typeof rule === 'object' && rule.pattern) {
                    if (!rule.pattern.test(value)) {
                        this.addError(field, message);
                        break;
                    }
                }
            }
        }

        this.displayErrors();
        return Object.keys(this.errors).length === 0;
    }

    addError(field, message) {
        if (!this.errors[field]) {
            this.errors[field] = [];
        }
        this.errors[field].push(message);
    }

    displayErrors() {
        // Clear previous errors
        this.form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        this.form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Display new errors
        for (const [field, messages] of Object.entries(this.errors)) {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = messages[0]; // Show first error
                
                input.parentNode.appendChild(errorDiv);
            }
        }
    }

    // Common validation rules
    static rules = {
        required: (value) => value.trim() !== '',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        minLength: (min) => (value) => value.length >= min,
        maxLength: (max) => (value) => value.length <= max,
        numeric: (value) => /^\d+$/.test(value),
        alphanumeric: (value) => /^[a-zA-Z0-9]+$/.test(value),
        phone: (value) => /^[\+]?[1-9][\d]{0,15}$/.test(value.replace(/\s/g, '')),
        url: (value) => {
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        },
        password: (value) => {
            // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/.test(value);
        },
        confirmPassword: (password) => (value) => value === password
    };
}

// Shopping cart management
class ShoppingCart {
    constructor() {
        this.items = Utils.getStorage('cart') || [];
        this.updateCartDisplay();
    }

    addItem(productId, quantity = 1, price = 0, name = '', image = '') {
        const existingItem = this.items.find(item => item.productId === productId);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                productId,
                quantity,
                price,
                name,
                image,
                addedAt: new Date().toISOString()
            });
        }
        
        this.saveCart();
        this.updateCartDisplay();
        Utils.showToast(`${name} added to cart!`, 'success');
    }

    removeItem(productId) {
        this.items = this.items.filter(item => item.productId !== productId);
        this.saveCart();
        this.updateCartDisplay();
        Utils.showToast('Item removed from cart', 'info');
    }

    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.productId === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
                this.updateCartDisplay();
            }
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    getItemCount() {
        return this.items.reduce((count, item) => count + item.quantity, 0);
    }

    clear() {
        this.items = [];
        this.saveCart();
        this.updateCartDisplay();
    }

    saveCart() {
        Utils.setStorage('cart', this.items);
    }

    updateCartDisplay() {
        const cartCount = document.querySelector('.cart-count');
        const cartTotal = document.querySelector('.cart-total');
        
        if (cartCount) {
            cartCount.textContent = this.getItemCount();
        }
        
        if (cartTotal) {
            cartTotal.textContent = Utils.formatCurrency(this.getTotal());
        }

        // Update cart icon badge
        const cartBadge = document.querySelector('.cart-badge');
        if (cartBadge) {
            const count = this.getItemCount();
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline' : 'none';
        }
    }
}

// Legacy jQuery support for existing functionality
(function($) {
    'use strict';

    // Application object for backward compatibility
    const App = {
        init: function() {
            this.bindEvents();
            this.initComponents();
            this.setupAjax();
        },

        bindEvents: function() {
            // Form submissions
            $(document).on('submit', '.ajax-form', this.handleAjaxForm);
            
            // Dynamic content loading
            $(document).on('click', '.load-more', this.loadMoreContent);
            
            // Product quantity controls
            $(document).on('click', '.quantity-btn', this.handleQuantityChange);
            
            // Add to cart functionality
            $(document).on('click', '.add-to-cart', this.addToCart);
            
            // Remove from cart
            $(document).on('click', '.remove-from-cart', this.removeFromCart);
            
            // Product image gallery
            $(document).on('click', '.product-thumbnail', this.changeProductImage);
            
            // Search functionality
            $(document).on('input', '#search-input', this.debounce(this.performSearch, 300));
            
            // Filter functionality
            $(document).on('change', '.filter-checkbox, .filter-select', this.applyFilters);
            
            // Wishlist functionality
            $(document).on('click', '.wishlist-btn', this.toggleWishlist);
            
            // Rating system
            $(document).on('click', '.rating-star', this.handleRating);
            
            // Modal events
            $(document).on('show.bs.modal', '.modal', this.onModalShow);
            
            // Tooltip and popover initialization
            this.initTooltips();
            this.initPopovers();
        },

        initComponents: function() {
            // Initialize carousels
            $('.carousel').carousel();
            
            // Initialize tabs
            $('.nav-tabs a').on('click', function(e) {
                e.preventDefault();
                $(this).tab('show');
            });
            
            // Initialize dropdowns
            $('.dropdown-toggle').dropdown();
            
            // Auto-hide alerts
            setTimeout(function() {
                $('.alert.auto-hide').alert('close');
            }, 5000);
            
            // Initialize price range slider
            this.initPriceRange();
            
            // Initialize image lazy loading
            this.initLazyLoading();
            
            // Initialize scroll animations
            this.initScrollAnimations();
        },

        setupAjax: function() {
            // CSRF token for all AJAX requests
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                        const csrfToken = $('meta[name="csrf-token"]').attr('content');
                        if (csrfToken) {
                            xhr.setRequestHeader("X-CSRFToken", csrfToken);
                        }
                    }
                }
            });
            
            // Global AJAX error handler
            $(document).ajaxError(function(event, xhr, settings, thrownError) {
                if (xhr.status === 401) {
                    App.showNotification('Session expired. Please login again.', 'error');
                    window.location.href = '/login';
                } else if (xhr.status === 403) {
                    App.showNotification('Access denied.', 'error');
                } else if (xhr.status >= 500) {
                    App.showNotification('Server error. Please try again later.', 'error');
                }
            });
        },

        // Form handling
        handleAjaxForm: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            // Show loading state
            $submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
            
            // Clear previous errors
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();
            
            $.ajax({
                url: $form.attr('action') || window.location.href,
                method: $form.attr('method') || 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        App.showNotification(response.message, 'success');
                        
                        // Handle redirects
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            // Reset form if no redirect
                            $form[0].reset();
                        }
                    } else {
                        App.showNotification(response.message, 'error');
                        
                        // Show field errors
                        if (response.errors) {
                            App.showFieldErrors($form, response.errors);
                        }
                    }
                },
                error: function(xhr) {
                    let message = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    App.showNotification(message, 'error');
                },
                complete: function() {
                    // Reset button
                    $submitBtn.text(originalText).prop('disabled', false);
                }
            });
        },

        // Shopping cart functionality
        addToCart: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const quantity = $btn.closest('.product-card').find('.quantity-input').val() || 1;
            const originalText = $btn.html();
            
            $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            
            $.ajax({
                url: '/ajax/add-to-cart.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        App.showNotification('Product added to cart!', 'success');
                        App.updateCartCount(response.cart_count);
                        $btn.html('<i class="fas fa-check"></i> Added');
                        
                        setTimeout(function() {
                            $btn.html(originalText).prop('disabled', false);
                        }, 2000);
                    } else {
                        App.showNotification(response.message, 'error');
                        $btn.html(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    App.showNotification('Failed to add product to cart.', 'error');
                    $btn.html(originalText).prop('disabled', false);
                }
            });
        },

        removeFromCart: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const cartItemId = $btn.data('cart-item-id');
            
            if (!confirm('Remove this item from cart?')) {
                return;
            }
            
            $.ajax({
                url: '/ajax/remove-from-cart.php',
                method: 'POST',
                data: { cart_item_id: cartItemId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $btn.closest('.cart-item').fadeOut(300, function() {
                            $(this).remove();
                        });
                        App.updateCartCount(response.cart_count);
                        App.updateCartTotal(response.cart_total);
                        App.showNotification('Item removed from cart.', 'success');
                    } else {
                        App.showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    App.showNotification('Failed to remove item from cart.', 'error');
                }
            });
        },

    };

    // Initialize when document is ready
    $(document).ready(function() {
        App.init();
    });

})(jQuery);

// Modern JavaScript functionality
// Search functionality
class SearchManager {
    constructor() {
        this.searchInput = document.querySelector('#search-input');
        this.searchResults = document.querySelector('#search-results');
        this.searchForm = document.querySelector('#search-form');
        
        if (this.searchInput) {
            this.init();
        }
    }

    init() {
        // Debounced search as user types
        this.searchInput.addEventListener('input', Utils.debounce((e) => {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                this.performSearch(query);
            } else {
                this.hideResults();
            }
        }, AppConfig.debounceDelay));

        // Handle form submission
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const query = this.searchInput.value.trim();
                if (query) {
                    window.location.href = `/products?search=${encodeURIComponent(query)}`;
                }
            });
        }

        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-container')) {
                this.hideResults();
            }
        });
    }

    async performSearch(query) {
        try {
            this.showLoading();
            const results = await AjaxManager.get('/api/search.php', { q: query, limit: 10 });
            this.displayResults(results);
        } catch (error) {
            console.error('Search error:', error);
            this.hideResults();
        }
    }

    displayResults(results) {
        if (!this.searchResults) return;

        if (results.length === 0) {
            this.searchResults.innerHTML = '<div class="search-no-results">No results found</div>';
        } else {
            this.searchResults.innerHTML = results.map(item => `
                <div class="search-result-item">
                    <img src="${item.image || '/public/images/placeholder.jpg'}" alt="${item.name}" class="search-result-image">
                    <div class="search-result-content">
                        <h6 class="search-result-title">${item.name}</h6>
                        <p class="search-result-description">${item.description}</p>
                        <span class="search-result-price">${Utils.formatCurrency(item.price)}</span>
                    </div>
                </div>
            `).join('');
        }

        this.searchResults.style.display = 'block';
    }

    showLoading() {
        if (this.searchResults) {
            this.searchResults.innerHTML = '<div class="search-loading">Searching...</div>';
            this.searchResults.style.display = 'block';
        }
    }

    hideResults() {
        if (this.searchResults) {
            this.searchResults.style.display = 'none';
        }
    }
}

// Product gallery and image management
class ProductGallery {
    constructor(container) {
        this.container = container;
        this.currentImage = 0;
        this.images = [];
        this.init();
    }

    init() {
        const thumbnails = this.container.querySelectorAll('.product-thumbnail');
        const mainImage = this.container.querySelector('.product-main-image');

        if (thumbnails.length > 0 && mainImage) {
            this.images = Array.from(thumbnails).map(thumb => ({
                src: thumb.dataset.fullSize || thumb.src,
                alt: thumb.alt
            }));

            thumbnails.forEach((thumb, index) => {
                thumb.addEventListener('click', () => {
                    this.showImage(index);
                    this.setActiveThumbnail(index);
                });
            });

            // Keyboard navigation
            this.container.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    this.previousImage();
                } else if (e.key === 'ArrowRight') {
                    this.nextImage();
                }
            });

            // Add zoom functionality
            mainImage.addEventListener('click', () => {
                this.openLightbox();
            });
        }
    }

    showImage(index) {
        if (index >= 0 && index < this.images.length) {
            const mainImage = this.container.querySelector('.product-main-image');
            mainImage.src = this.images[index].src;
            mainImage.alt = this.images[index].alt;
            this.currentImage = index;
        }
    }

    setActiveThumbnail(index) {
        const thumbnails = this.container.querySelectorAll('.product-thumbnail');
        thumbnails.forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
        });
    }

    nextImage() {
        const nextIndex = (this.currentImage + 1) % this.images.length;
        this.showImage(nextIndex);
        this.setActiveThumbnail(nextIndex);
    }

    previousImage() {
        const prevIndex = (this.currentImage - 1 + this.images.length) % this.images.length;
        this.showImage(prevIndex);
        this.setActiveThumbnail(prevIndex);
    }

    openLightbox() {
        // Create lightbox modal
        const lightbox = document.createElement('div');
        lightbox.className = 'product-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <button class="lightbox-close">&times;</button>
                <button class="lightbox-prev">&#8249;</button>
                <img src="${this.images[this.currentImage].src}" alt="${this.images[this.currentImage].alt}" class="lightbox-image">
                <button class="lightbox-next">&#8250;</button>
            </div>
        `;

        document.body.appendChild(lightbox);

        // Event listeners
        lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
            document.body.removeChild(lightbox);
        });

        lightbox.querySelector('.lightbox-prev').addEventListener('click', () => {
            this.previousImage();
            lightbox.querySelector('.lightbox-image').src = this.images[this.currentImage].src;
        });

        lightbox.querySelector('.lightbox-next').addEventListener('click', () => {
            this.nextImage();
            lightbox.querySelector('.lightbox-image').src = this.images[this.currentImage].src;
        });

        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                document.body.removeChild(lightbox);
            }
        });
    }
}

// Theme management
class ThemeManager {
    constructor() {
        this.currentTheme = Utils.getStorage('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        
        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                this.toggleTheme();
            });
        }
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.currentTheme);
        Utils.setStorage('theme', this.currentTheme);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        const themeIcon = document.querySelector('.theme-toggle i');
        if (themeIcon) {
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }
    }
}

// Initialize modern components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing modern components...');

    // Initialize global components
    window.cart = new ShoppingCart();
    window.searchManager = new SearchManager();
    window.themeManager = new ThemeManager();

    // Initialize product galleries
    document.querySelectorAll('.product-gallery').forEach(gallery => {
        new ProductGallery(gallery);
    });

    // Initialize form validators
    document.querySelectorAll('form[data-validate]').forEach(form => {
        const validator = new FormValidator(form);
        
        // Add common validation rules based on input types
        form.querySelectorAll('input[required]').forEach(input => {
            validator.addRule(input.name, FormValidator.rules.required, 'This field is required');
        });

        form.querySelectorAll('input[type="email"]').forEach(input => {
            validator.addRule(input.name, FormValidator.rules.email, 'Please enter a valid email address');
        });

        form.querySelectorAll('input[data-min-length]').forEach(input => {
            const minLength = parseInt(input.dataset.minLength);
            validator.addRule(input.name, FormValidator.rules.minLength(minLength), `Minimum ${minLength} characters required`);
        });

        // Validate on submit
        form.addEventListener('submit', (e) => {
            if (!validator.validate()) {
                e.preventDefault();
            }
        });
    });

    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const productId = button.dataset.productId;
            const price = parseFloat(button.dataset.price);
            const name = button.dataset.name;
            const image = button.dataset.image;
            const quantity = parseInt(button.closest('.product-item')?.querySelector('.quantity-input')?.value || 1);

            if (productId && price && name) {
                window.cart.addItem(productId, quantity, price, name, image);
            }
        });
    });

    // Quantity controls
    document.querySelectorAll('.quantity-controls').forEach(controls => {
        const input = controls.querySelector('.quantity-input');
        const minusBtn = controls.querySelector('.quantity-minus');
        const plusBtn = controls.querySelector('.quantity-plus');

        if (minusBtn && input) {
            minusBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }

        if (plusBtn && input) {
            plusBtn.addEventListener('click', () => {
                const currentValue = parseInt(input.value) || 1;
                const maxValue = parseInt(input.max) || 999;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Back to top button
    const backToTop = document.querySelector('.back-to-top');
    if (backToTop) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        backToTop.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    console.log('Modern components initialized successfully!');
});

// Export utilities for use in other scripts
window.AppUtils = Utils;
window.AjaxManager = AjaxManager;
window.FormValidator = FormValidator;
        handleQuantityChange: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $input = $btn.siblings('.quantity-input');
            const currentVal = parseInt($input.val()) || 1;
            const isIncrease = $btn.hasClass('quantity-increase');
            const min = parseInt($input.attr('min')) || 1;
            const max = parseInt($input.attr('max')) || 999;
            
            let newVal = isIncrease ? currentVal + 1 : currentVal - 1;
            newVal = Math.max(min, Math.min(max, newVal));
            
            $input.val(newVal).trigger('change');
        },

        // Product search
        performSearch: function(e) {
            const query = $(this).val().trim();
            
            if (query.length < 3) {
                $('#search-results').hide();
                return;
            }
            
            $.ajax({
                url: '/ajax/search.php',
                method: 'GET',
                data: { q: query },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        App.displaySearchResults(response.products);
                    }
                },
                error: function() {
                    $('#search-results').hide();
                }
            });
        },

        displaySearchResults: function(products) {
            const $results = $('#search-results');
            
            if (products.length === 0) {
                $results.html('<div class="p-3 text-muted">No products found</div>').show();
                return;
            }
            
            let html = '<div class="list-group">';
            products.forEach(function(product) {
                html += `
                    <a href="/product/${product.slug}" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <img src="${product.image || '/images/placeholder.jpg'}" alt="${product.name}" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1">${product.name}</h6>
                                <small class="text-success">$${product.price}</small>
                            </div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            
            $results.html(html).show();
        },

        // Filter functionality
        applyFilters: function() {
            const filters = {};
            
            // Collect all filter values
            $('.filter-checkbox:checked').each(function() {
                const name = $(this).attr('name');
                if (!filters[name]) filters[name] = [];
                filters[name].push($(this).val());
            });
            
            $('.filter-select').each(function() {
                const value = $(this).val();
                if (value) {
                    filters[$(this).attr('name')] = value;
                }
            });
            
            // Apply filters to URL
            const params = new URLSearchParams(window.location.search);
            Object.keys(filters).forEach(key => {
                if (Array.isArray(filters[key])) {
                    params.set(key, filters[key].join(','));
                } else {
                    params.set(key, filters[key]);
                }
            });
            
            window.location.search = params.toString();
        },

        // Wishlist functionality
        toggleWishlist: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const productId = $btn.data('product-id');
            const isInWishlist = $btn.hasClass('in-wishlist');
            
            $.ajax({
                url: '/ajax/toggle-wishlist.php',
                method: 'POST',
                data: { product_id: productId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.in_wishlist) {
                            $btn.addClass('in-wishlist').html('<i class="fas fa-heart"></i>');
                            App.showNotification('Added to wishlist!', 'success');
                        } else {
                            $btn.removeClass('in-wishlist').html('<i class="far fa-heart"></i>');
                            App.showNotification('Removed from wishlist!', 'info');
                        }
                    }
                },
                error: function() {
                    App.showNotification('Please login to use wishlist.', 'warning');
                }
            });
        },

        // Rating system
        handleRating: function(e) {
            e.preventDefault();
            
            const $star = $(this);
            const rating = $star.data('rating');
            const productId = $star.closest('.rating-container').data('product-id');
            
            // Update visual rating
            $star.parent().find('.rating-star').each(function(index) {
                $(this).removeClass('fas far').addClass(index < rating ? 'fas' : 'far');
            });
            
            // Submit rating
            $.ajax({
                url: '/ajax/rate-product.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    rating: rating
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        App.showNotification('Thank you for your rating!', 'success');
                    } else {
                        App.showNotification(response.message, 'error');
                    }
                }
            });
        },

        // Product image gallery
        changeProductImage: function(e) {
            e.preventDefault();
            
            const $thumb = $(this);
            const newSrc = $thumb.data('image');
            
            // Update main image
            $('.product-main-image').attr('src', newSrc);
            
            // Update active thumbnail
            $thumb.siblings().removeClass('active');
            $thumb.addClass('active');
        },

        // Utility functions
        updateCartCount: function(count) {
            $('.cart-count').text(count);
            if (count > 0) {
                $('.cart-count').removeClass('d-none');
            } else {
                $('.cart-count').addClass('d-none');
            }
        },

        updateCartTotal: function(total) {
            $('.cart-total').text('$' + parseFloat(total).toFixed(2));
        },

        showNotification: function(message, type = 'info') {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';
            
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            $('body').append(alertHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        },

        showFieldErrors: function($form, errors) {
            Object.keys(errors).forEach(function(field) {
                const $field = $form.find(`[name="${field}"]`);
                $field.addClass('is-invalid');
                $field.after(`<div class="invalid-feedback">${errors[field]}</div>`);
            });
        },

        initTooltips: function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        },

        initPopovers: function() {
            $('[data-bs-toggle="popover"]').popover();
        },

        initPriceRange: function() {
            const $slider = $('#price-range');
            if ($slider.length && typeof noUiSlider !== 'undefined') {
                noUiSlider.create($slider[0], {
                    start: [0, 1000],
                    connect: true,
                    range: {
                        'min': 0,
                        'max': 1000
                    },
                    format: {
                        to: function(value) {
                            return Math.round(value);
                        },
                        from: function(value) {
                            return Number(value);
                        }
                    }
                });
            }
        },

        initLazyLoading: function() {
            $('img[data-src]').each(function() {
                const $img = $(this);
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });
                observer.observe(this);
            });
        },

        initScrollAnimations: function() {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            });
            
            $('.animate-on-scroll').each(function() {
                observer.observe(this);
            });
        },

        onModalShow: function(e) {
            const $modal = $(e.target);
            const productId = $modal.data('product-id');
            
            if (productId) {
                // Load product details for quick view modal
                $.ajax({
                    url: '/ajax/product-details.php',
                    method: 'GET',
                    data: { id: productId },
                    success: function(response) {
                        $modal.find('.modal-body').html(response);
                    }
                });
            }
        },

        loadMoreContent: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const page = $btn.data('page') || 2;
            const container = $btn.data('container') || '.products-container';
            
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop('disabled', true);
            
            $.ajax({
                url: window.location.href,
                method: 'GET',
                data: { page: page, ajax: 1 },
                success: function(response) {
                    $(container).append(response);
                    $btn.data('page', page + 1);
                    $btn.html('Load More').prop('disabled', false);
                },
                error: function() {
                    $btn.html('Load More').prop('disabled', false);
                    App.showNotification('Failed to load more content.', 'error');
                }
            });
        },

        // Debounce function for search
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        App.init();
    });

    // Export App object for external access
    window.App = App;

})(jQuery);
