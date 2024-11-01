<?php

/**
 * WC_Stan_Payment_Gateway_API Implements apis functionalities
 *
 * Communicates with Stan API.
 *
 * @since      1.0.0
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class WC_Stan_Payment_Gateway_API {

    const API_URL = 'https://api.stan-app.fr';

	private static $_secret_key = '';
	private static $_client_id = '';

	/**
	 * Set live or test secret key.
	 *
	 * @since 1.3.0
	 */
	public static function set_secret_key( $secret_key ) {
        self::$_secret_key = $secret_key;
	}

	/**
	 * Set client ID.
	 *
	 * @since 1.0.0
	 */
	public static function set_client_id( $client_id ) {
		self::$_client_id = $client_id;
	}

    /**
	 * Sets and gets secret key.
	 *
     * @since 1.3.0
     * 
	 * @return string The secret key
	 */
	public static function get_secret_key() {
		if ( ! self::$_secret_key ) {
            $options = get_option( 'woocommerce_stan_settings' );

			if ( isset( $options['testmode'], $options['secret_key'], $options['test_secret_key'] ) ) {
				self::set_secret_key( 'yes' === $options['testmode'] ? $options['test_secret_key'] : $options['secret_key'] );
			}
		}
		return self::$_secret_key;
	}

    /**
	 * Sets and gets client id.
	 *
     * @since 1.3.0
     * 
	 * @return string The client id
	 */
	public static function get_client_id() {
		if ( ! self::$_client_id ) {
            $options = get_option( 'woocommerce_stan_settings' );

			if ( isset( $options['testmode'], $options['client_id'], $options['test_client_id'] ) ) {
				self::set_client_id( 'yes' === $options['testmode'] ? $options['test_client_id'] : $options['client_id'] );
			}
		}
		return self::$_client_id;
	}


	/**
	 * Generates the headers to pass to API request.
	 *
	 * @since 1.0.0
	 */
	private static function get_headers() {
		return apply_filters(
			'woocommerce_stan_payment_gateway_req_headers',
			array(
				'Authorization' => 'Basic ' . base64_encode( self::get_client_id() . ':' . self::get_secret_key() ),
			)
		);
	}

	/**
	 * Send a request to Stan's API
	 *
	 * @since 1.0.0
     * @param string $uri
     * @param string $method
	 * @param array $request
     * @param array $custom_headers
	 * @return array
	 */
	public static function request( $uri, $method = 'GET', $request = array(), $custom_headers = array() ) {
		$current_headers = self::get_headers();

        $headers = array_merge( $current_headers, $custom_headers );

		WC_Stan_Payment_Gateway_Logger::log( "Request {$method} {$uri}" );

		switch ( $method ) {
			case 'GET':
				$response = wp_safe_remote_get(
					$uri,
					array(
						'method' => 'GET',
						'headers' => $headers,
						'timeout' => 100,
					)
				);
                break;
            case 'PUT':
			case 'POST':
            case 'PATCH':
				$headers['Content-Type'] = 'application/json; charset=utf-8';
				$response = wp_safe_remote_request(
					$uri,
					array(
						'method' => $method,
						'headers' => $headers,
						'body' => json_encode( $request ),
						'timeout' => 100,
					)
				);
				break;
        }

        if ( is_wp_error( $response ) ) {
            throw new Exception( 'Wordpress error. Response : ' . json_encode( $response ) );
        }

        $statusCode = $response['response']['code'];
		if ( empty( $response['body'] ) && $statusCode != 204 ) {
			throw new Exception( 'Response body is missing' );
		}

		if ( $statusCode >= 400) {
			throw new Exception( sprintf( "There was a problem connecting to the Stan API. Code %s", $statusCode ) );
		}

        if ( ! empty( $response['body'] ) ) {
            return json_decode( wp_remote_retrieve_body( $response ) );
        }

        return array();
	}

	/**
	 * Returns the good URL.
	 *
	 * @since 1.0.0
	 * @return string
	 * @access private
	 */
	public static function GetAPIURL() {
		return self::API_URL;
	}

}
