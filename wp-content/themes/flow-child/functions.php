<?php

/*** Child Theme Function  ***/

function flow_child_theme_enqueue_scripts() {
	wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css'  );
	wp_enqueue_style( 'childstyle' );
}
add_action('wp_enqueue_scripts', 'flow_child_theme_enqueue_scripts', 11);

function sam_load_rxPosts() {
	$posts = array();
	$posts_args =  array(
		'post_type'  => 'post',
		'posts_per_page' => 5,
		'post_status' => 'publish',
		'category_name' => 'library'
	);
	$posts_query = new WP_Query($posts_args);
	// var_dump($posts_query);
	if($posts_query->have_posts()) {
		while($posts_query->have_posts()) {
			$posts_query->the_post();
			$posts[] = get_the_ID();
		}
	}
	
	wp_reset_postdata();
	return $posts;
}

//Amend parent function
function flow_elated_get_blog_type($type, $sidebar, $query = '') {
		
	if (empty($query)){
		$blog_query = flow_elated_get_blog_query();
	} else {
		$blog_query = flow_elated_get_blog_query($query);
	}
		
		$paged = flow_elated_paged();
		
		if(flow_elated_options()->getOptionValue('blog_page_range') != ""){
			
			$blog_page_range = esc_attr(flow_elated_options()->getOptionValue('blog_page_range'));
			
		} else{
			
			$blog_page_range = $blog_query->max_num_pages;
			
		}	
		
		$blog_classes = flow_elated_get_blog_holder_classes($type, $sidebar);
		
		$params = array(
			'blog_query' => $blog_query,
			'paged' => $paged,
			'blog_page_range' => $blog_page_range,
			'blog_type' => $type,
			'blog_classes' => $blog_classes
		);

	flow_elated_get_module_template_part('templates/lists/' .  $type, 'blog', '', $params);
}

function flow_elated_get_blog_query($query = ''){
	global $wp_query;
	if(!empty($query)){
		$query_array = $query;
		$blog_query = new WP_Query($query_array);
	} else {
		$id = flow_elated_get_page_id();
		$category = esc_attr(get_post_meta($id, "eltd_blog_category_meta", true));
		
		if(esc_attr(get_post_meta($id, "eltd_show_posts_per_page_meta", true)) != ""){
			$post_number = esc_attr(get_post_meta($id, "eltd_show_posts_per_page_meta", true));
		}else{			
			$post_number = esc_attr(get_option('posts_per_page'));
		} 
		
		$paged = flow_elated_paged();
		$query_array = array(
			'post_type' => 'post',
			'post_status'	=> 'publish',
			'paged' => $paged,
			'cat' 	=> $category,
			'posts_per_page' => $post_number
		);
		if(is_archive()){
			$blog_query = $wp_query;
		}else{
			$blog_query = new WP_Query($query_array);
		}
	}


	return $blog_query;
	
}
