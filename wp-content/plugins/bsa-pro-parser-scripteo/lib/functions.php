<?php
add_shortcode( 'bsa_pro_ma_space', 'create_bsa_pro_ma_short_code_space' );
function create_bsa_pro_ma_short_code_space( $atts )
{
	$a = shortcode_atts( array(
		'url' 			=> ( isset($atts['url']) ) ? $atts['url'] : '',
		'id' 			=> ( isset($atts['id']) ) ? $atts['id'] : '',
		'type' 			=> ( isset($atts['type']) ) ? $atts['type'] : '',
		'secure' 		=> ( isset($atts['secure']) ) ? $atts['secure'] : '',
		'max_width' 	=> ( isset($atts['max_width']) ) ? $atts['max_width'] : '',
		'delay' 		=> ( isset($atts['delay']) ) ? $atts['delay'] : '',
		'position' 		=> ( isset($atts['position']) ) ? $atts['position'] : '',
		'padding_top' 	=> ( isset($atts['padding_top']) ) ? $atts['padding_top'] : '',
		'attachment' 	=> ( isset($atts['attachment']) ) ? $atts['attachment'] : '',
		'crop' 			=> ( isset($atts['crop']) ) ? $atts['crop'] : '',
	), $atts );

	$url 				= ( isset($a['url']) ) ? $a['url'] : '';
	$id 				= ( isset($a['id']) ) ? (strpos($url,'?') !== false) ? '&' : '?' .'id='.$a['id'] : '';
	$type 				= ( isset($a['type']) ) ? '&type='.$a['type'] : '';
	$secure 			= ( isset($a['secure']) ) ? '&secure='.$a['secure'] : '';
	$max_width 			= ( isset($a['max_width']) ) ? '&max_width='.$a['max_width'] : '';
	$delay 				= ( isset($a['delay']) ) ? '&delay='.$a['delay'] : '';
	$position 			= ( isset($a['position']) ) ? '&position='.$a['position'] : '';
	$padding_top 		= ( isset($a['padding_top']) ) ? '&padding_top='.$a['padding_top'] : '';
	$attachment 		= ( isset($a['attachment']) ) ? '&attachment='.$a['attachment'] : '';
	$crop 				= ( isset($a['crop']) ) ? '&crop='.$a['crop'] : '';
	$url1 				= ( isset($_SERVER['SERVER_NAME']) ) ? '&url1='.$_SERVER['SERVER_NAME'] : '';
	$url2 				= ( isset($_SERVER['HTTP_HOST']) ) ? '&url2='.$_SERVER['HTTP_HOST'] : '';

	ob_start();
	if ( isset($a['url']) && $a['url'] != '' && isset($a['id']) && $a['id'] != '' && isset($a['type']) && $a['type'] != '' && isset($a['secure']) && $a['secure'] != '' ) {

		$getSpace = wp_remote_fopen($url.$id.$type.$secure.$max_width.$delay.$position.$padding_top.$attachment.$crop.$url1.$url2);

		$allCss = plugin_dir_path( __FILE__ ) . '../frontend/css/all.css';
		if ( !file_exists($allCss) || file_exists($allCss) && filemtime($allCss) > time() + (15 * 60) ) {
			$getTemplate = wp_remote_fopen($url.$id.'&type=template'.$secure.$max_width.$delay.$position.$padding_top.$attachment.$crop.$url1.$url2);
			$getDomain = wp_remote_fopen($url.$id.'&type=domain'.$secure.$max_width.$delay.$position.$padding_top.$attachment.$crop.$url1.$url2);

			if ( !file_exists(plugin_dir_path( __FILE__ ) . '../frontend/css/'.$getTemplate.'.css') ) {
				if ( isset($getTemplate) && isset($getDomain) && file_exists($getDomain.'/bsa-pro-scripteo/frontend/css/'.$getTemplate.'.css') ) {
					$template = file_get_contents($getDomain.'/bsa-pro-scripteo/frontend/css/'.$getTemplate.'.css');
					file_put_contents(plugin_dir_path( __FILE__ ) . '../frontend/css/'.$getTemplate.'.css', $template);
				}
			}

			$allExternalCss = $getDomain.'/bsa-pro-scripteo/frontend/css/all.css?ver='.rand(23121,12312312);
			$file_headers = @get_headers($allExternalCss);

			if($file_headers[0] == 'HTTP/1.0 404 Not Found'){
					//echo "The file $filename does not exist";
			} else if ($file_headers[0] == 'HTTP/1.0 302 Found' && $file_headers[7] == 'HTTP/1.0 404 Not Found'){
					//echo "The file $filename does not exist, and I got redirected to a custom 404 page..";
			} else { // if exists
				if ( !file_exists($allCss) || file_exists($allCss) && file_get_contents($allCss) != file_get_contents($allExternalCss) || 1 == 1 ) {
						$template = file_get_contents($allExternalCss);
						file_put_contents(plugin_dir_path( __FILE__ ) . '../frontend/css/all.css', $template);
				}
			}
		}

		echo $getSpace;
	} else {
		echo 'URL param invalid.';
	}
	return ob_get_clean();
}

