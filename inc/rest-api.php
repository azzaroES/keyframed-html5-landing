<?php
/**
 * REST API Endpoints for Novamira MCP & Headless Agents
 *
 * Provides dedicated endpoints for programmatic inspection and modification of:
 * 1. Multi-Niche NAP & Business ID Profile
 * 2. Design Tokens (theme.json & DESIGN.md synchronization)
 * 3. Shortlinker management & click analytics
 * 4. SVG QR generation
 * 5. Dynamic live opening hours status
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_REST_API {

    const NAMESPACE = 'kebab/v1';

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        // NAP endpoints
        register_rest_route(self::NAMESPACE, '/nap', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [__CLASS__, 'get_nap'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [__CLASS__, 'update_nap'],
                'permission_callback' => [__CLASS__, 'check_manage_permission'],
            ],
        ]);

        // Tokens endpoints (theme.json + DESIGN.md)
        register_rest_route(self::NAMESPACE, '/tokens', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [__CLASS__, 'get_tokens'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [__CLASS__, 'update_tokens'],
                'permission_callback' => [__CLASS__, 'check_manage_permission'],
            ],
        ]);

        // QR Code generation
        register_rest_route(self::NAMESPACE, '/qr', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_qr_svg'],
            'permission_callback' => '__return_true',
            'args'                => [
                'url'   => ['type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
                'size'  => ['type' => 'integer', 'default' => 240],
                'color' => ['type' => 'string', 'default' => '#C9A962'],
            ],
        ]);

        // Shortlinks management
        register_rest_route(self::NAMESPACE, '/shortlinks', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [__CLASS__, 'get_shortlinks'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [__CLASS__, 'create_shortlink'],
                'permission_callback' => [__CLASS__, 'check_manage_permission'],
            ],
        ]);

        // Live open/closed business status
        register_rest_route(self::NAMESPACE, '/status', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [__CLASS__, 'get_live_status'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function check_manage_permission() {
        return current_user_can('edit_theme_options');
    }

    public static function get_nap() {
        $settings = Kebab_NAP_Engine::get_settings();
        return rest_ensure_response([
            'success'  => true,
            'data'     => $settings,
            'schema'   => 'https://schema.org/' . ($settings['niche'] ?? 'LocalBusiness'),
        ]);
    }

    public static function update_nap($request) {
        $params = $request->get_json_params();
        if (empty($params) || !is_array($params)) {
            return new WP_Error('invalid_payload', 'Missing JSON configuration body', ['status' => 400]);
        }

        $updated = Kebab_NAP_Engine::update_settings($params);
        return rest_ensure_response([
            'success' => true,
            'message' => 'Business NAP profile updated successfully',
            'data'    => $updated,
        ]);
    }

    public static function get_tokens() {
        $theme_json_path = get_template_directory() . '/theme.json';
        $theme_json = [];
        if (file_exists($theme_json_path)) {
            $content = file_get_contents($theme_json_path);
            $theme_json = json_decode($content, true);
        }

        return rest_ensure_response([
            'success'    => true,
            'theme_json' => $theme_json['settings'] ?? [],
        ]);
    }

    public static function update_tokens($request) {
        $params = $request->get_json_params();
        if (empty($params)) {
            return new WP_Error('empty_payload', 'Tokens payload cannot be empty', ['status' => 400]);
        }

        $theme_json_path = get_template_directory() . '/theme.json';
        if (!file_exists($theme_json_path) || !is_writable($theme_json_path)) {
            return new WP_Error('file_not_writable', 'theme.json is not writable on disk', ['status' => 500]);
        }

        $existing = json_decode(file_get_contents($theme_json_path), true);
        if (isset($params['settings'])) {
            $existing['settings'] = array_replace_recursive($existing['settings'], $params['settings']);
        }

        $saved = file_put_contents($theme_json_path, wp_json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        if ($saved !== false) {
            Kebab_Performance_Synergy::purge_all_caches();
            return rest_ensure_response([
                'success' => true,
                'message' => 'theme.json updated successfully',
            ]);
        }

        return new WP_Error('write_failed', 'Could not save theme.json', ['status' => 500]);
    }

    public static function get_qr_svg($request) {
        $url = $request->get_param('url') ?: home_url('/');
        $size = (int)($request->get_param('size') ?: 240);
        $color = sanitize_hex_color($request->get_param('color')) ?: '#C9A962';

        $svg = Kebab_QR_Generator::generate_svg($url, $size, $color);

        $response = new WP_REST_Response(['svg' => $svg, 'target' => $url]);
        $response->set_headers(['Content-Type' => 'application/json; charset=utf-8']);
        return $response;
    }

    public static function get_shortlinks() {
        $links = Kebab_Shortlinker::get_all_links();
        $stats = get_option(Kebab_Shortlinker::OPTION_STATS, []);
        
        return rest_ensure_response([
            'success' => true,
            'links'   => $links,
            'stats'   => $stats,
        ]);
    }

    public static function create_shortlink($request) {
        $params = $request->get_json_params();
        $slug = sanitize_title($params['slug'] ?? '');
        $target = esc_url_raw($params['target'] ?? '');
        $title = sanitize_text_field($params['title'] ?? '');

        if (empty($slug) || empty($target)) {
            return new WP_Error('invalid_args', 'Slug and target URL are required', ['status' => 400]);
        }

        Kebab_Shortlinker::set_link($slug, $target, $title);

        return rest_ensure_response([
            'success'   => true,
            'slug'      => $slug,
            'short_url' => home_url('/go/' . $slug),
            'target'    => $target,
        ]);
    }

    public static function get_live_status() {
        $nap = Kebab_NAP_Engine::get_settings();
        return rest_ensure_response([
            'success'        => true,
            'business_id'    => $nap['business_id'],
            'opening_hours'  => $nap['opening_hours'],
            'current_time'   => current_time('mysql'),
        ]);
    }
}

// Initialize REST API
Kebab_REST_API::init();
