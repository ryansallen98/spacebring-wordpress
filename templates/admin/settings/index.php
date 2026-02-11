<?php

use RSA\Spacebring\Admin\Settings;

if (!defined('ABSPATH')) {
    exit;
}

$settings = new Settings();
$tabs = $settings->get_tabs();

$active_tab = sanitize_key($_GET['tab'] ?? array_key_first($tabs));
?>

<div class="wrap rsa-spacebring-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors(); ?>

    <h2 class="nav-tab-wrapper">
        <?php foreach ($tabs as $key => $tab): ?>
            <a href="<?php echo esc_url(
                admin_url('admin.php?page=spacebring&tab=' . $key)
            ); ?>" class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab['label']); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <form method="post" action="options.php">
        <?php
        settings_fields($tabs[$active_tab]['group']);
        do_settings_sections($tabs[$active_tab]['group']);
        submit_button();
        ?>
    </form>
</div>