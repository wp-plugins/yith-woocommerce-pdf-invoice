<?php global $ywpi_document; ?>

<table class="invoice-details">
	<thead>
	<tr>
		<th class="column-product">Product</th>
		<th class="column-quantity">Qty</th>
		<th class="column-price">Price</th>
		<th class="column-total">Line total</th>
		<th class="column-tax">Tax</th>
	</tr>
	</thead>
	<tbody>
	<?php

	$order_items = $ywpi_document->order->get_items();
	foreach ( $order_items as $item_id => $item ) {
		if ( isset( $item['qty'] ) ) {
			$price_per_unit      = $item["line_subtotal"] / $item['qty'];
			$price_per_unit_sale = $item["line_total"] / $item['qty'];
			$discount            = $price_per_unit - $price_per_unit_sale;
		}
		$tax = $item["line_tax"];

		?>

		<tr>
			<td class="column-product"><?php echo $item['name']; ?></td>
			<td class="column-quantity"><?php echo ( isset( $item['qty'] ) ) ? esc_html( $item['qty'] ) : ''; ?></td>
			<td class="column-price"><?php echo wc_price( $price_per_unit ); ?></td>
			<td class="column-total"><?php echo wc_price( $item["line_subtotal"] ); ?></td>
			<td class="column-tax"><?php echo wc_price( $tax ); ?></td>
		</tr>

	<?php }; ?>
	</tbody>
</table>

<table>
	<tr>
		<td class="column1">

		</td>
		<td class="column2">
			<table class="invoice-totals">
				<tr class="invoice-details-subtotal">
					<td class="column-product"><?php _e( "Subtotal", "ywpi" ); ?></td>
					<td class="column-total"><?php echo wc_price( $ywpi_document->order->get_subtotal() ); ?></td>
				</tr>

				<tr>
					<td class="column-product"><?php _e( "Discount", "ywpi" ); ?></td>
					<td class="column-total"><?php echo wc_price( $ywpi_document->order->get_total_discount() ); ?></td>
				</tr>

				<?php if ( 'yes' == get_option( 'woocommerce_calc_taxes' ) ) : ?>
					<?php foreach ( $ywpi_document->order->get_tax_totals() as $code => $tax ) : ?>
						<tr class="invoice-details-vat">
							<td class="column-product"><?php echo $tax->label; ?>:</td>
							<td class="column-total"><?php echo $tax->formatted_amount; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>

				<tr class="invoice-details-total">
					<td class="column-product"><?php _e( "Total", "ywpi" ); ?></td>
					<td class="column-total"><?php echo wc_price( $ywpi_document->order->get_total() ); ?></td>
				</tr>
			</table>
		</td>
	</tr>

</table>