add_action('wp_enqueue_scripts', 'BSA_MA_PRO_add_stylesheet_and_script');
function BSA_MA_PRO_add_stylesheet_and_script()
{
	wp_register_style('buy_sell_ads_pro_main_stylesheet', plugins_url('../frontend/css/asset/style.css', __FILE__));
	wp_enqueue_style('buy_sell_ads_pro_main_stylesheet');
	if ( file_exists(plugin_dir_path( __FILE__ ) . '../frontend/css/all.css') ) {
		wp_register_style('buy_sell_ads_pro_template_stylesheet', plugins_url('../frontend/css/all.css', __FILE__));
		wp_enqueue_style('buy_sell_ads_pro_template_stylesheet');
	} else {
		$get_templates = array_diff( scandir(plugin_dir_path( __FILE__ )."../frontend/css/"), Array( ".", "..", "asset", "template.css.php", "rtl-template.css.php", "all.css", "rtl-all.css", ".DS_Store" ) );
		foreach ( $get_templates as $template ) {
			wp_register_style('buy_sell_ads_pro_'.$template.'_stylesheet', plugins_url('../frontend/css/'.$template, __FILE__));
			wp_enqueue_style('buy_sell_ads_pro_'.$template.'_stylesheet');
		}
	}
	// $get_templates = array_diff( scandir(plugin_dir_path( __FILE__ )."../frontend/css/"), Array( ".", "..", "asset", "template.css.php", "all.css", ".DS_Store" ) );
	// foreach ( $get_templates as $template ) {
	// 	wp_register_style('buy_sell_ads_pro_'.$template.'_stylesheet', plugins_url('../frontend/css/'.$template, __FILE__));
	// 	wp_enqueue_style('buy_sell_ads_pro_'.$template.'_stylesheet');
	// }
	// wp_register_style('buy_sell_ads_pro_template_stylesheet', plugins_url('../frontend/css/template.css.php', __FILE__));
	// wp_enqueue_style('buy_sell_ads_pro_template_stylesheet');
	wp_register_style('buy_sell_ads_pro_materialize_stylesheet', plugins_url('../frontend/css/asset/material-design.css', __FILE__));
	wp_enqueue_style('buy_sell_ads_pro_materialize_stylesheet');
	wp_register_style('buy_sell_ads_pro_animate_stylesheet', plugins_url('../frontend/css/asset/animate.css', __FILE__));
	wp_enqueue_style('buy_sell_ads_pro_animate_stylesheet');
	wp_register_style('buy_sell_ads_pro_owl_carousel_stylesheet', plugins_url('../frontend/css/asset/owl.carousel.css', __FILE__));
	wp_enqueue_style('buy_sell_ads_pro_owl_carousel_stylesheet');
	wp_register_script('buy_sell_ads_pro_js_script', plugins_url('../frontend/js/script.js', __FILE__), array('jquery','media-upload','thickbox'));
	wp_enqueue_script('buy_sell_ads_pro_js_script');
	wp_register_script('buy_sell_ads_pro_viewport_checker_js_script', plugins_url('../frontend/js/jquery.viewportchecker.js', __FILE__), array('jquery','media-upload','thickbox'));
	wp_enqueue_script('buy_sell_ads_pro_viewport_checker_js_script');
	wp_register_script('buy_sell_ads_pro_owl_carousel_js_script', plugins_url('../frontend/js/owl.carousel.js', __FILE__), array('jquery','media-upload','thickbox'));
	wp_enqueue_script('buy_sell_ads_pro_owl_carousel_js_script');
}

