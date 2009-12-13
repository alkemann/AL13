<?php

namespace al13_logable\tests\mocks;

use \lithium\data\model\Document;

class MockLog extends \al13_logable\models\Log {

	protected $_meta = array(
		'connection' => 'mock-source',
		'source' => 'mock_logs',
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

	public static function fixtures() {
		return array(
			array(
				'model' => 'MockCar', 'action' => 'create', 'pk' => 1, 'title' => 'Rose',
				'created' => '2009-12-13 03:56:06', 'id' => 1
			),
			array(
				'model' => 'MockCar', 'action' => 'create', 'pk' => 2, 'title' => 'Petal',
				'created' => '2009-12-13 03:56:06', 'id' => 2
			),
			array(
				'model' => 'MockCar', 'action' => 'update', 'pk' => 1, 'title' => 'Rose',
				'created' => '2009-12-13 03:56:06', 'id' => 3
			),
		);
	}
}

?>