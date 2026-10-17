<?php
/**
 * Title: 3D Keyframe Hero Scrubber
 * Slug: carinena-kebab-fse/hero-3d-scrubber
 * Categories: carinena-kebab-hero
 * Description: Interactive 240-frame 3D canvas scrubber with animated cast cards and Google Maps closing card.
 */

$frames_base = get_template_directory_uri() . '/assets/frames/frame_';
$logo_uri = get_template_directory_uri() . '/assets/logo/KEBAB CARINENA LOGO.svg';
?>
<!-- wp:group {"tagName":"section","className":"hero film theme-night","metadata":{"name":"3D Hero Scrubber"}} -->
<section class="hero film theme-night" id="top"
  data-frame-base="<?php echo esc_url($frames_base); ?>"
  data-frame-count="240"
  data-frame-pad="3"
  data-frame-ext=".webp">
  <div class="film__stage">
    <div class="film__media" aria-hidden="true">
      <canvas class="film__canvas"></canvas>
    </div>
    <div class="film__veil" aria-hidden="true"></div>
    <div class="film__title hero__content container">
      <p class="eyebrow hero__eyebrow"><?php esc_html_e('Cariñena Kebab presenta', 'carinena-kebab-fse'); ?></p>
      <h1 class="hero__title"><?php esc_html_e('El asador que gira frente a ti', 'carinena-kebab-fse'); ?></h1>
    </div>
    <ol class="film__cast" aria-label="<?php esc_attr_e('Reparto', 'carinena-kebab-fse'); ?>">
      <li class="cast-card cast-card--left" data-cast-start="0.08" data-cast-end="0.22">
        <p class="cast-card__role"><?php esc_html_e('Protagonizada por', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__name"><?php esc_html_e('Dürüm Especial', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Carne recién laminada del asador, enrollada en pan fino y tostada a la plancha.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="cast-card cast-card--right" data-cast-start="0.24" data-cast-end="0.38">
        <p class="cast-card__role"><?php esc_html_e('Con', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__name"><?php esc_html_e('Plato Kebab', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Virutas de carne dorada, patatas crujientes, ensalada fresca y salsa blanca casera.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="cast-card cast-card--left" data-cast-start="0.41" data-cast-end="0.55">
        <p class="cast-card__role"><?php esc_html_e('Y el favorito de la casa', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__name"><?php esc_html_e('Combo Menú Kebab', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Tu plato o dürüm con patatas crujientes y bebida helada para redondear el día.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="cast-card cast-card--left" data-cast-start="0.58" data-cast-end="0.72">
        <p class="cast-card__role"><?php esc_html_e('Recién salida del horno', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__name"><?php esc_html_e('Pizza Kebab Artesanal', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Masa fina crujiente, base de queso fundido y carne especiada horneada a la piedra.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="cast-card cast-card--right" data-cast-start="0.75" data-cast-end="0.88">
        <p class="cast-card__role"><?php esc_html_e('Para los más peques', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__name"><?php esc_html_e('Kids Menu', 'carinena-kebab-fse'); ?></p>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Porciones más pequeñas pensadas para niños, con patatas doradas, bebida y juguete sorpresa.', 'carinena-kebab-fse'); ?></p>
      </li>
      <li class="cast-card cast-card--center cast-card--closing" data-cast-start="0.91" data-cast-end="1">
        <a class="cast-card__logo" href="<?php echo esc_url(home_url('/go/maps')); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Cómo llegar a Cariñena Kebab (Google Maps)', 'carinena-kebab-fse'); ?>">
          <img src="<?php echo esc_url($logo_uri); ?>" alt="<?php esc_attr_e('Cariñena Kebab', 'carinena-kebab-fse'); ?>" width="180" height="202" loading="lazy" decoding="async">
        </a>
        <div class="cast-card__center-group">
          <p class="cast-card__name cast-card__name--caps"><?php esc_html_e('Kebab', 'carinena-kebab-fse'); ?></p>
          <address class="cast-card__note stroke-p stroke-p--center"><?php echo esc_html(do_shortcode('[business_nap field="address"]')); ?></address>
        </div>
        <div class="cast-card__meta-row">
          <span class="cast-card__role"><?php esc_html_e('Ubicados en Cariñena', 'carinena-kebab-fse'); ?></span>
          <span class="cast-card__meta-sep" aria-hidden="true">·</span>
          <a class="cast-card__directions" href="<?php echo esc_url(home_url('/go/maps')); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e('Cómo llegar a Cariñena Kebab en Google Maps', 'carinena-kebab-fse'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
              <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"></path>
              <circle cx="12" cy="10" r="2.4"></circle>
            </svg>
            <span><?php esc_html_e('Cómo llegar', 'carinena-kebab-fse'); ?></span>
          </a>
        </div>
      </li>
    </ol>
  </div>
</section>
<!-- /wp:group -->
