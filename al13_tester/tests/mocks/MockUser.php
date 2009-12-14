<?php

namespace al13_tester\tests\mocks;

class MockUser extends \al13_tester\test\data\TestModel {

	protected $_schema = array(
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

	protected $_meta = array(
		'connection' => 'test-source',
		'source' => 'mock_users',
		'key' => 'id',
		'title' => 'name'
	);

	public static function records() {
		return array(
			array(
				'id' => 1,
				'name' => 'moe',
				'description' => null,
				'status' => 1,
				'created' => '2009-12-12 08:00:00'
			),
			array(
				'id' => 2,
				'name' => 'santa',
				'description' => 'Big, fat, red and jolly',
				'status' => 0,
				'created' => '2009-12-12 08:01:00'
			),
			array(
				'id' => 3,
				'name' => 'ralph',
				'description' => null,
				'status' => 1,
				'created' => '2009-12-12 08:02:00'
			)
		);
	}
}

?>