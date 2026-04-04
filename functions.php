<?php
if (!defined('ABSPATH')) {
    exit;
}

function website_flexi_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));

    register_nav_menus(
        array(
            'primary' => __('Primary Menu', 'website-flexi-theme'),
        )
    );
}
add_action('after_setup_theme', 'website_flexi_theme_setup');

function website_flexi_enqueue_assets() {
    wp_enqueue_style(
        'website-flexi-google-fonts',
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700;800&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'website-flexi-main-style',
        get_template_directory_uri() . '/assets/css/main.css',
        array('website-flexi-google-fonts'),
        '1.0.0'
    );

    wp_enqueue_script(
        'website-flexi-main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'website_flexi_enqueue_assets');
