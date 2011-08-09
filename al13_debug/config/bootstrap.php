<?php
/**
  * Global convenience methods
  */

use al13_debug\util\Debug;

// Li3 specific code for loading defaults, disable if not used with Li3
$config = lithium\core\Libraries::get('al13_debug');
if (isset($config['defaults']) && !empty($config['defaults'])) {
	Debug::$defaults = $config['defaults'] + Debug::$defaults;
}

/**
 * Dump any amount of paramters in a html styled var_dump
 * 
 * @param mixed any amount
 */
function d() {
    $debug = Debug::get_instance();
    $args = func_get_args();
    $trace = debug_backtrace();
    $split = true;
    if (count($args) == 1) {
        $split = false;
        $args = $args[0];
    }
    $debug->dump($args, compact('trace', 'split'));
};

// Debug dump any amount of variables and then die()
function dd() {
    $debug = Debug::get_instance();
    $args = func_get_args();
    $trace = debug_backtrace();
    $split = true;
    if (count($args) == 1) {
        $split = false;
        $args = $args[0];
    }
	$echo = true;
    @ob_end_clean();
    $debug->dump($args, compact('trace', 'split', 'echo'));
	if (!empty($debug->output)) {
		dout();
	}
    die('<div style="margin-top: 25px;font-size: 10px;color: #500;">-Debug die-</div>');
}

/**
 * Convenient way of adding / setting a Debug setting
 * 
 * @param string $setting Name/Key of Debug config/setting to set
 * @param mixed $value Value
 */
function ds($setting, $value) {
    Debug::$defaults[$setting] = $value;
}

/**
 * Conventience method for adding to blacklist
 * If an array it will overwrite the category with the supplied array
 * 
 * @param mixed $value Value add to specified blacklist category or array to set the entire category
 * @param string $category Name of category of blacklist that is being modified, 'property' by default
 */
function dsb($value, $category = 'property') {
    if (is_array($value)) {
        Debug::$defaults['blacklist'][$category] = $value;
        return;
    }
    Debug::$defaults['blacklist'][$category][] = $value;
}

/**
 * Extra short way of adding a blacklisted array key
 *
 * @param mixed $value Name of array key to black list, or array of
 */
function dbk($value) {
    $category = 'key';
    dsb($value, $category);
}

/**
 * Extra short way of adding a blacklisted object property
 * 
 * @param mixed $value Name of object property to blacklist, or array of
 */
function dbp($value) {
    $category = 'property';
    dsb($value, $category);
}

/**
 * Extra short way of blacklisting classes
 *
 * @param mixed $value Name of class, of which objects will be blacklisted, or array of
 */
function dbc($value) {
    $category = 'class';
    dsb($value, $category);
}

/**
 * Convenient wrapper for other \util\Debug methods
 * 
 * @param string $method Name of method to call on the Debug obect
 * @param boolean $echo True will echo, false will return result
 */
function dw($method) {
    $args = func_get_args();
    $trace = debug_backtrace();
    $split = true;
    if (count($args) == 1) {
        $split = false;
        $args = $args[0];
    }
    $debug = Debug::get_instance();
    $result = $debug->$method();
    $debug->dump($result, compact('trace', 'split'));
}

/**
 * Output any stored up debugging from using the echo false option
 *
 * @param int $key array key of the output to put out
 */
function dout($key = null, $incStyle = true) {
    $debug = Debug::get_instance();
	if ($key && $incStyle) {
		$debug->out(0);
	}
	$debug->out($key);
}

/**
 * Returns array of output without the leading style html tag
 *
 * Place in media handler for json as part of the container
 *
 * @param string $key
 * @return string
 */
function daout($key = null) {
    $debug = Debug::get_instance();
	return $debug->array_out($key);
}
