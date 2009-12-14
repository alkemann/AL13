<?php

namespace al13_logable\tests\mocks;

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
			array('name' => 'Rose', 'brand' => 'BMW', 'plate' => 'IRH0T', 'id' => 1),
			array('name' => 'Petal', 'brand' => 'BMW', 'plate' => 'FL0WR', 'id' => 2)
		);
	}
}

?>