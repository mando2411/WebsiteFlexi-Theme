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
            <h1>لوحة تحكم Website Flexi</h1>
            <p>مرحبًا <?php echo esc_html($current_user->display_name); ?>، تقدر تتابع كل تفاصيل شغلك من مكان واحد.</p>
        </div>

        <div class="dashboard-layout">
            <aside class="dashboard-tabs glass-card" aria-label="Dashboard Tabs">
                <button class="dashboard-tab is-active" type="button" data-tab-target="tab-overview">لوحة التحكم</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-projects">المشاريع</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-achievements">الانجازات</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-assets">الاصول / Assets</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-stats">الاحصائيات</button>
                <button class="dashboard-tab" type="button" data-tab-target="tab-account">تفاصيل حسابك</button>
                <a class="dashboard-tab dashboard-tab-link" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">تسجيل خروج</a>
            </aside>

            <div class="dashboard-panels">
                <section class="dashboard-panel is-active glass-card" id="tab-overview">
                    <h2>لوحة التحكم</h2>
                    <div class="dashboard-kpis">
                        <article><strong><?php echo esc_html((string) wp_count_posts($project_types[0])->publish); ?></strong><span>Projects</span></article>
                        <article><strong><?php echo esc_html((string) $post_count->publish); ?></strong><span>Posts</span></article>
                        <article><strong><?php echo esc_html((string) $page_count->publish); ?></strong><span>Pages</span></article>
                        <article><strong><?php echo esc_html((string) $users_count['total_users']); ?></strong><span>Users</span></article>
                    </div>
                </section>

                <section class="dashboard-panel glass-card" id="tab-projects">
                    <h2>المشاريع</h2>
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
                        <p>لا توجد مشاريع مضافة بعد.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-achievements">
                    <h2>الانجازات</h2>
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
                        <p>لا توجد انجازات مضافة حاليًا.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-assets">
                    <h2>الاصول / Assets</h2>
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
                        <p>لا توجد ملفات Assets مرفوعة حتى الآن.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-stats">
                    <h2>الاحصائيات</h2>
                    <ul class="dashboard-list dashboard-list-compact">
                        <li><strong>إجمالي المقالات:</strong> <span><?php echo esc_html((string) $post_count->publish); ?></span></li>
                        <li><strong>إجمالي الصفحات:</strong> <span><?php echo esc_html((string) $page_count->publish); ?></span></li>
                        <li><strong>إجمالي المستخدمين:</strong> <span><?php echo esc_html((string) $users_count['total_users']); ?></span></li>
                        <li><strong>إجمالي الملفات:</strong> <span><?php echo esc_html((string) wp_count_posts('attachment')->inherit); ?></span></li>
                    </ul>
                </section>

                <section class="dashboard-panel glass-card" id="tab-account">
                    <h2>تفاصيل حسابك</h2>
                    <ul class="dashboard-list dashboard-list-compact">
                        <li><strong>الاسم:</strong> <span><?php echo esc_html($current_user->display_name); ?></span></li>
                        <li><strong>اسم المستخدم:</strong> <span><?php echo esc_html($current_user->user_login); ?></span></li>
                        <li><strong>البريد:</strong> <span><?php echo esc_html($current_user->user_email); ?></span></li>
                        <li><strong>الدور:</strong> <span><?php echo esc_html(implode(', ', $current_user->roles)); ?></span></li>
                    </ul>
                    <div class="dashboard-actions">
                        <a class="btn btn-secondary" href="<?php echo esc_url(admin_url('profile.php')); ?>">تعديل الحساب</a>
                        <a class="btn btn-primary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">تسجيل خروج</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</section>
<?php
wp_reset_postdata();
get_footer();
