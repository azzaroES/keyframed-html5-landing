<?php
/**
 * Native Shortlinker Router (/go/{slug})
 *
 * Provides shortlink redirection directly on the site's domain.
 * Integrates click analytics and preset shortcuts for WhatsApp, Carta, Maps, Contact, and vCard.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_Shortlinker {

    const OPTION_LINKS = 'kebab_shortlinks';
    const OPTION_STATS = 'kebab_shortlink_stats';

    /**
     * Initialize rewrite rules and query vars
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_rewrites']);
        add_filter('query_vars', [__CLASS__, 'register_query_vars']);
        add_action('template_redirect', [__CLASS__, 'handle_redirect']);
    }

    /**
     * Add rewrite rule: /go/{slug}
     */
    public static function register_rewrites() {
        add_rewrite_rule('^go/([a-zA-Z0-9_-]+)/?$', 'index.php?kebab_go=$matches[1]', 'top');
    }

    /**
     * Register query var
     */
    public static function register_query_vars($vars) {
        $vars[] = 'kebab_go';
        return $vars;
    }

    /**
     * Get default predefined shortlinks dynamically mapped to NAP settings
     */
    public static function get_default_links() {
        $nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];

        $phone = !empty($nap['phone_clean']) ? $nap['phone_clean'] : '+34976620000';
        $wa = !empty($nap['whatsapp_clean']) ? $nap['whatsapp_clean'] : '34600000000';
        $lat = !empty($nap['geo_lat']) ? $nap['geo_lat'] : 41.3385;
        $lng = !empty($nap['geo_lng']) ? $nap['geo_lng'] : -1.2225;

        return [
            'pedir'   => [
                'target' => 'https://wa.me/' . $wa . '?text=Hola,%20quisiera%20hacer%20un%20pedido%20en%20Cari%C3%B1ena%20Kebab',
                'title'  => 'Pedir Online por WhatsApp',
            ],
            'carta'   => [
                'target' => home_url('/#carta'),
                'title'  => 'Ver Carta & Menú',
            ],
            'menu'    => [
                'target' => home_url('/#carta'),
                'title'  => 'Ver Carta & Menú',
            ],
            'wa'      => [
                'target' => 'https://wa.me/' . $wa,
                'title'  => 'WhatsApp Directo',
            ],
            'maps'    => [
                'target' => 'https://maps.google.com/?q=' . $lat . ',' . $lng,
                'title'  => 'Cómo llegar (Google Maps)',
            ],
            'gps'     => [
                'target' => 'https://maps.google.com/?q=' . $lat . ',' . $lng,
                'title'  => 'Navegación GPS',
            ],
            'contact' => [
                'target' => home_url('/contact/'),
                'title'  => 'Página de Contacto & NAP',
            ],
            'blog'    => [
                'target' => home_url('/blog/'),
                'title'  => 'Blog & Novedades',
            ],
            'vcard'   => [
                'target' => home_url('/?download_vcard=1'),
                'title'  => 'Descargar Tarjeta de Contacto (vCard)',
            ],
        ];
    }

    /**
     * Get all active shortlinks (custom + defaults)
     */
    public static function get_all_links() {
        $custom = get_option(self::OPTION_LINKS, []);
        $defaults = self::get_default_links();
        return array_merge($defaults, $custom);
    }

    /**
     * Add or update a shortlink
     */
    public static function set_link($slug, $target_url, $title = '') {
        $slug = sanitize_title($slug);
        $custom = get_option(self::OPTION_LINKS, []);
        $custom[$slug] = [
            'target' => esc_url_raw($target_url),
            'title'  => sanitize_text_field($title),
        ];
        update_option(self::OPTION_LINKS, $custom);
        return $slug;
    }

    /**
     * Handle redirect when /go/{slug} is hit
     */
    public static function handle_redirect() {
        $slug = get_query_var('kebab_go');

        // Fallback check for REQUEST_URI if rewrite rule not yet flushed
        if (empty($slug)) {
            $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
            if (preg_match('#^go/([a-zA-Z0-9_-]+)$#i', $uri, $matches)) {
                $slug = $matches[1];
            }
        }

        if (empty($slug)) {
            return;
        }

        $slug = sanitize_title($slug);
        $links = self::get_all_links();

        if (isset($links[$slug]) && !empty($links[$slug]['target'])) {
            // Track click statistics
            self::record_click($slug);

            $target = $links[$slug]['target'];
            wp_redirect($target, 302);
            exit;
        }

        // Unknown shortlink: redirect to home
        wp_safe_redirect(home_url('/'));
        exit;
    }

    /**
     * Record click for shortlink analytics
     */
    private static function record_click($slug) {
        $stats = get_option(self::OPTION_STATS, []);
        if (!isset($stats[$slug])) {
            $stats[$slug] = [
                'hits'      => 0,
                'last_hit'  => null,
            ];
        }
        $stats[$slug]['hits']++;
        $stats[$slug]['last_hit'] = current_time('mysql');
        update_option(self::OPTION_STATS, $stats, false);
    }
}

// Initialize shortlinker
Kebab_Shortlinker::init();
