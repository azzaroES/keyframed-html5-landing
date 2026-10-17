<?php
/**
 * Cariñena Kebab FSE Theme Functions & Definitions
 *
 * Full Site Editing (FSE) Block Theme with Novamira MCP compatibility,
 * multi-niche Google NAP, SVG QR generator, shortlinker, and LiteSpeed Cache synergy.
 *
 * @package Cariñena_Kebab_FSE
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KEBAB_THEME_VERSION', '2.0.0');
define('KEBAB_THEME_DIR', get_template_directory());
define('KEBAB_THEME_URI', get_template_directory_uri());

// 1. Load Performance & Core Engine Modules
require_once KEBAB_THEME_DIR . '/inc/seo-nap.php';
require_once KEBAB_THEME_DIR . '/inc/qr-generator.php';
require_once KEBAB_THEME_DIR . '/inc/shortlinker.php';
require_once KEBAB_THEME_DIR . '/inc/vcard-generator.php';
require_once KEBAB_THEME_DIR . '/inc/lscache-compat.php';
require_once KEBAB_THEME_DIR . '/inc/rest-api.php';

/**
 * Theme Setup: Enable FSE features, block styles, and semantic HTML5
 */
function kebab_fse_theme_setup() {
    // Add default block styles support
    add_theme_support('wp-block-styles');

    // Add support for editor styles
    add_theme_support('editor-styles');
    add_editor_style('style.css');

    // Responsive embeds
    add_theme_support('responsive-embeds');

    // Post thumbnails
    add_theme_support('post-thumbnails');

    // Semantic HTML5 markup
    add_theme_support('html5', [
        'comment-list',
        'comment-form',
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
        'navigation-widgets',
    ]);

    // Register Block Pattern Categories
    register_block_pattern_category('carinena-kebab-hero', [
        'label' => __('3D Interactive Heroes', 'carinena-kebab-fse'),
    ]);
    register_block_pattern_category('carinena-kebab-sections', [
        'label' => __('Cariñena Sections (Order, Bento, Menu, Club)', 'carinena-kebab-fse'),
    ]);
    register_block_pattern_category('carinena-kebab-contact', [
        'label' => __('Contact Hub (Foto V-MAG Multi-Niche)', 'carinena-kebab-fse'),
    ]);
    register_block_pattern_category('carinena-kebab-blog', [
        'label' => __('Cinematic Blog Posts (Video Pull-Up)', 'carinena-kebab-fse'),
    ]);
}
add_action('after_setup_theme', 'kebab_fse_theme_setup');

/**
 * Flush rewrite rules on theme switch to activate /go/ routes
 */
function kebab_fse_rewrite_activation() {
    Kebab_Shortlinker::register_rewrites();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'kebab_fse_rewrite_activation');

/**
 * Enqueue Theme Styles & Scripts
 */
