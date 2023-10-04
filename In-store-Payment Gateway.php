<?php
/**
 * Plugin Name: In-store Payment Gateway
 * Description: Dodaje opcję płatności "Płatność przy odbiorze w sklepie stacjonarnym" do sklepu WooCommerce.
 * Version: 1.0
 * Author: TraviLabs
 * License: GPL-2.0+
 */

// Prevent direct file access
defined('ABSPATH') || exit;

// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // Create the custom payment gateway
    add_action('plugins_loaded', 'init_in_store_payment_gateway', 0);
    function init_in_store_payment_gateway() {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        class WC_Gateway_In_Store_Payment extends WC_Payment_Gateway {
            public function __construct() {
                $this->id = 'in_store_payment';
                $this->icon = '';
                $this->has_fields = false;
                $this->method_title = __('Płatność przy odbiorze w sklepie stacjonarnym', 'woocommerce');
                $this->method_description = __('Pozwala na płatność przy odbiorze w sklepie stacjonarnym.', 'woocommerce');

                // Load the settings
                $this->init_form_fields();
                $this->init_settings();

                // Define user set variables
                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');

                // Actions
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            }

            public function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Włącz płatność przy odbiorze w sklepie stacjonarnym', 'woocommerce'),
                        'default' => 'yes',
                    ),
                    'title' => array(
                        'title' => __('Title', 'woocommerce'),
                        'type' => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                        'default' => __('Płatność przy odbiorze w sklepie stacjonarnym', 'woocommerce'),
                    ),
                    'description' => array(
                        'title' => __('Customer Message', 'woocommerce'),
                        'type' => 'textarea',
                        'default' => __('Zapłać przy odbiorze w naszym sklepie stacjonarnym.', 'woocommerce'),
                    ),
                );
            }

            public function process_payment($order_id) {
                $order = wc_get_order($order_id);

                // Mark as processing (payment won't be taken until delivery)
                $order->update_status('processing', __('Płatność przy odbiorze w sklepie stacjonarnym', 'woocommerce'));

                // Reduce stock levels
                $order->reduce_order_stock();

                // Remove cart
                WC()->cart->empty_cart();

                // Return thankyou redirect
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),

                );
            }
            }
            }
               
// Add the custom payment gateway to WooCommerce
add_filter('woocommerce_payment_gateways', 'add_in_store_payment_gateway');
function add_in_store_payment_gateway($methods) {
    $methods[] = 'WC_Gateway_In_Store_Payment';
    return $methods;
}
}