<?php
/**
 * Title: Interactive Menu & Dishes Grid
 * Slug: carinena-kebab-fse/section-menu
 * Categories: carinena-kebab-sections
 * Description: Interactive 3D reverse film scrubber highlighting signature dishes with editable prices and call-to-order buttons.
 */

$frames_base = get_template_directory_uri() . '/assets/frames/frame_';
$phone_order = do_shortcode('[business_nap field="phone_clean"]') ?: '+34876653461';
?>
<!-- wp:group {"tagName":"section","className":"film film--menu theme-night","metadata":{"name":"Interactive Menu"}} -->
<section class="film film--menu theme-night" id="carta" aria-labelledby="carta-title"
  data-frame-base="<?php echo esc_url($frames_base); ?>"
  data-frame-count="240"
  data-frame-pad="3"
  data-frame-ext=".webp"
  data-frame-reverse
  data-uncover>
  <div class="film__stage">
    <div class="film__media" aria-hidden="true">
      <canvas class="film__canvas"></canvas>
    </div>
    <header class="film__title section-head section-head--center container">
      <p class="eyebrow"><?php esc_html_e('La carta', 'carinena-kebab-fse'); ?></p>
      <h2 class="section-title" id="carta-title"><?php esc_html_e('Los favoritos de la casa', 'carinena-kebab-fse'); ?></h2>
    </header>
    <ul class="film__cast film__cast--menu" role="list">
      <li class="cast-card cast-card--right" data-cast-start="0.09" data-cast-end="0.22">
        <p class="cast-card__role"><?php esc_html_e('Para los más peques', 'carinena-kebab-fse'); ?></p>
        <h3 class="cast-card__name"><?php esc_html_e('Kids Menu', 'carinena-kebab-fse'); ?></h3>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Porciones más pequeñas con patatas doradas, bebida y juguete sorpresa.', 'carinena-kebab-fse'); ?></p>
        <div class="cast-card__buy">
          <p class="cast-card__price">6,50&nbsp;€</p>
          <a class="button button--small cast-card__order" href="tel:<?php echo esc_attr($phone_order); ?>"><?php esc_html_e('Pedir ahora', 'carinena-kebab-fse'); ?></a>
        </div>
      </li>
      <li class="cast-card cast-card--left" data-cast-start="0.28" data-cast-end="0.45">
        <p class="cast-card__role"><?php esc_html_e('Horno de piedra', 'carinena-kebab-fse'); ?></p>
        <h3 class="cast-card__name"><?php esc_html_e('Pizza Kebab Artesanal', 'carinena-kebab-fse'); ?></h3>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Masa fina crujiente, base de mozzarella fundida y virutas de kebab especiadas.', 'carinena-kebab-fse'); ?></p>
        <div class="cast-card__buy">
          <p class="cast-card__price">9,50&nbsp;€</p>
          <a class="button button--small cast-card__order" href="tel:<?php echo esc_attr($phone_order); ?>"><?php esc_html_e('Pedir ahora', 'carinena-kebab-fse'); ?></a>
        </div>
      </li>
      <li class="cast-card cast-card--right" data-cast-start="0.52" data-cast-end="0.70">
        <p class="cast-card__role"><?php esc_html_e('Plato estrella', 'carinena-kebab-fse'); ?></p>
        <h3 class="cast-card__name"><?php esc_html_e('Plato Kebab', 'carinena-kebab-fse'); ?></h3>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Virutas de carne dorada, patatas crujientes, ensalada fresca y salsas caseras.', 'carinena-kebab-fse'); ?></p>
        <div class="cast-card__buy">
          <p class="cast-card__price">8,50&nbsp;€</p>
          <a class="button button--small cast-card__order" href="tel:<?php echo esc_attr($phone_order); ?>"><?php esc_html_e('Pedir ahora', 'carinena-kebab-fse'); ?></a>
        </div>
      </li>
      <li class="cast-card cast-card--left" data-cast-start="0.75" data-cast-end="1">
        <p class="cast-card__role"><?php esc_html_e('El más pedido', 'carinena-kebab-fse'); ?></p>
        <h3 class="cast-card__name"><?php esc_html_e('Dürüm Kebab Especial', 'carinena-kebab-fse'); ?></h3>
        <p class="cast-card__note stroke-p"><?php esc_html_e('Rollo tostado con carne recién cortada, ensalada crujiente y doble salsa de la casa.', 'carinena-kebab-fse'); ?></p>
        <div class="cast-card__buy">
          <p class="cast-card__price">6,00&nbsp;€</p>
          <a class="button button--small cast-card__order" href="tel:<?php echo esc_attr($phone_order); ?>"><?php esc_html_e('Pedir ahora', 'carinena-kebab-fse'); ?></a>
        </div>
      </li>
    </ul>
  </div>
</section>
<!-- /wp:group -->
