<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://compte.stan-app.fr
 * @since      1.0.0
 *
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class WC_Stan_Payment_Gateway_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woo-stan-payment-gateway',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
