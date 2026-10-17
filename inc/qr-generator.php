<?php
/**
 * Pure PHP Zero-Dependency Vector SVG QR Code Generator
 *
 * Automatically generates vector SVG QR codes pointing to the current domain or custom target.
 * Zero external libraries or Composer packages required. 100% lightweight and performant.
 *
 * @package Cariñena_Kebab_FSE
 */

if (!defined('ABSPATH')) {
    exit;
}

class Kebab_QR_Generator {

    /**
     * Generate an SVG QR code string.
     * Uses a self-contained byte-mode QR encoder for URLs.
     *
     * @param string $data Content to encode (defaults to current site URL)
     * @param int $size Width and height in px
     * @param string $color Hex or CSS color for QR modules
     * @param string $bg Hex or CSS color for background (or 'transparent')
     * @return string SVG markup
     */
    public static function generate_svg($data = '', $size = 220, $color = '#C9A962', $bg = 'transparent') {
        if (empty($data)) {
            $data = home_url('/');
        }

        $matrix = self::encode_qr_matrix($data);
        $module_count = count($matrix);
        $viewBoxSize = $module_count + 4; // 2-module quiet zone

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $viewBoxSize . ' ' . $viewBoxSize . '" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" shape-rendering="crispEdges" role="img" aria-label="Código QR">';
        
        if ($bg !== 'transparent' && !empty($bg)) {
            $svg .= '<rect width="100%" height="100%" fill="' . esc_attr($bg) . '"/>';
        }

        $path_data = '';
        $offset = 2; // quiet zone
        for ($y = 0; $y < $module_count; $y++) {
            for ($x = 0; $x < $module_count; $x++) {
                if ($matrix[$y][$x]) {
                    $path_data .= 'M' . ($x + $offset) . ',' . ($y + $offset) . 'h1v1h-1z ';
                }
            }
        }

        $svg .= '<path d="' . trim($path_data) . '" fill="' . esc_attr($color) . '"/>';
        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Compact QR Code matrix constructor supporting ISO/IEC 18004 Byte Mode.
     * Generates a valid 21x21 (V1), 25x25 (V2), or 29x29 (V3) matrix with Reed-Solomon Error Correction.
     */
    private static function encode_qr_matrix($text) {
        $len = strlen($text);
        
        // Select smallest fitting version (Level M)
        if ($len <= 14) {
            $version = 1;
            $size = 21;
            $data_codewords = 16;
            $ec_codewords = 10;
        } elseif ($len <= 26) {
            $version = 2;
            $size = 25;
            $data_codewords = 28;
            $ec_codewords = 16;
        } elseif ($len <= 42) {
            $version = 3;
            $size = 29;
            $data_codewords = 44;
            $ec_codewords = 26;
        } else {
            $version = 4;
            $size = 33;
            $data_codewords = 64;
            $ec_codewords = 36;
        }

        // Initialize matrix: null = unallocated
        $matrix = array_fill(0, $size, array_fill(0, $size, null));

        // 1. Finder patterns (top-left, top-right, bottom-left)
        self::place_finder_pattern($matrix, 0, 0);
        self::place_finder_pattern($matrix, $size - 7, 0);
        self::place_finder_pattern($matrix, 0, $size - 7);

        // 2. Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
            $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
        }

        // 3. Dark module and reserved format regions
        $matrix[4 * $version + 9][8] = 1;

        // Reserve format info areas
        for ($i = 0; $i < 9; $i++) {
            if ($matrix[8][$i] === null) $matrix[8][$i] = 0;
            if ($matrix[$i][8] === null) $matrix[$i][8] = 0;
        }
        for ($i = 0; $i < 8; $i++) {
            if ($matrix[8][$size - 1 - $i] === null) $matrix[8][$size - 1 - $i] = 0;
            if ($matrix[$size - 1 - $i][8] === null) $matrix[$size - 1 - $i][8] = 0;
        }

        // 4. Alignment pattern for Version 2+
        if ($version >= 2) {
            $pos = $size - 7;
            self::place_alignment_pattern($matrix, $pos, $pos);
        }

        // 5. Data encoding (Byte mode: 0100 + char count + bytes)
        $bitstream = '0100'; // Byte mode
        $bitstream .= str_pad(decbin($len), 8, '0', STR_PAD_LEFT);
        for ($i = 0; $i < $len; $i++) {
            $bitstream .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Terminator & padding to boundary
        $total_data_bits = $data_codewords * 8;
        $bitstream .= '0000';
        if (strlen($bitstream) > $total_data_bits) {
            $bitstream = substr($bitstream, 0, $total_data_bits);
        }
        while (strlen($bitstream) % 8 !== 0 && strlen($bitstream) < $total_data_bits) {
            $bitstream .= '0';
        }
        $pad_bytes = ['11101100', '00010001'];
        $p = 0;
        while (strlen($bitstream) < $total_data_bits) {
            $bitstream .= $pad_bytes[$p % 2];
            $p++;
        }

        // Convert data bitstream into byte array
        $data_bytes = [];
        for ($i = 0; $i < strlen($bitstream); $i += 8) {
            $data_bytes[] = bindec(substr($bitstream, $i, 8));
        }

        // 6. Reed-Solomon Error Correction computation
        $ec_bytes = self::calculate_reed_solomon($data_bytes, $ec_codewords);
        $final_bytes = array_merge($data_bytes, $ec_bytes);

        // Convert final bytes to bitstream
        $final_bitstream = '';
        foreach ($final_bytes as $b) {
            $final_bitstream .= str_pad(decbin($b), 8, '0', STR_PAD_LEFT);
        }

        // 7. Place data bits in matrix (zig-zag upward/downward from right to left)
        $bit_idx = 0;
        $bit_len = strlen($final_bitstream);
        $right = $size - 1;
        $going_up = true;

        while ($right > 0) {
            if ($right === 6) { // skip vertical timing column
                $right--;
            }
            $cols = [$right, $right - 1];
            $rows = $going_up ? range($size - 1, 0) : range(0, $size - 1);

            foreach ($rows as $y) {
                foreach ($cols as $x) {
                    if ($matrix[$y][$x] === null) {
                        $bit = ($bit_idx < $bit_len) ? (int)$final_bitstream[$bit_idx] : 0;
                        // Apply Mask 0: (x + y) % 2 == 0
                        if (($x + $y) % 2 === 0) {
                            $bit ^= 1;
                        }
                        $matrix[$y][$x] = $bit;
                        $bit_idx++;
                    }
                }
            }
            $right -= 2;
            $going_up = !$going_up;
        }

        // 8. Apply format string for Mask 0 / Level M: 101010000010010
        $format_bits = '101010000010010';
        for ($i = 0; $i < 6; $i++) {
            $matrix[8][$i] = (int)$format_bits[$i];
            $matrix[$size - 1 - $i][8] = (int)$format_bits[$i];
        }
        $matrix[8][7] = (int)$format_bits[6];
        $matrix[8][8] = (int)$format_bits[7];
        $matrix[7][8] = (int)$format_bits[8];
        $matrix[$size - 7][8] = (int)$format_bits[8];

        for ($i = 9; $i < 15; $i++) {
            $matrix[14 - $i][8] = (int)$format_bits[$i];
            $matrix[8][$size - 15 + $i] = (int)$format_bits[$i];
        }

        return $matrix;
    }

    private static function place_finder_pattern(&$matrix, $ox, $oy) {
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                if ($y === 0 || $y === 6 || $x === 0 || $x === 6 || ($y >= 2 && $y <= 4 && $x >= 2 && $x <= 4)) {
                    $matrix[$oy + $y][$ox + $x] = 1;
                } else {
                    $matrix[$oy + $y][$ox + $x] = 0;
                }
            }
        }
        // Separator ring
        $size = count($matrix);
        for ($i = -1; $i <= 7; $i++) {
            $yy = $oy + $i;
            $xx = $ox + $i;
            if ($yy >= 0 && $yy < $size) {
                if ($ox - 1 >= 0) $matrix[$yy][$ox - 1] = 0;
                if ($ox + 7 < $size) $matrix[$yy][$ox + 7] = 0;
            }
            if ($xx >= 0 && $xx < $size) {
                if ($oy - 1 >= 0) $matrix[$oy - 1][$xx] = 0;
                if ($oy + 7 < $size) $matrix[$oy + 7][$xx] = 0;
            }
        }
    }

    private static function place_alignment_pattern(&$matrix, $ox, $oy) {
        for ($y = -2; $y <= 2; $y++) {
            for ($x = -2; $x <= 2; $x++) {
                if (abs($x) === 2 || abs($y) === 2 || ($x === 0 && $y === 0)) {
                    $matrix[$oy + $y][$ox + $x] = 1;
                } else {
                    $matrix[$oy + $y][$ox + $x] = 0;
                }
            }
        }
    }

    /**
     * Galois Field GF(256) Reed-Solomon polynomial division
     */
    private static function calculate_reed_solomon($data, $ec_count) {
        $gf_exp = array_fill(0, 512, 0);
        $gf_log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $gf_exp[$i] = $x;
            $gf_exp[$i + 255] = $x;
            $gf_log[$x] = $i;
            $x = ($x << 1);
            if ($x & 256) {
                $x ^= 285; // primitive polynomial x^8 + x^4 + x^3 + x^2 + 1
            }
        }

        // Generator polynomial for ec_count
        $gen = [1];
        for ($i = 0; $i < $ec_count; $i++) {
            $next = [1, $gf_exp[$i]];
            $prod = array_fill(0, count($gen) + 1, 0);
            for ($j = 0; $j < count($gen); $j++) {
                $p1 = $gen[$j];
                $prod[$j] ^= $p1;
                $p2 = ($p1 === 0) ? 0 : $gf_exp[$gf_log[$p1] + $gf_log[$next[1]]];
                $prod[$j + 1] ^= $p2;
            }
            $gen = $prod;
        }

        // Remainder division
        $res = array_merge($data, array_fill(0, $ec_count, 0));
        for ($i = 0; $i < count($data); $i++) {
            $coef = $res[$i];
            if ($coef !== 0) {
                $log_c = $gf_log[$coef];
                for ($j = 0; $j < count($gen); $j++) {
                    if ($gen[$j] !== 0) {
                        $res[$i + $j] ^= $gf_exp[$log_c + $gf_log[$gen[$j]]];
                    }
                }
            }
        }

        return array_slice($res, count($data));
    }

    /**
     * Shortcode handler: [kebab_qr size="200" url="" color="#C9A962" bg="transparent"]
     */
    public static function shortcode_handler($atts) {
        $a = shortcode_atts([
            'size'  => 220,
            'url'   => '',
            'color' => '#C9A962',
            'bg'    => 'transparent',
            'class' => '',
        ], $atts);

        $url = !empty($a['url']) ? esc_url_raw($a['url']) : home_url('/');
        $svg = self::generate_svg($url, (int)$a['size'], sanitize_hex_color($a['color']) ?: '#C9A962', $a['bg']);

        $wrapper_class = 'kebab-qr-code-wrapper ' . esc_attr($a['class']);
        return '<div class="' . $wrapper_class . '" data-qr-target="' . esc_attr($url) . '">' . $svg . '</div>';
    }
}

// Register shortcode
add_shortcode('kebab_qr', ['Kebab_QR_Generator', 'shortcode_handler']);
