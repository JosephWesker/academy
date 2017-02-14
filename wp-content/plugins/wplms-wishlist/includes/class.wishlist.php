<?php
/**
 * WPLMS WISHLIST Component
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-Wishlist/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Wishlist_Component{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Wishlist_Component();
        return self::$instance;
    }

	private function __construct(){
		$this->initialise();
		add_action( 'bp_setup_nav', array($this,'add_wishlist'), 100 ); //Priority must be higher than 10
		add_action('wplms_logged_in_top_menu',array($this,'loggedin_menu'));
		add_action('wishtlist_course_instructors',array($this,'instructor'),1);
		add_action('wishtlist_course_rating',array($this,'rating'),1);
		//add_action('wishtlist_course_actions',array($this,'actions'),1);
	}
	

	function initialise(){
		$init = Wplms_Wishlist_Init::init();

		$permalinks = $init->permalinks;
		if(empty($permalinks)){
			$permalinks = array(
				'wishlist_slug'=>'wishlist',
				'collection_slug'=>'collection'
				);
		}
		$this->slug = $permalinks['wishlist_slug'];
		$this->collections_slug = $permalinks['collection_slug'];

	}

	function loggedin_menu($menu){
		
		$menu['wishlist']=array(
                          'icon' => 'icon-heart',
                          'label' => __('Wishlist','wplms-wishlist'),
                          'link' => bp_loggedin_user_domain().$this->slug,
                          );
		return $menu;
	}
	function add_wishlist() {
		global $bp;
		$access = apply_filters('wplms_wishlist_access',false);

	    bp_core_new_nav_item( array( 
	        'name' => __('Wishlist','wplms-wishlist'),
	        'slug' => $this->slug, 
	        'item_css_id' => 'wishlist',
	        'screen_function' => array($this,'show_screen'),
	        'default_subnav_slug' => 'home', 
	        'position' => 55
	    ) );


	    bp_core_new_subnav_item( array(
				'name' 		  => __( 'Wishlist', 'wplms-wishlist' ),
				'slug' 		  => 'home',
				'parent_slug' => $this->slug,
	        	'parent_url' => $bp->displayed_user->domain.$this->slug.'/',
				'screen_function' => array($this,'show_wishlist'),
			) );

	    bp_core_new_subnav_item( array(
				'name' 		  => __( 'Collections', 'wplms-wishlist' ),
				'slug' 		  => $this->collections_slug,
				'parent_slug' => $this->slug,
	        	'parent_url' => $bp->displayed_user->domain.$this->slug.'/',
				'screen_function' => array($this,'show_collections'),
			) );
	   
	}

	function show_wishlist(){
		add_action( 'bp_template_title', array($this,'wishlist_title'));
		add_action( 'bp_template_content', array($this,'wishlist_content'));
    	bp_core_load_template( 'members/single/plugins');
	}

	function show_collections(){
		add_action( 'bp_template_title', array($this,'collections_title'));
		add_action( 'bp_template_content', array($this,'collections_content'));
    	bp_core_load_template( 'members/single/plugins');
	}

	function wishlist_title(){
		echo '<h3 class="heading"><span>'.__('My Wishlist','wplms-wishlist').'</span></h3>';
	}

	function collections_title(){
		echo '<h3 class="heading"><span>'.__('Collections','wplms-wishlist').'</span></h3>';
	}

	function wishlist_content(){
		$user_id = bp_displayed_user_id();
		$wishlist = get_user_meta($user_id,'wishlist_course',false);
		if(empty($wishlist)){
			echo '<div class="message">';
			_e('No Courses in Wishlist.','wplms-wishlist');
			echo '</div>';
		}else{
			$posts_per_page = 5;
	        if(function_exists('vibe_get_option')){
	          $posts_per_page = vibe_get_option('loop_number');
	        }
			$args = array('post_type'=>'course','post__in' => $wishlist , 'orderby' => 'post__in','posts_per_page'=>$posts_per_page);
			$the_query = new WP_Query($args);
			?>
			<table id="course_wishlists" class="table table-hover">
				<thead>
					<tr>
						<th style="width:5%;">
							<div class="checkbox" style="margin:0;">
								<input type="checkbox" id="checkall_wishlist_courses"/>
								<label for="checkall_wishlist_courses"></label>
								<script>
								jQuery(document).ready(function($){$('#checkall_wishlist_courses').click(function(){
									var checkboxes= $('.wishlist_course_checkbox');if($(this).prop('checked')){checkboxes.prop('checked',1);}else{checkboxes.prop('checked',0);}});});
								</script>
							</div>
						</th>
						<th>
							<?php _e('Course','wplms-wishlist'); ?>
						</th>
						<?php
							$wishlist_course_details = apply_filters('wplms_wishlist_course_details',array(
								'rating'=>__('Rating','wplms-wishlist'),
								'instructors' => __('Instructors','wplms-wishlist'),
							));
							foreach($wishlist_course_details as $key => $detail){
								?>
								<th class="<?php echo $key; ?>"><?php echo $detail; ?></th>
								<?php
							}
						?>
					</tr>
				</thead>
				<tbody>
				<?php
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
				</tbody>
				<tfoot>
				</tfoot>
			</table>
			<?php
			if(!empty($the_query->max_num_pages) && $the_query->max_num_pages > 1){
				?>
				<div class="pagination">
				<?php
				$flag = 0;
				for($i=1;$i<=$the_query->max_num_pages;$i++){
					if($i == 1){
						?><a class="page-numbers wishlist_page current" data-paged=<?php echo $i;?>>1</a><?php
					}else{
						?><a class="page-numbers wishlist_page" data-paged=<?php echo $i;?>><?php echo $i; ?></a><?php
					}
				}

				?>
				</div>
				<?php
			}

			wp_reset_postdata();
			?>
			<a id="remove_from_wishlist" class="button small" title="<?php _e('Remove Course','wplms-wishlist'); ?>"><span class="fa fa-trash"></span></a>
			<?php
			global $wpdb;
			$collections = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->posts} WHERE post_type='collection' AND post_status NOT IN ('trash','draft','auto-draft') AND post_author = $user_id");
			if(!empty($collections)){
			?>
			<a class="display_next button small" title="<?php _e('Add to Collection','wplms-wishlist'); ?>"><span class="fa fa-plus"></span></a>
			<div style="display:none;">
			<select id="wishlist_collection">
			<?php
				foreach($collections as $collection){
					echo '<option value="'.$collection->ID.'">'.$collection->post_title.'</option>';
				}
			?>
			</select>

			<a id="add_to_collection" class="button small"><span class="fa fa-plus"></span> <?php _e('Add to Collection','wplms-wishlist'); ?></a>
			</div>
			<?php
			}
			wp_nonce_field('wishlist_security','wishlist_security');
			?>
			<script>
			jQuery(document).ready(function($){
				$('.display_next').on('click',function(){$(this).next().toggle(200);});
				$('.wishlist_page').on('click',function(){
					var $this = $(this);
					if($this.hasClass('disabled') || $this.hasClass('current'))
						return;

					$this.addClass('disabled');
					$this.append('<i class="fa fa-spinner"></i>');
					$.ajax({
						type: "POST",
					    url: ajaxurl,
					    data: { action: 'wishlist_paged', 
					            security: $('#wishlist_security').val(),
					            paged:$this.attr('data-paged')
					        },
			           	cache: false,
                      	success: function (html) {
                      		$this.find('.fa-spinner').remove();
                      		$this.removeClass('disabled');
                      		$this.parent().find('.current').removeClass('current');
                      		$this.addClass('current');
                      		var defaultt = $this.html();
                      		$('#course_wishlists tbody').html(html);
                      	}
                    });
				});
				$('#remove_from_wishlist').on('click',function(){
					var $this = $(this);
					$this.append('<span class="fa fa-spinner"></span>');
					var wishlist_courses = [];
					$('.wishlist_course_checkbox:checked').each(function(){
						var data={id:$(this).attr('data-id')};
						wishlist_courses.push(data);
					});
					$.ajax({
						type: "POST",
					    url: ajaxurl,
					    data: { action: 'remove_wishlist_courses', 
					            security: $('#wishlist_security').val(),
					            courses:JSON.stringify(wishlist_courses)
					        },
			           	cache: false,
                      	success: function (html) {
                      		$this.find('.fa-spinner').remove();
                      		var defaultt = $this.html();
                      		if($.isNumeric(html)) {
                      			$('.wishlist_course_checkbox:checked').each(function(){
                      				var wishlist_id = 'wishlist_'+$(this).attr('data-id');
									localStorage.removeItem(wishlist_id);
									$(this).closest('tr').hide(100).remove();
                      			});

                      			$this.html(html+' <?php _e('Courses Removed from wishlist','wplms-wishlist'); ?>');
                      			setTimeout(function(){
                      				$this.html(defaultt);
                      			},2000);
                      		}
                      	}
					});
				});
				$('#add_to_collection').on('click',function(){
					var $this = $(this);
					$this.append('<span class="fa fa-spinner"></span>');
					var wishlist_courses = [];
					$('.wishlist_course_checkbox:checked').each(function(){
						var data={id:$(this).attr('data-id')};
						wishlist_courses.push(data);
					});
					$.ajax({
						type: "POST",
					    url: ajaxurl,
					    data: { action: 'add_wishlist_courses_to_collection', 
					            security: $('#wishlist_security').val(),
					            collection:$('#wishlist_collection').val(),
					            courses:JSON.stringify(wishlist_courses)
					        },
			           	cache: false,
                      	success: function (html) {
                      		$this.find('.fa-spinner').remove();
                      		var defaultt = $this.html();
                      		if($.isNumeric(html)) {
                      			$this.html(html+' <?php _e('courses added to collection ','wplms-wishlist'); ?>'+$('#wishlist_collection option:selected').text());
                      			setTimeout(function(){
                      				$this.html(defaultt);
                      			},2000);
                      		}
                      	}
					});
				});
			});
			</script>
			<?php
		}
	}

	function rating($custom_post){
		echo bp_course_get_course_meta($custom_post->ID);
	}

	function instructor($custom_post){
		echo '<a href="'.bp_core_get_user_domain($custom_post->post_author).'" class="small">'.bp_course_get_instructor_avatar(array('item_id'=>$custom_post->post_author,'width'=>'32','height'=>'32')).'</a>';
	}


	function collections_content(){
		$user_id = bp_displayed_user_id();
		
		$args = apply_filters('wplms_course_collections',array(
			'post_type' => 'collection',
			'post_author'=>$user_id
			));
		
		$query = new WP_Query($args);
		$flag=0;
		?>
		<table id="list_collections" class="table table-hover">
			<thead>
				<tr>
					<th style="width:5%;">
						<div class="checkbox" style="margin:0;">
							<input type="checkbox" id="checkall_collections"/>
							<label for="checkall_collections"></label>
							<script>
							jQuery(document).ready(function($){$('#checkall_collections').click(function(){
									var checkboxes= $('.collection_checkbox');if($(this).prop('checked')){checkboxes.prop('checked',1);}else{checkboxes.prop('checked',0);}});});
							</script>
						</div>
					</th>
					<th>
						<?php _e('Collection','wplms-wishlist'); ?>
					</th>
					<th>
						<?php _e('# Courses','wplms-wishlist'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if($query->have_posts()){
					$flag = 1;
					while($query->have_posts()){
						$query->the_post();
						global $post;
						$id = get_the_ID();
						global $wpdb;
						$count = $wpdb->get_var("SELECT count(*) FROM {$wpdb->postmeta} WHERE meta_key = 'wishlist_course' AND post_id = $id");
						?>
						<tr>
							<td>
								<div class="checkbox">
									<input type="checkbox" id="collection_<?php echo get_the_ID();?>" class="collection_checkbox" data-id="<?php echo get_the_ID();?>" />
									<label for="collection_<?php echo get_the_ID();?>"></label>
								</div>
							</td>
							<td class="name"><a href="<?php echo get_permalink();?>" target="_blank"><?php echo get_the_title(); ?></a></td>
							<td><?php echo (empty($count)?0:$count); ?></td>
						</tr>
						<?php
					}
				}
				?>
				<tr id="blank_collection">
					<td>
						<div class="checkbox">
							<input type="checkbox" id="collection_" class="collection_checkbox" data-id="" />
							<label for="collection_"></label>
						</div>
					</td>
					<td class="name"></td>
					<td><?php echo _x('0','0 courses in collection when first created','wplms-wishlist'); ?></td>
				</tr>
			</tbody>
		</table>
		<?php
		
		if(empty($flag)){
			echo '<div class="message">'.__('No collections found ! Click + to create a new collection.','wplms-wishlist').'</div><style>#list_collections{display:none;}</style>';
		}
		wp_reset_postdata();

		echo '</div>';
		do_action('wplms_wishlist_after_collection_loop',$flag);
	}

	
}

Wplms_Wishlist_Component::init();	


