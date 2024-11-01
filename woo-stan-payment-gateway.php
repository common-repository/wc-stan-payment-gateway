<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://compte.stan-app.fr
 * @since             1.0.0
 * @package           WC_Stan_Payment_Gateway
 *
 * @wordpress-plugin
 * Plugin Name:       Stan, la solution de paiement sans carte
 * Plugin URI:        https://compte.stan-app.fr
 * Description:       La nouvelle solution de paiement en ligne sans carte. Gagnez et fidélisez vos clients avec le paiement sans carte sécurisé. Rendez l'expérience de votre ecommerce unique et innovante !
 * Version:           2.7.10
 * Author:            Brightweb
 * Author URI:        https://stan-app.fr
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       wc-stan-payment-gateway
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WC_Stan_Payment_Gateway_VERSION', '2.7.6' );
define( 'WC_STAN_PAYMENT_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

/**
 * WooCommerce fallback notice.
 *
 * @since 1.0.0
 * @return string
 */
function woocommerce_stan_missing_wc_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Stan Pay requires WooCommerce to be installed and active. You can download %s here.', 'wc-stan-payment-gateway' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

/**
 * WooCommerce not supported fallback notice.
 *
 * @since 1.0.0
 * @return string
 */
function woocommerce_stan_wc_not_supported() {
	/* translators: $1. Minimum WooCommerce version. $2. Current WooCommerce version. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'Stan Pay requires WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'wc-stan-payment-gateway' ), WC_STRIPE_MIN_WC_VER, WC_VERSION ) . '</strong></p></div>';
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wc-stan-payment-gateway-activator.php
 */
function activate_WC_Stan_Payment_Gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-stan-payment-gateway-activator.php';
	WC_Stan_Payment_Gateway_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wc-stan-payment-gateway-deactivator.php
 */
function deactivate_WC_Stan_Payment_Gateway() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-stan-payment-gateway-deactivator.php';
	WC_Stan_Payment_Gateway_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_WC_Stan_Payment_Gateway' );
register_deactivation_hook( __FILE__, 'deactivate_WC_Stan_Payment_Gateway' );

add_filter( 'woocommerce_payment_gateways', 'wc_stan_add_gateway_class' );
/**
 * Adds Stan Payment Gateway in WooCommerce
 */
function wc_stan_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Stan_Payment_Gateway';
	return $gateways;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

add_action( 'plugins_loaded', 'run_WC_Stan_Payment_Gateway' );
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_WC_Stan_Payment_Gateway() {
    if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action(
            'admin_notices',
            function() {
                /* translators: 1. URL link. */
                echo '<div class="error"><p><strong>' . sprintf( esc_html__( "Stan Payment nécessite l'extension WooCommerce pour être actif et fonctionnel. Vous pouvez télécharger Woocommerce ici %s.", 'woocommerce-stan-payment-gateway' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
            }
        );

		return;
	}

	include_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-stan-payment-gateway.php';

	$plugin = new WC_Stan_Payment_Gateway();
	$plugin->run();

    $plugin->setupStanConnect();

}

// Handlers multiple names
add_filter( 'plugin_action_links_woo-stan-payment-gateway/woo-stan-payment-gateway.php', 'display_stan_payment_gateway_settings_link' );
add_filter( 'plugin_action_links_woo-stan-pay/woo-stan-payment-gateway.php', 'display_stan_payment_gateway_settings_link' );
add_filter( 'plugin_action_links_wc-stan-payment-gateway/woo-stan-payment-gateway.php', 'display_stan_payment_gateway_settings_link' );
function display_stan_payment_gateway_settings_link( $links ) {
	$url = esc_url( add_query_arg(
		array(
			'page' => 'wc-settings',
			'tab' => 'checkout',
			'section' => 'wc_stan_payment_gateway'
		),
		get_admin_url() . 'admin.php'
	) );

	$settings_link = "<a href='$url'>Configurer</a>";

	array_unshift(
		$links,
		$settings_link
	);

	return $links;
}