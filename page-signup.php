<?php
/*
Template Name: Signup Page
*/

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$login_url = website_flexi_get_login_url();

$errors = new WP_Error();
$success_message = '';

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    if (!isset($_POST['website_flexi_signup_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['website_flexi_signup_nonce'])), 'website_flexi_signup_action')) {
        $errors->add('invalid_nonce', __('Security check failed. Please try again.', 'website-flexi-theme'));
    }

    if (empty($errors->errors)) {
        $username = isset($_POST['signup_username']) ? sanitize_user(wp_unslash($_POST['signup_username']), true) : '';
        $email    = isset($_POST['signup_email']) ? sanitize_email(wp_unslash($_POST['signup_email'])) : '';
        $pass_1   = isset($_POST['signup_password']) ? (string) wp_unslash($_POST['signup_password']) : '';
        $pass_2   = isset($_POST['signup_password_confirm']) ? (string) wp_unslash($_POST['signup_password_confirm']) : '';

        if ('' === $username) {
            $errors->add('username_empty', __('Username is required.', 'website-flexi-theme'));
        } elseif (username_exists($username)) {
            $errors->add('username_exists', __('This username is already used.', 'website-flexi-theme'));
        }

        if (!is_email($email)) {
            $errors->add('email_invalid', __('Please enter a valid email address.', 'website-flexi-theme'));
        } elseif (email_exists($email)) {
            $errors->add('email_exists', __('This email is already used.', 'website-flexi-theme'));
        }

        if (strlen($pass_1) < 8) {
            $errors->add('password_short', __('Password must be at least 8 characters.', 'website-flexi-theme'));
        }

        if ($pass_1 !== $pass_2) {
            $errors->add('password_mismatch', __('Passwords do not match.', 'website-flexi-theme'));
        }

        if (empty($errors->errors)) {
            $user_id = wp_create_user($username, $pass_1, $email);

            if (is_wp_error($user_id)) {
                $errors->add('create_failed', $user_id->get_error_message());
            } else {
                $success_message = __('Account created successfully. You can now login.', 'website-flexi-theme');
            }
        }
    }
}
?>
<section class="auth-page reveal">
    <div class="container auth-container">
        <article class="auth-card glass-card">
            <p class="kicker">CREATE ACCOUNT</p>
            <h1>Sign Up</h1>

            <?php if (is_user_logged_in()) : ?>
                <p class="auth-message success">You are already logged in.</p>
                <div class="auth-links">
                    <a class="btn btn-primary" href="<?php echo esc_url(admin_url()); ?>">Go to Dashboard</a>
                </div>
            <?php elseif (!get_option('users_can_register')) : ?>
                <p class="auth-message error">Registration is currently disabled.</p>
                <div class="auth-links">
                    <a href="<?php echo esc_url($login_url); ?>">Go to login</a>
                </div>
            <?php else : ?>
                <?php if (!empty($success_message)) : ?>
                    <p class="auth-message success"><?php echo esc_html($success_message); ?></p>
                    <div class="auth-links">
                        <a class="btn btn-primary" href="<?php echo esc_url($login_url); ?>">Login Now</a>
                    </div>
                <?php else : ?>
                    <?php if (!empty($errors->errors)) : ?>
                        <div class="auth-errors" role="alert" aria-live="polite">
                            <?php foreach ($errors->get_error_messages() as $error_message) : ?>
                                <p><?php echo esc_html($error_message); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
                        <?php wp_nonce_field('website_flexi_signup_action', 'website_flexi_signup_nonce'); ?>

                        <p>
                            <label for="signup_username">Username</label>
                            <input type="text" id="signup_username" name="signup_username" value="<?php echo isset($_POST['signup_username']) ? esc_attr(wp_unslash($_POST['signup_username'])) : ''; ?>" required />
                        </p>
                        <p>
                            <label for="signup_email">Email</label>
                            <input type="email" id="signup_email" name="signup_email" value="<?php echo isset($_POST['signup_email']) ? esc_attr(wp_unslash($_POST['signup_email'])) : ''; ?>" required />
                        </p>
                        <p>
                            <label for="signup_password">Password</label>
                            <input type="password" id="signup_password" name="signup_password" minlength="8" required />
                        </p>
                        <p>
                            <label for="signup_password_confirm">Confirm Password</label>
                            <input type="password" id="signup_password_confirm" name="signup_password_confirm" minlength="8" required />
                        </p>
                        <p>
                            <button class="btn btn-primary" type="submit">Create Account</button>
                        </p>
                    </form>
                    <div class="auth-links">
                        <a href="<?php echo esc_url($login_url); ?>">Already have an account? Login</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php
get_footer();
