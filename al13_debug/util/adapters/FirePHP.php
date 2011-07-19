<?php
/**
 * Global debug methods
 */
namespace al13_debug\util\adapters;

/**
 * Requires li3 logging enabled
 */
class FirePHP {

    public static function dump_array(array $array, $debug) {
        $debug->current_depth++;
        $count = count($array);
		$indent = ''; for ($i=1;$i <= $debug->current_depth; $i++) { $indent .= '_'; }
        $ret = array(' array [ ' . $count . ' ]');
        if ($count > 0) {
            if (in_array('array', $debug->options['avoid'])) {
                $ret[] = $indent . ' -- Array Type Avoided -- ';
            } else
                foreach ($array as $key => $value) {
					if (!is_numeric($key)) $key = '\'' . $key . '\'';
                    $row = $indent . ' ' . $key . ' : ';
                    if (is_string($key) && in_array($key, $debug->options['blacklist']['key'])) {
                        $ret[] = $row . ' -- Blacklisted Key Avoided -- ';
                        continue;
                    }
                    if ((is_array($value) || is_object($value)) && $debug->current_depth >= $debug->options['depth']) {
                        $ret[] = $row . ' array [' . count($value) . ']';
                        $ret[] = $indent . '_ -- Debug Depth reached -- ';
                        continue;
                    }
					$dump = $debug->dump_it($value);
					if (is_string($dump))
						$ret[] = $row . $dump;
					else {
						$ret[] = $row . current($dump);
						$ret = array_merge($ret, \array_slice($dump, 1));
					}
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
        $ret = array( $class . ' [ ' . $id . ' ]');
		$indent = ''; for ($i=0;$i <= $debug->current_depth; $i++) { $indent .= '_'; }
        if (in_array(get_class($obj), $debug->options['blacklist']['class'])) {
            $debug->current_depth--;
			$ret[] = $indent . " -- Blacklisted Object Avoided -- ";
            return $ret;
        }
        if (isset($debug->object_references[$hash]))  {
            $debug->current_depth--;
			$ret[] = $indent . " -- Object Recursion Avoided -- ";
            return $ret;
        }
        if (in_array('object', $debug->options['avoid']))  {
            $debug->current_depth--;
			$ret[] = $indent . " -- Object Type Avoided -- ";
            return $ret;
        }
        if ($debug->current_depth > $debug->options['depth']) {
            $debug->current_depth--;
			$ret[] = $indent . " -- Debug Depth reached -- ";
            return $ret;
        }
        $debug->object_references[$hash] = true;
        $reflection = new \ReflectionObject($obj);
        $props = array();
        foreach (array(
            'public' => \ReflectionProperty::IS_PUBLIC,
            'protected' => \ReflectionProperty::IS_PROTECTED,
            'private' => \ReflectionProperty::IS_PRIVATE
            ) as $type => $rule) {
                $props = array_merge($props, self::dump_properties($reflection, $obj, $type, $rule, $debug));
        }
        $debug->current_depth--;
        if (empty($props)) {
			$indent = ''; for ($i=0;$i <= $debug->current_depth; $i++) { $indent .= '_'; }
			$ret[] = $indent . " -- No properties -- ";
			return $ret;
		} else {
			$ret = array_merge($ret, $props);
		}
        return $ret;
    }

    public static function dump_properties($reflection, $obj, $type, $rule, $debug) {
        $vars = $reflection->getProperties($rule);
        $i = 0; $ret = array();
        foreach ($vars as $refProp) {
            $property = $refProp->getName();
            $i++;
            $refProp->setAccessible(true);
            $value = $refProp->getValue($obj);
			$indent = ''; for ($i=1;$i <= $debug->current_depth; $i++) { $indent .= '_'; }
            $row = $indent . ' ' . $type . ' \'' . $property . '\' : ';
            if (in_array($property, $debug->options['blacklist']['property']))
                $ret[] = $row . " -- Blacklisted Property Avoided -- ";
            else {
				$dump = $debug->dump_it($value);
				if (is_string($dump))
					$ret[] = $row . $dump;
				else {
					$ret[] = $row . current($dump);
					$ret = array_merge($ret, \array_slice($dump, 1));
				}
			}
        }
        return $ret;
    }

    public static function dump_other($var) {
        $type = gettype($var);
        switch ($type) {
            case 'boolean': $var = $var ? 'true' : 'false'; break;
            case 'string' : $size = strlen($var); $var = '\'' . htmlentities($var) . '\' [' . $size . ']'; break;
            case 'NULL' : return '[ NULL ]'; break;
        }
        return $var .' (' . $type . ')';
    }

    public static function locationString($location) {
        extract($location);
        $ret = 'line : ' . $line . ', file : ' . $file . ', ';
        $ret .= isset($class) ? 'class : ' . $class . ', ' :'';
        $ret .= isset($function) && $function != 'include' ? 'function : ' . $function . ', ' :'';
		$res = substr($ret, 0, -2);
        return $res;
    }

}
