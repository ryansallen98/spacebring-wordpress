<?php

use RSA\Spacebring\Helpers\Markdown;
/**
 * Spacebring Events Shortcode Template
 *
 * This template can be overridden by copying it to:
 * your-theme/spacebring/shortcodes/events.php
 *
 * @version 1.0.0
 *
 * Available variables:
 * - $events (array)
 * - $template_version (string)
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="rsa-spacebring-wrap" data-template-version="<?php echo esc_attr($template_version); ?>">

    <?php if (empty($events)): ?>

        <p class="spacebring-events-empty">
            <?php esc_html_e('No upcoming events.', 'spacebring'); ?>
        </p>

    <?php else: ?>

        <div class="flex gap-2 mb-6 bg-gray-100 rounded-md p-2 w-fit text-sm" role="tablist">
            <button type="button" data-view="list" data-active="true"
                class="spacebring-tab data-[active=true]:bg-gray-200 px-4 py-2 rounded hover:bg-gray-200/50 focus-visible:bg-gray-200/50 cursor-pointer">
                List View
            </button>
            <button type="button" data-view="calendar" data-active="false"
                class="spacebring-tab data-[active=true]:bg-gray-200 px-4 py-2 rounded hover:bg-gray-200/50 focus-visible:bg-gray-200/50 cursor-pointer">
                Calendar View
            </button>
        </div>

        <div class="spacebring-view spacebring-view-list">
            <ul class="flex flex-col list-none p-0 m-0 gap-8 lg:gap-16">
                <?php foreach ($events as $event): ?>
                    <li class="m-0 p-0 border-b border-dashed border-gray-300 pb-8 lg:pb-16 last:border-0">
                        <a href="<?php echo esc_url($domain . $event['locationRef'] . "/events/" . $event['id']); ?>"
                            target="_blank">
                            <?php if (!empty($event['imageUrl'])): ?>
                                <img src="<?php echo esc_url($event['imageUrl']); ?>"
                                    alt="<?php echo esc_attr($event['title'] ?? ''); ?>" loading="lazy"
                                    class="rounded overflow-hidden" />
                            <?php endif; ?>

                            <h2>
                                <?php echo esc_html($event['title'] ?? 'Untitled Event'); ?>
                            </h2>

                            <?php if (!empty($event['startDate'])): ?>
                                <small>
                                    <?php echo esc_html(date_i18n(
                                        get_option('date_format'),
                                        strtotime($event['startDate'])
                                    )); ?>
                                </small>
                            <?php endif; ?>

                            <?php if (!empty($event['description'])): ?>
                                <?php echo Markdown::firstParagraph($event['description']) ?>
                            <?php endif; ?>
                        </a>

                        <a href="<?php echo esc_url($domain . $event['locationRef'] . "/events/" . $event['id']); ?>"
                            target="_blank" class="button inline-block">
                            View Event
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="spacebring-view spacebring-view-calendar hidden">
            <div class="p-6 border rounded bg-gray-50">
                <!-- Calendar will render here -->
                <div id="spacebring-calendar"></div>
            </div>
        </div>
    <?php endif; ?>

</div>
