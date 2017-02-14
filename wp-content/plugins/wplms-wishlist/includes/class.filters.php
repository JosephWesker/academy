<?php
/**
 * Filters in WPLMS WISHLIST
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-Wishlist/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Wishlist_Filters{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Wishlist_Filters();
        return self::$instance;
    }

    function __construct(){
        $this->flag = 1;
    	add_filter('wplms_course_details_widget',array($this,'show_wishlist_icon'),10,2);
    	add_filter('the_content',array($this,'collection_view'));
    }

    function show_wishlist_icon($return,$course_id){
    	$return['wishlist']='<li><div class="wishlist_course" data-id="'.$course_id.'">'.__('Wishlist','wplms-wishlist').'</div></li>';
    	return $return;
    }

    function collection_view($content){
    	global $post;
    	if(!is_singular('collection') || empty($this->flag))
    		return $content;
        $this->flag = 0;
    	$courses = get_post_meta(get_the_ID(),'wishlist_course',false);
    	if(!empty($courses)){
    		echo '<div class="row">';
    		foreach($courses as $course_id){
    			echo '<div class="col-md-4 clear3">'.do_shortcode('[course id="'.$course_id.'"]').'</div>';
    		}
    		echo '</div>';
    	}else{
    		echo '<div class="message">'.__('No courses found in collection.','wplms-wishlist').'</div>';
    	}
    	
    	return $content;
    }

}

Wplms_Wishlist_Filters::init();