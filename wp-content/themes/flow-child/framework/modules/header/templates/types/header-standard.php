<?php do_action('flow_elated_before_page_header'); ?>

<header class="eltd-page-header">
    <?php if($show_fixed_wrapper) : ?>
        <div class="eltd-fixed-wrapper">
    <?php endif; ?>
    <div class="eltd-menu-area" <?php flow_elated_inline_style($menu_area_background_color); ?>>
        <?php if($menu_area_in_grid) : ?>
            <div class="eltd-grid">
        <?php endif; ?>
			<?php do_action( 'flow_elated_after_header_menu_area_html_open' )?>
            <div class="eltd-vertical-align-containers">
                <div class="eltd-position-left">
                    <div class="eltd-position-left-inner">
                        <?php if(!$hide_logo) {
                            flow_elated_get_logo();
                        } ?>
                    </div>
                </div>
                <div class="eltd-position-center">
                    <div class="eltd-position-center-inner">
                        <?php flow_elated_get_main_menu(); ?>
                    </div>
                </div>
                <div class="eltd-position-right">
                <div class="log-reg-container">
                    <?php if(!is_user_logged_in()): ?>
                        <div class="log-reg-elm">
                            <a href="http://rxleaf.samiscoding.com/register"><img src="http://rxleaf.samiscoding.com/wp-content/themes/flow-child/assets/img/regis.png" alt="register"  width="40px"></a>
                        </div>
                    <?php endif; ?>
                </div>
                    <div class="eltd-position-right-inner">
                        <?php if(is_active_sidebar('eltd-right-from-main-menu')) : ?>
                            <?php dynamic_sidebar('eltd-right-from-main-menu'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php if($menu_area_in_grid) : ?>
        </div>
        <?php endif; ?>
    </div>
    <?php if($show_fixed_wrapper) : ?>
        </div>
    <?php endif; ?>
    <?php if($show_sticky) {
        flow_elated_get_sticky_header();
    } ?>
</header>

<?php do_action('flow_elated_after_page_header'); ?>

