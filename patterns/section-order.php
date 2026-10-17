<?php
/**
 * Title: Interactive Order Flow
 * Slug: carinena-kebab-fse/section-order
 * Categories: carinena-kebab-sections
 * Description: Quick calling and order action flow linked with active NAP phone numbers.
 */

$nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];
$phone = !empty($nap['phone']) ? $nap['phone'] : '876 65 34 61';
$phone_clean = !empty($nap['phone_clean']) ? $nap['phone_clean'] : '+34876653461';
$wa = !empty($nap['whatsapp']) ? $nap['whatsapp'] : '643 05 51 02';
$wa_clean = !empty($nap['whatsapp_clean']) ? $nap['whatsapp_clean'] : '34643055102';
?>
<!-- wp:group {"tagName":"section","className":"order theme-cream","metadata":{"name":"Order Section"}} -->
<section class="order theme-cream" id="pedir" aria-labelledby="order-title">
  <div class="container order__inner">
    <header class="section-head section-head--center" data-reveal data-from="left">
      <p class="eyebrow"><?php esc_html_e('Pedidos', 'carinena-kebab-fse'); ?></p>
      <h2 class="section-title" id="order-title"><?php esc_html_e('Pide ahora', 'carinena-kebab-fse'); ?></h2>
    </header>
    <p class="order__copy stroke-p stroke-p--center" data-reveal data-from="right" data-reveal-delay="1">
      <?php esc_html_e('Haz tu pedido para llevar o a domicilio. Carne asada a fuego lento y laminada al momento.', 'carinena-kebab-fse'); ?>
    </p>
    <div class="order__actions" data-reveal data-from="left" data-reveal-delay="2" data-flash>
      <a class="button" href="tel:<?php echo esc_attr($phone_clean); ?>">
        <?php printf(esc_html__('Fijo: %s', 'carinena-kebab-fse'), esc_html($phone)); ?>
      </a>
      <a class="button button--ghost" href="https://wa.me/<?php echo esc_attr($wa_clean); ?>?text=Hola,%20quisiera%20hacer%20un%20pedido">
        <?php printf(esc_html__('WhatsApp: %s', 'carinena-kebab-fse'), esc_html($wa)); ?>
      </a>
    </div>
    <p class="order__phone" data-reveal data-from="right" data-reveal-delay="3" data-flash>
      <a href="tel:<?php echo esc_attr($phone_clean); ?>"><?php echo esc_html($phone); ?></a> &middot; 
      <a href="https://wa.me/<?php echo esc_attr($wa_clean); ?>"><?php echo esc_html($wa); ?></a>
    </p>
  </div>
</section>
<!-- /wp:group -->
