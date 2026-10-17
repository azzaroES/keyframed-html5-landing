<?php
/**
 * Instant Streaming vCard (.vcf) Generator
 *
 * Dynamically streams a standard vCard 3.0 file based on active NAP settings.
 * Enables users to save the business directly to iOS / Android contacts with one tap.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_VCard_Generator {

    public static function init() {
        add_action('init', [__CLASS__, 'handle_download_request']);
    }

    /**
     * Listen for ?download_vcard=1 or /download-vcard
     */
    public static function handle_download_request() {
        if (!isset($_GET['download_vcard']) && strpos($_SERVER['REQUEST_URI'] ?? '', 'download-vcard') === false) {
            return;
        }

        $nap = class_exists('Kebab_NAP_Engine') ? Kebab_NAP_Engine::get_settings() : [];

        $name = !empty($nap['name']) ? $nap['name'] : get_bloginfo('name');
        $legal_name = !empty($nap['legal_name']) ? $nap['legal_name'] : $name;
        $phone = !empty($nap['phone']) ? $nap['phone'] : '';
        $whatsapp = !empty($nap['whatsapp']) ? $nap['whatsapp'] : '';
        $email = !empty($nap['email']) ? $nap['email'] : get_bloginfo('admin_email');
        $street = !empty($nap['street_address']) ? $nap['street_address'] : '';
        $locality = !empty($nap['locality']) ? $nap['locality'] : '';
        $region = !empty($nap['region']) ? $nap['region'] : '';
        $postal_code = !empty($nap['postal_code']) ? $nap['postal_code'] : '';
        $country = !empty($nap['country']) ? $nap['country'] : 'ES';
        $lat = !empty($nap['geo_lat']) ? (float)$nap['geo_lat'] : 0.0;
        $lng = !empty($nap['geo_lng']) ? (float)$nap['geo_lng'] : 0.0;
        $url = home_url('/');
        $slogan = !empty($nap['slogan']) ? $nap['slogan'] : '';

        // Build vCard 3.0 formatted text
        $vcard = [];
        $vcard[] = 'BEGIN:VCARD';
        $vcard[] = 'VERSION:3.0';
        $vcard[] = 'FN:' . self::escape_vcard($name);
        $vcard[] = 'ORG:' . self::escape_vcard($legal_name);
        $vcard[] = 'TITLE:' . self::escape_vcard($slogan);
        if (!empty($phone)) {
            $vcard[] = 'TEL;TYPE=WORK,VOICE:' . self::escape_vcard($phone);
        }
        if (!empty($whatsapp)) {
            $vcard[] = 'TEL;TYPE=CELL,VOICE:' . self::escape_vcard($whatsapp);
        }
        if (!empty($email)) {
            $vcard[] = 'EMAIL;TYPE=PREF,INTERNET:' . self::escape_vcard($email);
        }
        $vcard[] = 'URL:' . self::escape_vcard($url);
        $vcard[] = 'ADR;TYPE=WORK:;;' . self::escape_vcard($street) . ';' . self::escape_vcard($locality) . ';' . self::escape_vcard($region) . ';' . self::escape_vcard($postal_code) . ';' . self::escape_vcard($country);
        if ($lat !== 0.0 && $lng !== 0.0) {
            $vcard[] = 'GEO:' . $lat . ';' . $lng;
        }
        $vcard[] = 'NOTE:' . self::escape_vcard(!empty($nap['description']) ? $nap['description'] : '');
        $vcard[] = 'REV:' . gmdate('Ymd\THis\Z');
        $vcard[] = 'END:VCARD';

        $content = implode("\r\n", $vcard);
        $filename = sanitize_file_name(strtolower($name) . '-contacto') . '.vcf';

        // Stream file download headers
        if (!headers_sent()) {
            header('Content-Type: text/vcard; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($content));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        echo $content;
        exit;
    }

    private static function escape_vcard($string) {
        $string = str_replace('\\', '\\\\', $string);
        $string = str_replace(',', '\,', $string);
        $string = str_replace(';', '\;', $string);
        $string = str_replace("\n", '\n', $string);
        $string = str_replace("\r", '', $string);
        return trim($string);
    }
}

// Hook vCard listener
Kebab_VCard_Generator::init();
