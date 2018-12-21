<?php

/*** Child Theme Function  ***/

function flow_child_theme_enqueue_scripts() {
	wp_register_style( 'childstyle', get_stylesheet_directory_uri() . '/style.css'  );
	wp_enqueue_style( 'childstyle' );
}
add_action('wp_enqueue_scripts', 'flow_child_theme_enqueue_scripts', 11);
	/**
	 * Function which return html for post formats
	 * @param $type - dependns on blog list type 
	 * @param $ajax
	 * @param $no_posts - when ajax is set to yes check which template to load if there are no posts in WP Query 
	 * @return post format template
	 */

	function flow_elated_get_post_format_html($type = "", $ajax = '', $no_posts = '', $counter = 0) {

		if(($counter % 3) == 0){
			// echo "<h1>SAMERALO</h1>";
			return flow_elated_get_blog_module_template_part('templates/lists/post-formats/mantis-list-ad.php');
		}
		$post_format = get_post_format();
		
		$supported_post_formats = array('audio', 'video', 'link', 'quote', 'gallery');
		if(in_array($post_format,$supported_post_formats)) {
			$post_format = $post_format;
		} else {
			$post_format = 'standard';
		}
		$slug = '';
		if($type !== ""){
			$slug = $type;			
		}
		

		$params = array();
		$params['blog_type'] = $type;
		
		switch( $post_format ) {
			case 'standard':
				break;
			case 'audio':
				break;
			case 'video':
				break;
			case 'link':
				$id = get_the_ID();
				$params['image'] = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'large');
				$params['external_link'] = esc_html(get_post_meta(get_the_ID(), "eltd_post_link_link_meta", true));
				$params['link_target'] = '_blank';
				$params['title_tag'] = 'h4';
				break;
			case 'quote':
				$id = get_the_ID();
				$params['image'] = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'large');
				$quote_array = flow_elated_get_quote_meta_fields();
				$params['quote_text'] = $quote_array['quote_text'];
				$params['quote_author'] = $quote_array['quote_author'];
				break;
			case 'gallery':
				break;
			default:
		}

		$chars_array = flow_elated_blog_lists_number_of_chars();
		
		if(isset($chars_array[$type])) {
			$params['excerpt_length'] = $chars_array[$type];
		} else {
			$params['excerpt_length'] = '';
		}
		
		
		if($type == 'masonry' || $type='masonry-full-width'){
			$params['image_size'] = flow_elated_get_post_image_size();
		}
		
		$params['featured_class'] = flow_elated_featured_post_class();

		if($ajax == ''){
			flow_elated_get_module_template_part('templates/lists/post-formats/' . $post_format, 'blog', $slug, $params);
		}

		if($ajax == 'yes' && $no_posts == ''){
			
			return flow_elated_get_blog_module_template_part('templates/lists/post-formats/' . $post_format, $slug, $params);
						
		}
		
		if($no_posts == 'yes' && $ajax == 'yes'){
			return flow_elated_get_blog_module_template_part('templates/parts/no-posts', $slug, $params);
		}
		
	}

