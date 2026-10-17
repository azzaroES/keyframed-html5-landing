<?php
/**
 * Universal WP Code Snippets Bridge for Novamira MCP & Headless Agents
 *
 * Provides a unified adapter that interfaces with:
 * 1. Code Snippets Plugin (table: wp_snippets)
 * 2. WPCode Plugin (post_type: wpcode)
 * 3. Native File-Based MU-Snippets Fallback (wp-content/mu-plugins/kebab-snippets/ or inc/snippets/)
 *
 * Includes pre-flight PHP tokenizer syntax validation (WSOD Prevention),
 * kill-switch instant toggling, LiteSpeed Cache invalidation, and
 * registration into Novamira's WordPress Abilities API.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_Snippet_Bridge {

    const BACKEND_CODE_SNIPPETS = 'code_snippets';
    const BACKEND_WPCODE        = 'wpcode';
    const BACKEND_FILE          = 'file_fallback';

    /**
     * Initialize Bridge
     */
    public static function init() {
        // Register Novamira MCP Abilities if Abilities API is available
        add_action('wp_abilities_api_init', [__CLASS__, 'register_abilities']);

        // In fallback file-mode, auto-load active frontend snippets safely
        add_action('after_setup_theme', [__CLASS__, 'boot_file_snippets'], 100);
    }

    /**
     * Detect Active Snippet Backend
     *
     * @return string
     */
    public static function get_backend(): string {
        global $wpdb;

        // 1. Check for Code Snippets table
        $table_name = $wpdb->prefix . 'snippets';
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
        if ($table_exists === $table_name) {
            return self::BACKEND_CODE_SNIPPETS;
        }

        // 2. Check for WPCode post type
        if (post_type_exists('wpcode')) {
            return self::BACKEND_WPCODE;
        }

        // 3. Fallback to native file-based drop-ins
        return self::BACKEND_FILE;
    }

    /**
     * Get Directory for File-Based Fallback Snippets
     *
     * @return string
     */
    public static function get_fallback_dir(): string {
        $theme_dir = get_template_directory() . '/inc/snippets';
        if (!file_exists($theme_dir)) {
            @wp_mkdir_p($theme_dir);
        }
        return $theme_dir;
    }

    /**
     * Pre-flight Syntax Validation (WSOD Prevention)
     *
     * Uses PHP token_get_all with TOKEN_PARSE flag to catch syntax and parse
     * errors before code is saved or activated.
     *
     * @param string $code
     * @param string $type
     * @return true|WP_Error
     */
    public static function validate_syntax(string $code, string $type = 'php') {
        if ($type !== 'php') {
            return true;
        }

        $clean = trim($code);
        if ($clean === '') {
            return true;
        }

        // Ensure opening tag is present for tokenizer evaluation
        $eval_code = str_starts_with($clean, '<?php') ? $clean : "<?php\n" . $clean;

        try {
            // PHP 7.0+ throws ParseError on syntax error when TOKEN_PARSE is set
            token_get_all($eval_code, TOKEN_PARSE);
        } catch (\ParseError $e) {
            return new WP_Error(
                'syntax_error',
                sprintf('PHP Parse Error: %s on line %d', $e->getMessage(), $e->getLine()),
                ['status' => 422, 'line' => $e->getLine()]
            );
        } catch (\Throwable $e) {
            return new WP_Error(
                'syntax_validation_failed',
                'Syntax validation error: ' . $e->getMessage(),
                ['status' => 422]
            );
        }

        return true;
    }

    /**
     * List All Snippets
     *
     * @return array
     */
    public static function get_all(): array {
        $backend = self::get_backend();

        switch ($backend) {
            case self::BACKEND_CODE_SNIPPETS:
                return [
                    'backend'  => $backend,
                    'snippets' => self::get_code_snippets_list(),
                ];

            case self::BACKEND_WPCODE:
                return [
                    'backend'  => $backend,
                    'snippets' => self::get_wpcode_list(),
                ];

            case self::BACKEND_FILE:
            default:
                return [
                    'backend'  => $backend,
                    'snippets' => self::get_file_snippets_list(),
                ];
        }
    }

    /**
     * Get Single Snippet
     *
     * @param string|int $id
     * @return array|WP_Error
     */
    public static function get_one($id) {
        $backend = self::get_backend();

        switch ($backend) {
            case self::BACKEND_CODE_SNIPPETS:
                return self::get_code_snippet_one((int)$id);

            case self::BACKEND_WPCODE:
                return self::get_wpcode_one((int)$id);

            case self::BACKEND_FILE:
            default:
                return self::get_file_snippet_one((string)$id);
        }
    }

    /**
     * Create New Snippet
     *
     * @param array $data
     * @return array|WP_Error
     */
    public static function create(array $data) {
        $name   = sanitize_text_field($data['name'] ?? 'Untitled Snippet');
        $code   = (string)($data['code'] ?? '');
        $type   = sanitize_key($data['type'] ?? 'php');
        $scope  = sanitize_key($data['scope'] ?? 'global');
        $active = !empty($data['active']);
        $desc   = sanitize_textarea_field($data['description'] ?? '');

        // 1. Validate syntax
        $validation = self::validate_syntax($code, $type);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $backend = self::get_backend();
        $result = null;

        switch ($backend) {
            case self::BACKEND_CODE_SNIPPETS:
                $result = self::create_code_snippet($name, $code, $desc, $scope, $active);
                break;

            case self::BACKEND_WPCODE:
                $result = self::create_wpcode($name, $code, $desc, $type, $scope, $active);
                break;

            case self::BACKEND_FILE:
            default:
                $result = self::create_file_snippet($name, $code, $desc, $type, $scope, $active);
                break;
        }

        if (!is_wp_error($result)) {
            Kebab_Performance_Synergy::purge_all_caches();
        }

        return $result;
    }

    /**
     * Update Snippet
     *
     * @param string|int $id
     * @param array $data
     * @return array|WP_Error
     */
    public static function update($id, array $data) {
        // If code is supplied, validate syntax
        if (isset($data['code'])) {
            $type = $data['type'] ?? 'php';
            $validation = self::validate_syntax($data['code'], $type);
            if (is_wp_error($validation)) {
                return $validation;
            }
        }

        $backend = self::get_backend();
        $result = null;

        switch ($backend) {
            case self::BACKEND_CODE_SNIPPETS:
                $result = self::update_code_snippet((int)$id, $data);
                break;

            case self::BACKEND_WPCODE:
                $result = self::update_wpcode((int)$id, $data);
                break;

            case self::BACKEND_FILE:
            default:
                $result = self::update_file_snippet((string)$id, $data);
                break;
        }

        if (!is_wp_error($result)) {
            Kebab_Performance_Synergy::purge_all_caches();
        }

        return $result;
    }

    /**
     * Toggle Snippet Active State (Kill Switch)
     *
     * @param string|int $id
     * @param bool $active
     * @return array|WP_Error
     */
    public static function toggle($id, bool $active) {
        return self::update($id, ['active' => $active]);
    }

    /**
     * Delete Snippet
     *
     * @param string|int $id
     * @return bool|WP_Error
     */
    public static function delete($id) {
        $backend = self::get_backend();
        $result = null;

        switch ($backend) {
            case self::BACKEND_CODE_SNIPPETS:
                $result = self::delete_code_snippet((int)$id);
                break;

            case self::BACKEND_WPCODE:
                $result = self::delete_wpcode((int)$id);
                break;

            case self::BACKEND_FILE:
            default:
                $result = self::delete_file_snippet((string)$id);
                break;
        }

        if (!is_wp_error($result) && $result) {
            Kebab_Performance_Synergy::purge_all_caches();
        }

        return $result;
    }

    // =========================================================================
    // BACKEND 1: Code Snippets Plugin (wp_snippets table)
    // =========================================================================

    private static function get_code_snippets_list(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'snippets';
        $rows  = $wpdb->get_results("SELECT id, name, description, scope, active, modified FROM {$table} ORDER BY id ASC", ARRAY_A);

        if (!$rows) {
            return [];
        }

        return array_map(function ($row) {
            return [
                'id'          => (int)$row['id'],
                'name'        => $row['name'],
                'description' => $row['description'],
                'type'        => 'php',
                'scope'       => self::map_code_snippets_scope($row['scope']),
                'active'      => (bool)$row['active'],
                'modified'    => $row['modified'],
            ];
        }, $rows);
    }

    private static function get_code_snippet_one(int $id) {
        global $wpdb;
        $table = $wpdb->prefix . 'snippets';
        $row   = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id), ARRAY_A);

        if (!$row) {
            return new WP_Error('snippet_not_found', 'Snippet not found', ['status' => 404]);
        }

        return [
            'id'          => (int)$row['id'],
            'name'        => $row['name'],
            'description' => $row['description'],
            'code'        => $row['code'],
            'type'        => 'php',
            'scope'       => self::map_code_snippets_scope($row['scope']),
            'active'      => (bool)$row['active'],
            'modified'    => $row['modified'],
        ];
    }

    private static function create_code_snippet(string $name, string $code, string $desc, string $scope, bool $active) {
        global $wpdb;
        $table = $wpdb->prefix . 'snippets';

        $clean_code = ltrim($code);
        if (str_starts_with($clean_code, '<?php')) {
            $clean_code = substr($clean_code, 5);
        }

        $inserted = $wpdb->insert($table, [
            'name'        => $name,
            'description' => $desc,
            'code'        => $clean_code,
            'scope'       => self::unmap_code_snippets_scope($scope),
            'active'      => $active ? 1 : 0,
            'modified'    => current_time('mysql'),
        ], ['%s', '%s', '%s', '%d', '%d', '%s']);

        if (!$inserted) {
            return new WP_Error('db_insert_failed', 'Failed to insert snippet into Code Snippets database', ['status' => 500]);
        }

        return self::get_code_snippet_one((int)$wpdb->insert_id);
    }

    private static function update_code_snippet(int $id, array $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'snippets';

        $update_data   = [];
        $update_format = [];

        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
            $update_format[] = '%s';
        }
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
            $update_format[] = '%s';
        }
        if (isset($data['code'])) {
            $clean_code = ltrim($data['code']);
            if (str_starts_with($clean_code, '<?php')) {
                $clean_code = substr($clean_code, 5);
            }
            $update_data['code'] = $clean_code;
            $update_format[] = '%s';
        }
        if (isset($data['scope'])) {
            $update_data['scope'] = self::unmap_code_snippets_scope($data['scope']);
            $update_format[] = '%d';
        }
        if (isset($data['active'])) {
            $update_data['active'] = !empty($data['active']) ? 1 : 0;
            $update_format[] = '%d';
        }

        $update_data['modified'] = current_time('mysql');
        $update_format[] = '%s';

        $wpdb->update($table, $update_data, ['id' => $id], $update_format, ['%d']);

        return self::get_code_snippet_one($id);
    }

    private static function delete_code_snippet(int $id): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'snippets';
        return (bool)$wpdb->delete($table, ['id' => $id], ['%d']);
    }

    private static function map_code_snippets_scope($scope): string {
        switch ((int)$scope) {
            case 1:  return 'admin';
            case 2:  return 'frontend';
            case 3:  return 'single_use';
            default: return 'global';
        }
    }

    private static function unmap_code_snippets_scope(string $scope): int {
        switch ($scope) {
            case 'admin':      return 1;
            case 'frontend':   return 2;
            case 'single_use': return 3;
            default:           return 0; // global
        }
    }

    // =========================================================================
    // BACKEND 2: WPCode Plugin (wpcode custom post type)
    // =========================================================================

    private static function get_wpcode_list(): array {
        $posts = get_posts([
            'post_type'      => 'wpcode',
            'posts_per_page' => -1,
            'post_status'    => ['publish', 'draft'],
        ]);

        return array_map(function ($p) {
            return [
                'id'          => $p->ID,
                'name'        => $p->post_title,
                'description' => $p->post_content,
                'type'        => get_post_meta($p->ID, '_wpcode_snippet_type', true) ?: 'php',
                'scope'       => get_post_meta($p->ID, '_wpcode_location', true) ?: 'global',
                'active'      => (bool)get_post_meta($p->ID, '_wpcode_active', true),
                'modified'    => $p->post_modified,
            ];
        }, $posts);
    }

    private static function get_wpcode_one(int $id) {
        $post = get_post($id);
        if (!$post || $post->post_type !== 'wpcode') {
            return new WP_Error('snippet_not_found', 'WPCode snippet not found', ['status' => 404]);
        }

        return [
            'id'          => $post->ID,
            'name'        => $post->post_title,
            'description' => $post->post_content,
            'code'        => get_post_meta($post->ID, '_wpcode_snippet_code', true) ?: '',
            'type'        => get_post_meta($post->ID, '_wpcode_snippet_type', true) ?: 'php',
            'scope'       => get_post_meta($post->ID, '_wpcode_location', true) ?: 'global',
            'active'      => (bool)get_post_meta($post->ID, '_wpcode_active', true),
            'modified'    => $post->post_modified,
        ];
    }

    private static function create_wpcode(string $name, string $code, string $desc, string $type, string $scope, bool $active) {
        $post_id = wp_insert_post([
            'post_title'   => $name,
            'post_content' => $desc,
            'post_type'    => 'wpcode',
            'post_status'  => 'publish',
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }

        update_post_meta($post_id, '_wpcode_snippet_code', $code);
        update_post_meta($post_id, '_wpcode_snippet_type', $type);
        update_post_meta($post_id, '_wpcode_location', $scope);
        update_post_meta($post_id, '_wpcode_active', $active ? 1 : 0);

        return self::get_wpcode_one($post_id);
    }

    private static function update_wpcode(int $id, array $data) {
        $post_args = ['ID' => $id];
        if (isset($data['name'])) {
            $post_args['post_title'] = sanitize_text_field($data['name']);
        }
        if (isset($data['description'])) {
            $post_args['post_content'] = sanitize_textarea_field($data['description']);
        }
        if (count($post_args) > 1) {
            wp_update_post($post_args);
        }

        if (isset($data['code'])) {
            update_post_meta($id, '_wpcode_snippet_code', $data['code']);
        }
        if (isset($data['type'])) {
            update_post_meta($id, '_wpcode_snippet_type', sanitize_key($data['type']));
        }
        if (isset($data['scope'])) {
            update_post_meta($id, '_wpcode_location', sanitize_key($data['scope']));
        }
        if (isset($data['active'])) {
            update_post_meta($id, '_wpcode_active', !empty($data['active']) ? 1 : 0);
        }

        return self::get_wpcode_one($id);
    }

    private static function delete_wpcode(int $id): bool {
        return (bool)wp_delete_post($id, true);
    }

    // =========================================================================
    // BACKEND 3: Native File-Based MU-Snippets (Zero-Plugin Mode)
    // =========================================================================

    public static function boot_file_snippets() {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        if (self::get_backend() !== self::BACKEND_FILE) {
            return;
        }

        $dir = self::get_fallback_dir();
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*.php');
        if (!$files) {
            return;
        }

        foreach ($files as $file) {
            // Check if file is disabled via .disabled or header
            if (str_ends_with($file, '.disabled.php') || str_ends_with($file, '.php.disabled')) {
                continue;
            }

            $meta = self::parse_file_headers($file);
            if (!empty($meta['active'])) {
                try {
                    require_once $file;
                } catch (\Throwable $e) {
                    error_log('Kebab Snippet Error in file ' . basename($file) . ': ' . $e->getMessage());
                }
            }
        }
    }

    private static function get_file_snippets_list(): array {
        $dir = self::get_fallback_dir();
        if (!is_dir($dir)) {
            return [];
        }

        $all_files = array_merge(
            glob($dir . '/*.php') ?: [],
            glob($dir . '/*.php.disabled') ?: []
        );

        $list = [];
        foreach ($all_files as $file) {
            $base = basename($file);
            $slug = preg_replace('/\.(php|disabled)+$/', '', $base);
            $meta = self::parse_file_headers($file);

            $is_disabled_ext = str_contains($base, '.disabled');

            $list[] = [
                'id'          => $slug,
                'name'        => $meta['name'] ?: $slug,
                'description' => $meta['description'] ?: '',
                'type'        => $meta['type'] ?: 'php',
                'scope'       => $meta['scope'] ?: 'global',
                'active'      => !$is_disabled_ext && !empty($meta['active']),
                'modified'    => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        return $list;
    }

    private static function get_file_snippet_one(string $slug) {
        $dir = self::get_fallback_dir();
        $file_active   = $dir . '/' . $slug . '.php';
        $file_disabled = $dir . '/' . $slug . '.php.disabled';

        $target = file_exists($file_active) ? $file_active : (file_exists($file_disabled) ? $file_disabled : null);

        if (!$target) {
            return new WP_Error('snippet_not_found', 'File snippet not found: ' . $slug, ['status' => 404]);
        }

        $meta = self::parse_file_headers($target);
        $raw  = file_get_contents($target);
        $code = self::strip_file_header($raw);

        $is_disabled = str_ends_with($target, '.disabled');

        return [
            'id'          => $slug,
            'name'        => $meta['name'] ?: $slug,
            'description' => $meta['description'] ?: '',
            'code'        => $code,
            'type'        => $meta['type'] ?: 'php',
            'scope'       => $meta['scope'] ?: 'global',
            'active'      => !$is_disabled && !empty($meta['active']),
            'modified'    => date('Y-m-d H:i:s', filemtime($target)),
        ];
    }

    private static function create_file_snippet(string $name, string $code, string $desc, string $type, string $scope, bool $active) {
        $dir  = self::get_fallback_dir();
        $slug = sanitize_title($name);
        if (empty($slug)) {
            $slug = 'snippet-' . time();
        }

        $filename = $active ? "{$slug}.php" : "{$slug}.php.disabled";
        $path     = $dir . '/' . $filename;

        $content = self::build_file_content($name, $desc, $type, $scope, $active, $code);
        $saved   = file_put_contents($path, $content);

        if ($saved === false) {
            return new WP_Error('file_write_failed', 'Could not write snippet to file system', ['status' => 500]);
        }

        return self::get_file_snippet_one($slug);
    }

    private static function update_file_snippet(string $slug, array $data) {
        $existing = self::get_file_snippet_one($slug);
        if (is_wp_error($existing)) {
            return $existing;
        }

        $name   = $data['name'] ?? $existing['name'];
        $desc   = $data['description'] ?? $existing['description'];
        $code   = $data['code'] ?? $existing['code'];
        $type   = $data['type'] ?? $existing['type'];
        $scope  = $data['scope'] ?? $existing['scope'];
        $active = isset($data['active']) ? !empty($data['active']) : $existing['active'];

        $dir = self::get_fallback_dir();
        $old_active_path   = $dir . '/' . $slug . '.php';
        $old_disabled_path = $dir . '/' . $slug . '.php.disabled';

        // Remove old file path
        if (file_exists($old_active_path)) {
            unlink($old_active_path);
        }
        if (file_exists($old_disabled_path)) {
            unlink($old_disabled_path);
        }

        $new_filename = $active ? "{$slug}.php" : "{$slug}.php.disabled";
        $new_path     = $dir . '/' . $new_filename;

        $content = self::build_file_content($name, $desc, $type, $scope, $active, $code);
        file_put_contents($new_path, $content);

        return self::get_file_snippet_one($slug);
    }

    private static function delete_file_snippet(string $slug): bool {
        $dir = self::get_fallback_dir();
        $deleted = false;
        if (file_exists($dir . '/' . $slug . '.php')) {
            $deleted = unlink($dir . '/' . $slug . '.php');
        }
        if (file_exists($dir . '/' . $slug . '.php.disabled')) {
            $deleted = unlink($dir . '/' . $slug . '.php.disabled');
        }
        return $deleted;
    }

    private static function parse_file_headers(string $path): array {
        $meta = [
            'name'        => '',
            'description' => '',
            'type'        => 'php',
            'scope'       => 'global',
            'active'      => true,
        ];

        if (!file_exists($path)) {
            return $meta;
        }

        $content = file_get_contents($path, false, null, 0, 4096);
        if (preg_match('/Snippet Name:\s*([^\r\n]+)/i', $content, $m)) {
            $meta['name'] = trim($m[1]);
        }
        if (preg_match('/Description:\s*([^\r\n]+)/i', $content, $m)) {
            $meta['description'] = trim($m[1]);
        }
        if (preg_match('/Type:\s*([^\r\n]+)/i', $content, $m)) {
            $meta['type'] = trim($m[1]);
        }
        if (preg_match('/Scope:\s*([^\r\n]+)/i', $content, $m)) {
            $meta['scope'] = trim($m[1]);
        }
        if (preg_match('/Active:\s*(true|false|1|0)/i', $content, $m)) {
            $meta['active'] = in_array(strtolower($m[1]), ['true', '1'], true);
        }

        return $meta;
    }

    private static function build_file_content(string $name, string $desc, string $type, string $scope, bool $active, string $code): string {
        $active_str = $active ? 'true' : 'false';
        $date       = current_time('mysql');

        $header = "<?php\n"
            . "/**\n"
            . " * Snippet Name: {$name}\n"
            . " * Description: {$desc}\n"
            . " * Type: {$type}\n"
            . " * Scope: {$scope}\n"
            . " * Active: {$active_str}\n"
            . " * Modified: {$date}\n"
            . " */\n\n"
            . "if (!defined('ABSPATH')) {\n"
            . "    exit;\n"
            . "}\n\n";

        $clean_code = ltrim($code);
        if (str_starts_with($clean_code, '<?php')) {
            $clean_code = substr($clean_code, 5);
            $clean_code = ltrim($clean_code);
        }

        return $header . $clean_code . "\n";
    }

    private static function strip_file_header(string $raw): string {
        // Strip opening comment block and ABSPATH guard
        $code = preg_replace('/^<\?php\s*\/\*\*.*?\*\/\s*(if\s*\(!defined\(\'ABSPATH\'\)\)\s*\{\s*exit;\s*\})?/s', '', $raw);
        return trim($code);
    }

    // =========================================================================
    // NOVAMIRA MCP ABILITIES REGISTRATION
    // =========================================================================

    public static function register_abilities() {
        if (!function_exists('wp_register_ability')) {
            return;
        }

        // 1. Ability: List Snippets
        wp_register_ability('kebab/list-snippets', [
            'label'        => __('List WP Code Snippets', 'carinena-kebab-fse'),
            'description'  => __('Lists all code snippets across Code Snippets, WPCode, or native MU fallback, including active status, scope, and modification timestamps.', 'carinena-kebab-fse'),
            'category'     => 'code-execution',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'backend'  => ['type' => 'string'],
                    'snippets' => ['type' => 'array'],
                ],
            ],
            'execute_callback' => function () {
                return self::get_all();
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // 2. Ability: Get Snippet
        wp_register_ability('kebab/get-snippet', [
            'label'        => __('Get WP Code Snippet', 'carinena-kebab-fse'),
            'description'  => __('Retrieves a single code snippet by ID or slug with full executable code.', 'carinena-kebab-fse'),
            'category'     => 'code-execution',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'id' => ['type' => ['integer', 'string'], 'description' => 'Snippet ID or slug'],
                ],
                'required' => ['id'],
            ],
            'execute_callback' => function ($input) {
                return self::get_one($input['id']);
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // 3. Ability: Save / Create Snippet
        wp_register_ability('kebab/save-snippet', [
            'label'        => __('Save WP Code Snippet', 'carinena-kebab-fse'),
            'description'  => __('Creates or updates a code snippet with automatic pre-flight PHP tokenizer syntax checking to prevent fatal errors.', 'carinena-kebab-fse'),
            'category'     => 'code-execution',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'id'          => ['type' => ['integer', 'string'], 'description' => 'Optional ID if updating'],
                    'name'        => ['type' => 'string'],
                    'code'        => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'type'        => ['type' => 'string', 'enum' => ['php', 'js', 'css', 'html']],
                    'scope'       => ['type' => 'string', 'enum' => ['global', 'admin', 'frontend']],
                    'active'      => ['type' => 'boolean'],
                ],
                'required' => ['name', 'code'],
            ],
            'execute_callback' => function ($input) {
                if (!empty($input['id'])) {
                    return self::update($input['id'], $input);
                }
                return self::create($input);
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // 4. Ability: Toggle Snippet (Kill Switch)
        wp_register_ability('kebab/toggle-snippet', [
            'label'        => __('Toggle WP Code Snippet Active State', 'carinena-kebab-fse'),
            'description'  => __('Instantly activates or deactivates a snippet without touching source files, providing an instant kill-switch for troubleshooting.', 'carinena-kebab-fse'),
            'category'     => 'code-execution',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'id'     => ['type' => ['integer', 'string'], 'description' => 'Snippet ID or slug'],
                    'active' => ['type' => 'boolean', 'description' => 'True to activate, false to deactivate'],
                ],
                'required' => ['id', 'active'],
            ],
            'execute_callback' => function ($input) {
                return self::toggle($input['id'], (bool)$input['active']);
            },
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }
}

// Boot Snippet Bridge
Kebab_Snippet_Bridge::init();
