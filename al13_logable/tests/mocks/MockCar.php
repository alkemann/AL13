<?php

namespace al13_logable\tests\mocks;

use \lithium\data\model\Document;

class MockCar extends \lithium\data\Model {

	protected $_schema = array(
		'id' => array('type' => 'integer'),
		'name' => array('default' => 'Lily'),
		'brand' => array('default' => 'Ford'),
		'plate' => array('default' => 'R0KKY')
	);

	protected $_meta = array(
		'connection' => 'mock-source',
		'source' => 'mock_cars',
		'key' => 'id',
		'title' => 'name'
	);

	protected $_classes = array(
		'query' => '\lithium\data\model\Query',
		'record' => '\lithium\data\model\Document',
		'validator' => '\lithium\util\Validator',
		'recordSet' => '\lithium\data\model\Document',
		'connections' => '\lithium\data\Connections'
	);

	public static function clearFilters() {
		static::_instance()->_instanceFilters = array();
	}

	public static function getFilters() {
		return static::_instance()->_instanceFilters;
	}
	public static function fixtures() {
		return array(
			array('name' => 'Rose', 'brand' => 'BMW', 'plate' => 'IRH0T', 'id' => 1),
			array('name' => 'Petal', 'brand' => 'BMW', 'plate' => 'FL0WR', 'id' => 2)
		);
	}
}

?>