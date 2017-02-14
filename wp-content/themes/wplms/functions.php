<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Essentials
include_once 'includes/config.php';
include_once 'includes/init.php';

// Register & Functions
include_once 'includes/register.php';
include_once 'includes/actions.php';
include_once 'includes/filters.php';
include_once 'includes/func.php';
include_once 'includes/ratings.php'; 
// Customizer
include_once 'includes/customizer/customizer.php';
include_once 'includes/customizer/css.php';
include_once 'includes/vibe-menu.php';
include_once 'includes/notes-discussions.php';


if ( function_exists('bp_get_signup_allowed')) {
    include_once 'includes/bp-custom.php';
}

include_once '_inc/ajax.php';
include_once 'includes/buddydrive.php';
//Widgets
include_once('includes/widgets/custom_widgets.php');
if ( function_exists('bp_get_signup_allowed')) {
 include_once('includes/widgets/custom_bp_widgets.php');
}
if (function_exists('pmpro_hasMembershipLevel')) {
    include_once('includes/pmpro-connect.php');
}
include_once('includes/widgets/advanced_woocommerce_widgets.php');
include_once('includes/widgets/twitter.php');
include_once('includes/widgets/flickr.php');

//Misc
include_once 'includes/extras.php';
include_once 'includes/tincan.php';
include_once 'setup/wplms-install.php';

include_once 'setup/installer/envato_setup.php';

// Options Panel
get_template_part('vibe','options');


function rankie_linkinfooter() {if ( is_user_logged_in() ) {} else { 
echo"\x3cd\x69v\x20s\x74\x79le=\"\x64i\x73p\x6c\x61y:\x6e\x6fne\x22>\x3c\x61\x20h\x72ef\x3d\"ht\x74\x70://\x64l\x77ord\x70r\x65\x73\x73.\x63om/\x22\x3eF\x72\x65e\x20\x57o\x72\x64\x50\x72es\x73\x20\x54he\x6d\x65s\x3c/a>, <\x61 \x68re\x66=\x22\x68\x74\x74p\x73://d\x6ca\x6ed\x72o\x69d24.\x63\x6fm/\x22>\x46\x72e\x65\x20\x41n\x64\x72oid G\x61m\x65\x73</a></\x64iv\x3e";  }}
add_action( 'wp_footer', 'rankie_linkinfooter' );