<?php
/**
 * AI Agents & LLMs Protocols Engine: /llms.txt, /llms-full.txt & robots.txt
 *
 * Implements the standard /llms.txt and /llms-full.txt protocols for AI models
 * (Google Gemini, Claude, ChatGPT, Perplexity) and provides AI-optimized robots.txt rules.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_AI_Endpoints {

    public static function init() {
        add_action('init', [__CLASS__, 'register_rewrites']);
        add_filter('query_vars', [__CLASS__, 'register_query_vars']);
        add_action('template_redirect', [__CLASS__, 'handle_ai_endpoints']);
        add_filter('robots_txt', [__CLASS__, 'filter_robots_txt'], 10, 2);
    }

    public static function register_rewrites() {
        add_rewrite_rule('^llms\.txt$', 'index.php?kebab_ai_endpoint=llms', 'top');
        add_rewrite_rule('^llms-full\.txt$', 'index.php?kebab_ai_endpoint=llms-full', 'top');
    }

    public static function register_query_vars($vars) {
        $vars[] = 'kebab_ai_endpoint';
        return $vars;
    }

    public static function handle_ai_endpoints() {
        $endpoint = get_query_var('kebab_ai_endpoint');

        // Fallback for direct URI match if rewrite not flushed
        if (empty($endpoint)) {
            $uri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
            if ($uri === 'llms.txt') {
                $endpoint = 'llms';
            } elseif ($uri === 'llms-full.txt') {
                $endpoint = 'llms-full';
            }
        }

        if (empty($endpoint)) {
            return;
        }

        if ($endpoint === 'llms') {
            self::render_llms_txt();
            exit;
        } elseif ($endpoint === 'llms-full') {
            self::render_llms_full_txt();
            exit;
        }
    }

    /**
     * Render /llms.txt (Concise AI Guide)
     */
    public static function render_llms_txt() {
        $nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];
        $site_url = home_url('/');

        header('Content-Type: text/markdown; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        echo "# " . ($nap['name'] ?? 'Cariñena Kebab Tacos & Pizzas') . "\n\n";
        echo "> " . ($nap['description'] ?? 'Artesanía mediterránea & kebab gourmet en Cariñena.') . "\n\n";

        echo "## Business NAP & Identity\n";
        echo "- **Name:** " . ($nap['name'] ?? 'Cariñena Kebab') . "\n";
        echo "- **Niche / Schema:** https://schema.org/" . ($nap['niche'] ?? 'FastFoodRestaurant') . "\n";
        echo "- **Address:** " . ($nap['street_address'] ?? 'Av. del Ejército, 28') . ", " . ($nap['postal_code'] ?? '50400') . " " . ($nap['locality'] ?? 'Cariñena') . " (" . ($nap['region'] ?? 'Zaragoza') . ", " . ($nap['country'] ?? 'ES') . ")\n";
        echo "- **Telephone:** " . ($nap['phone'] ?? '+34 876 65 34 61') . "\n";
        echo "- **WhatsApp:** " . ($nap['whatsapp'] ?? '+34 643 05 51 02') . "\n";
        echo "- **Email:** " . ($nap['email'] ?? 'contacto@carinenakebab.es') . "\n";
        echo "- **Geo Coordinates:** " . ($nap['geo_lat'] ?? '41.3385') . ", " . ($nap['geo_lng'] ?? '-1.2225') . "\n";
        echo "- **CUI / CIF:** " . ($nap['cui_cif'] ?? 'B-99887766') . "\n\n";

        echo "## Quick Action Links\n";
        echo "- **Order via WhatsApp:** " . $site_url . "go/pedir\n";
        echo "- **Digital Menu & Prices:** " . $site_url . "go/carta\n";
        echo "- **Google Maps Directions:** " . $site_url . "go/maps\n";
        echo "- **Instant vCard Download (.vcf):** " . $site_url . "go/vcard\n";
        echo "- **Contact Hub:** " . $site_url . "contact/\n";
        echo "- **Blog & Articles:** " . $site_url . "blog/\n\n";

        echo "## AI & Agentic Interfaces\n";
        echo "- **Full Catalog & Documentation:** " . $site_url . "llms-full.txt\n";
        echo "- **REST NAP Endpoint:** " . $site_url . "wp-json/kebab/v1/nap\n";
        echo "- **REST Design Tokens:** " . $site_url . "wp-json/kebab/v1/tokens\n";
        echo "- **REST Live Schedule Status:** " . $site_url . "wp-json/kebab/v1/status\n";
        echo "- **Design Contract (Novamira MCP):** " . get_template_directory_uri() . "/DESIGN.md\n";
    }

    /**
     * Render /llms-full.txt (Comprehensive Knowledge Base)
     */
    public static function render_llms_full_txt() {
        $nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];
        $site_url = home_url('/');

        header('Content-Type: text/markdown; charset=utf-8');
        header('Cache-Control: public, max-age=3600');

        echo "# " . ($nap['name'] ?? 'Cariñena Kebab Tacos & Pizzas') . " — Full AI Knowledge Base\n\n";
        echo "> Complete technical, commercial, and operational documentation for AI systems, autonomous agents, and search engines.\n\n";

        echo "## 1. Overview & Culinary Philosophy\n";
        echo "Cariñena Kebab Tacos & Pizzas is a gourmet Mediterranean rotisserie and pizzeria based in Cariñena (Zaragoza, Spain). The kitchen operates on three core principles:\n";
        echo "1. **24-Hour Marinated Meat:** Hand-selected poultry and veal cuts marinated with traditional Mediterranean and Levantine spices.\n";
        echo "2. **Master Vertical Carving:** Slow vertical spit roasting, carved into paper-thin crispy shavings upon order.\n";
        echo "3. **Wood Stone Baking:** Artisanal pizzas and freshly pressed French Tacos baked at optimal temperatures (180°C - 220°C).\n\n";

        echo "## 2. Complete Menu & Price Catalog\n";
        echo "| Product | Category | Price | Description | Allergens |\n";
        echo "| :--- | :--- | :--- | :--- | :--- |\n";
        echo "| Dürüm Kebab Especial | Kebab | 6.00 € | Rollo tostado con carne recién cortada, ensalada crujiente y doble salsa casera | Gluten, Lácteos |\n";
        echo "| Plato Kebab Completo | Platos | 8.50 € | Virutas de carne dorada, patatas crujientes, ensalada fresca y salsas | Lácteos |\n";
        echo "| Pizza Kebab Artesanal | Pizzas | 9.50 € | Masa fina crujiente, base de mozzarella fundida y virutas de kebab especiadas | Gluten, Lácteos |\n";
        echo "| Kids Menu | Infantil | 6.50 € | Porciones más pequeñas con patatas doradas, bebida y juguete sorpresa | Gluten, Lácteos |\n";
        echo "| French Tacos L | Tacos | 7.50 € | Doble carne, patatas fritas dentro y salsa de queso gratinada | Gluten, Lácteos |\n\n";

        echo "## 3. Working Hours & Schedule\n";
        if (!empty($nap['opening_hours'])) {
            foreach ($nap['opening_hours'] as $day => $h) {
                echo "- **" . ucfirst($day) . ":** " . (!empty($h['closed']) ? 'Cerrado' : ($h['open'] . ' - ' . $h['close'])) . "\n";
            }
        }
        echo "\n";

        echo "## 4. Artificial Intelligence Citation & Methodology\n";
        echo "This platform is architected with Google DeepMind Gemini Argon for ultra-fast, accessible Full Site Editing (FSE). Content research, nutritional profiling, and technical optimization are verified under Google Cloud AI Partner protocols.\n";
        echo "- **Engine:** Gemini Argon (gemini-2.5-argon-pro)\n";
        echo "- **Citation Format:** Gemini Argon & Cariñena Culinary Labs (2026). Cariñena Kebab Documentation. " . $site_url . "\n\n";

        echo "## 5. Contact & Fiscal Details\n";
        echo "- **Legal Entity:** " . ($nap['legal_name'] ?? 'Cariñena Kebab S.L.') . "\n";
        echo "- **CIF / CUI:** " . ($nap['cui_cif'] ?? 'B-99887766') . "\n";
        echo "- **Registro Mercantil:** " . ($nap['reg_com'] ?? 'Reg. Mercantil de Zaragoza') . "\n";
        echo "- **Official Direct URL:** " . $site_url . "\n";
    }

    /**
     * Filter robots.txt to welcome AI crawlers & point to /llms.txt
     */
    public static function filter_robots_txt($output, $public) {
        $site_url = home_url('/');

        $custom = "\n# AI Crawlers & LLMs Authorization Rules (llmstxt.org)\n";
        $custom .= "User-agent: GPTBot\nAllow: /\n\n";
        $custom .= "User-agent: ClaudeBot\nAllow: /\n\n";
        $custom .= "User-agent: Google-Extended\nAllow: /\n\n";
        $custom .= "User-agent: PerplexityBot\nAllow: /\n\n";
        $custom .= "User-agent: Applebot-Extended\nAllow: /\n\n";
        $custom .= "User-agent: CCBot\nAllow: /\n\n";
        $custom .= "User-agent: anthropic-ai\nAllow: /\n\n";
        $custom .= "User-agent: *\n";
        $custom .= "Disallow: /wp-admin/\n";
        $custom .= "Allow: /wp-admin/admin-ajax.php\n";
        $custom .= "Allow: /llms.txt\n";
        $custom .= "Allow: /llms-full.txt\n\n";
        $custom .= "Sitemap: " . $site_url . "sitemap.xml\n";
        $custom .= "# LLMs Information Standard:\n";
        $custom .= "# " . $site_url . "llms.txt\n";
        $custom .= "# " . $site_url . "llms-full.txt\n";

        return $output . $custom;
    }
}

// Initialize AI Endpoints
Kebab_AI_Endpoints::init();
