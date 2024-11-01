<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helpers
 *
 * @since 2.0.0
 */
class WC_Stan_Payment_Gateway_Helper {

	/**
	 * Get payment amount
     * 
     * @since 2.0.0
	 *
	 * @param float  $total Amount due.
	 * @param string $currency Accepted currency.
	 *
	 * @return float|int
	 */
	public static function format_amount( $total, $currency = '' ) {
		if ( ! $currency ) {
			$currency = get_woocommerce_currency();
		}

        return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) );
	}

	/**
	 * Checks minimum order value authorized
     * 
     * @since 2.0.0
     * 
     * @return int The minimum amount allowed (-1 means no minimum)
	 */
	public static function get_minimum_amount() {
        // TODO should check for every currencies
		return 100;
	}

    /**
	 * Checks maximum order value authorized
     * 
     * @since 2.0.0
     * 
     * @return int The maximum amount allowed (-1 means no maximum)
	 */
    public static function get_maximum_amount() {
        return -1;
    }
}
