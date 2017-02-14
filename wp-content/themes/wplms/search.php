<?php

if (isset($_GET["post_type"]) && $_GET["post_type"] == 'course'){ 
    if(file_exists(get_stylesheet_directory(). '/search-incourse.php')){
        load_template(get_stylesheet_directory() . '/search-incourse.php'); 
        exit();
    }
    if(file_exists(get_template_directory(). '/search-incourse.php')){
        load_template(get_template_directory() . '/search-incourse.php'); 
        exit();
    }
}

get_header(vibe_get_header());
global $wp_query;
$total_results = $wp_query->found_posts;
?>
<section id="title">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php vibe_breadcrumbs(); ?>  
                    <h1><?php _e('Search Results for "', 'vibe'); the_search_query(); ?>"</h1>
                    <h5><?php echo $total_results.__(' results found','vibe');  ?></h5>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="col-md-9 col-sm-8">
            <div class="content">
                <?php
                    if ( have_posts() ) : while ( have_posts() ) : the_post();

                    $categories = get_the_category();
                    $cats='<ul>';
                    if($categories){
                        foreach($categories as $category) {
                            $cats .= '<li><a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), 'vibe' ) ) . '">'.$category->cat_name.'</a></li>';
                        }
                    }
                    $cats .='</ul>';
                        
                       echo ' <div class="blogpost">
                            <div class="meta">
                               <div class="date">
                                <p class="day"><span>'.get_the_time('j').'</span></p>
                                <p class="month">'.get_the_time('M').'</p>
                               </div>
                            </div>
                            '.(has_post_thumbnail(get_the_ID())?'
                            <div class="featured">
                                <a href="'.get_permalink().'">'.get_the_post_thumbnail(get_the_ID(),'full').'</a>
                            </div>':'').'
                            <div class="excerpt '.(has_post_thumbnail(get_the_ID())?'thumb':'').'">
                                <h3><a href="'.get_permalink().'">'.get_the_title().'</a></h3>
                                <div class="cats">
                                    '.$cats.'
                                    <p>| 
                                    <a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'">'.get_the_author_meta( 'display_name' ).'</a>
                                    </p>
                                </div>
                                <p>'.get_the_excerpt().'</p>
                                <a href="'.get_permalink().'" class="link">'.__('Read More','vibe').'</a>
                            </div>
                        </div>';
                    endwhile;
                    else:
                        echo '<h3>'.__('Sorry, No results found.','vibe').'</h3>';
                    endif;
                    pagination();
                ?>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="sidebar">
                <?php
                    $sidebar = apply_filters('wplms_sidebar','searchsidebar');
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());
?>