<?php

/**
 *  functions to override from easy-digital-downloads/includes/admin/payments/class-payments-table.php
 */

function coopfy_payments_table_columns($columns) {

	$columns['campaign'] =  __( 'Campaign', 'coopfundify');
	return $columns;

}
add_filter ('edd_payments_table_columns', 'coopfy_payments_table_columns');


function coopfy_payments_table_sortable_columns($columns) {
	
	$columns['campaign'] = array( 'campaign', false );
	return $columns;
	
}
//add_filter ( 'edd_payments_table_sortable_columns', 'coopfy_payments_table_sortable_columns');


function coopfy_column_campaign ( $value, $payment_id, $column_name ) {

	if( $column_name == 'campaign' ) {
		$cart_items  = edd_get_payment_meta_cart_details( $payment_id );

		if ( $cart_items ) {
			foreach ( $cart_items as $key => $cart_item ) :
				$item_id  = isset( $cart_item['id']    ) ? $cart_item['id']    : $cart_item;
				$value = get_the_title($item_id);//basename( get_permalink( $item_id ) );
			endforeach;
		} else {
			$value = 'empty cart: '.$value;
		}
	}

	return $value;
	
}
add_filter ( 'edd_payments_table_column', 'coopfy_column_campaign', 1, 3  );


// add view filter for admins

function campaigns_payments_table_views ( $views ){

    if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $views; //wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}
    
    $args = array(
        'posts_per_page'   => 50,
        'offset'           => 0,
        'category'         => '',
        'category_name'    => '',
        'orderby'          => 'ID',
        'order'            => 'DESC',
        'include'          => '',
        'exclude'          => '',
        'meta_key'         => '',
        'meta_value'       => '',
        'post_type'        => 'download',
        'post_mime_type'   => '',
        'post_parent'      => '',
        'author'	   => '',
        'post_status'      => 'publish',
        'suppress_filters' => true 
    );
    $campaigns = get_posts( $args );

    //$campaigns = EDD()->api->get_products();
    $current        = isset( $_GET['campaign'] ) ? $_GET['campaign'] : '';
    $views['campaign_filter'] = '</ul><br /><span class="subsubsub">'.sprintf('<a href="%s"%s>%s</a> ', add_query_arg( array('campaign'=>FALSE) ), $current=='' ? ' class="current"' : '', 'All campaigns');
    
    $ides = array();
    foreach($campaigns as $campaign){//$key => $arr){
       $ide = icl_object_id( $campaign->ID, 'download', true ); 
       if(!in_array($ide, $ides)){
           $views[ $ide ] = sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'campaign' => $ide, 'paged' => FALSE ) ), $current== $ide ? ' class="current"' : '', $campaign->post_name );
           $ides[] = $ide;
       }
    }
     
    return $views;
}
add_filter( 'edd_payments_table_views', 'campaigns_payments_table_views');


function campaign_filter( $query ) {
	global $pagenow;//, $post_type;
    if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $query; //wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}
	if( 'edit.php' != $pagenow || $query->query_vars['post_type'][0] != 'edd_payment' || !isset( $_GET['campaign'] ) )//|| !is_main_query())
		return $query;

        $langs = icl_get_languages('skip_missing=1');
        $langids = array();
        foreach($langs as $code => $arr){
            $langids[] = icl_object_id( $_GET['campaign'], 'download', false, $code);
        }
        //print_r($langids);   
        $query->set('meta_query', array(
            array(
                'key' => '_edd_payment_campaign',
                'value' => $langids,
                'compare' => 'IN',
            ),
          ));

	//$query->set('meta_key', '_edd_payment_campaign');
	//$query->set('meta_value', $_GET['campaign'] );
    //$query->query['meta_key'] = '_edd_payment_gateway';
    //$query->query['meta_value'] = $_GET['gateway'];
    return $query;
}
add_action( 'pre_get_posts', 'campaign_filter', 9999 );

?>
