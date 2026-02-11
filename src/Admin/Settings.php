<?php
namespace RSA\Spacebring\Admin;

use RSA\Spacebring\Helpers\Credentials;
use RSA\Spacebring\Helpers\Template;

class Settings
{
    public function register()
    {
        add_action('admin_init', [$this, 'register_settings']);

        add_action(
            'update_option_spacebring_sync_options',
            [self::class, 'maybe_flush_rewrites'],
            10,
            2
        );
    }

    public function get_tabs(): array
    {
        $tabs = [
            'credentials' => [
                'position' => 2,
                'label' => __('Credentials', 'spacebring'),
                'group' => 'spacebring_api_settings',
                'option' => 'spacebring_api_options',
                'sections' => [
                    'credentials' => [
                        'title' => __('Credentials', 'spacebring'),
                        'show_save_button' => true,
                        'fields' => [
                            'api_username' => [
                                'label' => __('Authorization Username', 'spacebring'),
                                'type' => 'text',
                            ],
                            'api_password' => [
                                'label' => __('Authorization Password', 'spacebring'),
                                'type' => 'password',
                            ],
                        ],
                    ],
                    'test' => [
                        'callback' => function () {
                            if (Credentials::exists())
                                Template::admin('settings/test-api-connection');
                        }
                    ],
                ],
            ],
        ];

        if (Credentials::exists()) {
            $tabs['synchronization'] = [
                'position' => 1,
                'label' => __('Synchronization', 'spacebring'),
                'group' => 'spacebring_sync_settings',
                'option' => 'spacebring_sync_options',
                'sections' => [
                    'synchronization' => [
                        'callback' => function () {
                            if (Credentials::exists())
                                Template::admin('settings/synchronization');
                        }
                    ],
                    'synchronization_config' => [
                        'title' => __('Configuration', 'spacebring'),
                        'fields' => [
                            'sync_locations' => [
                                'label' => __('Locations', 'spacebring'),
                                'type' => 'checkbox',
                                'checked' => true,
                                'disabled' => true,
                            ],
                            'single_location' => [
                                'label' => __('Single Location', 'spacebring'),
                                'type' => 'checkbox',
                            ],
                            'sync_resources' => [
                                'label' => __('Resources', 'spacebring'),
                                'type' => 'checkbox_group',
                                'choices' => [
                                    'hot_desks' => __('Hot Desks', 'spacebring'),
                                    'dedicated_desks' => __('Dedicated Desks', 'spacebring'),
                                    'offices' => __('Offices', 'spacebring'),
                                    'parking_lots' => __('Parking Lots', 'spacebring'),
                                    'rooms' => __('Rooms', 'spacebring'),
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        // Sort tabs by position (lower number = earlier)
        uasort($tabs, function ($a, $b) {
            $aPos = $a['position'] ?? 10;
            $bPos = $b['position'] ?? 10;

            return $aPos <=> $bPos;
        });

        return apply_filters('spacebring_settings_tabs', $tabs);
    }

    public function register_settings()
    {
        foreach ($this->get_tabs() as $tab_key => $tab) {

            register_setting(
                $tab['group'],
                $tab['option']
            );

            foreach ($tab['sections'] as $section_key => $section) {

                add_settings_section(
                    "spacebring_{$tab_key}_{$section_key}",
                    $section['title'] ?? '',
                    function () use ($section, $tab) {
                        if (!empty($section['callback']) && is_callable($section['callback'])) {
                            call_user_func($section['callback'], $tab);
                        }
                    },
                    $tab['group']
                );

                foreach (($section['fields'] ?? []) as $field_key => $field) {

                    add_settings_field(
                        "spacebring_{$tab_key}_{$field_key}",
                        $field['label'],
                        function () use ($tab, $field_key, $field) {
                            $options = get_option($tab['option']);
                            $value = $options[$field_key] ?? '';

                            if ($field['type'] === 'checkbox_group' && !empty($field['choices'])) {
                                foreach ($field['choices'] as $choice_key => $choice_label) {
                                    $checked = isset($value[$choice_key]) ? 'checked' : '';

                                    printf('<label style="display:block; margin-bottom:4px;">
                                                        <input type="checkbox" name="%s[%s][%s]" value="1" %s>
                                                        %s
                                                    </label>',
                                        esc_attr($tab['option']),
                                        esc_attr($field_key),
                                        esc_attr($choice_key),
                                        $checked,
                                        esc_html($choice_label)
                                    );
                                }
                                return;
                            } elseif ($field['type'] === 'checkbox') {
                                $name = sprintf('%s[%s]', $tab['option'], $field_key);

                                $checked = !empty($value) ? 'checked' : '';
                                $disabled = !empty($field['disabled']) ? 'disabled' : '';

                                if (!empty($field['disabled']) && !empty($field['checked'])) {
                                    printf(
                                        '<input type="hidden" name="%s" value="1">',
                                        esc_attr($name)
                                    );
                                }

                                printf(
                                    '<label style="display:block; opacity:%s;">
                                                <input type="checkbox" name="%s" value="1" %s %s>
                                                %s
                                            </label>',
                                    $disabled ? '0.6' : '1',
                                    esc_attr($name),
                                    $checked,
                                    $disabled,
                                    esc_html($field['label'])
                                );
                            } else {
                                printf(
                                    '<input type="%s" name="%s[%s]" value="%s" class="regular-text">',
                                    esc_attr($field['type']),
                                    esc_attr($tab['option']),
                                    esc_attr($field_key),
                                    esc_attr($value)
                                );
                            }
                        },
                        $tab['group'],
                        "spacebring_{$tab_key}_{$section_key}"
                    );
                }
            }
        }
    }

    public function get_option(string $key, $default = null, string $tab = 'credentials')
    {
        $tabs = $this->get_tabs();

        if (!isset($tabs[$tab])) {
            return $default;
        }

        $option_name = $tabs[$tab]['option'];

        $options = get_option($option_name, []);

        return $options[$key] ?? $default;
    }

    public static function maybe_flush_rewrites($old_value, $new_value): void
    {
        if ($old_value === $new_value) {
            return;
        }

        /**
         * Only flush if CPT-relevant flags changed
         */
        $keys_that_affect_cpts = [
            'sync_locations',
            'sync_resources',
        ];

        foreach ($keys_that_affect_cpts as $key) {
            if (($old_value[$key] ?? null) !== ($new_value[$key] ?? null)) {
                flush_rewrite_rules();
                return;
            }
        }
    }
}