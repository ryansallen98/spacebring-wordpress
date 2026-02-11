<?php

namespace RSA\Spacebring\PostTypes;

abstract class AbstractPostType
{
    abstract public static function post_type(): string;
    abstract protected static function labels(): array;

    // Overridable defaults
    protected static function supports(): array
    {
        return ['title', 'editor', 'thumbnail'];
    }

    protected static function meta_fields(): array
    {
        return [];
    }

    protected static function menu_parent(): ?string
    {
        return null;
    }

    protected static function api_controlled(): bool
    {
        return false;
    }

    protected static function classic_editor_only(): bool
    {
        return false;
    }

    protected static function disable_add_new(): bool
    {
        return false;
    }

    protected static function revisions(): bool
    {
        return true;
    }

    protected static function public_visibility(): bool
    {
        return true;
    }

    /* ---------------------------------------------
     * Bootstrap
     * ------------------------------------------- */

    public static function register(): void
    {
        add_action('init', [static::class, 'register_post_type']);
        add_action('init', [static::class, 'register_meta_fields']);

        if (static::classic_editor_only()) {
            add_filter('use_block_editor_for_post_type', [static::class, 'disable_gutenberg'], 10, 2);
        }

        if (!empty(static::meta_fields())) {
            add_action('add_meta_boxes', [static::class, 'add_meta_boxes']);
            add_action('save_post_' . static::post_type(), [static::class, 'save_meta']);
        }

        if (static::api_controlled()) {
            add_action('admin_enqueue_scripts', [static::class, 'lock_editor_fields']);
        }

        if (static::disable_add_new()) {
            add_action('admin_menu', [static::class, 'remove_add_new']);
            add_action('admin_enqueue_scripts', [static::class, 'hide_add_new_button']);
            add_filter('post_row_actions', [static::class, 'remove_row_actions'], 10, 2);
        }
    }

    /* ---------------------------------------------
     * CPT Registration
     * ------------------------------------------- */

    public static function register_post_type(): void
    {
        $args = [
            'labels' => static::labels(),
            'show_ui' => true,
            'supports' => static::supports(),
            'show_in_rest' => true,
            'revisions' => static::revisions(),
        ];

        if (static::public_visibility()) {
            $args['public'] = true;
            $args['publicly_queryable'] = true;
            $args['has_archive'] = true;
            $args['rewrite'] = [
                'slug' => static::post_type(),
                'with_front' => false,
            ];
        } else {
            $args['public'] = false;
            $args['publicly_queryable'] = false;
            $args['rewrite'] = false;
        }

        if ($parent = static::menu_parent()) {
            $args['show_in_menu'] = $parent;
        }

        if (static::disable_add_new()) {
            $args['capabilities'] = [
                'create_posts' => 'do_not_allow',
            ];
            $args['map_meta_cap'] = true;
        }

        register_post_type(static::post_type(), $args);
    }

    /* ---------------------------------------------
     * Gutenberg Control
     * ------------------------------------------- */

    public static function disable_gutenberg(bool $use, string $post_type): bool
    {
        return $post_type === static::post_type() ? false : $use;
    }

    /* ---------------------------------------------
     * Meta Registration
     * ------------------------------------------- */

    public static function register_meta_fields(): void
    {
        foreach (static::meta_fields() as $key => $config) {
            register_post_meta(static::post_type(), $key, array_merge([
                'type' => 'string',
                'single' => true,
                'show_in_rest' => true,
            ], $config));
        }
    }

    /* ---------------------------------------------
     * Meta Box
     * ------------------------------------------- */

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            static::post_type() . '_meta',
            'Details',
            [static::class, 'render_meta_box'],
            static::post_type(),
            'normal'
        );
    }

    public static function render_meta_box($post): void
    {
        echo '<table class="form-table">';

        foreach (static::meta_fields() as $key => $config) {
            $label = $config['label'] ?? ucfirst(str_replace('_', ' ', $key));
            $value = get_post_meta($post->ID, $key, true);
            $readonly = static::api_controlled() ? 'readonly' : '';

            echo '<tr>';
            echo '<th>' . esc_html($label) . '</th>';
            echo '<td>';

            if (is_array($value) || is_object($value)) {
                echo '<textarea class="large-text code" rows="8" ' . $readonly . '>';
                echo esc_textarea(
                    wp_json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
                echo '</textarea>';
            } else {
                echo '<input type="text" class="regular-text" value="' . esc_attr($value) . '" ' . $readonly . '>';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    /* ---------------------------------------------
     * Save Meta
     * ------------------------------------------- */

    public static function save_meta(int $post_id): void
    {
        if (static::api_controlled()) {
            return;
        }

        foreach (array_keys(static::meta_fields()) as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }

    /* ---------------------------------------------
     * Lock Title & Content
     * ------------------------------------------- */

    public static function lock_editor_fields(): void
    {
        $screen = get_current_screen();

        if (!$screen || $screen->post_type !== static::post_type()) {
            return;
        }
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                ['title', 'content'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.setAttribute('readonly', 'readonly');
                });
            });
        </script>
        <?php
    }

    /* ---------------------------------------------
     * Remove Add New
     * ------------------------------------------- */

    public static function remove_add_new(): void
    {
        remove_submenu_page('edit.php?post_type=' . static::post_type(), 'post-new.php?post_type=' . static::post_type());
    }

    public static function hide_add_new_button(): void
    {
        $screen = get_current_screen();

        if ($screen && $screen->post_type === static::post_type()) {
            echo '<style>.page-title-action{display:none!important;}</style>';
        }
    }

    public static function remove_row_actions(array $actions, $post): array
    {
        if ($post->post_type === static::post_type()) {
            unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }
}