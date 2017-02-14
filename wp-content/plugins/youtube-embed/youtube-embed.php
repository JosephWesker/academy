<?php
/*
Plugin Name: YouTube Embed
Plugin URI: https://wordpress.org/plugins/youtube-embed/
Description: Embed YouTube Videos in WordPress
Version: 5.0.3
Author: dartiss
Author URI: http://www.artiss.co.uk
Text Domain: youtube-embed
Domain Path: /languages
*/

/**
* YouTube Embed
*
* Main code - include various functions
*
* @package	YouTube-Embed
* @since	2.0
*/

define( 'youtube_embed_version', '5.0.3' );

$functions_dir = plugin_dir_path( __FILE__ ) . 'includes/';

// Include all the various functions

include_once( $functions_dir . 'shared-functions.php' );				// Shared routines

include_once( $functions_dir . 'add-scripts.php' );     				// Add various scripts

include_once( $functions_dir . 'generate-embed-code.php' );				// Generate YouTube embed code

include_once( $functions_dir . 'generate-other-code.php' );				// Generate download & short URLs & thumbnails

include_once( $functions_dir . 'generate-widgets.php' );				// Generate widgets

include_once( $functions_dir . 'api-access.php' );						// Fetch video data from YouTube API

include_once( $functions_dir . 'caching.php' );							// Data caching functions

include_once( $functions_dir . 'shortcodes.php' );						// Shortcodes

include_once( $functions_dir . 'admin-config.php' );					// Administration configuration
?>
