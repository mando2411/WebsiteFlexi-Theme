<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$offers = array(
    array(
        'title' => 'Exclusive Cash Payment Offers',
        'text'  => 'Pay in cash and enjoy special discounts on web design, development, and digital services.',
    ),
    array(
        'title' => 'Pay Later in Easy Installments',
        'text'  => 'Start with a small down payment and complete the rest through flexible installment plans.',
    ),
    array(
        'title' => 'Get Your Website for Free',
        'text'  => 'Apply for the free website offer and expand your customer base with zero upfront stress.',
    ),
    array(
        'title' => 'Support and Maintenance',
        'text'  => '24/7 support, regular updates, and ongoing maintenance to keep your site fast and secure.',
    ),
);

$services = array(
    array(
        'title' => 'Social Media Management',
        'text'  => 'Content planning, posting, audience growth, and engagement across all major social platforms.',
    ),
    array(
        'title' => 'Paid Advertising',
        'text'  => 'Performance campaigns on Meta, Google, and more to increase leads, sales, and brand awareness.',
    ),
    array(
        'title' => 'Website Design and Development',
        'text'  => 'Modern, responsive websites and stores built for speed, trust, and conversion.',
    ),
    array(
        'title' => 'Brand and Growth Strategy',
        'text'  => 'Data-backed digital strategy to scale your business and dominate your target market.',
    ),
);
?>
<section class="hero reveal">
    <div class="container hero-grid">
        <div>
            <p class="kicker">DIGITAL MARKETING + DEVELOPMENT</p>
            <h1>Grow Your Business Online with Website Flexi</h1>
            <p>
                We help businesses win online through social media, digital advertising, web development,
                and smart growth systems tailored to your goals.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="#contact">Get Started</a>
                <a class="btn btn-secondary" href="#services">Learn More</a>
            </div>
        </div>
        <div class="stats-card">
            <h2>Worldwide Experience</h2>
            <ul>
                <li><strong>436</strong><span>Websites</span></li>
                <li><strong>97%</strong><span>Positive Feedback</span></li>
                <li><strong>80+</strong><span>Users</span></li>
                <li><strong>72+</strong><span>Contributors</span></li>
            </ul>
        </div>
    </div>
</section>

<section class="offers reveal" id="offers">
    <div class="container">
        <h2>Flexible Payment Options</h2>
        <div class="card-grid">
            <?php foreach ($offers as $offer) : ?>
                <article class="card">
                    <h3><?php echo esc_html($offer['title']); ?></h3>
                    <p><?php echo esc_html($offer['text']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="services reveal" id="services">
    <div class="container">
        <h2>What We Do</h2>
        <p class="section-intro">
            Full-service digital solutions for brands that want real growth, measurable results, and long-term impact.
        </p>
        <div class="card-grid">
            <?php foreach ($services as $service) : ?>
                <article class="card">
                    <h3><?php echo esc_html($service['title']); ?></h3>
                    <p><?php echo esc_html($service['text']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="about reveal">
    <div class="container about-box">
        <h2>Achieve Your Goals with Flexi</h2>
        <p>
            More views, more leads, and more sales. Our team blends creativity, technical depth, and marketing strategy
            to build digital experiences that move your business forward.
        </p>
    </div>
</section>

<section class="contact reveal" id="contact">
    <div class="container contact-cta">
        <h2>Ready to Scale Your Business?</h2>
        <p>Tell us your goal and we will craft the right plan for social media, ads, website, and growth.</p>
        <a class="btn btn-primary" href="<?php echo esc_url(home_url('/contact')); ?>">Book a Consultation</a>
    </div>
</section>

<?php get_footer(); ?>
