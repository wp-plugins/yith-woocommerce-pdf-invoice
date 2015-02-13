<?php
/**
 * The Template for invoice
 *
 * Override this template by copying it to yourtheme/invoice/template.php
 *
 * @author        Yithemes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
?>

<!DOCTYPE html>
<html>
<head>

	<?php
	/**
	 * yith_ywpi_invoice_template_head hook
	 *
	 * @hooked add_style_files - 10 (add css file based on type of current document
	 */
	do_action( 'yith_ywpi_invoice_template_head' );
	?>
</head>

<body>
<?php
/**
 * yith_ywpi_invoice_template_content hook
 *
 * @hooked add_invoice_content - 10 (add invoice template content
 */
do_action( 'yith_ywpi_invoice_template_content' );
?>

</body>
</html>