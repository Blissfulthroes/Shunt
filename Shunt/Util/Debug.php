<?php

namespace Shunt\Util {

    defined('SHUNT_ACCESS_SECURED') or header('location: /403-Forbidden');

    class Debug {

        /**
         * Log an activity
         *
         * @param string $entry
         * @param string $filename 
         * @return void
         */
        public function log($entry, $filename) {
            $handle = fopen($filename, 'ab');
            $entry = 'Entry ' . date('g:i:s a') . ' >> ' . $entry . "\n";
            fwrite($handle, $entry);
            fclose($handle);
        }

        /**
         * Print human-readable information
         * 
         * @param string $input 
         * @return void
         */
        public function pre($input) {
            echo '<pre>' . print_r($input, 1) . '</pre>';
        }

        public function dump($input) {
            echo '<pre>';
            var_dump($input);
            echo '</pre>';
        }

        /**
         * Logs messages/variables/data to browser console from within php
         *
         * @param $name: message to be shown for optional data/vars
         * @param $data: variable (scalar/mixed) arrays/objects, etc to be logged
         * @param $jsEval: whether to apply JS eval() to arrays/objects
         *
         * @return none
         * @author Sarfraz
         */
        public function console($data = null, $label = null, $js_print_r = false) {
            $trace = debug_backtrace();
            $trace = str_replace('\\', '/', $trace[0]['file']) . ' on line ' . $trace[0]['line'];
            $signature = \Shunt::SIGNATURE . ' ' . \Shunt::VERSION;
            $label = (is_null($label)) ? 'Debug Output' : $label;

            $is_evaled = false;
            if ($js_print_r) {
                $data = self::print_rToJs(print_r($data, 1));
                $data = json_encode($data, 0);
            } else {
                $data = self::print_rToArray(print_r($data, 1));
                $data = 'eval(' . json_encode($data) . ')';
                $is_evaled = true;
            }

            # sanitalize
            $data = $data ? $data : '';
            $search_array = array("#'#", '#""#', "#''#", "#\n#", "#\r\n#");
            $replace_array = array('"', '', '', '\\n', '\\n');
            $data = preg_replace($search_array, $replace_array, $data);
            $data = ltrim(rtrim($data, '"'), '"');
            $data = $is_evaled ? $data : ($data[0] === "'") ? $data : "'" . $data . "'";

            $js = <<<JSCODE
\n<script>
     // fallback - to deal with IE (or browsers that don't have console)
     if (! window.console) console = {};
     console.log = console.log || function(name, data){};
     // end of fallback
 
     console.log('[$label > $signature > $trace]');
     console.log($data);
</script>
JSCODE;

            echo $js;
        }

        public static function print_rToJs($in) {
            $lines = explode("\n", trim($in));
            if (trim($lines[0]) != 'Array') {
                // bottomed out to something that isn't an array 
                return $in;
            } else {
                // this is an array, lets parse it 
                if (preg_match("/(\s{5,})\(/", $lines[1], $match)) {
                    // this is a tested array/recursive call to this function 
                    // take a set of spaces off the beginning 
                    $spaces = $match[1];
                    $spaces_length = strlen($spaces);
                    $lines_total = count($lines);
                    for ($i = 0; $i < $lines_total; $i++) {
                        if (substr($lines[$i], 0, $spaces_length) == $spaces) {
                            $lines[$i] = substr($lines[$i], $spaces_length);
                        }
                    }
                }
                array_shift($lines); // Array 
                array_shift($lines); // ( 
                array_pop($lines); // ) 
                $in = implode("\n", $lines);
                // make sure we only match stuff with 4 preceding spaces (stuff for this array and not a nested one) 
                preg_match_all("/^\s{4}\[(.+?)\] \=\> /m", $in, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
                $pos = array();
                $previous_key = '';
                $in_length = strlen($in);
                // store the following in $pos: 
                // array with key = key of the parsed array's item 
                // value = array(start position in $in, $end position in $in) 
                foreach ($matches as $match) {
                    $key = $match[1][0];
                    $start = $match[0][1] + strlen($match[0][0]);
                    $pos[$key] = array($start, $in_length);
                    if ($previous_key != '')
                        $pos[$previous_key][1] = $match[0][1] - 1;
                    $previous_key = $key;
                }
                $ret = array();
                foreach ($pos as $key => $where) {
                    // recursively see if the parsed out value is an array too 
                    $ret[$key] = self::print_rToJs(substr($in, $where[0], $where[1] - $where[0]));
                }
                return $ret;
            }
        }

        public static function print_rToArray($printr) {
            $newarray = array();
            $a[0] = &$newarray;
            if (preg_match_all('/^\s+\[(\w+).*\] => (.*)\n/m', $printr, $match)) {
                foreach ($match[0] as $key => $value) {
                    (int) $tabs = substr_count(substr($value, 0, strpos($value, "[")), "        ");
                    if ($match[2][$key] == 'Array' || substr($match[2][$key], -6) == 'Object') {
                        $a[$tabs + 1] = &$a[$tabs][$match[1][$key]];
                    } else {
                        if (is_null($match[2][$key])) {
                            $val = null;
                        } else if (is_bool($match[2][$key])) {
                            $val = (bool) $match[2][$key];
                        } else if (empty($match[2][$key])) {
                            $val = (string) " ";
                        } else {
                            $val = $match[2][$key];
                        }
                        $a[$tabs][$match[1][$key]] = str_replace('\\', '/', $val); // fix any windows paths
                    }
                }
            }
            return $newarray;
        }

    }

}