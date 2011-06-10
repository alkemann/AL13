<?php
/**
 * Global debug methods
 */
namespace al13_debug\util\adapters;

/**
 * Requires li3 logging enabled
 */
class Log {

    public static function dump_array(array $array, $debug) {
        $debug->current_depth++;
        $count = count($array);
        $ret = ' type[ Array ] ';
        $ret .= '[ ' . $count . " ] elements \n";
        if ($count > 0) {
            if (in_array('array', $debug->options['avoid'])) {
                $ret .= " -- Array Type Avoided -- \n";
            } else 
                foreach ($array as $key => $value) {
					for ($i=0;$i < $debug->current_depth; $i++) { $ret .= '  '; }
                    $ret .= '[ ' . $key . ' ] => ';
                    if (is_string($key) && in_array($key, $debug->options['blacklist']['key'])) {
						$ret .= "-- Blacklisted Key Avoided -- \n";
                        continue;
                    }
                    if ((is_array($value) || is_object($value)) && $debug->current_depth >= $debug->options['depth']) {
                        $ret .= ' type[ Array ] ';
                        $ret .= '[ ' . count($value) . ' ] elements';
                        $ret .= "\n";
						for ($i=0;$i <= $debug->current_depth; $i++) { $ret .= '  '; }
						$ret .= "-- Debug Depth reached -- \n";
                        continue;
                    }
                    $ret .= $debug->dump_it($value);
                }
        }
        $debug->current_depth--;
        return $ret;    
    }

    public static function dump_object($obj, $debug) {
        $debug->current_depth++;
        $hash = spl_object_hash($obj);
        $id = substr($hash, 9, 7);
        $class = get_class($obj);
        $ret = ' object[  ' . $id . '  ] ';
        $ret .= ' class[ ' . $class . "] \n";
        if (in_array(get_class($obj), $debug->options['blacklist']['class'])) {
            $debug->current_depth--;
			for ($i=0;$i <= $debug->current_depth; $i++) { $ret .= '  '; }
			$ret .= "-- Blacklisted Object Avoided -- \n";
            return $ret;
        }
        if (isset($debug->object_references[$hash]))  {
            $debug->current_depth--;
			for ($i=0;$i <= $debug->current_depth; $i++) { $ret .= '  '; }
			$ret .= "-- Object Recursion Avoided -- \n";
            return $ret;
        }
        if (in_array('object', $debug->options['avoid']))  {
            $debug->current_depth--;
			for ($i=0;$i <= $debug->current_depth; $i++) { $ret .= '  '; }
			$ret .= "-- Object Type Avoided -- \n";
            return $ret;
        }
        if ($debug->current_depth > $debug->options['depth']) {
            $debug->current_depth--;
			for ($i=0;$i <= $debug->current_depth; $i++) { $ret .= '  '; }
			$ret .= "-- Debug Depth reached -- \n";
            return $ret;
        }
        $debug->object_references[$hash] = true;
        $reflection = new \ReflectionObject($obj);
        $props = '';
        foreach (array(
            'public' => \ReflectionProperty::IS_PUBLIC,
            'protected' => \ReflectionProperty::IS_PROTECTED,
            'private' => \ReflectionProperty::IS_PRIVATE
            ) as $type => $rule) {                
                $props .= self::dump_properties($reflection, $obj, $type, $rule, $debug);
        }
        $debug->current_depth--;
        if ($props == '') { 
			for ($i=0;$i <= $debug->current_depth; $i++) { $ret .= '  '; }
			return $ret .= " -- No properties -- \n";
		}
        else  $ret .=  $props;
        return  $ret;
    }

    public static function dump_properties($reflection, $obj, $type, $rule, $debug) {
        $vars = $reflection->getProperties($rule);
        $i = 0; $ret = '';
        foreach ($vars as $refProp) {
            $property = $refProp->getName();
            $i++;
            $refProp->setAccessible(true);
            $value = $refProp->getValue($obj);
			for ($i=0;$i < $debug->current_depth; $i++) { $ret .= '  '; }
            $ret .= '[ ' . $property . ' ][ ' . $type . ' ] => ';
            if (in_array($property, $debug->options['blacklist']['property']))
                $ret .= "-- Blacklisted Property Avoided -- \n";
            else
                $ret .= $debug->dump_it($value);
        }
        return $i ? $ret : '';
    }

    public static function dump_other($var) {
        $type = gettype($var);
        switch ($type) {
            case 'boolean': $var = $var ? 'true' : 'false'; break;
            case 'string' : $var = '\'' . htmlentities($var) . '\''; break;
            case 'NULL' : return '[ NULL ]'; break;
        }
        return '[ ' . $type . '][ ' . $var . ' ] ' . "\n";
    }

    public static function locationString($location) {
        extract($location);
        $ret = "line: $line  ".
               "file: $file  ";
        $ret .= isset($class) ? "class: $class  " :'';
        $ret .= isset($function) && $function != 'include' ? "function: $function  " :'';
        return $ret;
    }

    public static function location($trace) {
        $ret = array(
            'file' => $trace[0]['file'],
            'line' => $trace[0]['line']
        );
        if (isset($trace[1]['function'])) $ret['function'] = $trace[1]['function'];
        if (isset($trace[1]['class'])) $ret['class'] = $trace[1]['class'];
        return $ret;
    }

}
