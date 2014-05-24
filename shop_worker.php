<?php


/**
 *
 * Shop_worker EDD->get_payments hook
 *
 * Provide 'shop_worker' role the ability to manage pending payments once they are paid outside website.
 *
 * NOTICE:
 * 1) This we will only check for payments done by "[Manual Gateway](https://github.com/aleph1888/manual_edd_wp_plugin)" wich will only process ONE item on Cart at once.
 * 1.1) This will only show "pending" payments.
 * 2) Campaign_contributor role will be changed to 'shop_worker' when a user creates a campaign.
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

	if ( class_exists('WP_Roles') ) {
	        if ( ! isset( $wp_roles ) )
	                $wp_roles = new WP_Roles();
	}

	if ( is_object( $wp_roles ) ) {

		// Clone campaign_contributor caps
	        $fundify_caps = $wp_roles->get_role('campaign_contributor');
		foreach ( $fundify_caps->capabilities as $key=>$value )
	        	$wp_roles->add_cap( 'shop_worker', $key );

		// add specific payments caps
        	$wp_roles->add_cap( 'shop_worker', 'edit_shop_payments' );
                $wp_roles->add_cap( 'shop_worker', 'edit_others_shop_payments' );

		// remove some  no allowed caps
		$wp_roles->remove_cap( 'shop_worker', 'manage_options' );
                $wp_roles->remove_cap( 'shop_worker', 'publish_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'delete_published_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'edit_posts' );
                $wp_roles->remove_cap( 'shop_worker', 'publish_products' );
                $wp_roles->remove_cap( 'shop_worker', 'upload_files' );

	}
}
register_activation_hook( __FILE__, 'coopfy_add_caps' );


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
add_action( 'user_register', 'coopfy_registration_save', 10, 1 );


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


/**
 *
 * Just hook edd_pre_get_payments to remove fundify filter by user hooking.
 */
function coopfy_pre_get_payments( $class_payments_query ) {

        // Get current user
        $user = wp_get_current_user();

        // Gatekeeper: This is for user role shop_worker
        if ( !  in_array( "shop_worker", $user->roles ) )
                return  $class_payments_query;

	// remove fundify hook that sets author in every query
	add_action( 'pre_get_posts', 'coopfy_set_all_author' );

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

	// Filter payments by owned list
	$owned_payments = array();
	foreach( $class_payments_query->payments as $payment ) {

		$payment_campaigns = edd_get_payment_meta_cart_details( $payment->ID, false );

		$is_gateway = $payment->gateway == "manual_gateway";
		$is_pending = $payment->post_status == "pending";

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
}
add_action( "edd_post_get_payments", "coopfy_post_get_payments" );
