<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YITH_Invoice' ) ) {

	/**
	 * Implements features related to an invoice document
	 *
	 * @class Invoice
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_Invoice {

		/**
		 * @var WC_Order
		 */
		public $order;

		/**
		 * @var string
		 */
		private $document_type = 'invoice';

		public $has_invoice = false;

		public $invoice_date;

		private $invoice_number;

		private $invoice_prefix;

		private $invoice_suffix;

		public $invoice_path;

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 * @return void
		 */
		public function __construct( $order_id ) {

			/**
			 * Register this order globally, if $order_id refers to a real order
			 */
			$this->order = wc_get_order( $order_id );
			if ( ! isset( $this->order ) ) {
				return;
			}

			/**
			 *  Fill invoice information from a previous invoice is exists or from general plugin options plus order related data
			 * */
			$this->init_invoice();

			global $YWPI_order;
			$YWPI_order = $this->order;
		}

		/*
		 * Check if an invoice exist for current order and load related data
		 */
		private function init_invoice() {
			$this->has_invoice = get_post_meta( $this->order->id, '_ywpi_invoiced', true );


			if ( $this->has_invoice ) {
				$this->invoice_number = get_post_meta( $this->order->id, '_ywpi_invoice_number', true );
				$this->invoice_prefix = get_post_meta( $this->order->id, '_ywpi_invoice_prefix', true );
				$this->invoice_suffix = get_post_meta( $this->order->id, '_ywpi_invoice_suffix', true );
				$this->invoice_date   = get_post_meta( $this->order->id, '_ywpi_invoice_date', true );
				$this->invoice_path   = get_post_meta( $this->order->id, '_ywpi_invoice_path', true );
			} else {
				$this->invoice_prefix = get_option( 'ywpi_invoice_prefix' );
				$this->invoice_suffix = get_option( 'ywpi_invoice_suffix' );
			}
		}

		public function get_formatted_invoice_number() {
			$formatted_invoice_number = get_option( 'ywpi_invoice_number_format' );

			$formatted_invoice_number = str_replace(
				array( '[prefix]', '[suffix]', '[number]' ),
				array( $this->invoice_prefix, $this->invoice_suffix, $this->invoice_number ),
				$formatted_invoice_number );


			return $formatted_invoice_number;
		}

		public function  get_formatted_invoice_date() {
			$format = get_option( 'ywpi_invoice_date_format' );

			return date( $format, $this->invoice_date );
		}


		/**
		 * Save invoice to wordpress uploads dir, inside the correct directory
		 *
		 */
		private function save_pdf_invoice() {
			$date = getdate( $this->invoice_date );
			$year = $date['year'];

			if ( ! file_exists( YITH_YWPI_INVOICE_DIR ) ) {
				wp_mkdir_p( YITH_YWPI_INVOICE_DIR );
			}

			if ( ! file_exists( YITH_YWPI_INVOICE_DIR . $year ) ) {
				wp_mkdir_p( YITH_YWPI_INVOICE_DIR . $year );
			}
			$pdf_content = $this->generate_template();
			$pdf_path    = YITH_YWPI_INVOICE_DIR . $this->invoice_path;
			file_put_contents( $pdf_path, $pdf_content );
		}

		/**
		 * Generate the template
		 */
		public function generate_template( $type = 'invoice' ) {
			ob_start();
			wc_get_template( 'template.php', null, YITH_YWPI_INVOICE_TEMPLATE_DIR, YITH_YWPI_INVOICE_TEMPLATE_DIR );
			//exit;

			$html = ob_get_contents();
			ob_end_clean();


			require_once( YITH_YWPI_DOMPDF_DIR . "dompdf_config.inc.php" );

			$dompdf = new DOMPDF();

			$dompdf->load_html( $html );

			$dompdf->render();

			// The next call will store the entire PDF as a string in $pdf
			$pdf = $dompdf->output();

			return $pdf;
		}

		/**
		 *
		 */
		public function reset() {

			delete_post_meta( $this->order->id, '_ywpi_invoiced' );

			delete_post_meta( $this->order->id, '_ywpi_invoice_number' );
			delete_post_meta( $this->order->id, '_ywpi_invoice_prefix' );
			delete_post_meta( $this->order->id, '_ywpi_invoice_suffix' );
			delete_post_meta( $this->order->id, '_ywpi_invoice_path' );
			delete_post_meta( $this->order->id, '_ywpi_invoice_date' );
		}

		/*
		 * Return the next available invoice number
		 */
		private function get_new_invoice_number() {
			//  Check if this is the first invoice of the year, in this case, if reset on new year is enabled, restart from 1
			if ( 'yes' === get_option( 'ywpi_invoice_reset' ) ) {
				$last_year = get_option( 'ywpi_invoice_year_billing' );

				if ( isset( $last_year ) && is_numeric( $last_year ) ) {
					$current_year = getdate();
					$current_year = $current_year['year'];
					if ( $last_year < $current_year ) {
						//  set new year as last invoiced year and reset invoice number
						update_option( 'ywpi_invoice_year_billing', $current_year );
						update_option( 'ywpi_invoice_number', 1 );
					}
				}
			}

			$current_invoice_number = get_option( 'ywpi_invoice_number' );
			if ( ! isset( $current_invoice_number ) || ! is_numeric( $current_invoice_number ) ) {
				$current_invoice_number = 1;
			}

			return $current_invoice_number;
		}

		/**
		 * Set invoice data for current order, picking the invoice number from the related general option
		 */
		public function create_invoice() {
			//  Avoid generating a new invoice from a previous one
			if ( $this->has_invoice ) {
				return;
			}

			$this->invoice_number = $this->get_new_invoice_number();
			$this->invoice_prefix = get_option( 'ywpi_invoice_prefix' );
			$this->invoice_suffix = get_option( 'ywpi_invoice_suffix' );

			$this->invoice_date = time();

			$date = getdate( $this->invoice_date );
			$year = $date['year'];
			$this->invoice_path = $year . "/invoice_" . $this->invoice_number . ".pdf";

			$this->save_invoice();
		}

		private function save_invoice() {
			//  Avoid generating a new invoice from a previous one
			if ( $this->has_invoice ) {
				return;
			}

			$this->has_invoice = true;

			update_post_meta( $this->order->id, '_ywpi_invoiced', $this->has_invoice );

			update_post_meta( $this->order->id, '_ywpi_invoice_number', $this->invoice_number );

			update_post_meta( $this->order->id, '_ywpi_invoice_prefix', $this->invoice_prefix );
			update_post_meta( $this->order->id, '_ywpi_invoice_suffix', $this->invoice_suffix );
			update_post_meta( $this->order->id, '_ywpi_invoice_date', $this->invoice_date );
			update_post_meta( $this->order->id, '_ywpi_invoice_path', $this->invoice_path );

			$this->save_pdf_invoice();

			update_option( 'ywpi_invoice_number', $this->invoice_number + 1 );
		}
	}
}