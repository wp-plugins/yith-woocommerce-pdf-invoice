<?php
/*
Plugin Name: YITH WooCommerce PDF Invoice
Plugin URI: http://yithemes.com
Description: Generate PDF invoices for WooCommerce orders. You can customize how invoice are generated and how the invoice template looks like.
Author: Yithemes
Text Domain: ywpi
Version: 1.0.0
Author URI: http://yithemes.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'WC' ) ) {
	return;
}

//  Stop activation if the premium version of the same plugin is still active
if ( defined( 'YITH_YWPI_VERSION' ) ) {
	return;
}

if ( ! defined( 'YITH_YWPI_PLUGIN_STARTING' ) ) {
	define( 'YITH_YWPI_PLUGIN_STARTING', '1' );
}

if ( ! defined( 'YITH_YWPI_FREE_INIT' ) ) {
	define( 'YITH_YWPI_FREE_INIT', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'YITH_YWPI_VERSION' ) ) {
	define( 'YITH_YWPI_VERSION', '1.0.0' );
}

if ( ! defined( 'YITH_YWPI_FILE' ) ) {
	define( 'YITH_YWPI_FILE', __FILE__ );
}

if ( ! defined( 'YITH_YWPI_DIR' ) ) {
	define( 'YITH_YWPI_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'YITH_YWPI_URL' ) ) {
	define( 'YITH_YWPI_URL', plugins_url( '/', __FILE__ ) );
}

if ( ! defined( 'YITH_YWPI_ASSETS_URL' ) ) {
	define( 'YITH_YWPI_ASSETS_URL', YITH_YWPI_URL . 'assets' );
}

if ( ! defined( 'YITH_YWPI_TEMPLATE_DIR' ) ) {
	define( 'YITH_YWPI_TEMPLATE_DIR', YITH_YWPI_DIR . 'templates' );
}

if ( ! defined( 'YITH_YWPI_INVOICE_TEMPLATE_URL' ) ) {
	define( 'YITH_YWPI_INVOICE_TEMPLATE_URL', YITH_YWPI_URL . 'templates/invoice/' );
}

if ( ! defined( 'YITH_YWPI_INVOICE_TEMPLATE_DIR' ) ) {
	define( 'YITH_YWPI_INVOICE_TEMPLATE_DIR', YITH_YWPI_DIR . '/templates/invoice/' );
}

if ( ! defined( 'YITH_YWPI_ASSETS_IMAGES_URL' ) ) {
	define( 'YITH_YWPI_ASSETS_IMAGES_URL', YITH_YWPI_ASSETS_URL . '/images/' );
}

if ( ! defined( 'YITH_YWPI_DOMPDF_DIR' ) ) {
	define( 'YITH_YWPI_DOMPDF_DIR', YITH_YWPI_DIR . '/lib/dompdf/' );
}

$wp_upload_dir = wp_upload_dir();

if ( ! defined( 'YITH_YWPI_INVOICE_DIR' ) ) {
	define( 'YITH_YWPI_INVOICE_DIR', $wp_upload_dir['basedir'] . '/ywpi-pdf-invoice/' );
}

if ( ! defined( 'YITH_YWPI_INVOICE_URL' ) ) {
	define( 'YITH_YWPI_INVOICE_URL', $wp_upload_dir['baseurl'] . '/ywpi-pdf-invoice/' );
}


/* Load YWPI text domain */
load_plugin_textdomain( 'ywpi', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

require_once( YITH_YWPI_DIR . 'class.yith-woocommerce-pdf-invoice.php' );
require_once( YITH_YWPI_DIR . 'class.yith-invoice.php' );
require_once( YITH_YWPI_DIR . 'functions.php' );

$YWPI_Instance = new YITH_WooCommerce_Pdf_Invoice();