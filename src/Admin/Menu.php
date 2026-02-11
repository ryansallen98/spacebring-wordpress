<?php

namespace RSA\Spacebring\Admin;

use RSA\Spacebring\Helpers\Template;

class Menu
{

    public function register()
    {
        add_action('admin_menu', [$this, 'add_menu']);
    }

    public function get_menu_items(): array
    {
        $menus = [
            [
                'slug' => 'spacebring',
                'title' => 'Settings',
                'template' => 'settings',
            ],
        ];

        /**
         * Allow filtering menu items
         */
        return apply_filters('spacebring_admin_menu_items', $menus);
    }

    public function add_menu()
    {
        $icon_path = SPACEBRING_PATH . 'assets/icons/spacebring_logo.svg';

        $icon_svg = 'data:image/svg+xml;base64,' . base64_encode(
            file_get_contents($icon_path)
        );

        $menus = $this->get_menu_items();

        // First item is always the parent
        $parent = $menus[0];

        add_menu_page(
            'Spacebring',
            'Spacebring',
            'manage_options',
            $parent['slug'],
            [$this, 'render_admin_page'],
            $icon_svg,
            25
        );

        foreach ($menus as $menu) {
            add_submenu_page(
                $parent['slug'],
                $menu['title'],
                $menu['title'],
                'manage_options',
                $menu['slug'],
                [$this, 'render_admin_page']
            );
        }
    }

    public function render_admin_page()
    {
        $page = $_GET['page'] ?? 'spacebring';

        $menus = $this->get_menu_items();

        $templates = array_column($menus, 'template', 'slug');

        $template = $templates[$page] ?? 'settings';

        Template::admin($template);
    }
}