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
$is_admin_user = current_user_can('manage_options');

$allowed_dashboard_tabs = array(
    'tab-overview',
    'tab-projects',
    'tab-achievements',
    'tab-assets',
    'tab-stats',
    'tab-account',
    'tab-admin-requests',
);

$initial_tab = isset($_GET['dashboard_tab']) ? sanitize_key(wp_unslash($_GET['dashboard_tab'])) : 'tab-overview';
if (!in_array($initial_tab, $allowed_dashboard_tabs, true)) {
    $initial_tab = 'tab-overview';
}

$request_status_labels = array(
    'submitted' => 'Submitted',
    'in_review' => 'In Review',
    'completed' => 'Completed',
);

$service_catalog = array(
    'Facebook',
    'Instagram',
    'TikTok',
    'YouTube',
    'Website',
    'Google Business Profile',
    'Google Merchant',
    'Content Creator',
    'Logo',
    'Design',
    'Reels',
    'SEO',
);

$service_actions = array(
    'Build from scratch',
    'Edit & Improve',
    'Management',
    'Ads',
    'Fix an issue',
);

$form_errors = array();
$form_success = '';
$about_business = '';
$business_type = '';
$legal_status = '';
$needs_full_service = false;
$full_goals = '';
$service_items = array(
    array(
        'service' => '',
        'actions' => array(),
        'description' => '',
    ),
);

$admin_form_errors = array();
$admin_form_success = '';

$client_status_notifications = array();

$admin_selected_request_id = 0;
if ($is_admin_user && isset($_GET['request_id'])) {
    $admin_selected_request_id = absint($_GET['request_id']);
}

