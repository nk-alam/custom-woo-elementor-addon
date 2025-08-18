<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Custom WooCommerce Product Widget for Elementor
 */
class Custom_Woo_Product_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'custom-woo-product';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('Custom WooCommerce Product', 'custom-woo-elementor');
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
        return ['woocommerce', 'product', 'shop', 'store', 'custom'];
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

        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Product Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Product Selection Type
        $this->add_control(
            'product_selection_type',
            [
                'label' => __('Product Selection', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'single' => __('Single Product', 'custom-woo-elementor'),
                    'multiple' => __('Multiple Products', 'custom-woo-elementor'),
                ],
                'default' => 'single',
            ]
        );

        // Single Product Selection
        $this->add_control(
            'product_id',
            [
                'label' => __('Select Product', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_products_list(),
                'default' => '',
                'label_block' => true,
                'condition' => [
                    'product_selection_type' => 'single',
                ],
            ]
        );

        // Multiple Products Settings
        $this->add_control(
            'products_per_page',
            [
                'label' => __('Products Per Page', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 20,
                'condition' => [
                    'product_selection_type' => 'multiple',
                ],
            ]
        );

        $this->add_control(
            'product_order',
            [
                'label' => __('Order By', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'date' => __('Date', 'custom-woo-elementor'),
                    'title' => __('Title', 'custom-woo-elementor'),
                    'price' => __('Price', 'custom-woo-elementor'),
                    'popularity' => __('Popularity', 'custom-woo-elementor'),
                    'rating' => __('Rating', 'custom-woo-elementor'),
                    'menu_order' => __('Menu Order', 'custom-woo-elementor'),
                ],
                'default' => 'date',
                'condition' => [
                    'product_selection_type' => 'multiple',
                ],
            ]
        );

        $this->add_control(
            'product_order_direction',
            [
                'label' => __('Order Direction', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'DESC' => __('Descending', 'custom-woo-elementor'),
                    'ASC' => __('Ascending', 'custom-woo-elementor'),
                ],
                'default' => 'DESC',
                'condition' => [
                    'product_selection_type' => 'multiple',
                ],
            ]
        );

        $this->add_control(
            'product_categories',
            [
                'label' => __('Product Categories', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'multiple' => true,
                'label_block' => true,
                'condition' => [
                    'product_selection_type' => 'multiple',
                ],
            ]
        );

        // Layout Settings
        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => __('1 Column', 'custom-woo-elementor'),
                    '2' => __('2 Columns', 'custom-woo-elementor'),
                    '3' => __('3 Columns', 'custom-woo-elementor'),
                    '4' => __('4 Columns', 'custom-woo-elementor'),
                ],
                'default' => '4',
                'condition' => [
                    'product_selection_type' => 'multiple',
                ],
            ]
        );

        // Auto-select first variation
        $this->add_control(
            'auto_select_first_variation',
            [
                'label' => __('Auto-select First Variation', 'custom-woo-elementor'),
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
                'default' => 'no',
            ]
        );

        // Show original price as badge
        $this->add_control(
            'show_original_price_badge',
            [
                'label' => __('Show Original Price as Badge', 'custom-woo-elementor'),
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
                'fields_options' => [
                    'box_shadow_type' => [
                        'default' => 'yes',
                    ],
                    'box_shadow' => [
                        'default' => [
                            'horizontal' => 0,
                            'vertical' => 4,
                            'blur' => 20,
                            'spread' => 0,
                            'color' => 'rgba(0,0,0,0.1)',
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                ],
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
                'default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .custom-product-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'container_margin',
            [
                'label' => __('Container Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .custom-product-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'products_gap',
            [
                'label' => __('Products Gap', 'custom-woo-elementor'),
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
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .products-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'product_selection_type' => 'multiple',
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

        $this->add_responsive_control(
            'image_height',
            [
                'label' => __('Image Height', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 600,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-image img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ],
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Image Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'label' => __('Image Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-image img',
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
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => ['default' => ['size' => 18, 'unit' => 'px']],
                    'font_weight' => ['default' => '600'],
                    'line_height' => ['default' => ['size' => 1.4, 'unit' => 'em']],
                ],
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
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Title Hover Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .custom-product-container:hover .product-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 10,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_alignment',
            [
                'label' => __('Alignment', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'custom-woo-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'custom-woo-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'custom-woo-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .product-title' => 'text-align: {{VALUE}};',
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
                    '{{WRAPPER}} .product-rating .star.filled' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'star_empty_color',
            [
                'label' => __('Empty Star Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ddd',
                'selectors' => [
                    '{{WRAPPER}} .product-rating .star' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'star_size',
            [
                'label' => __('Star Size', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 30,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-rating .star' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'rating_text_typography',
                'label' => __('Rating Text Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-rating .rating-text',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => ['default' => ['size' => 14, 'unit' => 'px']],
                    'font_weight' => ['default' => '500'],
                ],
            ]
        );

        $this->add_control(
            'rating_text_color',
            [
                'label' => __('Rating Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#666',
                'selectors' => [
                    '{{WRAPPER}} .product-rating .rating-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'rating_margin',
            [
                'label' => __('Rating Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 12,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
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
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => ['default' => ['size' => 12, 'unit' => 'px']],
                    'font_weight' => ['default' => '500'],
                    'text_transform' => ['default' => 'uppercase'],
                    'letter_spacing' => ['default' => ['size' => 0.5, 'unit' => 'px']],
                ],
            ]
        );

        $this->add_responsive_control(
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
                    'size' => 6,
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-badges' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label' => __('Badge Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => 4,
                    'right' => 12,
                    'bottom' => 4,
                    'left' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-badges .badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'badge_border_radius',
            [
                'label' => __('Badge Border Radius', 'custom-woo-elementor'),
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
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-badges .badge' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badges_margin',
            [
                'label' => __('Badges Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 15,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-badges' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Price
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
                'label' => __('Price Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .product-price .current-price',
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => ['default' => ['size' => 20, 'unit' => 'px']],
                    'font_weight' => ['default' => '700'],
                ],
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
            'original_price_badge_background',
            [
                'label' => __('Original Price Badge Background', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ff6b35',
                'selectors' => [
                    '{{WRAPPER}} .original-price-badge' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_original_price_badge' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'original_price_badge_color',
            [
                'label' => __('Original Price Badge Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .original-price-badge' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_original_price_badge' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'price_margin',
            [
                'label' => __('Price Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 20,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => ['default' => ['size' => 14, 'unit' => 'px']],
                    'font_weight' => ['default' => '600'],
                    'text_transform' => ['default' => 'uppercase'],
                ],
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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'add_to_cart_background',
                'label' => __('Background', 'custom-woo-elementor'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .add-to-cart-btn',
                'fields_options' => [
                    'background' => ['default' => 'classic'],
                    'color' => ['default' => '#007cba'],
                ],
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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'add_to_cart_hover_background',
                'label' => __('Background', 'custom-woo-elementor'),
                'types' => ['classic', 'gradient'],
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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'buy_now_background',
                'label' => __('Background', 'custom-woo-elementor'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .buy-now-btn',
                'condition' => [
                    'show_buy_now' => 'yes',
                ],
                'fields_options' => [
                    'background' => ['default' => 'classic'],
                    'color' => ['default' => '#ff6b35'],
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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'buy_now_hover_background',
                'label' => __('Background', 'custom-woo-elementor'),
                'types' => ['classic', 'gradient'],
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
                'default' => [
                    'top' => 12,
                    'right' => 24,
                    'bottom' => 12,
                    'left' => 24,
                    'unit' => 'px',
                ],
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
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
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
                    '{{WRAPPER}} .product-buttons' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_width',
            [
                'label' => __('Button Width', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'auto' => __('Auto', 'custom-woo-elementor'),
                    '100%' => __('Full Width', 'custom-woo-elementor'),
                    'custom' => __('Custom', 'custom-woo-elementor'),
                ],
                'default' => 'auto',
                'selectors_dictionary' => [
                    'auto' => 'auto',
                    '100%' => '100%',
                    'custom' => 'var(--button-width)',
                ],
                'selectors' => [
                    '{{WRAPPER}} .product-buttons button' => 'width: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_custom_width',
            [
                'label' => __('Custom Width', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'button_width' => 'custom',
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--button-width: {{SIZE}}{{UNIT}};',
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
                'fields_options' => [
                    'typography' => ['default' => 'yes'],
                    'font_size' => ['default' => ['size' => 14, 'unit' => 'px']],
                ],
            ]
        );

        $this->add_control(
            'variation_text_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333',
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
                'fields_options' => [
                    'border' => ['default' => 'solid'],
                    'width' => ['default' => ['top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2, 'unit' => 'px']],
                    'color' => ['default' => '#e1e5e9'],
                ],
            ]
        );

        $this->add_control(
            'variation_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .variation-selector select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'variation_padding',
            [
                'label' => __('Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'default' => [
                    'top' => 12,
                    'right' => 16,
                    'bottom' => 12,
                    'left' => 16,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .variation-selector select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'variation_margin',
            [
                'label' => __('Margin', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 15,
                    'left' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .variation-selector' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Get products list for dropdown
     */
    private function get_products_list() {
        $products = [];
        $query = new WP_Query([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $products[get_the_ID()] = get_the_title();
            }
            wp_reset_postdata();
        }

        return $products;
    }

    /**
     * Get product categories for dropdown
     */
    private function get_product_categories() {
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
     * Get products based on settings
     */
    private function get_products_query($settings) {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $settings['products_per_page'] ?? 4,
            'orderby' => $settings['product_order'] ?? 'date',
            'order' => $settings['product_order_direction'] ?? 'DESC',
        ];

        // Handle different order types
        switch ($settings['product_order']) {
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby'] = 'meta_value_num';
                break;
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby'] = 'meta_value_num';
                break;
        }

        // Filter by categories
        if (!empty($settings['product_categories'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $settings['product_categories'],
                ],
            ];
        }

        return new WP_Query($args);
    }

    /**
     * Render single product
     */
    private function render_single_product($product_id, $settings) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        // Get product data
        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'full');
        $product_title = $product->get_name();
        $product_rating = $product->get_average_rating();
        $product_rating_count = $product->get_rating_count();
        $custom_weight = get_post_meta($product_id, '_custom_weight_display', true);
        $product_badges = get_post_meta($product_id, '_product_badges', true);
        $badge_style = get_post_meta($product_id, '_badge_style', true) ?: 'yellow';

        // Get variations if variable product
        $variations = [];
        $default_variation = null;
        $current_price = $product->get_price_html();
        $original_price = '';

        if ($product->is_type('variable')) {
            $available_variations = $product->get_available_variations();
            foreach ($available_variations as $variation) {
                $variation_obj = wc_get_product($variation['variation_id']);
                $attributes = [];
                foreach ($variation['attributes'] as $attr_name => $attr_value) {
                    $taxonomy = str_replace('attribute_', '', $attr_name);
                    $term = get_term_by('slug', $attr_value, $taxonomy);
                    $attributes[] = $term ? $term->name : $attr_value;
                }
                
                $weight_display = $custom_weight ?: ($variation_obj->get_weight() ? $variation_obj->get_weight() . 'g' : '');
                
                $variations[] = [
                    'id' => $variation['variation_id'],
                    'name' => implode(' - ', $attributes),
                    'price' => $variation_obj->get_price(),
                    'regular_price' => $variation_obj->get_regular_price(),
                    'sale_price' => $variation_obj->get_sale_price(),
                    'price_html' => $variation_obj->get_price_html(),
                    'weight' => $weight_display,
                    'attributes' => $variation['attributes']
                ];
            }
            
            if ($settings['auto_select_first_variation'] === 'yes' && !empty($variations)) {
                $default_variation = $variations[0];
                $current_price = wc_price($default_variation['price']);
                if ($default_variation['regular_price'] && $default_variation['sale_price']) {
                    $original_price = wc_price($default_variation['regular_price']);
                }
            }
        }

        ?>
        <div class="custom-product-container" data-product-id="<?php echo esc_attr($product_id); ?>">
            
            <!-- Product Image -->
            <div class="product-image">
                <?php if ($product_image): ?>
                    <img src="<?php echo esc_url($product_image[0]); ?>" alt="<?php echo esc_attr($product_title); ?>" loading="lazy">
                <?php else: ?>
                    <img src="<?php echo wc_placeholder_img_src(); ?>" alt="<?php echo esc_attr($product_title); ?>" loading="lazy">
                <?php endif; ?>
            </div>

            <!-- Product Content -->
            <div class="product-content">
                
                <!-- Product Title -->
                <?php if ($settings['show_title'] === 'yes'): ?>
                    <h3 class="product-title"><?php echo esc_html($product_title); ?></h3>
                <?php endif; ?>

                <!-- Product Rating -->
                <?php if ($settings['show_rating'] === 'yes' && $product_rating > 0): ?>
                    <div class="product-rating">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $product_rating ? 'filled' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text"><?php echo esc_html($product_rating); ?> | <?php echo esc_html($product_rating_count); ?> <?php _e('Reviews', 'custom-woo-elementor'); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Product Badges -->
                <?php if ($settings['show_badges'] === 'yes' && $product_badges): ?>
                    <div class="product-badges">
                        <?php
                        $badges = array_map('trim', explode(',', $product_badges));
                        foreach ($badges as $badge):
                        ?>
                            <span class="badge badge-<?php echo esc_attr($badge_style); ?>"><?php echo esc_html($badge); ?></span>
                        <?php endforeach; ?>
                        
                        <!-- Original Price Badge -->
                        <?php if ($settings['show_original_price_badge'] === 'yes' && $original_price): ?>
                            <span class="badge original-price-badge"><?php echo $original_price; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Variation Selector -->
                <?php if ($settings['show_variation_selector'] === 'yes' && !empty($variations)): ?>
                    <div class="variation-selector">
                        <select class="variation-dropdown" data-default-variation="<?php echo $default_variation ? esc_attr($default_variation['id']) : ''; ?>">
                            <?php foreach ($variations as $variation): ?>
                                <option value="<?php echo esc_attr($variation['id']); ?>" 
                                        data-price="<?php echo esc_attr($variation['price']); ?>"
                                        data-price-html="<?php echo esc_attr($variation['price_html']); ?>"
                                        data-regular-price="<?php echo esc_attr($variation['regular_price']); ?>"
                                        data-sale-price="<?php echo esc_attr($variation['sale_price']); ?>"
                                        data-weight="<?php echo esc_attr($variation['weight']); ?>"
                                        data-attributes="<?php echo esc_attr(json_encode($variation['attributes'])); ?>"
                                        <?php echo ($default_variation && $default_variation['id'] == $variation['id']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($variation['weight'] . ' - ' . wc_price($variation['price'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Product Price -->
                <div class="product-price">
                    <span class="current-price"><?php echo $current_price; ?></span>
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

                <!-- Messages -->
                <div class="product-messages"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if ($settings['product_selection_type'] === 'single') {
            $product_id = $settings['product_id'];
            if (empty($product_id)) {
                echo '<div class="custom-product-notice">' . __('Please select a product from the widget settings.', 'custom-woo-elementor') . '</div>';
                return;
            }
            $this->render_single_product($product_id, $settings);
        } else {
            // Multiple products
            $query = $this->get_products_query($settings);
            $columns = $settings['columns'] ?? 4;
            
            if ($query->have_posts()) {
                echo '<div class="products-grid products-columns-' . esc_attr($columns) . '">';
                while ($query->have_posts()) {
                    $query->the_post();
                    $this->render_single_product(get_the_ID(), $settings);
                }
                echo '</div>';
                wp_reset_postdata();
            } else {
                echo '<div class="custom-product-notice">' . __('No products found.', 'custom-woo-elementor') . '</div>';
            }
        }
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <#
        if (settings.product_selection_type === 'single') {
            if (settings.product_id) {
        #>
        <div class="custom-product-container">
            <div class="product-image">
                <img src="https://via.placeholder.com/300x300?text=Product+Image" alt="Product Image">
            </div>
            <div class="product-content">
                <# if (settings.show_title === 'yes') { #>
                    <h3 class="product-title">Sample Product Title</h3>
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
                        <# if (settings.show_original_price_badge === 'yes') { #>
                            <span class="badge original-price-badge">Rs 400</span>
                        <# } #>
                    </div>
                <# } #>
                
                <# if (settings.show_variation_selector === 'yes') { #>
                    <div class="variation-selector">
                        <select class="variation-dropdown">
                            <option>325g - Rs 299</option>
                            <option>500g - Rs 399</option>
                        </select>
                    </div>
                <# } #>
                
                <div class="product-price">
                    <span class="current-price">Rs 299</span>
                </div>
                
                <div class="product-buttons">
                    <button class="add-to-cart-btn">ADD TO CART</button>
                    <# if (settings.show_buy_now === 'yes') { #>
                        <button class="buy-now-btn">BUY NOW</button>
                    <# } #>
                </div>
            </div>
        </div>
        <#
            } else {
        #>
        <div class="custom-product-notice">Please select a product from the widget settings.</div>
        <#
            }
        } else {
            // Multiple products preview
            var columns = settings.columns || 4;
        #>
        <div class="products-grid products-columns-{{{columns}}}">
            <# for (var i = 0; i < (settings.products_per_page || 4); i++) { #>
            <div class="custom-product-container">
                <div class="product-image">
                    <img src="https://via.placeholder.com/300x300?text=Product+{{{i+1}}}" alt="Product Image">
                </div>
                <div class="product-content">
                    <# if (settings.show_title === 'yes') { #>
                        <h3 class="product-title">Sample Product {{{i+1}}}</h3>
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
                        </div>
                    <# } #>
                    
                    <div class="product-price">
                        <span class="current-price">Rs 299</span>
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
        <#
        }
        #>
        <?php
    }
}