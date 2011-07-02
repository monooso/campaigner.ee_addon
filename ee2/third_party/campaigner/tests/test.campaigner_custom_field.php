<?php

/**
 * Campaigner Custom Field tests.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field.php';

class Test_campaigner_custom_field extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Properies.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_props = array();
	
	
	
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
			'cm_key' 			=> '[DateOfBirth]',
			'label'				=> 'Date of Birth',
			'member_field_id' 	=> 'm_field_id_10'
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$field = new Campaigner_custom_field($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $field->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		$this->_props['INVALID'] = 'INVALID';
		
		// If this doesn't throw an error, we're golden.
		new Campaigner_custom_field($this->_props);
	}
	
	
	public function test_to_array__success()
	{
		$field = new Campaigner_custom_field($this->_props);
		$field_array = $field->to_array();
		
		ksort($this->_props);
		ksort($field_array);
		
		$this->assertIdentical($this->_props, $field_array);
	}
	
}


/* End of file		: test.campaigner_custom_field.php */
/* File location	: third_party/campaigner/tests/test.campaigner_custom_field.php */
