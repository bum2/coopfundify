<?php

////
// only send purchase receipt when is paypal, the others are sending their own emails
function coopfunding_trigger_purchase_receipt( $payment_id ) {
    remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 1 );
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['edd-action'] ) && 'edit_payment' == $_POST['edd-action'] )
		return;
    
    $gateway = edd_get_payment_gateway( $payment_id );
    if( $gateway == 'paypal'){
        // Send email with secure download link
        edd_email_purchase_receipt( $payment_id );
    } else {
        return;
    }
}
remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 1 );
add_action( 'edd_complete_purchase', 'coopfunding_trigger_purchase_receipt', 1000, 1 );

?>
