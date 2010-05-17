<?php
/**
 * Form helper tests file
 *
 * @copyright     Copyright 2010, alkemann
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 *
 */

namespace al13_helpers\tests\cases;

use \al13_helpers\extensions\helper\Form;
use \lithium\tests\mocks\template\helper\MockFormRenderer;

class FormTest extends \lithium\test\Unit {

	public function setUp() {
		$this->form = new Form(array('context' => new MockFormRenderer()));
	}

	public function testMultipleFieldsWithOptions() {
		$result = $this->form->fields(array(
			'name',
			'surname' => array('label' => false),
			'present' => array('type' => 'checkbox')
		), array(
			'template' => '<li{:wrap}>{:label}{:input}{:error}</li>'
		));
		$this->assertTags($result, array(
			array('li' => array()),
				array('label' => array('for' => 'name')),
					'Name',
				'/label',
				array('input' => array('type' => 'text', 'name' => 'name')),
			'/li',
			array('li' => array()),
				array('input' => array('type' => 'text', 'name' => 'surname')),
			'/li',
			array('li' => array()),
				array('label' => array('for' => 'present')),
					'Present',
				'/label',
				array('input' => array('type' => 'hidden', 'value' => 0, 'name' => 'present')),
				array('input' => array('type' => 'checkbox', 'value' => 1, 'name' => 'present')),
			'/li',
		));
	}

}

?>