<?php

namespace al13_tester\tests\mocks;

class MockPost extends \al13_tester\test\data\TestModel {

	protected $_schema = array(
		'id' => array('type' => 'integer'),
		'title' => array('type' => 'string'),
		'content' => array('type' => 'text'),
		'created' => array('type' => 'datetime')
	);

	protected $_meta = array(
		'connection' => 'test-source',
		'source' => 'mock_posts'
	);

	public static function records() {
		return array(
			array(
				'id' => 1,
				'title' => 'A story of two stones',
				'content' => 'Lorem ipsom content',
				'created' => '2009-12-12 08:00:00'
			),
			array(
				'id' => 2,
				'title' => 'Once upon a midnight dreary',
				'content' => 'Lorem ipsom content',
				'created' => '2009-12-12 08:01:00'
			),
			array(
				'id' => 3,
				'title' => 'On that pallid bust of Phallas',
				'content' => 'Lorem ipsom content',
				'created' => '2009-12-12 08:02:00'
			),
			array(
				'id' => 4,
				'title' => 'Nevermore!',
				'content' => 'Lorem ipsom content',
				'created' => '2009-12-12 08:03:00'
			),
		);
	}
}

?>