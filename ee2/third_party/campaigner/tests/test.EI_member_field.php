<?php

/**
 * EI Member Field tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package 	EI
 */

require_once PATH_THIRD .'campaigner/classes/EI_member_field.php';

class Test_EI_member_field extends Testee_unit_test_case {
	
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
			'id'		=> 'm_field_id_10',
			'label'		=> 'Favourite Colour',
			'options'	=> array('Red', 'Blue', 'Green'),
			'type'		=> EI_member_field::DATATYPE_SELECT
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$field = new EI_member_field($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $field->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		$this->_props['INVALID'] = 'INVALID';
		
		// If no error is thrown, we're golden.
		new EI_member_field($this->_props);
	}
	
	
	public function test_add_option__success()
	{
		$field 		= new EI_member_field($this->_props);
		$new_option = 'Purple';
		
		$this->_props['options'][] = $new_option;
		
		$this->assertIdentical($this->_props['options'], $field->add_option($new_option));
	}
	
	
	public function test_add_options__invalid_values()
	{
		$field = new EI_member_field($this->_props);
		
		$this->assertIdentical($this->_props['options'], $field->add_option(NULL));
		$this->assertIdentical($this->_props['options'], $field->add_option(FALSE));
		$this->assertIdentical($this->_props['options'], $field->add_option(100));
		$this->assertIdentical($this->_props['options'], $field->add_option(new StdClass()));
	}
	
	
	public function test_set_id__invalid_values()
	{
		$field = new EI_member_field($this->_props);
		
		$this->assertIdentical($this->_props['id'], $field->set_id(NULL));
		$this->assertIdentical($this->_props['id'], $field->set_id(FALSE));
		$this->assertIdentical($this->_props['id'], $field->set_id(new StdClass()));
	}
	
	
	public function test_set_label__invalid_values()
	{
		$field = new EI_member_field($this->_props);
		
		$this->assertIdentical($this->_props['label'], $field->set_label(NULL));
		$this->assertIdentical($this->_props['label'], $field->set_label(FALSE));
		$this->assertIdentical($this->_props['label'], $field->set_label(100));
		$this->assertIdentical($this->_props['label'], $field->set_label(new StdClass()));
	}
	
	
	public function test_set_type__invalid_values()
	{
		$field = new EI_member_field($this->_props);
		
		$this->assertIdentical($this->_props['type'], $field->set_type(NULL));
		$this->assertIdentical($this->_props['type'], $field->set_type(FALSE));
		$this->assertIdentical($this->_props['type'], $field->set_type(100));
		$this->assertIdentical($this->_props['type'], $field->set_type('checkbox'));
		$this->assertIdentical($this->_props['type'], $field->set_type(new StdClass()));
	}
	
	
	public function test_populate_from_db_array__success()
	{
		// Dummy values.
		$db_array = array(
			'm_field_id'			=> 'm_field_id_10',
			'm_field_label'			=> 'Favourite Colour',
			'm_field_list_items'	=> "Red\nGreen\nBlue",
			'm_field_type'			=> 'select'
		);
		
		$field_options = array('Red', 'Green', 'Blue');
		
		$field = new EI_member_field();
		$field->populate_from_db_array($db_array);
		
		// Tests.
		$this->assertIdentical($db_array['m_field_id'], $field->get_id());
		$this->assertIdentical($db_array['m_field_label'], $field->get_label());
		$this->assertIdentical($field_options, $field->get_options());
		$this->assertIdentical($db_array['m_field_type'], $field->get_type());
	}
	
	
	public function test_to_array__success()
	{
		$field = new EI_member_field($this->_props);
		$field_array = $field->to_array();
		
		ksort($this->_props);
		ksort($field_array);
		
		$this->assertIdentical($this->_props, $field_array);
	}
	
	
	public function test_to_db_array__success()
	{
		// Dummy values.
		$db_array = array(
			'm_field_id'			=> 'm_field_id_10',
			'm_field_label'			=> 'Favourite Colour',
			'm_field_list_items'	=> "Red\nGreen\nBlue",
			'm_field_type'			=> 'select'
		);
		
		$field = new EI_member_field();
		$field->populate_from_db_array($db_array);
		
		// Tests.
		$field_array = $field->to_db_array();
		
		ksort($db_array);
		ksort($field_array);
		
		$this->assertIdentical($db_array, $field_array);
	}
	
	
	public function test_set_id__integer_conversion()
	{
		$this->_props['id'] = 10;
		$field = new EI_member_field($this->_props);
		
		$this->assertIdentical('m_field_id_' .$this->_props['id'], $field->get_id());
	}
	
}


/* End of file		: test.EI_member_field.php */
/* File location	: third_party/campaigner/tests/test.EI_member_field.php */
