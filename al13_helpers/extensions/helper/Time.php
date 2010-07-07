<?php
/**
 * Time Helper class file.
 *
 * @copyright     Copyright 2010, alkemann
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace al13_helpers\extensions\helper;

use \DateTime;
use \DateInterval;

/**
 * Time Helper class for easy use of time data.
 *
 * Manipulation of time data.
 */
class Time extends \al13_helpers\extensions\Helper {

	const DAY = 86400;
	const HOUR = 3600;

	/**
	 * Question if supplied date is 'today', 'yesterday', 'this week' etc
	 *
	 * Valid questions:
	 *  - today
	 *  - yesterday
	 *  - tomorrow
	 *  - this week
	 *  - this month
	 *  - this year
	 *  - leap year
	 *
	 * @param string $question
	 * @param mixed $date string|int|null
	 * @param array $options
	 * return boolean
	 */
	public function is($question, $date = null, array $options = array()) {	
		switch ($question) {
			case 'leap year' :	
				$date = $date ?: date('Y-m-d H:i:s');
				$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
				return $date->format('L');				
			break;
			default :
				return $this->_relativeCheck($question, $date, $options);
			break;
		}
	}

	/**
	 * Convert given date (or current if null) to 'nice', 'short' or 'words' etc
	 *
	 * Valid types:
	 *  - nice
	 *  - short
	 *  - words
	 *  - unix
	 *  - atom
	 *  - rss
	 *  - cookie
	 *
	 * @param string $type
	 * @param mixed $date string|int|null
	 * @param array $options
	 * return string 
	 */
	public function to($type, $date = null, array $options = array()) {
		switch ($type) {
			case 'format' : 
				if (!isset($options['format'])) return null;
				return $this->format($options['format'], $date);
			break;
			case 'nice':
				$offset = (isset($options['offset'])) ? $options['offset'] : 0;
				return $this->_nice($date, $offset);
			break;
			case 'niceShort' : 
			case 'short' :
				$offset = (isset($options['offset'])) ? $options['offset'] : 0;
				return $this->_short($date, $offset);
			break;
			case 'unix' : case 'Unix' : case 'UNIX' :
				if ($date == null) return time();
				$date = $date ?: date('Y-m-d H:i:s');
				$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
				return $date->format('U');
			break;
			case 'rss' : case 'Rss' : case 'RSS' :
				$date = $date ?: date('Y-m-d H:i:s');
				$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
				return $date->format(DateTime::RSS);
			break;
			case 'atom' : case 'Atom' : case 'ATOM' :
				$date = $date ?: date('Y-m-d H:i:s');
				$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
				return $date->format(DateTime::ATOM);
			break;
			case 'cookie' : case 'Cookie' : case 'COOKIE' :
				$date = $date ?: date('Y-m-d H:i:s');
				$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
				return $date->format(DateTime::COOKIE);
			break;
			case 'words' : 
			case 'timeAgoInWords' : 
			case 'relative' :
			default:
				return $this->_words($date, $options);
			break;
		}	
	}
		
	/**
	 * Format a date using the DateTime native PHP class
	 * 
	 * @param string $format
	 * @param mixed $data string|int|null
	 * @return string
	 */
	public function format($format, $date = null) {		
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
		return $date->format($format);
	}

	private function _relativeCheck($question, $date = null, array $options = array()) {
		$defaults = array('offset' => 0, 'now' => date('Y-m-d H:i:s'));
		$options += $defaults;
		$now = $options['now'];
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
		$now = new DateTime(is_int($now) ? date('Y-m-d H:i:s', $now) : $now);
		switch ($question) {
			case 'today' :
				return $date->format('dmy') == $now->format('dmy');
			break;
			case 'tomorrow' :	
				$now->add(DateInterval::createFromDateString('1 day'));
				return $date->format('dmy') == $now->format('dmy');
			break;
			case 'yesterday' :	
				$now->add(DateInterval::createFromDateString('-1 day'));
				return $date->format('dmy') == $now->format('dmy');
			break;
			case 'this week' :
				return $date->format('Wy') == $now->format('Wy');
			break;
			case 'this month' :
				return $date->format('my') == $now->format('my');
			break;
			case 'this year' :
				return $date->format('y') == $now->format('y');
			break;
		}	
		throw new \Exception('Illegal $question parameter');
		return null;
	}
	
	private function _nice($date, $offset = 0) {
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);

		if ($offset) {
			$date->add(DateInterval::createFromDateString("{$offset} hours"));
		}
		return $date->format('D, M jS Y, H:i');
	}
		
	private function _short($date = null, $offset = 0) {
		$now = new DateTime();
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
	
		if ($offset) {
			$date->add(DateInterval::createFromDateString("{$offset} hours"));
		}
		$diff = $date->diff($now);
		$y = ($diff->format('%y') != 0) ? ' Y' : '';
		$onlyDay = ($diff->format('%y%m') == '00');
		$dayDirection = $diff->format('%R');
		switch (true) {
			case ($diff->d == 0 && $onlyDay) :
				$text = 'Today, %s';
				$format = 'H:i';
			break;
			case ($diff->d == 1 && $dayDirection == '+' && $onlyDay) :
				$text = 'Yesterday, %s';
				$format = 'H:i';
			break;
			case ($diff->d == 1 && $dayDirection == '-' && $onlyDay) :
				$text = 'Tomorrow, %s';
				$format = 'H:i';
			break;
			default : 
				$text = null;
				$format = "M jS$y, H:i";
		}
		$ret = $date->format($format);
		if ($text) 
			$ret = sprintf($text, $ret);
		return $ret;
	}
	
	private function _words($date, array $options = array()) {
		$defaults = array(
			'offset' => 0, 'format' => 'j/n/y', 'end' => '+1 month', 'now' => date('Y-m-d H:i:s')
		);
		$options += $defaults;
		$now = $options['now'];

		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
		$now = new DateTime(is_int($now) ? date('Y-m-d H:i:s', $now) : $now);
		if ($date == $now) return 'Now!';

		if ($offset = $options['offset']) {
			$date->add(DateInterval::createFromDateString("{$offset} hours"));
		}

		if ($end = $options['end']) {
			$end = new DateTime(($date > $now ? '+' : '-') . $end);
			$outOfBounds = (($date > $now && $date > $end) || ($date < $now && $date < $end));

			if ($outOfBounds) {
				return 'on ' . $date->format($options['format']);
			}
		}

		$diff = $date->diff($now);
		$keys = (array) $diff + array('w' => 0);
		$result = '';

		if ($keys['d'] >= 7) {
			$keys['w'] = floor($keys['d'] / 7);
			$keys['d'] -= ($keys['w'] * 7);
		}

		$strings = array(
			'y' => array('year', 'years'),
			'm' => array('month', 'months'),
			'w' => array('week', 'weeks'),
			'd' => array('day', 'days'),
			'h' => array('hour', 'hours'),
			'i' => array('minute', 'minutes'),
			's' => array('second', 'seconds')
		);

		foreach ($strings as $key => $text) {
			if (!$value = $keys[$key]) {
				continue;
			}
			list($singular, $plural) = $text;
			$title = ($value == 1) ? $singular : $plural;
			$result .= ", {$value} {$title}";
		}
		$result .= ($diff->format('%R') == '+') ? ' ago' : '';	
		return substr($result, 2);
	}
}

?>