<?php
/**
 * Global debug methods
 */
namespace al13_debug\util\adapters;

class Html {

    public static function dump_array(array $array, $debug) {
        $debug->current_depth++;
        $count = count($array);
        $ret = ' <span class="class">array</span>';
        $ret .= '[<span class="count">' . $count . '</span>]</li>';
        if ($count > 0) {
            $ret .= '<ul class="array">';
            if (in_array('array', $debug->options['avoid'])) {
                $ret .= '<li><span class="empty"> -- Array Type Avoided -- </span></li>';
            } else 
                foreach ($array as $key => $value) {
                    $ret .= '<li>[ <span class="key">' . $key . '</span> ] => ';
                    if (is_string($key) && in_array($key, $debug->options['blacklist']['key'])) {
                        $ret .= '<span class="empty"> -- Blacklisted Key Avoided -- </span></li>';
                        continue;
                    }
                    if ((is_array($value) || is_object($value)) && $debug->current_depth >= $debug->options['depth']) {
                        $ret .= ' <span class="class">array</span> ';
                        $ret .= '[<span class="count">' . count($value) . '</span>]</li>';
                        $ret .= '<ul><li><span class="empty"> -- Debug Depth reached -- </span></li></ul>';
                        continue;
                    }
                    $ret .= $debug->dump_it($value);
                }
            $ret .= '</ul>';
        }
        $debug->current_depth--;
        return $ret;    
    }

    public static function dump_object($obj, $debug) {
        $debug->current_depth++;
        $hash = spl_object_hash($obj);
        $id = substr($hash, 9, 7);
        $class = get_class($obj);
        $ret = ' object[ <span class="class-id"> ' . $id . ' </span> ] ';
        $ret .= ' class[ <span class="class">' . $class . '</span> ] </li>';
        $ret .= '<ul class="properties">';
        if (in_array(get_class($obj), $debug->options['blacklist']['class'])) {
            $debug->current_depth--;
            return $ret . '<li><span class="empty"> -- Blacklisted Object Avoided -- </span></li></ul>';
        }
        if (isset($debug->object_references[$hash]))  {
            $debug->current_depth--;
            return $ret . '<li><span class="empty"> -- Object Recursion Avoided -- </span></li></ul>';
        }
        if (in_array('object', $debug->options['avoid']))  {
            $debug->current_depth--;
            return $ret . '<li><span class="empty"> -- Object Type Avoided -- </span></li></ul>';
        }
        if ($debug->current_depth > $debug->options['depth']) {
            $debug->current_depth--;
            return $ret . '<li><span class="empty"> -- Debug Depth reached -- </span></li></ul>';
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
        if ($props == '') return $ret .= '<li><span class="empty"> -- No properties -- </span></li></ul>';
        else  $ret .=  $props;
        $ret .= '</ul>';
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
            $ret .= '<li>';
            $ret .= '<span class="access">' . $type . '</span> <span class="property">' . $property . '</span> ';
            if (in_array($property, $debug->options['blacklist']['property']))
                $ret .= '<span class="empty"> -- Blacklisted Property Avoided -- </span>';
            else
                $ret .= ' : ' . $debug->dump_it($value);
            $ret .= '</li>';
        }
        return $i ? $ret : '';
    }

    public static function dump_other($var) {
        $type = gettype($var);
        switch ($type) {
            case 'boolean': $var = $var ? 'true' : 'false'; break;
            case 'string' : $length = strlen($var); $var = '\'' . htmlentities($var) . '\''; break;
            case 'NULL' : return '<span class="empty">NULL</span>'; break;
        }
		$ret = '<span class="value ' . $type .'">' . $var . '</span> ';

		if ($type == 'string') {
			$ret .= '<span class="type">string[' . $length . ']</span>';
		} else {
			$ret .= '<span class="type">' . $type . '</span>';
		}
        return $ret;
    }

    public static function locationString($location) {
        extract($location);
        $ret = "line: <span>$line</span> &nbsp;".
               "file: <span>$file</span> &nbsp;";
        $ret .= isset($class) ? "class: <span>$class</span> &nbsp;" :'';
        $ret .= isset($function) && $function != 'include' ? "function: <span>$function</span> &nbsp;" :'';
        return $ret;
    }

}
