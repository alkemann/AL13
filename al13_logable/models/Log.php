<?php

namespace al13_logable\models;

/**
 * This model should not be used directly but rather extended in your app so you can
 * control the meta, schema, created field and count implementations.
 *
 *
 */
class Log extends \lithium\data\Model {

	/**
	 * Override in model as needed, should be at least these
	 *
	 * @var array
	 */
	protected $_schema = array(
		'id' => array('type' => 'string'),
		'model'=> array('type' => 'string'),
		'action'=> array('type' => 'string'),
		'title' => array('type' => 'string'),
		'pk' => array('type' => 'string'),
		'created'=> array('type' => 'string')
	);

	/**
	 * For each fully namespaced model given, setup log filters on each event given.
	 * If no events is given, save and delete filters will be applied
	 *
	 * @param array $models
	 * @param array $events
	 * @param array $options
	 * @return void
	 */
	public static function events($models, $events = array(), $options = array()) {
		$logmodel = '\\'.get_called_class();
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
				} elseif($event == 'read') {
					$filter = 'find';
				}

				$model::applyFilter($filter, function($self, $params, $chain) use ($logmodel, $events) {
					$filteredMethod = $chain->method();
					$action = (isset($params['record']->id)) ? 'update' : 'create';
					$result =  $chain->next($self, $params, $chain);
					switch ($filteredMethod) {
						case 'find' :
							if ($params['type'] != 'first') {
								return $result;
							}
							$action = 'read';
							if ($result) {
								$pk = $result->id;
								$title = $result->{$self::meta('title')};
							}
							break;
						case 'save' :
							if (!in_array($action, $events)) {
								return $result;
							}
							$pk = $params['record']->id;
							$title = $params['record']->{$self::meta('title')};
							break;
						default:
							$action = $filteredMethod;
							$pk = $params['record']->id;
							$title = $params['record']->{$self::meta('title')};
					}
					if ($result) {
						$logData = array(
							'model' => $self::invokeMethod('_name'),
							'action' => $action,
							'pk' => $pk,
							'title' => $title
						);
						$logmodel::create($logData)->save();
					}
					return $result;
				});
			}
		}
	}
}

?>