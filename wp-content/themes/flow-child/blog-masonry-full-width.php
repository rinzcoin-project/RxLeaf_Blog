<?php
	/*
	Template Name: Blog: Masonry Full Width
	*/
?>
<?php get_header(); ?>

<?php flow_elated_get_title(); ?>
<?php 

$slider_position = flow_elated_get_blog_slider_position();

if($slider_position !== 'above_content'){
	get_template_part('slider');
}

?>

	<div class="eltd-full-width">
	<div id="main-page-id">
		<?php /* Start the Loop */ ?>
		<?php while(have_posts()) : the_post(); ?>
		<?php the_content();?>
		<?php endwhile; ?>
	</div>
		<div class="eltd-full-width-inner clearfix">
			<?php flow_elated_get_blog('masonry-full-width'); ?>
		</div>
		<?php do_action('flow_elated_before_container_close'); ?>
	</div>

<?php get_footer();