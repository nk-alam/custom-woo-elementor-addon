<?php
/**
 * Plugin Name: Custom WooCommerce Product Elementor Addon
 * Description: Professional Elementor addon for custom WooCommerce product display with advanced customization options
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: custom-woo-elementor
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Elementor tested up to: 3.17
 * Elementor Pro tested up to: 3.17
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Plugin constants
define('CUSTOM_WOO_ELEMENTOR_VERSION', '1.0.0');
define('CUSTOM_WOO_ELEMENTOR_FILE', __FILE__);
define('CUSTOM_WOO_ELEMENTOR_PATH', plugin_dir_path(__FILE__));
define('CUSTOM_WOO_ELEMENTOR_URL', plugin_dir_url(__FILE__));
define('CUSTOM_WOO_ELEMENTOR_ASSETS', CUSTOM_WOO_ELEMENTOR_URL . 'assets/');

/**
 * Main Plugin Class
 */
final class Custom_Woo_Elementor_Plugin {

    /**
     * Plugin Version
     */
    const VERSION = '1.0.0';

    /**
     * Minimum Elementor Version
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum PHP Version
     */
    const MINIMUM_PHP_VERSION = '7.4';

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
    }

    /**
     * Load Textdomain
     */
    public function i18n() {
        load_plugin_textdomain('custom-woo-elementor');
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

        // Add Plugin actions
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets']);
        add_action('elementor/controls/controls_registered', [$this, 'init_controls']);

        // Register widget scripts
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
        add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);

        // Add AJAX handlers
        add_action('wp_ajax_add_to_cart_variation', [$this, 'ajax_add_to_cart_variation']);
        add_action('wp_ajax_nopriv_add_to_cart_variation', [$this, 'ajax_add_to_cart_variation']);
        add_action('wp_ajax_buy_now_variation', [$this, 'ajax_buy_now_variation']);
        add_action('wp_ajax_nopriv_buy_now_variation', [$this, 'ajax_buy_now_variation']);

        // Add custom product fields
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_product_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_product_fields']);
    }

    /**
     * Admin notice - Missing main plugin
     */
    public function admin_notice_missing_main_plugin() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'custom-woo-elementor'),
            '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'custom-woo-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - Minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-woo-elementor'),
            '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'custom-woo-elementor') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - Minimum PHP version
     */
    public function admin_notice_minimum_php_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'custom-woo-elementor'),
            '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
            '<strong>' . esc_html__('PHP', 'custom-woo-elementor') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice - Missing WooCommerce
     */
    public function admin_notice_missing_woocommerce() {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'custom-woo-elementor'),
            '<strong>' . esc_html__('Custom WooCommerce Product Elementor Addon', 'custom-woo-elementor') . '</strong>',
            '<strong>' . esc_html__('WooCommerce', 'custom-woo-elementor') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Init Widgets
     */
    public function init_widgets() {
        // Include Widget files
        require_once(CUSTOM_WOO_ELEMENTOR_PATH . 'widgets/custom-product-widget.php');

        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Custom_Woo_Product_Widget());
    }

    /**
     * Init Controls
     */
    public function init_controls() {
        // Include Control files here if needed
    }

    /**
     * Register widget scripts
     */
    public function widget_scripts() {
        wp_register_script(
            'custom-woo-elementor-js',
            CUSTOM_WOO_ELEMENTOR_ASSETS . 'js/custom-woo-elementor.js',
            ['jquery', 'elementor-frontend'],
            CUSTOM_WOO_ELEMENTOR_VERSION,
            true
        );

        // Localize script
        wp_localize_script('custom-woo-elementor-js', 'customWooElementor', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom_woo_elementor_nonce'),
            'loading_text' => __('Loading...', 'custom-woo-elementor'),
            'error_text' => __('Something went wrong. Please try again.', 'custom-woo-elementor'),
            'added_to_cart' => __('Added to cart!', 'custom-woo-elementor'),
            'checkout_url' => wc_get_checkout_url(),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'currency_position' => get_option('woocommerce_currency_pos'),
            'price_decimal_sep' => wc_get_price_decimal_separator(),
            'price_thousand_sep' => wc_get_price_thousand_separator(),
            'price_decimals' => wc_get_price_decimals()
        ]);
    }

    /**
     * Register widget styles
     */
    public function widget_styles() {
        wp_register_style(
            'custom-woo-elementor-css',
            CUSTOM_WOO_ELEMENTOR_ASSETS . 'css/custom-woo-elementor.css',
            [],
            CUSTOM_WOO_ELEMENTOR_VERSION
        );
    }

    /**
     * AJAX handler for add to cart with variations
     */
    public function ajax_add_to_cart_variation() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'custom_woo_elementor_nonce')) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'custom-woo-elementor')
            ]);
        }

        $product_id = intval($_POST['product_id']);
        $variation_id = intval($_POST['variation_id']);
        $quantity = intval($_POST['quantity']);
        $variation_data = isset($_POST['variation_data']) ? $_POST['variation_data'] : [];
        
        // Sanitize variation data
        if (is_array($variation_data)) {
            $variation_data = array_map('sanitize_text_field', $variation_data);
        }

        if (!$product_id || !$variation_id) {
            wp_send_json_error([
                'message' => __('Invalid product or variation.', 'custom-woo-elementor')
            ]);
        }
        
        // Validate product exists and is purchasable
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_purchasable()) {
            wp_send_json_error([
                'message' => __('This product cannot be purchased.', 'custom-woo-elementor')
            ]);
        }
        
        // Validate variation if it's a variable product
        if ($variation_id && $product->is_type('variable')) {
            $variation = wc_get_product($variation_id);
            if (!$variation || !$variation->is_purchasable()) {
                wp_send_json_error([
                    'message' => __('This variation cannot be purchased.', 'custom-woo-elementor')
                ]);
            }
        }

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

        if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data)) {
            // Get updated cart data
            $cart_count = WC()->cart->get_cart_contents_count();
            $cart_total = WC()->cart->get_cart_total();
            
            wp_send_json_success([
                'message' => __('Product added to cart successfully!', 'custom-woo-elementor'),
                'cart_count' => $cart_count,
                'cart_total' => $cart_total,
                'fragments' => apply_filters('woocommerce_add_to_cart_fragments', [])
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
    public function ajax_buy_now_variation() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'custom_woo_elementor_nonce')) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'custom-woo-elementor')
            ]);
        }

        $product_id = intval($_POST['product_id']);
        $variation_id = intval($_POST['variation_id']);
        $quantity = intval($_POST['quantity']);
        $variation_data = isset($_POST['variation_data']) ? $_POST['variation_data'] : [];
        
        // Sanitize variation data
        if (is_array($variation_data)) {
            $variation_data = array_map('sanitize_text_field', $variation_data);
        }

        if (!$product_id || !$variation_id) {
            wp_send_json_error([
                'message' => __('Invalid product or variation.', 'custom-woo-elementor')
            ]);
        }
        
        // Validate product exists and is purchasable
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_purchasable()) {
            wp_send_json_error([
                'message' => __('This product cannot be purchased.', 'custom-woo-elementor')
            ]);
        }

        // Clear cart first
        WC()->cart->empty_cart();

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

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
     * Add custom product fields in admin
     */
    public function add_custom_product_fields() {
        global $woocommerce, $post;

        echo '<div class="options_group">';
        
        echo '<h3>' . __('Custom Product Display Settings', 'custom-woo-elementor') . '</h3>';

        // Custom weight display field
        woocommerce_wp_text_input([
            'id' => '_custom_weight_display',
            'label' => __('Custom Weight Display', 'custom-woo-elementor'),
            'placeholder' => 'e.g., 325g, 500ml, etc.',
            'desc_tip' => 'true',
            'description' => __('Custom weight/size text to display on frontend', 'custom-woo-elementor')
        ]);

        // Product tags/badges
        woocommerce_wp_text_input([
            'id' => '_product_badges',
            'label' => __('Product Badges', 'custom-woo-elementor'),
            'placeholder' => 'Medium Spicy, Jain Friendly, Mustard Oil',
            'desc_tip' => 'true',
            'description' => __('Comma separated badges to display (e.g., Medium Spicy, Jain Friendly)', 'custom-woo-elementor')
        ]);

        // Additional badge styles
        woocommerce_wp_select([
            'id' => '_badge_style',
            'label' => __('Badge Style', 'custom-woo-elementor'),
            'options' => [
                'yellow' => __('Yellow', 'custom-woo-elementor'),
                'green' => __('Green', 'custom-woo-elementor'),
                'orange' => __('Orange', 'custom-woo-elementor'),
                'red' => __('Red', 'custom-woo-elementor'),
                'blue' => __('Blue', 'custom-woo-elementor')
            ]
        ]);

        echo '</div>';
    }
        
        // Product priority for sorting
        woocommerce_wp_text_input([
            'id' => '_custom_product_priority',
            'label' => __('Display Priority', 'custom-woo-elementor'),
            'placeholder' => '0',
            'type' => 'number',
            'desc_tip' => 'true',
            'description' => __('Higher numbers appear first when sorting by priority', 'custom-woo-elementor')
        ]);

    /**
     * Save custom product fields
     */
    public function save_custom_product_fields($post_id) {
        // Verify nonce for security
        if (!isset($_POST['woocommerce_meta_nonce']) || !wp_verify_nonce($_POST['woocommerce_meta_nonce'], 'woocommerce_save_data')) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $custom_weight = isset($_POST['_custom_weight_display']) ? sanitize_text_field($_POST['_custom_weight_display']) : '';
        if (!empty($custom_weight)) {
            update_post_meta($post_id, '_custom_weight_display', $custom_weight);
        } else {
            delete_post_meta($post_id, '_custom_weight_display');
        }

        $product_badges = isset($_POST['_product_badges']) ? sanitize_textarea_field($_POST['_product_badges']) : '';
        if (!empty($product_badges)) {
            update_post_meta($post_id, '_product_badges', $product_badges);
        } else {
            delete_post_meta($post_id, '_product_badges');
        }

        $badge_style = isset($_POST['_badge_style']) ? sanitize_text_field($_POST['_badge_style']) : '';
        if (!empty($badge_style)) {
            update_post_meta($post_id, '_badge_style', $badge_style);
        } else {
            delete_post_meta($post_id, '_badge_style');
        }
        
        $product_priority = isset($_POST['_custom_product_priority']) ? intval($_POST['_custom_product_priority']) : 0;
        if ($product_priority > 0) {
            update_post_meta($post_id, '_custom_product_priority', $product_priority);
        } else {
            delete_post_meta($post_id, '_custom_product_priority');
        }
    }
    
    /**
     * Declare WooCommerce HPOS compatibility
     */
    public function declare_wc_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        }
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
        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }
}
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_woocommerce']);
            return;
        }
        
        // Declare WooCommerce compatibility
        add_action('before_woocommerce_init', [$this, 'declare_wc_compatibility']);

        // Add Plugin actions
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets']);
        add_action('elementor/controls/controls_registered', [$this, 'init_controls']);
        // Check for required Elementor version
        // Register widget scripts
        add_action('elementor/frontend/after_register_scripts', [$this, 'widget_scripts']);
        add_action('elementor/frontend/after_register_styles', [$this, 'widget_styles']);
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
        // Add AJAX handlers
        add_action('wp_ajax_add_to_cart_variation', [$this, 'ajax_add_to_cart_variation']);
        add_action('wp_ajax_nopriv_add_to_cart_variation', [$this, 'ajax_add_to_cart_variation']);
        add_action('wp_ajax_buy_now_variation', [$this, 'ajax_buy_now_variation']);
        add_action('wp_ajax_nopriv_buy_now_variation', [$this, 'ajax_buy_now_variation']);
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
        // Add custom product fields
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_product_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_custom_product_fields']);
    }
            return;
        }
Custom_Woo_Elementor_Plugin::instance();