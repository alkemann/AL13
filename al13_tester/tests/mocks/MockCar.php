<?php

namespace al13_tester\tests\mocks;

class MockCar extends \al13_tester\test\data\TestModel {

	protected $_schema = array(
		'id' => array('type' => 'integer'),
		'name' => array('default' => 'Lily'),
		'brand' => array('default' => 'Ford'),
		'plate' => array('default' => 'R0KKY')
	);

	protected $_meta = array(
		'connection' => 'test-source',
		'source' => 'mock_cars',
		'title' => 'name'
	);

	public static function records() {
		return array(
			array('id' => 1, 'name' => 'Rose', 'brand' => 'BMW', 'plate' => 'IRH0T'),
			array('id' => 2, 'name' => 'Petal', 'brand' => 'BMW', 'plate' => 'FL0WR')
		);
	}
}

?>