// add_shortcode( 'bsa_pro_ma_space', 'create_bsa_pro_ma_short_code_space' );
// function create_bsa_pro_ma_short_code_space( $atts )
// {
// 	$a = shortcode_atts( array(
// 		'url' 			=> ( isset($atts['url']) ) ? $atts['url'] : '',
// 		'id' 			=> ( isset($atts['id']) ) ? $atts['id'] : '',
// 		'type' 			=> ( isset($atts['type']) ) ? $atts['type'] : '',
// 		'secure' 		=> ( isset($atts['secure']) ) ? $atts['secure'] : '',
// 		'max_width' 	=> ( isset($atts['max_width']) ) ? $atts['max_width'] : '',
// 		'delay' 		=> ( isset($atts['delay']) ) ? $atts['delay'] : '',
// 		'position' 		=> ( isset($atts['position']) ) ? $atts['position'] : '',
// 		'padding_top' 	=> ( isset($atts['padding_top']) ) ? $atts['padding_top'] : '',
// 		'attachment' 	=> ( isset($atts['attachment']) ) ? $atts['attachment'] : '',
// 		'crop' 			=> ( isset($atts['crop']) ) ? $atts['crop'] : '',
// 	), $atts );
//
// 	$url 				= ( isset($a['url']) ) ? $a['url'] : '';
// 	$id 				= ( isset($a['id']) ) ? (strpos($url,'?') !== false) ? '&' : '?' .'id='.$a['id'] : '';
// 	$type 				= ( isset($a['type']) ) ? '&type='.$a['type'] : '';
// 	$secure 			= ( isset($a['secure']) ) ? '&secure='.$a['secure'] : '';
// 	$max_width 			= ( isset($a['max_width']) ) ? '&max_width='.$a['max_width'] : '';
// 	$delay 				= ( isset($a['delay']) ) ? '&delay='.$a['delay'] : '';
// 	$position 			= ( isset($a['position']) ) ? '&position='.$a['position'] : '';
// 	$padding_top 		= ( isset($a['padding_top']) ) ? '&padding_top='.$a['padding_top'] : '';
// 	$attachment 		= ( isset($a['attachment']) ) ? '&attachment='.$a['attachment'] : '';
// 	$crop 				= ( isset($a['crop']) ) ? '&crop='.$a['crop'] : '';
// 	$url1 				= ( isset($_SERVER['SERVER_NAME']) ) ? '&url1='.$_SERVER['SERVER_NAME'] : '';
// 	$url2 				= ( isset($_SERVER['HTTP_HOST']) ) ? '&url2='.$_SERVER['HTTP_HOST'] : '';
//
// 	ob_start();
// 	if ( isset($a['url']) && $a['url'] != '' && isset($a['id']) && $a['id'] != '' && isset($a['type']) && $a['type'] != '' && isset($a['secure']) && $a['secure'] != '' ) {
//
// 		echo '<div class="bsa_pro_ajax_ma_load-'.$a['id'].'" style="display:none"></div>';
// 		echo '
// 		<script>
// 		(function($) {
// 			$.post("'.admin_url("admin-ajax.php").'", {
// 				action:"bsa_pro_ajax_ma_load_ad_space",
// 				url:"'.$url.'",
// 				id:"'.$id.'",
// 				type:"'.$type.'",
// 				secure:"'.$secure.'",
// 				max_width:"'.$max_width.'",
// 				delay:"'.$delay.'",
// 				position:"'.$position.'",
// 				padding_top:"'.$padding_top.'",
// 				attachment:"'.$attachment.'",
// 				crop:"'.$crop.'",
// 				url1:"'.$url1.'",
// 				url2:"'.$url2.'"
// 			}, function(result) {
// 				$(".bsa_pro_ajax_ma_load-'.$a['id'].'").html(result).fadeIn();
// 			});
// 		})(jQuery);
// 		</script>
// 		';
// 	} else {
// 		echo 'URL param invalid.';
// 	}
// 	return ob_get_clean();
// }
//
// function bsa_pro_ajax_ma_load_ad_space()
// {
// 	$url 				= ( isset($_POST['url']) ) ? $_POST['url'] : '';
// 	$id 				= ( isset($_POST['id']) ) ? $_POST['id'] : '';
// 	$type 				= ( isset($_POST['type']) ) ? '&type='.$_POST['type'] : '';
// 	$secure 			= ( isset($_POST['secure']) ) ? '&secure='.$_POST['secure'] : '';
// 	$max_width 			= ( isset($_POST['max_width']) ) ? '&max_width='.$_POST['max_width'] : '';
// 	$delay 				= ( isset($_POST['delay']) ) ? '&delay='.$_POST['delay'] : '';
// 	$position 			= ( isset($_POST['position']) ) ? '&position='.$_POST['position'] : '';
// 	$padding_top 		= ( isset($_POST['padding_top']) ) ? '&padding_top='.$_POST['padding_top'] : '';
// 	$attachment 		= ( isset($_POST['attachment']) ) ? '&attachment='.$_POST['attachment'] : '';
// 	$crop 				= ( isset($_POST['crop']) ) ? '&crop='.$_POST['crop'] : '';
// 	$url1 				= ( isset($_SERVER['SERVER_NAME']) ) ? '&url1='.$_SERVER['SERVER_NAME'] : '';
// 	$url2 				= ( isset($_SERVER['HTTP_HOST']) ) ? '&url2='.$_SERVER['HTTP_HOST'] : '';
//
// 	if ( isset($_POST['action']) && $_POST['action'] == 'bsa_pro_ajax_ma_load_ad_space' ) {
// 		$getSpace = file_get_contents($url.$id.$type.$secure.$max_width.$delay.$position.$padding_top.$attachment.$crop.$url1.$url2);
// 		$getDomain = file_get_contents($url.$id.'&type=domain'.$secure.$max_width.$delay.$position.$padding_top.$attachment.$crop.$url1.$url2);
// 		$getTemplate = file_get_contents($url.$id.'&type=template'.$secure.$max_width.$delay.$position.$padding_top.$attachment.$crop.$url1.$url2);
//
// 		if ( !file_exists(plugin_dir_path( __FILE__ ) . '../frontend/css/'.$getTemplate.'.css') ) {
// 			$template = file_get_contents($getDomain.'/bsa-pro-scripteo/frontend/css/'.$getTemplate.'.css');
// 			file_put_contents(plugin_dir_path( __FILE__ ) . '../frontend/css/'.$getTemplate.'.css', $template);
// 		}
//
// 		$allCss = plugin_dir_path( __FILE__ ) . '../frontend/css/all.css';
// 		$allExternalCss = $getDomain.'/bsa-pro-scripteo/frontend/css/all.css?ver='.rand(23121,12312312);
// 		$file_headers = @get_headers($allExternalCss);
//
// 		if($file_headers[0] == 'HTTP/1.0 404 Not Found'){
// 				//echo "The file $filename does not exist";
// 		} else if ($file_headers[0] == 'HTTP/1.0 302 Found' && $file_headers[7] == 'HTTP/1.0 404 Not Found'){
// 				//echo "The file $filename does not exist, and I got redirected to a custom 404 page..";
// 		} else { // if exists
// 			if ( !file_exists($allCss) || file_exists($allCss) && file_get_contents($allCss) != file_get_contents($allExternalCss) ) {
// 					$template = file_get_contents($allExternalCss);
// 					file_put_contents(plugin_dir_path( __FILE__ ) . '../frontend/css/all.css', $template);
// 			}
// 		}
//
// 		echo $getSpace;
// 	} else {
// 		echo null;
// 	}
// 	die();
// }
// add_action('wp_ajax_bsa_pro_ajax_ma_load_ad_space', 'bsa_pro_ajax_ma_load_ad_space');
// add_action( 'wp_ajax_nopriv_bsa_pro_ajax_ma_load_ad_space', 'bsa_pro_ajax_ma_load_ad_space' );
