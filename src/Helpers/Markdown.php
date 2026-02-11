<?php

namespace RSA\Spacebring\Helpers;

use League\CommonMark\CommonMarkConverter;

class Markdown
{
    protected static ?CommonMarkConverter $converter = null;

    protected static function converter(): CommonMarkConverter
    {
        if (self::$converter === null) {
            self::$converter = new CommonMarkConverter([
                'html_input' => 'strip',        // Strip raw HTML from markdown
                'allow_unsafe_links' => false, // Prevent javascript: links, etc.
            ]);
        }

        return self::$converter;
    }

    /**
     * Convert Markdown to HTML
     */
    public static function toHtml(string $markdown): string
    {
        return self::converter()->convert($markdown)->getContent();
    }

    /**
     * Convert Markdown to safe HTML for WordPress output
     */
    public static function toSafeHtml(string $markdown): string
    {
        $html = self::toHtml($markdown);

        // WordPress sanitization (safe for post content)
        return wp_kses_post($html);
    }


    public static function firstParagraph(string $markdown, string $truncate = '…'): string
    {
        $html = self::toSafeHtml($markdown);

        // Load HTML into DOM
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_clear_errors();

        $paragraphs = $dom->getElementsByTagName('p');

        if ($paragraphs->length === 0) {
            return '';
        }

        $first = $paragraphs->item(0)->textContent;
        $hasMore = $paragraphs->length > 1;

        // Escape text for safe output
        $first = esc_html($first);

        return '<p>' . $first . ($hasMore ? $truncate : '') . '</p>';
    }
}