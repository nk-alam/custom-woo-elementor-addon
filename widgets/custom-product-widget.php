<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Custom WooCommerce Products Grid Widget for Elementor
 */
class Custom_Woo_Product_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'custom-woo-products-grid';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('Custom WooCommerce Products Grid', 'custom-woo-elementor');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-woocommerce';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['woocommerce-elements'];
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['woocommerce', 'products', 'grid', 'shop', 'store', 'custom'];
    }

    /**
     * Get script dependencies
     */
    public function get_script_depends() {
        return ['custom-woo-elementor-js'];
    }

    /**
     * Get style dependencies
     */
    public function get_style_depends() {
        return ['custom-woo-elementor-css'];
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {

        // Query Section
        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Query Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'query_type',
            [
                'label' => __('Query Type', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'all',
                'options' => [
                    'all' => __('All Products', 'custom-woo-elementor'),
                    'featured' => __('Featured Products', 'custom-woo-elementor'),
                    'on_sale' => __('On Sale Products', 'custom-woo-elementor'),
                ],
            ]
        );

        $this->add_control(
            'products_count',
            [
                'label' => __('Products Count', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 20,
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => __('Categories', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_categories_list(),
                'multiple' => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Date', 'custom-woo-elementor'),
                    'popularity' => __('Popularity (Best Selling)', 'custom-woo-elementor'),
                    'rating' => __('Rating', 'custom-woo-elementor'),
                    'price' => __('Price: Low to High', 'custom-woo-elementor'),
                    'price-desc' => __('Price: High to Low', 'custom-woo-elementor'),
                    'rand' => __('Random', 'custom-woo-elementor'),
                    'menu_order' => __('Menu Order', 'custom-woo-elementor'),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label' => __('Order', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'desc',
                'options' => [
                    'asc' => __('Ascending', 'custom-woo-elementor'),
                    'desc' => __('Descending', 'custom-woo-elementor'),
                ],
            ]
        );

        $this->end_controls_section();

        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Product Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Auto-select first variation if no default set
        $this->add_control(
            'auto_select_first_variation',
            [
                'label' => __('Auto-select First Variation if No Default', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Show product title
        $this->add_control(
            'show_title',
            [
                'label' => __('Show Product Title', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Show rating
        $this->add_control(
            'show_rating',
            [
                'label' => __('Show Rating', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Enable fake rating
        $this->add_control(
            'enable_fake_rating',
            [
                'label' => __('Enable Fake Rating (Fallback)', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => __('If enabled, use fake rating if no custom or real rating is available.', 'custom-woo-elementor'),
            ]
        );

        // Fake rating value
        $this->add_control(
            'fake_rating',
            [
                'label' => __('Fake Rating', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 5,
                'step' => 0.1,
                'default' => 4.5,
                'condition' => [
                    'enable_fake_rating' => 'yes',
                ],
            ]
        );

        // Fake review count
        $this->add_control(
            'fake_review_count',
            [
                'label' => __('Fake Review Count', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'default' => 100,
                'condition' => [
                    'enable_fake_rating' => 'yes',
                ],
            ]
        );

        // Show badges
        $this->add_control(
            'show_badges',
            [
                'label' => __('Show Product Badges', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Show variation selector
        $this->add_control(
            'show_variation_selector',
            [
                'label' => __('Show Variation Selector', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Show buy now button
        $this->add_control(
            'show_buy_now',
            [
                'label' => __('Show Buy Now Button', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        // Style Section - Layout
        $this->start_controls_section(
            'layout_style_section',
            [
                'label' => __('Layout Style', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Columns
        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 6,
                'default' => 4,
                'tablet_default' => 2,
                'mobile_default' => 2,
                'selectors' => [
                    '{{WRAPPER}} .products-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        // Grid Gap
        $this->add_responsive_control(
            'grid_gap',
            [
                'label' => __('Grid Gap', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .products-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Container styling
        $this->add_control(
            'container_background',
            [
                'label' => __('Container Background', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .custom-product-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_shadow',
                'label' => __('Container Shadow', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .custom-product-container',
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .custom-product-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Container Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .custom-product-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Product Image
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Product Image', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Image Width', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 600,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-image img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Image Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .product-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Product Title
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Product Title', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .product-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .product-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .product-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Rating
        $this->start_controls_section(
            'rating_style_section',
            [
                'label' => __('Product Rating', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_rating' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'star_color',
            [
                'label' => __('Star Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffb400',
                'selectors' => [
                    '{{WRAPPER}} .product-rating .star' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'rating_text_typography',
                'label' => __('Rating Text Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-rating .rating-text',
            ]
        );

        $this->end_controls_section();

        // Style Section - Badges
        $this->start_controls_section(
            'badges_style_section',
            [
                'label' => __('Product Badges', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_badges' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'label' => __('Badge Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-badges .badge',
            ]
        );

        $this->add_control(
            'badge_spacing',
            [
                'label' => __('Badge Spacing', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 5,
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-badges .badge' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'discount_badge_color',
            [
                'label' => __('Discount Badge Background', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#dc3545',
                'selectors' => [
                    '{{WRAPPER}} .discount-badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'original_badge_background',
            [
                'label' => __('Original Price Badge Background', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f8f9fa',
                'selectors' => [
                    '{{WRAPPER}} .badge-original' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'original_badge_color',
            [
                'label' => __('Original Price Badge Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6c757d',
                'selectors' => [
                    '{{WRAPPER}} .badge-original' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Variation Selector
        $this->start_controls_section(
            'variation_style_section',
            [
                'label' => __('Variation Selector', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_variation_selector' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'variation_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .variation-selector select',
            ]
        );

        $this->add_control(
            'variation_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .variation-selector select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'variation_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .variation-selector select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'variation_border',
                'label' => __('Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .variation-selector select',
            ]
        );

        $this->add_responsive_control(
            'variation_padding',
            [
                'label' => __('Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .variation-selector select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'variation_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .variation-selector select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Buttons
        $this->start_controls_section(
            'buttons_style_section',
            [
                'label' => __('Buttons', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Add to Cart Button
        $this->add_control(
            'add_to_cart_heading',
            [
                'label' => __('Add to Cart Button', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'add_to_cart_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .add-to-cart-btn',
            ]
        );

        $this->start_controls_tabs('add_to_cart_tabs');

        $this->start_controls_tab(
            'add_to_cart_normal',
            [
                'label' => __('Normal', 'custom-woo-elementor'),
            ]
        );

        $this->add_control(
            'add_to_cart_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .add-to-cart-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'add_to_cart_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .add-to-cart-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'add_to_cart_border',
                'label' => __('Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .add-to-cart-btn',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'add_to_cart_hover',
            [
                'label' => __('Hover', 'custom-woo-elementor'),
            ]
        );

        $this->add_control(
            'add_to_cart_hover_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .add-to-cart-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'add_to_cart_hover_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .add-to-cart-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'add_to_cart_hover_border',
                'label' => __('Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .add-to-cart-btn:hover',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        // Buy Now Button
        $this->add_control(
            'buy_now_heading',
            [
                'label' => __('Buy Now Button', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'buy_now_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .buy-now-btn',
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('buy_now_tabs');

        $this->start_controls_tab(
            'buy_now_normal',
            [
                'label' => __('Normal', 'custom-woo-elementor'),
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'buy_now_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .buy-now-btn' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'buy_now_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff6b35',
                'selectors' => [
                    '{{WRAPPER}} .buy-now-btn' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'buy_now_border',
                'label' => __('Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .buy-now-btn',
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'buy_now_hover',
            [
                'label' => __('Hover', 'custom-woo-elementor'),
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'buy_now_hover_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .buy-now-btn:hover' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'buy_now_hover_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .buy-now-btn:hover' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'buy_now_hover_border',
                'label' => __('Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .buy-now-btn:hover',
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        // Button dimensions
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Button Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .product-buttons button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Button Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .product-buttons button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_spacing',
            [
                'label' => __('Button Spacing', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-buttons button:not(:last-child)' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Additional Style Sections for More Customization
        $this->start_controls_section(
            'price_style_section',
            [
                'label' => __('Product Price', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-price .current-price',
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label' => __('Price Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .product-price .current-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'original_price_color',
            [
                'label' => __('Original Price Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#999',
                'selectors' => [
                    '{{WRAPPER}} .product-price del' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get categories list for dropdown
     */
    private function get_categories_list() {
        $categories = [];
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $categories[$term->term_id] = $term->name;
            }
        }

        return $categories;
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $args = [
            'limit' => $settings['products_count'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
        ];

        if ($settings['query_type'] === 'featured') {
            $args['featured'] = true;
        } elseif ($settings['query_type'] === 'on_sale') {
            $args['on_sale'] = true;
        }

        if (!empty($settings['categories'])) {
            $cat_slugs = [];
            foreach ($settings['categories'] as $cat_id) {
                $term = get_term($cat_id, 'product_cat');
                if ($term && !is_wp_error($term)) {
                    $cat_slugs[] = $term->slug;
                }
            }
            $args['category'] = $cat_slugs;
        }

        $products = wc_get_products($args);

        if (empty($products)) {
            echo '<div class="custom-product-notice">' . __('No products found.', 'custom-woo-elementor') . '</div>';
            return;
        }

        echo '<div class="products-grid">';

        foreach ($products as $product) {
            $product_id = $product->get_id();
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'full');
            $product_title = $product->get_name();
            $product_badges = get_post_meta($product_id, '_product_badges', true);
            $badge_style = get_post_meta($product_id, '_badge_style', true) ?: 'yellow';

            // Rating
            $custom_rating = get_post_meta($product_id, '_custom_rating', true);
            $product_rating = $custom_rating !== '' ? floatval($custom_rating) : ($settings['enable_fake_rating'] === 'yes' ? $settings['fake_rating'] : $product->get_average_rating());

            $custom_review_count = get_post_meta($product_id, '_custom_review_count', true);
            $product_rating_count = $custom_review_count !== '' ? intval($custom_review_count) : ($settings['enable_fake_rating'] === 'yes' ? $settings['fake_review_count'] : $product->get_rating_count());

            // Default price html
            $regular_price = (float) $product->get_regular_price();
            $sale_price = (float) $product->get_sale_price();
            if ($product->is_on_sale() && $regular_price > 0) {
                $product_price = wc_price($sale_price) . ' <span class="badge badge-original">' . wc_price($regular_price) . '</span>';
                $discount = round((1 - $sale_price / $regular_price) * 100);
            } else {
                $product_price = wc_price($regular_price);
                $discount = 0;
            }

            // Get variations if variable product
            $variations = [];
            $default_variation = null;
            if ($product->is_type('variable')) {
                $default_variation_id = get_post_meta($product_id, '_default_variation', true);

                $available_variations = $product->get_available_variations();
                foreach ($available_variations as $variation_data) {
                    $variation_obj = wc_get_product($variation_data['variation_id']);
                    $attributes = [];
                    foreach ($variation_data['attributes'] as $attr_name => $attr_value) {
                        $taxonomy = str_replace('attribute_', '', $attr_name);
                        $term = get_term_by('slug', $attr_value, $taxonomy);
                        $attributes[] = $term ? $term->name : $attr_value;
                    }

                    $regular_price = (float) $variation_obj->get_regular_price();
                    $sale_price = (float) $variation_obj->get_sale_price();
                    $var_discount = 0;
                    if ($variation_obj->is_on_sale() && $regular_price > 0) {
                        $price_html = wc_price($sale_price) . ' <span class="badge badge-original">' . wc_price($regular_price) . '</span>';
                        $price_text = strip_tags(wc_price($sale_price)) . ' (' . strip_tags(wc_price($regular_price)) . ')';
                        $var_discount = round((1 - $sale_price / $regular_price) * 100);
                    } else {
                        $price_html = wc_price($regular_price);
                        $price_text = strip_tags(wc_price($regular_price));
                    }

                    $weight = $variation_obj->get_weight() ? $variation_obj->get_weight() . 'g' : '';

                    $variations[] = [
                        'id' => $variation_data['variation_id'],
                        'name' => implode(' - ', $attributes),
                        'price_html' => $price_html,
                        'price_text' => $price_text,
                        'weight' => $weight,
                        'attributes' => $variation_data['attributes'],
                        'discount' => $var_discount,
                    ];
                }

                // Set default variation
                if (!empty($variations)) {
                    if ($default_variation_id) {
                        foreach ($variations as $var) {
                            if ($var['id'] == $default_variation_id) {
                                $default_variation = $var;
                                break;
                            }
                        }
                    }
                    if (!$default_variation && $settings['auto_select_first_variation'] === 'yes') {
                        $default_variation = $variations[0];
                    }
                    if ($default_variation) {
                        $product_price = $default_variation['price_html'];
                        $discount = $default_variation['discount'];
                    }
                }
            } else {
                // For simple products, prepend weight if set
                $weight = $product->get_weight() ? $product->get_weight() . 'g' : '';
                if ($weight) {
                    $product_price = esc_html($weight) . ' - ' . $product_price;
                }
            }

            $permalink = get_permalink($product_id);

            ?>
            <div class="custom-product-container" data-product-id="<?php echo esc_attr($product_id); ?>">
                <!-- Product Image -->
                <div class="product-image">
                    <?php if ($product_image): ?>
                        <a href="<?php echo esc_url($permalink); ?>">
                            <img src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product_title); ?>">
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Product Content -->
                <div class="product-content">
                    <!-- Product Title -->
                    <?php if ($settings['show_title'] === 'yes'): ?>
                        <h3 class="product-title">
                            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($product_title); ?></a>
                        </h3>
                    <?php endif; ?>

                    <!-- Product Rating -->
                    <?php if ($settings['show_rating'] === 'yes' && $product_rating > 0): ?>
                        <div class="product-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?php echo $i <= $product_rating ? 'filled' : ''; ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?php echo esc_html(number_format($product_rating, 1)); ?> | <?php echo esc_html($product_rating_count); ?> <?php _e('Reviews', 'custom-woo-elementor'); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Product Badges -->
                    <?php if ($settings['show_badges'] === 'yes'): ?>
                        <div class="product-badges">
                            <?php if ($product_badges): ?>
                                <?php
                                $badges = array_map('trim', explode(',', $product_badges));
                                foreach ($badges as $badge):
                                ?>
                                    <span class="badge badge-<?php echo esc_attr($badge_style); ?>"><?php echo esc_html($badge); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <span class="discount-badge badge badge-red" style="display: <?php echo $discount > 0 ? 'inline-block' : 'none'; ?>;"><?php echo $discount > 0 ? '-' . $discount . '%' : ''; ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Variation Selector -->
                    <?php if ($settings['show_variation_selector'] === 'yes' && !empty($variations)): ?>
                        <div class="variation-selector">
                            <select class="variation-dropdown" data-default-variation="<?php echo $default_variation ? esc_attr($default_variation['id']) : ''; ?>">
                                <?php foreach ($variations as $variation): ?>
                                    <option value="<?php echo esc_attr($variation['id']); ?>"
                                            data-price-html="<?php echo esc_attr($variation['price_html']); ?>"
                                            data-discount="<?php echo esc_attr($variation['discount']); ?>"
                                            data-weight="<?php echo esc_attr($variation['weight']); ?>"
                                            data-attributes="<?php echo esc_attr(json_encode($variation['attributes'])); ?>"
                                            <?php echo ($default_variation && $default_variation['id'] == $variation['id']) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($variation['weight'] . ' - ' . $variation['price_text']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- Product Price -->
                    <div class="product-price">
                        <span class="current-price"><?php echo $product_price; ?></span>
                    </div>

                    <!-- Product Buttons -->
                    <div class="product-buttons">
                        <button class="add-to-cart-btn" data-product-id="<?php echo esc_attr($product_id); ?>">
                            <?php _e('ADD TO CART', 'custom-woo-elementor'); ?>
                        </button>

                        <?php if ($settings['show_buy_now'] === 'yes'): ?>
                            <button class="buy-now-btn" data-product-id="<?php echo esc_attr($product_id); ?>">
                                <?php _e('BUY NOW', 'custom-woo-elementor'); ?>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Loading indicator -->
                    <div class="loading-indicator" style="display: none;">
                        <span><?php _e('Loading...', 'custom-woo-elementor'); ?></span>
                    </div>

                    <!-- Messages -->
                    <div class="product-messages"></div>
                </div>
            </div>
            <?php
        }

        echo '</div>';
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="products-grid">
            <# for (let i = 0; i < 4; i++) { #>
                <div class="custom-product-container">
                    <div class="product-image">
                        <a href="#">
                            <img src="https://via.placeholder.com/300x300?text=Product+Image" alt="Product Image">
                        </a>
                    </div>
                    <div class="product-content">
                        <# if (settings.show_title === 'yes') { #>
                            <h3 class="product-title"><a href="#">Sample Product Title</a></h3>
                        <# } #>
                        
                        <# if (settings.show_rating === 'yes') { #>
                            <div class="product-rating">
                                <div class="stars">
                                    <span class="star filled">★</span>
                                    <span class="star filled">★</span>
                                    <span class="star filled">★</span>
                                    <span class="star filled">★</span>
                                    <span class="star">★</span>
                                </div>
                                <span class="rating-text">4.0 | 150 Reviews</span>
                            </div>
                        <# } #>
                        
                        <# if (settings.show_badges === 'yes') { #>
                            <div class="product-badges">
                                <span class="badge badge-yellow">Medium Spicy</span>
                                <span class="badge badge-green">Jain Friendly</span>
                                <span class="badge badge-orange">Mustard Oil</span>
                                <span class="discount-badge badge badge-red">-20%</span>
                            </div>
                        <# } #>
                        
                        <# if (settings.show_variation_selector === 'yes') { #>
                            <div class="variation-selector">
                                <select class="variation-dropdown">
                                    <option>325g - Rs 299 (Rs 400)</option>
                                    <option>500g - Rs 399 (Rs 500)</option>
                                </select>
                            </div>
                        <# } #>
                        
                        <div class="product-price">
                            <span class="current-price">Rs 299 <span class="badge badge-original">Rs 400</span></span>
                        </div>
                        
                        <div class="product-buttons">
                            <button class="add-to-cart-btn">ADD TO CART</button>
                            <# if (settings.show_buy_now === 'yes') { #>
                                <button class="buy-now-btn">BUY NOW</button>
                            <# } #>
                        </div>
                    </div>
                </div>
            <# } #>
        </div>
        <?php
    }
}