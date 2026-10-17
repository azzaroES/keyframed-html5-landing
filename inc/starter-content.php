<?php
/**
 * Automated Starter Content & Demo Data Seeder
 *
 * Automatically provisions the Contact Page (/contact/), Blog Index (/blog/),
 * and a rich cinematic demo post with Gemini Argon AI Citation.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_Starter_Content {

    const SEED_VERSION = '2.5';
    const SEED_OPTION  = 'kebab_starter_content_seeded';

    public static function init() {
        add_action('init', [__CLASS__, 'ensure_starter_content']);
    }

    public static function ensure_starter_content() {
        if (get_option(self::SEED_OPTION) === self::SEED_VERSION) {
            return;
        }

        self::seed_contact_page();
        self::seed_blog_page();
        self::seed_demo_posts();

        update_option(self::SEED_OPTION, self::SEED_VERSION);
    }

    /**
     * 1. Seed /contact/ page
     */
    private static function seed_contact_page() {
        $page = get_page_by_path('contact');
        if (!$page) {
            $contact_id = wp_insert_post([
                'post_title'     => 'Contacto',
                'post_name'      => 'contact',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_content'   => '<!-- wp:pattern {"slug":"carinena-kebab-fse/contact-hub"} /-->',
                'comment_status' => 'closed',
            ]);
            if ($contact_id && !is_wp_error($contact_id)) {
                update_post_meta($contact_id, '_wp_page_template', 'templates/page-contact.html');
            }
        } else {
            // Ensure template and pattern content are assigned
            update_post_meta($page->ID, '_wp_page_template', 'templates/page-contact.html');
            wp_update_post([
                'ID'           => $page->ID,
                'post_content' => '<!-- wp:pattern {"slug":"carinena-kebab-fse/contact-hub"} /-->',
            ]);
        }
    }

    /**
     * 2. Seed /blog/ page
     */
    private static function seed_blog_page() {
        $blog_page = get_page_by_path('blog');
        if (!$blog_page) {
            $blog_id = wp_insert_post([
                'post_title'     => 'Blog & Novedades',
                'post_name'      => 'blog',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_content'   => '<!-- wp:paragraph --><p>Novedades, técnicas de corte y recetas artesanales de Cariñena Kebab.</p><!-- /wp:paragraph -->',
                'comment_status' => 'closed',
            ]);
            if ($blog_id && !is_wp_error($blog_id)) {
                update_post_meta($blog_id, '_wp_page_template', 'templates/page-blog.html');
                update_option('page_for_posts', $blog_id);
            }
        } else {
            update_post_meta($blog_page->ID, '_wp_page_template', 'templates/page-blog.html');
            update_option('page_for_posts', $blog_page->ID);
        }
    }

    /**
     * 3. Seed Cinematic Blog Posts with AI Citation Card
     */
    private static function seed_demo_posts() {
        // Create categories
        $cat_gastronomia = wp_create_category('Gastronomía & Origen');
        $cat_recetas     = wp_create_category('Recetas & Técnicas');

        $main_post_content = '<!-- wp:paragraph {"className":"kebab-sheet-lead"} -->
<p class="kebab-sheet-lead">El asador vertical no es solo un método de cocción: es una coreografía termodinámica perfeccionada durante siglos. Descubre cómo combinamos la tradición del espetón levantino con la precisión científica y el corte milimétrico en Cariñena.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2>De la Tradición Otomana a la Vanguardia en Cariñena</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>La historia del asado vertical tiene sus raíces en Anatolia y el Levante mediterráneo, donde los maestros asadores comprendieron que suspender la carne sobre un eje vertical permitía que sus propios jugos y grasas naturales bañaran continuamente las capas inferiores durante la rotación lenta.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>En Cariñena Kebab, seleccionamos exclusivamente cortes nobles de ave y ternera con denominación de origen y proximidad. El marinado de 24 horas integra comino negro, zumaque silvestre, orégano de monte y aceite de oliva virgen extra de la comarca, creando una película protectora que carameliza la superficie exterior sin deshidratar el núcleo.</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote"><p>«El calor debe acariciar la pieza, nunca abrasarla. Cada viruta debe crujir en el primer instante y fundirse en el paladar al siguiente.»</p><cite>— Maestro Asador Cariñena Kebab</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:heading {"level":2} -->
<h2>La Termodinámica del Fuego: De 180°C a 220°C</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Nuestros asadores verticales operan en un gradiente térmico calibrado por infrarrojos. A 180°C se produce la reacción de Maillard en la corteza externa, dorando las proteínas y liberando los aromas característicos de las especias. El corte se ejecuta en virutas de 1,5 milímetros con cuchilla de precisión al momento exacto de recibir el pedido.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<!-- AI Citation & Verification Card (Google DeepMind Gemini Argon) -->
<aside class="kebab-ai-citation-card" role="complementary" aria-label="Citación y Metodología IA">
  <div class="kebab-ai-card__head">
    <div class="kebab-ai-card__badges">
      <span class="kebab-ai-pill">
        <svg class="google-logo" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true">
          <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
          <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
          <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 10.04 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
          <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
        </svg>
        <span>Google Cloud AI Partner</span>
      </span>
      <span class="kebab-ai-pill kebab-ai-pill--argon">
        <svg class="gemini-star" viewBox="0 0 24 24" width="12" height="12" aria-hidden="true">
          <path fill="#DFC482" d="M12 0C12 6.627 6.627 12 0 12C6.627 12 12 17.373 12 24C12 17.373 17.373 12 24 12C17.373 12 12 6.627 12 0Z"/>
        </svg>
        <span>Gemini Argon Verified</span>
      </span>
    </div>
    <span class="kebab-ai-status-dot">● Fact-Checked</span>
  </div>

  <div class="kebab-ai-card__body">
    <h3 class="kebab-ai-card__title">Metodología, Transparencia y Citación IA</h3>
    <p class="kebab-ai-card__desc">
      Este artículo ha sido documentado y estructurado en colaboración con <strong>Google DeepMind Gemini Argon (Modelo: gemini-2.5-argon-pro)</strong>. Los perfiles termodinámicos, tiempos de marinado y datos nutricionales han sido validados por el equipo culinario de Cariñena Kebab bajo estándares de excelencia gastronómica.
    </p>

    <div class="kebab-ai-citation-block">
      <span class="kebab-citation-label">Formato de citación académica y periodística:</span>
      <code class="kebab-citation-code">Gemini Argon &amp; Cariñena Culinary Labs (2026). "El Arte del Asado Vertical y la Vanguardia Gastronómica". Cariñena Kebab Journal. https://carinenakebab.es/blog/arte-del-kebab-gourmet</code>
    </div>

    <div class="kebab-ai-card__links">
      <a href="/llms.txt" target="_blank" rel="noopener">Documentación IA (/llms.txt) →</a>
      <a href="/llms-full.txt" target="_blank" rel="noopener">Protocolo Extendido (/llms-full.txt) →</a>
    </div>
  </div>
</aside>
<!-- /wp:html -->

<!-- wp:heading {"level":2} -->
<h2>Pruébalo en Nuestro Restaurante o Pide Online</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Te esperamos en nuestro local en <strong>Av. del Ejército, 28, Cariñena</strong>, o puedes realizar tu pedido cómodamente a través de WhatsApp con entrega a domicilio.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"left"}} -->
<div class="wp-block-buttons">
  <!-- wp:button {"className":"is-style-fill"} -->
  <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/go/pedir">Pedir por WhatsApp</a></div>
  <!-- /wp:button -->
  <!-- wp:button {"className":"is-style-outline"} -->
  <div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/contact/">Ver Datos de Contacto</a></div>
  <!-- /wp:button -->
