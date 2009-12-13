<?php

namespace al13_tester\tests\mocks;

/**
 * Generic source mock that models can use to act normally without using a real source
 *
 * @author alkemann
 * @version 1.0
 */
class MockSource extends \lithium\data\Source {

	/**
	 * Counter used by the result method to lazy load results
	 *
	 * @var array
	 */
	protected $_iterator = 0;

	/**
	 * Temporary store fixtures of fake sources
	 * Set through the $mockSource->fixtures()
	 *
	 * @var array
	 */
	private $__data = array();

	/**
	 * Set an array of records as the fixtures of given source
	 *
	 * @param string $source
	 * @param array $fixtures if empty, will empty the fixtures (and return them)
	 * @return void|array
	 */
	public function fixtures($source, $fixtures = array()) {
		if (empty($fixtures)) {
			$tmp = $this->__data[$source];
			$this->__data[$source] = array();
			return $tmp;
		}
		$this->__data[$source] = array();
		foreach ($fixtures as $row) {
			$this->__data[$source][] = (object) $row;
		}
	}

	/**
	 * Creates a new record in source
	 *
	 * @param Query $query
	 * @param array $options
	 * @return boolean
	 */
	public function create($query, $options) {
		$model = $query->model();
		$source = $model::meta('source');
		$record = $query->record();
		$record->id = sizeof($this->__data[$source])+1;
		$this->__data[$source][] = (object) $record->data();
		return true;
	}
	/**
	 * Ask the source for the query'ed record
	 *
	 * @param Query $query
	 * @param array $options
	 * @return boolean
	 */
	public function read($query, $options) {
		$model = $query->model();
		$source = $model::meta('source');
		if (!isset($this->__data[$source])) {
			return array(); //array('error' => $source . ' doesnt exist'));
		}
		if (!empty($options['conditions'])) {
			$key = $this->locate($source, array('id' => $options['conditions']['id']));
			if ($key !== false) {
				return $this->__data[$source][$key];
			}
			return array(); //array('error' => 'record not found in '.$source));
		}
		return $this->__data[$source];
	}

	/**
	 * Update the contents of a record in source
	 *
	 * @param Query $query
	 * @param array $options
	 * @return boolean
	 */
	public function update($query, $options) {
		$model = $query->model();
		$source = $model::meta('source');
		$data = $query->record()->data();
		$key = $this->locate($source, array('id' => $data['id']));
		$this->__data[$source][$key] = (object) $data;
		return true;
	}

	/**
	 * Delete an entry in the source
	 *
	 * @param Query $query
	 * @param array $options
	 * @return boolean
	 */
	public function delete($query, $options) {
		$model = $query->model();
		$source = $model::meta('source');
		$key = $this->locate($source, array('id' => $options['record']->id));
		if ($key === false) {
			return false;
		}
		if ($this->__data[$source][$key] == end($this->__data[$source])) {
			unset($this->__data[$source][$key]);
		} else {
			$this->__data[$source][$key] = array_pop($this->__data[$source]);
		}
		return true;
	}

	/**
	 * Lazyload a result from the resource
	 *
	 * @param string $type
	 * @param mixed $resource
	 * @param object $context
	 * @return array
	 */
	public function result($type, $resource, $context) {
		$result = null;
		switch ($type) {
			case 'next':
				if (!is_array($resource)) {
					$result = $resource;
				} elseif (isset($resource[$this->_iterator])) {
					$result = $resource[$this->_iterator++];
				} else {
					$this->_iterator = 0;
				}
			break;
			case 'close':
				unset($resource);
				$result = null;
			break;
		}
		return $result;
	}

	/**
	 * Returns a list of sources currently in the db
	 *
	 * @param null $class
	 * @return array
	 */
	public function entities($class = null) {
		return array_keys($this->__data);
	}

	/**
	 * Returns an array of fields of the first record in source
	 *
	 * @param string $entity
	 * @param null $meta
	 * @return array
	 */
	public function describe($entity, $meta = null) {
		if (isset($this->__data[$entity]) && !empty($this->__data[$entity])) {
			return array_keys($this->__data[$entity][0]);
		}
		return array();
	}

	/**
	 * Set source to use Document
	 *
	 * @param $class
	 * @return void
	 */
	public function configureClass($class) {
		return array(
			'classes' => array(
				'record' => '\lithium\data\model\Document',
				'recordSet' => '\lithium\data\model\Document'
			)
		);
	}

	/**
	 * Locate a record in the given source
	 *
	 * @param string $source name of "table"
	 * @param array $conditions array('id' => 3)
	 * @return mixed int/false
	 */
	protected function locate($source, $conditions = array()) {
		if (empty($conditions)) {
			return false;
		}
		$key = -1;
		foreach ($this->__data[$source] as $k => $row) {
			foreach ($conditions as $field => $con) {
				if ($row->{$field} == $con) {
					$key = $k;
					break;
				}
			}
		}
		if ($key < 0) {
			return false;
		}
		return $key;
	}

/********/

	public function connect() {
		return true;
	}
	public function disconnect() {
		return true;
	}
	public function columns($query, $resource = null, $context = null) {
		return null;
	}
}

?>