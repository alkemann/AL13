<?php
/**
 * Global debug methods
 */
namespace al13_debug\util\adapters;

/**
 * Put's dump output in arrays such that they go nicely
 * into json_encode. Suggested use for Lithium is for json
 * by inserting the debug dump in the media handler, as per
 * a dout() in an html layout. Example:
 * 
 * {{{
 * /// app/config/bootstrap/media.php
 * use lithium\net\http\Media;
 * 
 * 	Media::type('json', 'application/json', array(
 * 		'cast' => true,
 * 		'layout' => '{:library}/views/layouts/default.json.php',
 * 		'type' => 'json',
 * 		'encode' => function($data) {
 * 			$container = array(
 * 				'name' => 'Container',
 * 				'debug' => daout(),
 * 				'count' => count($data['data']),
 *				'maxCount' => $data['total'] ?: $count,
 *				'data' => $data['data']
 * 			);
 * 			return json_encode($container);
 * 		},
 * 		'decode' => function($data) {
 * 			return json_decode($data, true);
 * 		}
 * 	));
 * }}}
 * 
 */
class Json {

    public static function dump_array(array $array, $debug) {
        $debug->current_depth++;
        $count = count($array);
        $ret = array();
        if ($count > 0) {
            if (in_array('array', $debug->options['avoid'])) {
                $ret[] = ' -- Array Type Avoided -- ';
            } else 
                foreach ($array as $key => $value) {
					if (!is_numeric($key)) $key = '\'' . $key . '\'';
                    if (is_string($key) && in_array($key, $debug->options['blacklist']['key'])) {
                        $ret[$key. " "] = ' -- Blacklisted Key Avoided -- ';
                        continue;
                    }
                    if ((is_array($value) || is_object($value)) && $debug->current_depth >= $debug->options['depth']) {
                        $ret[$key. " "] = array ( 'array [' . count($value) .']' => ' -- Debug Depth reached -- ');
                        continue;
                    }
					$ret[$key. " "] = $debug->dump_it($value);
                }
        }
        $debug->current_depth--;
        return array(' array [' . $count . ']' => $ret);
    }

    public static function dump_object($obj, $debug) {
        $debug->current_depth++;
        $hash = spl_object_hash($obj);
        $id = substr($hash, 9, 7);
        $class = get_class($obj);
        $ret = array();
        if (in_array(get_class($obj), $debug->options['blacklist']['class'])) {
            $debug->current_depth--;
			$ret = " -- Blacklisted Object Avoided -- ";
            return array( $class . ' [' . $id . ']'  => $ret);
        }
        if (isset($debug->object_references[$hash]))  {
            $debug->current_depth--;
			$ret = " -- Object Recursion Avoided -- ";
            return array( $class . ' [' . $id . ']'  => $ret);
        }
        if (in_array('object', $debug->options['avoid']))  {
            $debug->current_depth--;
			$ret = " -- Object Type Avoided -- ";
            return array( $class . ' [' . $id . ']'  => $ret);
        }
        if ($debug->current_depth > $debug->options['depth']) {
            $debug->current_depth--;
			$ret = " -- Debug Depth reached -- ";
            return array( $class . ' [' . $id . ']'  => $ret);
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
			return array( $class . ' [' . $id . ']'  => ' -- No properties -- ');
		} else {
			$ret = array_merge($ret, $props);
		}
        return array( $class . ' [' . $id . ']'  => $ret);
    }

    public static function dump_properties($reflection, $obj, $type, $rule, $debug) {
        $vars = $reflection->getProperties($rule);
        $i = 0; $ret = array();
        foreach ($vars as $refProp) {
            $property = $refProp->getName();
            $i++;
            $refProp->setAccessible(true);
            $value = $refProp->getValue($obj);
            $row = ' ' . $type . ' \'' . $property . '\'';
            if (in_array($property, $debug->options['blacklist']['property']))
                $ret[$row] = " -- Blacklisted Property Avoided -- ";
            else {
				$dump = $debug->dump_it($value);
				if (is_string($dump))
					$ret[$row] = $dump;
				else {
					$ret[$row] = $dump;
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
            case 'string' : $length = strlen($var); $var = '\'' . htmlentities($var) . '\' [' . $length . ']'; break;
            case 'NULL' : return '[ NULL ]'; break;
        }
        return $var . ' (' . $type . ')';
    }

    public static function locationString($location) {
        extract($location);
		$ret = new \stdClass();
		$ret->line = $line;
		$ret->file = $file;
		if (isset($class)) $ret->class = $class;
		if (isset($function) && $function != 'include') $ret->function = $function;
        return $ret;
    }

}
