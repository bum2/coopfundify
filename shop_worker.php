<?php


/**
 *
 * Shop_worker EDD->get_payments hook
 *
 * Provide 'shop_worker' role the ability to manage pending payments once they are paid outside website.
 *
 * NOTICE:
 * 1) This we will only check for payments done by "[Manual Gateway](https://github.com/aleph1888/manual_edd_wp_plugin)" wich will only process ONE item on Cart at once.
 * 1.1) This will only show "pending" payments. REMOVED
 * 2) Campaign_contributor role will be changed to 'shop_worker' hooking wp_insert user.
 * 3) Payment management will be done on backend wp-admin. While campaign edition can be done in both frontend and backend.
 *
 * @package coopfundify
 * @copyleft Copyleft (l) 2014, Enredaos.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 0
 */


 /*
 * Redirect users who shouldn't be here.
 * Hook fundify: _prevent_admin_access()
 */
function coopfy_prevent_admin_access() {

	remove_action( 'admin_init', 'atcf_prevent_admin_access', 1000 );
        if (
                // Look for the presence of /wp-admin/ in the url
                stripos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) !== false
                &&
                // Allow calls to async-upload.php
                stripos( $_SERVER['REQUEST_URI'], 'async-upload.php' ) == false
                &&
                // Allow calls to admin-ajax.php
                stripos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) == false
        ) {
		global $current_user;

                if ( in_array( "campaign_contributor" , $current_user->roles ) ) {
                        wp_safe_redirect( home_url() );
                        exit();
                }
        }
}
add_action( 'admin_init', 'coopfy_prevent_admin_access' );


/**
 *
 * Add shop_worker role, campaign_contributor capabilities
 */
