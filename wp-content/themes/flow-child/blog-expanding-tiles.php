<?php
/*
Template Name: Blog: Expanding Tiles
*/
?>
<?php get_header(); ?>
	<div class="eltd-full-width">
		<?php do_action('flow_elated_after_container_open'); ?>
		<div id="main-page-id">
			<?php /* Start the Loop */ ?>
			<?php while(have_posts()) : the_post(); ?>
			<?php the_content();?>
			<?php endwhile; ?>
		</div>
		<div class="eltd-full-width-inner">
			<?php flow_elated_get_blog('expanding-tiles'); ?>
		</div>
		<aside class="sam-homepage-ad-sidebar"><?php dynamic_sidebar('home-ad-sidebar'); ?></aside>
		<?php do_action('flow_elated_before_container_close'); ?>
	</div>
	<div class="sam-home-display-posts">
	<div class="wpb_wrapper" style="text-align:left; padding-left: 35px; margin-bottom: 40px;">
			<h3>Top Articles &amp; Updates</h3>
<h4>Selection of our most popular articles</h4>

		</div>
		<?php 
										$posts_args =  array(
											'post_type'  => 'post',
											'posts_per_page' => 12,
											'post_status' => 'publish',
											'category_name' => 'library',
											'orderby' => 'comment_count',
										);
										flow_elated_get_blog_type('masonary','default', $posts_args);
									?>
	</div>
<?php get_footer(); ?>