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

function website_flexi_get_page_url_by_template($template_file, $fallback = '') {
    $query = new WP_Query(
        array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'meta_key'       => '_wp_page_template',
            'meta_value'     => $template_file,
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        )
    );

    if ($query->have_posts()) {
        $page_url = get_permalink($query->posts[0]->ID);
        wp_reset_postdata();
        return $page_url;
    }

    wp_reset_postdata();
    return $fallback;
}

function website_flexi_custom_login_url($login_url, $redirect, $force_reauth) {
    if (is_admin()) {
        return $login_url;
    }

    $custom_login_url = website_flexi_get_page_url_by_template('page-login.php', '');

    if (!$custom_login_url) {
        return $login_url;
    }

    if (!empty($redirect)) {
        $custom_login_url = add_query_arg('redirect_to', rawurlencode($redirect), $custom_login_url);
    }

    if ($force_reauth) {
        $custom_login_url = add_query_arg('reauth', '1', $custom_login_url);
    }

    return $custom_login_url;
}
add_filter('login_url', 'website_flexi_custom_login_url', 10, 3);

function website_flexi_custom_register_url($register_url) {
    if (is_admin()) {
        return $register_url;
    }

    $custom_signup_url = website_flexi_get_page_url_by_template('page-signup.php', '');

    return $custom_signup_url ? $custom_signup_url : $register_url;
}
add_filter('register_url', 'website_flexi_custom_register_url');
