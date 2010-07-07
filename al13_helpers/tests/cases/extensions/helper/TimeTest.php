<?php
/**
 * TimeTest file
 *
 * @copyright     Copyright 2010, alkemann
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace al13_helpers\tests\cases\extensions\helper;

use DateTime;
use DateTimeZone;
use al13_helpers\extensions\helper\Time;
use lithium\tests\mocks\template\helper\MockFormRenderer;

/**
 * NumberHelperTest class
 *
 */
class TimeTest extends \lithium\test\Unit {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		$this->time = new Time(array('context' => new MockFormRenderer()));
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->time);
	}

	/**
	 * testToQuarter method
	 *
	 * @return void
	 */
	public function testToQuarter() {
		$result = $this->time->toQuarter('2007-12-25');
		$this->assertEqual($result, 4);

		$result = $this->time->toQuarter('2007-9-25');
		$this->assertEqual($result, 3);

		$result = $this->time->toQuarter('2007-3-25');
		$this->assertEqual($result, 1);

		$result = $this->time->toQuarter('2007-3-25', true);
		$this->assertEqual($result, array('2007-01-01', '2007-03-31'));

		$result = $this->time->toQuarter('2007-5-25', true);
		$this->assertEqual($result, array('2007-04-01', '2007-06-30'));

		$result = $this->time->toQuarter('2007-8-25', true);
		$this->assertEqual($result, array('2007-07-01', '2007-09-30'));

		$result = $this->time->toQuarter('2007-12-25', true);
		$this->assertEqual($result, array('2007-10-01', '2007-12-31'));
	}

	/**
	 * testtimeInWords method
	 *
	 * @return void
	 */
	public function testtimeInWords() {
		$time = time() - 2 * Time::DAY;
		$expected = "2 days ago";
		$result = $this->time->timeInWords($time);
		$this->assertEqual($expected, $result);
		
		$result = $this->time->timeInWords('2007-9-25');
		$this->assertEqual($result, 'on 25/9/07');

		$result = $this->time->timeInWords('2007-9-25', array('format' => 'Y-m-d'));
		$this->assertEqual($result, 'on 2007-09-25');

		$result = $this->time->timeInWords('2007-9-25', array('format' => 'Y-m-d'));
		$this->assertEqual($result, 'on 2007-09-25');

		$result = $this->time->timeInWords(strtotime('-2 weeks, -2 days'), array(
			'format' => 'Y-m-d'
		));
		$this->assertEqual($result, '2 weeks, 2 days ago');

		$result = $this->time->timeInWords(strtotime('2 weeks, 2 days'), array(
			'format' => 'Y-m-d'
		));
		$this->assertPattern('/^2 weeks, [1|2] day(s)?$/', $result);
	}

	public function testTimeInWordsOptions() {
		$result = $this->time->timeInWords(strtotime('4 months, 2 weeks, 3 days'), array('end' => '8 years'), true);
		$this->assertEqual('4 months, 2 weeks, 3 days', $result);

		$result = $this->time->timeInWords(strtotime('4 months, 2 weeks, 2 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '4 months, 2 weeks, 2 days');

		$result = $this->time->timeInWords(strtotime('4 months, 2 weeks, 1 day'), array('end' => '8 years'), true);
		$this->assertEqual($result, '4 months, 2 weeks, 1 day');

		$result = $this->time->timeInWords(strtotime('3 months, 2 weeks, 1 day'), array('end' => '8 years'), true);
		$this->assertEqual($result, '3 months, 2 weeks, 1 day');

		$result = $this->time->timeInWords(strtotime('3 months, 2 weeks'), array('end' => '8 years'), true);
		$this->assertEqual($result, '3 months, 2 weeks');

		$result = $this->time->timeInWords(strtotime('3 months, 1 week, 6 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '3 months, 1 week, 6 days');

		$result = $this->time->timeInWords(strtotime('2 months, 2 weeks, 1 day'), array('end' => '8 years'), true);
		$this->assertEqual($result, '2 months, 2 weeks, 1 day');

		$result = $this->time->timeInWords(strtotime('2 months, 2 weeks'), array('end' => '8 years'), true);
		$this->assertEqual($result, '2 months, 2 weeks');

		$result = $this->time->timeInWords(strtotime('2 months, 1 week, 6 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '2 months, 1 week, 6 days');

		$result = $this->time->timeInWords(strtotime('1 month, 1 week, 6 days'), array('end' => '8 years'), true);
		$this->assertEqual($result, '1 month, 1 week, 6 days');

		$result = $this->time->timeInWords(strtotime('-2 years, -5 months, -2 days'), array('end' => '3 years'), true);
		$this->assertEqual($result, '2 years, 5 months, 2 days ago');

		$result = $this->time->timeInWords(strtotime('2 months, 2 days'), array('end' => '1 month'));
		$this->assertEqual('on ' . date('j/n/y', strtotime('2 months, 2 days')), $result);

		$result = $this->time->timeInWords(strtotime('+2 months, 2 days'), array('end' => '3 months'));
		$this->assertPattern('/2 months/', $result);

		$result = $this->time->timeInWords(strtotime('2 months, 12 days'), array('end' => '3 months'));
		$this->assertPattern('/2 months, 1 week/', $result);

		$result = $this->time->timeInWords(strtotime('3 months, 5 days'), array('end' => '4 months'));
		$this->assertEqual($result, '3 months, 5 days');

		$result = $this->time->timeInWords(strtotime('-2 months, -2 days'), array('end' => '3 months'));
		$this->assertEqual($result, '2 months, 2 days ago');

		$result = $this->time->timeInWords(strtotime('-2 months, -2 days'), array('end' => '3 months'));
		$this->assertEqual($result, '2 months, 2 days ago');

		$result = $this->time->timeInWords(strtotime('2 months, 2 days'), array('end' => '3 months'));
		$this->assertPattern('/2 months/', $result);

		$result = $this->time->timeInWords(strtotime('2 months, 2 days'), array('end' => '1 month', 'format' => 'Y-m-d'));
		$this->assertEqual($result, 'on ' . date('Y-m-d', strtotime('2 months, 2 days')));
		
		$result = $this->time->timeInWords(strtotime('-2 months, -2 days'), array('end' => '-1 month', 'format' => 'Y-m-d'));
		$this->assertEqual('on ' . date('Y-m-d', strtotime('-2 months, -2 days')), $result);

		$result = $this->time->timeInWords(strtotime('-13 months, -5 days'), array('end' => '2 years'));
		$this->assertEqual($result, '1 year, 1 month, 5 days ago');

		$earlier = $this->time->timeInWords(strtotime('-5 days'), array('offset' => -4));
		$later = $this->time->timeInWords(strtotime('-5 days, -8 hours'), array('offset' => 4));
		$this->assertEqual($earlier, $later);

		$result = $this->time->timeInWords(strtotime('-2 hours'));
		$expected = '2 hours ago';
		$this->assertEqual($expected, $result);

		$result = $this->time->timeInWords(strtotime('-12 minutes'));
		$expected = '12 minutes ago';
		$this->assertEqual($expected, $result);

		$result = $this->time->timeInWords(strtotime('-12 seconds'));
		$expected = '12 seconds ago';
		$this->assertEqual($expected, $result);

		$time = strtotime('-3 years -12 months');
		$result = $this->time->timeInWords($time);
		$expected = 'on ' . date('j/n/y', $time);
		$this->assertEqual($expected, $result);
	}

	/**
	 * testRelative method
	 *
	 * @return void
	 */
	public function testRelative() {
		$result = $this->time->relativeTime('-1 week');
		$this->assertEqual($result, '1 week ago');
		$result = $this->time->relativeTime('+1 week');
		$this->assertEqual($result, '1 week');
	}

	/**
	 * testNice method
	 *
	 * @return void
	 */
	public function testNice() {
		$time = time() + (2 * Time::DAY);
		$this->assertEqual(date('D, M jS Y, H:i', $time), $this->time->nice($time));

		$time = time() - (2 * Time::DAY);
		$this->assertEqual(date('D, M jS Y, H:i', $time), $this->time->nice($time));

		$time = time();
		$this->assertEqual(date('D, M jS Y, H:i', $time), $this->time->nice($time));

		$time = 0;
		$this->assertEqual(date('D, M jS Y, H:i', time()), $this->time->nice($time));

		$time = null;
		$this->assertEqual(date('D, M jS Y, H:i', time()), $this->time->nice($time));
		
		$time = date('H:i', time());
		$expected = 'Fri, Dec 24th 2010, '.$time;
		$result = $this->time->nice('2010-12-24 '.$time.':00');
		$this->assertEqual($expected, $result);
		
		$expected = 'Sat, Dec 25th 2010, '.$time;
		$result = $this->time->nice('2010-12-24 '.$time.':00', 24); //offset 24 hours
		$this->assertEqual($expected, $result);
	}

	/**
	 * testNiceShort method
	 *
	 * @return void
	 */
	public function testNiceShort() {
		$time = time() + 2 * Time::DAY;
		if (date('Y', $time) == date('Y')) {
			$this->assertEqual(date('M jS, H:i', $time), $this->time->niceShort($time));
		} else {
			$this->assertEqual(date('M jSY, H:i', $time), $this->time->niceShort($time));
		}

		$time = time();
		$this->assertEqual('Today, '.date('H:i', $time), $this->time->niceShort($time));
		
		$time = time() + Time::DAY;
		$this->assertEqual('Tomorrow, '.date('H:i', $time), $this->time->niceShort($time));

		$time = time() - Time::DAY;
		$this->assertEqual('Yesterday, '.date('H:i', $time), $this->time->niceShort($time));
	}

	/**
	 * testDaysAsSql method
	 *
	 * @return void
	 */
	public function testDaysAsSql() {
		$begin = time();
		$end = time() + Time::DAY;
		$field = 'my_field';
		$expected = '(my_field >= \''.date('Y-m-d', $begin).' 00:00:00\') AND (my_field <= \''.date('Y-m-d', $end).' 23:59:59\')';
		$this->assertEqual($expected, $this->time->daysAsSql($begin, $end, $field));
	}

	/**
	 * testDayAsSql method
	 *
	 * @return void
	 */
	public function testDayAsSql() {
		$time = time();
		$field = 'my_field';
		$expected = '(my_field >= \''.date('Y-m-d', $time).' 00:00:00\') AND (my_field <= \''.date('Y-m-d', $time).' 23:59:59\')';
		$this->assertEqual($expected, $this->time->dayAsSql($time, $field));
	}

	/**
	 * testToUnix method
	 *
	 * @return void
	 */
	public function testToUnix() {
		$this->assertEqual(time(), $this->time->toUnix(time()));
		$this->assertEqual(strtotime('+1 day'), $this->time->toUnix('+1 day'));
		$this->assertEqual(strtotime('+0 days'), $this->time->toUnix('+0 days'));
		$this->assertEqual(strtotime('-1 days'), $this->time->toUnix('-1 days'));
		$this->assertEqual(false, $this->time->toUnix(''));
		$this->assertEqual(false, $this->time->toUnix(null));
	}

	/**
	 * testToAtom method
	 *
	 * @return void
	 */
	public function testToAtom() {
		$this->assertEqual(date('Y-m-d\TH:i:s\Z'), $this->time->toAtom(time()));
	}

	/**
	 * testToRss method
	 *
	 * @return void
	 */
	public function testToRss() {
		$this->assertEqual(date('r'), $this->time->toRss(time()));
	}

	/**
	 * testFormat method
	 *
	 * @access public
	 * @return void
	 */
	public function testFormat() {
		$format = 'D-M-Y';
		$arr = array(time(), strtotime('+1 days'), strtotime('+1 days'), strtotime('+0 days'));

		foreach ($arr as $val) {
			$this->assertEqual(date($format, $val), $this->time->format($format, $val));
		}

		$result = $this->time->format('Y-m-d', null, 'never');
		$this->assertEqual($result, 'never');
	}

	/**
	 * testOfGmt method
	 *
	 * @access public
	 * @return void
	 */
	public function testGmt() {
		$hour = 3;
		$min = 4;
		$sec = 2;
		$month = 5;
		$day = 14;
		$year = 2007;
		$time = mktime($hour, $min, $sec, $month, $day, $year);
		$expected = gmmktime($hour, $min, $sec, $month, $day, $year);
		$this->assertEqual($expected, $this->time->gmt(date('Y-n-j G:i:s', $time)));

		$hour = date('H');
		$min = date('i');
		$sec = date('s');
		$month = date('m');
		$day = date('d');
		$year = date('Y');
		$expected = gmmktime($hour, $min, $sec, $month, $day, $year);
		$this->assertEqual($expected, $this->time->gmt(null));
	}

	/**
	 * testIsToday method
	 *
	 * @access public
	 * @return void
	 */
	public function testIsToday() {
		$result = $this->time->isToday('+1 day');
		$this->assertFalse($result);
		$result = $this->time->isToday('+1 days');
		$this->assertFalse($result);
		$result = $this->time->isToday('+0 day');
		$this->assertTrue($result);
		$result = $this->time->isToday('-1 day');
		$this->assertFalse($result);
	}
/**
 * testIsThisWeek method
 *
 * @access public
 * @return void
 */
	function testIsThisWeek() {
		// A map of days which goes from -1 day of week to +1 day of week
		$map = array(
			'Mon' => array(-1, 7), 'Tue' => array(-2, 6), 'Wed' => array(-3, 5),
			'Thu' => array(-4, 4), 'Fri' => array(-5, 3), 'Sat' => array(-6, 2),
			'Sun' => array(-7, 1)
		);
		$days = $map[date('D')];

		for ($day = $days[0] + 1; $day < $days[1]; $day++) {
			$this->assertTrue($this->time->isThisWeek(($day > 0 ? '+' : '') . $day . ' days'));
		}
		$this->assertFalse($this->time->isThisWeek($days[0] . ' days'));
		$this->assertFalse($this->time->isThisWeek('+' . $days[1] . ' days'));
	}

	/**
	 * testIsThisMonth method
	 *
	 * @access public
	 * @return void
	 */
	function testIsThisMonth() {
		$result = $this->time->isThisMonth('+0 day');
		$this->assertTrue($result);
		$result = $this->time->isThisMonth($time = mktime(0, 0, 0, date('m'), mt_rand(1, 28), date('Y')));
		$this->assertTrue($result);
		$result = $this->time->isThisMonth(mktime(0, 0, 0, date('m'), mt_rand(1, 28), date('Y') - mt_rand(1, 12)));
		$this->assertFalse($result);
		$result = $this->time->isThisMonth(mktime(0, 0, 0, date('m'), mt_rand(1, 28), date('Y') + mt_rand(1, 12)));
		$this->assertFalse($result);

	}

	/**
	 * testIsThisYear method
	 *
	 * @access public
	 * @return void
	 */
	public function testIsThisYear() {
		$result = $this->time->isThisYear('+0 day');
		$this->assertTrue($result);
		$result = $this->time->isThisYear(mktime(0, 0, 0, mt_rand(1, 12), mt_rand(1, 28), date('Y')));
		$this->assertTrue($result);
	}

	/**
	 * testWasYesterday method
	 *
	 * @return void
	 */
	public function testWasYesterday() {
		$result = $this->time->wasYesterday('+1 day');
		$this->assertFalse($result);
		$result = $this->time->wasYesterday('+1 days');
		$this->assertFalse($result);
		$result = $this->time->wasYesterday('+0 day');
		$this->assertFalse($result);
		$result = $this->time->wasYesterday('-1 day');
		$this->assertTrue($result);
		$result = $this->time->wasYesterday('-1 days');
		$this->assertTrue($result);
		$result = $this->time->wasYesterday('-2 days');
		$this->assertFalse($result);
	}

	/**
	 * testIsTomorrow method
	 *
	 * @return void
	 */
	public function testIsTomorrow() {
		$result = $this->time->isTomorrow('+1 day');
		$this->assertTrue($result);
		$result = $this->time->isTomorrow('+1 days');
		$this->assertTrue($result);
		$result = $this->time->isTomorrow('+0 day');
		$this->assertFalse($result);
		$result = $this->time->isTomorrow('-1 day');
		$this->assertFalse($result);
	}

	/**
	 * testWasWithinLast method
	 *
	 * @return void
	 */
	public function testWasWithinLast() {
		$this->assertTrue($this->time->wasWithinLast('1 day', '-1 day'));
		$this->assertTrue($this->time->wasWithinLast('1 week', '-1 week'));
		$this->assertTrue($this->time->wasWithinLast('1 year', '-1 year'));
		$this->assertTrue($this->time->wasWithinLast('1 second', '-1 second'));
		$this->assertTrue($this->time->wasWithinLast('1 minute', '-1 minute'));
		$this->assertTrue($this->time->wasWithinLast('1 year', '-1 year'));
		$this->assertTrue($this->time->wasWithinLast('1 month', '-1 month'));
		$this->assertTrue($this->time->wasWithinLast('1 day', '-1 day'));

		$this->assertTrue($this->time->wasWithinLast('1 week', '-1 day'));
		$this->assertTrue($this->time->wasWithinLast('2 week', '-1 week'));
		$this->assertFalse($this->time->wasWithinLast('1 second', '-1 year'));
		$this->assertTrue($this->time->wasWithinLast('10 minutes', '-1 second'));
		$this->assertTrue($this->time->wasWithinLast('23 minutes', '-1 minute'));
		$this->assertFalse($this->time->wasWithinLast('0 year', '-1 year'));
		$this->assertTrue($this->time->wasWithinLast('13 month', '-1 month'));
		$this->assertTrue($this->time->wasWithinLast('2 days', '-1 day'));

		$this->assertFalse($this->time->wasWithinLast('1 week', '-2 weeks'));
		$this->assertFalse($this->time->wasWithinLast('1 second', '-2 seconds'));
		$this->assertFalse($this->time->wasWithinLast('1 day', '-2 days'));
		$this->assertFalse($this->time->wasWithinLast('1 hour', '-2 hours'));
		$this->assertFalse($this->time->wasWithinLast('1 month', '-2 months'));
		$this->assertFalse($this->time->wasWithinLast('1 year', '-2 years'));

		$this->assertFalse($this->time->wasWithinLast('1 day', '-2 weeks'));
		$this->assertFalse($this->time->wasWithinLast('1 day', '-2 days'));
		$this->assertFalse($this->time->wasWithinLast('0 days', '-2 days'));
		$this->assertTrue($this->time->wasWithinLast('1 hour', '-20 seconds'));
		$this->assertTrue($this->time->wasWithinLast('1 year', '-60 minutes -30 seconds'));
		$this->assertTrue($this->time->wasWithinLast('3 years', '-2 months'));
		$this->assertTrue($this->time->wasWithinLast('5 months', '-4 months'));

		$this->assertTrue($this->time->wasWithinLast('5 ', '-3 days'));
		$this->assertTrue($this->time->wasWithinLast('1   ', '-1 hour'));
		$this->assertTrue($this->time->wasWithinLast('1   ', '-1 minute'));
		$this->assertTrue($this->time->wasWithinLast('1   ', '-23 hours -59 minutes -59 seconds'));
	}
	/**
	 * testUserOffset method
	 *
	 * @return void
	 */
	public function testUserOffset() {
		if ($this->skipIf(!class_exists('DateTimeZone'), '%s DateTimeZone class not available.')) {
			return;
		}

		$timezoneServer = new DateTimeZone(date_default_timezone_get());
		$timeServer = new DateTime('now', $timezoneServer);
		$yourTimezone = $timezoneServer->getOffset($timeServer) / Time::HOUR;

		$expected = time();
		$result = $this->time->fromString(time(), $yourTimezone);
		$this->assertEqual($result, $expected);
	}

	/**
	 * test fromString()
	 *
	 * @return void
	 */
	public function testFromString() {
		$result = $this->time->fromString('');
		$this->assertFalse($result);

		$result = $this->time->fromString(0, 0);
		$this->assertFalse($result);

		$result = $this->time->fromString('+1 hour');
		$expected = strtotime('+1 hour');
		$this->assertEqual($result, $expected);

		$timezone = date('Z', time());
		$result = $this->time->fromString('+1 hour', $timezone);
		$expected = $this->time->convert(strtotime('+1 hour'), $timezone);
		$this->assertEqual($result, $expected);
	}
}

?>