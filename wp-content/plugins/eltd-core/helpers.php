<?php

if(!function_exists('eltd_core_version_class')) {
    /**
     * Adds plugins version class to body
     * @param $classes
     * @return array
     */
    function eltd_core_version_class($classes) {
        $classes[] = 'eltd-core-'.ELATED_CORE_VERSION;

        return $classes;
    }

    add_filter('body_class', 'eltd_core_version_class');
}

if(!function_exists('eltd_core_theme_installed')) {
    /**
     * Checks whether theme is installed or not
     * @return bool
     */
    function eltd_core_theme_installed() {
        return defined('ELATED_ROOT');
    }
}