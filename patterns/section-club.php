<?php
/**
 * Title: VIP Club Pre-release
 * Slug: carinena-kebab-fse/section-club
 * Categories: carinena-kebab-sections
 * Description: VIP club signup pre-footer card with asynchronous email validation.
 */
?>
<!-- wp:group {"tagName":"section","className":"cta theme-night","metadata":{"name":"VIP Club Signup"}} -->
<section class="cta theme-night" id="club" aria-labelledby="cta-title">
  <div class="container cta__inner">
    <header class="section-head section-head--center">
      <p class="eyebrow"><?php esc_html_e('Preestreno', 'carinena-kebab-fse'); ?></p>
      <h2 class="section-title" id="cta-title"><?php esc_html_e('Entra en el Club Cariñena Kebab', 'carinena-kebab-fse'); ?></h2>
    </header>
    <p class="cta__copy stroke-p stroke-p--center">
      <?php esc_html_e('Ofertas, sabores nuevos y estrenos antes que nadie. Un correo de vez en cuando, nada más.', 'carinena-kebab-fse'); ?>
    </p>
    <div class="cta__signup">
      <form class="cta__form" id="cta-form">
        <div class="cta__row">
          <label class="sr-only" for="cta-email"><?php esc_html_e('Correo electrónico', 'carinena-kebab-fse'); ?></label>
          <input class="cta__input" id="cta-email" name="email" type="email" autocomplete="email" placeholder="tu@correo.com" aria-describedby="cta-error" required>
          <button class="button" type="submit"><?php esc_html_e('Quiero entrar', 'carinena-kebab-fse'); ?></button>
        </div>
        <p class="cta__error" id="cta-error" hidden><?php esc_html_e('Escribe un correo electrónico válido, por ejemplo nombre@correo.com.', 'carinena-kebab-fse'); ?></p>
      </form>
      <p class="cta__success" id="cta-success" role="status" tabindex="-1" hidden><?php esc_html_e('¡Ya estás dentro! Te escribiremos con el próximo estreno.', 'carinena-kebab-fse'); ?></p>
    </div>
  </div>
</section>
<!-- /wp:group -->
