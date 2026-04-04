<?php
/*
Template Name: Login Page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$signup_url = website_flexi_get_signup_url();
?>
<section class="auth-page reveal">
    <div class="container auth-container">
        <article class="auth-card glass-card">
            <p class="kicker">WELCOME BACK</p>
            <h1>Login to Your Account</h1>

            <?php if (is_user_logged_in()) : ?>
                <p class="auth-message success">You are already logged in.</p>
                <div class="auth-links">
                    <a class="btn btn-primary" href="<?php echo esc_url(admin_url()); ?>">Go to Dashboard</a>
                    <a class="btn btn-secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
                </div>
            <?php else : ?>
                <?php
                wp_login_form(
                    array(
                        'echo'           => true,
                        'redirect'       => esc_url(home_url('/')),
                        'remember'       => true,
                        'form_id'        => 'website-flexi-loginform',
                        'id_username'    => 'website-flexi-user-login',
                        'id_password'    => 'website-flexi-user-pass',
                        'id_remember'    => 'website-flexi-rememberme',
                        'id_submit'      => 'website-flexi-login-submit',
                        'label_username' => __('Email or Username', 'website-flexi-theme'),
                        'label_password' => __('Password', 'website-flexi-theme'),
                        'label_remember' => __('Remember Me', 'website-flexi-theme'),
                        'label_log_in'   => __('Login', 'website-flexi-theme'),
                    )
                );
                ?>
                <div class="auth-links">
                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Lost your password?</a>
                    <a href="<?php echo esc_url($signup_url); ?>">Create new account</a>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php
get_footer();
