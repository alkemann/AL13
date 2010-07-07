<?php
/**
 * Number Helper.
 *
 * Li3 adaption
 *
 * @copyright     Copyright 2010, alkemann
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace al13_helpers\extensions\helper;

/**
 * Number helper library.
 *
 * Methods to make numbers more readable.
 */
class Number extends \al13_helpers\extensions\Helper {

	/**
	 * Formats a number with a level of precision.
	 *
	 * @param  float	$number	A floating point number.
	 * @param  integer $precision The precision of the returned number.
	 * @return float Enter description here...
	 */
	public function precision($number, $precision = 3) {
		return sprintf("%01.{$precision}f", $number);
	}

	/**
	 * Returns a formatted-for-humans file size.
	 *
	 * @param integer $length Size in bytes
	 * @return string Human readable size
	 */
	public function toReadableSize($size) {
		switch (true) {
			case $size == 1:
				return '1 Byte';
			case $size < 1024:
				return sprintf('%d Bytes', $size);
			case round($size / 1024) < 1024:
				return sprintf('%d KB', $this->precision($size / 1024, 0));
			case round($size / 1024 / 1024, 2) < 1024:
				return sprintf('%.2f MB', $this->precision($size / 1024 / 1024, 2));
			case round($size / 1024 / 1024 / 1024, 2) < 1024:
				return sprintf('%.2f GB', $this->precision($size / 1024 / 1024 / 1024, 2));
			default:
				return sprintf('%.2f TB', $this->precision($size / 1024 / 1024 / 1024 / 1024, 2));
		}
	}

	/**
	 * Formats a number into a percentage string.
	 *
	 * @param float $number A floating point number
	 * @param integer $precision The precision of the returned number
	 * @return string Percentage string
	 */
	public function toPercentage($number, $precision = 2) {
		return $this->precision($number, $precision) . '%';
	}

	/**
	 * Formats a number into a currency format.
	 *
	 * @param float $number A floating point number
	 * @param integer $options if int then places, if string then before, if (,.-) then use it
	 *   or array with places and before keys
	 * @return string formatted number
	 */
	public function format($number, $options = false) {
		$places = 0;
		if (is_int($options)) {
			$places = $options;
		}

		$separators = array(',', '.', '-', ':');

		$before = $after = null;
		if (is_string($options) && !in_array($options, $separators)) {
			$before = $options;
		}
		$thousands = ',';
		if (!is_array($options) && in_array($options, $separators)) {
			$thousands = $options;
		}
		$decimals = '.';
		if (!is_array($options) && in_array($options, $separators)) {
			$decimals = $options;
		}

		$escape = true;
		if (is_array($options)) {
			$options = array_merge(array('before'=>'$', 'places' => 2, 'thousands' => ',', 'decimals' => '.'), $options);
			extract($options);
		}

		$out = $before . number_format($number, $places, $decimals, $thousands) . $after;

		if ($escape) {
			return htmlspecialchars($out);
		}
		return $out;
	}

	/**
	 * Formats a number into a currency format.
	 *
	 * @param float $number
	 * @param string $currency Shortcut to default options. Valid values are 'USD', 'EUR', 'GBP', otherwise
	 *               set at least 'before' and 'after' options.
	 * @param array $options
	 * @return string Number formatted as a currency.
	 */
	public function currency($number, $currency = 'USD', $options = array()) {
		$default = array(
			'before'=>'', 'after' => '', 'zero' => '0', 'places' => 2, 'thousands' => ',',
			'decimals' => '.','negative' => '()', 'escape' => true
		);
		$currencies = array(
			'USD' => array(
				'before' => '$', 'after' => 'c', 'zero' => 0, 'places' => 2, 'thousands' => ',',
				'decimals' => '.', 'negative' => '()', 'escape' => true
			),
			'GBP' => array(
				'before'=>'&#163;', 'after' => 'p', 'zero' => 0, 'places' => 2, 'thousands' => ',',
				'decimals' => '.', 'negative' => '()','escape' => false
			),
			'EUR' => array(
				'before'=>'&#8364;', 'after' => 'c', 'zero' => 0, 'places' => 2, 'thousands' => '.',
				'decimals' => ',', 'negative' => '()', 'escape' => false
			)
		);

		if (isset($currencies[$currency])) {
			$default = $currencies[$currency];
		} elseif (is_string($currency)) {
			$options['before'] = $currency;
		}

		$options = array_merge($default, $options);

		$result = null;

		if ($number == 0 ) {
			if ($options['zero'] !== 0 ) {
				return $options['zero'];
			}
			$options['after'] = null;
		} elseif ($number < 1 && $number > -1 ) {
			$multiply = intval('1' . str_pad('', $options['places'], '0'));
			$number = $number * $multiply;
			$options['before'] = null;
			$options['places'] = null;
		} elseif (empty($options['before'])) {
			$options['before'] = null;
		} else {
			$options['after'] = null;
		}

		$abs = abs($number);
		$result = $this->format($abs, $options);

		if ($number < 0 ) {
			if ($options['negative'] == '()') {
				$result = '(' . $result .')';
			} else {
				$result = $options['negative'] . $result;
			}
		}
		return $result;
	}
}

?>