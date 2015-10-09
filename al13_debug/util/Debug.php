<?php
/**
 * Global debug methods
 */
namespace al13_debug\util;

class Debug {

    public static $defaults = array(
        'echo' => true,
        'mode' => 'Html',
        'depth' => 10,
        'avoid' => array(),
        'docroot' => '/web',
        'blacklist' => array(
            'class' => array(),
            'property' => array(),
            'key' => array()
        )
    );

    protected static $__instance = null;

    public $current_depth;
    public $object_references;
    public $options;
    public $output = array('<style type="text/css">@import url("/al13/css/debug.css");</style>');

	/**
	 * Get the singleton instance
	 *
	 * @return al13_debug\util\Debug
	 */
    public static function get_instance() {
        if (!static::$__instance) {
            $class = __CLASS__;
            static::$__instance = new $class();
        }
        return static::$__instance;
    }

	/**
	 * Dump
	 *
	 * @param mixed $var
	 * @param array $options
	 */
    public function dump($var, $options = array()) {
        $options += self::$defaults + array('split' => false, 'trace' => false);
        $this->options = $options;
        $this->current_depth = 0;
        $this->object_references = array();

        if (!$options['trace']) $options['trace'] = debug_backtrace();
        extract($options);
        $location = $this->location($trace);

        $dump = array();
        if ($options['split'] && is_array($var)) {
            $this->current_depth = 0;
            foreach ($var as $one) {
				$dump = array_merge($dump, array($this->dump_it($one), ' - '));
			}
			$dump = array_slice($dump, 0, -1);
        } else
            $dump[] = $this->dump_it($var);

        switch ($mode) {
			case 'FirePHP':
				$locString = \al13_debug\util\adapters\FirePHP::locationString($location);
				require_once LITHIUM_LIBRARY_PATH . '/FirePHPCore/FirePHP.class.php';
				$firephp = \FirePHP::getInstance(true);
				if (!$firephp) throw new \Exception('FirePHP not installed');
				$firephp->group($locString, array('Collapsed' => false, 'Color' => '#AA0000'));

				$f = function($o) use ($firephp,&$f) {
					if (is_array($o)) {
						foreach ($o as $r)
							$f($r);
					} else {
						$firephp->log($o);
					}
				};
				$f($dump);
				$firephp->groupEnd();
				return;
				break;
			case 'Json':
				$locString = \al13_debug\util\adapters\Json::locationString($location);
				$this->output[] = array('location' => $locString, 'dump' => $dump);
				break;
			case 'Log' :
				$locString = \al13_debug\util\adapters\Log::locationString($location);
				\lithium\analysis\Logger::debug($locString . "\n" . implode("\n", $dump));
				return;
				break;
			case 'Html' :
			default :
				$locString = \al13_debug\util\adapters\Html::locationString($location);
				$this->output[] = '<div class="debug-dump"><div class="debug-location">' . $locString . '</div>'.
					'<div class="debug-content"> ' . implode("<br>\n", $dump) . '</div></div>';
				break;
		}
		if ($options['echo']) {
			$this->__out();
		}
    }

	/**
	 * Return output dump as array
	 *
	 * @param string $key
	 * @return array
	 */
	public function array_out($key = null) {
		if (count($this->output) < 2 || ($key && !isset($this->output[$key]))) {
			return array();
		}
		if ($key) {
			return $this->output[$key];
		}
		array_shift($this->output);
		return $this->output;
	}

	/**
	 * Echo out stored debugging
	 *
	 * @param int $key
	 */
	public function out($key = null) {
		if ($this->options['mode'] == 'Html') {
			\al13_debug\util\adapters\Html::top($this->output, $key);
			return;
		}
		$this->__out($key);
	}

	private function __out($key = null) {
		if ($key !== null) {
			if (!isset($this->output[$key])) {
				throw new Exception('DEBUG: Not that many outputs in buffer');
			}
			echo $this->output[$key];
			return;
		}
		foreach ($this->output as $out) {
			echo $out;
		}
		$this->output = array();
	}

	/**
	 * Grab global defines, will start at 'FIRST_APP_CONSTANT', if defined
	 *
	 * @return type
	 */
    public function defines() {
        $defines = get_defined_constants();
        $ret = array(); $offset = -1;
        while ($def = array_slice($defines, $offset--, 1)) {
            $key = key($def);
            $value = current($def);
            if ($key  == 'FIRST_APP_CONSTANT') break;
            $ret[$key ] = $value;
        }
        return $ret;
    }

	/**
	 * Send a variable to the adapter and return it's formated output
	 *
	 * @param mixed $var
	 * @return string
	 */
    public function dump_it($var) {
		$adapter = '\al13_debug\util\adapters\\'. $this->options['mode'];
        if (is_array($var))
            return $adapter::dump_array($var, $this);
        elseif (is_object($var))
            return $adapter::dump_object($var, $this);
        else
            return $adapter::dump_other($var);
    }

