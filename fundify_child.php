//https://github.com/bum2/fundify-child/blob/master/header.php#L42
function coopfy_header() {
    echo '<div class="pastilla"><h2>' .  __('NEW SITE','fundify') . '</h2></div>';
}
add_action( 'icl_language_selector', 'coopfy_header' )

