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
            'checkout_url' => wc_get_checkout_url()
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
        check_ajax_referer('custom_woo_elementor_nonce', 'nonce');

        $product_id = intval($_POST['product_id']);
        $variation_id = intval($_POST['variation_id']);
        $quantity = intval($_POST['quantity']);
        $variation_data = $_POST['variation_data'];

        if (!$product_id || !$variation_id) {
            wp_die();
        }

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

        if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation_data)) {
            wp_send_json_success([
                'message' => __('Product added to cart successfully!', 'custom-woo-elementor'),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total()
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
        check_ajax_referer('custom_woo_elementor_nonce', 'nonce');

        $product_id = intval($_POST['product_id']);
        $variation_id = intval($_POST['variation_id']);
        $quantity = intval($_POST['quantity']);
        $variation_data = $_POST['variation_data'];

        if (!$product_id || !$variation_id) {
            wp_die();
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

    /**
     * Save custom product fields
     */
    public function save_custom_product_fields($post_id) {
        $custom_weight = $_POST['_custom_weight_display'];
        if (!empty($custom_weight)) {
            update_post_meta($post_id, '_custom_weight_display', esc_attr($custom_weight));
        }

        $product_badges = $_POST['_product_badges'];
        if (!empty($product_badges)) {
            update_post_meta($post_id, '_product_badges', esc_attr($product_badges));
        }

        $badge_style = $_POST['_badge_style'];
        if (!empty($badge_style)) {
            update_post_meta($post_id, '_badge_style', esc_attr($badge_style));
        }
    }
}

Custom_Woo_Elementor_Plugin::instance();