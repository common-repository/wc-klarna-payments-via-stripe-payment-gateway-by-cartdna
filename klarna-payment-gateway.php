<?php

/**
 * Plugin Name: Klarna payments via Stripe Payment Gateway by CartDNA
 * Description: Klarna Payment gateway that will allow you to make your payment with woocommerce.
 * Version: 1.0
 * Author: CartDNA
 * Author URI: https://www.cartdna.com/
 */

//scriptFile.php
if (!defined('ABSPATH')) {
    die;
}

require __DIR__ . '/pluginFiles/klarna.php';
require __DIR__ . '/pluginFiles/installationHooks.php';
require __DIR__ . '/pluginFiles/admin/top-menu.php';
require __DIR__ . '/pluginFiles/admin/kl-config-menu.php';
require __DIR__ . '/pluginFiles/scriptFile.php';
require __DIR__ . '/pluginFiles/shortCode.php';

register_activation_hook(__FILE__, 'activate_klarna_payment_gateway_oganro');

//deactivation
register_deactivation_hook(__FILE__, 'uninstall_klarna_payment_gateway_oganro');

add_action('plugins_loaded', 'klarna_payment_gateway_init', 11);

function klarna_payment_gateway_init()
{
    class WC_klarna_payment_gateway extends WC_Payment_Gateway
    {
        public $clientDomainName;
        public function __construct()
        {
            $plugin_dir = plugin_dir_url(__FILE__);
            /**
             * Following detials will define the gateway and make it visible in the payment area
             */
            $this->id                 = 'klarna_payment_gateway_method';
            $this->icon               = apply_filters('woocommerce_frist_payment_icon', '' . $plugin_dir . 'image/klarna-big-new.png');
            $this->has_fields         = false;
            $this->method_title       = __('Klarna Payment Gateway', 'wc-klarna-payment-gateway');
            $this->method_description = __('One of the fastest ways to pay your orders', 'wc-klarna-payment-gateway');
            $this->clientDomainName = $_SERVER['HTTP_HOST'];



            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            //these are the values stored in the setting page(save chances)	  
            $this->title                     = $this->get_option('title');
            $this->description             = $this->get_option('description');
            $this->checkout_msg            = $this->get_option('checkout_msg');
            $this->klarna_pay_now       = $this->get_option('klarna_pay_now');
            $this->klarna_pay_later       = $this->get_option('klarna_pay_later');
            $this->klarna_slice_it       = $this->get_option('klarna_slice_it');

            add_action('init', array(&$this, 'check_payme_gateway_transaction_response'));
            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            //add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
            // Customer Emails
            add_filter('woocommerce_ship_to_different_address_checked', '__return_true');
            //add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        }
        function init_form_fields()
        {

            $this->form_fields = array(
                'enabled'     => array(
                    'title'         => __('Enable/Disable', 'wc-klarna-payment-gateway'),
                    'type'             => 'checkbox',
                    'label'         => __('Enable Klarna Payment Gateway.', 'wc-klarna-payment-gateway'),
                    'default'         => 'no'
                ),

                'title'     => array(
                    'title'         => __('Title:', 'wc-klarna-payment-gateway'),
                    'type'            => 'text',
                    'description'     => __('This controls the title which the user sees during checkout.', 'wc-klarna-payment-gateway'),
                    'default'         => __('Pay Now, Pay Later, Pay Over Time', 'wc-klarna-payment-gateway')
                ),

                'description' => array(
                    'title'         => __('Description:', 'wc-klarna-payment-gateway'),
                    'type'            => 'textarea',
                    'description'     => __('This controls the description which the user sees during checkout.', 'wc-klarna-payment-gateway'),
                    'default'         => __('Klarna Payment Gateway', 'wc-klarna-payment-gateway')
                ),
                'klarna_pay_now'     => array(
                    'title'         => __('Pay Now', 'wc-klarna-payment-gateway'),
                    'type'             => 'checkbox',
                    'label'         => __('Enable Pay Now payment method.', 'wc-klarna-payment-gateway'),
                    'default'         => 'yes'
                ),
                'klarna_pay_later'     => array(
                    'title'         => __('Pay Later', 'wc-klarna-payment-gateway'),
                    'type'             => 'checkbox',
                    'label'         => __('Enable Pay Later payment method.', 'wc-klarna-payment-gateway'),
                    'default'         => 'no'
                ),
                'klarna_slice_it'     => array(
                    'title'         => __('Slice It', 'wc-klarna-payment-gateway'),
                    'type'             => 'checkbox',
                    'label'         => __('Enable Slice payment method.', 'wc-klarna-payment-gateway'),
                    'default'         => 'no'
                ),
                'checkout_msg' => array(
                    'title'         => __('Checkout Message:', 'wc-klarna-payment-gateway'),
                    'type'            => 'textarea',
                    'description'     => __('Message display when checkout'),
                    'default'         => __('Thank you for your order, please click the button below to pay with the secured Klarana payment gateway.', 'wc-klarna-payment-gateway')
                ),
            );
        }
        function receipt_page($order)
        {
            global $woocommerce;
            $order_details = new WC_Order($order);

            echo $this->generate_ipg_form($order);
        }
        public function generate_ipg_form($order_id)
        {
            global $wpdb;
            global $woocommerce;
            $table_name = $wpdb->prefix . 'klarna_payment_gateway_token_oganro';
            $klarna = new klarna();
            $gatewayCode = $klarna->gatewayCode;
            $order = new WC_Order($order_id);
            $productinfo = "Order $order_id";
            $plugin_dir = plugin_dir_url(__FILE__);
            // $currency_code     = $this->currency_code;
            $curr_symbol     = get_woocommerce_currency();
            $paymentHtml = "";

            $check_token = $wpdb->get_results("SELECT * FROM $table_name WHERE gateway = '" . $gatewayCode . "'");

            if (empty($check_token[0]->token)) {
                print_r("Please use other available payment methods!!!");
                exit();
            } else {
                $mode = $check_token[0]->test_mode;
                if ($mode === 'test') {
                    echo '<div style="padding: 10px;background-color:#f44336;color:white;border-radius:5px;">
                                     You are in a test mode!!
                                 </div>';
                }

                $payload = [
                    "token" => $check_token[0]->token,
                    "testMode" => $mode,
                    "hostName" => $check_token[0]->domain,
                    "orderId" => $order_id,
                    "gatewayToken" => $gatewayCode,
                    "amount" => $order->get_total() * 100,
                    "currency" => $curr_symbol,
                    "first_name" => $order->get_billing_first_name(),
                    "last_name" => $order->get_billing_last_name(),
                    "email" => $order->get_billing_email(),
                    "city" => $order->get_billing_city(),
                    "country" => $order->get_billing_country(),
                    "line1" => $order->get_billing_address_1(),
                    "postal_code" => $order->get_billing_postcode(),
                ];

                $pageName = $klarna->klarnaIndex;
                $form_args_array = array();
                foreach ($payload as $key => $value) {
                    $form_args_array[] = "<input type='hidden' name='" . esc_attr($key) . "' value='" . esc_attr($value) . "'/>";
                }


                if ($this->klarna_pay_now === 'yes') {
                    $paymentHtml .= '
                    <div class="klarna-checkout-payment-option-flex-container">
                    <div>
                        <input id="payment_method_klarna_payment_gateway_method" type="radio" class="input-radio" name="payment_method" value="pay_now">
                        <label for="payment_method_klarna_payment_gateway_method">
                            Pay Now </label>
                    </div>
                    <div><img src="' . esc_url($plugin_dir . "image/klarna-big-new.png") . '" alt="Klarna Payment Gateway" style="width:60%;"></div>
                </div>
                    ';
                }

                if ($this->klarna_pay_later === 'yes') {
                    $paymentHtml .= '
                    <div class="klarna-checkout-payment-option-flex-container">
                    <div>
                        <input id="payment_method_klarna_payment_gateway_method" type="radio" class="input-radio" name="payment_method" value="pay_later">
                        <label for="payment_method_klarna_payment_gateway_method">
                            Pay Later </label>
                    </div>
                    <div><img src="' . esc_url($plugin_dir . "image/klarna-big-new.png") . '" alt="Klarna Payment Gateway" style="width:60%;"></div>
                </div>
                    ';
                }
                if ($this->klarna_slice_it === 'yes') {
                    $paymentHtml .= '
                    <div class="klarna-checkout-payment-option-flex-container">
                    <div>
                        <input id="payment_method_klarna_payment_gateway_method" type="radio" class="input-radio" name="payment_method" value="pay_over_time">
                        <label for="payment_method_klarna_payment_gateway_method">
                        Slice It </label>
                    </div>
                    <div><img src="' . esc_url($plugin_dir . "image/klarna-big-new.png") . '" alt="Klarna Payment Gateway" style="width:60%;"></div>
                </div>
                    ';
                }
                if (empty($paymentHtml)) {
                    $paymentHtml .= '
                    <div class="klarna-checkout-payment-option-flex-container">
                    <div>
                        <input id="payment_method_klarna_payment_gateway_method" type="radio" class="input-radio" name="payment_method" value="pay_now">
                        <label for="payment_method_klarna_payment_gateway_method">
                            Pay Now </label>
                    </div>
                    <div><img src="' . esc_url($plugin_dir . "image/klarna-big-new.png") . '" alt="Klarna Payment Gateway" style="width:60%;"></div>
                </div>
                    ';
                }

                return '</p>
                            <form action="' . esc_url($pageName) . '" method="post">
                            ' . implode('', $form_args_array) . ' 
                            <h3>Avilable Payment Methods</h3>
                            <div id="payment" class="klarna-checkout-payment-width">
                            <ul class="wc_payment_methods payment_methods methods">
                            ' . $paymentHtml . '
                            </ul>
                            </div>
                            <br />
                             <p>' . esc_html($this->checkout_msg) . '</p>
                            <input type="submit" class="checkout-button button alt" id="submit_form" value="Make The Payment" /> 
                            <a class="button"  href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel', 'ognro') . '</a>  
                            </form>';
            }
        }

        //Once Customer chose the payment method, and click the place order
        //this function will be executed
        public function process_payment($order_id)
        {

            $order = wc_get_order($order_id);

            return array(
                'result' => 'success', 'redirect' => add_query_arg(
                    'order',
                    $order->id,
                    add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay')))
                )
            );
        }
    }
    function wc_add_klarna_payment_to_gateways($gateways)
    {
        $gateways[] = 'WC_klarna_payment_gateway';
        return $gateways;
    }

    add_filter('woocommerce_payment_gateways', 'wc_add_klarna_payment_to_gateways');
}

add_shortcode('shortcode-klarna-payment-gateway-response', 'klarna_payment_response_oganro');
add_shortcode('shortcode-klarna-payment-gateway-callback', 'klarna_payment_callback_oganro');
add_shortcode('shortcode-klarna-payment-gateway-store-token', 'klarna_gateway_store_token_oganro');

//applying custom pages
add_action('wp_enqueue_scripts', 'klarna_gateway_wpb_hooks_custom_css');
// add_action('wp_enqueue_scripts', 'klarna_gateway_wpb_hooks_js_cripts');
add_action('admin_enqueue_scripts', 'klarna_enqueue_admin_scripts');

//verify client ajax
add_action('wp_ajax_verifyKlarnaClientSecurityToken', 'verifyKlarnaClientSecurityToken_callback');
add_action('wp_ajax_nopriv_verifyKlarnaClientSecurityToken', 'verifyKlarnaClientSecurityToken_callback');

//footer js
add_action('wp_footer', 'klarna_gateway_footer_js_scripts');
//store merchant details
add_action('wp_ajax_storeKlarnaConfigData', 'storeKlarnaConfigData_callback');
add_action('wp_ajax_nopriv_storeKlarnaConfigData', 'storeKlarnaConfigData_callback');

//show saved config data
add_action('wp_ajax_showKlarnaClientConfigRecords', 'showKlarnaClientConfigRecords_callback');
add_action('wp_ajax_nopriv_showKlarnaClientConfigRecords', 'showKlarnaClientConfigRecords_callback');

//update config data
add_action('wp_ajax_updateKlarnaClientConfigRecords', 'updateKlarnaClientConfigRecords_callback');
add_action('wp_ajax_nopriv_updateKlarnaClientConfigRecords', 'updateKlarnaClientConfigRecords_callback');

//woocommerce hooks
// add_action('woocommerce_review_order_after_payment', 'klana_show_test_mode_checkout');
