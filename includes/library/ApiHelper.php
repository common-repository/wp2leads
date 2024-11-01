<?php
/**
 * Created by PhpStorm.
 * User: oleksii.khodakivskyi
 * Date: 30.07.18
 * Time: 23:19
 */

class ApiHelper {

    private static $minutes_number = 60;

    private static $hours_number = 24;

    private static $days_number = 31;

    private static $unique_subscribers_option_name = 'wp2l_unique_subscribers_counter';

    private static $default_api_fields = array(
        'api_email', 'api_fieldFirstName', 'api_fieldLastName', 'api_fieldCompanyName', 'api_fieldStreet1',
        'api_fieldStreet2', 'api_fieldCity', 'api_fieldState', 'api_fieldZip', 'api_fieldCountry', 'api_fieldPrivatePhone',
        'api_fieldMobilePhone', 'api_fieldPhone', 'api_fieldFax', 'api_fieldWebsite', 'api_fieldBirthday', 'api_fieldLeadValue'
    );

    private static $default_api_fields_types = array(
        'api_email' => 'text',
        'api_fieldFirstName' => 'text',
        'api_fieldLastName' => 'text',
        'api_fieldCompanyName' => 'text',
        'api_fieldStreet1' => 'text',
        'api_fieldStreet2' => 'text',
        'api_fieldCity' => 'text',
        'api_fieldState' => 'text',
        'api_fieldZip' => 'text',
        'api_fieldCountry' => 'text',
        'api_fieldPrivatePhone' => 'text',
        'api_fieldMobilePhone' => 'text',
        'api_fieldPhone' => 'text',
        'api_fieldFax' => 'text',
        'api_fieldWebsite' => 'url',
        'api_fieldBirthday' => 'date',
        'api_fieldLeadValue' => 'number'
    );

    public static function get_paths($results, $decodedMap) {
        $paths = array();

        foreach ($results as $index => $result) {
            $result = (array) $result;

            foreach ($result as $table_column => $value) {
                if (false/*strpos($table_column, '(concatenated)') !== false*/) {
                    $parts = explode(', ', $value);

                    foreach ($parts as $part) {
                        $part = json_encode(trim($part), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $part = str_replace("\n", " ", str_replace('"', "", $part));
                        $paths[] = $table_column . ' (' . $part . ')';
                    }
                } else {
                    $value = json_encode(trim($value), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $value = str_replace("\n", " ", str_replace('"', "", $value));
                    $paths[] = $table_column . ' (' . $value . ')';
                }
            }
        }

        return $paths;
    }

    public static function get_options_paths($result, $decodedMap) {
        foreach ($result as $table_column => $value) {
            $value = apply_filters("wp2leads_output_{$table_column}_value", $value, $table_column, $decodedMap);
            if (false/*strpos($table_column, '(concatenated)') !== false*/) {
                $parts = explode(', ', $value);

                foreach ($parts as $part) {
                    $part = json_encode(trim($part), JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $part = str_replace("\n", " ", str_replace('"', "", $part));
                    $paths[] = $table_column . ' (' . $part . ')';
                }
            } else {
                $paths[] = $table_column . ' (' . ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(ApiHelper::maybe_json_decode($value)))) . ')';
            }
        }

        return $paths;
    }

    public static function date_to_timestamp($date_string) {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $date_string);

        if ($date) {
            return date_timestamp_get($date);
        }

        $date = DateTime::createFromFormat('Y-m-d', $date_string);

        if ($date) {
            return date_timestamp_get($date);
        }

        $date = DateTime::createFromFormat( 'U', (int) $date_string );

        if ($date) {
            return date_timestamp_get($date);
        }

        return false;
    }

    public static function arrayToString ($array, $glue = '.') {
        $paths = array();
        $index = 0;

        foreach ($array as $key => &$mixed) {
            if (is_array($mixed)) {
                $results = ApiHelper::arrayToString($mixed, $glue);

                foreach ($results as $k => &$v) {
                    $prep = '';

                    if (is_string($k) && is_bool($v)) {
                        if ($v) {
                            $v = $k;
                        } else {
                            $v = '';
                        }
                    } else {
                        if ( is_string( $k ) ) {
                            $prep = $k . ':';
                        }

                        if ( ! is_string( $v ) && ! is_int( $v ) && ! is_float( $v ) && ! is_bool( $v ) ) {
                            $v = '';
                        }
                    }

                    $value_to_check = $prep . (string) $v;

                    if (!empty($value_to_check)) {
                        $paths[$index] = $value_to_check;
                        $index++;
                    }
                }
                unset($results);
            } else {
                $prep = '';

                if (is_string($key) && is_bool($mixed)) {
                    if ($mixed) {
                        $mixed = $key;
                    } else {
                        $mixed = '';
                    }
                } else {
                    if (is_string($key)) {
                        $prep = $key . ':';
                    }

                    if (!is_string($mixed) && !is_int($mixed) && !is_float($mixed) && !is_bool($mixed)) {
                        $mixed = '';
                    }
                }

                $value_to_check = $prep . (string) $mixed;

                if (!empty($value_to_check)) {
                    $paths[$index] = $value_to_check;
                    $index++;
                }
            }

        }

        return $paths;
    }

    public static function filterForbidenKTSymbols($value) {
        // $value = self::remove_wp_emoji($value);
        // $value = ApiHelper::maybe_json_decode($value);
        $value = strip_tags($value);
        $value = strip_shortcodes($value);
        $value = trim($value);
        $value = str_replace('&#167;', '', $value);
        $value = str_replace('&#xa7;', '', $value);
        $value = str_replace('&sect;', '', $value);
        $value = str_replace('§', '', $value);
        $value = str_replace('&#38;', '+', $value);
        $value = str_replace('&amp;', '+', $value);
        $value = str_replace('&', '+', $value);
        $value = str_replace('`', '', $value);
        $value = str_replace('<', '', $value);
        $value = str_replace('>', '', $value);
        $value = str_replace('"', '', $value);
        $value = str_replace('\'', '', $value);

        return $value;
    }

