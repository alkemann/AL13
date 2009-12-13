<?php

namespace al13_logable\tests\cases;

use \al13_logable\tests\mocks\MockLog;
use \al13_logable\tests\mocks\MockCar;
use \lithium\data\Connections;

class LogFilterTest extends \lithium\test\Unit {

	public function _init() {
		Connections::add('mock-source', '\al13_logable\tests\mocks\MockSource');
	}

	public function setUp() {
		Connections::get('mock-source')->fixtures(MockCar::meta('source'), MockCar::fixtures());
		Connections::get('mock-source')->fixtures(MockLog::meta('source'), MockLog::fixtures());
		MockCar::clearFilters();
	}

	public function testFilters() {
		$expected = array();
		$result = MockCar::getFilters();
		$this->assertEqual($expected, $result);

		MockLog::setModelEvents(array('\al13_logable\tests\mocks\MockCar'));

		$filters = MockCar::getFilters();
		$this->assertEqual(2, sizeof($filters));
		$this->assertTrue(isset($filters['save']));
		$this->assertTrue(isset($filters['delete']));
	}

	public function testCreateFilter() {
		MockLog::setModelEvents(array('\al13_logable\tests\mocks\MockCar'), array('create'));
		$saved = MockCar::create()->save();

		$expected = array('name' => 'Lily', 'brand' => 'Ford', 'plate' => 'R0KKY', 'id' => 3);
		$res = MockCar::find(3);
		$result = $res->data();
		$this->assertEqual($expected, $result);

		$res = MockLog::find('all');
		$logs = $res->data();

		$this->assertIdentical(4, sizeof($logs));
		$this->assertEqual('Lily', $logs[3]['title']);
		$this->assertEqual('create', $logs[3]['action']);
		$this->assertEqual('MockCar', $logs[3]['model']);
	}

	public function testUpdateFilter() {
		MockLog::setModelEvents(array('\al13_logable\tests\mocks\MockCar'), array('update'));

		$car = MockCar::find(2);
		$car->plate = 'IR2';
		$this->assertTrue($car->save());

		$car = MockCar::find(2);
		$this->assertEqual('IR2', $car->plate);

		$res = MockLog::find('all');
		$logs = $res->data();

		$this->assertIdentical(4, sizeof($logs));
		$this->assertEqual('update', $logs[3]['action']);
	}

	public function testCreateAndUpdateWithDefaultFilters() {
		MockLog::setModelEvents(array('\al13_logable\tests\mocks\MockCar'));

		$saved = MockCar::create()->save();
		$result = MockLog::find('all');
		$this->assertIdentical(4, sizeof($result));

		$car = MockCar::find(1);
		$car->plate = 'IR2';
		$car->save();

		$result = MockLog::find('all');
		$this->assertIdentical(5, sizeof($result));
	}

	public function testSaveFilter() {
		MockLog::setModelEvents(array('\al13_logable\tests\mocks\MockCar'), array('save'));

		$saved = MockCar::create()->save();
		$result = MockLog::find('all');
		$this->assertIdentical(4, sizeof($result));

		$car = MockCar::find(1);
		$car->plate = 'IR2';
		$car->save();

		$result = MockLog::find('all');
		$this->assertIdentical(5, sizeof($result));
	}

	public function testDeleteFilter() {
		MockLog::setModelEvents(array('\al13_logable\tests\mocks\MockCar'), array('delete'));

		$car = MockCar::find(2);
		$this->assertTrue($car->delete());

		$logs = MockLog::find('all');
		$this->assertIdentical(4, sizeof($logs));
		$this->assertEqual('delete', $logs[3]['action']);
	}
}

?>