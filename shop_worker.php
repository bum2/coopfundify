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


// Will have to decide between 6.1) or 6.2)

//6.1) Based on 5.4.1
/**
 *
 * This is to provide 'shop_worker" role the ability to manage pending payments once they are paid outside website.
 * Hooking action on EDD->get_payments for roles == "shop_woker"
 * We will:
 * - Remove 'user' filter don't need "payments done by the user" even in not-owned campaigns, but:
 * any payment done over a campaign owned by this user.
 * add proper filter by owned_campaings soy class get_payments method retrieve desidered payments.
 * NOTICE: that we will only check for payments done by "Manual Gateway" wich will only process ONE item on Cart at once.
 */
function myHack( $my_class_payments_query ) {
    // Gatekeeper: This is for user role shop_worker
    if ( ! in_array(wp_current_user->roles, "shop_worker" )    
        return  $my_class_payments_query; 
        
     
    // Get owned campaigns   
   $list_of_owned_campaings = new WP_Query( array(
                'post_type'   => 'download',
                'author' => $user->ID,
                 'post_status' => array( 'publish', 'pending', 'draft' ), //We can  manage here only pending ones if this is needed!!
                'nopaging'    => true
        ) );
    // set a list of required id's to search in _edd_payment_meta->id    
    foreach (  $list_of_owned_campaings as $item ) 
                        $meta_values[] = $item["id"];
    // retrieve our desired payments         
    $owned = 
          //Filter by id
            array(
                'meta_key' => "_edd_payment_meta",
                'meta_query' => array(
                            'meta_key' => 'id'
                            'meta_type => "NUMERIC",
                           'meta_compare' => "IN",
                        'meta_value' =>   $meta_values 
                        )
            ),
        //Filter by gateway type
        array(
             'meta_key' => '_edd_payment_gateway'
             'meta_type => "STRING",
             'meta_value' =>   "Manual" 
            )
        )
    );
 
    unset( $my_class_payments_query->args["user"]) 
    $my_class_payments_query->args = wp_parse_args(  $my_class_payments_query->args, $owned );
}
add_action( "edd_pre_get_payments", "myHack" );


//6.2) Based on 5.4.2
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
function myHack( $my_class_payments_query ) {
    // Gatekeeper: This is for user role shop_worker
    if ( ! in_array(wp_current_user->roles, "shop_worker" )    
        return  $my_class_payments_query; 
    
    // Get owned campaigns   
   $list_of_owned_campaings = new WP_Query( array(
                'post_type'   => 'download',
                'author' => $user->ID,
                'post_status' => array( 'publish', 'pending', 'draft' ), //We can manage here only pending ones if this is needed!!
                'nopaging'    => true
        ) );
    // set a list of required id's to search in _edd_payment_meta->id    
    foreach (  $list_of_owned_campaings as $item ) 
                        $meta_values[] = $item["id"];
    // retrieve our desired payments         
    $owned = new WP_Query( 
        //Filter by id
            array(
                'meta_key' => "_edd_payment_meta",
                'meta_query' => array(
                            'meta_key' => 'id'
                            'meta_type => "NUMERIC",
                           'meta_compare' => "IN",
                        'meta_value' =>   $meta_values 
                        )
            ),
        //Filter by gateway type
        array(
             'meta_key' => '_edd_payment_gateway'
             'meta_type => "STRING",
             'meta_value' =>   "Manual" 
            )
        )
    );
   // override class payments
   $my_class_payments_query->payments =  array();
    
  // fill with new payments
           if ( $owned->have_posts() ) {
                        while ( $owned->have_posts() ) {
                                $owned->the_post();
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
                                $my_class_payments_query->payments[] = apply_filters( 'edd_payment', $details, $payment_id, $this );
                        }
                }
}
add_action( "edd_post_get_payments", "myHack" );