    public static function filterBeforeOutput($value) {
        $value = trim($value);
        $value = ApiHelper::remove_accents( $value );
        $special_chars = array("\"", "\\", "'", "{", "}");
        $value = str_replace( $special_chars, '', $value );
        $value = json_encode($value, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $value = str_replace("\n", " ", str_replace('"', "", $value));

        return $value;
    }

    public static function maybe_json_decode ($value) {
        $value_array = [];

        if (!empty($value)) {
            $value_array = json_decode($value, true);
        }

        $last_error = json_last_error();

        if (!$last_error == JSON_ERROR_NONE) {
            $value_array = maybe_unserialize( $value );

            if (is_object($value_array)) {
                $value_array = json_decode(json_encode($value_array), true);
            }
        }

        if (is_array($value_array)) {
            $value = ApiHelper::arrayToStringAll($value_array);
            $value = implode('::', $value);
        }

        return $value;
    }

    public static function arrayToStringAll ($array, $glue = '.') {
        $paths = array();
        $index = 0;

        foreach ($array as $key => &$mixed) {
            if (is_array($mixed)) {
                $results = ApiHelper::arrayToStringAll($mixed, $glue);

                foreach ($results as $k => &$v) {
                    $prep = '';

                    if (is_string($k) && is_bool($v)) {
                        if ($v) {
                            $v = $k;
                        } else {
                            $v = '';
                        }
                    } else {
                        if ( is_string( $k ) ) {
                            $prep = $k . ':';
                        }

                        if ( ! is_string( $v ) && ! is_int( $v ) && ! is_float( $v ) && ! is_bool( $v ) ) {
                            $v = '';
                        }
                    }

                    $value_to_check = $prep . (string) $v;

                    if (!empty($value_to_check)) {
                        $paths[$index] = $value_to_check;
                        $index++;
                    }
                }
                unset($results);
            } else {
                $prep = '';

                if (is_string($key) && is_bool($mixed)) {
                    if ($mixed) {
                        $mixed = $key;
                    } else {
                        $mixed = '';
                    }
                } else {
                    if (is_string($key)) {
                        $prep = $key . ':';
                    }

                    if (!is_string($mixed) && !is_int($mixed) && !is_float($mixed) && !is_bool($mixed)) {
                        $mixed = '';
                    }
                }

                $value_to_check = $prep . (string) $mixed;

                if (!empty($value_to_check)) {
                    $paths[$index] = $value_to_check;
                    $index++;
                }
            }

        }

        return $paths;
    }

    public static function remove_wp_emoji( $content ) {
        $emoji  = self::_emoji_list();
        $compat = version_compare( phpversion(), '5.4', '<' );

        foreach ( $emoji as $emojum ) {
            if ( $compat ) {
                $emoji_char = html_entity_decode( $emojum, ENT_COMPAT, 'UTF-8' );
            } else {
                $emoji_char = html_entity_decode( $emojum );
            }
            if ( false !== strpos( $content, $emoji_char ) ) {
                $content = preg_replace( "/$emoji_char/", '', $content );
            }
        }

        return $content;
    }

    public static function remove_accents( $string ) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        if (!seems_utf8($string)) {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e"
                           ."\x9f\xa2\xa5\xb5\xc0\xc1\xc2"
                           ."\xc3\xc4\xc5\xc7\xc8\xc9\xca"
                           ."\xcb\xcc\xcd\xce\xcf\xd1\xd2"
                           ."\xd3\xd4\xd5\xd6\xd8\xd9\xda"
                           ."\xdb\xdc\xdd\xe0\xe1\xe2\xe3"
                           ."\xe4\xe5\xe7\xe8\xe9\xea\xeb"
                           ."\xec\xed\xee\xef\xf1\xf2\xf3"
                           ."\xf4\xf5\xf6\xf8\xf9\xfa\xfb"
                           ."\xfc\xfd\xff";

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array("\x8c", "\x9c", "\xc6", "\xd0", "\xde", "\xdf", "\xe6", "\xf0", "\xfe");
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    private static function _emoji_list() {
        $partials = array( '&#x1f004;', '&#x1f0cf;', '&#x1f170;', '&#x1f171;', '&#x1f17e;', '&#x1f17f;', '&#x1f18e;', '&#x1f191;', '&#x1f192;', '&#x1f193;', '&#x1f194;', '&#x1f195;', '&#x1f196;', '&#x1f197;', '&#x1f198;', '&#x1f199;', '&#x1f19a;', '&#x1f1e6;', '&#x1f1e8;', '&#x1f1e9;', '&#x1f1ea;', '&#x1f1eb;', '&#x1f1ec;', '&#x1f1ee;', '&#x1f1f1;', '&#x1f1f2;', '&#x1f1f4;', '&#x1f1f6;', '&#x1f1f7;', '&#x1f1f8;', '&#x1f1f9;', '&#x1f1fa;', '&#x1f1fc;', '&#x1f1fd;', '&#x1f1ff;', '&#x1f1e7;', '&#x1f1ed;', '&#x1f1ef;', '&#x1f1f3;', '&#x1f1fb;', '&#x1f1fe;', '&#x1f1f0;', '&#x1f1f5;', '&#x1f201;', '&#x1f202;', '&#x1f21a;', '&#x1f22f;', '&#x1f232;', '&#x1f233;', '&#x1f234;', '&#x1f235;', '&#x1f236;', '&#x1f237;', '&#x1f238;', '&#x1f239;', '&#x1f23a;', '&#x1f250;', '&#x1f251;', '&#x1f300;', '&#x1f301;', '&#x1f302;', '&#x1f303;', '&#x1f304;', '&#x1f305;', '&#x1f306;', '&#x1f307;', '&#x1f308;', '&#x1f309;', '&#x1f30a;', '&#x1f30b;', '&#x1f30c;', '&#x1f30d;', '&#x1f30e;', '&#x1f30f;', '&#x1f310;', '&#x1f311;', '&#x1f312;', '&#x1f313;', '&#x1f314;', '&#x1f315;', '&#x1f316;', '&#x1f317;', '&#x1f318;', '&#x1f319;', '&#x1f31a;', '&#x1f31b;', '&#x1f31c;', '&#x1f31d;', '&#x1f31e;', '&#x1f31f;', '&#x1f320;', '&#x1f321;', '&#x1f324;', '&#x1f325;', '&#x1f326;', '&#x1f327;', '&#x1f328;', '&#x1f329;', '&#x1f32a;', '&#x1f32b;', '&#x1f32c;', '&#x1f32d;', '&#x1f32e;', '&#x1f32f;', '&#x1f330;', '&#x1f331;', '&#x1f332;', '&#x1f333;', '&#x1f334;', '&#x1f335;', '&#x1f336;', '&#x1f337;', '&#x1f338;', '&#x1f339;', '&#x1f33a;', '&#x1f33b;', '&#x1f33c;', '&#x1f33d;', '&#x1f33e;', '&#x1f33f;', '&#x1f340;', '&#x1f341;', '&#x1f342;', '&#x1f343;', '&#x1f344;', '&#x1f345;', '&#x1f346;', '&#x1f347;', '&#x1f348;', '&#x1f349;', '&#x1f34a;', '&#x1f34b;', '&#x1f34c;', '&#x1f34d;', '&#x1f34e;', '&#x1f34f;', '&#x1f350;', '&#x1f351;', '&#x1f352;', '&#x1f353;', '&#x1f354;', '&#x1f355;', '&#x1f356;', '&#x1f357;', '&#x1f358;', '&#x1f359;', '&#x1f35a;', '&#x1f35b;', '&#x1f35c;', '&#x1f35d;', '&#x1f35e;', '&#x1f35f;', '&#x1f360;', '&#x1f361;', '&#x1f362;', '&#x1f363;', '&#x1f364;', '&#x1f365;', '&#x1f366;', '&#x1f367;', '&#x1f368;', '&#x1f369;', '&#x1f36a;', '&#x1f36b;', '&#x1f36c;', '&#x1f36d;', '&#x1f36e;', '&#x1f36f;', '&#x1f370;', '&#x1f371;', '&#x1f372;', '&#x1f373;', '&#x1f374;', '&#x1f375;', '&#x1f376;', '&#x1f377;', '&#x1f378;', '&#x1f379;', '&#x1f37a;', '&#x1f37b;', '&#x1f37c;', '&#x1f37d;', '&#x1f37e;', '&#x1f37f;', '&#x1f380;', '&#x1f381;', '&#x1f382;', '&#x1f383;', '&#x1f384;', '&#x1f385;', '&#x1f3fb;', '&#x1f3fc;', '&#x1f3fd;', '&#x1f3fe;', '&#x1f3ff;', '&#x1f386;', '&#x1f387;', '&#x1f388;', '&#x1f389;', '&#x1f38a;', '&#x1f38b;', '&#x1f38c;', '&#x1f38d;', '&#x1f38e;', '&#x1f38f;', '&#x1f390;', '&#x1f391;', '&#x1f392;', '&#x1f393;', '&#x1f396;', '&#x1f397;', '&#x1f399;', '&#x1f39a;', '&#x1f39b;', '&#x1f39e;', '&#x1f39f;', '&#x1f3a0;', '&#x1f3a1;', '&#x1f3a2;', '&#x1f3a3;', '&#x1f3a4;', '&#x1f3a5;', '&#x1f3a6;', '&#x1f3a7;', '&#x1f3a8;', '&#x1f3a9;', '&#x1f3aa;', '&#x1f3ab;', '&#x1f3ac;', '&#x1f3ad;', '&#x1f3ae;', '&#x1f3af;', '&#x1f3b0;', '&#x1f3b1;', '&#x1f3b2;', '&#x1f3b3;', '&#x1f3b4;', '&#x1f3b5;', '&#x1f3b6;', '&#x1f3b7;', '&#x1f3b8;', '&#x1f3b9;', '&#x1f3ba;', '&#x1f3bb;', '&#x1f3bc;', '&#x1f3bd;', '&#x1f3be;', '&#x1f3bf;', '&#x1f3c0;', '&#x1f3c1;', '&#x1f3c2;', '&#x1f3c3;', '&#x200d;', '&#x2640;', '&#xfe0f;', '&#x2642;', '&#x1f3c4;', '&#x1f3c5;', '&#x1f3c6;', '&#x1f3c7;', '&#x1f3c8;', '&#x1f3c9;', '&#x1f3ca;', '&#x1f3cb;', '&#x1f3cc;', '&#x1f3cd;', '&#x1f3ce;', '&#x1f3cf;', '&#x1f3d0;', '&#x1f3d1;', '&#x1f3d2;', '&#x1f3d3;', '&#x1f3d4;', '&#x1f3d5;', '&#x1f3d6;', '&#x1f3d7;', '&#x1f3d8;', '&#x1f3d9;', '&#x1f3da;', '&#x1f3db;', '&#x1f3dc;', '&#x1f3dd;', '&#x1f3de;', '&#x1f3df;', '&#x1f3e0;', '&#x1f3e1;', '&#x1f3e2;', '&#x1f3e3;', '&#x1f3e4;', '&#x1f3e5;', '&#x1f3e6;', '&#x1f3e7;', '&#x1f3e8;', '&#x1f3e9;', '&#x1f3ea;', '&#x1f3eb;', '&#x1f3ec;', '&#x1f3ed;', '&#x1f3ee;', '&#x1f3ef;', '&#x1f3f0;', '&#x1f3f3;', '&#x1f3f4;', '&#x2620;', '&#xe0067;', '&#xe0062;', '&#xe0065;', '&#xe006e;', '&#xe007f;', '&#xe0073;', '&#xe0063;', '&#xe0074;', '&#xe0077;', '&#xe006c;', '&#x1f3f5;', '&#x1f3f7;', '&#x1f3f8;', '&#x1f3f9;', '&#x1f3fa;', '&#x1f400;', '&#x1f401;', '&#x1f402;', '&#x1f403;', '&#x1f404;', '&#x1f405;', '&#x1f406;', '&#x1f407;', '&#x1f408;', '&#x1f409;', '&#x1f40a;', '&#x1f40b;', '&#x1f40c;', '&#x1f40d;', '&#x1f40e;', '&#x1f40f;', '&#x1f410;', '&#x1f411;', '&#x1f412;', '&#x1f413;', '&#x1f414;', '&#x1f415;', '&#x1f9ba;', '&#x1f416;', '&#x1f417;', '&#x1f418;', '&#x1f419;', '&#x1f41a;', '&#x1f41b;', '&#x1f41c;', '&#x1f41d;', '&#x1f41e;', '&#x1f41f;', '&#x1f420;', '&#x1f421;', '&#x1f422;', '&#x1f423;', '&#x1f424;', '&#x1f425;', '&#x1f426;', '&#x1f427;', '&#x1f428;', '&#x1f429;', '&#x1f42a;', '&#x1f42b;', '&#x1f42c;', '&#x1f42d;', '&#x1f42e;', '&#x1f42f;', '&#x1f430;', '&#x1f431;', '&#x1f432;', '&#x1f433;', '&#x1f434;', '&#x1f435;', '&#x1f436;', '&#x1f437;', '&#x1f438;', '&#x1f439;', '&#x1f43a;', '&#x1f43b;', '&#x1f43c;', '&#x1f43d;', '&#x1f43e;', '&#x1f43f;', '&#x1f440;', '&#x1f441;', '&#x1f5e8;', '&#x1f442;', '&#x1f443;', '&#x1f444;', '&#x1f445;', '&#x1f446;', '&#x1f447;', '&#x1f448;', '&#x1f449;', '&#x1f44a;', '&#x1f44b;', '&#x1f44c;', '&#x1f44d;', '&#x1f44e;', '&#x1f44f;', '&#x1f450;', '&#x1f451;', '&#x1f452;', '&#x1f453;', '&#x1f454;', '&#x1f455;', '&#x1f456;', '&#x1f457;', '&#x1f458;', '&#x1f459;', '&#x1f45a;', '&#x1f45b;', '&#x1f45c;', '&#x1f45d;', '&#x1f45e;', '&#x1f45f;', '&#x1f460;', '&#x1f461;', '&#x1f462;', '&#x1f463;', '&#x1f464;', '&#x1f465;', '&#x1f466;', '&#x1f467;', '&#x1f468;', '&#x1f4bb;', '&#x1f4bc;', '&#x1f527;', '&#x1f52c;', '&#x1f680;', '&#x1f692;', '&#x1f9af;', '&#x1f9b0;', '&#x1f9b1;', '&#x1f9b2;', '&#x1f9b3;', '&#x1f9bc;', '&#x1f9bd;', '&#x2695;', '&#x2696;', '&#x2708;', '&#x1f91d;', '&#x1f469;', '&#x2764;', '&#x1f48b;', '&#x1f46a;', '&#x1f46b;', '&#x1f46c;', '&#x1f46d;', '&#x1f46e;', '&#x1f46f;', '&#x1f470;', '&#x1f471;', '&#x1f472;', '&#x1f473;', '&#x1f474;', '&#x1f475;', '&#x1f476;', '&#x1f477;', '&#x1f478;', '&#x1f479;', '&#x1f47a;', '&#x1f47b;', '&#x1f47c;', '&#x1f47d;', '&#x1f47e;', '&#x1f47f;', '&#x1f480;', '&#x1f481;', '&#x1f482;', '&#x1f483;', '&#x1f484;', '&#x1f485;', '&#x1f486;', '&#x1f487;', '&#x1f488;', '&#x1f489;', '&#x1f48a;', '&#x1f48c;', '&#x1f48d;', '&#x1f48e;', '&#x1f48f;', '&#x1f490;', '&#x1f491;', '&#x1f492;', '&#x1f493;', '&#x1f494;', '&#x1f495;', '&#x1f496;', '&#x1f497;', '&#x1f498;', '&#x1f499;', '&#x1f49a;', '&#x1f49b;', '&#x1f49c;', '&#x1f49d;', '&#x1f49e;', '&#x1f49f;', '&#x1f4a0;', '&#x1f4a1;', '&#x1f4a2;', '&#x1f4a3;', '&#x1f4a4;', '&#x1f4a5;', '&#x1f4a6;', '&#x1f4a7;', '&#x1f4a8;', '&#x1f4a9;', '&#x1f4aa;', '&#x1f4ab;', '&#x1f4ac;', '&#x1f4ad;', '&#x1f4ae;', '&#x1f4af;', '&#x1f4b0;', '&#x1f4b1;', '&#x1f4b2;', '&#x1f4b3;', '&#x1f4b4;', '&#x1f4b5;', '&#x1f4b6;', '&#x1f4b7;', '&#x1f4b8;', '&#x1f4b9;', '&#x1f4ba;', '&#x1f4bd;', '&#x1f4be;', '&#x1f4bf;', '&#x1f4c0;', '&#x1f4c1;', '&#x1f4c2;', '&#x1f4c3;', '&#x1f4c4;', '&#x1f4c5;', '&#x1f4c6;', '&#x1f4c7;', '&#x1f4c8;', '&#x1f4c9;', '&#x1f4ca;', '&#x1f4cb;', '&#x1f4cc;', '&#x1f4cd;', '&#x1f4ce;', '&#x1f4cf;', '&#x1f4d0;', '&#x1f4d1;', '&#x1f4d2;', '&#x1f4d3;', '&#x1f4d4;', '&#x1f4d5;', '&#x1f4d6;', '&#x1f4d7;', '&#x1f4d8;', '&#x1f4d9;', '&#x1f4da;', '&#x1f4db;', '&#x1f4dc;', '&#x1f4dd;', '&#x1f4de;', '&#x1f4df;', '&#x1f4e0;', '&#x1f4e1;', '&#x1f4e2;', '&#x1f4e3;', '&#x1f4e4;', '&#x1f4e5;', '&#x1f4e6;', '&#x1f4e7;', '&#x1f4e8;', '&#x1f4e9;', '&#x1f4ea;', '&#x1f4eb;', '&#x1f4ec;', '&#x1f4ed;', '&#x1f4ee;', '&#x1f4ef;', '&#x1f4f0;', '&#x1f4f1;', '&#x1f4f2;', '&#x1f4f3;', '&#x1f4f4;', '&#x1f4f5;', '&#x1f4f6;', '&#x1f4f7;', '&#x1f4f8;', '&#x1f4f9;', '&#x1f4fa;', '&#x1f4fb;', '&#x1f4fc;', '&#x1f4fd;', '&#x1f4ff;', '&#x1f500;', '&#x1f501;', '&#x1f502;', '&#x1f503;', '&#x1f504;', '&#x1f505;', '&#x1f506;', '&#x1f507;', '&#x1f508;', '&#x1f509;', '&#x1f50a;', '&#x1f50b;', '&#x1f50c;', '&#x1f50d;', '&#x1f50e;', '&#x1f50f;', '&#x1f510;', '&#x1f511;', '&#x1f512;', '&#x1f513;', '&#x1f514;', '&#x1f515;', '&#x1f516;', '&#x1f517;', '&#x1f518;', '&#x1f519;', '&#x1f51a;', '&#x1f51b;', '&#x1f51c;', '&#x1f51d;', '&#x1f51e;', '&#x1f51f;', '&#x1f520;', '&#x1f521;', '&#x1f522;', '&#x1f523;', '&#x1f524;', '&#x1f525;', '&#x1f526;', '&#x1f528;', '&#x1f529;', '&#x1f52a;', '&#x1f52b;', '&#x1f52d;', '&#x1f52e;', '&#x1f52f;', '&#x1f530;', '&#x1f531;', '&#x1f532;', '&#x1f533;', '&#x1f534;', '&#x1f535;', '&#x1f536;', '&#x1f537;', '&#x1f538;', '&#x1f539;', '&#x1f53a;', '&#x1f53b;', '&#x1f53c;', '&#x1f53d;', '&#x1f549;', '&#x1f54a;', '&#x1f54b;', '&#x1f54c;', '&#x1f54d;', '&#x1f54e;', '&#x1f550;', '&#x1f551;', '&#x1f552;', '&#x1f553;', '&#x1f554;', '&#x1f555;', '&#x1f556;', '&#x1f557;', '&#x1f558;', '&#x1f559;', '&#x1f55a;', '&#x1f55b;', '&#x1f55c;', '&#x1f55d;', '&#x1f55e;', '&#x1f55f;', '&#x1f560;', '&#x1f561;', '&#x1f562;', '&#x1f563;', '&#x1f564;', '&#x1f565;', '&#x1f566;', '&#x1f567;', '&#x1f56f;', '&#x1f570;', '&#x1f573;', '&#x1f574;', '&#x1f575;', '&#x1f576;', '&#x1f577;', '&#x1f578;', '&#x1f579;', '&#x1f57a;', '&#x1f587;', '&#x1f58a;', '&#x1f58b;', '&#x1f58c;', '&#x1f58d;', '&#x1f590;', '&#x1f595;', '&#x1f596;', '&#x1f5a4;', '&#x1f5a5;', '&#x1f5a8;', '&#x1f5b1;', '&#x1f5b2;', '&#x1f5bc;', '&#x1f5c2;', '&#x1f5c3;', '&#x1f5c4;', '&#x1f5d1;', '&#x1f5d2;', '&#x1f5d3;', '&#x1f5dc;', '&#x1f5dd;', '&#x1f5de;', '&#x1f5e1;', '&#x1f5e3;', '&#x1f5ef;', '&#x1f5f3;', '&#x1f5fa;', '&#x1f5fb;', '&#x1f5fc;', '&#x1f5fd;', '&#x1f5fe;', '&#x1f5ff;', '&#x1f600;', '&#x1f601;', '&#x1f602;', '&#x1f603;', '&#x1f604;', '&#x1f605;', '&#x1f606;', '&#x1f607;', '&#x1f608;', '&#x1f609;', '&#x1f60a;', '&#x1f60b;', '&#x1f60c;', '&#x1f60d;', '&#x1f60e;', '&#x1f60f;', '&#x1f610;', '&#x1f611;', '&#x1f612;', '&#x1f613;', '&#x1f614;', '&#x1f615;', '&#x1f616;', '&#x1f617;', '&#x1f618;', '&#x1f619;', '&#x1f61a;', '&#x1f61b;', '&#x1f61c;', '&#x1f61d;', '&#x1f61e;', '&#x1f61f;', '&#x1f620;', '&#x1f621;', '&#x1f622;', '&#x1f623;', '&#x1f624;', '&#x1f625;', '&#x1f626;', '&#x1f627;', '&#x1f628;', '&#x1f629;', '&#x1f62a;', '&#x1f62b;', '&#x1f62c;', '&#x1f62d;', '&#x1f62e;', '&#x1f62f;', '&#x1f630;', '&#x1f631;', '&#x1f632;', '&#x1f633;', '&#x1f634;', '&#x1f635;', '&#x1f636;', '&#x1f637;', '&#x1f638;', '&#x1f639;', '&#x1f63a;', '&#x1f63b;', '&#x1f63c;', '&#x1f63d;', '&#x1f63e;', '&#x1f63f;', '&#x1f640;', '&#x1f641;', '&#x1f642;', '&#x1f643;', '&#x1f644;', '&#x1f645;', '&#x1f646;', '&#x1f647;', '&#x1f648;', '&#x1f649;', '&#x1f64a;', '&#x1f64b;', '&#x1f64c;', '&#x1f64d;', '&#x1f64e;', '&#x1f64f;', '&#x1f681;', '&#x1f682;', '&#x1f683;', '&#x1f684;', '&#x1f685;', '&#x1f686;', '&#x1f687;', '&#x1f688;', '&#x1f689;', '&#x1f68a;', '&#x1f68b;', '&#x1f68c;', '&#x1f68d;', '&#x1f68e;', '&#x1f68f;', '&#x1f690;', '&#x1f691;', '&#x1f693;', '&#x1f694;', '&#x1f695;', '&#x1f696;', '&#x1f697;', '&#x1f698;', '&#x1f699;', '&#x1f69a;', '&#x1f69b;', '&#x1f69c;', '&#x1f69d;', '&#x1f69e;', '&#x1f69f;', '&#x1f6a0;', '&#x1f6a1;', '&#x1f6a2;', '&#x1f6a3;', '&#x1f6a4;', '&#x1f6a5;', '&#x1f6a6;', '&#x1f6a7;', '&#x1f6a8;', '&#x1f6a9;', '&#x1f6aa;', '&#x1f6ab;', '&#x1f6ac;', '&#x1f6ad;', '&#x1f6ae;', '&#x1f6af;', '&#x1f6b0;', '&#x1f6b1;', '&#x1f6b2;', '&#x1f6b3;', '&#x1f6b4;', '&#x1f6b5;', '&#x1f6b6;', '&#x1f6b7;', '&#x1f6b8;', '&#x1f6b9;', '&#x1f6ba;', '&#x1f6bb;', '&#x1f6bc;', '&#x1f6bd;', '&#x1f6be;', '&#x1f6bf;', '&#x1f6c0;', '&#x1f6c1;', '&#x1f6c2;', '&#x1f6c3;', '&#x1f6c4;', '&#x1f6c5;', '&#x1f6cb;', '&#x1f6cc;', '&#x1f6cd;', '&#x1f6ce;', '&#x1f6cf;', '&#x1f6d0;', '&#x1f6d1;', '&#x1f6d2;', '&#x1f6d5;', '&#x1f6e0;', '&#x1f6e1;', '&#x1f6e2;', '&#x1f6e3;', '&#x1f6e4;', '&#x1f6e5;', '&#x1f6e9;', '&#x1f6eb;', '&#x1f6ec;', '&#x1f6f0;', '&#x1f6f3;', '&#x1f6f4;', '&#x1f6f5;', '&#x1f6f6;', '&#x1f6f7;', '&#x1f6f8;', '&#x1f6f9;', '&#x1f6fa;', '&#x1f7e0;', '&#x1f7e1;', '&#x1f7e2;', '&#x1f7e3;', '&#x1f7e4;', '&#x1f7e5;', '&#x1f7e6;', '&#x1f7e7;', '&#x1f7e8;', '&#x1f7e9;', '&#x1f7ea;', '&#x1f7eb;', '&#x1f90d;', '&#x1f90e;', '&#x1f90f;', '&#x1f910;', '&#x1f911;', '&#x1f912;', '&#x1f913;', '&#x1f914;', '&#x1f915;', '&#x1f916;', '&#x1f917;', '&#x1f918;', '&#x1f919;', '&#x1f91a;', '&#x1f91b;', '&#x1f91c;', '&#x1f91e;', '&#x1f91f;', '&#x1f920;', '&#x1f921;', '&#x1f922;', '&#x1f923;', '&#x1f924;', '&#x1f925;', '&#x1f926;', '&#x1f927;', '&#x1f928;', '&#x1f929;', '&#x1f92a;', '&#x1f92b;', '&#x1f92c;', '&#x1f92d;', '&#x1f92e;', '&#x1f92f;', '&#x1f930;', '&#x1f931;', '&#x1f932;', '&#x1f933;', '&#x1f934;', '&#x1f935;', '&#x1f936;', '&#x1f937;', '&#x1f938;', '&#x1f939;', '&#x1f93a;', '&#x1f93c;', '&#x1f93d;', '&#x1f93e;', '&#x1f93f;', '&#x1f940;', '&#x1f941;', '&#x1f942;', '&#x1f943;', '&#x1f944;', '&#x1f945;', '&#x1f947;', '&#x1f948;', '&#x1f949;', '&#x1f94a;', '&#x1f94b;', '&#x1f94c;', '&#x1f94d;', '&#x1f94e;', '&#x1f94f;', '&#x1f950;', '&#x1f951;', '&#x1f952;', '&#x1f953;', '&#x1f954;', '&#x1f955;', '&#x1f956;', '&#x1f957;', '&#x1f958;', '&#x1f959;', '&#x1f95a;', '&#x1f95b;', '&#x1f95c;', '&#x1f95d;', '&#x1f95e;', '&#x1f95f;', '&#x1f960;', '&#x1f961;', '&#x1f962;', '&#x1f963;', '&#x1f964;', '&#x1f965;', '&#x1f966;', '&#x1f967;', '&#x1f968;', '&#x1f969;', '&#x1f96a;', '&#x1f96b;', '&#x1f96c;', '&#x1f96d;', '&#x1f96e;', '&#x1f96f;', '&#x1f970;', '&#x1f971;', '&#x1f973;', '&#x1f974;', '&#x1f975;', '&#x1f976;', '&#x1f97a;', '&#x1f97b;', '&#x1f97c;', '&#x1f97d;', '&#x1f97e;', '&#x1f97f;', '&#x1f980;', '&#x1f981;', '&#x1f982;', '&#x1f983;', '&#x1f984;', '&#x1f985;', '&#x1f986;', '&#x1f987;', '&#x1f988;', '&#x1f989;', '&#x1f98a;', '&#x1f98b;', '&#x1f98c;', '&#x1f98d;', '&#x1f98e;', '&#x1f98f;', '&#x1f990;', '&#x1f991;', '&#x1f992;', '&#x1f993;', '&#x1f994;', '&#x1f995;', '&#x1f996;', '&#x1f997;', '&#x1f998;', '&#x1f999;', '&#x1f99a;', '&#x1f99b;', '&#x1f99c;', '&#x1f99d;', '&#x1f99e;', '&#x1f99f;', '&#x1f9a0;', '&#x1f9a1;', '&#x1f9a2;', '&#x1f9a5;', '&#x1f9a6;', '&#x1f9a7;', '&#x1f9a8;', '&#x1f9a9;', '&#x1f9aa;', '&#x1f9ae;', '&#x1f9b4;', '&#x1f9b5;', '&#x1f9b6;', '&#x1f9b7;', '&#x1f9b8;', '&#x1f9b9;', '&#x1f9bb;', '&#x1f9be;', '&#x1f9bf;', '&#x1f9c0;', '&#x1f9c1;', '&#x1f9c2;', '&#x1f9c3;', '&#x1f9c4;', '&#x1f9c5;', '&#x1f9c6;', '&#x1f9c7;', '&#x1f9c8;', '&#x1f9c9;', '&#x1f9ca;', '&#x1f9cd;', '&#x1f9ce;', '&#x1f9cf;', '&#x1f9d0;', '&#x1f9d1;', '&#x1f9d2;', '&#x1f9d3;', '&#x1f9d4;', '&#x1f9d5;', '&#x1f9d6;', '&#x1f9d7;', '&#x1f9d8;', '&#x1f9d9;', '&#x1f9da;', '&#x1f9db;', '&#x1f9dc;', '&#x1f9dd;', '&#x1f9de;', '&#x1f9df;', '&#x1f9e0;', '&#x1f9e1;', '&#x1f9e2;', '&#x1f9e3;', '&#x1f9e4;', '&#x1f9e5;', '&#x1f9e6;', '&#x1f9e7;', '&#x1f9e8;', '&#x1f9e9;', '&#x1f9ea;', '&#x1f9eb;', '&#x1f9ec;', '&#x1f9ed;', '&#x1f9ee;', '&#x1f9ef;', '&#x1f9f0;', '&#x1f9f1;', '&#x1f9f2;', '&#x1f9f3;', '&#x1f9f4;', '&#x1f9f5;', '&#x1f9f6;', '&#x1f9f7;', '&#x1f9f8;', '&#x1f9f9;', '&#x1f9fa;', '&#x1f9fb;', '&#x1f9fc;', '&#x1f9fd;', '&#x1f9fe;', '&#x1f9ff;', '&#x1fa70;', '&#x1fa71;', '&#x1fa72;', '&#x1fa73;', '&#x1fa78;', '&#x1fa79;', '&#x1fa7a;', '&#x1fa80;', '&#x1fa81;', '&#x1fa82;', '&#x1fa90;', '&#x1fa91;', '&#x1fa92;', '&#x1fa93;', '&#x1fa94;', '&#x1fa95;', '&#x203c;', '&#x2049;', '&#x2122;', '&#x2139;', '&#x2194;', '&#x2195;', '&#x2196;', '&#x2197;', '&#x2198;', '&#x2199;', '&#x21a9;', '&#x21aa;', '&#x20e3;', '&#x231a;', '&#x231b;', '&#x2328;', '&#x23cf;', '&#x23e9;', '&#x23ea;', '&#x23eb;', '&#x23ec;', '&#x23ed;', '&#x23ee;', '&#x23ef;', '&#x23f0;', '&#x23f1;', '&#x23f2;', '&#x23f3;', '&#x23f8;', '&#x23f9;', '&#x23fa;', '&#x24c2;', '&#x25aa;', '&#x25ab;', '&#x25b6;', '&#x25c0;', '&#x25fb;', '&#x25fc;', '&#x25fd;', '&#x25fe;', '&#x2600;', '&#x2601;', '&#x2602;', '&#x2603;', '&#x2604;', '&#x260e;', '&#x2611;', '&#x2614;', '&#x2615;', '&#x2618;', '&#x261d;', '&#x2622;', '&#x2623;', '&#x2626;', '&#x262a;', '&#x262e;', '&#x262f;', '&#x2638;', '&#x2639;', '&#x263a;', '&#x2648;', '&#x2649;', '&#x264a;', '&#x264b;', '&#x264c;', '&#x264d;', '&#x264e;', '&#x264f;', '&#x2650;', '&#x2651;', '&#x2652;', '&#x2653;', '&#x265f;', '&#x2660;', '&#x2663;', '&#x2665;', '&#x2666;', '&#x2668;', '&#x267b;', '&#x267e;', '&#x267f;', '&#x2692;', '&#x2693;', '&#x2694;', '&#x2697;', '&#x2699;', '&#x269b;', '&#x269c;', '&#x26a0;', '&#x26a1;', '&#x26aa;', '&#x26ab;', '&#x26b0;', '&#x26b1;', '&#x26bd;', '&#x26be;', '&#x26c4;', '&#x26c5;', '&#x26c8;', '&#x26ce;', '&#x26cf;', '&#x26d1;', '&#x26d3;', '&#x26d4;', '&#x26e9;', '&#x26ea;', '&#x26f0;', '&#x26f1;', '&#x26f2;', '&#x26f3;', '&#x26f4;', '&#x26f5;', '&#x26f7;', '&#x26f8;', '&#x26f9;', '&#x26fa;', '&#x26fd;', '&#x2702;', '&#x2705;', '&#x2709;', '&#x270a;', '&#x270b;', '&#x270c;', '&#x270d;', '&#x270f;', '&#x2712;', '&#x2714;', '&#x2716;', '&#x271d;', '&#x2721;', '&#x2728;', '&#x2733;', '&#x2734;', '&#x2744;', '&#x2747;', '&#x274c;', '&#x274e;', '&#x2753;', '&#x2754;', '&#x2755;', '&#x2757;', '&#x2763;', '&#x2795;', '&#x2796;', '&#x2797;', '&#x27a1;', '&#x27b0;', '&#x27bf;', '&#x2934;', '&#x2935;', '&#x2b05;', '&#x2b06;', '&#x2b07;', '&#x2b1b;', '&#x2b1c;', '&#x2b50;', '&#x2b55;', '&#x3030;', '&#x303d;', '&#x3297;', '&#x3299;', '&#xe50a;' );

        return $partials;
    }

    public static function getSelectedOptin($conditions, $options, $default_optin) {
        $selected_option = $default_optin;

        if (!isset($conditions['optins']) || !is_array($conditions['optins'])) {
            return $selected_option;
        }

        foreach ($conditions['optins'] as $condition) {
            if (!empty($condition['option']) && isset($options->{$condition['option']})) {
                $value = $options->{$condition['option']};
                $condition_result = self::getConditionResult($value, $condition['string'], $condition['operator']);

                if ($condition_result) {
                    $selected_option = $condition['connectTo'];
                    break;
                }
            }
        }

        return $selected_option;
    }

    private static function getConditionResult($value, $value_to, $operator) {
        $result = false;
        $value = ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($value))));
        $value = str_replace(':', '', $value);
        $value = str_replace(';', '', $value);
        $value = trim($value);