function kebab_fse_enqueue_assets() {
    $uri = KEBAB_THEME_URI;
    $ver = KEBAB_THEME_VERSION;

    // 1. Root & Base Tokens
    wp_enqueue_style('kebab-root-style', get_stylesheet_uri(), [], $ver);
    wp_enqueue_style('kebab-tokens', $uri . '/css/tokens.css', ['kebab-root-style'], $ver);
    wp_enqueue_style('kebab-base', $uri . '/css/base.css', ['kebab-tokens'], $ver);
    wp_enqueue_style('kebab-components', $uri . '/css/components.css', ['kebab-base'], $ver);
    wp_enqueue_style('kebab-themes', $uri . '/css/themes.css', ['kebab-tokens'], $ver);
    wp_enqueue_style('kebab-motion', $uri . '/css/motion.css', ['kebab-components'], $ver);

    // Effects & Section specific styles
    wp_enqueue_style('kebab-fx-assembly', $uri . '/css/effects/assembly.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-header', $uri . '/css/sections/header.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-films-stage', $uri . '/css/sections/films/stage.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-films-hero', $uri . '/css/sections/films/hero.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-films-cast', $uri . '/css/sections/films/cast.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-films-seq', $uri . '/css/sections/films/sequence.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-order', $uri . '/css/sections/order.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-pillars', $uri . '/css/sections/pillars.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-menu', $uri . '/css/sections/menu.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-club', $uri . '/css/sections/club.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-footer-hero', $uri . '/css/sections/footer/hero.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-footer-nav', $uri . '/css/sections/footer/nav.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-footer-bottom', $uri . '/css/sections/footer/bottom.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-sec-footer-terminal', $uri . '/css/sections/footer/terminal.css', ['kebab-components'], $ver);

    // UI Additions
    wp_enqueue_style('kebab-ui-a11y', $uri . '/css/ui/a11y-panel.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-whatsapp', $uri . '/css/ui/whatsapp.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-fab-nav', $uri . '/css/ui/fab-nav.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-back-to-top', $uri . '/css/ui/back-to-top.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-fx-paging', $uri . '/css/effects/paging.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-preloader', $uri . '/css/ui/preloader.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-live-status', $uri . '/css/ui/live-status.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-bottom-nav', $uri . '/css/ui/bottom-nav.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-blog-sheet', $uri . '/css/ui/blog-sheet.css', ['kebab-components'], $ver);
    wp_enqueue_style('kebab-ui-contact-hub', $uri . '/css/ui/contact-hub.css', ['kebab-components'], $ver);

    // 2. JavaScript Controllers (deferred, loaded in exact dependency order)
    wp_enqueue_script('kebab-core-js', $uri . '/js/core.js', [], $ver, true);
    wp_enqueue_script('kebab-frames-js', $uri . '/js/films/frames.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-film-js', $uri . '/js/films/film.js', ['kebab-core-js', 'kebab-frames-js'], $ver, true);
    wp_enqueue_script('kebab-scroll-assembly', $uri . '/js/scroll/assembly.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-scroll-parallax', $uri . '/js/scroll/parallax.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-scroll-strokes', $uri . '/js/scroll/strokes.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-scroll-paging', $uri . '/js/scroll/paging.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-ui-back-to-top', $uri . '/js/ui/back-to-top.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-ui-a11y-js', $uri . '/js/ui/a11y-panel.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-ui-fab-nav-js', $uri . '/js/ui/fab-nav.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-ui-wa-js', $uri . '/js/ui/whatsapp.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-signup-js', $uri . '/js/signup.js', ['kebab-core-js'], $ver, true);
    wp_enqueue_script('kebab-preloader-js', $uri . '/js/ui/preloader.js', ['kebab-core-js', 'kebab-frames-js'], $ver, true);
    wp_enqueue_script('kebab-main-js', $uri . '/js/main.js', ['kebab-core-js', 'kebab-film-js'], $ver, true);

    // Dynamic UI enhancements
    wp_enqueue_script('kebab-live-status-js', $uri . '/js/ui/live-status.js', [], $ver, true);
    wp_enqueue_script('kebab-blog-sheet-js', $uri . '/js/ui/blog-sheet.js', [], $ver, true);

    // Localize configuration for JS controllers
    wp_localize_script('kebab-core-js', 'kebabThemeConfig', [
        'themeUrl'       => $uri,
        'homeUrl'        => home_url('/'),
        'isFrontPage'    => is_front_page(),
        'isEditor'       => false,
        'restUrl'        => esc_url_raw(rest_url('kebab/v1/')),
        'nonce'          => wp_create_nonce('wp_rest'),
    ]);
}
add_action('wp_enqueue_scripts', 'kebab_fse_enqueue_assets');

/**
 * Enqueue scripts and styles in Gutenberg Editor
 */
function kebab_fse_block_editor_assets() {
    $uri = KEBAB_THEME_URI;
    $ver = KEBAB_THEME_VERSION;

    wp_enqueue_style('kebab-editor-tokens', $uri . '/css/tokens.css', [], $ver);
    wp_enqueue_style('kebab-editor-components', $uri . '/css/components.css', [], $ver);
    wp_enqueue_style('kebab-editor-films', $uri . '/css/sections/films.css', [], $ver);
    wp_enqueue_style('kebab-editor-menu', $uri . '/css/sections/menu.css', [], $ver);
    wp_enqueue_style('kebab-editor-pillars', $uri . '/css/sections/pillars.css', [], $ver);
    wp_enqueue_style('kebab-editor-live-status', $uri . '/css/ui/live-status.css', [], $ver);
    wp_enqueue_style('kebab-editor-blog-sheet', $uri . '/css/ui/blog-sheet.css', [], $ver);

    wp_enqueue_script('kebab-editor-film-guard', $uri . '/js/films/film-editor-guard.js', ['wp-blocks', 'wp-dom-ready'], $ver, true);

    wp_localize_script('kebab-editor-film-guard', 'kebabEditorConfig', [
        'themeUrl' => $uri,
        'isEditor' => true,
    ]);
}
add_action('enqueue_block_editor_assets', 'kebab_fse_block_editor_assets');

/**
 * Filter body class to add front-page vs inner-page markers for adaptive navigation
 */
function kebab_fse_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'kebab-front-page';
    } else {
        $classes[] = 'kebab-inner-page';
    }
    return $classes;
}
add_filter('body_class', 'kebab_fse_body_classes');
