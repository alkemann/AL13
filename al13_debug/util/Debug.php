<?php
/**
 * Global debug methods
 */
namespace al13_debug\util;

class Debug {

    public static $options = array(
        'echo' => true,
        'depth' => 10,
        'avoid' => array(),
        'blacklist' => array(
            'class' => array(),
            'property' => array(),
            'key' => array()
        )
    );
    
    private static $__instance = null;

    private $__current_depth;
    private $__object_references;
    private $__options;

    public static function get_instance() {
        if (!static::$__instance) {
            $class = __CLASS__;
            static::$__instance = new $class();
        }
        return static::$__instance;
    }

    public function dump($var, $options = array()) {
        $options += self::$options + array('split' => false);
        $this->__options = $options;
        $this->__current_depth = 0;
        $this->__object_references = array();
        
        if (!$options['trace']) $options['trace'] = debug_backtrace();
        extract($options);
        $location = $this->location($trace);

        $dump = '';
        if ($options['split'] && is_array($var)) {
            $this->__current_depth = 0;
            foreach ($var as $one) $dump .= $this->dump_it($one) . '<div>-</div>';
        } else
            $dump = $this->dump_it($var);
        
        $locString = $this->locationString($location);
        $result = '<style type="text/css">@import url("/al13/css/debug.css");</style>';
        $result .= '<div class="debug-dump"><span class="location">' . $locString . '</span>'.
            '<br> ' . $dump . '</div>';
        if ($echo) echo $result;
    }

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

    private function dump_it($var) {
        if (is_array($var))
            return $this->dump_array($var);
        elseif (is_object($var)) 
            return $this->dump_object($var);
        else
            return $this->dump_other($var);
    }

    private function dump_array(array $array) {
        $this->__current_depth++;
        $count = count($array);
        $ret = ' type[<span class="type"> Array </span>] ';
        $ret .= '[ <span class="count">' . $count . '</span> ] elements</li>';
        if ($count > 0) {
            $ret .= '<ul class="array">';
            if (in_array('array', $this->__options['avoid'])) {
                $ret .= '<li><span class="empty"> -- Array Type Avoided -- </span></li>';
            } else 
                foreach ($array as $key => $value) {
                    $ret .= '<li>[ <span class="key">' . $key . '</span> ] => ';
                    if (is_string($key) && in_array($key, $this->__options['blacklist']['key'])) {
                        $ret .= '<span class="empty"> -- Blacklisted Key Avoided -- </span></li>';
                        continue;
                    }
                    if ((is_array($value) || is_object($value)) && $this->__current_depth >= $this->__options['depth']) {
                        $ret .= ' type[<span class="type"> Array </span>] ';
                        $ret .= '[ <span class="count">' . count($value) . '</span> ] elements</li>';
                        $ret .= '<ul><li><span class="empty"> -- Debug Depth reached -- </span></li></ul>';
                        continue;
                    }
                    $ret .= $this->dump_it($value);
                }
            $ret .= '</ul>';
        }
        $this->__current_depth--;
        return $ret;    
    }

    private function dump_object($obj) {
        $this->__current_depth++;
        $hash = spl_object_hash($obj);
        $id = substr($hash, 9, 7);
        $class = get_class($obj);
        $ret = ' object[ <span class="class-id"> ' . $id . ' </span> ] ';
        $ret .= ' class[ <span class="class">' . $class . '</span> ] </li>';
        $ret .= '<ul class="properties">';
        if (in_array(get_class($obj), $this->__options['blacklist']['class'])) {
            $this->__current_depth--;
            return $ret . '<li><span class="empty"> -- Blacklisted Object Avoided -- </span></li></ul>';
        }
        if (isset($this->__object_references[$hash]))  {
            $this->__current_depth--;
            return $ret . '<li><span class="empty"> -- Object Recursion Avoided -- </span></li></ul>';
        }
        if (in_array('object', $this->__options['avoid']))  {
            $this->__current_depth--;
            return $ret . '<li><span class="empty"> -- Object Type Avoided -- </span></li></ul>';
        }
        if ($this->__current_depth > $this->__options['depth']) {
            $this->__current_depth--;
            return $ret . '<li><span class="empty"> -- Debug Depth reached -- </span></li></ul>';
        }
        $this->__object_references[$hash] = true;
        $reflection = new \ReflectionObject($obj);
        $props = '';
        foreach (array(
            'public' => \ReflectionProperty::IS_PUBLIC,
            'protected' => \ReflectionProperty::IS_PROTECTED,
            'private' => \ReflectionProperty::IS_PRIVATE
            ) as $type => $rule) {                
                $props .= $this->dump_properties($reflection, $obj, $type, $rule);
        }
        $this->__current_depth--;
        if ($props == '') return $ret .= '<li><span class="empty"> -- No properties -- </span></li></ul>';
        else  $ret .=  $props;
        $ret .= '</ul>';
        return  $ret;
    }

    private function dump_properties($reflection, $obj, $type, $rule) {
        $vars = $reflection->getProperties($rule);
        $i = 0; $ret = '';
        foreach ($vars as $refProp) {
            $property = $refProp->getName();
            $i++;
            $refProp->setAccessible(true);
            $value = $refProp->getValue($obj);
            $ret .= '<li>';
            $ret .= '[ <span class="property">' . $property . '</span> ][ <span class="access">' . $type . '</span>] => ';
            if (in_array($property, $this->__options['blacklist']['property']))
                $ret .= '<span class="empty"> -- Blacklisted Property Avoided -- </span>';
            else
                $ret .= $this->dump_it($value);
            $ret .= '</li>';
        }
        return $i ? $ret : '';
    }

    private function dump_other($var) {
        $type = gettype($var);
        switch ($type) {
            case 'boolean': $var = $var ? 'true' : 'false'; break;
            case 'string' : $var = '\'' . htmlentities($var) . '\''; break;
            case 'NULL' : return '[ <span class="empty">NULL</span> ]'; break;
        }
        return '[ <span class="type">' . $type . '</span> ][ <span class="value">' . $var . '</span> ]';
    }

    private function locationString($location) {
        extract($location);
        $ret = "line: <span>$line</span> &nbsp;".
               "file: <span>$file</span> &nbsp;";
        $ret .= isset($class) ? "class: <span>$class</strong> &nbsp;" :'';
        $ret .= isset($function) && $function != 'include' ? "function: <span>$function</span> &nbsp;" :'';
        return $ret;
    }

    private function location($trace) {
        $ret = array(
            'file' => $trace[0]['file'],
            'line' => $trace[0]['line']
        );
        if (isset($trace[1]['function'])) $ret['function'] = $trace[1]['function'];
        if (isset($trace[1]['class'])) $ret['class'] = $trace[1]['class'];
        return $ret;
    }

}


