<?php

////    P A Y M E N T   H I S T O R Y   /////

function payments_table_gateway_column( $columns ){
  $columns['gateway'] = __('Gateway', 'coopfundify');
  return $columns;
}
add_filter('edd_payments_table_columns', 'payments_table_gateway_column');


function coopfundify_payments_table_views ( $views ){
    if( ! current_user_can( 'manage_shop_settings' ) ) {
        //$views['gateways'] = '</span></li></ul><br><br><br>';
        return $views; //wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
    } else {
        $views['gateways'] = '</span></li></ul>';
    }
    
    $gateways = edd_get_payment_gateways();
    $current        = isset( $_GET['gateway'] ) ? $_GET['gateway'] : '';
    $views['gateways'] .= '<ul class="subsub" style="display:inline-flex;">'.sprintf('<li style="display:inline-block;"><a href="%s"%s>%s</a></li> ', add_query_arg( array('gateway'=>FALSE) ), $current=='' ? ' class="current"' : '', __('All gateways', 'coopfundify'));
    foreach($gateways as $key => $arr){
       $views[ $key ] = sprintf( '<li style="display:inline-block;"> <a href="%s"%s>%s</a> </li>', add_query_arg( array( 'gateway' => $key, 'paged' => FALSE ) ), $current === $key ? ' class="current"' : '', $key );//__('Failed', 'edd') . $failed_count );
    } 
    return $views;
}
add_filter( 'edd_payments_table_views', 'coopfundify_payments_table_views');


function gateway_filter( $query ) {
	global $pagenow;//, $post_type;
	if( 'edit.php' != $pagenow || $query->query_vars['post_type'][0] != 'edd_payment' || !isset( $_GET['gateway'] ) )//|| !is_main_query())
		return;

	$query->set('meta_key', '_edd_payment_gateway');
	$query->set('meta_value', $_GET['gateway'] );
    //$query->query['meta_key'] = '_edd_payment_gateway';
    //$query->query['meta_value'] = $_GET['gateway'];
}
add_action( 'pre_get_posts', 'gateway_filter', 9999 );


?>