	/**
	 * Create an array that describes the location of the debug call
	 *
	 * @param string $trace
	 * @return array
	 */
    public function location($trace) {
        $root = substr($_SERVER['DOCUMENT_ROOT'], 0 , strlen(static::$defaults['docroot']) * -1);
        $file = implode('/', array_diff(explode('/', $trace[0]['file']), explode('/', $root)));
        $ret = array(
            'file' => $file,
            'line' => $trace[0]['line']
        );
        if (isset($trace[1]['function'])) $ret['function'] = $trace[1]['function'];
        if (isset($trace[1]['class'])) $ret['class'] = $trace[1]['class'];
        return $ret;
    }

    public function trace() {
        $root = substr($_SERVER['DOCUMENT_ROOT'], 0 , strlen(static::$defaults['docroot']) * -1);
        $trace = debug_backtrace();
        array_unshift($trace, array());
        $arr = array();
        foreach ($trace as $k => $one) {
            $arr[$k] = array();
            if (isset($one['file'])) {

                $full_file = $one['file'];
                $file = implode('/', array_diff(explode('/', $full_file), explode('/', $root)));

                $arr[$k]['file'] = $file;
            }
            if (isset($one['line'])) $arr[$k]['line'] = $one['line'];
            if (isset($one['class'])) $arr[$k-1]['class'] = $one['class'];
            if (isset($one['function'])) $arr[$k-1]['function'] = $one['function'];
        }
        array_shift($arr);
        array_shift($arr);
        return $arr;
    }

    public function api($var) {
        if (is_object($var)) {
            $class = get_class($var);
            $obj = $var;
        } else {
            if (!class_exists($var)) {
                throw new \Exception('Class ['.$var.'] doesn\'t exist');
            }
            $class = $var;
            try {
                $obj = new $class();
            } catch (\Exception $e) {
                throw new \Exception('Debug::api could not instantiate ['.$var.'], send it an object.');
            }
        }
        $reflection = new \ReflectionObject($obj);
        $properties = array();
        foreach (array(
            'public' => \ReflectionProperty::IS_PUBLIC,
            'protected' => \ReflectionProperty::IS_PROTECTED,
            'private' => \ReflectionProperty::IS_PRIVATE
            ) as $access => $rule) {
                $vars = $reflection->getProperties($rule);
                foreach ($vars as $refProp) {
                    $property = $refProp->getName();
                    $refProp->setAccessible(true);
                    $value = $refProp->getValue($obj);
                    $type = gettype($value);
                    if (is_object($value)) {
                        $value = get_class($value);
                    } elseif (is_array($value)) {
                        $value = 'array['.count($value).']';
                    }

                    $properties[$access][$property] = compact('value', 'type');
                }
        }
        $constants = $reflection->getConstants();

        $methods = array();

        foreach (array(
            'public' => \ReflectionMethod::IS_PUBLIC,
            'protected' => \ReflectionMethod::IS_PROTECTED,
            'private' => \ReflectionMethod::IS_PRIVATE
            ) as $access => $rule) {
                $refMethods = $reflection->getMethods($rule);
                foreach ($refMethods as $refMethod) {
                    $refParams = $refMethod->getParameters();
                    $params = array();
                    foreach ($refParams as $refParam) {
                        $params[] = $refParam->getName();
                    }/*
                    $required = $refMethod->getNumberOfRequiredParameters();
                    $requiredParams = array();
                    for ($i=0;$i<$required;$i++) {
                        $requiredParams[] = array_shift($params);
                    }*/
                    $method_name = $refMethod->getName();

                    $string = $access .' function '.$method_name.'(';
                    $paramString = '';
                    foreach ($params as $p) {
                        $paramString .= '$'.$p.', ';
                    }
                    $paramString  = substr($paramString,0,-2);
                    $string .= $paramString;
                    $string .= ')';

                    $comment = $refMethod->getDocComment();
                    $comment = trim(preg_replace('/^(\s*\/\*\*|\s*\*{1,2}\/|\s*\* ?)/m', '', $comment));
                    $comment = str_replace("\r\n", "\n", $comment);
                    $commentParts = explode('@', $comment);
                    $description = array_shift($commentParts);
                    $tags = array();
                    foreach ($commentParts as $part) {
                        $tagArr = explode(' ', $part, 2);
                        if ($tagArr[0] == 'param') {
                            $paramArr = preg_split("/[\s\t]+/", $tagArr[1]);
                            $type = array_shift($paramArr);
                            if (empty($type)) $type = array_shift($paramArr);
                            $name = array_shift($paramArr);
                            $info = implode(' ', $paramArr);
                            $tags['param'][$name] = compact('type', 'info');
                        } else {
                            $tags[$tagArr[0]] = isset($tagArr[1])?$tagArr[1]:'';
                        }
                    }


                    $methods[$access][$string] = compact('description', 'tags');
                }
        }

        return compact('properties', 'constants' ,'methods');
    }

}
