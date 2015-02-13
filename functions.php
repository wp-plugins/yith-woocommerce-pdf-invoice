<?php


//region some constant values used for url argument vars
if ( ! defined( 'YITH_YWPI_CREATE_INVOICE_ARG_NAME' ) ) {
	define( 'YITH_YWPI_CREATE_INVOICE_ARG_NAME', 'create-invoice' );
}

if ( ! defined( 'YITH_YWPI_VIEW_INVOICE_ARG_NAME' ) ) {
	define( 'YITH_YWPI_VIEW_INVOICE_ARG_NAME', 'view-invoice' );
}

if ( ! defined( 'YITH_YWPI_RESET_INVOICE_ARG_NAME' ) ) {
	define( 'YITH_YWPI_RESET_INVOICE_ARG_NAME', 'reset-invoice' );
}

if ( ! defined( 'YITH_YWPI_NONCE_ARG_NAME' ) ) {
	define( 'YITH_YWPI_NONCE_ARG_NAME', 'check' );
}

//endregion
function ywpi_get_query_args_list() {
	return array(
		YITH_YWPI_CREATE_INVOICE_ARG_NAME,
		YITH_YWPI_VIEW_INVOICE_ARG_NAME,
		YITH_YWPI_RESET_INVOICE_ARG_NAME,
		YITH_YWPI_NONCE_ARG_NAME
	);
}

function ywpi_action_name_for_nonce_checking( $invoice ) {
	return 'ywpi_action_' . $invoice->has_invoice . $invoice->order->order_key;
}

/*
 * Build an action link for a specific YITH-WooCommerce-PDF-Invoice action, resetting others query args presented for current url and
 * specified by $ywpi_query_arg_array array.
 */
function ywpi_invoice_nonce_url( $arg_name, $invoice ) {
	$action_url   = add_query_arg( $arg_name, $invoice->order->id, remove_query_arg( ywpi_get_query_args_list() ) );
	$complete_url = wp_nonce_url( $action_url, ywpi_action_name_for_nonce_checking( $invoice ), YITH_YWPI_NONCE_ARG_NAME);
	return $complete_url;
}

/**
 * Verify nonce on an url asking to do an action over an invoice
 *
 * @param $order_id the order id of the invoice
 *
 * @return bool Nonce validated
 */
function ywpi_invoice_nonce_check( $invoice ) {
//  check for nounce value
	if ( ! check_admin_referer( ywpi_action_name_for_nonce_checking( $invoice ), YITH_YWPI_NONCE_ARG_NAME ) ) {
		return false;
	}
	return true;
}
