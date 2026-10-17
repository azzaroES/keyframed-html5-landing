<?php
/**
 * LiteSpeed Cache (LSCache) & Super PWA Synergy Engine
 *
 * Guarantees zero-bloat caching, automated cache purges on token changes,
 * WebP immutable browser caching, and instant Progressive Web App (PWA) readiness.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_Performance_Synergy {

    public static function init() {
        // LSCache header optimizations
        add_action('send_headers', [__CLASS__, 'set_performance_headers']);
        
        // Automated cache invalidation when content/design changes
        add_action('save_post', [__CLASS__, 'purge_cache_on_update']);
        add_action('update_option_kebab_nap_settings', [__CLASS__, 'purge_all_caches']);
        add_action('update_option_kebab_shortlinks', [__CLASS__, 'purge_all_caches']);

        // PWA Meta Tags & Manifest in wp_head
        add_action('wp_head', [__CLASS__, 'render_pwa_head_tags'], 2);

        // Native PWA Manifest Endpoint (fallback if Super PWA plugin is not installed)
        add_action('init', [__CLASS__, 'handle_manifest_endpoint']);
    }

    /**
     * Set high-performance browser caching headers
     */
    public static function set_performance_headers() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';

        // If requesting keyframes or fonts, instruct browser to cache for 1 year immutable
        if (preg_match('#\.(webp|woff2|woff|svg|png|jpg)$#i', $uri)) {
            header('Cache-Control: public, max-age=31536000, immutable');
            header('X-Kebab-Cache: Static-Asset-Immutable');
        } else {
            // Advise LSCache to tag pages for easy selective invalidation
            if (function_exists('litespeed_tag_add')) {
                litespeed_tag_add('kebab_fse_page');
            }
        }
    }

    /**
     * Purge LiteSpeed Cache on content updates
     */
    public static function purge_cache_on_update($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        self::purge_all_caches();
    }

    /**
     * Complete cache invalidation across LiteSpeed and standard caches
     */
    public static function purge_all_caches() {
        // LiteSpeed Cache Plugin
        if (class_exists('LiteSpeed_Cache_API')) {
            LiteSpeed_Cache_API::purge_all();
        } elseif (has_action('litespeed_purge_all')) {
            do_action('litespeed_purge_all');
        }

        // WP Super Cache
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix;
            wp_cache_clean_cache($file_prefix, true);
        }

        // W3 Total Cache
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
    }

    /**
     * Render PWA metadata tags in <head>
     */
    public static function render_pwa_head_tags() {
        $theme_color = '#1A1A1A';
        $manifest_url = home_url('/kebab-manifest.json');

        echo "\n<!-- Super PWA & Mobile Web App Meta -->\n";
        echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
        echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        echo '<link rel="manifest" href="' . esc_url($manifest_url) . '">' . "\n";
    }

    /**
     * Handle native /kebab-manifest.json endpoint
     */
    public static function handle_manifest_endpoint() {
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'kebab-manifest.json') !== false) {
            $nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];
            $name = !empty($nap['name']) ? $nap['name'] : get_bloginfo('name');
            $slogan = !empty($nap['slogan']) ? $nap['slogan'] : get_bloginfo('description');

            $manifest = [
                'name'             => $name,
                'short_name'       => substr($name, 0, 12),
                'description'      => $slogan,
                'start_url'        => home_url('/'),
                'display'          => 'standalone',
                'background_color' => '#1A1A1A',
                'theme_color'      => '#1A1A1A',
                'orientation'      => 'portrait-primary',
                'icons'            => [
                    [
                        'src'     => get_template_directory_uri() . '/assets/pwa-icon-192.png',
                        'sizes'   => '192x192',
                        'type'    => 'image/png',
                        'purpose' => 'any maskable',
                    ],
                    [
                        'src'     => get_template_directory_uri() . '/assets/pwa-icon-512.png',
                        'sizes'   => '512x512',
                        'type'    => 'image/png',
                        'purpose' => 'any maskable',
                    ],
                ],
            ];

            header('Content-Type: application/manifest+json; charset=utf-8');
            header('Cache-Control: public, max-age=86400');
            echo wp_json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        }
    }
}

// Initialize performance and PWA engine
Kebab_Performance_Synergy::init();
