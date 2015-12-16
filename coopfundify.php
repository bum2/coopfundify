<?php

/*
Plugin Name: Coopfundify
Plugin URL: https://github.com/aleph1888/coopfundify
Description: Add some customizations to fundify by Astoundify theme.
Version: 0.3.14159265359
Author: enredaos.net
*/


/**
*
* Main plugin initialization.
*/
function coopfy_load_plugin() {

	//Load plugin files
	include ( __DIR__ . "/shop_worker.php" );

	include ( __DIR__ . "/campaign_payments_column_adder.php");

	include ( __DIR__ . "/profile_campaigns_link_id.php");

        include ( __DIR__ . "/gateway_payments_column_adder.php");

	include ( __DIR__ . "/no_email_on_complete.php");

 	//include ( __DIR__ . "/checkout_language_fix.php");

	//include ( __DIR__ . "/dont_count_abandoned_payments.php");

}
add_action( 'plugins_loaded', 'coopfy_load_plugin' );
