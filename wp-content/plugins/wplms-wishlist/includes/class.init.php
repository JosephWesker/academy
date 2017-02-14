<?php
/**
 * Initialise WPLMS WISHLIST
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-Wishlist/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Wishlist_Init{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Wishlist_Init();
        return self::$instance;
    }

	private function __construct(){
		$this->get_slugs();
		add_action('init',array($this,'initialise'));
		add_action('wp_footer',array($this,'append_span'));
		add_action('wp_ajax_add_wishlist',array($this,'add_wishlist'));
		add_action('wp_ajax_create_wishlist',array($this,'create_wishlist'));
		add_action('wp_ajax_remove_wishlist',array($this,'remove_wishlist'));
		add_action('init', array( $this, 'install_collections' ) );
		register_activation_hook(__FILE__,array($this,'install_collections'));
		register_activation_hook(__FILE__,'flush_rewrite_rules', 20);
	}

	function get_slugs(){
		$permalinks = get_option( 'wishlist_permalinks' );
		$defaults = array(
			'wishlist_slug'=>'wishlist',
			'collection_slug'=>'collection'
			);
		
		if(empty($permalinks['wishlist_slug'])){
			$permalinks['wishlist_slug'] = $defaults['wishlist_slug'];
		}
		if(empty($permalinks['collection_slug'])){
			$permalinks['collection_slug'] = $defaults['collection_slug'];
		}
		$this->permalinks = $permalinks;
	}

	function install_collections(){
		
		
		register_post_type( 'collection',
			array(
				'labels' => array(
					'name' => __('Collections','wplms-wishlist'),
					'menu_name' => __('Collections','wplms-wishlist'),
					'singular_name' => __('Collection','wplms-wishlist'),
					'all_items' => __('All Collections','wplms-wishlist')
				),
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
	            'has_archive' => false,
				'show_in_menu' => false,
				'exclude_from_search' => true, 
				'show_in_admin_bar' => true,
				'show_in_nav_menus' => true,
				'supports' => array( 'title','author','thumbnail','custom-fields'),
				'hierarchical' => true,
	            'show_in_nav_menus' => false,
				'rewrite' => array( 'slug' => (!empty($this->permalinks)?$this->permalinks['collection_slug']:'collection'),'hierarchical' => true, 'with_front' => false )
			)
		);

	}

	function initialise(){
		if(!is_user_logged_in())
			return;
		$this->security = wp_create_nonce('security');
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
			$this->wishlist_courses = get_user_meta($user_id,'wishlist_course',false);
		}
	}


	function append_span(){
		if(!is_user_logged_in())
			return;
		global $post;
		?>
		<script>
		jQuery(document).ready(function($){
			$('.block.courseitem,.block.modern_course,.single-course #item-header-avatar,#course-list .item-avatar,.wishlist_course').each(function(){
				var wishlist_item = 'wishlist_'+$(this).attr('data-id');
				var id = $(this).attr('data-id');
				if(typeof id !== typeof undefined && id !== false) {
					var value = localStorage.getItem(wishlist_item);
					var wishlist_courses = <?php echo (empty($this->wishlist_courses)?'[]':json_encode($this->wishlist_courses)); ?>;
					var element = '<span class="add_wishlist"></span>';
					
					if(value !=null || jQuery.inArray(id,wishlist_courses) >= 0){
						element = '<span class="add_wishlist active"></span>'; // REquired for ajax calls
	                }
	            	$(this).append(element);
	            }
			});
			$('#buddypress').on('bp_filter_request',function(){ 
				$('#course-list .item-avatar').each(function(){ 
					var $this = $(this);
					var wishlist_item = 'wishlist_'+$this.attr('data-id');
					var id = $this.attr('data-id');
					if(id){
						var value = localStorage.getItem(wishlist_item);
						var wishlist_courses = <?php echo (empty($this->wishlist_courses)?'[]':json_encode($this->wishlist_courses)); ?>;
						var element = '<span class="add_wishlist"></span>';
						
						if(value !=null || jQuery.inArray(id,wishlist_courses) >= 0){
							element = '<span class="add_wishlist active"></span>'; // REquired for ajax calls
		                }
		            	$this.append(element);
		            }
				});
				$('.add_wishlist').on('click',function(){
					var id = $(this).parent().attr('data-id');
					var wishlist_id = 'wishlist_'+id;
					var value = localStorage.getItem(wishlist_id);
					var action;
					if(value !=null){
	                    localStorage.removeItem(wishlist_id);
	                    $(this).removeClass('active');
	                    action = 'remove_wishlist';
	                }else{
	                	localStorage.setItem(wishlist_id,1);
	                	$(this).addClass('active');
	                	action = 'add_wishlist';
	                }
	                <?php 
	                if(is_user_logged_in()){
	                ?>
	                $.ajax({
					    type: "POST",
					    url: ajaxurl,
					    async: true,
					    data: { action: action, 
					            id:id,
					            security: '<?php echo $this->security; ?>'
					          },
					});
					<?php } ?>
				});
			});
			$('.add_wishlist').on('click',function(){
				var id = $(this).parent().attr('data-id');
				var wishlist_id = 'wishlist_'+id;
				var value = localStorage.getItem(wishlist_id);
				var action;
				if(value !=null){
                    localStorage.removeItem(wishlist_id);
                    $(this).removeClass('active');
                    action = 'remove_wishlist';
                }else{
                	localStorage.setItem(wishlist_id,1);
                	$(this).addClass('active');
                	action = 'add_wishlist';
                }
                <?php 
                if(is_user_logged_in()){
                ?>
                $.ajax({
				    type: "POST",
				    url: ajaxurl,
				    async: true,
				    data: { action: action, 
				            id:id,
				            security: '<?php echo $this->security; ?>'
				          },
				});
				<?php } ?>
			});
		});
		</script>
		<style>
		td.instructors img{border-radius:50%;}
		.block,#item-header-avatar,.item-avatar{position:relative;}
		.block .add_wishlist,
		.item-avatar span.add_wishlist{
		    position: absolute;
		    cursor:pointer;
		    top: 10px;text-align: center;
		    right: 10px;
		    background:rgba(255,255,255,0.2);
		    line-height: 0;
		    padding-top:15px;
		    width:30px;height:30px;
		    border-radius:50%;
		}
		.add_wishlist{color: rgba(0,0,0,0.3);}
		.add_wishlist:before{
			content:"\f08a";
			font-family:'fontawesome';
			font-size: 20px;
			color: rgba(0,0,0,0.3);
		}
		.wishlist_course .add_wishlist{float:right;margin-right:10px;}
		.wishlist_course .add_wishlist:before{font-size:16px;}
		.add_wishlist.active,
		.add_wishlist:hover,
		.add_wishlist:hover:before,
		.add_wishlist.active:before{
			color: rgba(245,65,65,1);
		}
		.add_wishlist.active:before{content:"\f004";}
		</style>
		<?php
	}

	function add_wishlist(){
		if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security') ){
			$user_id = get_current_user_id();
			add_user_meta($user_id,'wishlist_course',$_POST['id'],false);
		}
		die();
	}

	function remove_wishlist(){
		if ( isset($_POST['security']) && wp_verify_nonce($_POST['security'],'security') ){
		 	$user_id = get_current_user_id();
			delete_user_meta($user_id,'wishlist_course',$_POST['id']);   
		}
		die();
	}

	function create_wishlist(){
		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || empty($_POST['name'])){
			echo '<div class="message error">'.__('Security error','wplms-wishlist').'</div>';;
			die();
		}
		$check = get_user_meta($user_id,'course_wishlist_'.$_POST['name'],true);
		if(!empty($check)){
			echo '<div class="message error">'.__('Wishlist already exists. Please chose a different name.','wplms-wishlist').'</div>';
			die();
		}
		$user_id = get_current_user_id();
		$wishlist = json_decode(stripslashes($_POST['wishlist']));
		$wishlist_courses = array();
		foreach($wishlist as $course){ 
			$wishlist_courses[] = $course->id;
		}
		if(!empty($wishlist_courses)){
			add_user_meta($user_id,'wishlist_course_list',$_POST['name']);
			add_user_meta($user_id,'course_wishlist_'.$_POST['name'],$wishlist_courses);
			echo '<div class="message success">'.__('Wishlist created','wplms-wishlist').'</div>';;
		}
		die();
	}
}

Wplms_Wishlist_Init::init();	