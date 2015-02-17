<?php
    class BogoblastUtil {
        
        public static function random_string($length = 24, $character_set = 'alphanumeric_upper') {
            
            switch($character_set) {
                case 'readable':
                    $possible = '2346789BCDFGHJKLMNPQRTVWXYZ';
                    break;
                case 'alphanumeric':
                    $possible = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                case 'alphanumeric_upper':
                    $possible = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    break;
                case 'alphanumeric_lower':
                    $possible = '1234567890abcdefghijklmnopqrstuvwxyz';
                    break;
                case 'extended':
                    $possible = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()[]{}<>?-=_+~';
                    break;
                default:
                    throw new Exception('Unknown character set.');
            }
            $random_string = '';
            for($i = 0; $i < $length; $i++) {
                $random_char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
                $random_string .= $random_char;
            }
            return $random_string;
        }
        
        public static function current_url() {
            $url = 'http';
            if ($_SERVER["HTTPS"] == "on") {$url .= "s";}
            $url .= "://";
            if ($_SERVER["SERVER_PORT"] != "80") {
                $url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            return $url;
        }
        
        public static function validate_as($type, $string, $empty_ok = true) {
            $string = trim($string);
            if(strlen($string) == 0 && $empty_ok) return true;
            
            switch($type) {
                case 'zip':
                    $regex = "(^\d{5}(-\d{4})?$)";
                    break;
                case 'email':
                    $regex = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
                    break;
                default:
                    $regex = $type;
            }
            
            return !(preg_match($regex, $string) == 0);
        }
        
        public static function format_phone($phone_number, $format = 'human') {
            $phone_number = trim($phone_number);
            
            switch($format) {
                case 'human':
                    $format_string = '($1) $2-$3';
            }
            
            return preg_replace('~(\d{3})[^\d]*(\d{3})[^\d]*(\d{4})$~', $format_string, $phone_number);
        }
        
        public static function strip_nonnumeric_chars($string, $strip_decimal = false, $strip_hyphen = false) {
            
            if($strip_decimal && $strip_hyphen) {
                $regex = "/[^0-9]/";
            } elseif($strip_decimal) {
                $regex = "/[^0-9\-]/";
            } elseif($strip_hyphen) {
                $regex = "/[^0-9.]/";
            } else {
                //strip neither
                $regex = "/[^0-9.\-]/";
            }
            
            return preg_replace($regex, "", $string);
        }
        
        public static function us_states($index = null) {
            $state_list = array(
                'AL'=>"Alabama",  
                'AK'=>"Alaska",  
                'AZ'=>"Arizona",  
                'AR'=>"Arkansas",  
                'CA'=>"California",  
                'CO'=>"Colorado",  
                'CT'=>"Connecticut",  
                'DE'=>"Delaware",  
                'DC'=>"District Of Columbia",  
                'FL'=>"Florida",  
                'GA'=>"Georgia",  
                'HI'=>"Hawaii",  
                'ID'=>"Idaho",  
                'IL'=>"Illinois",  
                'IN'=>"Indiana",  
                'IA'=>"Iowa",  
                'KS'=>"Kansas",  
                'KY'=>"Kentucky",  
                'LA'=>"Louisiana",  
                'ME'=>"Maine",  
                'MD'=>"Maryland",  
                'MA'=>"Massachusetts",  
                'MI'=>"Michigan",  
                'MN'=>"Minnesota",  
                'MS'=>"Mississippi",  
                'MO'=>"Missouri",  
                'MT'=>"Montana",
                'NE'=>"Nebraska",
                'NV'=>"Nevada",
                'NH'=>"New Hampshire",
                'NJ'=>"New Jersey",
                'NM'=>"New Mexico",
                'NY'=>"New York",
                'NC'=>"North Carolina",
                'ND'=>"North Dakota",
                'OH'=>"Ohio",  
                'OK'=>"Oklahoma",  
                'OR'=>"Oregon",  
                'PA'=>"Pennsylvania",  
                'RI'=>"Rhode Island",  
                'SC'=>"South Carolina",  

                'SD'=>"South Dakota",
                'TN'=>"Tennessee",  
                'TX'=>"Texas",  
                'UT'=>"Utah",  
                'VT'=>"Vermont",  
                'VA'=>"Virginia",  
                'WA'=>"Washington",  
                'WV'=>"West Virginia",  
                'WI'=>"Wisconsin",  
                'WY'=>"Wyoming");
            
            if($index !== null) {
                if(array_key_exists($index, $state_list)) {
                    return $state_list[$index];
                } elseif(array_key_exists($index, $flipped = array_flip($state_list))) {
                    return $flipped[$index];
                } else {
                    return null;
                }
            }
            
            return $state_list;
        }
        
        public static function format_date($timestamp) {
            return date_i18n('Y-m-d h:i:s a', $timestamp);
        }
        
        public static function set_url_query_value($url, $name, $value = null) {
            // This function will take a URL, break it apart, and
            // make sure that if $name is already present, $value is replaced.
            // If $name is not present, it is added.
            // If $value is null, the pair will be removed from the URL if present.
            // $name and $value can be provided as arrays, where their respective
            // indices are used ($name[0] for $value[0], etc.)
            
            
            //1. Fields can be separated by semicolons also, not just the &, thus:
                //field1=val1;field2=val2 
                //is a valid query, with two fields. So instead exploding on & you should split like:
                // $queryFields = split('[;&]', $data['query']);
            //2. Fields don't necessarily get values. As seen on many places, there are URLs like
                //page.php?fail
                //So you should test if you get a second return value after exploding on equal sign
            
            $url_array = explode('#', $url);
            $host_and_path_and_query = array_shift($url_array);
            $fragment = implode('#', $url_array);
            
            $host_and_path_and_query_array = explode('?', $host_and_path_and_query);
            $host_and_path = array_shift($host_and_path_and_query_array);
            $query_string = implode('&', $host_and_path_and_query_array); //if there was more than one '?', it's gone now.
            
            $query_pairs = explode('&', $query_string);
            $query_vars = array();
            foreach($query_pairs as $pair) {
                $tmp = explode('=', $pair);
                $query_vars[$tmp[0]] = $tmp[1];
            }
            
            $name = is_array($name) ? $name : array($name);
            foreach($name as $i => $n) {
                if(is_array($value) && isset($value[$i]) && $value[$i] !== null) {
                    $query_vars[$n] = urlencode($value[$i]);
                } elseif(!is_array($value) && $value !== null) {
                    $query_vars[$n] = urlencode($value);
                } else {
                    unset($query_vars[$n]);
                }
            }
            
            $return_url_array = array();
            foreach($query_vars as $n => $v) {
                if(strlen($n) > 0) $return_url_array[] = "$n=$v";
            }
            
            $return_url = $host_and_path;
            if(sizeof($return_url_array) > 0) $return_url .= '?'.implode('&', $return_url_array);
            if(strlen($fragment) > 0) $return_url .= '#'.$fragment;
            
            return $return_url;
        }
        
        public static function geocode_address($address) {
            
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($address);
            $response_body = wp_remote_retrieve_body( wp_remote_get($url));
            if(strlen($response_body) > 0) {
                $response = json_decode($response_body);
                
                $lat = null;
                $lng = null;
                if(is_array($response->results) && sizeof($response->results) > 0) {
                    $geocode_result = array_shift($response->results);
                    if(isset($geocode_result->geometry) && isset($geocode_result->geometry->location)) {
                        if(isset($geocode_result->geometry->location->lat)) $lat = $geocode_result->geometry->location->lat;
                        if(isset($geocode_result->geometry->location->lng)) $lng = $geocode_result->geometry->location->lng;
                    }
                }
                
                if($lat !== null & $lng !== null) {
                    return array('lat' => $lat, 'lng' => $lng);
                }
            }
            
            return null;
        }
        
        public static function async_request($url) {
            return wp_remote_get($url, array(
                'method' => 'GET',
                'timeout' => 5,
                'redirection' => 5,
                'user-agent' => 'bogoblast',
                'blocking' => false,
                'compress' => true,
                'decompress' => false,
                'sslverify' => false
            ));
        }
    }
?>