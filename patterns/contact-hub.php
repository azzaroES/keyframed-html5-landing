<?php
/**
 * Title: Contact Hub (Cariñena Kebab Multi-Niche)
 * Slug: carinena-kebab-fse/contact-hub
 * Categories: carinena-kebab-contact
 * Description: Complete multi-niche contact hub with Business ID binding, vCard streaming, 4 action cards, live hours badge, map, and fiscal trust footer.
 */

$nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];
$name = !empty($nap['name']) ? $nap['name'] : 'Cariñena Kebab Tacos & Pizzas';
$phone = !empty($nap['phone']) ? $nap['phone'] : '+34 876 65 34 61';
$phone_clean = !empty($nap['phone_clean']) ? $nap['phone_clean'] : '+34876653461';
$whatsapp = !empty($nap['whatsapp']) ? $nap['whatsapp'] : '+34 643 05 51 02';
$whatsapp_clean = !empty($nap['whatsapp_clean']) ? $nap['whatsapp_clean'] : '34643055102';
$email = !empty($nap['email']) ? $nap['email'] : 'pedidos@carinenakebab.es';
$address = !empty($nap['street_address']) ? $nap['street_address'] . ', ' . $nap['postal_code'] . ' ' . $nap['locality'] . ' (' . $nap['region'] . ')' : 'Av. del Ejército, 28, 50400 Cariñena (Zaragoza)';
$map_url = !empty($nap['map_embed_url']) ? $nap['map_embed_url'] : 'https://maps.google.com/maps?q=Av.+del+Ej%C3%A9rcito,+28,+50400+Cari%C3%B1ena,+Zaragoza&t=&z=16&ie=UTF8&iwloc=&output=embed';
$cui = !empty($nap['cui_cif']) ? $nap['cui_cif'] : 'B-50987654';
$reg_com = !empty($nap['reg_com']) ? $nap['reg_com'] : 'Registro Mercantil de Zaragoza';
$business_id = !empty($nap['business_id']) ? $nap['business_id'] : 'carinena-kebab-01';
$vcard_url = home_url('/go/vcard');
?>
<!-- wp:html -->
<section class="kebab-contact-hub" data-business-id="<?php echo esc_attr($business_id); ?>">
  <!-- Hero Section -->
  <header class="kebab-contact-hero">
    <div class="kebab-contact-kicker">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
      <span><?php esc_html_e('Contáctanos', 'carinena-kebab-fse'); ?></span>
    </div>
    <h1 class="kebab-contact-title"><?php esc_html_e('Estamos a tu disposición', 'carinena-kebab-fse'); ?></h1>
    <p class="kebab-contact-sub"><?php esc_html_e('Elige tu canal preferido de comunicación. ¡Te responderemos al instante con todos los detalles!', 'carinena-kebab-fse'); ?></p>
    
    <a class="kebab-vcard-btn" href="<?php echo esc_url($vcard_url); ?>" aria-label="<?php esc_attr_e('Guardar nuestro contacto en tu agenda (vCard)', 'carinena-kebab-fse'); ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"></path>
        <circle cx="12" cy="10" r="3"></circle>
        <path d="M7 18c0-2.5 2.5-4 5-4s5 1.5 5 4"></path>
      </svg>
      <span><?php esc_html_e('Guardar contacto en la agenda', 'carinena-kebab-fse'); ?></span>
    </a>
  </header>

  <!-- 4 Action Cards Grid -->
  <div class="kebab-contact-cards-grid">
    <!-- Card 1: Phone -->
    <div class="kebab-contact-card">
      <div>
        <div class="kebab-card-header">
          <div class="kebab-card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
          </div>
          <h2 class="kebab-card-title"><?php esc_html_e('Teléfono Fijo', 'carinena-kebab-fse'); ?></h2>
        </div>
        <p class="kebab-card-desc"><?php echo esc_html($phone); ?></p>
      </div>
      <a class="kebab-card-cta kebab-card-cta--primary" href="tel:<?php echo esc_attr($phone_clean); ?>">
        <?php esc_html_e('Llamar ahora', 'carinena-kebab-fse'); ?>
      </a>
    </div>

    <!-- Card 2: WhatsApp -->
    <div class="kebab-contact-card">
      <div>
        <div class="kebab-card-header">
          <div class="kebab-card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            </svg>
          </div>
          <h2 class="kebab-card-title"><?php esc_html_e('WhatsApp & Móvil', 'carinena-kebab-fse'); ?></h2>
        </div>
        <p class="kebab-card-desc"><?php esc_html_e('Respondemos al momento y tomamos pedidos para llevar y a domicilio.', 'carinena-kebab-fse'); ?></p>
      </div>
      <a class="kebab-card-cta kebab-card-cta--primary" href="https://wa.me/<?php echo esc_attr($whatsapp_clean); ?>?text=Hola%20Cari%C3%B1ena%20Kebab%2C%20quisiera%20hacer%20un%20pedido" target="_blank" rel="noopener noreferrer">
        <?php esc_html_e('Escribir por WhatsApp', 'carinena-kebab-fse'); ?>
      </a>
    </div>

    <!-- Card 3: Email -->
    <div class="kebab-contact-card">
      <div>
        <div class="kebab-card-header">
          <div class="kebab-card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
          <h2 class="kebab-card-title"><?php esc_html_e('Correo Electrónico', 'carinena-kebab-fse'); ?></h2>
        </div>
        <p class="kebab-card-desc"><?php echo esc_html($email); ?></p>
      </div>
      <a class="kebab-card-cta kebab-card-cta--outline" href="mailto:<?php echo esc_attr($email); ?>">
        <?php esc_html_e('Enviar Email', 'carinena-kebab-fse'); ?>
      </a>
    </div>

    <!-- Card 4: Address / Navigation -->
    <div class="kebab-contact-card">
      <div>
        <div class="kebab-card-header">
          <div class="kebab-card-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"></path>
              <circle cx="12" cy="10" r="2.5"></circle>
            </svg>
          </div>
          <h2 class="kebab-card-title"><?php esc_html_e('Dirección y Cómo Llegar', 'carinena-kebab-fse'); ?></h2>
        </div>
        <p class="kebab-card-desc"><?php echo esc_html($address); ?></p>
      </div>
      <a class="kebab-card-cta kebab-card-cta--outline" href="<?php echo esc_url(home_url('/go/maps')); ?>" target="_blank" rel="noopener noreferrer">
        <?php esc_html_e('Abrir en Google Maps', 'carinena-kebab-fse'); ?>
      </a>
    </div>
  </div>

  <!-- Schedule & Map Split -->
  <div class="kebab-contact-split">
    <!-- Schedule Box with Live Hydrated Status Pill -->
    <div class="kebab-schedule-box">
      <div class="kebab-schedule-header">
        <h2 class="kebab-schedule-title"><?php esc_html_e('Horario de Apertura', 'carinena-kebab-fse'); ?></h2>
        <?php echo Kebab_NAP_Engine::get_schedule_badge_html(); ?>
      </div>
      <ul class="kebab-hours-list">
        <?php
        $days_labels = [
            'monday'    => __('Lunes', 'carinena-kebab-fse'),
            'tuesday'   => __('Martes', 'carinena-kebab-fse'),
            'wednesday' => __('Miércoles', 'carinena-kebab-fse'),
            'thursday'  => __('Jueves', 'carinena-kebab-fse'),
            'friday'    => __('Viernes', 'carinena-kebab-fse'),
            'saturday'  => __('Sábado', 'carinena-kebab-fse'),
            'sunday'    => __('Domingo', 'carinena-kebab-fse'),
        ];
        if (!empty($nap['opening_hours'])) {
            foreach ($nap['opening_hours'] as $day_key => $hours) {
                $label = $days_labels[$day_key] ?? ucfirst($day_key);
                $time_str = !empty($hours['closed']) ? __('Cerrado', 'carinena-kebab-fse') : ($hours['open'] . ' - ' . $hours['close']);
                echo '<li class="kebab-hours-item">';
                echo '<span class="kebab-hours-day">' . esc_html($label) . '</span>';
                echo '<span class="kebab-hours-time">' . esc_html($time_str) . '</span>';
                echo '</li>';
            }
        }
        ?>
      </ul>
    </div>

    <!-- Google Map Box -->
    <div class="kebab-map-box">
      <h2 class="kebab-schedule-title"><?php esc_html_e('Ubicación en el Mapa', 'carinena-kebab-fse'); ?></h2>
      <iframe class="kebab-map-frame" src="<?php echo esc_url($map_url); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e('Ubicación en Google Maps', 'carinena-kebab-fse'); ?>"></iframe>
    </div>
  </div>

  <!-- Fiscal Verification & QR Code Footer -->
  <div class="kebab-contact-trust-footer">
    <div class="kebab-trust-info">
      <h3 class="kebab-trust-heading"><?php echo esc_html($nap['legal_name'] ?? $name); ?></h3>
      <p class="kebab-trust-meta">
        <strong>CIF / NIF:</strong> <?php echo esc_html($cui); ?><br>
        <strong>Registro:</strong> <?php echo esc_html($reg_com); ?><br>
        <strong>Domicilio Social:</strong> <?php echo esc_html($address); ?>
      </p>
    </div>
    <div class="kebab-qr-container">
      <?php echo Kebab_QR_Generator::generate_svg(home_url('/contact/'), 110, '#C9A962'); ?>
      <span class="kebab-qr-label"><?php esc_html_e('Escanea para guardar contacto', 'carinena-kebab-fse'); ?></span>
    </div>
  </div>
</section>
<!-- /wp:html -->
