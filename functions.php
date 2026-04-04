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
    $style_path = get_template_directory() . '/assets/css/main.css';
    $script_path = get_template_directory() . '/assets/js/main.js';
    $style_ver = file_exists($style_path) ? (string) filemtime($style_path) : '1.0.0';
    $script_ver = file_exists($script_path) ? (string) filemtime($script_path) : '1.0.0';

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
        $style_ver
    );

    wp_enqueue_script(
        'website-flexi-main-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        $script_ver,
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

function website_flexi_get_page_url_by_slug($slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');

    return $page ? get_permalink($page) : '';
}

function website_flexi_get_login_url() {
    $login_url = website_flexi_get_page_url_by_template('page-login.php', '');

    if (!$login_url) {
        $login_url = website_flexi_get_page_url_by_slug('login');
    }

    if (!$login_url) {
        $login_url = home_url('/login/');
    }

    return $login_url;
}

function website_flexi_get_signup_url() {
    $signup_url = website_flexi_get_page_url_by_template('page-signup.php', '');

    if (!$signup_url) {
        $signup_url = website_flexi_get_page_url_by_slug('signup');
    }

    if (!$signup_url) {
        $signup_url = website_flexi_get_page_url_by_slug('register');
    }

    if (!$signup_url) {
        $signup_url = home_url('/signup/');
    }

    return $signup_url;
}

function website_flexi_custom_login_url($login_url, $redirect, $force_reauth) {
    if (is_admin()) {
        return $login_url;
    }

    $custom_login_url = website_flexi_get_login_url();

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

    $custom_signup_url = website_flexi_get_signup_url();

    return $custom_signup_url ? $custom_signup_url : $register_url;
}
add_filter('register_url', 'website_flexi_custom_register_url');

function website_flexi_get_request_path() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $path = wp_parse_url($request_uri, PHP_URL_PATH);

    if (!is_string($path) || '' === $path) {
        return '/';
    }

    return trailingslashit($path);
}

function website_flexi_is_auth_path($path_slug) {
    $request_path = website_flexi_get_request_path();
    $target_path  = trailingslashit(home_url('/' . ltrim($path_slug, '/')));
    $target_only  = trailingslashit(wp_parse_url($target_path, PHP_URL_PATH));

    return $request_path === $target_only;
}

function website_flexi_render_auth_virtual_pages() {
    if (is_admin()) {
        return;
    }

    if (website_flexi_is_auth_path('login')) {
        $template_file = locate_template('page-login.php');
        if (!$template_file) {
            return;
        }

        status_header(200);
        nocache_headers();
        include $template_file;
        exit;
    }

    if (website_flexi_is_auth_path('signup') || website_flexi_is_auth_path('register')) {
        $template_file = locate_template('page-signup.php');
        if (!$template_file) {
            return;
        }

        status_header(200);
        nocache_headers();
        include $template_file;
        exit;
    }
}
add_action('template_redirect', 'website_flexi_render_auth_virtual_pages', 1);

function website_flexi_auth_slug_template_override($template) {
    if (!is_page()) {
        return $template;
    }

    $slug = get_query_var('pagename');

    if ('login' === $slug) {
        $login_template = locate_template('page-login.php');
        if ($login_template) {
            return $login_template;
        }
    }

    if ('signup' === $slug || 'register' === $slug) {
        $signup_template = locate_template('page-signup.php');
        if ($signup_template) {
            return $signup_template;
        }
    }

    return $template;
}
add_filter('template_include', 'website_flexi_auth_slug_template_override', 99);

function website_flexi_redirect_default_login_page() {
    if (!isset($GLOBALS['pagenow']) || 'wp-login.php' !== $GLOBALS['pagenow']) {
        return;
    }

    if ('POST' === $_SERVER['REQUEST_METHOD']) {
        return;
    }

    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';
    $native_actions = array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'postpass', 'confirmaction');

    if (in_array($action, $native_actions, true)) {
        return;
    }

    $target_url = ('register' === $action) ? website_flexi_get_signup_url() : website_flexi_get_login_url();

    if (isset($_REQUEST['redirect_to']) && '' !== $_REQUEST['redirect_to']) {
        $target_url = add_query_arg(
            'redirect_to',
            rawurlencode(wp_unslash($_REQUEST['redirect_to'])),
            $target_url
        );
    }

    wp_safe_redirect($target_url);
    exit;
}
add_action('login_init', 'website_flexi_redirect_default_login_page');

function website_flexi_hide_admin_bar_for_non_admins($show) {
    if (current_user_can('manage_options')) {
        return $show;
    }

    return false;
}
add_filter('show_admin_bar', 'website_flexi_hide_admin_bar_for_non_admins');
