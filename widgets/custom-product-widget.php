<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Custom WooCommerce Products Widget for Elementor
 * Enhanced with carousel, pagination, load more, and advanced styling options
 */
class Custom_Woo_Product_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'custom-woo-products-display';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('Custom WooCommerce Products Display', 'custom-woo-elementor');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-products';
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
        return ['woocommerce', 'products', 'grid', 'carousel', 'shop', 'store', 'custom', 'pagination', 'load more'];
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
        $this->register_query_controls();
        $this->register_layout_controls();
        $this->register_content_controls();
        $this->register_carousel_controls();
        $this->register_pagination_controls();
        $this->register_style_controls();
    }

    /**
     * Register query controls
     */
    private function register_query_controls() {
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
                    'best_selling' => __('Best Selling', 'custom-woo-elementor'),
                    'top_rated' => __('Top Rated', 'custom-woo-elementor'),
                    'recent' => __('Recent Products', 'custom-woo-elementor'),
                ],
            ]
        );

        $this->add_control(
            'products_count',
            [
                'label' => __('Products Count', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 100,
                'description' => __('Total number of products to display', 'custom-woo-elementor'),
            ]
        );

        $this->add_control(
            'products_per_page',
            [
                'label' => __('Products Per Page', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 20,
                'description' => __('Number of products to load at once (for pagination/load more)', 'custom-woo-elementor'),
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
                'description' => __('Leave empty to show products from all categories', 'custom-woo-elementor'),
            ]
        );

        $this->add_control(
            'exclude_categories',
            [
                'label' => __('Exclude Categories', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_categories_list(),
                'multiple' => true,
                'label_block' => true,
                'description' => __('Categories to exclude from the query', 'custom-woo-elementor'),
            ]
        );

        $this->add_control(
            'tags',
            [
                'label' => __('Product Tags', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_tags_list(),
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
                    'title' => __('Title', 'custom-woo-elementor'),
                    'price' => __('Price', 'custom-woo-elementor'),
                    'popularity' => __('Popularity', 'custom-woo-elementor'),
                    'rating' => __('Rating', 'custom-woo-elementor'),
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
    }

    /**
     * Register layout controls
     */
    private function register_layout_controls() {
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'display_type',
            [
                'label' => __('Display Type', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid', 'custom-woo-elementor'),
                    'carousel' => __('Carousel', 'custom-woo-elementor'),
                    'list' => __('List', 'custom-woo-elementor'),
                    'masonry' => __('Masonry', 'custom-woo-elementor'),
                ],
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 6,
                'default' => 4,
                'tablet_default' => 3,
                'mobile_default' => 2,
                'condition' => [
                    'display_type!' => 'list',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwea-products-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_responsive_control(
            'grid_gap',
            [
                'label' => __('Grid Gap', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwea-products-grid' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cwea-products-masonry' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'enable_pagination',
            [
                'label' => __('Enable Pagination', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'display_type!' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'pagination_type',
            [
                'label' => __('Pagination Type', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'numbers',
                'options' => [
                    'numbers' => __('Numbers', 'custom-woo-elementor'),
                    'prev_next' => __('Previous/Next', 'custom-woo-elementor'),
                    'load_more' => __('Load More Button', 'custom-woo-elementor'),
                    'infinite_scroll' => __('Infinite Scroll', 'custom-woo-elementor'),
                ],
                'condition' => [
                    'enable_pagination' => 'yes',
                    'display_type!' => 'carousel',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register content controls
     */
    private function register_content_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_image',
            [
                'label' => __('Show Product Image', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'image_size',
            [
                'label' => __('Image Size', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'woocommerce_thumbnail',
                'options' => [
                    'thumbnail' => __('Thumbnail', 'custom-woo-elementor'),
                    'medium' => __('Medium', 'custom-woo-elementor'),
                    'large' => __('Large', 'custom-woo-elementor'),
                    'woocommerce_thumbnail' => __('WooCommerce Thumbnail', 'custom-woo-elementor'),
                    'woocommerce_single' => __('WooCommerce Single', 'custom-woo-elementor'),
                    'full' => __('Full Size', 'custom-woo-elementor'),
                ],
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

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

        $this->add_control(
            'title_tag',
            [
                'label' => __('Title HTML Tag', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'h3',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                ],
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_price',
            [
                'label' => __('Show Price', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

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

        $this->add_control(
            'enable_fake_rating',
            [
                'label' => __('Enable Fake Rating (Fallback)', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => __('Show fake rating if no custom or real rating is available.', 'custom-woo-elementor'),
                'condition' => [
                    'show_rating' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'fake_rating',
            [
                'label' => __('Fake Rating Value', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 5,
                'step' => 0.1,
                'default' => 4.5,
                'condition' => [
                    'show_rating' => 'yes',
                    'enable_fake_rating' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'fake_review_count',
            [
                'label' => __('Fake Review Count', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'default' => 100,
                'condition' => [
                    'show_rating' => 'yes',
                    'enable_fake_rating' => 'yes',
                ],
            ]
        );

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

        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Excerpt', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 5,
                'max' => 100,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
            ]
        );

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

        $this->add_control(
            'auto_select_first_variation',
            [
                'label' => __('Auto-select First Variation', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'show_variation_selector' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_add_to_cart',
            [
                'label' => __('Show Add to Cart Button', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

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

        $this->add_control(
            'show_quick_view',
            [
                'label' => __('Show Quick View Button', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register carousel controls
     */
    private function register_carousel_controls() {
        $this->start_controls_section(
            'carousel_section',
            [
                'label' => __('Carousel Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'display_type' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'slides_to_show',
            [
                'label' => __('Slides to Show', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 10,
            ]
        );

        $this->add_control(
            'slides_to_scroll',
            [
                'label' => __('Slides to Scroll', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 10,
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed (ms)', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3000,
                'min' => 1000,
                'max' => 10000,
                'step' => 100,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'infinite_loop',
            [
                'label' => __('Infinite Loop', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_arrows',
            [
                'label' => __('Show Navigation Arrows', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label' => __('Show Pagination Dots', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'custom-woo-elementor'),
                'label_off' => __('Hide', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'pause_on_hover',
            [
                'label' => __('Pause on Hover', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-woo-elementor'),
                'label_off' => __('No', 'custom-woo-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register pagination controls
     */
    private function register_pagination_controls() {
        $this->start_controls_section(
            'pagination_section',
            [
                'label' => __('Pagination Settings', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'enable_pagination' => 'yes',
                    'display_type!' => 'carousel',
                ],
            ]
        );

        $this->add_control(
            'load_more_text',
            [
                'label' => __('Load More Button Text', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Load More Products', 'custom-woo-elementor'),
                'condition' => [
                    'pagination_type' => 'load_more',
                ],
            ]
        );

        $this->add_control(
            'loading_text',
            [
                'label' => __('Loading Text', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Loading...', 'custom-woo-elementor'),
                'condition' => [
                    'pagination_type' => ['load_more', 'infinite_scroll'],
                ],
            ]
        );

        $this->add_control(
            'no_more_products_text',
            [
                'label' => __('No More Products Text', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No more products to load.', 'custom-woo-elementor'),
                'condition' => [
                    'pagination_type' => ['load_more', 'infinite_scroll'],
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register style controls
     */
    private function register_style_controls() {
        // Container Style
        $this->start_controls_section(
            'container_style_section',
            [
                'label' => __('Container', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'container_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'label' => __('Border', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-product-item',
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_shadow',
                'label' => __('Box Shadow', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-product-item',
            ]
        );

        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Add more style sections for different elements
        $this->register_image_style_controls();
        $this->register_title_style_controls();
        $this->register_price_style_controls();
        $this->register_rating_style_controls();
        $this->register_badge_style_controls();
        $this->register_button_style_controls();
        $this->register_pagination_style_controls();
    }

    /**
     * Register image style controls
     */
    private function register_image_style_controls() {
        $this->start_controls_section(
            'image_style_section',
            [
                'label' => __('Product Image', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Width', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
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
                    '{{WRAPPER}} .cwea-product-image img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height',
            [
                'label' => __('Height', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'vh'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-image img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ],
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'image_css_filters',
                'selector' => '{{WRAPPER}} .cwea-product-image img',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register title style controls
     */
    private function register_title_style_controls() {
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
                'selector' => '{{WRAPPER}} .cwea-product-title',
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cwea-product-title a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Hover Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-title a:hover' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .cwea-product-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register price style controls
     */
    private function register_price_style_controls() {
        $this->start_controls_section(
            'price_style_section',
            [
                'label' => __('Product Price', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_price' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-product-price',
            ]
        );

        $this->add_control(
            'price_color',
            [
                'label' => __('Price Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'sale_price_color',
            [
                'label' => __('Sale Price Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e74c3c',
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-price .woocommerce-Price-amount' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'regular_price_color',
            [
                'label' => __('Regular Price Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#999999',
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-price del' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register rating style controls
     */
    private function register_rating_style_controls() {
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
                    '{{WRAPPER}} .cwea-product-rating .star.filled' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'empty_star_color',
            [
                'label' => __('Empty Star Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ddd',
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-rating .star' => 'color: {{VALUE}};',
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
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-rating .star' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'rating_text_typography',
                'label' => __('Rating Text Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-product-rating .rating-text',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register badge style controls
     */
    private function register_badge_style_controls() {
        $this->start_controls_section(
            'badge_style_section',
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
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-product-badges .badge',
            ]
        );

        $this->add_responsive_control(
            'badge_padding',
            [
                'label' => __('Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-badges .badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'badge_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-badges .badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'badge_spacing',
            [
                'label' => __('Spacing', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'default' => [
                    'size' => 5,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-badges' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Register button style controls
     */
    private function register_button_style_controls() {
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Buttons', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-product-buttons button',
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-buttons button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cwea-product-buttons button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Add to Cart Button Styles
        $this->add_control(
            'add_to_cart_heading',
            [
                'label' => __('Add to Cart Button', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('add_to_cart_tabs');

        $this->start_controls_tab(
            'add_to_cart_normal',
            [
                'label' => __('Normal', 'custom-woo-elementor'),
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'add_to_cart_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .cwea-add-to-cart-btn' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .cwea-add-to-cart-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'add_to_cart_hover',
            [
                'label' => __('Hover', 'custom-woo-elementor'),
                'condition' => [
                    'show_add_to_cart' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'add_to_cart_hover_color',
            [
                'label' => __('Text Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwea-add-to-cart-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'add_to_cart_hover_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwea-add-to-cart-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        // Buy Now Button Styles
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
                    '{{WRAPPER}} .cwea-buy-now-btn' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .cwea-buy-now-btn' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .cwea-buy-now-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'buy_now_hover_background',
            [
                'label' => __('Background Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cwea-buy-now-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    /**
     * Register pagination style controls
     */
    private function register_pagination_style_controls() {
        $this->start_controls_section(
            'pagination_style_section',
            [
                'label' => __('Pagination', 'custom-woo-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_pagination' => 'yes',
                    'display_type!' => 'carousel',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'pagination_typography',
                'label' => __('Typography', 'custom-woo-elementor'),
                'selector' => '{{WRAPPER}} .cwea-pagination',
            ]
        );

        $this->add_control(
            'pagination_color',
            [
                'label' => __('Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .cwea-pagination' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'pagination_active_color',
            [
                'label' => __('Active Color', 'custom-woo-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .cwea-pagination .current' => 'color: {{VALUE}};',
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
     * Get tags list for dropdown
     */
    private function get_tags_list() {
        $tags = [];
        $terms = get_terms([
            'taxonomy' => 'product_tag',
            'hide_empty' => false,
        ]);

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $tags[$term->term_id] = $term->name;
            }
        }

        return $tags;
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Build query arguments
        $args = $this->build_query_args($settings);
        
        // Get products
        $products = wc_get_products($args);
        
        if (empty($products)) {
            echo '<div class="cwea-no-products">' . esc_html__('No products found.', 'custom-woo-elementor') . '</div>';
            return;
        }

        // Render products based on display type
        $this->render_products_display($products, $settings);
    }

    /**
     * Build query arguments
     */
    private function build_query_args($settings) {
        $args = [
            'limit' => $settings['products_count'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'status' => 'publish',
            'visibility' => 'catalog'
        ];

        // Query type
        switch ($settings['query_type']) {
            case 'featured':
                $args['featured'] = true;
                break;
            case 'on_sale':
                $args['on_sale'] = true;
                break;
            case 'best_selling':
                $args['orderby'] = 'popularity';
                break;
            case 'top_rated':
                $args['orderby'] = 'rating';
                break;
            case 'recent':
                $args['orderby'] = 'date';
                $args['order'] = 'desc';
                break;
        }

        // Categories
        if (!empty($settings['categories'])) {
            $args['category'] = $settings['categories'];
        }

        // Exclude categories
        if (!empty($settings['exclude_categories'])) {
            $args['exclude_category'] = $settings['exclude_categories'];
        }

        // Tags
        if (!empty($settings['tags'])) {
            $args['tag'] = $settings['tags'];
        }

        return $args;
    }

    /**
     * Render products display
     */
    private function render_products_display($products, $settings) {
        $display_type = $settings['display_type'];
        $wrapper_class = 'cwea-products-wrapper cwea-display-' . $display_type;
        
        if ($settings['enable_pagination'] === 'yes' && $display_type !== 'carousel') {
            $wrapper_class .= ' cwea-has-pagination';
        }

        echo '<div class="' . esc_attr($wrapper_class) . '" data-settings="' . esc_attr(wp_json_encode($settings)) . '">';

        switch ($display_type) {
            case 'carousel':
                $this->render_carousel($products, $settings);
                break;
            case 'list':
                $this->render_list($products, $settings);
                break;
            case 'masonry':
                $this->render_masonry($products, $settings);
                break;
            default:
                $this->render_grid($products, $settings);
                break;
        }

        // Render pagination if enabled
        if ($settings['enable_pagination'] === 'yes' && $display_type !== 'carousel') {
            $this->render_pagination($settings);
        }

        echo '</div>';
    }

    /**
     * Render grid layout
     */
    private function render_grid($products, $settings) {
        echo '<div class="cwea-products-grid">';
        
        foreach ($products as $product) {
            $this->render_product_item($product, $settings);
        }
        
        echo '</div>';
    }

    /**
     * Render carousel layout
     */
    private function render_carousel($products, $settings) {
        $carousel_settings = [
            'slidesToShow' => $settings['slides_to_show'],
            'slidesToScroll' => $settings['slides_to_scroll'],
            'autoplay' => $settings['autoplay'] === 'yes',
            'autoplaySpeed' => $settings['autoplay_speed'],
            'infinite' => $settings['infinite_loop'] === 'yes',
            'arrows' => $settings['show_arrows'] === 'yes',
            'dots' => $settings['show_dots'] === 'yes',
            'pauseOnHover' => $settings['pause_on_hover'] === 'yes',
        ];

        echo '<div class="cwea-products-carousel swiper" data-carousel="' . esc_attr(wp_json_encode($carousel_settings)) . '">';
        echo '<div class="swiper-wrapper">';
        
        foreach ($products as $product) {
            echo '<div class="swiper-slide">';
            $this->render_product_item($product, $settings);
            echo '</div>';
        }
        
        echo '</div>';
        
        if ($settings['show_arrows'] === 'yes') {
            echo '<div class="swiper-button-next"></div>';
            echo '<div class="swiper-button-prev"></div>';
        }
        
        if ($settings['show_dots'] === 'yes') {
            echo '<div class="swiper-pagination"></div>';
        }
        
        echo '</div>';
    }

    /**
     * Render list layout
     */
    private function render_list($products, $settings) {
        echo '<div class="cwea-products-list">';
        
        foreach ($products as $product) {
            $this->render_product_item($product, $settings, 'list');
        }
        
        echo '</div>';
    }

    /**
     * Render masonry layout
     */
    private function render_masonry($products, $settings) {
        echo '<div class="cwea-products-masonry">';
        
        foreach ($products as $product) {
            $this->render_product_item($product, $settings);
        }
        
        echo '</div>';
    }

    /**
     * Render individual product item
     */
    private function render_product_item($product, $settings, $layout = 'grid') {
        $product_id = $product->get_id();
        $item_class = 'cwea-product-item cwea-product-' . $layout;
        
        echo '<div class="' . esc_attr($item_class) . '" data-product-id="' . esc_attr($product_id) . '">';
        
        // Product image
        if ($settings['show_image'] === 'yes') {
            $this->render_product_image($product, $settings);
        }
        
        echo '<div class="cwea-product-content">';
        
        // Product title
        if ($settings['show_title'] === 'yes') {
            $this->render_product_title($product, $settings);
        }
        
        // Product rating
        if ($settings['show_rating'] === 'yes') {
            $this->render_product_rating($product, $settings);
        }
        
        // Product badges
        if ($settings['show_badges'] === 'yes') {
            $this->render_product_badges($product, $settings);
        }
        
        // Product excerpt
        if ($settings['show_excerpt'] === 'yes') {
            $this->render_product_excerpt($product, $settings);
        }
        
        // Product price
        if ($settings['show_price'] === 'yes') {
            $this->render_product_price($product, $settings);
        }
        
        // Variation selector
        if ($settings['show_variation_selector'] === 'yes' && $product->is_type('variable')) {
            $this->render_variation_selector($product, $settings);
        }
        
        // Product buttons
        $this->render_product_buttons($product, $settings);
        
        // Messages container
        echo '<div class="cwea-product-messages"></div>';
        
        echo '</div>'; // .cwea-product-content
        echo '</div>'; // .cwea-product-item
    }

    /**
     * Render product image
     */
    private function render_product_image($product, $settings) {
        $image_size = $settings['image_size'];
        $image_id = $product->get_image_id();
        
        if (!$image_id) {
            return;
        }
        
        $image_url = wp_get_attachment_image_url($image_id, $image_size);
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        
        if (!$image_alt) {
            $image_alt = $product->get_name();
        }
        
        echo '<div class="cwea-product-image">';
        echo '<a href="' . esc_url($product->get_permalink()) . '">';
        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($image_alt) . '" loading="lazy">';
        echo '</a>';
        echo '</div>';
    }

    /**
     * Render product title
     */
    private function render_product_title($product, $settings) {
        $title_tag = $settings['title_tag'];
        $seo_title = get_post_meta($product->get_id(), '_cwea_seo_title', true);
        $title = $seo_title ?: $product->get_name();
        
        echo '<' . esc_attr($title_tag) . ' class="cwea-product-title">';
        echo '<a href="' . esc_url($product->get_permalink()) . '">' . esc_html($title) . '</a>';
        echo '</' . esc_attr($title_tag) . '>';
    }

    /**
     * Render product rating
     */
    private function render_product_rating($product, $settings) {
        $custom_rating = get_post_meta($product->get_id(), '_cwea_custom_rating', true);
        $custom_review_count = get_post_meta($product->get_id(), '_cwea_custom_review_count', true);
        
        $rating = $custom_rating !== '' ? floatval($custom_rating) : $product->get_average_rating();
        $review_count = $custom_review_count !== '' ? intval($custom_review_count) : $product->get_rating_count();
        
        // Use fake rating if enabled and no real rating exists
        if (!$rating && $settings['enable_fake_rating'] === 'yes') {
            $rating = $settings['fake_rating'];
            $review_count = $settings['fake_review_count'];
        }
        
        if (!$rating) {
            return;
        }
        
        echo '<div class="cwea-product-rating">';
        echo '<div class="stars">';
        
        for ($i = 1; $i <= 5; $i++) {
            $class = $i <= $rating ? 'star filled' : 'star';
            echo '<span class="' . esc_attr($class) . '">★</span>';
        }
        
        echo '</div>';
        
        if ($review_count > 0) {
            echo '<span class="rating-text">';
            echo esc_html(number_format($rating, 1)) . ' | ';
            echo esc_html($review_count) . ' ' . esc_html__('Reviews', 'custom-woo-elementor');
            echo '</span>';
        }
        
        echo '</div>';
    }

    /**
     * Render product badges
     */
    private function render_product_badges($product, $settings) {
        $badges = get_post_meta($product->get_id(), '_cwea_product_badges', true);
        $badge_style = get_post_meta($product->get_id(), '_cwea_badge_style', true) ?: 'default';
        
        if (!$badges && !$product->is_on_sale()) {
            return;
        }
        
        echo '<div class="cwea-product-badges">';
        
        // Custom badges
        if ($badges) {
            $badge_list = array_map('trim', explode(',', $badges));
            foreach ($badge_list as $badge) {
                echo '<span class="badge badge-' . esc_attr($badge_style) . '">' . esc_html($badge) . '</span>';
            }
        }
        
        // Sale badge
        if ($product->is_on_sale()) {
            $regular_price = (float) $product->get_regular_price();
            $sale_price = (float) $product->get_sale_price();
            
            if ($regular_price > 0) {
                $discount = round((1 - $sale_price / $regular_price) * 100);
                echo '<span class="badge badge-sale">-' . esc_html($discount) . '%</span>';
            }
        }
        
        echo '</div>';
    }

    /**
     * Render product excerpt
     */
    private function render_product_excerpt($product, $settings) {
        $excerpt = $product->get_short_description();
        
        if (!$excerpt) {
            $excerpt = wp_trim_words($product->get_description(), $settings['excerpt_length']);
        } else {
            $excerpt = wp_trim_words($excerpt, $settings['excerpt_length']);
        }
        
        if ($excerpt) {
            echo '<div class="cwea-product-excerpt">' . wp_kses_post($excerpt) . '</div>';
        }
    }

    /**
     * Render product price
     */
    private function render_product_price($product, $settings) {
        echo '<div class="cwea-product-price">';
        echo $product->get_price_html();
        echo '</div>';
    }

    /**
     * Render variation selector
     */
    private function render_variation_selector($product, $settings) {
        if (!$product->is_type('variable')) {
            return;
        }
        
        $default_variation_id = get_post_meta($product->get_id(), '_cwea_default_variation', true);
        $available_variations = $product->get_available_variations();
        
        if (empty($available_variations)) {
            return;
        }
        
        echo '<div class="cwea-variation-selector">';
        echo '<select class="cwea-variation-dropdown" data-default-variation="' . esc_attr($default_variation_id) . '">';
        echo '<option value="">' . esc_html__('Choose an option', 'custom-woo-elementor') . '</option>';
        
        foreach ($available_variations as $variation_data) {
            $variation = wc_get_product($variation_data['variation_id']);
            if (!$variation) {
                continue;
            }
            
            $attributes = [];
            foreach ($variation_data['attributes'] as $attr_name => $attr_value) {
                $taxonomy = str_replace('attribute_', '', $attr_name);
                $term = get_term_by('slug', $attr_value, $taxonomy);
                $attributes[] = $term ? $term->name : $attr_value;
            }
            
            $option_text = implode(' - ', $attributes);
            $price_html = $variation->get_price_html();
            
            $selected = ($default_variation_id == $variation_data['variation_id']) ? 'selected' : '';
            
            echo '<option value="' . esc_attr($variation_data['variation_id']) . '" ';
            echo 'data-price-html="' . esc_attr($price_html) . '" ';
            echo 'data-attributes="' . esc_attr(wp_json_encode($variation_data['attributes'])) . '" ';
            echo esc_attr($selected) . '>';
            echo esc_html($option_text . ' - ' . strip_tags($price_html));
            echo '</option>';
        }
        
        echo '</select>';
        echo '</div>';
    }

    /**
     * Render product buttons
     */
    private function render_product_buttons($product, $settings) {
        echo '<div class="cwea-product-buttons">';
        
        // Add to Cart button
        if ($settings['show_add_to_cart'] === 'yes') {
            echo '<button class="cwea-add-to-cart-btn" data-product-id="' . esc_attr($product->get_id()) . '">';
            echo esc_html__('Add to Cart', 'custom-woo-elementor');
            echo '</button>';
        }
        
        // Buy Now button
        if ($settings['show_buy_now'] === 'yes') {
            echo '<button class="cwea-buy-now-btn" data-product-id="' . esc_attr($product->get_id()) . '">';
            echo esc_html__('Buy Now', 'custom-woo-elementor');
            echo '</button>';
        }
        
        // Quick View button
        if ($settings['show_quick_view'] === 'yes') {
            echo '<button class="cwea-quick-view-btn" data-product-id="' . esc_attr($product->get_id()) . '">';
            echo esc_html__('Quick View', 'custom-woo-elementor');
            echo '</button>';
        }
        
        echo '</div>';
    }

    /**
     * Render pagination
     */
    private function render_pagination($settings) {
        $pagination_type = $settings['pagination_type'];
        
        echo '<div class="cwea-pagination cwea-pagination-' . esc_attr($pagination_type) . '">';
        
        switch ($pagination_type) {
            case 'load_more':
                echo '<button class="cwea-load-more-btn" data-page="1">';
                echo esc_html($settings['load_more_text']);
                echo '</button>';
                break;
                
            case 'numbers':
                // This will be handled by JavaScript for AJAX pagination
                echo '<div class="cwea-pagination-numbers"></div>';
                break;
                
            case 'prev_next':
                echo '<div class="cwea-pagination-nav">';
                echo '<button class="cwea-prev-btn" disabled>' . esc_html__('Previous', 'custom-woo-elementor') . '</button>';
                echo '<button class="cwea-next-btn">' . esc_html__('Next', 'custom-woo-elementor') . '</button>';
                echo '</div>';
                break;
                
            case 'infinite_scroll':
                echo '<div class="cwea-infinite-scroll-trigger"></div>';
                break;
        }
        
        echo '</div>';
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="cwea-products-wrapper">
            <div class="cwea-products-grid">
                <# for (let i = 0; i < 4; i++) { #>
                    <div class="cwea-product-item">
                        <# if (settings.show_image === 'yes') { #>
                            <div class="cwea-product-image">
                                <a href="#">
                                    <img src="https://via.placeholder.com/300x300?text=Product+Image" alt="Product Image">
                                </a>
                            </div>
                        <# } #>
                        
                        <div class="cwea-product-content">
                            <# if (settings.show_title === 'yes') { #>
                                <{{{ settings.title_tag }}} class="cwea-product-title">
                                    <a href="#">Sample Product Title</a>
                                </{{{ settings.title_tag }}}>
                            <# } #>
                            
                            <# if (settings.show_rating === 'yes') { #>
                                <div class="cwea-product-rating">
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
                                <div class="cwea-product-badges">
                                    <span class="badge badge-default">Sample Badge</span>
                                    <span class="badge badge-sale">-20%</span>
                                </div>
                            <# } #>
                            
                            <# if (settings.show_excerpt === 'yes') { #>
                                <div class="cwea-product-excerpt">
                                    This is a sample product excerpt that shows a brief description...
                                </div>
                            <# } #>
                            
                            <# if (settings.show_price === 'yes') { #>
                                <div class="cwea-product-price">
                                    <span class="woocommerce-Price-amount">$29.99</span>
                                </div>
                            <# } #>
                            
                            <div class="cwea-product-buttons">
                                <# if (settings.show_add_to_cart === 'yes') { #>
                                    <button class="cwea-add-to-cart-btn">Add to Cart</button>
                                <# } #>
                                <# if (settings.show_buy_now === 'yes') { #>
                                    <button class="cwea-buy-now-btn">Buy Now</button>
                                <# } #>
                                <# if (settings.show_quick_view === 'yes') { #>
                                    <button class="cwea-quick-view-btn">Quick View</button>
                                <# } #>
                            </div>
                        </div>
                    </div>
                <# } #>
            </div>
        </div>
        <?php
    }
}