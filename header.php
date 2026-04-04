<?php
if (!defined('ABSPATH')) {
    exit;
}

$login_url  = website_flexi_get_login_url();
$signup_url = website_flexi_get_signup_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
            <span class="brand-dot"></span>
            <span class="brand-text"><?php bloginfo('name'); ?></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="header-tools" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <div class="header-tools" id="header-tools">
            <nav class="main-nav" aria-label="Primary Navigation">
                <ul class="nav-menu">
                    <li><a href="<?php echo esc_url(home_url('/about')); ?>">About</a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a></li>
                </ul>
            </nav>
            <div class="auth-actions" aria-label="Authentication actions">
                <?php if (is_user_logged_in()) : ?>
                    <a class="btn btn-secondary btn-auth" href="<?php echo esc_url(admin_url()); ?>">Dashboard</a>
                    <a class="btn btn-primary btn-auth" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
                <?php else : ?>
                    <a class="btn btn-secondary btn-auth" href="<?php echo esc_url($login_url); ?>">Login</a>
                    <a class="btn btn-primary btn-auth" href="<?php echo esc_url($signup_url); ?>">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<main class="site-main">
