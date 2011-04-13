<?php
/**
  * Global convenience methods
  */

// Debug dump any amount of variable
function d() {
    $debug = \al13_debug\util\Debug::get_instance();
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
    $debug = \al13_debug\util\Debug::get_instance();
    $args = func_get_args();
    $trace = debug_backtrace();
    $split = true;
    if (count($args) == 1) {
        $split = false;
        $args = $args[0];
    }

    ob_end_clean();
    $debug->dump($args, compact('trace', 'split'));
    die('<div style="margin-top: 25px;font-size: 10px;color: #500;">-Debug die-</div>');
}

// Convenient way of adding / setting a Debug setting
function ds($setting, $value) {
    \al13_debug\util\Debug::$options[$setting] = $value;
}
function dsb($value, $category = 'objects') {
    if (is_array($value)) {
        \al13_debug\util\Debug::$options['blacklist'][$category] = $value;
        return;
    }
    \al13_debug\util\Debug::$options['blacklist'][$category][] = $value;
}

// Extra short way of adding a blacklisted object property
function dbk($value) {
    $category = 'key';
    dsb($value, $category);
}
// Extra short way of adding a blacklisted object property
function dbp($value) {
    $category = 'property';
    dsb($value, $category);
}
// Extra short way of adding a blacklisted class instance
function dbc($value) {
    $category = 'class';
    dsb($value, $category);
}

// Convenient wrapper for other \util\Debug methods
function dw($method, $echo = true) {
    $debug = \al13_debug\util\Debug::get_instance();
    $result = $debug->$method();
    if (!$echo) return $result;
    $debug->dump($result);
}

