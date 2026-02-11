<?php

namespace RSA\Spacebring\Sync;

use WP_Error;

abstract class AbstractSync
{
    /**
     * CPT slug
     */
    abstract protected function post_type(): string;

    /**
     * API → meta mapping
     */
    abstract protected function meta_map(): array;

    /**
     * Title field from API
     */
    protected function title(array $item): string
    {
        return $item['title'] ?? 'Untitled';
    }

    /**
     * Content field from API
     */
    protected function content(array $item): string
    {
        if (empty($item['description'])) {
            return '';
        }

        return $this->normalize_content($item['description']);
    }

    /**
     * Image URL from API
     */
    protected function image_url(array $item): ?string
    {
        return $item['imageUrl'] ?? null;
    }

    /**
     * External ID key in API payload
     */
    protected function external_id(array $item): ?string
    {
        return $item['id'] ?? null;
    }

    public function sync(array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            $results[] = $this->upsert($item);
        }

        return $results;
    }

    protected function upsert(array $item): array
    {
        $external_id = $this->external_id($item);

        if (!$external_id) {
            return [
                'success' => false,
                'external_id' => null,
                'post_id' => null,
                'action' => 'skipped',
                'error' => 'Missing external ID',
            ];
        }

        try {
            $post_id = $this->find_existing($external_id);

            $post_data = [
                'post_type' => $this->post_type(),
                'post_status' => 'publish',
                'post_title' => $this->title($item),
                'post_content' => $this->content($item),
            ];

            if ($post_id) {
                $post_data['ID'] = $post_id;
                wp_update_post($post_data);
                $action = 'updated';
            } else {
                $post_id = wp_insert_post($post_data);

                if (is_wp_error($post_id)) {
                    throw new \RuntimeException($post_id->get_error_message());
                }

                update_post_meta($post_id, 'external_id', $external_id);
                $action = 'created';
            }

            $this->sync_meta($post_id, $item);

            $image_result = $this->sync_featured_image($post_id, $item);

            return [
                'success' => true,
                'external_id' => $external_id,
                'post_id' => $post_id,
                'action' => $action,
                'warnings' => $image_result['success'] ? [] : [
                    [
                        'type' => 'image',
                        'message' => $image_result['error'],
                    ]
                ],
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'external_id' => $external_id,
                'post_id' => null,
                'action' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function find_existing(string $external_id): ?int
    {
        $posts = get_posts([
            'post_type' => $this->post_type(),
            'meta_key' => 'external_id',
            'meta_value' => $external_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        return $posts[0] ?? null;
    }

    protected function sync_meta(int $post_id, array $item): void
    {
        foreach ($this->meta_map() as $api_key => $meta_key) {
            if (array_key_exists($api_key, $item)) {
                update_post_meta($post_id, $meta_key, $item[$api_key]);
            }
        }
    }

    protected function sync_featured_image(int $post_id, array $item): array
    {
        $url = $this->image_url($item);

        if (empty($url)) {
            return [
                'success' => true,
                'skipped' => true,
                'error' => null,
            ];
        }

        $meta_key = '_spacebring_image_url';
        $existing_url = get_post_meta($post_id, $meta_key, true);

        // Fast path: URL unchanged and thumbnail exists
        if ($existing_url === $url && has_post_thumbnail($post_id)) {
            return [
                'success' => true,
                'skipped' => true,
                'error' => null,
            ];
        }

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $old_attachment_id = has_post_thumbnail($post_id)
            ? get_post_thumbnail_id($post_id)
            : null;

        $tmp = download_url($url);

        if (is_wp_error($tmp)) {
            return [
                'success' => false,
                'skipped' => false,
                'error' => $tmp->get_error_message(),
            ];
        }

        /* ---------------------------------------------------------
         * Detect real MIME from file bytes (safe + fast)
         * --------------------------------------------------------- */
        $mime = wp_get_image_mime($tmp);

        $ext_map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!$mime || !isset($ext_map[$mime])) {
            @unlink($tmp);
            return [
                'success' => false,
                'skipped' => false,
                'error' => 'Unsupported image MIME type',
            ];
        }

        /* ---------------------------------------------------------
         * Hash-based short-circuit (skip identical images)
         * --------------------------------------------------------- */
        $new_hash = hash_file('sha256', $tmp);

        if ($old_attachment_id) {
            $old_hash = get_post_meta($old_attachment_id, '_spacebring_image_hash', true);

            if ($old_hash && hash_equals($old_hash, $new_hash)) {
                @unlink($tmp);

                // URL may have changed but content didn't
                update_post_meta($post_id, $meta_key, $url);

                return [
                    'success' => true,
                    'skipped' => true,
                    'error' => null,
                ];
            }
        }

        $file = [
            'name' => $this->title($item) . '.' . $ext_map[$mime],
            'type' => $mime,
            'tmp_name' => $tmp,
            'error' => 0,
            'size' => filesize($tmp),
        ];

        /* ---------------------------------------------------------
         * BIG speed win: disable image size generation
         * --------------------------------------------------------- */
        add_filter('intermediate_image_sizes_advanced', '__return_empty_array');

        $attachment_id = media_handle_sideload($file, 0);

        remove_filter('intermediate_image_sizes_advanced', '__return_empty_array');

        @unlink($tmp);

        if (is_wp_error($attachment_id)) {
            return [
                'success' => false,
                'skipped' => false,
                'error' => $attachment_id->get_error_message(),
            ];
        }

        /* ---------------------------------------------------------
         * Commit new attachment
         * --------------------------------------------------------- */
        set_post_thumbnail($post_id, $attachment_id);
        update_post_meta($post_id, $meta_key, $url);
        update_post_meta($attachment_id, '_spacebring_image_hash', $new_hash);

        // Alt text
        if ($alt = $this->title($item)) {
            update_post_meta(
                $attachment_id,
                '_wp_attachment_image_alt',
                wp_strip_all_tags($alt)
            );
        }

        // Delete old attachment AFTER success
        if ($old_attachment_id && $old_attachment_id !== $attachment_id) {
            wp_delete_attachment($old_attachment_id, true);
        }

        return [
            'success' => true,
            'skipped' => false,
            'error' => null,
        ];
    }

    public static function summarize(array $results): array
    {
        $warnings = [];

        foreach ($results as $result) {
            if (!empty($result['warnings'])) {
                foreach ($result['warnings'] as $warning) {
                    $warnings[] = array_merge(
                        ['post_id' => $result['post_id'] ?? null],
                        $warning
                    );
                }
            }
        }

        return [
            'total' => count($results),
            'success' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success'])),
            'warnings' => count($warnings),
            'issues' => $warnings,
            'errors' => array_values(array_filter($results, fn($r) => !$r['success'])),
        ];
    }

    protected function normalize_content(string $content): string
    {
        // Convert Markdown bold (**text**) → <strong>
        $content = preg_replace(
            '/\*\*(.*?)\*\*/s',
            '<strong>$1</strong>',
            $content
        );

        // Convert Markdown italic (*text*) → <em>
        // (avoid double ** already handled)
        $content = preg_replace(
            '/(?<!\*)\*(?!\*)(.*?)\*(?<!\*)/s',
            '<em>$1</em>',
            $content
        );

        // Convert Markdown links → HTML links
        $content = preg_replace(
            '/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/',
            '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>',
            $content
        );

        // Convert line breaks → paragraphs
        $content = wpautop($content);

        // Sanitize
        return wp_kses_post($content);
    }
}