if ($is_admin_user && isset($_POST['website_flexi_admin_pick_request'])) {
    if (isset($_POST['website_flexi_admin_pick_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['website_flexi_admin_pick_nonce'])), 'website_flexi_admin_pick_action')) {
        $admin_selected_request_id = isset($_POST['pick_request_id']) ? absint($_POST['pick_request_id']) : 0;
        $initial_tab = 'tab-admin-requests';
    } else {
        $admin_form_errors[] = 'Security check failed while opening the request.';
    }
}

$admin_selected_request = null;
$admin_about_business = '';
$admin_business_type = '';
$admin_legal_status = '';
$admin_needs_full_service = false;
$admin_full_goals = '';
$admin_service_items = array(
    array(
        'service' => '',
        'actions' => array(),
        'description' => '',
    ),
);
$admin_request_status = 'submitted';

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['website_flexi_project_submit'])) {
    $initial_tab = 'tab-projects';

    if (!isset($_POST['website_flexi_project_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['website_flexi_project_nonce'])), 'website_flexi_project_action')) {
        $form_errors[] = 'Security check failed. Please refresh and try again.';
    }

    $about_business = isset($_POST['about_business']) ? sanitize_textarea_field(wp_unslash($_POST['about_business'])) : '';
    $business_type = isset($_POST['business_type']) ? sanitize_text_field(wp_unslash($_POST['business_type'])) : '';
    $legal_status = isset($_POST['legal_status']) ? sanitize_text_field(wp_unslash($_POST['legal_status'])) : '';
    $needs_full_service = isset($_POST['needs_full_service']);
    $full_goals = isset($_POST['full_goals']) ? sanitize_textarea_field(wp_unslash($_POST['full_goals'])) : '';

    if ('' === $about_business) {
        $form_errors[] = 'Please provide an introduction about yourself and your business.';
    }

    if ('' === $business_type) {
        $form_errors[] = 'Please provide your business type.';
    }

    if ('' === $legal_status) {
        $form_errors[] = 'Please select your legal status.';
    }

    $raw_service_items = isset($_POST['service_items']) && is_array($_POST['service_items']) ? $_POST['service_items'] : array();
    $service_items = array();

    foreach ($raw_service_items as $raw_item) {
        if (!is_array($raw_item)) {
            continue;
        }

        $service = isset($raw_item['service']) ? sanitize_text_field(wp_unslash($raw_item['service'])) : '';
        $description = isset($raw_item['description']) ? sanitize_textarea_field(wp_unslash($raw_item['description'])) : '';
        $actions_raw = isset($raw_item['actions']) && is_array($raw_item['actions']) ? $raw_item['actions'] : array();
        $actions = array();

        foreach ($actions_raw as $action_item) {
            $clean_action = sanitize_text_field(wp_unslash($action_item));
            if (in_array($clean_action, $service_actions, true)) {
                $actions[] = $clean_action;
            }
        }

        if ('' === $service && '' === $description && empty($actions)) {
            continue;
        }

        $service_items[] = array(
            'service' => in_array($service, $service_catalog, true) ? $service : '',
            'actions' => $actions,
            'description' => $description,
        );
    }

    if (empty($service_items)) {
        $form_errors[] = 'Please add at least one service request.';
    }

    foreach ($service_items as $index => $service_item) {
        if ('' === $service_item['service']) {
            $form_errors[] = 'Please choose a service in request block #' . ($index + 1) . '.';
        }

        if (empty($service_item['actions'])) {
            $form_errors[] = 'Please choose at least one action in request block #' . ($index + 1) . '.';
        }

        if ('' === $service_item['description']) {
            $form_errors[] = 'Please add a description in request block #' . ($index + 1) . '.';
        }
    }

    if ($needs_full_service && '' === $full_goals) {
        $form_errors[] = 'Please describe your full goals for the full-service option.';
    }

    if (empty($form_errors)) {
        $request_id = wp_insert_post(
            array(
                'post_type'    => 'wf_project_request',
                'post_status'  => 'publish',
                'post_title'   => 'Project Request - ' . $current_user->display_name . ' - ' . wp_date('Y-m-d H:i'),
                'post_content' => $about_business,
                'post_author'  => get_current_user_id(),
            ),
            true
        );

        if (is_wp_error($request_id)) {
            $form_errors[] = $request_id->get_error_message();
        } else {
            update_post_meta($request_id, 'wf_business_type', $business_type);
            update_post_meta($request_id, 'wf_legal_status', $legal_status);
            update_post_meta($request_id, 'wf_needs_full_service', $needs_full_service ? 'yes' : 'no');
            update_post_meta($request_id, 'wf_full_goals', $full_goals);
            update_post_meta($request_id, 'wf_service_items', $service_items);
            update_post_meta($request_id, 'wf_request_status', 'submitted');

            $form_success = 'Your project request has been submitted successfully. Our team will review it carefully.';

            $about_business = '';
            $business_type = '';
            $legal_status = '';
            $needs_full_service = false;
            $full_goals = '';
            $service_items = array(
                array(
                    'service' => '',
                    'actions' => array(),
                    'description' => '',
                ),
            );
        }
    }
}

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['website_flexi_admin_request_update']) && $is_admin_user) {
    $initial_tab = 'tab-admin-requests';

    if (!isset($_POST['website_flexi_admin_request_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['website_flexi_admin_request_nonce'])), 'website_flexi_admin_request_action')) {
        $admin_form_errors[] = 'Security check failed. Please refresh and try again.';
    }

    $admin_selected_request_id = isset($_POST['admin_request_id']) ? absint($_POST['admin_request_id']) : 0;
    $admin_selected_request = $admin_selected_request_id ? get_post($admin_selected_request_id) : null;

    if (!$admin_selected_request || 'wf_project_request' !== $admin_selected_request->post_type) {
        $admin_form_errors[] = 'Invalid request selected.';
    }

    $admin_about_business = isset($_POST['admin_about_business']) ? sanitize_textarea_field(wp_unslash($_POST['admin_about_business'])) : '';
    $admin_business_type = isset($_POST['admin_business_type']) ? sanitize_text_field(wp_unslash($_POST['admin_business_type'])) : '';
    $admin_legal_status = isset($_POST['admin_legal_status']) ? sanitize_text_field(wp_unslash($_POST['admin_legal_status'])) : '';
    $admin_needs_full_service = isset($_POST['admin_needs_full_service']);
    $admin_full_goals = isset($_POST['admin_full_goals']) ? sanitize_textarea_field(wp_unslash($_POST['admin_full_goals'])) : '';
    $admin_request_status = isset($_POST['admin_request_status']) ? sanitize_key(wp_unslash($_POST['admin_request_status'])) : 'submitted';

    if (!isset($request_status_labels[$admin_request_status])) {
        $admin_request_status = 'submitted';
    }

    $raw_admin_items = isset($_POST['admin_service_items']) && is_array($_POST['admin_service_items']) ? $_POST['admin_service_items'] : array();
    $admin_service_items = array();

    foreach ($raw_admin_items as $raw_item) {
        if (!is_array($raw_item)) {
            continue;
        }

        $service = isset($raw_item['service']) ? sanitize_text_field(wp_unslash($raw_item['service'])) : '';
        $description = isset($raw_item['description']) ? sanitize_textarea_field(wp_unslash($raw_item['description'])) : '';
        $actions_raw = isset($raw_item['actions']) && is_array($raw_item['actions']) ? $raw_item['actions'] : array();
        $actions = array();

        foreach ($actions_raw as $action_item) {
            $clean_action = sanitize_text_field(wp_unslash($action_item));
            if (in_array($clean_action, $service_actions, true)) {
                $actions[] = $clean_action;
            }
        }

        if ('' === $service && '' === $description && empty($actions)) {
            continue;
        }

        $admin_service_items[] = array(
            'service' => in_array($service, $service_catalog, true) ? $service : '',
            'actions' => $actions,
            'description' => $description,
        );
    }

    if (empty($admin_service_items)) {
        $admin_form_errors[] = 'At least one service line is required.';
    }

    if (empty($admin_form_errors) && $admin_selected_request) {
        $previous_status = (string) get_post_meta($admin_selected_request->ID, 'wf_request_status', true);

        wp_update_post(
            array(
                'ID' => $admin_selected_request->ID,
                'post_content' => $admin_about_business,
            )
        );

        update_post_meta($admin_selected_request->ID, 'wf_business_type', $admin_business_type);
        update_post_meta($admin_selected_request->ID, 'wf_legal_status', $admin_legal_status);
        update_post_meta($admin_selected_request->ID, 'wf_needs_full_service', $admin_needs_full_service ? 'yes' : 'no');
        update_post_meta($admin_selected_request->ID, 'wf_full_goals', $admin_full_goals);
        update_post_meta($admin_selected_request->ID, 'wf_service_items', $admin_service_items);
        update_post_meta($admin_selected_request->ID, 'wf_request_status', $admin_request_status);

        if ($previous_status !== $admin_request_status) {
            update_post_meta($admin_selected_request->ID, 'wf_status_notification', 'unread');
            update_post_meta($admin_selected_request->ID, 'wf_status_changed_at', wp_date('Y-m-d H:i:s'));
        }

        $admin_form_success = 'Request updated successfully. Changes are now saved.';
    }
}

if ($is_admin_user && $admin_selected_request_id > 0) {
    $admin_selected_request = get_post($admin_selected_request_id);

    if ($admin_selected_request && 'wf_project_request' === $admin_selected_request->post_type) {
        $admin_about_business = (string) $admin_selected_request->post_content;
        $admin_business_type = (string) get_post_meta($admin_selected_request->ID, 'wf_business_type', true);
        $admin_legal_status = (string) get_post_meta($admin_selected_request->ID, 'wf_legal_status', true);
        $admin_needs_full_service = 'yes' === (string) get_post_meta($admin_selected_request->ID, 'wf_needs_full_service', true);
        $admin_full_goals = (string) get_post_meta($admin_selected_request->ID, 'wf_full_goals', true);
        $admin_request_status = (string) get_post_meta($admin_selected_request->ID, 'wf_request_status', true);

        if (!isset($request_status_labels[$admin_request_status])) {
            $admin_request_status = 'submitted';
        }

        $stored_items = get_post_meta($admin_selected_request->ID, 'wf_service_items', true);
        if (is_array($stored_items) && !empty($stored_items)) {
            $admin_service_items = $stored_items;
        }
    }
}

if ($is_admin_user && (isset($_POST['website_flexi_admin_request_update']) || isset($_GET['request_id']))) {
    $initial_tab = 'tab-admin-requests';
}

$user_request_ids = get_posts(
    array(
        'post_type'      => 'wf_project_request',
        'post_status'    => 'publish',
        'author'         => get_current_user_id(),
        'fields'         => 'ids',
        'posts_per_page' => -1,
    )
);

$submitted_count = 0;
$in_review_count = 0;
$completed_count = 0;
$active_services_count = 0;

foreach ($user_request_ids as $request_id) {
    $status = (string) get_post_meta($request_id, 'wf_request_status', true);
    $items = get_post_meta($request_id, 'wf_service_items', true);
    $items_count = is_array($items) ? count($items) : 0;
    $notification_flag = (string) get_post_meta($request_id, 'wf_status_notification', true);

    $active_services_count += $items_count;

    if ('completed' === $status) {
        $completed_count++;
    } elseif ('in_review' === $status) {
        $in_review_count++;
    } else {
        $submitted_count++;
    }

    if ('unread' === $notification_flag) {
        $status_label = isset($request_status_labels[$status]) ? $request_status_labels[$status] : 'Submitted';
        $client_status_notifications[] = array(
            'title' => get_the_title($request_id),
            'status' => $status_label,
            'changed_at' => (string) get_post_meta($request_id, 'wf_status_changed_at', true),
        );

        update_post_meta($request_id, 'wf_status_notification', 'read');
    }
}

$recent_requests = new WP_Query(
    array(
        'post_type'      => 'wf_project_request',
        'post_status'    => 'publish',
        'author'         => get_current_user_id(),
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

$admin_requests_query = null;
if ($is_admin_user) {
    $admin_requests_query = new WP_Query(
        array(
            'post_type'      => 'wf_project_request',
            'post_status'    => 'publish',
            'posts_per_page' => 25,
        )
    );
}
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
                <?php if ($is_admin_user) : ?>
                    <button class="dashboard-tab" type="button" data-tab-target="tab-admin-requests">Admin Requests</button>
                <?php endif; ?>
                <a class="dashboard-tab dashboard-tab-link" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
            </aside>

            <div class="dashboard-panels" data-initial-tab="<?php echo esc_attr($initial_tab); ?>">
                <section class="dashboard-panel is-active glass-card" id="tab-overview">
                    <h2>Dashboard</h2>
                    <div class="dashboard-kpis">
                        <article><strong><?php echo esc_html((string) $submitted_count); ?></strong><span>Submitted Requests</span></article>
                        <article><strong><?php echo esc_html((string) $in_review_count); ?></strong><span>In Review</span></article>
                        <article><strong><?php echo esc_html((string) $completed_count); ?></strong><span>Completed</span></article>
                        <article><strong><?php echo esc_html((string) $active_services_count); ?></strong><span>Active Service Lines</span></article>
                    </div>

                    <div class="dashboard-actions">
                        <button class="btn btn-primary" type="button" data-tab-target="tab-projects" id="open-new-project-request">Apply for a New Project</button>
                    </div>

                    <h3>Recent Requests</h3>
                    <?php if (!empty($client_status_notifications)) : ?>
                        <div class="status-notifications" role="status" aria-live="polite">
                            <h3>Status Updates</h3>
                            <ul class="dashboard-list dashboard-list-compact">
                                <?php foreach ($client_status_notifications as $status_notice) : ?>
                                    <li>
                                        <strong><?php echo esc_html($status_notice['title']); ?></strong>
                                        <span>
                                            <?php echo esc_html('New status: ' . $status_notice['status']); ?>
                                            <?php if (!empty($status_notice['changed_at'])) : ?>
                                                <?php echo esc_html(' | Updated: ' . $status_notice['changed_at']); ?>
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($recent_requests->have_posts()) : ?>
                        <ul class="dashboard-list">
                            <?php while ($recent_requests->have_posts()) : $recent_requests->the_post(); ?>
                                <?php $request_status = (string) get_post_meta(get_the_ID(), 'wf_request_status', true); ?>
                                <li>
                                    <span><?php the_title(); ?></span>
                                    <span><?php echo esc_html('Status: ' . ($request_status ? ucfirst(str_replace('_', ' ', $request_status)) : 'Submitted')); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else : ?>
                        <p>No project requests yet. Start by clicking "Apply for a New Project".</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-panel glass-card" id="tab-projects">
                    <h2>Projects</h2>
                    <p class="project-request-hint">If you are a new business and want Website Flexi to handle all digital marketing services, enable the option below and describe your full goals.</p>

                    <?php if (!empty($form_success)) : ?>
                        <p class="auth-message success"><?php echo esc_html($form_success); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($form_errors)) : ?>
                        <div class="auth-errors" role="alert" aria-live="polite">
                            <?php foreach ($form_errors as $form_error) : ?>
                                <p><?php echo esc_html($form_error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form class="project-request-form" method="post" action="<?php echo esc_url(website_flexi_get_dashboard_url()); ?>#tab-projects">
                        <?php wp_nonce_field('website_flexi_project_action', 'website_flexi_project_nonce'); ?>

                        <p>
                            <label for="about_business">About You / Your Business</label>
                            <textarea id="about_business" name="about_business" rows="4" required><?php echo esc_textarea($about_business); ?></textarea>
                        </p>

                        <div class="project-request-grid">
                            <p>
                                <label for="business_type">Business Type</label>
                                <input type="text" id="business_type" name="business_type" value="<?php echo esc_attr($business_type); ?>" placeholder="Store, agency, startup, personal brand..." required />
                            </p>

                            <p>
                                <label for="legal_status">Legal Status</label>
                                <select id="legal_status" name="legal_status" required>
                                    <option value="">Select status...</option>
                                    <option value="fully_registered" <?php selected($legal_status, 'fully_registered'); ?>>Commercial register + tax card + licenses</option>
                                    <option value="partially_registered" <?php selected($legal_status, 'partially_registered'); ?>>Partially registered</option>
                                    <option value="not_registered" <?php selected($legal_status, 'not_registered'); ?>>Not registered yet</option>
                                </select>
                            </p>
                        </div>

                        <p class="full-service-toggle">
                            <label>
                                <input type="checkbox" name="needs_full_service" value="1" <?php checked($needs_full_service); ?> data-full-service-toggle />
                                I am a new business and want Website Flexi to manage all digital marketing services.
                            </label>
                        </p>

                        <p class="full-service-goals <?php echo $needs_full_service ? 'is-visible' : ''; ?>" data-full-service-goals>
                            <label for="full_goals">Full Goals and Requirements</label>
                            <textarea id="full_goals" name="full_goals" rows="4" placeholder="Describe your full goals, timeline, and expectations..."><?php echo esc_textarea($full_goals); ?></textarea>
                        </p>

                        <div class="service-items" data-service-items>
                            <?php foreach ($service_items as $index => $service_item) : ?>
                                <article class="service-item-card" data-service-item>
                                    <div class="project-request-grid">
                                        <p>
                                            <label>Service Needed</label>
                                            <select name="service_items[<?php echo esc_attr((string) $index); ?>][service]" required>
                                                <option value="">Select service...</option>
                                                <?php foreach ($service_catalog as $service_name) : ?>
                                                    <option value="<?php echo esc_attr($service_name); ?>" <?php selected($service_item['service'], $service_name); ?>><?php echo esc_html($service_name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </p>

                                        <p>
                                            <label>Actions (multi-select)</label>
                                            <select name="service_items[<?php echo esc_attr((string) $index); ?>][actions][]" multiple size="5" required>
                                                <?php foreach ($service_actions as $action_name) : ?>
                                                    <option value="<?php echo esc_attr($action_name); ?>" <?php echo in_array($action_name, $service_item['actions'], true) ? 'selected' : ''; ?>><?php echo esc_html($action_name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </p>
                                    </div>

                                    <p>
                                        <label>Detailed Description</label>
                                        <textarea name="service_items[<?php echo esc_attr((string) $index); ?>][description]" rows="4" placeholder="Describe the issue or requested work in detail..." required><?php echo esc_textarea($service_item['description']); ?></textarea>
                                    </p>
                                </article>
                            <?php endforeach; ?>
                        </div>

                        <p class="project-request-hint">Do not worry. Your request will be reviewed carefully. If your selection is not fully correct, it will be adjusted during review.</p>

                        <div class="dashboard-actions">
                            <button class="btn btn-secondary" type="button" data-add-service-item>Add another service</button>
                            <button class="btn btn-primary" type="submit" name="website_flexi_project_submit" value="1">Submit Request</button>
                        </div>
                    </form>

                    <template id="service-item-template">
                        <article class="service-item-card" data-service-item>
                            <div class="project-request-grid">
                                <p>
                                    <label>Service Needed</label>
                                    <select data-name="service" required>
                                        <option value="">Select service...</option>
                                        <?php foreach ($service_catalog as $service_name) : ?>
                                            <option value="<?php echo esc_attr($service_name); ?>"><?php echo esc_html($service_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>

                                <p>
                                    <label>Actions (multi-select)</label>
                                    <select data-name="actions" multiple size="5" required>
                                        <?php foreach ($service_actions as $action_name) : ?>
                                            <option value="<?php echo esc_attr($action_name); ?>"><?php echo esc_html($action_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                            </div>

                            <p>
                                <label>Detailed Description</label>
                                <textarea data-name="description" rows="4" placeholder="Describe the issue or requested work in detail..." required></textarea>
                            </p>
                        </article>
                    </template>
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
                        <li><strong>Total Submitted Requests:</strong> <span><?php echo esc_html((string) $submitted_count); ?></span></li>
                        <li><strong>Total In Review:</strong> <span><?php echo esc_html((string) $in_review_count); ?></span></li>
                        <li><strong>Total Completed:</strong> <span><?php echo esc_html((string) $completed_count); ?></span></li>
                        <li><strong>Total Service Lines:</strong> <span><?php echo esc_html((string) $active_services_count); ?></span></li>
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

                <?php if ($is_admin_user) : ?>
                    <section class="dashboard-panel glass-card" id="tab-admin-requests">
                        <h2>Admin Requests Review</h2>

                        <?php if (!empty($admin_form_success)) : ?>
                            <p class="auth-message success"><?php echo esc_html($admin_form_success); ?></p>
                        <?php endif; ?>

                        <?php if (!empty($admin_form_errors)) : ?>
                            <div class="auth-errors" role="alert" aria-live="polite">
                                <?php foreach ($admin_form_errors as $admin_error) : ?>
                                    <p><?php echo esc_html($admin_error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($admin_requests_query && $admin_requests_query->have_posts()) : ?>
                            <ul class="dashboard-list">
                                <?php while ($admin_requests_query->have_posts()) : $admin_requests_query->the_post(); ?>
                                    <?php
                                    $row_status = (string) get_post_meta(get_the_ID(), 'wf_request_status', true);
                                    $row_status = isset($request_status_labels[$row_status]) ? $request_status_labels[$row_status] : 'Submitted';
                                    $edit_link = add_query_arg(
                                        array(
                                            'dashboard_tab' => 'tab-admin-requests',
                                            'request_id'    => get_the_ID(),
                                        ),
                                        website_flexi_get_dashboard_url()
                                    ) . '#tab-admin-requests';
                                    ?>
                                    <li>
                                        <span>
                                            <?php the_title(); ?>
                                            <small>(<?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', get_the_ID()))); ?>)</small>
                                        </span>
                                        <span>
                                            <?php echo esc_html($row_status); ?> |
                                            <form class="inline-review-form" method="post" action="<?php echo esc_url(website_flexi_get_dashboard_url()); ?>#tab-admin-requests">
                                                <?php wp_nonce_field('website_flexi_admin_pick_action', 'website_flexi_admin_pick_nonce'); ?>
                                                <input type="hidden" name="pick_request_id" value="<?php echo esc_attr((string) get_the_ID()); ?>" />
                                                <input type="hidden" name="website_flexi_admin_pick_request" value="1" />
                                                <button type="submit" class="inline-link-button">Review / Edit</button>
                                            </form>
                                        </span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else : ?>
                            <p>No submitted requests yet.</p>
                        <?php endif; ?>

                        <?php if ($admin_selected_request && 'wf_project_request' === $admin_selected_request->post_type) : ?>
                            <hr />
                            <h3>Edit Request</h3>
                            <p><strong>Request:</strong> <?php echo esc_html($admin_selected_request->post_title); ?></p>

                            <form class="project-request-form" method="post" action="<?php echo esc_url(add_query_arg(array('dashboard_tab' => 'tab-admin-requests', 'request_id' => $admin_selected_request->ID), website_flexi_get_dashboard_url())); ?>#tab-admin-requests">
                                <?php wp_nonce_field('website_flexi_admin_request_action', 'website_flexi_admin_request_nonce'); ?>
                                <input type="hidden" name="admin_request_id" value="<?php echo esc_attr((string) $admin_selected_request->ID); ?>" />

                                <p>
                                    <label for="admin_request_status">Request Status</label>
                                    <select id="admin_request_status" name="admin_request_status" required>
                                        <?php foreach ($request_status_labels as $status_key => $status_label) : ?>
                                            <option value="<?php echo esc_attr($status_key); ?>" <?php selected($admin_request_status, $status_key); ?>><?php echo esc_html($status_label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>

                                <p>
                                    <label for="admin_about_business">About Business</label>
                                    <textarea id="admin_about_business" name="admin_about_business" rows="4" required><?php echo esc_textarea($admin_about_business); ?></textarea>
                                </p>

                                <div class="project-request-grid">
                                    <p>
                                        <label for="admin_business_type">Business Type</label>
                                        <input type="text" id="admin_business_type" name="admin_business_type" value="<?php echo esc_attr($admin_business_type); ?>" required />
                                    </p>

                                    <p>
                                        <label for="admin_legal_status">Legal Status</label>
                                        <select id="admin_legal_status" name="admin_legal_status" required>
                                            <option value="">Select status...</option>
                                            <option value="fully_registered" <?php selected($admin_legal_status, 'fully_registered'); ?>>Commercial register + tax card + licenses</option>
                                            <option value="partially_registered" <?php selected($admin_legal_status, 'partially_registered'); ?>>Partially registered</option>
                                            <option value="not_registered" <?php selected($admin_legal_status, 'not_registered'); ?>>Not registered yet</option>
                                        </select>
                                    </p>
                                </div>

                                <p class="full-service-toggle">
                                    <label>
                                        <input type="checkbox" name="admin_needs_full_service" value="1" <?php checked($admin_needs_full_service); ?> data-admin-full-service-toggle />
                                        Enable full-service handling for this request.
                                    </label>
                                </p>

                                <p class="full-service-goals <?php echo $admin_needs_full_service ? 'is-visible' : ''; ?>" data-admin-full-service-goals>
                                    <label for="admin_full_goals">Full Goals and Requirements</label>
                                    <textarea id="admin_full_goals" name="admin_full_goals" rows="4"><?php echo esc_textarea($admin_full_goals); ?></textarea>
                                </p>

                                <div class="service-items" data-admin-service-items>
                                    <?php foreach ($admin_service_items as $admin_index => $admin_service_item) : ?>
                                        <article class="service-item-card" data-admin-service-item>
                                            <div class="project-request-grid">
                                                <p>
                                                    <label>Service Needed</label>
                                                    <select name="admin_service_items[<?php echo esc_attr((string) $admin_index); ?>][service]" required>
                                                        <option value="">Select service...</option>
                                                        <?php foreach ($service_catalog as $service_name) : ?>
                                                            <option value="<?php echo esc_attr($service_name); ?>" <?php selected(isset($admin_service_item['service']) ? $admin_service_item['service'] : '', $service_name); ?>><?php echo esc_html($service_name); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </p>

                                                <p>
                                                    <label>Actions (multi-select)</label>
                                                    <select name="admin_service_items[<?php echo esc_attr((string) $admin_index); ?>][actions][]" multiple size="5" required>
                                                        <?php foreach ($service_actions as $action_name) : ?>
                                                            <option value="<?php echo esc_attr($action_name); ?>" <?php echo (isset($admin_service_item['actions']) && is_array($admin_service_item['actions']) && in_array($action_name, $admin_service_item['actions'], true)) ? 'selected' : ''; ?>><?php echo esc_html($action_name); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </p>
                                            </div>

                                            <p>
                                                <label>Detailed Description</label>
                                                <textarea name="admin_service_items[<?php echo esc_attr((string) $admin_index); ?>][description]" rows="4" required><?php echo esc_textarea(isset($admin_service_item['description']) ? $admin_service_item['description'] : ''); ?></textarea>
                                            </p>
                                        </article>
                                    <?php endforeach; ?>
                                </div>

                                <div class="dashboard-actions">
                                    <button class="btn btn-secondary" type="button" data-add-admin-service-item>Add another service</button>
                                    <button class="btn btn-primary" type="submit" name="website_flexi_admin_request_update" value="1">Save Admin Changes</button>
                                </div>
                            </form>

                            <template id="admin-service-item-template">
                                <article class="service-item-card" data-admin-service-item>
                                    <div class="project-request-grid">
                                        <p>
                                            <label>Service Needed</label>
                                            <select data-admin-name="service" required>
                                                <option value="">Select service...</option>
                                                <?php foreach ($service_catalog as $service_name) : ?>
                                                    <option value="<?php echo esc_attr($service_name); ?>"><?php echo esc_html($service_name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </p>

                                        <p>
                                            <label>Actions (multi-select)</label>
                                            <select data-admin-name="actions" multiple size="5" required>
                                                <?php foreach ($service_actions as $action_name) : ?>
                                                    <option value="<?php echo esc_attr($action_name); ?>"><?php echo esc_html($action_name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </p>
                                    </div>

                                    <p>
                                        <label>Detailed Description</label>
                                        <textarea data-admin-name="description" rows="4" required></textarea>
                                    </p>
                                </article>
                            </template>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
wp_reset_postdata();
get_footer();
