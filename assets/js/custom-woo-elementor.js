(function($) {
    'use strict';

    /**
     * Custom WooCommerce Products Display Widget JavaScript
     * Enhanced with carousel, pagination, load more, and advanced functionality
     */
    class CustomWooProductWidget {
        constructor() {
            this.init();
            this.carousels = new Map();
            this.loadingStates = new Map();
            this.currentPages = new Map();
        }

        init() {
            $(document).ready(() => {
                this.bindEvents();
                this.initializeVariations();
                this.initializeCarousels();
                this.initializePagination();
                this.initializeInfiniteScroll();
                this.initAccessibility();
                this.initTouchEvents();
                this.optimizeImages();
                this.initSEOEnhancements();
            });
        }

        bindEvents() {
            // Variation change event
            $(document).on('change', '.cwea-variation-dropdown', this.handleVariationChange.bind(this));
            
            // Add to cart button click
            $(document).on('click', '.cwea-add-to-cart-btn', this.handleAddToCart.bind(this));
            
            // Buy now button click
            $(document).on('click', '.cwea-buy-now-btn', this.handleBuyNow.bind(this));
            
            // Quick view button click
            $(document).on('click', '.cwea-quick-view-btn', this.handleQuickView.bind(this));
            
            // Load more button click
            $(document).on('click', '.cwea-load-more-btn', this.handleLoadMore.bind(this));
            
            // Pagination navigation
            $(document).on('click', '.cwea-prev-btn', this.handlePrevPage.bind(this));
            $(document).on('click', '.cwea-next-btn', this.handleNextPage.bind(this));
            $(document).on('click', '.cwea-pagination-numbers a', this.handlePageClick.bind(this));
            
            // Handle quantity input if exists
            $(document).on('change', '.cwea-quantity-input', this.handleQuantityChange.bind(this));
            
            // Window resize for responsive handling
            $(window).on('resize', this.debounce(this.handleResize.bind(this), 250));
            
            // Keyboard navigation
            $(document).on('keydown', this.handleKeyboardNavigation.bind(this));
        }

        initializeVariations() {
            $('.cwea-variation-dropdown').each((index, element) => {
                const $dropdown = $(element);
                const defaultVariation = $dropdown.data('default-variation');
                
                if (defaultVariation) {
                    $dropdown.val(defaultVariation).trigger('change');
                }
            });
        }

        initializeCarousels() {
            $('.cwea-products-carousel').each((index, element) => {
                const $carousel = $(element);
                const settings = $carousel.data('carousel') || {};
                
                // Import Swiper if not already loaded
                if (typeof Swiper === 'undefined') {
                    console.warn('Swiper is not loaded. Carousel functionality will not work.');
                    return;
                }
                
                const swiperConfig = {
                    slidesPerView: settings.slidesToShow || 4,
                    slidesPerGroup: settings.slidesToScroll || 1,
                    spaceBetween: 20,
                    autoplay: settings.autoplay ? {
                        delay: settings.autoplaySpeed || 3000,
                        pauseOnMouseEnter: settings.pauseOnHover !== false,
                        disableOnInteraction: false,
                    } : false,
                    loop: settings.infinite !== false,
                    navigation: settings.arrows !== false ? {
                        nextEl: $carousel.find('.swiper-button-next')[0],
                        prevEl: $carousel.find('.swiper-button-prev')[0],
                    } : false,
                    pagination: settings.dots !== false ? {
                        el: $carousel.find('.swiper-pagination')[0],
                        clickable: true,
                        dynamicBullets: true,
                    } : false,
                    breakpoints: {
                        320: {
                            slidesPerView: 1,
                            slidesPerGroup: 1,
                        },
                        768: {
                            slidesPerView: Math.min(2, settings.slidesToShow || 4),
                            slidesPerGroup: 1,
                        },
                        1024: {
                            slidesPerView: Math.min(3, settings.slidesToShow || 4),
                            slidesPerGroup: settings.slidesToScroll || 1,
                        },
                        1200: {
                            slidesPerView: settings.slidesToShow || 4,
                            slidesPerGroup: settings.slidesToScroll || 1,
                        }
                    },
                    on: {
                        init: () => {
                            this.announceToScreenReader('Carousel initialized');
                        },
                        slideChange: (swiper) => {
                            this.announceToScreenReader(`Slide ${swiper.activeIndex + 1} of ${swiper.slides.length}`);
                        }
                    }
                };
                
                try {
                    const swiper = new Swiper($carousel[0], swiperConfig);
                    this.carousels.set($carousel[0], swiper);
                } catch (error) {
                    console.error('Error initializing carousel:', error);
                }
            });
        }

        initializePagination() {
            $('.cwea-products-wrapper[data-settings]').each((index, element) => {
                const $wrapper = $(element);
                const settings = $wrapper.data('settings') || {};
                
                if (settings.enable_pagination === 'yes' && settings.display_type !== 'carousel') {
                    this.currentPages.set(element, 1);
                    this.updatePaginationState($wrapper, 1, true);
                }
            });
        }

        initializeInfiniteScroll() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const $trigger = $(entry.target);
                            const $wrapper = $trigger.closest('.cwea-products-wrapper');
                            const settings = $wrapper.data('settings') || {};
                            
                            if (settings.pagination_type === 'infinite_scroll') {
                                this.loadMoreProducts($wrapper);
                            }
                        }
                    });
                }, {
                    rootMargin: '100px'
                });

                $('.cwea-infinite-scroll-trigger').each((index, element) => {
                    observer.observe(element);
                });
            }
        }

        handleVariationChange(e) {
            const $dropdown = $(e.target);
            const $container = $dropdown.closest('.cwea-product-item');
            const selectedOption = $dropdown.find(':selected');
            
            // Update price
            const newPriceHtml = selectedOption.data('price-html');
            if (newPriceHtml) {
                $container.find('.cwea-product-price').html(newPriceHtml);
            }
            
            // Update badges if discount changed
            const discount = selectedOption.data('discount') || 0;
            const $saleBadge = $container.find('.badge-sale');
            if (discount > 0) {
                $saleBadge.text(`-${discount}%`).show();
            } else {
                $saleBadge.hide();
            }
            
            // Store variation data for later use
            $container.data('selected-variation', {
                id: selectedOption.val(),
                attributes: selectedOption.data('attributes'),
                price_html: newPriceHtml,
                discount: discount
            });
            
            // Announce change to screen readers
            this.announceToScreenReader(`Variation changed to ${selectedOption.text()}`);
        }

        handleQuantityChange(e) {
            const $input = $(e.target);
            const quantity = parseInt($input.val());
            const min = parseInt($input.attr('min')) || 1;
            const max = parseInt($input.attr('max')) || 999;
            
            if (quantity < min) {
                $input.val(min);
            } else if (quantity > max) {
                $input.val(max);
            }
        }

        handleAddToCart(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $container = $button.closest('.cwea-product-item');
            const productId = $button.data('product-id');
            const selectedVariation = $container.data('selected-variation');
            const quantity = this.getQuantity($container);
            
            if (!productId) {
                this.showMessage($container, 'Product ID not found.', 'error');
                return;
            }
            
            if ($container.find('.cwea-variation-dropdown').length > 0 && !selectedVariation) {
                this.showMessage($container, customWooElementor.select_variation_text, 'error');
                return;
            }
            
            // Show loading state
            this.setButtonLoading($button, true);
            
            const ajaxData = {
                action: 'cwea_add_to_cart',
                product_id: productId,
                variation_id: selectedVariation ? selectedVariation.id : 0,
                quantity: quantity,
                variation_data: selectedVariation ? selectedVariation.attributes : {},
                nonce: customWooElementor.nonce
            };
            
            $.ajax({
                url: customWooElementor.ajaxurl,
                type: 'POST',
                data: ajaxData,
                timeout: 30000,
                success: (response) => {
                    this.setButtonLoading($button, false);
                    
                    if (response.success) {
                        this.showMessage($container, response.data.message, 'success');
                        this.updateCartCount(response.data.cart_count);
                        this.animateAddToCart($button);
                        
                        // Trigger custom events
                        $(document).trigger('cwea_added_to_cart', {
                            productId: productId,
                            variationId: selectedVariation ? selectedVariation.id : 0,
                            quantity: quantity,
                            cartCount: response.data.cart_count
                        });
                        
                        // Track event
                        this.trackEvent('add_to_cart', {
                            product_id: productId,
                            quantity: quantity,
                            value: this.extractPriceFromHtml(selectedVariation ? selectedVariation.price_html : $container.find('.cwea-product-price').html())
                        });
                        
                        // Announce to screen readers
                        this.announceToScreenReader(response.data.message);
                    } else {
                        this.showMessage($container, response.data.message, 'error');
                        this.announceToScreenReader(`Error: ${response.data.message}`);
                    }
                },
                error: (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    let errorMessage = customWooElementor.error_text;
                    
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Access denied. Please refresh the page and try again.';
                    }
                    
                    this.showMessage($container, errorMessage, 'error');
                    this.announceToScreenReader(`Error: ${errorMessage}`);
                    console.error('AJAX Error:', error, xhr);
                }
            });
        }

        handleBuyNow(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $container = $button.closest('.cwea-product-item');
            const productId = $button.data('product-id');
            const selectedVariation = $container.data('selected-variation');
            const quantity = this.getQuantity($container);
            
            if (!productId) {
                this.showMessage($container, 'Product ID not found.', 'error');
                return;
            }
            
            if ($container.find('.cwea-variation-dropdown').length > 0 && !selectedVariation) {
                this.showMessage($container, customWooElementor.select_variation_text, 'error');
                return;
            }
            
            // Show loading state
            this.setButtonLoading($button, true);
            
            const ajaxData = {
                action: 'cwea_buy_now',
                product_id: productId,
                variation_id: selectedVariation ? selectedVariation.id : 0,
                quantity: quantity,
                variation_data: selectedVariation ? selectedVariation.attributes : {},
                nonce: customWooElementor.nonce
            };
            
            $.ajax({
                url: customWooElementor.ajaxurl,
                type: 'POST',
                data: ajaxData,
                timeout: 30000,
                success: (response) => {
                    this.setButtonLoading($button, false);
                    
                    if (response.success) {
                        this.showMessage($container, response.data.message, 'info');
                        
                        // Track event before redirect
                        this.trackEvent('buy_now', {
                            product_id: productId,
                            quantity: quantity,
                            value: this.extractPriceFromHtml(selectedVariation ? selectedVariation.price_html : $container.find('.cwea-product-price').html())
                        });
                        
                        // Redirect to checkout
                        setTimeout(() => {
                            window.location.href = response.data.redirect_url || customWooElementor.checkout_url;
                        }, 1000);
                        
                        // Trigger custom event
                        $(document).trigger('cwea_buy_now', {
                            productId: productId,
                            variationId: selectedVariation ? selectedVariation.id : 0,
                            quantity: quantity
                        });
                        
                        // Announce to screen readers
                        this.announceToScreenReader(response.data.message);
                    } else {
                        this.showMessage($container, response.data.message, 'error');
                        this.announceToScreenReader(`Error: ${response.data.message}`);
                    }
                },
                error: (xhr, status, error) => {
                    this.setButtonLoading($button, false);
                    let errorMessage = customWooElementor.error_text;
                    
                    if (status === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                    }
                    
                    this.showMessage($container, errorMessage, 'error');
                    this.announceToScreenReader(`Error: ${errorMessage}`);
                    console.error('AJAX Error:', error, xhr);
                }
            });
        }

        handleQuickView(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const productId = $button.data('product-id');
            
            // This is a placeholder for quick view functionality
            // In a real implementation, you would open a modal with product details
            console.log('Quick view for product:', productId);
            
            // Track event
            this.trackEvent('quick_view', {
                product_id: productId
            });
        }

        handleLoadMore(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $wrapper = $button.closest('.cwea-products-wrapper');
            
            this.loadMoreProducts($wrapper);
        }

        handlePrevPage(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $wrapper = $button.closest('.cwea-products-wrapper');
            const currentPage = this.currentPages.get($wrapper[0]) || 1;
            
            if (currentPage > 1) {
                this.loadPage($wrapper, currentPage - 1);
            }
        }

        handleNextPage(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $wrapper = $button.closest('.cwea-products-wrapper');
            const currentPage = this.currentPages.get($wrapper[0]) || 1;
            
            this.loadPage($wrapper, currentPage + 1);
        }

        handlePageClick(e) {
            e.preventDefault();
            
            const $link = $(e.target);
            const page = parseInt($link.data('page'));
            const $wrapper = $link.closest('.cwea-products-wrapper');
            
            if (page && !isNaN(page)) {
                this.loadPage($wrapper, page);
            }
        }

        loadMoreProducts($wrapper) {
            const settings = $wrapper.data('settings') || {};
            const currentPage = this.currentPages.get($wrapper[0]) || 1;
            const nextPage = currentPage + 1;
            
            if (this.loadingStates.get($wrapper[0])) {
                return; // Already loading
            }
            
            this.setWrapperLoading($wrapper, true);
            
            const ajaxData = {
                action: 'cwea_load_more_products',
                page: nextPage,
                settings: settings,
                nonce: customWooElementor.nonce
            };
            
            $.ajax({
                url: customWooElementor.ajaxurl,
                type: 'POST',
                data: ajaxData,
                timeout: 30000,
                success: (response) => {
                    this.setWrapperLoading($wrapper, false);
                    
                    if (response.success) {
                        const $productsContainer = $wrapper.find('.cwea-products-grid, .cwea-products-list, .cwea-products-masonry').first();
                        $productsContainer.append(response.data.html);
                        
                        this.currentPages.set($wrapper[0], nextPage);
                        
                        // Update pagination state
                        if (!response.data.has_more) {
                            this.disableLoadMore($wrapper);
                        }
                        
                        // Re-initialize any new elements
                        this.initializeVariations();
                        this.optimizeImages();
                        
                        // Trigger custom event
                        $(document).trigger('cwea_products_loaded', {
                            page: nextPage,
                            hasMore: response.data.has_more
                        });
                        
                        // Announce to screen readers
                        this.announceToScreenReader(`Loaded more products. Page ${nextPage}.`);
                    } else {
                        this.showGlobalMessage(response.data.message, 'error');
                        this.disableLoadMore($wrapper);
                    }
                },
                error: (xhr, status, error) => {
                    this.setWrapperLoading($wrapper, false);
                    this.showGlobalMessage(customWooElementor.error_text, 'error');
                    console.error('Load more error:', error, xhr);
                }
            });
        }

        loadPage($wrapper, page) {
            const settings = $wrapper.data('settings') || {};
            
            if (this.loadingStates.get($wrapper[0])) {
                return; // Already loading
            }
            
            this.setWrapperLoading($wrapper, true);
            
            const ajaxData = {
                action: 'cwea_load_more_products',
                page: page,
                settings: settings,
                nonce: customWooElementor.nonce
            };
            
            $.ajax({
                url: customWooElementor.ajaxurl,
                type: 'POST',
                data: ajaxData,
                timeout: 30000,
                success: (response) => {
                    this.setWrapperLoading($wrapper, false);
                    
                    if (response.success) {
                        const $productsContainer = $wrapper.find('.cwea-products-grid, .cwea-products-list, .cwea-products-masonry').first();
                        $productsContainer.html(response.data.html);
                        
                        this.currentPages.set($wrapper[0], page);
                        this.updatePaginationState($wrapper, page, response.data.has_more);
                        
                        // Scroll to top of products
                        $wrapper[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
                        
                        // Re-initialize any new elements
                        this.initializeVariations();
                        this.optimizeImages();
                        
                        // Trigger custom event
                        $(document).trigger('cwea_page_loaded', {
                            page: page,
                            hasMore: response.data.has_more
                        });
                        
                        // Announce to screen readers
                        this.announceToScreenReader(`Loaded page ${page}.`);
                    } else {
                        this.showGlobalMessage(response.data.message, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.setWrapperLoading($wrapper, false);
                    this.showGlobalMessage(customWooElementor.error_text, 'error');
                    console.error('Load page error:', error, xhr);
                }
            });
        }

        updatePaginationState($wrapper, currentPage, hasMore) {
            const $pagination = $wrapper.find('.cwea-pagination');
            
            // Update prev/next buttons
            const $prevBtn = $pagination.find('.cwea-prev-btn');
            const $nextBtn = $pagination.find('.cwea-next-btn');
            
            $prevBtn.prop('disabled', currentPage <= 1);
            $nextBtn.prop('disabled', !hasMore);
            
            // Update load more button
            const $loadMoreBtn = $pagination.find('.cwea-load-more-btn');
            if (!hasMore) {
                $loadMoreBtn.prop('disabled', true).text(customWooElementor.no_more_products);
            }
        }

        disableLoadMore($wrapper) {
            const $loadMoreBtn = $wrapper.find('.cwea-load-more-btn');
            $loadMoreBtn.prop('disabled', true).text(customWooElementor.no_more_products);
        }

        getQuantity($container) {
            const $quantityInput = $container.find('.cwea-quantity-input');
            return $quantityInput.length ? parseInt($quantityInput.val()) || 1 : 1;
        }

        setButtonLoading($button, loading) {
            if (loading) {
                $button.addClass('loading').prop('disabled', true);
                $button.attr('aria-busy', 'true');
            } else {
                $button.removeClass('loading').prop('disabled', false);
                $button.attr('aria-busy', 'false');
            }
        }

        setWrapperLoading($wrapper, loading) {
            const wrapperElement = $wrapper[0];
            
            if (loading) {
                $wrapper.addClass('loading');
                this.loadingStates.set(wrapperElement, true);
                $wrapper.attr('aria-busy', 'true');
            } else {
                $wrapper.removeClass('loading');
                this.loadingStates.set(wrapperElement, false);
                $wrapper.attr('aria-busy', 'false');
            }
        }

        showMessage($container, message, type = 'info') {
            const $messagesContainer = $container.find('.cwea-product-messages');
            const messageHtml = `<div class="message ${type}" role="alert">${this.escapeHtml(message)}</div>`;
            
            $messagesContainer.html(messageHtml);
            
            // Auto-hide success and info messages
            if (type === 'success' || type === 'info') {
                setTimeout(() => {
                    $messagesContainer.find('.message').fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        }

        showGlobalMessage(message, type = 'info') {
            // Create or update global message container
            let $globalMessages = $('.cwea-global-messages');
            if (!$globalMessages.length) {
                $globalMessages = $('<div class="cwea-global-messages" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
                $('body').append($globalMessages);
            }
            
            const messageId = 'msg-' + Date.now();
            const messageHtml = `<div id="${messageId}" class="message ${type}" role="alert" style="background: white; padding: 15px; margin-bottom: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">${this.escapeHtml(message)}</div>`;
            
            $globalMessages.append(messageHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $(`#${messageId}`).fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        updateCartCount(count) {
            // Update cart count in various possible locations
            $('.cart-count, .cart-contents-count, .woocommerce-cart-count').text(count);
            
            // Update WooCommerce fragments
            if (typeof wc_add_to_cart_params !== 'undefined') {
                $(document.body).trigger('wc_fragment_refresh');
            }
            
            // Update cart icon badge
            $('.cart-icon .badge, .mini-cart .count').text(count);
        }

        animateAddToCart($button) {
            $button.addClass('cwea-add-to-cart-success');
            setTimeout(() => {
                $button.removeClass('cwea-add-to-cart-success');
            }, 600);
        }

        handleResize() {
            // Reinitialize carousels on resize
            this.carousels.forEach((swiper, element) => {
                if (swiper && swiper.update) {
                    swiper.update();
                }
            });
        }

        handleKeyboardNavigation(e) {
            // Handle keyboard navigation for accessibility
            if (e.key === 'Enter' || e.key === ' ') {
                const $target = $(e.target);
                
                if ($target.hasClass('cwea-product-item') || $target.closest('.cwea-product-item').length) {
                    e.preventDefault();
                    const $productLink = $target.find('.cwea-product-title a').first();
                    if ($productLink.length) {
                        $productLink[0].click();
                    }
                }
            }
        }

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

        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }

        extractPriceFromHtml(priceHtml) {
            if (!priceHtml) return 0;
            
            // Extract numeric value from price HTML
            const matches = priceHtml.match(/[\d,]+\.?\d*/);
            return matches ? parseFloat(matches[0].replace(/,/g, '')) : 0;
        }

        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, (m) => map[m]);
        }

        initAccessibility() {
            // Add ARIA labels and roles
            $('.cwea-add-to-cart-btn').attr('aria-label', 'Add product to cart');
            $('.cwea-buy-now-btn').attr('aria-label', 'Buy product now');
            $('.cwea-quick-view-btn').attr('aria-label', 'Quick view product');
            $('.cwea-variation-dropdown').attr('aria-label', 'Select product variation');
            
            // Add keyboard navigation support
            $('.cwea-product-buttons button').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).click();
                }
            });
            
            // Make product items focusable
            $('.cwea-product-item').attr('tabindex', '0').attr('role', 'article');
            
            // Add skip links for screen readers
            if (!$('.cwea-skip-link').length) {
                $('body').prepend('<a href="#cwea-products" class="cwea-skip-link screen-reader-text">Skip to products</a>');
            }
        }

        initTouchEvents() {
            let touchStartY = 0;
            let touchStartX = 0;
            
            $('.cwea-product-item').on('touchstart', function(e) {
                const touch = e.originalEvent.touches[0];
                touchStartY = touch.clientY;
                touchStartX = touch.clientX;
            });
            
            $('.cwea-product-item').on('touchend', function(e) {
                const touch = e.originalEvent.changedTouches[0];
                const touchEndY = touch.clientY;
                const touchEndX = touch.clientX;
                const deltaY = touchStartY - touchEndY;
                const deltaX = touchStartX - touchEndX;
                
                // Add subtle feedback for touch interactions
                if (Math.abs(deltaY) < 10 && Math.abs(deltaX) < 10) {
                    $(this).addClass('touched');
                    setTimeout(() => {
                        $(this).removeClass('touched');
                    }, 150);
                }
            });
        }

        optimizeImages() {
            $('.cwea-product-image img').each(function() {
                const $img = $(this);
                
                // Add loading attribute for better performance
                if (!$img.attr('loading')) {
                    $img.attr('loading', 'lazy');
                }
                
                // Add error handling
                $img.off('error.cwea').on('error.cwea', function() {
                    const fallbackSrc = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNiIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
                    $(this).attr('src', fallbackSrc);
                });
                
                // Optimize image loading with Intersection Observer
                if ('IntersectionObserver' in window && !$img.data('observed')) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                const $imgEl = $(img);
                                const dataSrc = $imgEl.data('src');
                                
                                if (dataSrc && dataSrc !== img.src) {
                                    img.src = dataSrc;
                                }
                                
                                observer.unobserve(img);
                            }
                        });
                    }, {
                        rootMargin: '50px'
                    });
                    
                    observer.observe(this);
                    $img.data('observed', true);
                }
            });
        }

        initSEOEnhancements() {
            // Add structured data for products
            $('.cwea-product-item').each(function() {
                const $item = $(this);
                const productId = $item.data('product-id');
                const $title = $item.find('.cwea-product-title');
                const $price = $item.find('.cwea-product-price');
                const $image = $item.find('.cwea-product-image img');
                const $rating = $item.find('.cwea-product-rating');
                
                if (productId && $title.length) {
                    // Add microdata attributes
                    $item.attr('itemscope', '').attr('itemtype', 'https://schema.org/Product');
                    $title.attr('itemprop', 'name');
                    $price.attr('itemprop', 'offers').attr('itemscope', '').attr('itemtype', 'https://schema.org/Offer');
                    $image.attr('itemprop', 'image');
                    
                    if ($rating.length) {
                        $rating.attr('itemprop', 'aggregateRating').attr('itemscope', '').attr('itemtype', 'https://schema.org/AggregateRating');
                    }
                }
            });
        }

        announceToScreenReader(message) {
            // Create or update screen reader announcement area
            let $announcer = $('#cwea-screen-reader-announcer');
            if (!$announcer.length) {
                $announcer = $('<div id="cwea-screen-reader-announcer" aria-live="polite" aria-atomic="true" class="screen-reader-text" style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;"></div>');
                $('body').append($announcer);
            }
            
            $announcer.text(message);
        }

        trackEvent(action, data = {}) {
            // Google Analytics 4
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: 'Custom WooCommerce Widget',
                    currency: customWooElementor.currency_symbol,
                    ...data
                });
            }
            
            // Facebook Pixel
            if (typeof fbq !== 'undefined') {
                fbq('track', action, data);
            }
            
            // Google Tag Manager
            if (typeof dataLayer !== 'undefined') {
                dataLayer.push({
                    event: 'cwea_' + action,
                    ...data
                });
            }
            
            // Custom tracking event
            $(document).trigger('cwea_track_event', { action, data });
        }

        // Public API methods
        refreshCarousels() {
            this.carousels.forEach((swiper) => {
                if (swiper && swiper.update) {
                    swiper.update();
                }
            });
        }

        destroyCarousels() {
            this.carousels.forEach((swiper) => {
                if (swiper && swiper.destroy) {
                    swiper.destroy(true, true);
                }
            });
            this.carousels.clear();
        }

        reinitialize() {
            this.destroyCarousels();
            this.initializeCarousels();
            this.initializeVariations();
            this.optimizeImages();
        }
    }

    // Initialize the widget when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function() {
        window.customWooProductWidget = new CustomWooProductWidget();
    });

    // Fallback initialization for non-Elementor pages
    if (typeof elementorFrontend === 'undefined') {
        $(document).ready(function() {
            window.customWooProductWidget = new CustomWooProductWidget();
        });
    }

    // Expose for global access
    window.CustomWooProductWidget = CustomWooProductWidget;

    // Add CSS for screen reader text if not already present
    if (!$('style[data-cwea-accessibility]').length) {
        $('<style data-cwea-accessibility>.screen-reader-text { position: absolute !important; clip: rect(1px, 1px, 1px, 1px); width: 1px !important; height: 1px !important; overflow: hidden; } .cwea-skip-link:focus { position: absolute; top: 0; left: 0; background: #000; color: #fff; padding: 10px; z-index: 999999; text-decoration: none; }</style>').appendTo('head');
    }

})(jQuery);