function coopfy_add_caps() {

	global $wp_roles;
		
	if ( !isset($wp_roles) ) return;

	if ( class_exists('WP_Roles') ) {
	        if ( ! isset( $wp_roles ) )
	                $wp_roles = new WP_Roles();
	}

	if ( is_object( $wp_roles ) ) {
		$prev_role_caps = $wp_roles->get_role('shop_worker');
		//foreach ( $prev_role_caps->capabilities as $key=>$value )
                //        $wp_roles->remove_cap( 'shop_worker', $key );
		
		$wp_roles->remove_role('shop_worker');
		$wp_roles->add_role('shop_worker', __('Shop Worker','coopfundify') );

		// Clone campaign_contributor caps
	        $fundify_caps = $wp_roles->get_role('shop_manager');//campaign_contributor');
		foreach ( $fundify_caps->capabilities as $key=>$value )
	        	$wp_roles->add_cap( 'shop_worker', $key );

		// add specific payments caps
        	/*$wp_roles->add_cap( 'shop_worker', 'edit_shop_payments' );
                $wp_roles->add_cap( 'shop_worker', 'edit_others_shop_payments' );
		$wp_roles->add_cap( 'shop_worker', 'view_shop_payment_stats' );
		$wp_roles->add_cap( 'shop_worker', 'view_shop_sensitive_data' );
		$wp_roles->add_cap( 'shop_worker', 'read_private_shop_payments' );
		$wp_roles->add_cap( 'shop_worker', 'read_shop_payment' );
		$wp_roles->add_cap( 'shop_worker', 'view_product_stats' );
		$wp_roles->add_cap( 'shop_worker', 'view_shop_discount_stats' );
		$wp_roles->add_cap( 'shop_worker', 'view_shop_reports' );
		$wp_roles->add_cap( 'shop_worker', 'read_shop_discount' );
		$wp_roles->add_cap( 'shop_worker', 'read_private_shop_discounts' );
		$wp_roles->add_cap( 'shop_worker', 'read_product' );*/
		//$wp_roles->add_cap( 'shop_worker', 'wpml_manage_navigation' );
		//$wp_roles->add_cap( 'shop_worker', 'wpml_manage_translation_management' );
                $wp_roles->add_cap( 'shop_worker', 'submit_campaigns' );
                $wp_roles->add_cap( 'shop_worker', 'view_shop_reports' );
                //$wp_roles->add_cap( 'shop_worker', 'translate' );
                
		// remove some  no allowed caps
		$wp_roles->remove_cap( 'shop_worker', 'edit_others_products' );
		$wp_roles->remove_cap( 'shop_worker', 'manage_shop_settings' );
		
		$wp_roles->remove_cap( 'shop_worker', 'edit_posts' );
		//$wp_roles->remove_cap( 'shop_worker', 'edit_others_posts');
		$wp_roles->remove_cap( 'shop_worker', 'publish_posts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_published_posts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_private_posts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_posts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_published_posts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_private_posts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_others_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_others_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'read_private_posts' );

		$wp_roles->remove_cap( 'shop_worker', 'edit_pages' );
		$wp_roles->remove_cap( 'shop_worker', 'publish_pages');
		$wp_roles->remove_cap( 'shop_worker', 'edit_published_pages' );
		$wp_roles->remove_cap( 'shop_worker', 'edit_private_pages' );
                $wp_roles->remove_cap( 'shop_worker', 'edit_others_pages' );
                $wp_roles->remove_cap( 'shop_worker', 'read_private_pages' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_published_pages' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_pages' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_others_pages' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_private_pages' );
                
                //$wp_roles->remove_cap( 'shop_worker', 'read' );
		$wp_roles->remove_cap( 'shop_worker', 'manage_categories');
		$wp_roles->remove_cap( 'shop_worker', 'export');
		$wp_roles->remove_cap( 'shop_worker', 'import');
		$wp_roles->remove_cap( 'shop_worker', 'manage_links');
		$wp_roles->remove_cap( 'shop_worker', 'view_shop_reports');
		$wp_roles->remove_cap( 'shop_worker', 'edit_product_terms');
		$wp_roles->remove_cap( 'shop_worker', 'delete_product_terms');
		$wp_roles->remove_cap( 'shop_worker', 'manage_product_terms');
		$wp_roles->remove_cap( 'shop_worker', 'unfiltered_html');

		$wp_roles->remove_cap( 'shop_worker', 'assign_shop_discounts_terms');
		$wp_roles->remove_cap( 'shop_worker', 'delete_others_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_private_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_published_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_shop_discount');
		$wp_roles->remove_cap( 'shop_worker', 'delete_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'delete_shop_discount_terms');
		$wp_roles->remove_cap( 'shop_worker', 'edit_others_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_private_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_published_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'edit_shop_discount');
		$wp_roles->remove_cap( 'shop_worker', 'edit_shop_discount_terms');
		$wp_roles->remove_cap( 'shop_worker', 'manage_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'manage_shop_discount_terms');
		$wp_roles->remove_cap( 'shop_worker', 'publish_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'read_private_shop_discounts');
		$wp_roles->remove_cap( 'shop_worker', 'read_shop_discount');

		$wp_roles->remove_cap( 'shop_worker', 'manage_options' );
                $wp_roles->remove_cap( 'shop_worker', 'publish_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_published_posts' );

                //$wp_roles->remove_cap( 'shop_worker', 'edit_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'publish_products' );
		$wp_roles->remove_cap( 'shop_worker', 'delete_others_products');
	        $wp_roles->remove_cap( 'shop_worker', 'edit_others_products');	
		$wp_roles->remove_cap( 'shop_worker', 'delete_published_products');
		$wp_roles->remove_cap( 'shop_worker', 'delete_private_products');
		$wp_roles->remove_cap( 'shop_worker', 'read_private_products');
                //$wp_roles->remove_cap( 'shop_worker', 'upload_files' );
		//$wp_roles->remove_cap( 'shop_worker', '

		$wp_roles->remove_cap( 'shop_worker', 'edit_others_attachments' );
		$wp_roles->remove_cap( 'shop_worker', 'delete_others_attachments' );
		$wp_roles->remove_cap( 'shop_worker', 'read_others_attachments' );

		$wp_roles->remove_cap( 'shop_worker', 'delete_products' );
	}
}
coopfy_add_caps();
register_activation_hook( __FILE__, 'coopfy_add_caps' );

#add_action( 'init', 'coopfy_add_caps', 10 );


/**
 *
 * Hook fundify wp_insert_user registration to change user role.
 */
function coopfy_registration_save( $user_id ) {

        $user = get_user_by( "id", $user_id );

        if ( in_array( "campaign_contributor", $user->roles ) ) {

        	$user->remove_role( 'campaign_contributor' );
        	$user->add_role( 'shop_worker' );

	}

}
//add_action( 'user_register', 'coopfy_registration_save', 10, 1 );


/**
 *
 * Removes only author on querys fundify restriction, to be called when needed
 * SEE: appthemer-crowdfunding/includes/roles.php | alt_set_only_author()
 * THINKING: Maybe we can do the same by removing/adding add_action( 'pre_get_posts', 'alt_set_only_author' );
 *
 */
function coopfy_set_all_author( $wp_query ) {
        global $current_user;

        if ( in_array( "shop_worker", $current_user->roles ) )
                $wp_query->set( 'author', '' );

}

function coopfy_parse_query( $wp_query ){
	if( $wp_query->query['post_type'][0] == 'edd_payment'){ //'download'){
		echo '<br>WPQUERY: ';
		print_r($wp_query);
	}
}
//add_action( 'parse_query', 'coopfy_parse_query' );
function coopfy_request( $request ){
	echo '<br>REQUEST: ';
	print_r( $request );
	return $request;
}
//add_action( 'request', 'coopfy_request');

/**
 *
 * Just hook edd_pre_get_payments to remove fundify filter by user hooking.
 * May be this can be optimized by https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/payments/class-payments-query.php#L412
 */
function coopfy_pre_get_payments( $class_payments_query ) {

        // Get current user
        $user = wp_get_current_user();

        // Gatekeeper: This is for user role shop_worker
        if ( !  in_array( "shop_worker", $user->roles ) )
                return  $class_payments_query;
	
	// Get owned campaigns
        /*$owned_campaigns = new WP_Query( array(
                'post_type'     => 'download',
                'author'        => $user->ID,
                'post_status'   => array( 'publish' ),
                'nopaging'      => true
        ) );
        $owned_campaigns_ids = array();
        if ( $owned_campaigns->have_posts() ) {
                while ( $owned_campaigns->have_posts() ) {
                        $owned_campaigns->the_post();
                        array_push( $owned_campaigns_ids, get_post()->ID );
                }
        }*/
	//$class_payments_query->args['download'] = "'".$owned_campaigns_ids[0].','.$owned_campaigns_ids[1]."'";
	$class_payments_query->args['number'] = 10000;
	//echo '<br>PRE_QUERY: ';
        //print_r($class_payments_query);
	
	// remove fundify hook that sets author in every query
	add_action( 'pre_get_posts', 'coopfy_set_all_author' );
	//add_action( 'parse_query', 'coopfy_parse_query' );
}
add_action( "edd_pre_get_payments", "coopfy_pre_get_payments" );


/**
 *
 * This is to provide 'shop_worker" role the ability to manage pending payments once they are paid outside website.
 * Hooking action on EDD->get_payments for roles == "shop_woker"
 * We will:
 * - Remove retrieved payments because we don't need "payments done by the user" even in not-owned campaigns, but:
 * any payment done over a campaign owned by this user.
 * Retrieve desidered payments and set to class payments attrib.
 * NOTICE: that we will only check for payments done by "Manual Gateway" wich will only process ONE item on Cart at once.
 */
function coopfy_post_get_payments( $class_payments_query ) {

	// Get current user
        $user = wp_get_current_user();

        // Gatekeeper: This is for user role shop_worker
        if ( !  in_array( "shop_worker", $user->roles ) )
                return  $class_payments_query;

	// Remove our nofiltering hook setted in coopfy_pre_get_payments
	remove_action( 'pre_get_posts', 'coopfy_set_all_author' );

	// Get owned campaigns
        $owned_campaigns = new WP_Query( array(
                'post_type'     => 'download',
                'author'        => $user->ID,
                'post_status'   => array( 'publish' ),
                'nopaging'      => true
        ) );
        $owned_campaigns_ids = array();
        if ( $owned_campaigns->have_posts() ) {
                while ( $owned_campaigns->have_posts() ) {
                        $owned_campaigns->the_post();
                        array_push( $owned_campaigns_ids, get_post()->ID );
                }
        }
        
        // Remove our nofiltering hook setted in coopfy_pre_get_payments
        //remove_action( 'pre_get_posts', 'coopfy_set_all_author' );
        //echo 'USERID: '.$user->ID;
	//echo '<br>IDS: '.count($owned_campaigns_ids);
        //print_r($owned_campaigns->query_vars);
	//echo '<br>QUERY: '.count($class_payments_query->payments);
        //print_r($class_payments_query->payments);

	// Filter payments by owned list
	$owned_payments = array();
	foreach( $class_payments_query->payments as $payment ) {

		$payment_campaigns = edd_get_payment_meta_cart_details( $payment->ID, false );

		$is_gateway = ($payment->gateway == "manual_gateway" || $payment->gateway == "fairbill");
		$is_pending = true; // Removed: $payment->post_status == "pending";

		if ( $is_gateway && $is_pending ) {
			foreach ( $payment_campaigns as $campaign ) {
				$is_owned = in_array( $campaign["id"], $owned_campaigns_ids );
				if ( $is_owned ) {
					$owned_payments[] = $payment;
					continue;
				}
			}
		}
	}

	// override class payments
	$class_payments_query->payments = $owned_payments;
	//print_r( $class_payments_query->payments );
}
add_action( "edd_post_get_payments", "coopfy_post_get_payments" );


function no_edit_fairbill_payments( $go, $payment_id, $status, $old_status ){
    // Get current user
    $user = wp_get_current_user();

    // Gatekeeper: This is for user role shop_worker
    if ( $user && !in_array( "shop_worker", $user->roles ) )
         return $go;

    if( edd_get_payment_gateway( $payment_id ) == "manual_gateway" ){
        return $go;
    } else {
        return false;
    }
}
add_filter( "edd_should_update_payment_status", "no_edit_fairbill_payments", 10, 4 );
