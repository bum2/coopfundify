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

    // BUMBUM rebuild func at themes/fundify-child/functions.php
    //rebuild_payment_campaign_meta();


    $ides = array();
    //$langs = icl_get_languages('skip_missing=1');
        //$langids = array();
        //foreach($langs as $code => $arr){
        //    $langid = apply_filters( 'wpml_object_id', $_GET['campaign'], 'download', false, $code);

    foreach($campaigns as $campaign){//$key => $arr){
       $ide = get_original_id($campaign->ID);//apply_filters( 'wpml_object_id', $campaign->ID, 'download', true);//icl_object_id( $campaign->ID, 'download', true );
       //$ide = apply_filters( 'wpml_object_id', $ide, 'download', true); 
       if(!in_array($ide, $ides)){
           $views[ $ide ] = sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'campaign' => $ide, 'paged' => FALSE ) ), $current == $ide ? ' class="current"' : '', basename( get_permalink($ide) ) );//$campaign->post_name );
           $ides[] = $ide;
       }
    }

    // Restore original Post Data
    wp_reset_postdata();

    return $views;
}
add_filter( 'edd_payments_table_views', 'campaigns_payments_table_views');

function get_original_id($id){

  $wpml_is_original = wpml_is_original( $id );

  $is_original = $wpml_is_original['is_original'];
  $original_id = $wpml_is_original['original_ID'];
  if(!$original_id) return $id;
  return $original_id;
}

function campaign_filter( $query ) {
	global $pagenow;//, $post_type;
        if( ! current_user_can( 'manage_shop_settings' ) ) {
		return; // $query; //wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
	}
	if( 'edit.php' != $pagenow || $query->query_vars['post_type'][0] != 'edd_payment' )//|| !is_main_query())
		return; // $query;

	//print_r( $query );
	if( !isset( $_GET['campaign'] ) )
		return;
        //rebuild_payment_campaign_meta();

        $langs = icl_get_languages('skip_missing=0');
        $langids = array();
        foreach($langs as $code => $arr){
            $langid = apply_filters( 'wpml_object_id', $_GET['campaign'], 'download', false, $code) ;//icl_object_id( $_GET['campaign'], 'download', false, $code);
            //if(!$langid) $langid = 
            if($langid && !in_array($langid, $langids)) $langids[] = $langid;
        }
        //print_r($langids);   

        $query->set('meta_query', array(
            array(
                'key' => '_edd_payment_campaign',
                'value' => $langids,
                'compare' => 'IN',
            ),
          ));
	$query->set('post_status', array('any'));//'publish','pending','sended') );

	$args = array( 
            'posts_per_page' => -1, 
            'post_type' => 'edd_payment', 
            'post_status' => array('sended'),//,'publish','pending','sended'), //'any',
            'meta_query' => array(
                array(
                        'key' => '_edd_payment_campaign',
                        'value' => $langids,
                        'compare' => 'IN',
                    )
            ),
        );
	
	//$query = new WP_Query( $args );
	//print_r($query);
	//$query->set('meta_key', '_edd_payment_campaign');
	//$query->set('meta_value', $_GET['campaign'] );
    //$query->query['meta_key'] = '_edd_payment_gateway';
    //$query->query['meta_value'] = $_GET['gateway'];
    //return; // $query;
}
add_action( 'pre_get_posts', 'campaign_filter', 999999);

function guest_reload_hidden_single_post($posts){
    global $wp_query, $wpdb, $pagenow;

    if (!is_user_logged_in()) return $posts;
    //user is not logged

    if(is_single()) return $posts;
    //this is a single post

    //if (!$wp_query->is_main_query()) return $posts;
    //this is the main query

    //if($wp_query->post_count) return $posts;
    //no posts were found

    if( ! current_user_can( 'manage_shop_settings' ) ) {
                return $posts; // $query; //wp_die( __( 'You do not have permission to do shop upgrades', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
        }
    if( 'edit.php' != $pagenow )//|| (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'][0] != 'edd_payment') ) //|| !is_main_query())
                return $posts;

    //print_r($posts);
    if( !isset( $_GET['campaign'] ) )
		return $posts;
    //$wp_query->set('post_status', 'any' );

    $langs = icl_get_languages('skip_missing=0');
    $langids = array();
    foreach($langs as $code => $arr){
        $langid = apply_filters( 'wpml_object_id', $_GET['campaign'], 'download', false, $code) ;//icl_object_id( $_GET['campaign'], 'download', false, $c$
        //if(!$langid) $langid = 
        if($langid && !in_array($langid, $langids)) $langids[] = $langid;
    }
    //print_r($langids);   

        /*$query->set('meta_query', array(
            array(
                'key' => '_edd_payment_campaign',
                'value' => $langids,
                'compare' => 'IN',
            ),
          ));*/

    $args = array( 
	'posts_per_page' => -1, 
	'post_type' => 'edd_payment', 
	'post_status' => array('any'), //sended'),//,'publish','pending','sended'), //'any',
	'meta_query' => array(
		array(
			'key' => '_edd_payment_campaign',
			'value' => $langids,
			'compare' => 'IN',
		)
	),
    );
    //$sec_query = new WP_Query( $args );
    
    //print_r($sec_query);//->request);
    //$posts = get_posts( $args );
    //print_r($posts);
    //$posts = $wpdb->get_results($sec_query->request);

    return $posts;
}
//reload hidden posts
add_filter('the_posts','guest_reload_hidden_single_post');

?>