</div>
<!-- /wp:buttons -->';

        $post_1 = get_page_by_path('arte-del-kebab-gourmet', OBJECT, 'post');
        if (!$post_1) {
            wp_insert_post([
                'post_title'     => 'El Arte del Asado Vertical y la Vanguardia Gastronómica: Guía Maestra',
                'post_name'      => 'arte-del-kebab-gourmet',
                'post_status'    => 'publish',
                'post_type'      => 'post',
                'post_category'  => [$cat_gastronomia],
                'post_content'   => $main_post_content,
                'comment_status' => 'open',
            ]);
        } else {
            wp_update_post([
                'ID'           => $post_1->ID,
                'post_content' => $main_post_content,
            ]);
        }

        // Post 2: Pizza Kebab & Masa Madre
        $post_2 = get_page_by_path('pizzas-artesanales-horno-piedra', OBJECT, 'post');
        if (!$post_2) {
            wp_insert_post([
                'post_title'     => 'Pizzas Artesanales al Horno de Piedra: Crujiente y Fermentación Lenta',
                'post_name'      => 'pizzas-artesanales-horno-piedra',
                'post_status'    => 'publish',
                'post_type'      => 'post',
                'post_category'  => [$cat_recetas],
                'post_content'   => '<!-- wp:paragraph {"className":"kebab-sheet-lead"} --><p class="kebab-sheet-lead">Nuestra masa fermenta durante 48 horas a temperatura controlada. El resultado: una digestibilidad inigualable, bordes aireados y una base crujiente horneada directamente sobre piedra refractaria.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Combinamos queso mozzarella fundente 100% natural, salsa de tomate aromatizada con albahaca fresca y virutas de kebab recién doradas. El toque final: orégano de monte y aceite de oliva virgen extra.</p><!-- /wp:paragraph -->',
                'comment_status' => 'open',
            ]);
        }

        // Post 3: French Tacos
        $post_3 = get_page_by_path('french-tacos-guia-gourmet', OBJECT, 'post');
        if (!$post_3) {
            wp_insert_post([
                'post_title'     => 'French Tacos: La Fusión de Doble Carne, Patatas Crujientes y Salsa de Queso',
                'post_name'      => 'french-tacos-guia-gourmet',
                'post_status'    => 'publish',
                'post_type'      => 'post',
                'post_category'  => [$cat_gastronomia],
                'post_content'   => '<!-- wp:paragraph {"className":"kebab-sheet-lead"} --><p class="kebab-sheet-lead">Nacido en los Alpes franceses y perfeccionado en Cariñena, el French Tacos es una experiencia contundente: tortilla de trigo XXL tostada a la plancha, carne especiada, patatas doradas en su interior y nuestra célebre salsa de queso caliente.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Personalízalo con ternera marinada, pollo crujiente o mixto. Cada taco se prensa al momento para sellar todos los sabores en su interior.</p><!-- /wp:paragraph -->',
                'comment_status' => 'open',
            ]);
        }
    }
}

// Initialize starter content seeder
Kebab_Starter_Content::init();
