<?php

/**
 * Campaigner API Custom Field tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package		Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_custom_field' .EXT;

class Test_campaigner_api_custom_field extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Properties.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_props;
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Runs before each test.
	 *
	 * @access	public
	 * @return	void
	 */
	public function setUp()
	{
		parent::setUp();
		
		$this->_props = array(
			'key'		=> '[FavoriteColor]',
			'name'		=> 'Favourite Colour',
			'options'	=> array('Red', 'Green', 'Blue'),
			'type'		=> Campaigner_api_custom_field::DATATYPE_MULTI_SELECT_ONE
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$field = new Campaigner_api_custom_field($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['key'], $field->get_key());
		$this->assertIdentical($this->_props['name'], $field->get_name());
		$this->assertIdentical($this->_props['options'], $field->get_options());
		$this->assertIdentical($this->_props['type'], $field->get_type());
	}
	
	
	public function test_constructor__invalid_property()
	{
		// Dummy data.
		$this->_props['INVALID'] = 'INVALID';
		
		// Tests. If this doesn't throw an error, we're good.
		new Campaigner_api_custom_field($this->_props);
	}
	
	
	public function test_add_option__success()
	{
		$field 		= new Campaigner_api_custom_field($this->_props);
		$new_option = 'Purple';
		
		$this->_props['options'][] = $new_option;
		$this->assertIdentical($this->_props['options'], $field->add_option($new_option));
	}
	
	
	public function test_add_option__invalid_values()
	{
		$field = new Campaigner_api_custom_field($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['options'], $field->add_option(FALSE));
		$this->assertIdentical($this->_props['options'], $field->add_option(NULL));
		$this->assertIdentical($this->_props['options'], $field->add_option(100));
		$this->assertIdentical($this->_props['options'], $field->add_option(new stdClass()));
	}
	
	
	public function test_set_key__invalid_values()
	{
		$field = new Campaigner_api_custom_field($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['key'], $field->set_key(FALSE));
		$this->assertIdentical($this->_props['key'], $field->set_key(NULL));
		$this->assertIdentical($this->_props['key'], $field->set_key(100));
		$this->assertIdentical($this->_props['key'], $field->set_key(new stdClass()));
	}
	
	
	public function test_set_name__invalid_values()
	{
		$field = new Campaigner_api_custom_field($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['name'], $field->set_name(FALSE));
		$this->assertIdentical($this->_props['name'], $field->set_name(NULL));
		$this->assertIdentical($this->_props['name'], $field->set_name(100));
		$this->assertIdentical($this->_props['name'], $field->set_name(new stdClass()));
	}
	
	
	public function test_set_type__invalid_values()
	{
		$field = new Campaigner_api_custom_field($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['type'], $field->set_type(FALSE));
		$this->assertIdentical($this->_props['type'], $field->set_type(NULL));
		$this->assertIdentical($this->_props['type'], $field->set_type(new stdClass()));
		$this->assertIdentical($this->_props['type'], $field->set_type('INVALID_TYPE'));
	}
	
	
	public function test_to_array()
	{
		$field = new Campaigner_api_custom_field($this->_props);
		$field_array = $field->to_array();
		
		ksort($this->_props);
		ksort($field_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $field_array);
	}
	
}


/* End of file		: test.campaigner_api_custom_field.php */
/* File location	: third_party/campaigner/tests/test.campaigner_api_custom_field.php */