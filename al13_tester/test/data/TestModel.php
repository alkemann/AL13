<?php

namespace al13_tester\test\data;

class TestModel extends \lithium\data\Model {

	protected $_meta = array(
		'connection' => 'test-source'
	);

	public static function __init($options = array()) {
		$self = static::_instance();
		if (!isset($self->_meta['source'])) {
			$model = get_class($self);
			$tmp = explode('\\', $model);
			$modelName = end($tmp);
			$self->_meta['source'] = \lithium\util\Inflector::tableize($modelName);
		}
		parent::__init($options);
	}
	public static function records() {
		return array();
	}
}

?>