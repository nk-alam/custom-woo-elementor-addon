<?php
/**
 * Plugin Name: Custom WooCommerce Product Elementor Addon
 * Description: Professional Elementor addon for custom WooCommerce product display with advanced customization options, HPOS compatible
 * Version: 2.0.0
 * Author: Nk Alam
 * Text Domain: custom-woo-elementor
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 10.1
 * Elementor tested up to: 3.31
 * Elementor Pro tested up to: 3.31
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('CUSTOM_WOO_ELEMENTOR_VERSION', '2.0.0');
define('CUSTOM_WOO_ELEMENTOR_FILE', __FILE__);
define('CUSTOM_WOO_ELEMENTOR_PATH', plugin_dir_path(__FILE__));
define('CUSTOM_WOO_ELEMENTOR_URL', plugin_dir_url(__FILE__));
define('CUSTOM_WOO_ELEMENTOR_ASSETS', CUSTOM_WOO_ELEMENTOR_URL . 'assets/');
define('CUSTOM_WOO_ELEMENTOR_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class Custom_Woo_Elementor_Plugin {

    /**
     * Plugin Version
     */
    const VERSION = '2.0.0';

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Minimum WooCommerce Version
     */
    const MINIMUM_WC_VERSION = '5.0';

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'i18n']);
        add_action('plugins_loaded', [$this, 'init']);
        
        // Declare HPOS compatibility
        add_action('before_woocommerce_init', [$this, 'declare_hpos_compatibility']);
        
        // Security headers
        add_action('send_headers', [$this, 'add_security_headers']);
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    /**
     * Declare WooCommerce HPOS compatibility
     */
    public function declare_hpos_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }

    /**
     * Add security headers
     */
    public function add_security_headers() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
        }
    }

    /**
     * Load Textdomain
     */
    public function i18n() {
        load_plugin_textdomain('custom-woo-elementor', false, dirname(CUSTOM_WOO_ELEMENTOR_BASENAME) . '/languages');
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
            return;
        }

        // Check WooCommerce version
        if (!version_compare(WC_VERSION, self::MINIMUM_WC_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_wc_version']);
            return;
        }

        // Initialize plugin components
        $this->init_components();
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Add Plugin actions
        add_action('elementor/widgets/register', [$this, 'init_widgets']);
        add_action('elementor/controls/register', [$this, 'init_controls']);

        // Register widget scripts and styles
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
        add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);

        // Add AJAX handlers with proper nonce verification
        add_action('wp_ajax_cwea_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_cwea_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_cwea_buy_now', [$this, 'ajax_buy_now']);
        add_action('wp_ajax_nopriv_cwea_buy_now', [$this, 'ajax_buy_now']);
        add_action('wp_ajax_cwea_load_more_products', [$this, 'ajax_load_more_products']);
        add_action('wp_ajax_nopriv_cwea_load_more_products', [$this, 'ajax_load_more_products']);

        // Add custom product fields
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_product_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_product_fields']);
        add_action('woocommerce_product_options_advanced', [$this, 'add_advanced_product_fields']);

        // Add custom badge styling options
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // SEO optimizations
        add_action('wp_head', [$this, 'add_structured_data'], 1);
        add_filter('wp_lazy_loading_enabled', [$this, 'enable_lazy_loading'], 10, 2);

        // Performance optimizations
        add_action('wp_enqueue_scripts', [$this, 'optimize_scripts'], 999);
    }

    /**
     * Admin notice - Missing main plugin
     */
    public function admin_notice_missing_main_plugin() {
        $this->display_admin_notice(
            sprintf(
                esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'custom-woo-elementor'),
                '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
                '<strong>' . esc_html__('Elementor', 'custom-woo-elementor') . '</strong>'
            )
        );
    }

    /**
     * Admin notice - Minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        $this->display_admin_notice(
            sprintf(
                esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-woo-elementor'),
                '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
                '<strong>' . esc_html__('Elementor', 'custom-woo-elementor') . '</strong>',
                self::MINIMUM_ELEMENTOR_VERSION
            )
        );
    }

    /**
     * Admin notice - Minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        $this->display_admin_notice(
            sprintf(
                esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-woo-elementor'),
                '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
                '<strong>' . esc_html__('PHP', 'custom-woo-elementor') . '</strong>',
                self::MINIMUM_PHP_VERSION
            )
        );
    }

    /**
     * Admin notice - Missing WooCommerce
     */
    public function admin_notice_missing_woocommerce() {
        $this->display_admin_notice(
            sprintf(
                esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'custom-woo-elementor'),
                '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
                '<strong>' . esc_html__('WooCommerce', 'custom-woo-elementor') . '</strong>'
            )
        );
    }

    /**
     * Admin notice - Minimum WooCommerce version
     */
    public function admin_notice_minimum_wc_version() {
        $this->display_admin_notice(
            sprintf(
                esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-woo-elementor'),
                '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
                '<strong>' . esc_html__('WooCommerce', 'custom-woo-elementor') . '</strong>',
                self::MINIMUM_WC_VERSION
            )
        );
    }

    /**
     * Display admin notice
     */
    private function display_admin_notice($message) {
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
        printf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', wp_kses_post($message));
    }

    /**
     * Init Widgets
     */
    public function init_widgets($widgets_manager) {
        // Include Widget files
        require_once(CUSTOM_WOO_ELEMENTOR_PATH . 'widgets/custom-product-widget.php');

        // Register widget
        $widgets_manager->register(new \Custom_Woo_Product_Widget());
    }

    /**
     * Init Controls
     */
    public function init_controls($controls_manager) {
        // Include Control files here if needed in future
    }

    /**
     * Register widget scripts
     */
    public function widget_scripts() {
        // Register Swiper for carousel functionality
        wp_register_script(
            'swiper-js',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            [],
            '11.0.0',
            true
        );

        wp_register_script(
            'custom-woo-elementor-js',
            CUSTOM_WOO_ELEMENTOR_ASSETS . 'js/custom-woo-elementor.js',
            ['jquery', 'swiper-js'],
            CUSTOM_WOO_ELEMENTOR_VERSION,
            true
        );

        // Localize script with enhanced security
        wp_localize_script('custom-woo-elementor-js', 'customWooElementor', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cwea_nonce'),
            'loading_text' => __('Loading...', 'custom-woo-elementor'),
            'error_text' => __('Something went wrong. Please try again.', 'custom-woo-elementor'),
            'added_to_cart' => __('Added to cart!', 'custom-woo-elementor'),
            'checkout_url' => wc_get_checkout_url(),
            'select_variation_text' => __('Please select a variation.', 'custom-woo-elementor'),
            'load_more_text' => __('Load More', 'custom-woo-elementor'),
            'no_more_products' => __('No more products to load.', 'custom-woo-elementor'),
            'cart_url' => wc_get_cart_url(),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'is_rtl' => is_rtl() ? '1' : '0'
        ]);
    }

    /**
     * Register widget styles
     */
    public function widget_styles() {
        // Register Swiper CSS
        wp_register_style(
            'swiper-css',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            [],
            '11.0.0'
        );

        wp_register_style(
            'custom-woo-elementor-css',
            CUSTOM_WOO_ELEMENTOR_ASSETS . 'css/custom-woo-elementor.css',
            ['swiper-css'],
            CUSTOM_WOO_ELEMENTOR_VERSION
        );
    }

    /**
     * AJAX handler for add to cart with enhanced security
     */
    public function ajax_add_to_cart() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cwea_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'custom-woo-elementor')]);
        }

        // Sanitize inputs
        $product_id = absint($_POST['product_id'] ?? 0);
        $variation_id = absint($_POST['variation_id'] ?? 0);
        $quantity = absint($_POST['quantity'] ?? 1);
        $variation_data = $this->sanitize_variation_data($_POST['variation_data'] ?? []);

        if (!$product_id) {
            wp_send_json_error(['message' => __('Product ID not found.', 'custom-woo-elementor')]);
        }

        // Verify product exists and is purchasable
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_purchasable()) {
            wp_send_json_error(['message' => __('Product is not available for purchase.', 'custom-woo-elementor')]);
        }

        // Add to cart with validation
        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation_data);

        if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data)) {
            // Trigger WooCommerce actions
            do_action('woocommerce_ajax_added_to_cart', $product_id);

            wp_send_json_success([
                'message' => __('Product added to cart successfully!', 'custom-woo-elementor'),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total(),
                'cart_hash' => WC()->cart->get_cart_hash()
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Failed to add product to cart.', 'custom-woo-elementor')
            ]);
        }
    }

    /**
     * AJAX handler for buy now
     */
    public function ajax_buy_now() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cwea_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'custom-woo-elementor')]);
        }

        // Sanitize inputs
        $product_id = absint($_POST['product_id'] ?? 0);
        $variation_id = absint($_POST['variation_id'] ?? 0);
        $quantity = absint($_POST['quantity'] ?? 1);
        $variation_data = $this->sanitize_variation_data($_POST['variation_data'] ?? []);

        if (!$product_id) {
            wp_send_json_error(['message' => __('Product ID not found.', 'custom-woo-elementor')]);
        }

        // Verify product exists and is purchasable
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_purchasable()) {
            wp_send_json_error(['message' => __('Product is not available for purchase.', 'custom-woo-elementor')]);
        }

        // Clear cart first
        WC()->cart->empty_cart();

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variation_data);

        if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data)) {
            wp_send_json_success([
                'message' => __('Redirecting to checkout...', 'custom-woo-elementor'),
                'redirect_url' => wc_get_checkout_url()
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Failed to process buy now request.', 'custom-woo-elementor')
            ]);
        }
    }

    /**
     * AJAX handler for load more products
     */
    public function ajax_load_more_products() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cwea_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'custom-woo-elementor')]);
        }

        $page = absint($_POST['page'] ?? 1);
        $settings = $this->sanitize_widget_settings($_POST['settings'] ?? []);

        // Build query args
        $args = $this->build_product_query_args($settings, $page);
        $products = wc_get_products($args);

        if (empty($products)) {
            wp_send_json_error(['message' => __('No more products found.', 'custom-woo-elementor')]);
        }

        // Generate HTML for products
        ob_start();
        $this->render_products($products, $settings);
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'has_more' => count($products) === $settings['products_per_page']
        ]);
    }

    /**
     * Sanitize variation data
     */
    private function sanitize_variation_data($variation_data) {
        if (!is_array($variation_data)) {
            return [];
        }

        $sanitized = [];
        foreach ($variation_data as $key => $value) {
            $sanitized[sanitize_key($key)] = sanitize_text_field($value);
        }

        return $sanitized;
    }

    /**
     * Sanitize widget settings
     */
    private function sanitize_widget_settings($settings) {
        $defaults = [
            'query_type' => 'all',
            'products_per_page' => 4,
            'categories' => [],
            'orderby' => 'date',
            'order' => 'desc'
        ];

        return wp_parse_args($settings, $defaults);
    }

    /**
     * Build product query args
     */
    private function build_product_query_args($settings, $page = 1) {
        $args = [
            'limit' => absint($settings['products_per_page']),
            'page' => $page,
            'orderby' => sanitize_text_field($settings['orderby']),
            'order' => sanitize_text_field($settings['order']),
            'status' => 'publish',
            'visibility' => 'catalog'
        ];

        if ($settings['query_type'] === 'featured') {
            $args['featured'] = true;
        } elseif ($settings['query_type'] === 'on_sale') {
            $args['on_sale'] = true;
        }

        if (!empty($settings['categories'])) {
            $args['category'] = array_map('sanitize_text_field', $settings['categories']);
        }

        return $args;
    }

    /**
     * Render products HTML
     */
    private function render_products($products, $settings) {
        // This will be implemented in the widget class
        // Placeholder for now
        foreach ($products as $product) {
            echo '<div class="product-item">' . esc_html($product->get_name()) . '</div>';
        }
    }

    /**
     * Add custom product fields in admin
     */
    public function add_custom_product_fields() {
        global $post;

        echo '<div class="options_group">';

        // Product badges
        woocommerce_wp_text_input([
            'id' => '_cwea_product_badges',
            'label' => __('Product Badges', 'custom-woo-elementor'),
            'placeholder' => __('Medium Spicy, Jain Friendly, Mustard Oil', 'custom-woo-elementor'),
            'desc_tip' => true,
            'description' => __('Comma separated badges to display (e.g., Medium Spicy, Jain Friendly)', 'custom-woo-elementor')
        ]);

        // Badge style
        woocommerce_wp_select([
            'id' => '_cwea_badge_style',
            'label' => __('Badge Style', 'custom-woo-elementor'),
            'options' => [
                'default' => __('Default', 'custom-woo-elementor'),
                'yellow' => __('Yellow', 'custom-woo-elementor'),
                'green' => __('Green', 'custom-woo-elementor'),
                'orange' => __('Orange', 'custom-woo-elementor'),
                'red' => __('Red', 'custom-woo-elementor'),
                'blue' => __('Blue', 'custom-woo-elementor'),
                'purple' => __('Purple', 'custom-woo-elementor'),
                'custom' => __('Custom', 'custom-woo-elementor')
            ]
        ]);

        // Custom badge colors (shown when custom is selected)
        woocommerce_wp_text_input([
            'id' => '_cwea_badge_bg_color',
            'label' => __('Badge Background Color', 'custom-woo-elementor'),
            'type' => 'color',
            'desc_tip' => true,
            'description' => __('Custom background color for badges', 'custom-woo-elementor')
        ]);

        woocommerce_wp_text_input([
            'id' => '_cwea_badge_text_color',
            'label' => __('Badge Text Color', 'custom-woo-elementor'),
            'type' => 'color',
            'desc_tip' => true,
            'description' => __('Custom text color for badges', 'custom-woo-elementor')
        ]);

        // Custom rating
        woocommerce_wp_text_input([
            'id' => '_cwea_custom_rating',
            'label' => __('Custom Rating', 'custom-woo-elementor'),
            'placeholder' => 'e.g., 4.5',
            'desc_tip' => true,
            'description' => __('Custom rating (0-5) to display on frontend', 'custom-woo-elementor'),
            'type' => 'number',
            'custom_attributes' => [
                'step' => '0.1',
                'min' => '0',
                'max' => '5',
            ],
        ]);

        // Custom review count
        woocommerce_wp_text_input([
            'id' => '_cwea_custom_review_count',
            'label' => __('Custom Review Count', 'custom-woo-elementor'),
            'placeholder' => 'e.g., 100',
            'desc_tip' => true,
            'description' => __('Custom review count to display on frontend', 'custom-woo-elementor'),
            'type' => 'number',
            'custom_attributes' => [
                'min' => '0',
            ],
        ]);

        echo '</div>';
    }

    /**
     * Add advanced product fields
     */
    public function add_advanced_product_fields() {
        global $post;

        echo '<div class="options_group">';

        // Default variation for variable products
        $product = wc_get_product($post->ID);
        if ($product && $product->is_type('variable')) {
            $variations = $product->get_children();
            $options = ['' => __('Select default variation', 'custom-woo-elementor')];
            foreach ($variations as $var_id) {
                $var = wc_get_product($var_id);
                if ($var) {
                    $options[$var_id] = $var->get_name();
                }
            }
            woocommerce_wp_select([
                'id' => '_cwea_default_variation',
                'label' => __('Default Variation', 'custom-woo-elementor'),
                'options' => $options,
                'desc_tip' => true,
                'description' => __('Select the default variation to display', 'custom-woo-elementor')
            ]);
        }

        // SEO fields
        woocommerce_wp_text_input([
            'id' => '_cwea_seo_title',
            'label' => __('SEO Title Override', 'custom-woo-elementor'),
            'desc_tip' => true,
            'description' => __('Override the product title for SEO purposes', 'custom-woo-elementor')
        ]);

        woocommerce_wp_textarea_input([
            'id' => '_cwea_seo_description',
            'label' => __('SEO Description', 'custom-woo-elementor'),
            'desc_tip' => true,
            'description' => __('Custom SEO description for this product', 'custom-woo-elementor')
        ]);

        echo '</div>';
    }

    /**
     * Save custom product fields
     */
    public function save_custom_product_fields($post_id) {
        // Verify nonce
        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save fields with proper sanitization
        $fields = [
            '_cwea_product_badges' => 'sanitize_text_field',
            '_cwea_badge_style' => 'sanitize_text_field',
            '_cwea_badge_bg_color' => 'sanitize_hex_color',
            '_cwea_badge_text_color' => 'sanitize_hex_color',
            '_cwea_custom_rating' => 'floatval',
            '_cwea_custom_review_count' => 'absint',
            '_cwea_default_variation' => 'absint',
            '_cwea_seo_title' => 'sanitize_text_field',
            '_cwea_seo_description' => 'sanitize_textarea_field'
        ];

        foreach ($fields as $field => $sanitize_callback) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_callback, $_POST[$field]);
                update_post_meta($post_id, $field, $value);
            }
        }
    }

    /**
     * Add admin menu for badge styling
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Custom Product Badges', 'custom-woo-elementor'),
            __('Product Badges', 'custom-woo-elementor'),
            'manage_woocommerce',
            'cwea-badge-settings',
            [$this, 'badge_settings_page']
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('cwea_badge_settings', 'cwea_badge_styles');
    }

    /**
     * Badge settings page
     */
    public function badge_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Custom Product Badge Settings', 'custom-woo-elementor'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('cwea_badge_settings');
                do_settings_sections('cwea_badge_settings');
                
                $badge_styles = get_option('cwea_badge_styles', []);
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Global Badge Styles', 'custom-woo-elementor'); ?></th>
                        <td>
                            <p><?php esc_html_e('Configure global styling for product badges. Individual products can override these settings.', 'custom-woo-elementor'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Add structured data for SEO
     */
    public function add_structured_data() {
        if (is_product()) {
            global $product;
            if ($product) {
                $structured_data = [
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => $product->get_name(),
                    'description' => wp_strip_all_tags($product->get_short_description()),
                    'sku' => $product->get_sku(),
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => $product->get_price(),
                        'priceCurrency' => get_woocommerce_currency(),
                        'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
                    ]
                ];

                echo '<script type="application/ld+json">' . wp_json_encode($structured_data) . '</script>';
            }
        }
    }

    /**
     * Enable lazy loading for images
     */
    public function enable_lazy_loading($default, $tag_name) {
        if ('img' === $tag_name) {
            return true;
        }
        return $default;
    }

    /**
     * Optimize scripts loading
     */
    public function optimize_scripts() {
        // Defer non-critical scripts
        if (!is_admin()) {
            wp_script_add_data('custom-woo-elementor-js', 'defer', true);
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary database tables or options
        $this->create_plugin_options();
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

    /**
     * Create plugin options
     */
    private function create_plugin_options() {
        $default_options = [
            'version' => self::VERSION,
            'badge_styles' => [
                'default' => [
                    'background' => '#f8f9fa',
                    'color' => '#6c757d',
                    'border' => '#dee2e6'
                ],
                'yellow' => [
                    'background' => '#fff3cd',
                    'color' => '#856404',
                    'border' => '#ffeaa7'
                ],
                'green' => [
                    'background' => '#d4edda',
                    'color' => '#155724',
                    'border' => '#c3e6cb'
                ],
                'orange' => [
                    'background' => '#ffe8d1',
                    'color' => '#8a4a00',
                    'border' => '#ffccaa'
                ],
                'red' => [
                    'background' => '#f8d7da',
                    'color' => '#721c24',
                    'border' => '#f5c6cb'
                ],
                'blue' => [
                    'background' => '#cce7ff',
                    'color' => '#0056b3',
                    'border' => '#99d6ff'
                ],
                'purple' => [
                    'background' => '#e2d9f3',
                    'color' => '#6f42c1',
                    'border' => '#d1b3ff'
                ]
            ]
        ];

        add_option('cwea_plugin_options', $default_options);
    }
}

// Initialize the plugin
Custom_Woo_Elementor_Plugin::instance();