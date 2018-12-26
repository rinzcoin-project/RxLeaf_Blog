<div class="<?php echo esc_attr($blog_classes)?>" data-blog-type="<?php echo esc_attr($blog_type)?>" <?php echo esc_attr(flow_elated_set_blog_holder_data_params()); ?> >
	
	<div class="eltd-expandable-tiles ">
		
		<div class="eltd-post-list">
			<div class="eltd-blog-list-expandable-grid-sizer"></div>
			<div class="eltd-blog-list-expandable-grid-sizer-gutter"></div>
			<?php
			$counter = 0;
			if($blog_query->have_posts()) : while ( $blog_query->have_posts() ) : $blog_query->the_post();
			if($counter == 5 ) {
				echo "<div data-mantis-zone='detail-inline-1'></div>";
			}
				flow_elated_get_post_format_html($blog_type);
				$counter++;

				
			endwhile;
			else:
				flow_elated_get_module_template_part('templates/parts/no-posts', 'blog');
			endif;
			?>
		</div>
		<div class="ad-banner-space expanding-tiles"></div>
		<?php
		if(flow_elated_options()->getOptionValue('pagination') == 'yes') {
			flow_elated_pagination($blog_query->max_num_pages, $blog_page_range, $paged, $blog_type);
		}
		?>
	</div>
	
</div>

<?php do_action('flow_elated_generate_scroll_trigger');