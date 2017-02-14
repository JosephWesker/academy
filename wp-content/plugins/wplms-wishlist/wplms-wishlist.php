<?php
/*
Plugin Name: WPLMS Wishlist
Plugin URI: http://www.vibethemes.com/
Description: This plugin adds a Wishlist feature in WPLMS LMS Platform
Author: Mr.Vibe
Version: 1.0
Author URI: http://www.vibethemes.com/
Text Domain: wplms-wishlist
Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) exit; 
/*  Copyright 2013 VibeThemes  (email: vibethemes@gmail.com) */

include_once 'includes/class.updater.php';
include_once 'includes/class.config.php';
include_once 'includes/class.admin.php';
include_once 'includes/class.init.php';
include_once 'includes/class.wishlist.php';
include_once 'includes/class.actions.php';
include_once 'includes/class.filters.php';

add_action('plugins_loaded','wplms_wishlist_translations');
function wplms_wishlist_translations(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-wishlist');
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-wishlist', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-wishlist', $mofile_global );
    } else {
        load_textdomain( 'wplms-wishlist', $mofile_local );
    }  
}


function Wplms_Wishlist_Plugin_updater() {
    $license_key = trim( get_option( 'wplms_wishlist_license_key' ) );
    $edd_updater = new Wplms_Wishlist_Plugin_Updater( 'http://vibethemes.com', __FILE__, array(
            'version'   => '1.0',               
            'license'   => $license_key,        
            'item_name' => 'WPLMS WISHLIST',    
            'author'    => 'VibeThemes' 
        )
    );
}
add_action( 'admin_init', 'Wplms_Wishlist_Plugin_updater', 0 );