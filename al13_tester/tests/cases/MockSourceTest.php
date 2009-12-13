<?php

namespace al13_tester\tests\cases;

use \al13_tester\tests\mocks\MockPost;
use \al13_tester\tests\mocks\MockCar;
use \lithium\data\Connections;

class MockSourceTest extends \lithium\test\Unit {

	public function _init($options = array()) {
		Connections::add('mock-source', '\al13_tester\tests\mocks\MockSource');
	}

	public function testSource() {
		$result = Connections::get('mock-source');
		$this->assertTrue(is_a($result,'\al13_tester\tests\mocks\MockSource'));
	}

	public function testEmptySources() {
		$result = MockPost::find('all');
		$result = $result->data();
		$this->assertTrue(empty($result));
	}

	public function testSetFixtures() {
		$mockTestData = array(
			array('id' => 1, 'title' => 'One'),
			array('id' => 2, 'title' => 'Two'),
			array('id' => 3, 'title' => 'Three')
		);
		Connections::get('mock-source')->fixtures('mock_tests', $mockTestData);

		$expected = array(
			(object) array('id' => 1, 'title' => 'One'),
			(object) array('id' => 2, 'title' => 'Two'),
			(object) array('id' => 3, 'title' => 'Three')
		);
		$result = Connections::get('mock-source')->fixtures('mock_tests');
		$this->assertEqual($expected, $result);
	}

	public function testMockPostFind() {
		Connections::get('mock-source')->fixtures(MockPost::meta('source'), MockPost::fixtures());

		$expected = array(
			'id' => 2,
			'title' => 'Once upon a midnight dreary',
			'content' => 'Lorem ipsom content',
			'created' => '2009-12-12 08:01:00'
		);
		$post = MockPost::find(2);
		$result = $post->data();
		$this->assertEqual($expected, $result);

		$expected = 4;
		$posts = MockPost::find('all');
		$result = sizeof($posts->data());
		$this->assertEqual($expected, $result);
	}

	public function testMockCarFind() {
		Connections::get('mock-source')->fixtures(MockCar::meta('source'), MockCar::fixtures());

		$expected = 'Rose';
		$car = MockCar::find(1);
		$result = $car->name;
		$this->assertEqual($expected, $result);
	}

	public function testCreateEmptySource() {
		Connections::get('mock-source')->fixtures(MockCar::meta('source'));
		$new = MockCar::create();
		$this->assertTrue($new->save());

		$expected = array('id' => 1, 'name' => 'Lily', 'brand' => 'Ford', 'plate' => 'R0KKY' );
		$car = MockCar::find($new->id);
		$result = $car->data();
		$this->assertEqual($expected, $result);
	}

	public function testCreateWithSource() {
		Connections::get('mock-source')->fixtures(MockCar::meta('source'), MockCar::fixtures());
		$new = MockCar::create();
		$this->assertTrue($new->save());

		$expected = array('id' => 3, 'name' => 'Lily', 'brand' => 'Ford', 'plate' => 'R0KKY' );
		$car = MockCar::find($new->id);
		$result = $car->data();
		$this->assertEqual($expected, $result);
	}

	public function testEdit() {
		Connections::get('mock-source')->fixtures(MockCar::meta('source'), MockCar::fixtures());

		$car = MockCar::find(2);
		$car->name = 'Lithium';
		$this->assertTrue($car->save());

		$expected = array('id' => 2, 'name' => 'Lithium', 'brand' => 'BMW', 'plate' => 'FL0WR' );
		$edited = MockCar::find($car->id);
		$result = $edited->data();
		$this->assertEqual($expected, $result);
	}

	public function testDelete() {
		Connections::get('mock-source')->fixtures(MockCar::meta('source'), MockCar::fixtures());

		$car = MockCar::find(2);
		$this->assertTrue($car->delete());

		$deleted = MockCar::find(3);
		$this->assertNull($deleted);
	}
}

?>