<?php


/**
 *
 * Shop_worker EDD->get_payments hook
 *
 * Provide 'shop_worker' role the ability to manage pending payments once they are paid outside website.
 *
 * NOTICE:
 * 1) That we will only check for payments done by "[Manual Gateway](https://github.com/aleph1888/manual_edd_wp_plugin)" wich will only process ONE item on Cart at once.
 * 2) Campaign_contributor role will be changed to 'shop_worker' when a user creates a campaign.
 * 3) Payment management will be done on backend wp-admin. While campaign edition can be done in both frontend and backend.
 *
 * @package coopfundify
 * @copyleft Copyleft (l) 2014, Enredaos.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 0
 */


/**
 *
 * Hook fundify wp_insert_user registration to change user role.
 */
function coopfy_registration_save( $user_id ) {

        $user = get_user_by( "ID", $user_id );

        if ( in_array( "campaings_contributor", $user->roles ) )
                 wp_update_user ( $user_id, array( 'roles' => array ("shop_worker") ) );

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

		if ( $is_gateway ) {
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
