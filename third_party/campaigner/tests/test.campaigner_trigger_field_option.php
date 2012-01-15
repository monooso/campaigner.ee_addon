<?php

/**
 * Trigger field option tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package 	Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_trigger_field_option.php';

class Test_campaigner_trigger_field_option extends Testee_unit_test_case {
	
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
			'id'		=> 'USD',
			'label'		=> 'U.S. Dollar',
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$field = new Campaigner_trigger_field_option($this->_props);
		
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
		new Campaigner_trigger_field_option($this->_props);
	}
	
	
	public function test_set_id__invalid_values()
	{
		$field = new Campaigner_trigger_field_option($this->_props);
		
		$this->assertIdentical($this->_props['id'], $field->set_id(NULL));
		$this->assertIdentical($this->_props['id'], $field->set_id(FALSE));
		$this->assertIdentical($this->_props['id'], $field->set_id(new StdClass()));
	}
	
	
	public function test_set_label__invalid_values()
	{
		$field = new Campaigner_trigger_field_option($this->_props);
		
		$this->assertIdentical($this->_props['label'], $field->set_label(NULL));
		$this->assertIdentical($this->_props['label'], $field->set_label(FALSE));
		$this->assertIdentical($this->_props['label'], $field->set_label(100));
		$this->assertIdentical($this->_props['label'], $field->set_label(new StdClass()));
	}
	
	
	public function test_to_array__success()
	{
		$field = new Campaigner_trigger_field_option($this->_props);
		$field_array = $field->to_array();
		
		ksort($this->_props);
		ksort($field_array);
		
		$this->assertIdentical($this->_props, $field_array);
	}
	
	
}


/* End of file		: test.campaigner_trigger_field_option.php */
/* File location	: third_party/campaigner/tests/test.campaigner_trigger_field_option.php */
