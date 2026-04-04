<?php
/*
Template Name: Dashboard Page
*/

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_safe_redirect(
        add_query_arg(
            'redirect_to',
            rawurlencode(website_flexi_get_dashboard_url()),
            website_flexi_get_login_url()
        )
    );
    exit;
}

get_header();

$current_user = wp_get_current_user();

$project_types = array();
if (post_type_exists('project')) {
    $project_types[] = 'project';
}
if (post_type_exists('portfolio')) {
    $project_types[] = 'portfolio';
}
if (empty($project_types)) {
    $project_types[] = 'post';
}

$projects_query = new WP_Query(
    array(
        'post_type'      => $project_types,
        'post_status'    => 'publish',
        'posts_per_page' => 6,
    )
);

$achievement_args = array(
    'post_type'      => 'post',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
);

if (term_exists('achievements', 'category')) {
    $achievement_args['category_name'] = 'achievements';
}

$achievements_query = new WP_Query($achievement_args);

$assets_query = new WP_Query(
    array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 8,
    )
);

$post_count = wp_count_posts('post');
$page_count = wp_count_posts('page');
$users_count = count_users();
?>
<section class="dashboard-page reveal">
    <div class="container">
        <div class="dashboard-head glass-card">
            <p class="kicker">WELCOME</p>
            <h1>Website Flexi Dashboard</h1>
            <p>Hello <?php echo esc_html($current_user->display_name); ?>, you can track all your work details from one place.</p>
        </div>

        <div class="dashboard-layout">
            <aside class="dashboard-tabs glass-card" aria-label="Dashboard Tabs">
                <button class="dashboard-tab is-active" type="button" data-tab-target="tab-overview">Dashboard</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-projects">Projects</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-achievements">Achievements</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-assets">Assets</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-stats">Statistics</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-account">Account Details</button>
                <a class="dashboard-tab dashboard-tab-link" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
            </aside>

            <div class="dashboard-panels">
                <section class="dashboard-panel is-active glass-card" id="tab-overview">
                    <h2>Dashboard</h2>
                    <div class="dashboard-kpis">
                        <article><strong><?php echo esc_html((string) wp_count_posts($project_types[0])->publish); ?></strong><span>Projects</span></article>
                        <article><strong><?php echo esc_html((string) $post_count->publish); ?></strong><span>Posts</span></article>
                        <article><strong><?php echo esc_html((string) $page_count->publish); ?></strong><span>Pages</span></article>
                        <article><strong><?php echo esc_html((string) $users_count['total_users']); ?></strong><span>Users</span></article>
                    </div>
                </section>

                <section class="dashboard-panel glass-card" id="tab-projects">
                    <h2>Projects</h2>
                    <?php if ($projects_query->have_posts()) : ?>
                        <ul class="dashboard-list">
                            <?php while ($projects_query->have_posts()) : $projects_query->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <span><?php echo esc_html(get_the_date()); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p>No projects have been added yet.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-achievements">
                    <h2>Achievements</h2>
                    <?php if ($achievements_query->have_posts()) : ?>
                        <ul class="dashboard-list">
                            <?php while ($achievements_query->have_posts()) : $achievements_query->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <span><?php echo esc_html(get_the_date()); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p>No achievements are available at the moment.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-assets">
                    <h2>Assets</h2>
                    <?php if ($assets_query->have_posts()) : ?>
                        <ul class="dashboard-list">
                            <?php while ($assets_query->have_posts()) : $assets_query->the_post(); ?>
                                <li>
                                    <a href="<?php echo esc_url(wp_get_attachment_url(get_the_ID())); ?>" target="_blank" rel="noopener noreferrer"><?php the_title(); ?></a>
                                    <span><?php echo esc_html(get_the_date()); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p>No assets have been uploaded yet.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-stats">
                    <h2>Statistics</h2>
                    <ul class="dashboard-list dashboard-list-compact">
                        <li><strong>Total Posts:</strong> <span><?php echo esc_html((string) $post_count->publish); ?></span></li>
                        <li><strong>Total Pages:</strong> <span><?php echo esc_html((string) $page_count->publish); ?></span></li>
                        <li><strong>Total Users:</strong> <span><?php echo esc_html((string) $users_count['total_users']); ?></span></li>
                        <li><strong>Total Files:</strong> <span><?php echo esc_html((string) wp_count_posts('attachment')->inherit); ?></span></li>
                    </ul>
                </section>

                <section class="dashboard-panel glass-card" id="tab-account">
                    <h2>Account Details</h2>
                    <ul class="dashboard-list dashboard-list-compact">
                        <li><strong>Name:</strong> <span><?php echo esc_html($current_user->display_name); ?></span></li>
                        <li><strong>Username:</strong> <span><?php echo esc_html($current_user->user_login); ?></span></li>
                        <li><strong>Email:</strong> <span><?php echo esc_html($current_user->user_email); ?></span></li>
                        <li><strong>Role:</strong> <span><?php echo esc_html(implode(', ', $current_user->roles)); ?></span></li>
                    </ul>
                    <div class="dashboard-actions">
                        <a class="btn btn-secondary" href="<?php echo esc_url(admin_url('profile.php')); ?>">Edit Account</a>
                        <a class="btn btn-primary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
<?php
wp_reset_postdata();
get_footer();
