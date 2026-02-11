<?php

namespace RSA\Spacebring\Helpers;

class Vite
{
    public const MANIFEST_DIR = 'assets/build/.vite/manifest.json';

    protected static function manifest(): array
    {
        static $manifest = null;

        if ($manifest !== null) {
            return $manifest;
        }

        $pluginRoot = dirname(__DIR__, 2);
        $manifestPath = $pluginRoot . '/' . self::MANIFEST_DIR;

        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        return is_array($manifest) ? $manifest : [];
    }

    protected static function assetUrl(string $path): string
    {
        return plugins_url('assets/build/' . ltrim($path, '/'), dirname(__DIR__, 2) . '/spacebring.php');
    }

    public static function enqueue(string $entry, array $args = []): void
    {
        $manifest = self::manifest();

        $entryKey = array_key_first(
            array_filter(
                $manifest,
                fn($item) => ($item['name'] ?? null) === $entry
            )
        );

        if (!$entryKey || !isset($manifest[$entryKey])) {
            return;
        }

        $data = $manifest[$entryKey];

        $handle = $args['handle'] ?? "spacebring-{$entry}";
        $deps = $args['deps'] ?? [];
        $inFooter = $args['in_footer'] ?? true;

        // JS
        if (!empty($data['file']) && str_ends_with($data['file'], '.js')) {
            wp_enqueue_script(
                $handle,
                self::assetUrl($data['file']),
                $deps,
                null,
                $inFooter
            );
        }

        // CSS
        if (!empty($data['file']) && str_ends_with($data['file'], '.css')) {
            wp_enqueue_style(
                $handle,
                self::assetUrl($data['file']),
                [],
                null
            );
        }

        if (!empty($data['css'])) {
            foreach ($data['css'] as $i => $css) {
                wp_enqueue_style(
                    "{$handle}-{$i}",
                    self::assetUrl($css),
                    [],
                    null
                );
            }
        }
    }
}