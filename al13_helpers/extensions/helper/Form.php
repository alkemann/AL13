<?php
namespace al13_helpers\extensions\helper;

class Form extends \lithium\template\helper\Form {

	/**
	 * Generates a form field with a label, input, and error message (if applicable), all contained
	 * within a wrapping element.
	 *
	 * {{{
	 *  echo $this->form->field('name');
	 *  echo $this->form->field('present', array('type' => 'checkbox'));
	 *  echo $this->form->field(array('email' => 'Enter a valid email'));
	 *  echo $this->form->field(array('name','email','phone'),array('div' => false));
	 *  echo $this->form->field(array(
	 *  		'name' => array('label' => false),
	 *  		'present' => array('type' => 'checkbox')
	 *  	), array('template' => '<li{:wrap}>{:label}{:input}{:error}</li>'
	 *  ));
	 * }}}
	 * @param mixed $name The name of the field to render. If the form was bound to an object
	 *                   passed in `create()`, `$name` should be the name of a field in that object.
	 *                   Otherwise, can be any arbitrary field name, as it will appear in POST data.
	 *                   Alternatively supply an array of fields that will use the same options
	 *                   array($field1 => $label1, $field2, $field3 => $label3)
	 * @param array $options Rendering options for the form field. The available options are as
	 *              follows:
	 *              - `'label'` _mixed_: A string or array defining the label text and / or
	 *                parameters. By default, the label text is a human-friendly version of `$name`.
	 *                However, you can specify the label manually as a string, or both the label
	 *                text and options as an array, i.e.:
	 *                `array('label text' => array('class' => 'foo', 'any' => 'other options'))`.
	 *              - `'type'` _string_: The type of form field to render. Available default options
	 *                are: `'text'`, `'textarea'`, `'select'`, `'checkbox'`, `'password'` or
	 *                `'hidden'`, as well as any arbitrary type (i.e. HTML5 form fields).
	 *              - `'template'` _string_: Defaults to `'template'`, but can be set to any named
	 *                template string, or an arbitrary HTML fragment. For example, to change the
	 *                default wrapper tag from `<div />` to `<li />`, you can pass the following:
	 *                `'<li{:wrap}>{:label}{:input}{:error}</li>'`.
	 *              - `'wrap'` _array_: An array of HTML attributes which will be embedded in the
	 *                wrapper tag.
	 *              - `list` _array_: If `'type'` is set to `'select'`, `'list'` is an array of
	 *                key/value pairs representing the `$list` parameter of the `select()` method.
	 * @return string Returns a form input (the input type is based on the `'type'` option), with
	 *         label and error message, wrapped in a `<div />` element.
	 */
	public function fields($name, array $options = array()) {
		if (is_array($name)) {
			$return = '';
			foreach ($name as $field => $label) {
				if (is_numeric($field)) {
					$field = $label;
					unset($label);
				}
				$fieldOptions = array();
				if (isset($label) && is_array($label)) {
					$fieldOptions = $label;
					unset($label);
				}
				$return .= $this->field($field, compact('label') + $fieldOptions + $options);
			}
			return $return;
		}
		$defaults = array(
			'label' => null,
			'type' => 'text',
			'template' => 'field',
			'wrap' => array(),
			'list' => null
		);
		list($options, $fieldOptions) = $this->_options($defaults, $options);
		list($name, $options, $template) = $this->_defaults(__FUNCTION__, $name, $options);

		if ($options['template'] != $defaults['template']) {
			$template = $options['template'];
		}
		$wrap = $options['wrap'];
		$type = $options['type'];
		$label = $input = null;

		if ($options['label'] === null || $options['label']) {
			$label = $this->label($name, $options['label']);
		}

		switch (true) {
			case ($type == 'select'):
				$input = $this->select($name, $options['list'], $fieldOptions);
			break;
			default:
				$input = $this->{$type}($name, $fieldOptions);
			break;
		}
		$error = ($this->_binding) ? $this->error($name) : null;
		$params = compact('wrap', 'label', 'input', 'error');

		return $this->_render(__METHOD__, $template, $params);
	}

}

?>