<?php
/*
Plugin Name: Elated Core
Description: Plugin that adds all post types needed by our theme
Author: Elated Themes
Version: 1.1.2
*/

require_once 'load.php';

if(!function_exists('eltd_core_activation')) {
    /**
     * Triggers when plugin is activated. It calls flush_rewrite_rules
     * and defines flow_elated_core_on_activate action
     */
    function eltd_core_activation() {
        do_action('flow_elated_core_on_activate');

        flush_rewrite_rules();
    }

    register_activation_hook(__FILE__, 'eltd_core_activation');
}

if(!function_exists('eltd_core_text_domain')) {
    /**
     * Loads plugin text domain so it can be used in translation
     */
    function eltd_core_text_domain() {
        load_plugin_textdomain('eltd_core', false, ELATED_CORE_REL_PATH.'/languages');
    }

    add_action('plugins_loaded', 'eltd_core_text_domain');
}

if(!function_exists('eltd_core_theme_menu')) {
    /**
     * Function that generates admin menu for options page.
     * It generates one admin page per options page.
     */
    function eltd_core_theme_menu() {
        if (eltd_core_theme_installed()) {

            global $flow_elated_Framework;
            flow_elated_init_theme_options();

            $page_hook_suffix = add_menu_page(
                'Elated Options',                   // The value used to populate the browser's title bar when the menu page is active
                'Elated Options',                   // The text of the menu in the administrator's sidebar
                'administrator',                  // What roles are able to access the menu
                'flow_elated_theme_menu',                // The ID used to bind submenu items to this menu
                array($flow_elated_Framework->getSkin(), 'renderOptions'), // The callback function used to render this menu
                $flow_elated_Framework->getSkin()->getMenuIcon('options'),             // Icon For menu Item
                $flow_elated_Framework->getSkin()->getMenuItemPosition('options')            // Position
            );

            foreach ($flow_elated_Framework->eltdOptions->adminPages as $key=>$value ) {
                $slug = "";

                if (!empty($value->slug)) {
                    $slug = "_tab".$value->slug;
                }

                $subpage_hook_suffix = add_submenu_page(
                    'flow_elated_theme_menu',
                    'Elated Options - '.$value->title,                   // The value used to populate the browser's title bar when the menu page is active
                    $value->title,                   // The text of the menu in the administrator's sidebar
                    'administrator',                  // What roles are able to access the menu
                    'flow_elated_theme_menu'.$slug,                // The ID used to bind submenu items to this menu
                    array($flow_elated_Framework->getSkin(), 'renderOptions')
                );

                add_action('admin_print_scripts-'.$subpage_hook_suffix, 'flow_elated_enqueue_admin_scripts');
                add_action('admin_print_styles-'.$subpage_hook_suffix, 'flow_elated_enqueue_admin_styles');
            };

            add_action('admin_print_scripts-'.$page_hook_suffix, 'flow_elated_enqueue_admin_scripts');
            add_action('admin_print_styles-'.$page_hook_suffix, 'flow_elated_enqueue_admin_styles');

        }
    }

    add_action( 'admin_menu', 'eltd_core_theme_menu');
}