<?php
/**
 * Actions in WPLMS WISHLIST
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-Wishlist/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Wishlist_Actions{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Wishlist_Actions();
        return self::$instance;
    }

    function __construct(){

      add_action('wp_ajax_wishlist_paged',array($this,'wishlist_paged'));
    	add_action('wplms_wishlist_after_collection_loop',array($this,'delete_collections'),10,1);
		  add_action('wplms_wishlist_after_collection_loop',array($this,'collection_form'));
    	add_action('wp_ajax_create_collection',array($this,'create_collection'));
    	add_action('wp_ajax_remove_collections',array($this,'remove_collections'));
    	add_action('wp_ajax_remove_wishlist_courses',array($this,'remove_wishlist_courses'));
    	add_action('wp_ajax_add_wishlist_courses_to_collection',array($this,'add_wishlist_courses_to_collection'));

      add_action('wp_footer',array($this,'single_collection'));
      add_action('wp_ajax_remove_from_collection',array($this,'remove_from_collection'));
    }

    function wishlist_paged(){
      if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wishlist_security') || !is_user_logged_in() || !is_numeric($_POST['paged'])){
          _e('Security check Failed. Contact Administrator.','wplms-wishlist');
          die();
        }
        $wishlist_course_details = apply_filters('wplms_wishlist_course_details',array(
          'rating'=>__('Rating','wplms-wishlist'),
          'instructors' => __('Instructors','wplms-wishlist'),
        ));
        $posts_per_page = 5;
        if(function_exists('vibe_get_option')){
          $posts_per_page = vibe_get_option('loop_number');
        }
        $user_id=get_current_user_id();
        $new_wishlist = get_user_meta($user_id,'wishlist_course',false);
        $paged = ($_POST['paged']-1)*$posts_per_page;

        foreach($new_wishlist as $i => $cid){
          if($i>=$paged){$wishlist[]=$cid;}          
        }
        $args = array('post_type'=>'course','post__in' => $wishlist, 'orderby' => 'post__in','posts_per_page'=>$posts_per_page);
        $the_query = new WP_Query($args);
        while($the_query->have_posts()):
          $the_query->the_post();
          global $post;

          ?>
          <tr>
            <td style="width:1%;">
              <div class="checkbox">
                <input type="checkbox" id="wishlist_course<?php echo $post->ID; ?>" class="wishlist_course_checkbox" data-id="<?php echo $post->ID; ?>" />
                <label for="wishlist_course<?php echo $post->ID; ?>"></label>
              </div>
            </td>
            <td>
            <?php 
              echo '<a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail( $post->ID, array(32,32));
              echo '<strong>&nbsp;&nbsp;'.$post->post_title.'</strong></a>'; 
              $status = bp_course_get_user_course_status($user_id,$post->ID);
              if(!empty($status)){
                switch($status){
                  case 1:
                    $status = __('Start','wplms-wishlist');
                  break;
                  case 2:
                    $status = __('Continue','wplms-wishlist');
                  break;
                  case 3:
                    $status = __('Under Evaluation','wplms-wishlist');
                  break;
                  case 4:
                    $status = __('Complete','wplms-wishlist');
                  break;
                }
                echo '&nbsp;&nbsp;<span class="label label-success">'.strtoupper($status).'</span>';
              }
            ?>
            </td>
            <?php
              foreach($wishlist_course_details as $key => $detail){
                ?>
                <td class="<?php echo $key; ?>"><?php do_action('wishtlist_course_'.$key,$post); ?></td>
                <?php
              }
            ?>
          </tr>
          <?php
        endwhile;
        ?>
        <?php
        die();
    }

    function create_collection(){
    	
      	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'collection_security') || !is_user_logged_in()){
         	_e('Security check Failed. Contact Administrator.','wplms-wishlist');
         	die();
      	}

      	if(empty($_POST['title'])){
      		_e('Please enter a valid title for collection','wplms-wishlist');
      		die();
      	}

      	$args = apply_filters('wplms_wishlist_collection_insert',array(
      		'post_title'=> wp_strip_all_tags($_POST['title']),
      		'post_type'=>  'collection',
      		'post_status'=>'publish'
      	));

      	$post_id = wp_insert_post($args);
      	if(is_wp_error($post_id)){
      		_e('Unable to create collection, try some other name.','wplms-wishlist');
      		die();
      	}

      	echo $post_id;
      	die();
    }

    function collection_form(){
		?>
		<a id="create_collection_form" class="button small" title="<?php _e('New Collection','wplms-wishlist'); ?>"><span class="fa fa-plus"></span></a>
		<div id="collection_form" style="display:none">
			<input type="text" name="name" id="collection_title" class="form_field" placeholder="<?php _e('Enter a name for the collection','wplms-wishlist'); ?>"/>
			<a id="create_collection" class="button small"><?php _e('Create collection','wplms-wishlist'); ?></a>
			<?php wp_nonce_field('collection_security','collection_security');?>
		</div> 
		<style>#blank_collection{display:none;}</style>
		<script>
		jQuery(document).ready(function($){$('#create_collection_form').click(function(){$(this).find('.fa-plus').toggleClass('fa-minus');$('#collection_form').toggle(200);});
			$('#create_collection').on('click',function(){
				var $this = $(this);
				if($this.hasClass('disabled'))
					return;
				$this.addClass('disabled');
				var title = $('#collection_title').val();
				$this.append('<span class="fa fa-spinner"></span>');
				$.ajax({
					    type: "POST",
					    url: ajaxurl,
					    data: { action: 'create_collection', 
					            security: $('#collection_security').val(),
					            title:title
					        },
			           	cache: false,
                      	success: function (html) {
                      		$this.find('.fa-spinner').remove();
                      		var defaultt = $this.html();
                      		if($.isNumeric(html)) {
                              $this.html('<?php _e('Collection successfully created','wplms-wishlist');?>');
                              $('.message').remove();
                              
                              var cloned = $('#blank_collection').clone();
                              cloned.attr('id','');
                              cloned.find('td.name').text(title);
                              cloned.find('.collection_checkbox').attr('id','collection_'+html);
                              cloned.find('.collection_checkbox').attr('data-id',html);
                              cloned.find('.collection_checkbox+label').attr('for','collection_'+html);
                              $('#list_collections').show();
                              $('#blank_collection').after(cloned);
                            }else{
                            	$this.html(html);	
                            } 
                      		
                      		setTimeout(function(){
                      			$this.html(defaultt);
                      			$('#collection_title').val('');
                              	$('#create_collection_form').trigger('click');
                      		},2000);
                      	}
					});
			});
		});
		</script>
		<?php
	}

	function delete_collections($flag){
		if(empty($flag))
			return;
		?>
		<a id="remove_collections" class="button small" title="<?php _e('Remove Collections','wplms-wishlist'); ?>"><span class="fa fa-trash"></span></a>
		<script>
		jQuery(document).ready(function($){
			$('#remove_collections').on('click',function(){
				var $this = $(this);
				if($this.hasClass('disabled'))
					return;
				$this.addClass('disabled');
				$this.append('<span class="fa fa-spinner"></span>');
				var collections = [];
				$('.collection_checkbox:checked').each(function(){
					var data = {id:$(this).attr('data-id')};
					collections.push(data);
				});
				$.ajax({
				    type: "POST",
				    url: ajaxurl,
				    data: { action: 'remove_collections', 
				            security: $('#collection_security').val(),
				            collections:JSON.stringify(collections)
				          },
		           	cache: false,
		           	success:function(html){
		           		$this.find('.fa-spinner').remove();
                  		var defaultt = $this.html();
                  		if($.isNumeric(html)) {
                          	$this.html(html+' <?php _e('Collections successfully removed','wplms-wishlist');?>');
                          	$('.collection_checkbox:checked').each(function(){
								$(this).closest('tr').hide(50).remove();
							});
                        }else{
                        	$this.html(html);	
                        } 
                  		
                  		setTimeout(function(){
                  			$this.html(defaultt);
                  		},2000);
		           	}
		       	});
			});
		});
		</script>
		<?php
	}

	function remove_collections(){
		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'collection_security') || !is_user_logged_in()){
         	_e('Security check Failed. Contact Administrator.','wplms-wishlist');
         	die();
      	}

      	if(empty($_POST['collections'])){
      		_e('No collections selected.','wplms-wishlist');
      		die();
      	}

      	$collections = json_decode(stripslashes($_POST['collections']));
      	$user_id = get_current_user_id();
      	if(!empty($collections)){
	        foreach($collections as $collection){
	        	if(!empty($collection->id)){
	        		if(get_post_type($collection->id) == 'collection'){
	        			wp_trash_post($collection->id);
	        		}
	        	}
	        }
	    }
        echo 1;
        die();
	}

	function remove_wishlist_courses(){

		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wishlist_security') || !is_user_logged_in()){
         	_e('Security check Failed. Contact Administrator.','wplms-wishlist');
         	die();
      	}

      	if(empty($_POST['courses'])){
      		_e('No courses selected.','wplms-wishlist');
      		die();
      	}
      	$courses = json_decode(stripslashes($_POST['courses']));
      	if(!empty($courses)){
      		$user_id = get_current_user_id();
      		foreach($courses as $course){
				delete_user_meta($user_id,'wishlist_course',$course->id);   
      		}	
      	}
      	echo count($courses);
      	die();
	}

	function add_wishlist_courses_to_collection(){
		  if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wishlist_security') || !is_user_logged_in()){
         	_e('Security check Failed. Contact Administrator.','wplms-wishlist');
         	die();
      	}

      	if(empty($_POST['courses'])){
      		_e('No courses selected.','wplms-wishlist');
      		die();
      	}

      	if(empty($_POST['collection']) || !is_numeric($_POST['collection']) || get_post_type($_POST['collection']) != 'collection'){
      		_e('No collection selected.','wplms-wishlist');
      		die();
      	}
      	$courses = json_decode(stripslashes($_POST['courses']));
      	if(!empty($courses)){
      		$user_id = get_current_user_id();
      		foreach($courses as $course){
				    add_post_meta($_POST['collection'],'wishlist_course',$course->id);   
      		}	
      	}
      	echo count($courses);
      	die();
	}

  function single_collection(){

    if(!is_singular('collection') || !is_user_logged_in())
      return;

    global $post;
    $user_id = get_current_user_id();
    if(current_user_can('manage_options') && $post->post_author != $user_id)
      return;

    wp_nonce_field('collection_security','collection_security');
    ?>
    <style>.remove_from_collection{ position: absolute;cursor:pointer;top: 10px;left: 10px;color: rgba(245,65,65,0.6);font-size: 20px;}.remove_from_collection:hover{color: rgba(245,65,65,1);}</style>
    <script>
      jQuery(document).ready(function($){
        $('.single-collection #content .block.courseitem').each(function(){
          var wishlist_item = 'wishlist_'+$(this).attr('data-id');
          var id = $(this).attr('data-id');
          if(typeof id !== typeof undefined && id !== false) {
              var element = '<span class="remove_from_collection fa fa-times-circle"></span>';
              $(this).append(element);
          }
        });
        $('.remove_from_collection').on('click',function(){
          $(this).parent().hide(100).remove();
          $.ajax({
              type: "POST",
              url: ajaxurl,
              async: true,
              data: { action: 'remove_from_collection', 
                      collection_id: <?php echo get_the_ID(); ?>,
                      id:$(this).parent().attr('data-id'),
                      security: $('#collection_security').val(),
                    },
          });
        });
      });
    </script>
    <?php
  }

  function remove_from_collection(){
    
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'collection_security') || !is_numeric($_POST['collection_id']) || !is_numeric($_POST['id']) || !is_user_logged_in()){
        _e('Security check Failed. Contact Administrator.','wplms-wishlist');
        die();
    }
    
      $user_id = get_current_user_id();
      if(!current_user_can('manage_options') && $user_id != get_post_field('post_author',$_POST['collection_id'])){
        _e('Unable to remove items from collection','wplms-wishlist');
        die();
      }
      delete_post_meta($_POST['collection_id'],'wishlist_course',$_POST['id']);
      die();
  }
}

Wplms_Wishlist_Actions::init();