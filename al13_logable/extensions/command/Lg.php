<?php

namespace al13_logable\extensions\command;

use \al13_logable\models\Log;
use \al13_logable\models\LogsView;

class Lg extends \lithium\console\Command {

	public function install() {
		$this->header('Logg installer');
		\al13_logable\models\Log::install();
		if ( LogsView::create()->save() ) {
			$this->out('Installed');
			return true;
		} else {
			$this->out('Install fail. Make sure couch is running and try again.');
			return false;
		}
	}

	public function update() {
		$this->header('Logg updater');
		$view = LogsView::find('_design/log');
		if (!isset($view->error)) {
			$view->delete();
		}
		if ( LogsView::create()->save() ) {
			$this->out('Installed');
			return true;
		} else {
			$this->out('Install fail. Make sure couch is running and try again.');
			return false;
		}
	}
}

?>