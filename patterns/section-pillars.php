<?php
/**
 * Title: 3 Pillars Bento Grid
 * Slug: carinena-kebab-fse/section-pillars
 * Categories: carinena-kebab-sections
 * Description: The 3 core culinary pillars bento grid with custom SVG iconography and scroll reveals.
 */
?>
<!-- wp:group {"tagName":"section","className":"pillars theme-crust","metadata":{"name":"Pillars Section"}} -->
<section class="pillars theme-crust" id="craft" aria-labelledby="pillars-title">
  <div class="container">
    <header class="section-head section-head--center" data-reveal data-from="right">
      <p class="eyebrow"><?php esc_html_e('El método Cariñena Kebab', 'carinena-kebab-fse'); ?></p>
      <h2 class="section-title" id="pillars-title"><?php esc_html_e('Tres reglas, ninguna excepción', 'carinena-kebab-fse'); ?></h2>
    </header>
    <ul class="pillars__grid" role="list">
      <li class="pillar" data-reveal data-from="left">
        <svg class="pillar__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
          <ellipse cx="30" cy="18" rx="12" ry="9.5" transform="rotate(-45 30 18)"></ellipse>
          <path d="M21.5 26.5 12 36"></path>
          <circle cx="9.5" cy="35" r="2.5"></circle>
          <circle cx="13" cy="38.5" r="2.5"></circle>
        </svg>
        <h3 class="pillar__title"><?php esc_html_e('Carne marinada', 'carinena-kebab-fse'); ?></h3>
        <p class="pillar__copy stroke-p stroke-p--center"><?php esc_html_e('Piezas seleccionadas, marinadas 24 horas con especias tradicionales y montadas a mano.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="pillar" data-reveal data-from="up" data-reveal-delay="1">
        <svg class="pillar__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
          <path d="M8.5 26h31"></path>
          <path d="M10.5 26a13.5 13.5 0 0 0 27 0"></path>
          <path d="M30.5 7 24 26"></path>
          <path d="M27.8 6.2l5.4 1.6"></path>
        </svg>
        <h3 class="pillar__title"><?php esc_html_e('Corte maestro', 'carinena-kebab-fse'); ?></h3>
        <p class="pillar__copy stroke-p stroke-p--center"><?php esc_html_e('Asada a fuego lento en vertical y cortada en finas virutas crujientes al momento de servir.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="pillar" data-reveal data-from="right" data-reveal-delay="2">
        <svg class="pillar__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
          <path d="M24 43c-6.9 0-11.5-4.7-11.5-11 0-5.4 3.4-9 5.9-12.1.5 3.3 2 5.4 4.1 6.3-.8-6.2 2-13 7.2-17.7-.3 5.3 2.3 8.7 4.9 12.2 1.9 2.6 3.4 5.9 3.4 10.3 0 7.3-4.7 12-14 12Z"></path>
          <path d="M24 43c-3.1 0-5.2-2.1-5.2-5 0-3 2.2-4.9 3.8-7.3.9 2.5 2.7 3.4 4.1 4.8 1.5 1.4 2.5 2.8 2.5 4.2 0 2.1-2.1 3.3-5.2 3.3Z"></path>
        </svg>
        <h3 class="pillar__title"><?php esc_html_e('Fuego vivo', 'carinena-kebab-fse'); ?></h3>
        <p class="pillar__copy stroke-p stroke-p--center"><?php esc_html_e('El calor exacto para dorar la superficie sin secar el interior. Textura tierna por dentro, crujiente por fuera.', 'carinena-kebab-fse'); ?></p>
      </li>
    </ul>
  </div>
</section>
<!-- /wp:group -->
