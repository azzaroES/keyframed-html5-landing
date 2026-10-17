<?php
/**
 * Multi-Niche Google NAP Engine & Schema.org JSON-LD
 * 
 * Supports Restaurant, PhotographyStudio, ProfessionalService, Store, LocalBusiness.
 * Includes live schedule evaluation with client-side hydration for 100% LiteSpeed Cache compatibility.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_NAP_Engine {

    const OPTION_KEY = 'kebab_nap_settings';

    /**
     * Default NAP configuration (Defaults to Cariñena Kebab; easily reconfigured for any niche like Foto V-MAG)
     */
    public static function get_defaults() {
        return [
            'business_id'      => 'carinena-kebab-01',
            'niche'            => 'FastFoodRestaurant', // Options: FastFoodRestaurant, Restaurant, PhotographyStudio, ProfessionalService, Store, LocalBusiness
            'name'             => 'Cariñena Kebab Tacos & Pizzas',
            'legal_name'       => 'Cariñena Kebab S.L.',
            'slogan'           => 'Artesanía mediterránea & kebab gourmet en Cariñena',
            'description'      => 'Kebab gourmet de alta gastronomía, pizzas artesanales y tacos mediterráneos elaborados con ingredientes de proximidad en Cariñena.',
            'phone'            => '+34 976 62 00 00',
            'phone_clean'      => '+34976620000',
            'whatsapp'         => '+34 600 00 00 00',
            'whatsapp_clean'   => '34600000000',
            'email'            => 'contacto@carinenakebab.es',
            'street_address'   => 'Calle Mayor 42',
            'locality'         => 'Cariñena',
            'region'           => 'Zaragoza',
            'postal_code'      => '50400',
            'country'          => 'ES',
            'geo_lat'          => 41.3385,
            'geo_lng'          => -1.2225,
            'map_embed_url'    => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d-1.2225!3d41.3385!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDIwJzE4LjYiTiAxwrAxMycyMS4wIlc!5e0!3m2!1ses!2ses!4v1',
            'price_range'      => '€',
            'cui_cif'          => 'B-99887766',
            'reg_com'          => 'Reg. Mercantil de Zaragoza, Tomo 4521, Folio 88',
            'currencies'       => 'EUR',
            'serves_cuisine'   => ['Kebab', 'Pizzas', 'Tacos', 'Mediterranean'],
            'opening_hours'    => [
                'monday'    => ['open' => '12:00', 'close' => '23:30', 'closed' => false],
                'tuesday'   => ['open' => '12:00', 'close' => '23:30', 'closed' => false],
                'wednesday' => ['open' => '12:00', 'close' => '23:30', 'closed' => false],
                'thursday'  => ['open' => '12:00', 'close' => '23:30', 'closed' => false],
                'friday'    => ['open' => '12:00', 'close' => '00:30', 'closed' => false],
                'saturday'  => ['open' => '12:00', 'close' => '00:30', 'closed' => false],
                'sunday'    => ['open' => '12:00', 'close' => '23:30', 'closed' => false],
            ],
            'social_links'     => [
                'instagram' => 'https://instagram.com/carinenakebab',
                'facebook'  => 'https://facebook.com/carinenakebab',
                'tiktok'    => 'https://tiktok.com/@carinenakebab',
            ],
            'action_links'     => [
                'call'      => 'tel:+34976620000',
                'whatsapp'  => 'https://wa.me/34600000000?text=Hola,%20quisiera%20hacer%20un%20pedido',
                'directions'=> 'https://maps.google.com/?q=41.3385,-1.2225',
                'vcard'     => '/go/vcard',
            ],
        ];
    }

    /**
     * Retrieve active NAP configuration
     */
    public static function get_settings() {
        $saved = get_option(self::OPTION_KEY, []);
        $defaults = self::get_defaults();
        return wp_parse_args($saved, $defaults);
    }

    /**
     * Update NAP configuration
     */
    public static function update_settings($new_data) {
        $current = self::get_settings();
        $updated = array_merge($current, $new_data);
        update_option(self::OPTION_KEY, $updated);
        
        // Purge LiteSpeed Cache when business info is updated
        if (class_exists('LiteSpeed_Cache_API')) {
            LiteSpeed_Cache_API::purge_all();
        }
        return $updated;
    }

    /**
     * Generate Schema.org JSON-LD
     */
    public static function render_schema_json_ld() {
        $nap = self::get_settings();
        $site_url = home_url('/');

        $days_map = [
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
            'sunday'    => 'Sunday',
        ];

        $opening_spec = [];
        if (!empty($nap['opening_hours'])) {
            foreach ($nap['opening_hours'] as $day => $times) {
                if (!empty($times['closed'])) {
                    continue;
                }
                $opening_spec[] = [
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => $days_map[$day] ?? ucfirst($day),
                    'opens'     => $times['open'] ?? '12:00',
                    'closes'    => $times['close'] ?? '23:00',
                ];
            }
        }

        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => !empty($nap['niche']) ? sanitize_text_field($nap['niche']) : 'LocalBusiness',
            '@id'              => $site_url . '#business',
            'name'             => $nap['name'],
            'legalName'        => $nap['legal_name'],
            'description'      => $nap['description'],
            'url'              => $site_url,
            'telephone'        => $nap['phone'],
            'email'            => $nap['email'],
            'priceRange'       => $nap['price_range'],
            'address'          => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $nap['street_address'],
                'addressLocality'  => $nap['locality'],
                'addressRegion'    => $nap['region'],
                'postalCode'       => $nap['postal_code'],
                'addressCountry'   => $nap['country'],
            ],
            'geo'              => [
                '@type'     => 'GeoCoordinates',
                'latitude'  => (float) $nap['geo_lat'],
                'longitude' => (float) $nap['geo_lng'],
            ],
            'openingHoursSpecification' => $opening_spec,
            'sameAs'           => array_values(array_filter($nap['social_links'] ?? [])),
        ];

        if (!empty($nap['serves_cuisine']) && in_array($nap['niche'], ['Restaurant', 'FastFoodRestaurant'])) {
            $schema['servesCuisine'] = $nap['serves_cuisine'];
        }

        echo "\n<!-- Universal Google NAP & Schema.org LocalBusiness -->\n";
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    /**
     * Client-side hydrated working hours badge generator.
     * Generates HTML with data-schedule JSON so client JS calculates exact status
     * without breaking LiteSpeed Cache static page caching!
     */
    public static function get_schedule_badge_html() {
        $nap = self::get_settings();
        $schedule_json = esc_attr(wp_json_encode($nap['opening_hours']));

        return '<div class="kebab-live-status-pill" data-schedule="' . $schedule_json . '" role="status" aria-live="polite">
            <span class="status-indicator-dot" aria-hidden="true"></span>
            <span class="status-indicator-text">Verificando horario...</span>
        </div>';
    }

    /**
     * Shortcode handler: [business_nap field="phone|address|hours|badge|cui"]
     */
    public static function shortcode_handler($atts) {
        $a = shortcode_atts([
            'field' => 'name',
        ], $atts);

        $nap = self::get_settings();
        $field = strtolower(trim($a['field']));

        switch ($field) {
            case 'name':
                return esc_html($nap['name']);
            case 'phone':
                return '<a href="tel:' . esc_attr($nap['phone_clean']) . '" class="nap-link nap-phone">' . esc_html($nap['phone']) . '</a>';
            case 'whatsapp':
                return '<a href="https://wa.me/' . esc_attr($nap['whatsapp_clean']) . '" target="_blank" rel="noopener noreferrer" class="nap-link nap-whatsapp">' . esc_html($nap['whatsapp']) . '</a>';
            case 'email':
                return '<a href="mailto:' . esc_attr($nap['email']) . '" class="nap-link nap-email">' . esc_html($nap['email']) . '</a>';
            case 'address':
                return esc_html($nap['street_address'] . ', ' . $nap['postal_code'] . ' ' . $nap['locality'] . ' (' . $nap['region'] . ')');
            case 'badge':
                return self::get_schedule_badge_html();
            case 'cui':
            case 'cif':
                return esc_html($nap['cui_cif']);
            case 'reg_com':
                return esc_html($nap['reg_com']);
            case 'business_id':
                return esc_html($nap['business_id']);
            default:
                return isset($nap[$field]) ? esc_html(is_array($nap[$field]) ? implode(', ', $nap[$field]) : $nap[$field]) : '';
        }
    }
}

// Hook Schema.org into wp_head
add_action('wp_head', ['Kebab_NAP_Engine', 'render_schema_json_ld'], 5);

// Register shortcode
add_shortcode('business_nap', ['Kebab_NAP_Engine', 'shortcode_handler']);
add_shortcode('kebab_nap', ['Kebab_NAP_Engine', 'shortcode_handler']);
