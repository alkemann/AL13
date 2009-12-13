<?php

namespace al13_logable\models;

class LogsView extends \lithium\data\Model {

	protected $_meta = array(
		'source' => 'logs'
	);

	protected $_schema = array(
		'id' => array('default' => '_design/log'),
		'language' => array('default' => 'javascript'),
		'views' => array('default' => array(
			'all' => array('map' => 'function(doc) { emit(doc.created, doc); }'),
			'count' => array('map' => 'function(doc) {emit(doc._id, null);}')
		)),
	);

}

?>