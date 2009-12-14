<?php

namespace al13_tester\tests\cases;

use \al13_tester\tests\mocks\MockPost;
use \al13_tester\tests\mocks\MockCar;
use \al13_tester\tests\mocks\MockUser;
use \lithium\data\Connections;

class TestSourceTest extends \lithium\test\Unit {

	public function _init($options = array()) {
		Connections::add('test-source', '\al13_tester\test\data\TestSource');
	}

	public function testSource() {
		$result = Connections::get('test-source');
		$this->assertTrue(is_a($result,'\al13_tester\test\data\TestSource'));
	}

	public function testEmptySources() {
		$result = MockPost::find('all');
		//$result = $result->data();
		$this->assertTrue(empty($result));
	}

	public function testFixtures() {
		$mockTestData = array(
			array('id' => 1, 'title' => 'One'),
			array('id' => 2, 'title' => 'Two'),
			array('id' => 3, 'title' => 'Three')
		);
		Connections::get('test-source')->records('mock_tests', $mockTestData);

		$expected = array(
			(object) array('id' => 1, 'title' => 'One'),
			(object) array('id' => 2, 'title' => 'Two'),
			(object) array('id' => 3, 'title' => 'Three')
		);
		$result = Connections::get('test-source')->records('mock_tests');
		$this->assertEqual($expected, $result);

		$result = Connections::get('test-source')->records('mock_tests');
		$this->assertEqual(array(), $result);
	}

	public function testMockPostFind() {
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockPost'));

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
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockCar'));

		$expected = 'Rose';
		$car = MockCar::find(1);
		$result = $car->name;
		$this->assertEqual($expected, $result);
	}

	public function testCreateEmptySource() {
		Connections::get('test-source')->setup(
			array('\al13_tester\tests\mocks\MockCar'),
			array('fixtures' => false)
		);
		$new = MockCar::create();
		$this->assertTrue($new->save());

		$expected = array('id' => 1, 'name' => 'Lily', 'brand' => 'Ford', 'plate' => 'R0KKY' );
		$car = MockCar::find($new->id);
		$result = $car->data();
		$this->assertEqual($expected, $result);
	}

	public function testCreateWithSource() {
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockCar'));

		$new = MockCar::create();
		$this->assertTrue($new->save());

		$expected = array('id' => 3, 'name' => 'Lily', 'brand' => 'Ford', 'plate' => 'R0KKY' );
		$car = MockCar::find($new->id);
		$result = $car->data();
		$this->assertEqual($expected, $result);
	}

	public function testEdit() {
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockCar'));

		$car = MockCar::find(2);
		$car->name = 'Lithium';
		$this->assertTrue($car->save());

		$expected = array('id' => 2, 'name' => 'Lithium', 'brand' => 'BMW', 'plate' => 'FL0WR' );
		$edited = MockCar::find($car->id);
		$result = $edited->data();
		$this->assertEqual($expected, $result);
	}

	public function testDelete() {
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockCar'));

		$car = MockCar::find(2);
		$this->assertTrue($car->delete());

		$deleted = MockCar::find(3);
		$this->assertNull($deleted);
	}

	public function testPostDelete() {
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockPost'));

		$this->assertTrue(MockPost::find(1)->delete());
		$this->assertTrue(MockPost::find(3)->delete());

		$posts = MockPost::find('all');
		$result = $posts->data();
		$this->assertEqual(4, $result[0]['id']);
		$this->assertEqual(2, $result[1]['id']);
	}

	public function testDescribe() {
		Connections::get('test-source')->setup(array('\al13_tester\tests\mocks\MockUser'));
		$expected = array(
			'id' => array(
				'type' => 'int',
				'null' => false,
				'length' => '10',
				'default' => null
			),
			'name' => array(
				'type' => 'varchar',
				'null' => false,
				'length' => '10',
				'default' => null
			),
			'description' => array(
				'type' => 'text',
				'length' => NULL,
				'null' => true,
				'default' => NULL
			),
			'status' => array(
				'type' => 'tinyint',
				'length' => '1',
				'null' => false,
				'default' => 0
			),
			'created' => array(
				'type' => 'datetime'
			)
		);
		$result = Connections::get('test-source')->describe(MockUser::meta('source'));
		$this->assertEqual($expected, $result);

		$expected = 3;
		$users = MockUser::find('all');
		$result = sizeof($users->data());
		$this->assertEqual($expected, $result);
	}
}

?>