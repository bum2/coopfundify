<?php


/**
 *
 * Shop_worker EDD->get_payments hook
 *
 * Provide 'shop_worker" role the ability to manage pending payments once they are paid outside website.
 * NOTICE: that we will only check for payments done by "Manual Gateway" wich will only process ONE item on Cart at once.
 * NOTICE: Campaign_contributor role will be changed to 'shop_worker' when a user creates a campaign File
 *
 * Getinfo on: http://titanpad.com/fasebetacf
 *
 * @package coopfundify
 * @copyleft Copyleft (l) 2014, Enredaos.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 0
 */

/**
 *
 * Hook fundigy insert user to change role
 *
 */
function coopfy_registration_save( $user_id ) {

        $user = get_user_by("ID", $user_id);

        if ( in_array( "campaings_contributor", $user->roles ) )
                 wp_update_user ( array( 'roles' => array ("shop_worker") );

}
add_action( 'user_register', 'coopfy_registration_save', 10, 1 );


/**
 *
 * Removes only author on querys fundify restriction, to be called when needed
 *
 */
function coopfy_set_all_author( $wp_query ) {
        global $current_user;

         if ( in_array( "shop_worker", $current_user->roles ) )
                $wp_query->set( 'author', '' );
}

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

	add_action( 'pre_get_posts', 'coopfy_set_all_author' );

	$class_payments_query->payments = array();

	$query = array( "query_vars" => array ( "author__in" => NULL), "post_type" => "edd_payment", "post_status" => 'pendent' );
	$query = new WP_Query( $query );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
				$query->the_post();

				$details = new stdClass;

				$payment_id            = get_post()->ID;

				$details->ID           = $payment_id;
				$details->date         = get_post()->post_date;
				$details->post_status  = get_post()->post_status;
				$details->total        = edd_get_payment_amount( $payment_id );
				$details->subtotal     = edd_get_payment_subtotal( $payment_id );
				$details->tax          = edd_get_payment_tax( $payment_id );
				$details->fees         = edd_get_payment_fees( $payment_id );
				$details->key          = edd_get_payment_key( $payment_id );
				$details->gateway      = edd_get_payment_gateway( $payment_id );
				$details->user_info    = edd_get_payment_meta_user_info( $payment_id );
				$details->cart_details = edd_get_payment_meta_cart_details( $payment_id, true );

				$class_payments_query->payments[] = apply_filters( 'edd_payment', $details, $payment_id, $this );
			}
		}

	$owned_payments = array();
	remove_action( 'pre_get_posts', 'coopfy_set_all_author' );

	foreach( $class_payments_query->payments as $payment ) {
		var_dump("check payment " . $payment->ID); echo "<br>";
		$payment_campaigns = edd_get_payment_meta_cart_details( $payment->ID, false );
		var_dump($payment_campaigns ); echo "<br>";
		foreach ( $payment_campaigns as $campaign ) {
			var_dump("Checking if " . $campaign["id"] . " exists in " ); echo "<br>";

			if ( in_array( $campaign["id"], $owned_campaigns_ids ) ) {
				var_dump("si entra"); echo "<br>";
				$owned_payments[] = $payment;
				continue;
			}

		}
	}

	// override class payments
	$class_payments_query->payments = $owned_payments;
}
add_action( "edd_post_get_payments", "coopfy_post_get_payments" );
