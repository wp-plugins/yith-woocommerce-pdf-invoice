<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'YITH_WooCommerce_Pdf_Invoice' ) ) {

	/**
	 * Implements features of Yith WooCommerce Pdf Invoice
	 *
	 * @class YITH_WooCommerce_Pdf_Invoice
	 * @package Yithemes
	 * @since   1.0.0
	 * @author  Your Inspiration Themes
	 */
	class YITH_WooCommerce_Pdf_Invoice {

		/**
		 * @var $_panel Panel Object
		 */
		protected $_panel;

		/**
		 * @var $_premium string Premium tab template file name
		 */
		protected $_premium = 'premium.php';

		/**
		 * @var string Premium version landing link
		 */
		protected $_premium_landing = 'http://plugins.yithemes.com/yith-woocommerce-pdf-invoice/';

		/**
		 * @var string Yith WooCommerce Pdf invoice panel page
		 */
		protected $_panel_page = 'yith_woocommerce_pdf_invoice';

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
		public function __construct() {
			if ( ! file_exists( YITH_YWPI_INVOICE_DIR ) ) {
				wp_mkdir_p( YITH_YWPI_INVOICE_DIR );
			}

			$this->init_invoice_action();

			/**
			 *  Create YIT menu items for current plugin
			 */
			$this->set_menu_items_related_actions();

			$this->set_metabox_actions();

			$this->add_buttons_on_customer_orders_page();
			$this->add_features_on_admin_orders_page();

			$this->add_invoice_template_actions();

			$this->add_order_status_related_actions();

			/*
            * Check if invoice should be attached to emails
			*/
			add_filter( 'woocommerce_email_attachments', array( $this, 'attach_invoice_to_email' ), 99, 3 );

			add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_admin_invoice_buttons' ) );

			//  Add stylesheets and scripts files
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}

		/**
		 * Enqueue css file
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 */
		public function enqueue_styles() {
			wp_enqueue_style( 'ywpi_css', YITH_YWPI_ASSETS_URL . '/css/ywpi.css' );
		}

		/*
		 * Add some actions triggered by order status
		 */
		public function add_order_status_related_actions() {
			//  If invoice generation is only manual, no automatic actions will be added
			if ( 'auto' != get_option( 'ywpi_invoice_generation' ) ) {
				return;
			}

			if ( 'new' === get_option( 'ywpi_create_invoice_on' ) ) {
				add_action( 'woocommerce_order_status_on-hold', array( $this, 'new_invoice' ) );
			} else if ( 'processing' === get_option( 'ywpi_create_invoice_on' ) ) {
				add_action( 'woocommerce_order_status_processing', array( $this, 'new_invoice' ) );
			} else if ( 'completed' === get_option( 'ywpi_create_invoice_on' ) ) {
				add_action( 'woocommerce_order_status_completed', array( $this, 'new_invoice' ) );
			}
		}

		/**
		 * Add PDF actions to the orders listing
		 */
		public function add_admin_invoice_buttons( $order ) {
			$invoice = new YITH_Invoice( $order->id );

			if ( $invoice->has_invoice ) {
				$url  = ywpi_invoice_nonce_url( YITH_YWPI_VIEW_INVOICE_ARG_NAME, $invoice );
				$text = "Show invoice";
			} else {
				$url  = ywpi_invoice_nonce_url( YITH_YWPI_CREATE_INVOICE_ARG_NAME, $invoice );
				$text = "Create invoice";
			}

			echo '<a href="' . $url . '" class="button tips ywpi_invoice" target="_blank" data-tip="' . $text . '" title="' . $text . '">' . $text . '<img src="' . YITH_YWPI_ASSETS_URL . '/images/invoice.png"></a>';
		}

		//region Invoice templates actions

		public function add_invoice_template_actions() {

			add_action( 'yith_ywpi_invoice_template_head', array( $this, 'add_invoice_style' ) );

			add_action( 'yith_ywpi_invoice_template_content', array( $this, 'add_invoice_content' ) );

			add_action( 'yith_ywpi_invoice_template_sender', array( $this, 'show_invoice_template_sender' ) );

			add_action( 'yith_ywpi_invoice_template_company_logo', array(
				$this,
				'show_invoice_template_company_logo'
			) );
			add_action( 'yith_ywpi_invoice_template_customer_data', array(
				$this,
				'show_invoice_template_customer_data'
			) );
			add_action( 'yith_ywpi_invoice_template_invoice_data', array(
				$this,
				'show_invoice_template_invoice_data'
			) );

			add_action( 'yith_ywpi_invoice_template_products_list', array(
				$this,
				'show_invoice_template_products_list'
			) );

			add_action( 'yith_ywpi_invoice_template_footer', array( $this, 'show_invoice_template_footer' ) );
		}

		public function attach_invoice_to_email( $attachments, $status, $order ) {
			$invoice = new YITH_Invoice( $order->id );
			if ( ! $invoice->has_invoice ) {
				return $attachments;
			}

			$allowed_statuses = array(
				'new_order',
				'customer_invoice',
				'customer_processing_order',
				'customer_completed_order'
			);

			if ( isset( $status ) && in_array( $status, $allowed_statuses ) ) {

				$your_pdf_path = YITH_YWPI_INVOICE_DIR . $invoice->invoice_path;
				$attachments[] = $your_pdf_path;
			}

			return $attachments;
		}


		public function add_invoice_style() {

			if ( file_exists( YITH_YWPI_INVOICE_TEMPLATE_DIR . 'invoice.css' ) ) {
				echo '<link rel="stylesheet" type="text/css" href="' . YITH_YWPI_INVOICE_TEMPLATE_URL . 'invoice.css">';
			}
		}

		public function add_invoice_content() {
			global $ywpi_invoice;
			if ( file_exists( YITH_YWPI_INVOICE_TEMPLATE_DIR . 'invoice.php' ) ) {
				wc_get_template( 'invoice.php', array( $ywpi_invoice ), YITH_YWPI_INVOICE_TEMPLATE_DIR, YITH_YWPI_INVOICE_TEMPLATE_DIR );
			}
		}

		/**
		 * Render and show data to "sender section" on invoice template
		 */
		public function show_invoice_template_sender() {
			$company_name    = 'yes' === get_option( 'ywpi_show_company_name' ) ? get_option( 'ywpi_company_name' ) : null;
			$company_details = 'yes' === get_option( 'ywpi_show_company_details' ) ? nl2br( get_option( 'ywpi_company_details' ) ) : null;

			if ( ! isset( $company_name ) && ! isset( $show_logo ) ) {
				return;
			}

			echo '<span class="invoice-from-to">Invoice From: </span>';
			if ( isset( $company_name ) ) {
				echo '<span class="company-name">' . $company_name . '</span>';
			}
			if ( isset ( $company_details ) ) {
				echo '<span class="company-details" > ' . $company_details . '</span > ';
			}
		}

		/**
		 * Show company logo on invoice template
		 */
		public function show_invoice_template_company_logo() {
			$company_logo = 'yes' === get_option( 'ywpi_show_company_logo' ) ? get_option( 'ywpi_company_logo' ) : null;

			if ( ! isset( $company_logo ) ) {
				return;
			}

			if ( isset( $company_logo ) ) {
				echo '<div class="company-logo">
					<img src="' . $company_logo . '">
				</div>';
			}
		}

		/**
		 * Show data of customer on invoice template
		 */
		public function show_invoice_template_customer_data() {
			global $ywpi_invoice;

			echo '<div class="invoice-to-section" > ';
			// Display values
			if ( $ywpi_invoice->order->get_formatted_billing_address() ) {
				echo '<span class="invoice-from-to" > ' . __( "Invoice To:", "ywpi" ) . '</span > ' . wp_kses( $ywpi_invoice->order->get_formatted_billing_address(), array( "br" => array() ) );
			}

			echo '</div > ';
		}

		/**
		 * Show data of customer on invoice template
		 */
		public function show_invoice_template_invoice_data() {
			global $ywpi_invoice;

			?>
			<table>
				<tr class="invoice-number">
					<td><?php _e( "Invoice", "ywpi" ); ?></td>
					<td class="right"><?php echo $ywpi_invoice->get_formatted_invoice_number(); ?></td>
				</tr>

				<tr class="invoice-order-number">
					<td><?php _e( "Order", "ywpi" ); ?></td>
					<td class="right"><?php echo $ywpi_invoice->order->get_order_number(); ?></td>
				</tr>

				<tr class="invoice-date">
					<td><?php _e( "Invoice date", "ywpi" ); ?></td>
					<td class="right"><?php echo $ywpi_invoice->get_formatted_invoice_date(); ?></td>
				</tr>
				<tr class="invoice-amount">
					<td><?php _e( "Order Amount", "ywpi" ); ?></td>
					<td class="right"><?php echo wc_price( $ywpi_invoice->order->get_total() ); ?></td>
				</tr>
			</table>
		<?php
		}

		/**
		 * Show product list for current order on invoice template
		 */
		public function show_invoice_template_products_list() {
			include( YITH_YWPI_INVOICE_TEMPLATE_DIR . 'invoice-details.php' );
		}

		/**
		 * Show footer information on invoice template
		 */
		public function show_invoice_template_footer() {
			include( YITH_YWPI_INVOICE_TEMPLATE_DIR . 'invoice-footer.php' );
		}

		//endregion

		/**
		 * Add front-end button for actions available for customers
		 */
		public function  add_buttons_on_customer_orders_page() {
			/**
			 * Show print invoice button on frontend orders page
			 */
			add_action( 'woocommerce_my_account_my_orders_actions', array(
				$this,
				'print_invoice_button'
			), 10, 2 );
		}

		/**
		 * Add back-end buttons for actions available for admins
		 */
		public function add_features_on_admin_orders_page() {
			add_action( 'manage_shop_order_posts_custom_column', array(
				$this,
				'show_invoice_custom_column_data'
			), 99 );
		}

		/*
		 * Fill invoice custom column on back-end with related data
		 */
		public function show_invoice_custom_column_data( $column ) {
			global $post;

			if ( 'order_title' != $column ) {
				return $column;
			}

			$invoice = new YITH_Invoice( $post->ID );
			if ( ! $invoice->has_invoice ) {
				return $column;
			}

			echo '<small class="meta">' . sprintf( "Invoice N : %s of %s", $invoice->get_formatted_invoice_number(), $invoice->get_formatted_invoice_date() ) . '</small>';
		}

		public function  init_invoice_action() {
			if ( isset( $_GET[ YITH_YWPI_CREATE_INVOICE_ARG_NAME ] ) ) {
				add_action( 'init', array( $this, 'create_invoice' ) );
			} else if ( isset( $_GET[ YITH_YWPI_VIEW_INVOICE_ARG_NAME ] ) ) {
				add_action( 'init', array( $this, 'view_invoice' ) );
			} else if ( isset( $_GET[ YITH_YWPI_RESET_INVOICE_ARG_NAME ] ) ) {
				add_action( 'init', array( $this, 'reset_invoice' ) );
			}
		}

		private function check_invoice_url_for_action( $order_id ) {
			//  Check if order id is a valid id
			if ( ! is_numeric( $order_id ) ) {
				return false;
			}

			//  check for nounce value
			$invoice = new YITH_Invoice( $order_id );
			if ( ! ywpi_invoice_nonce_check( $invoice ) ) {
				return false;
			}

			return true;
		}


		public function create_invoice() {
			if ( isset ( $_GET[ YITH_YWPI_CREATE_INVOICE_ARG_NAME ] ) ) {
				$order_id = $_GET[ YITH_YWPI_CREATE_INVOICE_ARG_NAME ];
				if ( ! $this->check_invoice_url_for_action( $order_id ) ) {
					return;
				}

				$this->new_invoice( $order_id );
			}
		}

		/*
		 * Create a new invoice starting from an order id
		 */
		public function new_invoice( $order_id ) {
			global $ywpi_invoice;
			$ywpi_invoice = new YITH_Invoice( $order_id );
			$ywpi_invoice->create_invoice();
		}

		public function view_invoice() {
			if ( isset ( $_GET[ YITH_YWPI_VIEW_INVOICE_ARG_NAME ] ) ) {
				$order_id = $_GET[ YITH_YWPI_VIEW_INVOICE_ARG_NAME ];
				if ( ! $this->check_invoice_url_for_action( $order_id ) ) {
					return;
				}

				$invoice = new YITH_Invoice( $order_id );

				$pdf_url = YITH_YWPI_INVOICE_DIR . $invoice->invoice_path;

				//  Check if show pdf invoice on browser or asking to download it
				$where_to_show = get_option( 'ywpi_pdf_invoice_behaviour' );

				if ( isset( $where_to_show ) && ( 'open' == $where_to_show ) ) {
					header( 'Content-type: application/pdf' );
					header( 'Content-Disposition: inline; filename="' . basename( $pdf_url ) . '"' );
				} else {
					header( 'Content-type: application / pdf' );
					header( 'Content-Disposition: attachment; filename = "' . basename( $pdf_url ) . '"' );
					header( 'Content-Transfer-Encoding: binary' );
					header( 'Content-Length: ' . filesize( $pdf_url ) );
					header( 'Accept-Ranges: bytes' );
				}
				@readfile( $pdf_url );
			}
		}

		public function reset_invoice() {
			if ( isset ( $_GET[ YITH_YWPI_RESET_INVOICE_ARG_NAME ] ) ) {
				$order_id = $_GET[ YITH_YWPI_RESET_INVOICE_ARG_NAME ];
				if ( ! $this->check_invoice_url_for_action( $order_id ) ) {
					return;
				}

				$invoice = new YITH_Invoice( $order_id );
				$invoice->reset();
			}
		}

		//region    ****   Custom menu entry for plugin, using Yith plugin framework    ****

		/**
		 * Register actions and filters to be used for creating an entry on YIT Plugin menu
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 * @return void
		 */
		private function set_menu_items_related_actions() {
			add_action( 'after_setup_theme', array( $this, 'plugin_fw_loader' ), 1 );

			//Add action links
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_YWPI_DIR . '/' . basename( YITH_YWPI_FILE ) ), array(
				$this,
				'action_links'
			) );

			//  Add stylesheets and scripts files
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
		}

		/**
		 * Register actions and filters to be used for creating an entry on YIT Plugin menu
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 * @return void
		 */
		private function set_metabox_actions() {
			/**
			 * Add metabox on order, to let vendor add order tracking code and carrier
			 */
			add_action( 'add_meta_boxes', array( $this, 'add_invoice_metabox' ) );
		}

		/**
		 *  Add a metabox on backend order page, to be filled with order tracking information
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 * @return void
		 */
		function add_invoice_metabox() {

			add_meta_box( 'yith-pdf-invoice-box', __( 'YITH PDF Invoice', 'ywpi' ), array(
				$this,
				'show_pdf_invoice_metabox'
			), 'shop_order', 'side', 'high' );
		}

		/**
		 * Show metabox content for tracking information on backend order page
		 *
		 * @param $post the order object that is currently shown
		 *
		 * @since  1.0
		 * @author Lorenzo giuffrida
		 * @access public
		 * @return void
		 */
		function show_pdf_invoice_metabox( $post ) {
			$invoice = new YITH_Invoice( $post->ID );

			?>
			<div class="invoice-information">
				<?php if ( $invoice->has_invoice ) : ?>
					<div style="overflow: hidden; padding: 5px 0">
						<span style="float:left"><?php _e( 'Invoiced on : ', 'ywpi' ); ?></span>
						<strong><span
								style="float:right"><?php echo $invoice->get_formatted_invoice_date(); ?></span></strong>
					</div>

					<div style="overflow: hidden; padding: 5px 0">

						<span style="float:left"><?php _e( 'Invoice number : ', 'ywpi' ); ?></span>
						<strong><span
								style="float:right"><?php echo $invoice->get_formatted_invoice_number(); ?></span></strong>
					</div>

					<div style="clear: both; margin-top: 15px">
						<a class="button"
						   href=" <?php echo ywpi_invoice_nonce_url( YITH_YWPI_VIEW_INVOICE_ARG_NAME, $invoice ); ?>">View
							invoice</a>
						<a class="button"
						   href="<?php echo ywpi_invoice_nonce_url( YITH_YWPI_RESET_INVOICE_ARG_NAME, $invoice ); ?>">Reset
							invoice</a>
					</div>
				<?php else : ?>
					<p>
						<a class="button"
						   href="<?php echo ywpi_invoice_nonce_url( YITH_YWPI_CREATE_INVOICE_ARG_NAME, $invoice ); ?>">Create
							invoice</a>
					</p>
				<?php
				endif; ?>
			</div>
		<?php

		}


		/**
		 * Load YIT core plugin
		 *
		 * @since  1.0
		 * @access public
		 * @return void
		 * @author Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT' ) || ! defined( 'YIT_CORE_PLUGIN' ) ) {
				require_once( 'plugin-fw/yit-plugin.php' );
			}
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->_panel ) ) {
				return;
			}

			$admin_tabs = array(
				'invoice'  => __( 'Invoice', 'ywpi' ),
				'template' => __( 'Template', 'ywpi' )
			);

			if ( ! defined( 'YITH_YWPI_PLUGIN_STARTING' ) ) {
				if ( ! defined( 'YITH_YWPI_PREMIUM' ) ) {
					$admin_tabs['premium-landing'] = __( 'Premium Version', 'ywpi' );
				}
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'page_title'       => 'Pdf Invoice',
				'menu_title'       => 'Pdf Invoice',
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yit_plugin_panel',
				'page'             => $this->_panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_YWPI_DIR . '/plugin-options'
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {

				require_once( 'plugin-fw/lib/yit-plugin-panel-wc.php' );
			}

			$this->_panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_ywpi_logo', array( $this->_panel, 'yit_upload' ), 10, 1 );
			add_action( 'woocommerce_update_option_ywpi_logo', array( $this->_panel, 'yit_upload_update' ), 10, 1 );
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return void
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_YWPI_TEMPLATE_DIR . '/admin/' . $this->_premium;
			if ( file_exists( $premium_tab_template ) ) {
				include_once( $premium_tab_template );
			}
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 *
		 * @param $links | links plugin array
		 *
		 * @return   mixed Array
		 * @since    1.0
		 * @author   Andrea Grillo <andrea.grillo@yithemes.com>
		 * @return mixed
		 * @use plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links[] = '<a href="' . admin_url( "admin.php?page={$this->_panel_page}" ) . '">' . __( 'Settings', 'ywpi' ) . '</a>';
			if ( defined( YITH_YWPI_PLUGIN_STARTING ) || YITH_YWPI_PLUGIN_STARTING ) {
				return $links;
			}

			if ( defined( 'YITH_YWPI_FREE_INIT' ) ) {
				$links[] = '<a href="' . $this->_premium_landing . '" target="_blank">' . __( 'Premium Version', 'ywpi' ) . '</a>';
			}

			return $links;
		}

		//endregion

		/**
		 * Add a button to print invoice, if exists, from order page on frontend.
		 */
		public function print_invoice_button( $actions, $order ) {
			$invoice = new YITH_Invoice( $order->id );

			if ( $invoice->has_invoice ) {
				// Add the print button
				$actions['print-invoice'] = array(
					'url'  => ywpi_invoice_nonce_url( YITH_YWPI_VIEW_INVOICE_ARG_NAME, $invoice ),
					'name' => __( 'Invoice', 'ywpi' )
				);
			}

			return $actions;
		}
	}
}