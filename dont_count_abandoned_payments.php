<?php

/**
 *  to check only for completed payments and exclude 'abandoned' payment-status,
 *  hooking the funcion 'get_payments' at easy-digital-downloads/includes/payments/class-payments-query.php
 *
 */

function bum_check_abandoned_payments( $args ) {

  print_r ( $args );

  return $args;
}

add_action('edd_stats_earnings_args', 'bum_chek_abandoned_payments');

?>
