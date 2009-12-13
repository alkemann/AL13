<?php

namespace al13_logable\models;

use \lithium\data\Connections;

class Log extends \lithium\data\Model {

	protected $_meta = array(
		'source' => 'logs'
	);

	protected $_schema = array(
		'id' => array('type' => 'string'),
		'model'=> array('type' => 'string'),
		'action'=> array('type' => 'string'),
		'title' => array('type' => 'string'),
		'pk' => array('type' => 'string'),
		'created'=> array('type' => 'string')
	);

	/**
	 *
	 * @param array $models
	 * @param array $events
	 * @param array $options
	 * @return void
	 */
	public static function setModelEvents($models, $events = array(), $options = array()) {
		$logmodel = get_called_class();
		if (empty($events)) {
			$events = array('create','update','delete');
		}
		if (in_array('save',$events)) {
			foreach ($events as $i => $v) {
				if ($v == 'save') {
					unset($events[$i]);
					break;
				}
			}
			$events[] = 'create';
			$events[] = 'update';
		}
		foreach ($models as $model) {
			foreach ($events as $event) {
				$filter = $event;
				if ($event == 'create' || $event == 'update') {
					$filter = 'save';
					if  ($event == 'create' && in_array('update', $events)) {
						continue; // so save filter is only added once.
					}
				}

				$model::applyFilter($filter, function($self, $params, $chain) use ($logmodel, $events) {
					$filteredMethod = $chain->method();
					switch ($filteredMethod) {
						case 'save' :
							$action = (isset($params['record']->id)) ? 'update' : 'create';
							if (!in_array($action, $events)) {
								return $chain->next($self, $params, $chain);
							}
							break;
						default:
							$action = $filteredMethod;
					}
					$res =  $chain->next($self, $params, $chain);
					if ($res) {
						'\\'.$logmodel::create(array(
							'model' => $self::invokeMethod('_name'),
							'action' => $action,
							'pk' => $params['record']->id,
							'title' => $params['record']->{$self::meta('title')}
						))->save();
					}
					return $res;
				});
			}
		}
	}

	public static function __init($options = array()) {
		parent::__init($options);
		$self = static::_instance();
		$self->_finders['count'] = function($self, $params, $chain) {
			$result = Connections::get($self::meta('connection'))->get(
				$self::meta('source') . '/_design/log/_view/count'
			);
			if (empty($result->total_rows)) {
				return 0;
			}
			return $result->total_rows;
		};
		static::applyFilter('save', function($self, $params, $chain) {
			if (!$params['record']->id) {
				$params['record']->created = date('Y-m-d h:i:s');
			}
			return $chain->next($self, $params, $chain);
		});
	}

	public static function install() {
		return Connections::get(static::meta('connection'))->put(static::meta('source'));
	}
}

?>