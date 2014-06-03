<?php

/**
 *  functions to override from easy-digital-downloads/includes/admin/payments/class-payments-table.php
 */
 
function coopfy_payments_table_columns($columns) {
	$columns['campaign'] = __( 'Campaign', 'edd');

	return $columns;
}
add_action ('edd_payments_table_columns', 'coopfy_payments_table_columns');


function coopfy_payments_table_sortable_columns($columns) {
	$columns['campaign'] = array( 'campaign', false );
	
	return $columns;
}
add_action ( 'edd_payments_table_sortable_columns', 'coopfy_payments_table_sortable_columns');


function coopfy_column_campaign ( $value, $payment_id, $column_name ) {
	if( $column_name == 'campaign' ) {
		$payment = get_post( $payment_id );
		$cart_items   = edd_get_payment_meta_cart_details( $payment );
		
		if ( $cart_items ) {
			foreach ( $cart_items as $key => $cart_item ) :
				$item_id  = isset( $cart_item['id']    ) ? $cart_item['id']    : $cart_item;
				$value = get_the_title( $item_id );
			endforeach;
		} else {
			$value = 'empty cart: '.$value;
		}
	}
	
	return $value;
}

add_action ( 'edd_payments_table_column', 'coopfy_column_campaign');

?>
