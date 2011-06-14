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
        $options += self::$defaults + array('split' => false);
        $this->options = $options;
        $this->current_depth = 0;
        $this->object_references = array();
        
        if (!$options['trace']) $options['trace'] = debug_backtrace();
        extract($options);
        $location = $this->location($trace);

        $dump = '';
        if ($options['split'] && is_array($var)) {
            $this->current_depth = 0;
            foreach ($var as $one) $dump .= $this->dump_it($one); // @todo . '<div>-</div>';
        } else
            $dump = $this->dump_it($var);

        switch ($mode) {
			case 'FirePHP':
				$locString = \al13_debug\util\adapters\FirePHP::locationString($location);
				require_once LITHIUM_LIBRARY_PATH . '/FirePHPCore/FirePHP.class.php';
				$firephp = \FirePHP::getInstance(true);
				if (!$firephp) throw new \Exception('FirePHP not installed');
				$firephp->group($locString, array('Collapsed' => false, 'Color' => '#AA0000'));
				$firephp->log($dump);
				$firephp->groupEnd();
				return;
				break;
			case 'Json': 
				$locString = \al13_debug\util\adapters\Json::locationString($location);
				$output = '<script type="text/javascript">';
				$output .= ' window.debug = {};';
				$output .= ' window.debug.location = ' . $locString . ';' . "\n";
				$output .= ' window.debug.dump = "' . $dump . '";';
				$output .= '</script>';
				$this->output[] = $output;
				break;
			case 'Log' :
				$locString = \al13_debug\util\adapters\Log::locationString($location);
				\lithium\analysis\Logger::debug($locString . "\n" .$dump);
				return;
				break;
			case 'Html' :
			default :
				$locString = \al13_debug\util\adapters\Html::locationString($location);
				$this->output[] = '<div class="debug-dump"><div class="debug-location">' . $locString . '</div>'.
					'<div class="debug-content"> ' . $dump . '</div></div>';
				break;
		}
		if ($options['echo']) {
			$this->__out();
		}
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
		$root = substr($_SERVER['DOCUMENT_ROOT'], 0 , strlen('app/webroot') * -1);
        $file = implode('/', array_diff(explode('/', $trace[0]['file']), explode('/', $root)));
        $ret = array(
            'file' => $file,
            'line' => $trace[0]['line']
        );
        if (isset($trace[1]['function'])) $ret['function'] = $trace[1]['function'];
        if (isset($trace[1]['class'])) $ret['class'] = $trace[1]['class'];
        return $ret;
	}

}
