<?php
/**
 * Global debug methods
 */
namespace al13_debug\util\adapters;

use \lithium\analysis\Logger;

/**
 * Requires li3 logging enabled
 */
class Li3FirePHP {

    public static function dump_array(array $array, $debug) {
		return $array;
    }

    public static function dump_object($obj, $debug) {
		return $obj;
    }

    public static function dump_other($var) {
        $type = gettype($var);
        switch ($type) {
            case 'boolean': $var = $var ? 'true' : 'false'; break;
            case 'string' : $size = strlen($var); $var = '\'' . htmlentities($var) . '\' ][ ' . $size; break;
            case 'NULL' : return '[ NULL ]'; break;
        }
        return '[ ' . $type . ' ][ ' . $var . ' ] ' . "\n" ;
    }

    public static function locationString($location) {
        extract($location);
        $ret = $file . ' - line ' . $line ;
        $ret .= isset($class) ? ' - class ' . $class : '';
        $ret .= isset($function) && $function != 'include' ? ' - function ' . $function :'';
        return $ret;
    }

}
