(function($) {
    'use strict';

    /**
     * Custom WooCommerce Product Widget JavaScript
     */
    class CustomWooProductWidget {
        constructor() {
            this.init();
        }

        init() {
            $(document).ready(() => {
                this.bindEvents();
                this.initializeVariations();
                this.initAccessibility();
                this.initTouchEvents();
                this.optimizeImages();
            });
        }

        bindEvents() {
            // Variation change event
            $(document).on('change', '.variation-dropdown', this.handleVariationChange.bind(this));
            
            // Add to cart button click
            $(document).on('click', '.add-to-cart-btn', this.handleAddToCart.bind(this));
            
            // Buy now button click
            $(document).on('click', '.buy-now-btn', this.handleBuyNow.bind(this));
            
            // Handle quantity input if exists
            $(document).on('change', '.quantity-input', this.handleQuantityChange.bind(this));
        }

        initializeVariations() {
            $('.variation-dropdown').each(function() {
                const $dropdown = $(this);
                const defaultVariation = $dropdown.data('default-variation');
                
                if (defaultVariation) {
                    $dropdown.val(defaultVariation).trigger('change');
                }
                
                // Add loading state
                $dropdown.closest('.custom-product-container').removeClass('loading');
            });
        }

        handleVariationChange(e) {
            const $dropdown = $(e.target);
            const $container = $dropdown.closest('.custom-product-container');
            const selectedOption = $dropdown.find(':selected');
            
            // Update price
            const newPrice = selectedOption.data('price-html') || selectedOption.data('price');
            const regularPrice = selectedOption.data('regular-price');
            const salePrice = selectedOption.data('sale-price');
            
            if (newPrice) {
                if (typeof newPrice === 'string' && newPrice.includes('₹')) {
                    $container.find('.current-price').html(newPrice);
                } else {
                    // Format price if it's just a number
                    const formattedPrice = this.formatPrice(newPrice);
                    $container.find('.current-price').html(formattedPrice);
                }
            }
            
            // Update original price badge if exists
            if (regularPrice && salePrice && regularPrice !== salePrice) {
                const $originalBadge = $container.find('.original-price-badge');
                if ($originalBadge.length) {
                    $originalBadge.text(this.formatPrice(regularPrice));
                } else {
                    // Add original price badge if it doesn't exist
                    const originalBadgeHtml = `<span class="badge original-price-badge">${this.formatPrice(regularPrice)}</span>`;
                    $container.find('.product-badges').append(originalBadgeHtml);
                }
            } else {
                // Remove original price badge if no sale
                $container.find('.original-price-badge').remove();
            }
            
            // Update weight display if needed
            const newWeight = selectedOption.data('weight');
            if (newWeight && $container.find('.weight-display').length) {
                $container.find('.weight-display').text(newWeight);
            }
            
            // Store variation data for later use
            $container.data('selected-variation', {
                id: selectedOption.val(),
                attributes: selectedOption.data('attributes'),
                price: newPrice,
                regular_price: regularPrice,
                sale_price: salePrice,
                weight: newWeight
            });
            
            // Trigger custom event for variation change
            $(document).trigger('custom_woo_variation_changed', {
                container: $container,
                variation: $container.data('selected-variation')
            });
        }

        handleQuantityChange(e) {
            const $input = $(e.target);
            const quantity = parseInt($input.val());
            
            if (quantity < 1) {
                $input.val(1);
            }
        }

        handleAddToCart(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $container = $button.closest('.custom-product-container');
            const productId = $button.data('product-id');
            const selectedVariation = $container.data('selected-variation');
            const quantity = this.getQuantity($container);
            
            if (!productId) {
                this.showMessage($container, 'Product ID not found.', 'error');
                return;
            }
            
            // Show loading state
            this.setButtonLoading($button, true);
            
            const ajaxData = {
                action: 'add_to_cart_variation',
                product_id: productId,
                variation_id: selectedVariation ? selectedVariation.id : 0,
                quantity: quantity,
                variation_data: selectedVariation ? selectedVariation.attributes : {},
                nonce: customWooElementor.nonce
            };
            
            this.makeAjaxRequest(
                ajaxData,
                (response) => {
                    this.setButtonLoading($button, false);
                    
                    if (response.success) {
                        this.showMessage($container, response.data.message, 'success');
                        this.updateCartCount(response.data.cart_count);
                        this.animateAddToCart($button);
                        
                        // Trigger custom event
                        $(document).trigger('custom_woo_added_to_cart', {
                            productId: productId,
                            variationId: selectedVariation ? selectedVariation.id : 0,
                            quantity: quantity,
                            cartCount: response.data.cart_count
                        });
                        
                    } else {
                        this.showMessage($container, response.data.message, 'error');
                    }
                },
                (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    this.handleAjaxError(xhr, status, error, $container);
                }
            );
        }

        handleBuyNow(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $container = $button.closest('.custom-product-container');
            const productId = $button.data('product-id');
            const selectedVariation = $container.data('selected-variation');
            const quantity = this.getQuantity($container);
            
            if (!productId) {
                this.showMessage($container, 'Product ID not found.', 'error');
                return;
            }
            
            // Show loading state
            this.setButtonLoading($button, true);
            
            const ajaxData = {
                action: 'buy_now_variation',
                product_id: productId,
                variation_id: selectedVariation ? selectedVariation.id : 0,
                quantity: quantity,
                variation_data: selectedVariation ? selectedVariation.attributes : {},
                nonce: customWooElementor.nonce
            };
            
            this.makeAjaxRequest(
                ajaxData,
                (response) => {
                    this.setButtonLoading($button, false);
                    
                    if (response.success) {
                        this.showMessage($container, response.data.message, 'info');
                        
                        // Redirect to checkout
                        setTimeout(() => {
                            window.location.href = response.data.redirect_url || customWooElementor.checkout_url;
                        }, 1000);
                        
                        // Trigger custom event
                        $(document).trigger('custom_woo_buy_now', {
                            productId: productId,
                            variationId: selectedVariation ? selectedVariation.id : 0,
                            quantity: quantity
                        });
                        
                    } else {
                        this.showMessage($container, response.data.message, 'error');
                    }
                },
                (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    this.handleAjaxError(xhr, status, error, $container);
                }
            );
        }

        getQuantity($container) {
            const $quantityInput = $container.find('.quantity-input');
            return $quantityInput.length ? parseInt($quantityInput.val()) || 1 : 1;
        }

        setButtonLoading($button, loading) {
            if (loading) {
                $button.addClass('loading').prop('disabled', true);
            } else {
                $button.removeClass('loading').prop('disabled', false);
            }
        }

        showMessage($container, message, type = 'info') {
            const $messagesContainer = $container.find('.product-messages');
            const messageHtml = `<div class="message ${type}">${message}</div>`;
            
            $messagesContainer.html(messageHtml);
            
            // Auto-hide success messages
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    $messagesContainer.find('.message').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        }

        updateCartCount(count) {
            // Update cart count in header or other locations
            $('.cart-count, .cart-contents-count').text(count);
            
            // Update WooCommerce fragments
            if (typeof wc_add_to_cart_params !== 'undefined') {
                $(document.body).trigger('wc_fragment_refresh');
            }
        }

        animateAddToCart($button) {
            $button.addClass('add-to-cart-success');
            setTimeout(() => {
                $button.removeClass('add-to-cart-success');
            }, 600);
        }

        // Utility methods
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
        }

        // Initialize price formatting
        formatPrice(price) {
            if (typeof price === 'string' && (price.includes('₹') || price.includes('$') || price.includes('£'))) {
                return price;
            }
            
            // Default formatting for Indian Rupees
            if (typeof customWooElementor !== 'undefined' && customWooElementor.currency_symbol) {
                return customWooElementor.currency_symbol + parseFloat(price).toFixed(2);
            }
            
            // Fallback formatting
            if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.currency_symbol) {
                return wc_add_to_cart_params.currency_symbol + parseFloat(price).toFixed(2);
            }
            
            return '₹' + parseFloat(price).toFixed(0);
        }

        // Accessibility improvements
        initAccessibility() {
            // Add ARIA labels
            $('.add-to-cart-btn').attr('aria-label', 'Add product to cart');
            $('.buy-now-btn').attr('aria-label', 'Buy product now');
            $('.variation-dropdown').attr('aria-label', 'Select product variation');
            
            // Add keyboard navigation support
            $('.product-buttons button').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });
        }

        // Handle touch events for mobile
        initTouchEvents() {
            let touchStartY = 0;
            
            $('.custom-product-container').on('touchstart', function(e) {
                touchStartY = e.originalEvent.touches[0].clientY;
            });
            
            $('.custom-product-container').on('touchend', function(e) {
                const touchEndY = e.originalEvent.changedTouches[0].clientY;
                const deltaY = touchStartY - touchEndY;
                
                // Add subtle feedback for touch interactions
                if (Math.abs(deltaY) < 10) {
                    $(this).addClass('touched');
                    setTimeout(() => {
                        $(this).removeClass('touched');
                    }, 150);
                }
            });
        }

        // Performance optimizations
        optimizeImages() {
            $('.product-image img').each(function() {
                const $img = $(this);
                
                // Add loading attribute for better performance
                $img.attr('loading', 'lazy');
                
                // Add proper alt text if missing
                if (!$img.attr('alt')) {
                    const productTitle = $img.closest('.custom-product-container').find('.product-title').text();
                    $img.attr('alt', productTitle || 'Product Image');
                }
                
                // Add error handling
                $img.on('error', function() {
                    $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==');
                });
            });
        }

        // Analytics tracking
        trackEvent(action, data = {}) {
            // Google Analytics 4
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: 'Custom WooCommerce Widget',
                    ...data
                });
            }
            
            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('track', action, data);
            }
            
            // Custom tracking
            $(document).trigger('custom_woo_track_event', { action, data });
        }
    }

    // Initialize the widget when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function() {
        const widget = new CustomWooProductWidget();
        
        // Initialize resize handler
        widget.initResize();
    });

    // Fallback initialization for non-Elementor pages
    if (typeof elementorFrontend === 'undefined') {
        $(document).ready(function() {
            const widget = new CustomWooProductWidget();
            widget.initResize();
        });
    }

    // Expose for global access if needed
    window.CustomWooProductWidget = CustomWooProductWidget;

})(jQuery);