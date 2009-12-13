<?php

namespace al13_logable\controllers;

use \al13_logable\models\Log;
use \al13_logable\models\LogsView;

class LogsController extends \lithium\action\Controller {

	public function index() {
		$page = 1;
		$limit = 3;
		if (isset($this->request->params['page'])) {
			$page = $this->request->params['page'];
		}
		if (!empty($this->request->params['limit'])) {
			$limit = $this->request->params['limit'];
		}
		$order = array('descending' => 'true');
		$conditions = array('design' => 'log', 'view' => 'all', 'skip' => ($page - 1) * $limit);
		$total = Log::find('count');
		$data = Log::find('all', compact('conditions', 'limit', 'order'));
		return compact('data', 'limit', 'page', 'total');
	}
}

?>