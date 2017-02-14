<?php
/*
Plugin Name: WPLMS Front End
Plugin URI: http://www.Vibethemes.com
Description: FRONT END Content Creation plugin for WPLMS 
Version: 2.1.1
Author: VibeThemes
Author URI: http://www.vibethemes.com
License: as Per Themeforest GuideLines
Text Domain: wplms-front-end
*/
/*
Copyright 2014  VibeThemes  (email : vibethemes@gmail.com)

WPLMS Front End is a plugin made for WPLMS Theme. This plugin is only meant to work with WPLMS and can only be used with WPLMS.
WPLMS Front End program is not a free software; you can not copy, redistribute it and/or modify the code without permission from VibeThemes.
Please consult VibeThemes.com or email us at vibethemes@gmail.com/support@vibethemes.com for more information.
*/

if ( !defined( 'ABSPATH' ) ) exit;

 

if ( ! class_exists( 'WPLMS_Front_End' ) ) {

    //require_once( 'includes/class_wplms_front_end.php' );
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class.generate_fields.php' );
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class.process_fields.php' );
    require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class_wplms_front_end.php' );
    WPLMS_Front_End::instance();
}

if ( ! class_exists( 'WP_Front_End_Editor' ) ) {
    $disable = get_option('wplms');
    if(!isset($disable['disable_front_end_editor']) || !$disable['disable_front_end_editor']){ // 0 to enable, blank or 1 to disable
        require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'front-end-editor/class-wp-front-end-editor.php' );
        WP_Front_End_Editor::instance();
    }
}



add_action( 'init', 'wplms_front_end_update' );
function wplms_front_end_update() {
	/* Load Plugin Updater */
	require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'autoupdate/class-plugin-update.php' );

	/* Updater Config */
	$config = array(
		'base'      => plugin_basename( __FILE__ ), //required
		'dashboard' => true,
		'repo_uri'  => 'http://www.vibethemes.com/',  //required
		'repo_slug' => 'wplms-front-end',  //required
	);

	/* Load Updater Class */
	new WPLMS_Front_End_Auto_Update( $config );
}

add_action('wp_enqueue_scripts','wplms_front_end_enqueue_scripts');
function wplms_front_end_enqueue_scripts(){
        if(function_exists('vibe_get_option')){
            $edit_course = vibe_get_option('create_course');
            
            if(function_exists('icl_object_id'))
                $edit_course = icl_object_id($edit_course, 'page', true);

            if(is_numeric($edit_course) && (is_page($edit_course))){ //adhoc fix
                
            }else{
                global $wp_query;
                if((!isset($_GET['edit']) && !isset($wp_query->query_vars['edit'])) || !current_user_can('edit_posts'))
                    return;
            }
        }
        wplms_front_end_loadscripts();
}


function wplms_front_end_loadscripts(){
        wp_enqueue_media();
        wp_enqueue_style( 'wplms-front-end-css', plugins_url( 'assets/css/wplms_front_end.min.css' , __FILE__ ),array(),'2.0.9');
        wp_enqueue_script( 'wplms-front-end-js', plugins_url( 'assets/js/wplms_front_end.min.js' , __FILE__ ), array( 'bp-course-js','jquery-ui-core','jquery-ui-sortable','jquery-ui-slider','jquery-ui-datepicker','bp-confirm' ) ,'2.0.9');
        
        $translation_array = array(
            'course_title' => __( 'Please change the course title','wplms-front-end' ), 
            'create_course_confirm' => __( 'This will create a new course in the site, do you want to continue ?','wplms-front-end' ), 
            'create_course_confirm_button' => __('Yes, create a new course','wplms-front-end'),
            'save_course_confirm' => __( 'This will overwrite the previous course settings, do you want to continue ?','wplms-front-end' ), 
            'save_course_confirm_button' => __('Save course','wplms-front-end'),
            'create_unit_confirm' => __( 'This will create a new unit in the site, do you want to continue ?','wplms-front-end' ), 
            'create_unit_confirm_button' => __('Yes, create a new unit','wplms-front-end'),
            'save_unit_confirm' => __( 'This will overwrite the existing unit settings, do you want to continue ?','wplms-front-end' ), 
            'save_unit_confirm_button' => __('Yes, save unit settings','wplms-front-end'),
            'create_question_confirm' => __( 'This will create a new question in the site, do you want to continue ?','wplms-front-end' ), 
            'create_question_confirm_button' => __('Yes, create a new question','wplms-front-end'),
            'create_quiz_confirm' => __( 'This will create a new quiz in the site, do you want to continue ?','wplms-front-end' ), 
            'create_quiz_confirm_button' => __('Yes, create a new quiz','wplms-front-end'),
            'save_quiz_confirm' => __( 'This will overwrite the existing quiz settings, do you want to continue ?','wplms-front-end' ), 
            'save_quiz_confirm_button' => __('Yes, save quiz settings','wplms-front-end'),
            'delete_confirm' => __( 'This will delete the element from your site, do you want to continue ?','wplms-front-end' ), 
            'delete_confirm_button' => __('Continue','wplms-front-end'),
            'save_confirm' => __( 'This will overwrite the previous settings, do you want to continue ?','wplms-front-end' ), 
            'save_confirm_button' => __('Save','wplms-front-end'),
            'create_assignment_confirm' => __( 'This will create a new assignment in the site, do you want to continue ?','wplms-front-end' ), 
            'create_assignment_confirm_button' => __('Yes, create a new assignment','wplms-front-end'),
            'course_offline' => __('Are you sure you want to take course offline, this will remove the course from course directory and it will not be visible to your students','wplms-front-end'),
            'delete_course_confirm' => __('Are you sure you want to delete this course ?','wplms-front-end'),
            'delete_button' => __('DELETE COURSE','wplms-front-end'),
            'create_group_confirm'=>__( 'This will create a new group in the site, do you want to continue ?','wplms-front-end' ), 
            'create_forum_confirm'=>__( 'This will create a new forum in the site, do you want to continue ?','wplms-front-end' ),
            );
        wp_localize_script( 'wplms-front-end-js', 'wplms_front_end_messages', $translation_array );
}
add_action( 'plugins_loaded', 'wplms_front_end_language_setup' );
function wplms_front_end_language_setup(){
    $locale = apply_filters("plugin_locale", get_locale(), 'wplms-front-end');
    
    $lang_dir = dirname( __FILE__ ) . '/languages/';
    $mofile        = sprintf( '%1$s-%2$s.mo', 'wplms-front-end', $locale );
    $mofile_local  = $lang_dir . $mofile;
    $mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;

    if ( file_exists( $mofile_global ) ) {
        load_textdomain( 'wplms-front-end', $mofile_global );
    } else {
        load_textdomain( 'wplms-front-end', $mofile_local );
    }   
}

?>
