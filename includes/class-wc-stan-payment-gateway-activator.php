<?php

/**
 * Fired during plugin activation
 *
 * @link       https://compte.stan-app.fr
 * @since      1.0.0
 *
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class WC_Stan_Payment_Gateway_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$url = 'https://account.stan-app.fr/account/pkcg94c5ggj9n4aycr7gnvnmhrkctr/integrations/notify';

		$body = array(
			'website' => site_url(),
			'source' => 'stan-pay',
			'stack' => 'woocommerce',
			'is_active' => true
		);

		$headers = array(
			'Content-Type' => 'application/json',
			'Authorization' => 'ApiKey xjGc42kfJxTZtR4KGeBUnN4H34V5HwBa3U'
		);

		wp_remote_post( $url, array(
			'body' => json_encode( $body ),
			'headers' => $headers
		));
	}

}
