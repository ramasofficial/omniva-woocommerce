<?php
/**
 * Customer note email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-note.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<p>Jūsų siuntos numeris:</p>

<?php
		$message = '';
		$tracking_number = $customer_note;
		$tracking_number_explode = explode(' ', $tracking_number);

		if($tracking_number_explode[0] == 'OMNIVA:')
		{
				$track = $tracking_number_explode[1];
				$message = '<p>Savo siuntą galite sekti: <a target="_blank" href="https://www.omniva.lt/verslo/siuntos_sekimas?barcode='.$track.'">OMNIVA</a></p>';
		}

		if($tracking_number_explode[0] == 'LP:'){
				$track = $tracking_number_explode[1];
				$message = '<a target="_blank" href="https://www.post.lt/">LIETUVOS PAŠTAS</a>';
		}

		if(empty($track))
		{
				$track = $tracking_number;
		}

		echo'<blockquote>'.wpautop( wptexturize( $track ) ).'</blockquote>';
		echo $message;
?>

<?php // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>

<p><?php esc_html_e( 'As a reminder, here are your order details:', 'woocommerce' ); ?></p>

<?php

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

?>
<p>Dėkojame, kad pirkote!</p>
<strong>
<?php

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );

?>
</strong>
