<?php
/*
Plugin Name: Edd Payment Locations
Description: Adds user locations to Payment History page and Payment Details.
Version: 0.1.0
Author: Theme Blvd
Author URI: http://themeblvd.com
License: GPL2

    Copyright 2017  Theme Blvd

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

/**
 * Setup EDD Payment Locations plugin.
 *
 * @since 0.1.0
 */
class EDD_Payment_Locations {

	/**
	 * Only instance of object.
	 *
	 * @var EDD_Payment_Locations
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return EDD_Payment_Locations A single instance of this class.
	 *
	 * @since 0.1.0
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {

			self::$instance = new self;

		}

		return self::$instance;

	}

	/**
	 * Run plugin.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {

		/**
		 * Add the "Location" column to the Payment History page.
		 */
		add_filter( 'edd_payments_table_columns', array( $this, 'columns' ) ) ;

		/**
		 * Filter in the values to display in the "Location" column,
		 * for each payment.
		 */
		add_filter( 'edd_payments_table_column', array( $this, 'column_val' ), 10, 3 );

		/**
		 * Display the payment location in the "Payment Meta" box
		 * on the payment details page.
		 */
		add_action( 'edd_view_order_details_payment_meta_after', array( $this, 'details' ) );

	}

	/**
	 * Filter columns for EDD payment history table.
	 *
	 * @since 0.1.0
	 */
	public function columns( $columns ) {

		$columns['location'] = __( 'Location', 'edd-payment-location' );

		return $columns;

	}

	/**
	 * Disolay value in column.
	 *
	 * @since 0.1.0
	 */
	public function column_val( $val, $payment_id, $column_name ) {

		if ( 'location' === $column_name ) {

			$val = get_post_meta( $payment_id, '_edd_payment_user_location', true );

			if ( ! $val ) {

				$val = get_post_meta( $payment_id, '_edd_payment_user_ip', true );
				$val = $this->get_city( $val );

				update_post_meta( $payment_id, '_edd_payment_user_location', $val );

			}

		}

		return $val;

	}

	/**
	 * Display location in Payment Meta box.
	 *
	 * @since 0.1.0
	 */
	public function details( $payment_id ) {

		$location = get_post_meta( $payment_id, '_edd_payment_user_location', true );

		if ( ! $location ) {

			$location = get_post_meta( $payment_id, '_edd_payment_user_ip', true );
			$location = $this->get_city( $location );

			update_post_meta( $payment_id, '_edd_payment_user_location', $location );

		}

		?>
		<div class="edd-order-location edd-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Location:', 'edd-payment-location' ); ?></span>&nbsp;
				<span><?php echo esc_attr( $location ); ?></span>
			</p>
		</div>
		<?php

	}

	/**
	 * Get city from IP.
	 *
	 * @since 0.1.0
	 */
	private function get_city( $ip ) {

		$location = 'UNKNOWN';
		$city = '';
		$state = '';

		$response = wp_remote_get( esc_url( "http://ip-api.com/json/{$ip}" ) );

		if ( is_array( $response ) ) {

			$info = json_decode( $response['body'] );

		}

		if ( ! empty( $info->city ) ) {

			$location = $info->city;

			if ( ! empty( $info->regionName ) ) {

				$location .= ', ' . $info->regionName;

			}
		}

		return esc_html( $location );

	}

}

EDD_Payment_Locations::get_instance();
