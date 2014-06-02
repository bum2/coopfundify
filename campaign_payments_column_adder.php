<?php

/**
 *  functions to override from easy-digital-downloads/includes/admin/payments/class-payments-table.php
 */
 
function coopfy_get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'ID'     	=> __( 'ID', 'edd' ),
			'email'  	=> __( 'Email', 'edd' ),
			'details'  	=> __( 'Details', 'edd' ),
			'amount'  	=> __( 'Amount', 'edd' ),
			'date'  	=> __( 'Date', 'edd' ),
			'user'  	=> __( 'User', 'edd' ),
			'status'  	=> __( 'Status', 'edd' ),
			/* bumbum in */
			'campaign'	=> __( 'Campaign', 'edd')
			/* bumbum out */
		);

		return apply_filters( 'edd_payments_table_columns', $columns );
}
add_action ('get_columns', 'coopfy_get_columns');


function coopfy_get_sortable_columns() {
		$columns = array(
			'ID' 		=> array( 'ID', true ),
			'amount' 	=> array( 'amount', false ),
			'date' 		=> array( 'date', false ),
			/* bumbum in */
			'campaign'	=> array( 'campaign', false )
			/* bumbum out */
		);
		return apply_filters( 'edd_payments_table_sortable_columns', $columns );
}
add_action ( 'get_sortable_columns', 'coopfy_get_sortable_columns');


function coopfy_column_default( $payment, $column_name ) {
		switch ( $column_name ) {
			case 'amount' :
				$amount  = ! empty( $payment->total ) ? $payment->total : 0;
				$value   = edd_currency_filter( edd_format_amount( $amount ) );
				break;
			case 'date' :
				$date    = strtotime( $payment->date );
				$value   = date_i18n( get_option( 'date_format' ), $date );
				break;
			case 'status' :
				$payment = get_post( $payment->ID );
				$value   = edd_get_payment_status( $payment, true );
				break;
			case 'details' :
				$value = '<a href="' . add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ) . '">' . __( 'View Order Details', 'edd' ) . '</a>';
				break;
				
			/* bumbum in */
			case 'campaign' :
				$payment = get_post( $payment->ID );
				$cart_items   = edd_get_payment_meta_cart_details( $payment );
				$value = 'default campaign name';
				if ( $cart_items ) :
					foreach ( $cart_items as $key => $cart_item ) :
						$item_id  = isset( $cart_item['id']    ) ? $cart_item['id']    : $cart_item;
						$value = get_the_title( $item_id );
					endforeach;
				endif;
				break;
			/* bumbum out */
			
			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;

		}
		return apply_filters( 'edd_payments_table_column', $value, $payment->ID, $column_name );
}

add_action ( 'column_default', 'coopfy_column_default');

?>
