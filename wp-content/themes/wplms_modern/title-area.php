<section id="title" class="title-area">
	<div class="title-content">
		<div class="container">
			<div class="title-text">
				<div class="row">
					<div class="col-md-12">
						<?php
                        $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                        if(vibe_validate($breadcrumbs) || empty($breadcrumbs))
                            vibe_breadcrumbs(); 
	                    ?>
	                    <div class="pagetitle">
				        	<h1>
				        	<?php
				        	$title=get_post_meta(get_the_ID(),'vibe_title',true);
				        	if(!isset($title) || !$title || (vibe_validate($title))){
				        		echo get_the_title($id); 
				        	}
				        	?>
				        	</h1>
				        </div>
	                    <?php the_sub_title(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>	
</section>