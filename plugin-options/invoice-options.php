<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$current_date = getdate();

$general_options = array(

	'invoice' => array(
		'section_invoice_settings' => array(
			'name' => __( 'Invoice settings', 'ywpi' ),
			'type' => 'title',
			'id'   => 'ywpi_section_invoice'
		),
		'next_invoice_number'      => array(
			'name'    => __( 'Next invoice number', 'ywpi' ),
			'type'    => 'number',
			'id'      => 'ywpi_invoice_number',
			'desc'    => __( 'Invoice number for next invoice document.', 'ywpi' ),
			'default' => 1
		),
		'next_invoice_year'        => array(
			'name'    => __( 'year billing', 'ywpi' ),
			'type'    => 'hidden',
			'id'      => 'ywpi_invoice_year_billing',
			'default' => $current_date['year']
		),
		'invoice_prefix'           => array(
			'name' => __( 'Invoice prefix', 'ywpi' ),
			'type' => 'text',
			'id'   => 'ywpi_invoice_prefix',
			'desc' => __( 'Set a text to be used as prefix in invoice number. Leave it blank if no prefix has to be used', 'ywpi' ),
		),
		'invoice_suffix'           => array(
			'name' => __( 'Invoice suffix', 'ywpi' ),
			'type' => 'text',
			'id'   => 'ywpi_invoice_suffix',
			'desc' => __( 'Set a text to be used as suffix in invoice number. Leave it blank if no suffix has to be used', 'ywpi' ),
		),
		'invoice_number_format'    => array(
			'name'    => __( 'Invoice number format', 'ywpi' ),
			'type'    => 'text',
			'id'      => 'ywpi_invoice_number_format',
			'desc'    => __( 'Set format for invoice number. Use [number], [prefix] and [suffix] as placeholders', 'ywpi' ),
			'default' => '[prefix]/[number]/[suffix]'
		),
		'invoice_reset'            => array(
			'name'    => __( 'Reset on 1st January', 'ywpi' ),
			'type'    => 'checkbox',
			'id'      => 'ywpi_invoice_reset',
			'desc'    => __( 'Set restart from 1 on 1st January.', 'ywpi' ),
			'default' => false
		),
		'invoice_date_format'      => array(
			'name'    => __( 'Invoice date format', 'ywpi' ),
			'type'    => 'text',
			'id'      => 'ywpi_invoice_date_format',
			'desc'    => __( 'Set date format as it should appear on invoices.', 'ywpi' ),
			'default' => 'd/m/Y'
		),
		array(
			'title'   => __( 'Invoice generation', 'ywpi' ),
			'id'      => 'ywpi_invoice_generation',
			'type'    => 'radio',
			'options' => array(
				'auto'   => "Automatic generation",
				'manual' => "Manual generation"
			),
			'default' => 'manual',
			'std'     => 'manual'
		),
		array(
			'title'   => __( 'Generate invoice automatically:', 'ywpi' ),
			'id'      => 'ywpi_create_invoice_on',
			'type'    => 'radio',
			'options' => array(
				'new'        => __( "On new order.", "ywpi" ),
				'processing' => __( "For processing order.", "ywpi" ),
				'completed'  => __( "For completed order.", "ywpi" )
			),
			'default' => 'completed',
			'std'     => 'completed'
		),
		array(
			'title'   => __( 'PDF invoice behaviour:', 'ywpi' ),
			'id'      => 'ywpi_pdf_invoice_behaviour',
			'type'    => 'radio',
			'options' => array(
				'download' => "Download PDF",
				'open'     => "Open PDF on browser"
			),
			'default' => 'download',
			'std'     => 'download'
		),
		'shipping_list'            => array(
			'name'    => __( 'Enable shipping list', 'ywpi' ),
			'type'    => 'checkbox',
			'id'      => 'ywpi_enable_shipping_list',
			'desc'    => __( 'Add buttons for shipping list document generation.', 'ywpi' ),
			'default' => 'yes'
		),
		'invoice_settings_end'     => array(
			'type' => 'sectionend',
			'id'   => 'ywpi_section_invoice_end'
		)
	)
);


return $general_options;
