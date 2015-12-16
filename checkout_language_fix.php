<?php

function edd_purchase_top_lang_cookie() {
  global $edd_options;
  global $sitepress;
  
  //echo '<br />COOKIE_COOP: '.get_language_cookie_coop();
  echo '<br />COOKIE: '.$sitepress->get_language_cookie();
  echo '<br />CURRENT: '.$sitepress->get_current_language();

}
add_action( 'edd_purchase_form_top', 'edd_purchase_top_lang_cookie' );


function edd_payment_top_lang_cookie() {
  global $edd_options;
  global $sitepress;
  
  echo '<br />COOKIE: '.$sitepress->get_language_cookie();
  echo '<br />CURRENT: '.$sitepress->get_current_language();//this_lang;
  //echo '<br />DEFAULT: '.$sitepress->get_default_language();
  
  $lang_cook = $sitepress->get_language_cookie();
  $lang_curr = $sitepress->get_current_language();
  if($lang_curr != $lang_cook){
    $sitepress->update_language_cookie($lang_curr);
    //$sitepress->set_language_cookie($lang_curr);
    //set_language_cookie_coop();

    echo '<br />REPARED COOKIE with current lang! '.$lang_curr;
    echo '<br />COOKIE: '.$sitepress->get_language_cookie();
    //echo '<br />COOKIECOOP: '.get_language_cookie_coop();

  }
  echo '<br />&nbsp;<br />';

  //echo '<br />COOKIE: '.$sitepress->get_language_cookie();
  //echo '<br />CURRENT: '.$sitepress->get_current_language();//this_lang;
  //echo '<br />DEFAULT: '.$sitepress->get_default_language();
}

//add_action( 'edd_purchase_form_top', 'show_lang_cookie' );
add_action( 'edd_payment_mode_top', 'edd_payment_top_lang_cookie');


function set_language_cookie_coop() {
	global $edd_options;
	global $sitepress;
		if ( !headers_sent() ) {
			if ( preg_match( '@\.(css|js|png|jpg|gif|jpeg|bmp)@i', basename( preg_replace( '@\?.*$@', '', $_SERVER[ 'REQUEST_URI' ] ) ) ) || isset( $_POST[ 'icl_ajx_action' ] ) || isset( $_POST[ '_ajax_nonce' ] ) || defined( 'DOING_AJAX' ) ) {
				return;
			}
			
			if(!isset($_SERVER[ 'HTTP_HOST' ])) {
				$host =  $_SERVER[ 'SERVER_NAME' ];
				if(isset( $_SERVER[ 'SERVER_PORT' ] ) && $_SERVER[ 'SERVER_PORT' ]!=80) {
					$host .= ':' . $_SERVER[ 'SERVER_PORT' ];
				}
			} else {
				$host =  $_SERVER[ 'HTTP_HOST' ];
			}
			$server_host_name = preg_replace( "@:[443]+([/]?)@", '$1', $host ); //$sitepress->get_server_host_name();
			$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : $server_host_name;
			$cookie_path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
			setcookie( '_icl_current_language_coop', $sitepress->get_current_language(), time() + 86400, $cookie_path, $cookie_domain );

		}
}

function get_language_cookie_coop() {
	global $edd_options;
        global $sitepress;
		static $active_languages = false;
		if ( isset( $_COOKIE[ '_icl_current_language_coop' ] ) ) {
			$lang = substr( $_COOKIE[ '_icl_current_language_coop' ], 0, 10 );
			if(!$active_languages) {
				$active_languages = $sitepress->get_active_languages();
			}
			if ( !isset( $active_languages[ $lang ] ) ) {
				$lang = $sitepress->get_default_language();
			}
		} else {
			$lang = 'x';
		}

		return $lang;
}

?>
