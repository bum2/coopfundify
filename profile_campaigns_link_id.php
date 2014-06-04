<?php

/**
 *  to override a function at appthemer-crowdfunding/includes/shortcode-profile.php
 *  just to add an id to the 'your-campaigns' h3
 * 
 */
 
function coopfy_add_id_to_your_campaigns( $campaign ) {

    $H3withoutID = '<h3 class="atcf-profile-section your-campaigns">';
    $H3withID =  '<h3 class="atcf-profile-section your-campaigns" id="campaigns">';
    $out = ob_get_clean();
    $out = str_replace($H3withoutID, $H3withID, $out);
    echo $out;
    
}
add_action( 'atcf_profile_campaign_before', 'coopfy_add_id_to_your_campaigns');
 

?>
