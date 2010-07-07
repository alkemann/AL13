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
	 * Converts given time (in server's time zone) to user's local time, given his/her offset from
	 * GMT.
	 *
	 * @param string $serverTime UNIX timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string UNIX timestamp
	 */
	public function convert($serverTime, $userOffset) {
		$serverOffset = $this->serverOffset();
		$gmtTime = $serverTime - $serverOffset;
		$userTime = $gmtTime + $userOffset * (60*60);
		return $userTime;
	}

	/**
	 * Returns server's offset from GMT in seconds.
	 *
	 * @return int Offset
	 */
	public function serverOffset() {
		return date('Z');
	}

	/**
	 * Returns a UNIX timestamp, given either a UNIX timestamp or a valid strtotime() date string.
	 *
	 * @param string $dateString Datetime string
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Parsed timestamp
	 */
	public function fromString($dateString, $userOffset = null) {
		if (empty($dateString)) {
			return false;
		}
		if (is_integer($dateString) || is_numeric($dateString)) {
			$date = intval($dateString);
		} else {
			$date = strtotime($dateString);
		}
		if ($userOffset !== null) {
			return $this->convert($date, $userOffset);
		}
		return $date;
	}

	/**
	 * Returns a nicely formatted date string for given Datetime string.
	 *
	 * @param mixed $date Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Formatted date string
	 */
	public function nice($date = null, $userOffset = 0) {
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);

		if ($userOffset) {
			$date->add(DateInterval::createFromDateString("{$userOffset} hours"));
		}
		return $date->format('D, M jS Y, H:i');
	}

	public function output($str) {
		return $str;
	}

	/**
	 * Returns a formatted descriptive date string for given datetime string.
	 *
	 * If the given date is today, the returned string could be "Today, 16:54".
	 * If the given date was yesterday, the returned string could be "Yesterday, 16:54".
	 * If $dateString's year is the current year, the returned string does not
	 * include mention of the year.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Described, relative date string
	 */
	public function niceShort($date = null, $userOffset = 0) {
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);
	
		if ($userOffset) {
			$date->add(DateInterval::createFromDateString("{$userOffset} hours"));
		}
		
		$diff = $date->diff(new DateTime());
		$y = ($diff->format('%y') != 0) ? ' Y' : '';

		$dayDiff = $diff->format('%d');
		$dayDirection = $diff->format('%R');
		switch (true) {
			case ($dayDiff == 0) :
				$text = 'Today, %s';
				$format = 'H:i';
			break;
			case ($dayDiff == 1 && $dayDirection == '+') :
				$text = 'Yesterday, %s';
				$format = 'H:i';
			break;
			case ($dayDiff == 1 && $dayDirection == '-') :
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

	/**
	 * Returns a partial SQL string to search for all records between two dates.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param string $end Datetime string or Unix timestamp
	 * @param string $fieldName Name of database field to compare with
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Partial SQL string.
	 */
	public function daysAsSql($begin, $end, $fieldName, $userOffset = null) {
		$begin = $this->fromString($begin, $userOffset);
		$end = $this->fromString($end, $userOffset);
		$begin = date('Y-m-d', $begin) . ' 00:00:00';
		$end = date('Y-m-d', $end) . ' 23:59:59';

		$ret  ="($fieldName >= '$begin') AND ($fieldName <= '$end')";
		return $this->output($ret);
	}

	/**
	 * Returns a partial SQL string to search for all records between two times
	 * occurring on the same day.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param string $fieldName Name of database field to compare with
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Partial SQL string.
	 */
	public function dayAsSql($dateString, $fieldName, $userOffset = null) {
		$date = $this->fromString($dateString, $userOffset);
		$ret = $this->daysAsSql($dateString, $dateString, $fieldName);
		return $this->output($ret);
	}

	/**
	 * Returns `true` if given datetime string is today.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return boolean Returns `true` if datetime string is today.
	 */
	public function isToday($dateString, $userOffset = null) {
		return date('Y-m-d', $this->fromString($dateString, $userOffset)) == date('Y-m-d');
	}

	/**
	 * Returns `true` if given datetime string is within this week.
	 *
	 * @param string $dateString
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return boolean True if datetime string is within current week
	 */
	public function isThisWeek($dateString, $userOffset = null) {
		return date('W Y', $this->fromString($dateString, $userOffset)) == date('W Y', time());
	}

	/**
	 * Returns true if given datetime string is within this month.
	 *
	 * @param string $dateString
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return boolean True if datetime string is within current month
	 */
	public function isThisMonth($dateString, $userOffset = null) {
		$date = $this->fromString($dateString);
		return date('m Y',$date) == date('m Y', time());
	}

	/**
	 * Returns `true` if given datetime string is within current year.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @return boolean True if datetime string is within current year
	 */
	public function isThisYear($dateString, $userOffset = null) {
		$date = $this->fromString($dateString, $userOffset);
		return  date('Y', $date) == date('Y');
	}

	/**
	 * Returns `true` if given datetime string was yesterday.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return boolean True if datetime string was yesterday
	 */
	public function wasYesterday($dateString, $userOffset = null) {
		$date = $this->fromString($dateString, $userOffset);
		return date('Y-m-d', $date) == date('Y-m-d', strtotime('yesterday'));
	}

	/**
	 * Returns true if given datetime string is tomorrow.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return boolean True if datetime string was yesterday
	 */
	public function isTomorrow($dateString, $userOffset = null) {
		$date = $this->fromString($dateString, $userOffset);
		return date('Y-m-d', $date) == date('Y-m-d', strtotime('tomorrow'));
	}

	/**
	 * Returns the quarter
	 * @param string $dateString
	 * @param boolean $range if true returns a range in Y-m-d format
	 * @return boolean True if datetime string is within current week
	 */
	public function toQuarter($dateString, $range = false) {
		$time = $this->fromString($dateString);
		$date = ceil(date('m', $time) / 3);

		if ($range === true) {
			$range = 'Y-m-d';
		}

		if ($range !== false) {
			$year = date('Y', $time);

			switch ($date) {
				case 1:
					$date = array($year.'-01-01', $year.'-03-31');
					break;
				case 2:
					$date = array($year.'-04-01', $year.'-06-30');
					break;
				case 3:
					$date = array($year.'-07-01', $year.'-09-30');
					break;
				case 4:
					$date = array($year.'-10-01', $year.'-12-31');
					break;
			}
		}
		return $this->output($date);
	}

	/**
	 * Returns a UNIX timestamp from a textual datetime description. Wrapper for PHP function strtotime().
	 *
	 * @param string $dateString Datetime string to be represented as a Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return integer Unix timestamp
	 */
	public function toUnix($dateString, $userOffset = null) {
		$ret = $this->fromString($dateString, $userOffset);
		return $this->output($ret);
	}

	/**
	 * Returns a date formatted for Atom RSS feeds.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Formatted date string
	 */
	public function toAtom($dateString, $userOffset = null) {
		$date = $this->fromString($dateString, $userOffset);
		$ret = date('Y-m-d\TH:i:s\Z', $date);
		return $this->output($ret);
	}

	/**
	 * Formats date for RSS feeds
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Formatted date string
	 */
	public function toRSS($dateString, $userOffset = null) {
		$date = $this->fromString($dateString, $userOffset);
		$ret = date("r", $date);
		return $this->output($ret);
	}

	/**
	 * Returns either a relative date or a formatted date depending
	 * on the difference between the current time and given datetime.
	 * $datetime should be in a <i>strtotime</i> - parsable format, like MySQL's datetime datatype.
	 *
	 * Options:
	 *
	 * - 'format' => a fall back format if the relative time is longer than the duration specified by end
	 * - 'end' => The end of relative time telling
	 * - 'userOffset' => Users offset from GMT (in hours)
	 *
	 * Relative dates look something like this:
	 *	3 weeks, 4 days ago
	 *	15 seconds ago
	 *
	 * Default date formatting is d/m/yy e.g: on 18/2/09
	 *
	 * The returned string includes 'ago' or 'on' and assumes you'll properly add a word
	 * like 'Posted ' before the function output.
	 *
	 * @param string $dateString Datetime string or Unix timestamp
	 * @param array $options Default format if timestamp is used in $dateString
	 * @return string Relative time string.
	 */
	public function timeInWords($date, $options = array()) {
		if (!is_array($options)) $options = array('format' => $options);
		$defaults = array('offset' => 0, 'format' => 'd/n/y', 'end' => '-1 year');		
		$options = array_merge($defaults, $options);		
		extract($options);
		
		$date = $date ?: date('Y-m-d H:i:s');
		$date = new DateTime(is_int($date) ? date('Y-m-d H:i:s', $date) : $date);

		if ($offset) {
			$date->add(DateInterval::createFromDateString("{$userOffset} hours"));
		}

		if ($end) {
			if (substr($end,0,1) != '-') $end = '-'.$end;
			$end = new DateTime(date(DateTime::ATOM, strtotime($end)));
			$diff = $date->diff($end);
			if ($diff->format('%R') == '+') {
				return 'on ' . $date->format($format);
			}
		}		
		
		$diff = $date->diff(new DateTime());
		
		$ret = '';
		if ($diff->y) $ret .= ($diff->y == 1) ? ', 1 year' 	: ', '.$diff->y.' years';
		if ($diff->m) $ret .= ($diff->m == 1) ? ', 1 month' 	: ', '.$diff->m.' months';
		$days = $diff->d;
		if ($days > 7) {
			$weeks = floor($days / 7);
			$days = $days - ($weeks * 7);
			$ret .= ($weeks == 1) ? ', 1 week' : ', '.$weeks.' weeks';
			if ($days) $ret .= ($days == 1) ? ', 1 day' : ', '.$days.' days';
		} else {
			if ($diff->d) $ret .= ($diff->d == 1) ? ', 1 day' : ', '.$diff->d.' days';
		}		
		if ($diff->h) $ret .= ($diff->h == 1) ? ', 1 hour' 	: ', '.$diff->h.' hours';
		if ($diff->i) $ret .= ($diff->i == 1) ? ', 1 minute' 	: ', '.$diff->i.' minutes';
		if ($diff->s) $ret .= ($diff->s == 1) ? ', 1 second' 	: ', '.$diff->s.' seconds';
		$ret .= ($diff->format('%R') == '+') ? ' ago' : '';	
		$ret = substr($ret,2);	
		return $ret;
	}

	/**
	 * Alias for timeInWords
	 *
	 * @param mixed $dateTime Datetime string (strtotime-compatible) or Unix timestamp
	 * @param mixed $options Default format string, if timestamp is used in $dateTime, or an array
	 *              of options to be passed on to `timeInWords()`.
	 * @return string Relative time string.
	 * @see Time::timeInWords
	 */
	public function relativeTime($dateTime, $options = array()) {
		return $this->timeInWords($dateTime, $options);
	}

	/**
	 * Returns true if specified datetime was within the interval specified, else false.
	 *
	 * @param mixed $timeInterval the numeric value with space then time type. Example of valid
	 *              types: 6 hours, 2 days, 1 minute.
	 * @param mixed $dateString the datestring or unix timestamp to compare
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return bool
	 */
	public function wasWithinLast($timeInterval, $dateString, $userOffset = null) {
		$tmp = str_replace(' ', '', $timeInterval);

		if (is_numeric($tmp)) {
			$timeInterval = "{$tmp} days";
		}

		$date = $this->fromString($dateString, $userOffset);
		$interval = $this->fromString('-' . $timeInterval);
		return ($date >= $interval && $date <= time());
	}

	/**
	 * Returns GMT, given either a UNIX timestamp or a valid `strtotime()` date string.
	 *
	 * @param string $dateString Datetime string
	 * @return string Formatted date string
	 */
	public function gmt($string = null) {
		$string = $this->fromString($string ?: time());
		$hour = intval(date("G", $string));
		$minute = intval(date("i", $string));
		$second = intval(date("s", $string));
		$month = intval(date("n", $string));
		$day = intval(date("j", $string));
		$year = intval(date("Y", $string));
		return gmmktime($hour, $minute, $second, $month, $day, $year);
	}

	/**
	 * Returns a formatted date string, given either a UNIX timestamp or a valid `strtotime()` date
	 * string.
	 *
	 * @param string $format date format string. defaults to 'd-m-Y'
	 * @param string $dateString Datetime string
	 * @param boolean $invalid flag to ignore results of fromString == false
	 * @param int $userOffset User's offset from GMT (in hours)
	 * @return string Formatted date string
	 */
	public function format($format = 'd-m-Y', $date, $invalid = false, $userOffset = null) {
		$date = $this->fromString($date, $userOffset);

		if ($date === false && $invalid !== false) {
			return $invalid;
		}
		return date($format, $date);
	}
}

?>