<?php

/**
 * WC_Stan_Payment_Gateway_API_Wrapper Stan apy API client
 *
 * Communicates with Stan API.
 *
 * @since      1.0.0
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class WC_Stan_Payment_Gateway_API_Wrapper {

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $testmode = false ) {
        $this->testmode = $testmode;
    }

    /**
	 * Create a payment for a client
	 * Doc : https://doc.stan-app.fr/#create-a-payment-invoice
     * 
     * @since 1.3.0
     * @param string $order_id ID of the order
     * @param int $amount Amount for the payment, must be a integer (1,23 should be 123)
     * 
	 * @return array JSON response
	 */
    public function create_payment( $order_id, $amount, $return_url, $customer_id = null ) {
        $payload = array(
            "order_id" => $order_id,
            "amount" => $amount,
            "return_url" => $return_url
        );

        if ( ! is_null( $customer_id ) ) {
            $payload["customer_id"] = $customer_id;
        }

        try {
            $payment = WC_Stan_Payment_Gateway_API::request( WC_Stan_Payment_Gateway_API::GetAPIURL() . '/v1/payments', 'POST', $payload );
            return $payment;
        } catch (\Exception $e) {
            throw new WC_Stan_Payment_Gateway_Exception( 'Error raised during payment creation, reason: ' . "\n" . $e, __( 'Il y a eu un soucis lors de la création du paiement', 'woo-stan-payment-gateway' ) );
        }
    }

    /**
	 * Create a payment for a client
	 * Doc : https://doc.stan-app.fr/#create-a-customer
     * 
     * @since 2.0.0
     * @param WC_Order $order The order to create a customer in Stan
     * 
	 * @return array JSON response
	 */
    public function create_customer( $order ) {
        $name = $order->get_billing_company();
        if ( isset( $name ) ) {
            $name = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
        }
        $payload = array(
            "name" => $name,
            "email" => $order->get_billing_email(),
            "phone_number" => $order->get_billing_phone(),
            "address" => array(
                "firstname" => $order->get_billing_first_name(),
                "lastname" => $order->get_billing_last_name(),
                "street_address" => $order->get_billing_address_1(),
                "street_address_line2" => $order->get_billing_address_2(),
                "locality" => $order->get_billing_city(),
                "zip_code" => $order->get_billing_postcode(),
                "country" => $order->get_billing_country(),
                "region" => $order->get_billing_state()
            )
        );

        try {
            $customer = WC_Stan_Payment_Gateway_API::request( WC_Stan_Payment_Gateway_API::GetAPIURL() . '/v1/customers', 'POST', $payload );
            return $customer;
        } catch (\Exception $e) {
            throw new WC_Stan_Payment_Gateway_Exception( 'Error raised during customer creation, reason: ' . "\n" . $e, __( 'Il y a eu un soucis lors de la création du paiement', 'woo-stan-payment-gateway' ) );
        }
    }

    /**
	 * Get payment by ID
	 * Doc : https://doc.stan-app.fr/#get-a-payment
     * 
     * @since 1.3.0
     * @param string $payment_id ID of the payment
     * 
	 * @return array JSON response
	 */
    public function get_payment( $payment_id ) {
        try {
            $payment = WC_Stan_Payment_Gateway_API::request( WC_Stan_Payment_Gateway_API::GetAPIURL() . '/v1/payments/' . $payment_id );
            return $payment;
        } catch(\Exception $e) {
            WC_Stan_Payment_Gateway_Logger::log( 'Error raised during get payment request, reason: ' . "\n" . $e);
            throw new WC_Stan_Payment_Gateway_Exception( 'Error raised during get payment request, reason: ' . '\n' . $e );
        }
    }
    
    /**
	 * Update the account infos.
	 *
     * @since 1.0.0
     * @param array $payload Account infos to update
     * 
	 * @return array JSON response
	 */
    public function update_account_infos( $payload ) {
        try {
            $res = WC_Stan_Payment_Gateway_API::request( WC_Stan_Payment_Gateway_API::GetAPIURL() . '/v1/accounts', 'PATCH', $payload );
            return $res;
        } catch (\Exception $e) {
            WC_Stan_Payment_Gateway_Logger::log( 'Error raised during settings update, reason: ' . "\n" . $e);
            throw new WC_Stan_Payment_Gateway_Exception( 'Error raised during settings update, reason: ' . '\n' . $e );
        }
    }

    /**
	 * Update the account API clients redirection infos.
	 *
     * @since 1.0.0
     * 
	 * @return array JSON response
	 */
    public function update_account_client() {
        $url = get_site_url();
        $order_redirect_url = $url . '/?wc-api=WC_Stan_Payment';
        $oauth_redirect_url = $url . '/stan-easy-connect-authorize';

        $payload = array( 
            "payment_webhook_url" => $order_redirect_url,
            "oauth_redirect_url" => $oauth_redirect_url
        );

        try {
            $res = WC_Stan_Payment_Gateway_API::request( WC_Stan_Payment_Gateway_API::GetAPIURL() . '/v1/apis', 'PUT', $payload );
            return $res;
        } catch (\Exception $e) {
            WC_Stan_Payment_Gateway_Logger::log( 'Error raised during settings update, reason: ' . "\n" . $e);
            throw new WC_Stan_Payment_Gateway_Exception( 'Error raised during settings update, reason: ' . '\n' . $e );
        }
    }

    /**
	 * Get account infos.
	 *
     * @since 2.1.0
     * 
	 * @return array JSON response
	 */
    public function get_account_infos() {
        try {
            $res = WC_Stan_Payment_Gateway_API::request( WC_Stan_Payment_Gateway_API::GetAPIURL() . '/v1/accounts', 'GET', array() );
            return $res;
        } catch(\Exception $e) {
            WC_Stan_Payment_Gateway_Logger::log( 'Error raised during account fetch, reason: ' . "\n" . $e);
            throw new WC_Stan_Payment_Gateway_Exception( 'Error raised during account fetch, reason: ' . "\n" . $e );
        }
    }
}