        $value_to = trim($value_to);
        $value_to = str_replace(':', '', $value_to);
        $value_to = str_replace(';', '', $value_to);
        $value_to = trim($value_to);

        switch ($operator) {
            case 'is like':
                if ($value === $value_to) {
                    $result = true;
                }
                break;
            case 'like':
                if ($value === $value_to) {
                    $result = true;
                }
                break;
            case 'not-like':
                if ($value !== $value_to) {
                    $result = true;
                }
                break;
            case 'contains':
                if (strpos($value, $value_to) !== false) {
                    $result = true;
                }
                break;
            case 'not contains':
                if (strpos($value, $value_to) === false) {
                    $result = true;
                }
                break;
            case 'bigger as':
                if ((float) $value > (float)$value_to) {
                    $result = true;
                }
                break;
            case 'smaller as':
                if ((float)$value < (float)$value_to) {
                    $result = true;
                }
                break;
        }

        return $result;
    }

    public static function getDefaultApiFields() {
        return self::$default_api_fields;
    }

    public static function getDefaultApiFieldsTypes() {
        return self::$default_api_fields_types;
    }

    public static function getUniqueTableColumns($paths) {
        $unique_array = array();

        foreach ($paths as $key => $value) {
            list($table_column, $current_option) = explode(' (', $value);

            if (!in_array($table_column, $unique_array) && "" !== trim(str_replace(')', '', $current_option))) {
                $unique_array[] = $table_column;
            }
        }

        return $unique_array;
    }

    public static function secureLicenseKey($license_key) {
        $parts = explode('-', $license_key);

        foreach ($parts as $index => $part) {
            if ($index >= 2 && $index <= 5) {
                $parts[$index] = 'XXXXX';
            }
        }

        return implode('-', $parts);
    }

    public static function get_map_tags_prefix($map_id) {
        $prefix = '';

        $map = MapsModel::get( $map_id );
        $decodedApiSettings = unserialize($map->api);

        $global_tag_prefix = get_option('wp2l_klicktipp_tag_prefix');

        if (!empty($decodedApiSettings['tags_prefix'])) {
            $prefix = $decodedApiSettings['tags_prefix'];
        } elseif (!empty($global_tag_prefix)) {
            $prefix = $global_tag_prefix;
        }

        return $prefix;
    }

    public static function prepareDataForDisplay($api, $data, $prefix = '') {
        $data_for_transfer = array();

        if (!empty($api['fields']['api_email']['table_columns'])) {
            $api_email_filed_name = $api['fields']['api_email']['table_columns'];

            foreach ($data as $item) {
                foreach ($api_email_filed_name as $tablecolumn) {
                    $email_column_value = trim($item->{$tablecolumn});
                    $email_column_valid = filter_var($email_column_value, FILTER_VALIDATE_EMAIL);

                    if ($email_column_valid) {
                        $user_email = $email_column_valid;
                        break;
                    }
                }

                if (empty($user_email)) {
                    continue;
                }

                foreach ($item as $property => $value) {
                    $item->{$property} = ApiHelper::maybe_json_decode($value);
                }

                $user = isset($data_for_transfer[$user_email]) ? $data_for_transfer[$user_email] : array();
                $user['optin'] = self::getSelectedOptin($api['conditions'], $item, $api['default_optin']);
                $user['fields'] = array();

                foreach ($api['fields'] as $key => $field_data) {
                    $api_key = str_replace('api_', '', $key);
                    if ('email' !== $api_key) {
                        $type = isset($field_data['type']) ? $field_data['type'] : '';
						$local_to_gmt = isset($field_data['gmt']) ? $field_data['gmt'] : false;
						$gmt_to_local = isset($field_data['gmt_to_local']) ? $field_data['gmt_to_local'] : false;
                        $gmt = array();

                        if ($local_to_gmt) {
                            $gmt['local_to_gmt'] = 1;
                        } elseif ($gmt_to_local) {
                            $gmt['gmt_to_local'] = 1;
                        }

                        if (!empty($field_data['table_columns'])) {
							if ($type == 'datetime') {
								$dt = self::splitDateTimeField($field_data['table_columns'], $item, $type);
								$user['fields'][$api_key][] = self::sanitizeValueByType($dt, $type, $gmt);
							} else {

								foreach ($field_data['table_columns'] as $table_column) {
									if (!empty($item->{$table_column})) {
									   $user['fields'][$api_key][] = self::sanitizeValueByType($item->{$table_column}, $type, $gmt);
									} else {
										$user['fields'][$api_key][] = '';
									}
								}
							}

                            $user['fields'][$api_key] = array_filter($user['fields'][$api_key], function($var) {
                                return ($var !== NULL && $var !== FALSE && trim($var) !== "");
                            });

                            $user['fields'][$api_key] = implode('; ', $user['fields'][$api_key]);
                        }
                    }
                }

                $user['tags'] = isset($user['tags']) && is_array($user['tags']) ? $user['tags'] : array();
                $api_conditions = !empty($api['conditions']) ? $api['conditions'] : array();
                $api_connected_for_tags = !empty($api['connected_for_tags']) ? $api['connected_for_tags'] : array();
                $api_multiple_connected = !empty($api['multiple_autotags']['autotag_items']) ? $api['multiple_autotags']['autotag_items'] : array();
                $tags = self::getTags($api_conditions, $api_connected_for_tags, $api_multiple_connected, $item, $prefix);
                $condition_tags = array_values(self::getConditionTags($api['conditions']['tags'], $item));
                $user['tags'] = array_unique(array_merge($user['tags'], $tags));
                $user['tags'] = array_filter(array_map('trim', $user['tags']), function($value) { return $value !== ''; });

                if (!empty($api['manually_selected_tags']['tag_ids'])) {
                    $user['manually_tags'] = $api['manually_selected_tags']['tag_ids'];
                } else {
                    $user['manually_tags'] = array();
                }

                $user['manually_tags'] = array_unique(array_merge($user['manually_tags'], $condition_tags));
                $user['detach_tags'] = isset($user['detach_tags']) && is_array($user['detach_tags']) ? $user['detach_tags'] : array();

                if (!empty($api['conditions']['detach_tags'])) {
                    $detach_tags = self::getTags($api_conditions, false, $api_multiple_connected, $item, '', 'detach');
                    $user['detach_tags'] = array_unique(array_merge($user['detach_tags'], $detach_tags));
                }

                if (!empty($api['conditions']['detach_autotags'])) {
                    $detach_autotags = self::getTags($api_conditions, $api_connected_for_tags, $api_multiple_connected, $item, $prefix, 'detach_autotags');
                    $user['detach_auto_tags'] = array_unique($detach_autotags);
                }

                $data_for_transfer[$user_email] = $user;
            }
        }

        return $data_for_transfer;
    }

    public static function prepareDataForTransfer($api, $data, $prefix = '', $date_to_compare = array()) {
        $data_for_transfer = array();

        if (!empty($api['fields']['api_email']['table_columns'])) {
            $api_email_filed_name = $api['fields']['api_email']['table_columns'];

            foreach ($data as $item) {

                if (!empty($date_to_compare) && !empty($date_to_compare['fields'])) {
                    $outdated = true;
                    $start_date_value = !empty($date_to_compare['date_range']['start']) ? self::date_to_timestamp($date_to_compare['date_range']['start']) : false;
                    $end_date_value = !empty($date_to_compare['date_range']['end']) ? self::date_to_timestamp($date_to_compare['date_range']['end']) : false;

                    foreach ($date_to_compare['fields'] as $field_to_compare) {
                        if (!empty($item->{$field_to_compare})) {
                            $date_value = self::date_to_timestamp($item->{$field_to_compare});


                            if ($date_value) {
                                if ($start_date_value && $end_date_value) {
                                    if ($date_value > $start_date_value && $date_value < ($end_date_value + 60 * 60 * 24)) {
                                        $outdated = false;
                                    }
                                } elseif ($start_date_value && !$end_date_value) {
                                    if ($date_value > $start_date_value) {
                                        $outdated = false;
                                    }
                                } elseif (!$start_date_value && $end_date_value) {
                                    if ($date_value < ($end_date_value + 60 * 60 * 24)) {
                                        $outdated = false;
                                    }
                                }
                            }
                        }
                    }

                    if ($outdated) {
                        continue;
                    }
                }

                if (!empty($api["conditions"]["donot_optins"])) {
                    $do_not_optin = false;

                    foreach ($api["conditions"]["donot_optins"] as $condition) {
                        if (!isset($condition['option']) || !isset($condition['string']) || !isset($condition['operator'])) {
                            continue;
                        }

                        $condition_result = self::getConditionResult($item->{$condition['option']}, $condition['string'], $condition['operator']);

                        if ($condition_result) {
                            $do_not_optin = true;
                        }
                    }

                    if ($do_not_optin) {
                        continue;
                    }
                }

                foreach ($api_email_filed_name as $tablecolumn) {
                    $email_column_value = trim($item->{$tablecolumn});
                    $email_column_valid = filter_var($email_column_value, FILTER_VALIDATE_EMAIL);

                    if ($email_column_valid) {
                        $user_email = $email_column_valid;
                        break;
                    }
                }

                if (empty($user_email)) {
                    continue;
                }

                foreach ($item as $property => $value) {
                    $item->{$property} = ApiHelper::maybe_json_decode($value);
                }

                $user = isset($data_for_transfer[$user_email]) ? $data_for_transfer[$user_email] : array();
                $user['optin'] = self::getSelectedOptin($api['conditions'], $item, $api['default_optin']);
                $user['fields'] = array();

                foreach ($api['fields'] as $key => $field_data) {
                    $api_key = str_replace('api_', '', $key);

                    if ('email' !== $api_key) {
                        $type = isset($field_data['type']) ? $field_data['type'] : '';
                        $local_to_gmt = isset($field_data['gmt']) ? $field_data['gmt'] : false;
						$gmt_to_local = isset($field_data['gmt_to_local']) ? $field_data['gmt_to_local'] : false;
                        $gmt = array();

                        if ($local_to_gmt) {
                            $gmt['local_to_gmt'] = 1;
                        } elseif ($gmt_to_local) {
                            $gmt['gmt_to_local'] = 1;
                        }

						$is_lead_value = $api_key == 'fieldLeadValue';

						if ($is_lead_value) {
						    $type = 'decimal';
                        }

                        $add_to_lead_value = !empty($field_data["add_to_lead"]);

                        if (!empty($field_data['table_columns'])) {
							if ($type == 'datetime') {
								$dt = self::splitDateTimeField($field_data['table_columns'], $item, $type);
								$user['fields'][$api_key][] = self::sanitizeValueByType($dt, $type, $gmt, 'transfer');
							} else {
								foreach ($field_data['table_columns'] as $table_column) {
									if (!empty($item->{$table_column})) {
									    if ($type == 'html') {
                                            $user['fields'][$api_key][] = stripslashes(self::sanitizeHtmlValue($item->{$table_column}));
                                        } elseif ($type == 'url') {
                                            $user['fields'][$api_key][] = stripslashes(self::sanitizeUrlValue($item->{$table_column}));
                                        } else {
                                            $user['fields'][$api_key][] = self::sanitizeValueByType($item->{$table_column}, $type, $gmt, 'transfer');
                                        }
									} else {
										$user['fields'][$api_key][] = '';
									}
								}
							}

                            $user['fields'][$api_key] = array_filter($user['fields'][$api_key], function($var) {
                                return ($var !== NULL && $var !== FALSE && trim($var) !== "");
                            });

							if ($type == 'date' || $type == 'time') {
                                $user['fields'][$api_key] = $user['fields'][$api_key][0];
                            } else {
                                $user['fields'][$api_key] = implode('; ', $user['fields'][$api_key]);
                            }


							if ($is_lead_value && $add_to_lead_value) {
                                $user['fields'][$api_key] = 'add::' . $user['fields'][$api_key];
                            }
                        }
                    }
                }

                $user['tags'] = isset($user['tags']) && is_array($user['tags']) ? $user['tags'] : array();
                $api_conditions = !empty($api['conditions']) ? $api['conditions'] : array();
                $api_connected_for_tags = !empty($api['connected_for_tags']) ? $api['connected_for_tags'] : array();
                $api_multiple_connected = !empty($api['multiple_autotags']['autotag_items']) ? $api['multiple_autotags']['autotag_items'] : array();
                $tags = self::getTags($api_conditions, $api_connected_for_tags, $api_multiple_connected, $item, $prefix, false, 'transfer');
                $condition_tags = array_values(self::getConditionTags($api['conditions']['tags'], $item));
                $user['tags'] = array_unique(array_merge($user['tags'], $tags));
                $user['tags'] = array_filter(array_map('trim', $user['tags']), function($value) { return $value !== ''; });

                if (!empty($api['manually_selected_tags']['tag_ids'])) {
                    $user['manually_tags'] = $api['manually_selected_tags']['tag_ids'];
                } else {
                    $user['manually_tags'] = array();
                }

                $user['manually_tags'] = array_unique(array_merge($user['manually_tags'], $condition_tags));
                $user['detach_tags'] = isset($user['detach_tags']) && is_array($user['detach_tags']) ? $user['detach_tags'] : array();

                if (!empty($api['conditions']['detach_tags'])) {
                    $detach_tags = self::getTags($api_conditions, false, $api_multiple_connected, $item, '', 'detach', 'transfer');
                    $user['detach_tags'] = array_unique(array_merge($user['detach_tags'], $detach_tags));
                }

                if (!empty($api['conditions']['detach_autotags'])) {
                    $detach_autotags = self::getTags($api_conditions, $api_connected_for_tags, $api_multiple_connected, $item, $prefix, 'detach_autotags', 'transfer');
                    $user['detach_auto_tags'] = array_unique($detach_autotags);
                }

                $data_for_transfer[$user_email] = $user;
            }
        }

        return $data_for_transfer;
    }

    public static function getCurrentUserData($users_data, $email) {
        return array($email => $users_data[$email]);
    }

    private static function getConditionTags($tags_conditions, $data) {
        $tags = array();

        if (!empty($tags_conditions)) {
            foreach ($tags_conditions as $condition) {
                if (!empty($condition['option'])) {
                    $condition_result = self::getConditionResult($data->{$condition['option']}, $condition['string'], $condition['operator']);

                    if ($condition_result) {
                        $tags[] = $condition['connectTo'];
                    }
                }
            }
        }

        return array_map('trim', $tags);
    }

    private static function getTags($tags_conditions, $tags_connected_conditions, $tags_multiple_connected, $data, $prefix = '', $type = false, $action = 'display') {
        $tags = array();

        if (!empty($tags_conditions['detach_tags']) && 'detach' === $type) {
            foreach ($tags_conditions['detach_tags'] as $condition) {
                if (!empty($condition['option'])) {
                    $condition_result = self::getConditionResult($data->{$condition['option']}, $condition['string'], $condition['operator']);

                    if ($condition_result) {
                        $tags[] = $condition['connectTo'];
                    }

//                    if ($condition_result && !empty($tags_connected_conditions['separators'])) {
//                        $separators = self::getSeparators($condition['option'], $tags_connected_conditions['separators']);
//
//                        if (!empty($separators) && isset($data->{$condition['option']})) {
//                            $temp_tags = explode($separators[0], str_replace($separators, $separators[0], $data->{$condition['option']}));
//                            $tags = array_unique(array_merge($tags, $temp_tags));
//                        } else {
//                            $tags[] = $condition['connectTo'];
//                        }
//                    }
                }
            }

            return array_map('trim', $tags);
        }

        $auto_tags_allowed = false;
        $auto_tags_detach = false;

        if (!empty($tags_conditions['autotags'])) {
            foreach ($tags_conditions['autotags'] as $condition) {
                $condition_result = self::getConditionResult($data->{$condition['option']}, $condition['string'], $condition['operator']);

                if ($condition_result) {
                    $auto_tags_allowed = true;
                }
            }
        } else {
            $auto_tags_allowed = true;
        }

        if (!empty($tags_conditions['detach_autotags'])) {
            foreach ($tags_conditions['detach_autotags'] as $condition) {
                $condition_result = self::getConditionResult($data->{$condition['option']}, $condition['string'], $condition['operator']);

                if ($condition_result) {
                    $auto_tags_allowed = false;
                    $auto_tags_detach = true;
                }
            }
        }

        if (($auto_tags_allowed && 'detach_autotags' !== $type) || ($auto_tags_detach && 'detach_autotags' === $type)) {
            if (!empty($tags_connected_conditions['tags'])) {
                foreach ($tags_connected_conditions['tags'] as $table_column) {
                    if ( isset($data->{$table_column}) &&  !empty(trim($data->{$table_column}))) {
						$value = trim($data->{$table_column});
						$m_prefix = $prefix;

						if (strpos($value, '; ') === 0) {
							// this means that we have a value wrom replacements table and no needs in ' '
						} else {
							$m_prefix .= ' ';
						}

                        if ('display' === $action) {
                            $tags[] = $m_prefix . ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($data->{$table_column}))));
                        } else {
                            $tags[] = $m_prefix . trim($data->{$table_column});
                        }
                    }
                }
            }

            if (!empty($tags_connected_conditions['tags_concat'])) {
                foreach ($tags_connected_conditions['tags_concat'] as $table_column) {
                    $tags_concat = $data->{$table_column};
					$preprefix = $prefix . ' ';

					if (strpos($tags_concat, '; ') === 0) {
						// get preprefix

						$temp_str = explode(':', $tags_concat, 2);

						if (count($temp_str) == 2) {
							$preprefix = $prefix . $temp_str[0] . ': ';
							$tags_concat = $temp_str[1];

						}
					}

                    $tags_array_temp = explode(',', $tags_concat);
                    $tags_array = array();

                    foreach ($tags_array_temp as $tag_temp) {

                        if (!empty(trim($tag_temp))) {
                            if ('display' === $action) {
                                $tags_array[] = $preprefix . ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($tag_temp))));
                            } else {
                                $tags_array[] = $preprefix . trim($tag_temp);
                            }
                        }
                    }

                    $tags = array_unique(array_merge($tags, $tags_array));
                }
            }

            if (!empty($tags_connected_conditions['separators'])) {
                foreach ($tags_connected_conditions['separators'] as $separator_options) {
                    $separator_prefix = !empty($separator_options["prefix"]) ? $separator_options["prefix"] . ' ' : '';
                    $separator_filter = !empty($separator_options["filter"]) ? explode('||', $separator_options["filter"]) : array();
                    $separator_filter_type = !empty($separator_options["filter_action"]) ? 1 : 0;

                    if (!empty($separator_filter)) {
                        foreach ($separator_filter as $i => $filter) {
                            $separator_filter[$i] = trim($filter);
                        }
                    }
                    if (!empty($separator_options['separator']) && !empty($separator_options['option'])) {
                        foreach ($separator_options['option'] as $table_column) {
                            $tags_concat = $data->{$table_column};
                            $tags_array_temp = explode($separator_options['separator'], $tags_concat);
                            $tags_array = array();

                            foreach ($tags_array_temp as $tag_temp) {
                                $tag_temp = trim($tag_temp);

                                if (!empty($separator_filter_type)) {
                                    if (!empty($separator_filter) && !in_array($tag_temp, $separator_filter)) {
                                        $tag_temp = '';
                                    }
                                } else {
                                    if (!empty($separator_filter) && in_array($tag_temp, $separator_filter)) {
                                        $tag_temp = '';
                                    }
                                }



                                if (!empty($tag_temp)) {
                                    if ('display' === $action) {
                                        $tags_array[] = $prefix . ' ' . $separator_prefix . ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji($tag_temp)));
                                    } else {
                                        $tags_array[] = $prefix . ' ' . $separator_prefix . $tag_temp;
                                    }
                                }
                            }

                            $tags = array_unique(array_merge($tags, $tags_array));
                        }
                    }
                }
            }
        }

        if (!empty($tags_multiple_connected) && 'detach_autotags' !== $type) {
            foreach ($tags_multiple_connected as $tags_multiple_item) {
                $single_tags_prefix = !empty($tags_multiple_item["single_tags_prefix"]) ? $tags_multiple_item["single_tags_prefix"] . ' ' : '';

                $separator_tags_prefix = !empty($tags_multiple_item["separator_tags_prefix"]) ? $tags_multiple_item["separator_tags_prefix"] . ' ' : '';
                $separator_tags_filter = !empty($tags_multiple_item["separator_tags_filter"]) ? explode('||', $tags_multiple_item["separator_tags_filter"]) : array();
                $separator_tags_filter_type = !empty($tags_multiple_item["separator_tags_filter_type"]) ? 1 : 0;

                if (!empty($separator_tags_filter)) {
                    foreach ($separator_tags_filter as $i => $filter) {
                        $separator_tags_filter[$i] = trim($filter);
                    }
                }

                $concat_tags_prefix = !empty($tags_multiple_item["concat_tags_prefix"]) ? $tags_multiple_item["concat_tags_prefix"] . ' ' : '';
                $concat_tags_filter = !empty($tags_multiple_item["concat_tags_filter"]) ? explode('||', $tags_multiple_item["concat_tags_filter"]) : array();
                $concat_tags_filter_type = !empty($tags_multiple_item["concat_tags_filter_type"]) ? 1 : 0;

                if (!empty($concat_tags_filter)) {
                    foreach ($concat_tags_filter as $i => $filter) {
                        $concat_tags_filter[$i] = trim($filter);
                    }
                }

                $multiple_auto_tags_allowed = false;

                if (!empty($tags_multiple_item['conditions'])) {
                    foreach ($tags_multiple_item['conditions'] as $condition) {
                        $condition_result = self::getConditionResult($data->{$condition['option']}, $condition['string'], $condition['operator']);

                        if ($condition_result) {
                            $multiple_auto_tags_allowed = true;
                        }
                    }
                } else {
                    $multiple_auto_tags_allowed = true;
                }

                if ($multiple_auto_tags_allowed) {
                    if (!empty($tags_multiple_item['single_tags'])) {
                        foreach ($tags_multiple_item['single_tags'] as $table_column) {

                            if (!empty($data->{$table_column}) && !empty(trim($data->{$table_column}))) {
                                $tags[] = $prefix . ' ' . $single_tags_prefix. ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($data->{$table_column}))));
                            }
                        }
                    }

                    if (!empty($tags_multiple_item['concat_tags'])) {
                        foreach ($tags_multiple_item['concat_tags'] as $table_column) {
                            $tags_concat = $data->{$table_column};
                            $tags_array_temp = explode(',', $tags_concat);
                            $tags_array = array();

                            foreach ($tags_array_temp as $tag_temp) {
                                $tag_temp = trim($tag_temp);

                                if ($concat_tags_filter_type) {
                                    if (!empty($concat_tags_filter) && !in_array($tag_temp, $concat_tags_filter)) {
                                        $tag_temp = '';
                                    }
                                } else {
                                    if (!empty($concat_tags_filter) && in_array($tag_temp, $concat_tags_filter)) {
                                        $tag_temp = '';
                                    }
                                }


                                if (!empty($tag_temp)) {
                                    $tags_array[] = $prefix . ' ' . $concat_tags_prefix . ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($tag_temp))));
                                }
                            }

                            $tags = array_unique(array_merge($tags, $tags_array));
                        }
                    }

                    if (!empty($tags_multiple_item['separator_tags'])) {
                        if (!empty($tags_multiple_item['separator_tags']['separator']) && !empty($tags_multiple_item['separator_tags']['option'])) {
                            foreach ($tags_multiple_item['separator_tags']['option'] as $table_column) {
                                $tags_concat = $data->{$table_column};
                                $tags_array_temp = explode($tags_multiple_item['separator_tags']['separator'], $tags_concat);
                                $tags_array = array();

                                foreach ($tags_array_temp as $tag_temp) {
                                    $tag_temp = trim($tag_temp);

                                    if (!empty($separator_tags_filter_type)) {
                                        if (!empty($separator_tags_filter) && !in_array($tag_temp, $separator_tags_filter)) {
                                            $tag_temp = '';
                                        }
                                    } else {
                                        if (!empty($separator_tags_filter) && in_array($tag_temp, $separator_tags_filter)) {
                                            $tag_temp = '';
                                        }
                                    }

                                    if (!empty($tag_temp)) {
                                        $tags_array[] = $prefix . ' ' . $separator_tags_prefix . ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($tag_temp))));
                                    }
                                }

                                $tags = array_unique(array_merge($tags, $tags_array));
                            }
                        }
                    }
                }
            }
        }

        return array_map('trim', $tags);
    }

    private static function getSeparators($table_column, $conditions) {
        $separators = array();

        if (!empty($conditions)) {
            foreach ($conditions as $separator) {
                if (in_array($table_column, $separator['option'])) {
                    $separators[] = $separator['separator'];
                }
            }
        }

        return $separators;
    }

    public static function getTagsIds($tag_names, $available_tags, $connector) {
        $ids = array();

        foreach ($tag_names as $tag_name) {

            $tag_name = ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji($tag_name)));
            $tag_name = str_replace('&#38;', '+', $tag_name);
            $tag_name = str_replace('&amp;', '+', $tag_name);
            $tag_name = str_replace('&', '+', $tag_name);

            $key = array_search(strtolower($tag_name), array_map('strtolower', $available_tags));

            if ($key !== false) {
                $ids[] = trim($key);
            } else {
                $tag_id = $connector->tag_create($tag_name);

                if ($tag_id) {
                    $ids[] = $tag_id;
                }
            }
        }

        return $ids;
    }

    private static function sanitizeUrlValue($value) {
        $value_array = parse_url ( $value );
        $value_to_check = '';

        if (!empty($value_array["host"])) {
            if (!empty($value_array["scheme"])) {
                $value_to_check .= $value_array["scheme"] . '://';
            }

            $value_to_check .= $value_array["host"];
        }

        if (filter_var($value_to_check, FILTER_VALIDATE_URL)) {
            $sanitized_value = $value;
        } else {
            $sanitized_value = '';
        }

        return $sanitized_value;
    }

    private static function sanitizeHtmlValue($value) {
        $sanitized_value = wpautop($value);

        return $sanitized_value;
    }

    private static function sanitizeValueByType($value, $type, $gmt = false, $action = 'display') {

		$log = array(
			'oldValue' => $value,
			'type' => $type,
			'gmt' => $gmt
		);

        $sanitized_value = '';

        if ('display' === $action) {
            $value = ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(ApiHelper::remove_wp_emoji(trim($value))));
        } else {
            $value = ApiHelper::filterBeforeOutput(ApiHelper::filterForbidenKTSymbols(trim($value)));
        }


        switch ($type) {
            case 'text':
                $sanitized_value = trim($value);

				// check for the date
				if ($date = DateTime::createFromFormat( 'Y.m.d', $value)) {
					$sanitized_value = $date->format('d.m.Y');
				}

				if ($date = DateTime::createFromFormat( 'Y-m-d', $value)) {
					$sanitized_value = $date->format('d.m.Y');
				}

				if ($date = DateTime::createFromFormat( 'Y-m-d', $value)) {
					$sanitized_value = $date->format('d.m.Y');
				}

				if ($date = DateTime::createFromFormat( 'Y-m-d', $value)) {
					$sanitized_value = $date->format('d.m.Y');
				}

                break;
            case 'html':
                $sanitized_value = wpautop($value);
                break;
            case 'number':
                $sanitized_value = floatval($value);
                $sanitized_value = round($sanitized_value, 0, PHP_ROUND_HALF_UP);
                $sanitized_value = (int) $sanitized_value;
                break;
            case 'decimal':
                $sanitized_value = floatval($value);
                $sanitized_value = round($sanitized_value, 2, PHP_ROUND_HALF_UP) * 100;
                break;
            case 'date':
                $time_zone = StatisticsManager::getTimeZone();
                $german = new DateTime(date('Y-m-d'), new DateTimeZone('Europe/Berlin'));
                $german_offset = date_offset_get($german);

                $value = self::grab_date_time_from_string($value);
                $date = self::get_date_from_string($value);

                if ($date) { // in WP time
                    $sanitized_value = date_timestamp_get($date);
					if (!empty($gmt['local_to_gmt'])) $sanitized_value = $sanitized_value - self::getServerToUTCOffset();
                    elseif (!empty($gmt['gmt_to_local'])) $sanitized_value = $sanitized_value + self::getServerToUTCOffset();
                } else {
                    $sanitized_value = '';
                }

                break;
			case 'time':
				$date = strtotime($value); // not utc!

				if ($date) {
					if ($gmt) {
                        if (!empty($gmt['local_to_gmt'])) $sanitized_value = $date - self::getServerToUTCOffset();
                        elseif (!empty($gmt['gmt_to_local'])) $sanitized_value = $date + self::getServerToUTCOffset();
					} else {
						$sanitized_value = $date; // WP timezone
					}
				}

				$new_date = date_create_from_format('U', $sanitized_value);

				if ($new_date) {
					$sanitized_value = (int)$new_date->format('G') * 60 * 60 + (int)$new_date->format('i') * 60 + (int)$new_date->format('s');
				}

				break;
            case 'url':
                $value_array = parse_url ( $value );
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    $sanitized_value = $value;
                } else {
                    $sanitized_value = '';
                }
                break;
			case 'datetime':
			    if (empty($value)) {
                    $sanitized_value = '';
                } else {
                    $german = new DateTime(date('Y-m-d'), new DateTimeZone('Europe/Berlin'));
                    $german_offset = date_offset_get($german);

                    $value = (int)$value;
                    $sanitized_value = $value;
                    if (!empty($gmt['local_to_gmt'])) $sanitized_value = $sanitized_value - self::getServerToUTCOffset();
                    elseif (!empty($gmt['gmt_to_local'])) $sanitized_value = $sanitized_value + self::getServerToUTCOffset();
                }
				break;
        }

		$log['newValue'] = (string) $sanitized_value;

        return (string) $sanitized_value;
    }

    public static function get_date_from_string($value) {
        $date = self::get_date_from_date_time_string($value);

        if (!$date) $date = self::get_date_from_date_string($value);
        if (!$date) $date = DateTime::createFromFormat( 'U', trim($value), new DateTimeZone('Europe/Berlin') );

        return $date;
    }

    public static function get_date_from_date_time_string($value) {
        $date = false;

        foreach (self::get_date_time_formats() as $dtstring) {
            $date = DateTime::createFromFormat( $dtstring, trim($value), new DateTimeZone('Europe/Berlin') );
            if ($date) break;
        }

        return $date;
    }

    public static function get_date_from_date_string($value) {
        $date = false;

        foreach (self::get_date_formats() as $dtstring) {
            $date = DateTime::createFromFormat( $dtstring, trim($value), new DateTimeZone('Europe/Berlin') );
            if ($date) break;
        }

        return $date;
    }

    public static function get_allowed_dt_formats() {
        $all_formats = array_merge(self::get_date_time_formats(), self::get_date_formats());
        $all_formats[] = 'U';
        return $all_formats;
    }

    public static function get_date_formats() {
        return array(
            'Y-m-d',
            'Y.m.d',
            'Y/m/d',
            'd.m.Y',
            'm/d/Y',
            'd/m/Y',
        );
    }

    public static function get_date_time_formats() {
        return array(
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'd.m.Y H:i:s',
            'd.m.Y H:i',
            'Y.m.d H:i:s',
            'Y.m.d H:i',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'Y/m/d H:i:s',
            'Y/m/d H:i',
        );
    }

    public static function get_date_regex() {
        return array(
            '/([0-9]{4})-([0-9]{2})-([0-9]{2})/',
            '/([0-9]{2}).([0-9]{2}).([0-9]{4})/',
            '/([0-9]{4}).([0-9]{2}).([0-9]{2})/',
            '/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/',
            '/([0-9]{4})\/([0-9]{2})\/([0-9]{2})/',
        );
    }

    public static function get_time_regex() {
        return array(
            '/([0-9]{2}):([0-9]{2}):([0-9]{2})/',
            '/([0-9]{2}):([0-9]{2})/',
        );
    }

    public static function grab_date_time_from_string($string) {
        $date_matches = array();
        $date_string = $string;

        foreach (self::get_date_regex() as $date_regex) {
            preg_match($date_regex, $string, $date_matches);

            if (!empty($date_matches[0])) {
                break;
            }
        }

        $time_matches = array();

        foreach (self::get_time_regex() as $time_regex) {
            preg_match($time_regex, $string, $time_matches);

            if (!empty($time_matches[0])) {
                break;
            }
        }

        if (!empty($date_matches[0])) {
            $date_string = $date_matches[0];

            if (!empty($time_matches[0])) {
                $date_string .= ' '. $time_matches[0];
            }
        }

        return $date_string;
    }

        // return timestamp from 2 date and time fields of false
	private static function splitDateTimeField($table_columns, $item, $type) {
		// check type
		if ($type !== 'datetime' || !is_array($table_columns) || empty($table_columns)) return false;

		$timestamp = false;
		$time = false;
		$date = false;

		if (1 === count($table_columns) && !empty($item->{$table_columns[0]})) {
            $value = $item->{$table_columns[0]};
            $value = self::grab_date_time_from_string($value);
            $date = self::get_date_from_string($value);
            if ($date) return date_timestamp_get($date);

            return false;
        }

		foreach ($table_columns as $table_column) {
			if (!empty($item->{$table_column})) {
				$value = self::grab_date_time_from_string($item->{$table_column});

				// Check if value already date time
				$date_time = self::get_date_from_date_time_string($value);
                if ($date_time) return date_timestamp_get($date_time);

                // Check if value date
                if (!$date) {
                    $date = self::get_date_from_string($value);

                    if ($date) continue;
                }

				// search for the time
                if (!$time) {
                    $t_time = explode(':', $value);

                    if (count($t_time) == 2 ||  count($t_time) == 3) {
                        // check hours
                        if ((int)$t_time[0] > -1 && (int)$t_time[0] < 24 ) {
                            // check minutes
                            if ((int)$t_time[1] > -1 && (int)$t_time[1] < 60 ) {
                                // check seconds
                                if (count($t_time) == 2) {
                                    $t_time[2] = "00";
                                    $time = $t_time;
                                    // $time = $t_time[0] . ':' . $t_time[1] . ':00';
                                } elseif ((int)$t_time[2] > -1 && (int)$t_time[2] < 60) {
                                    $time = $t_time;
                                    // $time = $t_time[0] . ':' . $t_time[1] . ':' . $t_time[2];
                                }
                            }
                        }
                    }
                }
			}
		}

		if ($date && $time) {
			//$datetime = date_create_from_format('U', $date); // WP timezone
            $date->setTime($time[0],$time[1],$time[2]);
            return date_timestamp_get($date);
		} else if ($date) {
		    return $date;
		}

		return $timestamp;
	}

    public static function getMinutes() {
        $minutes = array();

        for ($i = 1; $i < self::$minutes_number; $i++) {
            $minutes[] = $i;
        }

        return $minutes;
    }

    public static function getHours() {
        $hours = array();

        for ($i = 1; $i < self::$hours_number; $i++) {
            $hours[] = $i;
        }

        return $hours;
    }

    public static function getDays() {
        $days = array();

        for ($i = 1; $i <= self::$days_number; $i++) {
            $days[] = $i;
        }

        return $days;
    }

    public static function getWeekdays() {
        $weekdays = array(
            __( 'Monday', 'wp2leads' ),
            __( 'Tuesday', 'wp2leads' ),
            __( 'Wednesday', 'wp2leads' ),
            __( 'Thusday', 'wp2leads' ),
            __( 'Friday', 'wp2leads' ),
            __( 'Saturday', 'wp2leads' ),
            __( 'Sunday', 'wp2leads' )
        );

        return $weekdays;
    }

    public static function getMonths() {
        $months = array(
            __( 'January', 'wp2leads' ),
            __( 'February', 'wp2leads' ),
            __( 'March', 'wp2leads' ),
            __( 'April', 'wp2leads' ),
            __( 'May', 'wp2leads' ),
            __( 'June', 'wp2leads' ),
            __( 'July', 'wp2leads' ),
            __( 'August', 'wp2leads' ),
            __( 'September', 'wp2leads' ),
            __( 'October', 'wp2leads' ),
            __( 'November', 'wp2leads' ),
            __( 'December', 'wp2leads' )
        );

        return $months;
    }

    public static function getWoocommerceOrderStatuses() {
        $statuses = array();

        if (function_exists('wc_get_order_statuses')) {
            $statuses = array_keys(wc_get_order_statuses());
        }

        return $statuses;
    }

    public static function getDetachTags($map_id) {
        global $wpdb;
        $table = $wpdb->get_row(sprintf("SELECT * FROM %s WHERE id=%d", $wpdb->prefix . "wp2l_maps",(int)$map_id) );
        $api = unserialize($table->api);
        $tags = array();

        if (isset($api['detach_tags']['tag_ids'])) {
            $tags = array_merge($tags, $api['detach_tags']['tag_ids']);
        }

        return $tags;
    }

    public static function isValidTimeStamp($timestamp)  {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    public static function convertTimeToLocal($time) {
        $format = 'Y-m-d H:i:s';
        $time_zone = StatisticsManager::getTimeZone();;
        $dt = new DateTime();
        $dt->setTimestamp($time);
        $dt->setTimezone(new DateTimeZone($time_zone));
        return $dt->format($format);
    }

    public static function setSubscribersCounter($map_id, $values) {
        $counter = array(
            'unique' => !empty($values['unique']) ? $values['unique'] : 0
        );

        $all_counters = json_decode(get_option(self::$unique_subscribers_option_name), true);

        if (!empty($all_counters[$map_id])) {
            foreach ($all_counters[$map_id] as $key => $value) {
                $all_counters[$map_id][$key] = $value + $counter[$key];
            }
        } else {
            $all_counters[$map_id] = $counter;
        }

        update_option(self::$unique_subscribers_option_name, json_encode($all_counters));
    }

    public static function getSubscribersCounter($map_id) {
        $counter = array(
            'unique' => 0
        );

        $all_counters = json_decode(get_option(self::$unique_subscribers_option_name), true);

        if (!empty($all_counters[$map_id])) {
            $counter = $all_counters[$map_id];
        }

        return $counter;
    }

	// UTC timestamp + getServerToUTCOffset() = current timestamp in WP time
	public static function getServerToUTCOffset() {
		$this_tz = new DateTimeZone(get_option('timezone_string'));
		$now = new DateTime("now", $this_tz);
		return $this_tz->getOffset($now);
	}

	public static function endswith($haystack, $needle) {
		$strlen = strlen($haystack);
		$testlen = strlen($needle);
		if ($testlen > $strlen) return false;
		return substr_compare($haystack, $needle, $strlen - $testlen, $testlen) === 0;
	}

	public static function exclude_cf7_wrong_table_columns( $excluded_columns, $decodedMap, $all_columns ) {
		if ( empty($decodedMap['form_code']) || empty($decodedMap['selects_only'])) return $excluded_columns; // only for "form" maps

		if ( ! is_plugin_active('contact-form-entries/contact-form-entries.php') ) return $excluded_columns; // no vxcf plugin activated

		$form_fields = vxcf_form::get_form_fields($decodedMap['form_code']);

		foreach ( $all_columns as $column ) {
			if ( in_array($column, $decodedMap['selects_only']) ) {
				if ( strpos($column, '_detail-') === FALSE ) {
					continue;
				} else { // if it is a string about form input
					if (isset( $form_fields[str_replace('v.vxcf_leads_detail-', '', $column)]) ) continue; // and this input exist now in the form
				}
			}

			if ( in_array($column, $excluded_columns) ) continue;

			$excluded_columns[] = $column;
		}

		return $excluded_columns;
	}
}
