<?php

/**
 * Tests for the Campaigner_custom_field class.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field' .EXT;

class Test_campaigner_custom_field extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The custom field.
	 *
	 * @access	private
	 * @var		Campaigner_custom_field
	 */
	private $_field;
	
	
	
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
		$this->_field = new Campaigner_custom_field();
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor()
	{
		$id 		= 'cm_id';
		$field_id 	= 'm_field_id';
		
		$data = array(
			'field_id'	=> $field_id,
			'id'		=> $id
		);
		
		$var = new Campaigner_custom_field($data);
		
		$this->assertIdentical($field_id, $var->get_field_id());
		$this->assertIdentical($id, $var->get_id());
	}
	
	
	public function test_set_id()
	{
		$id = 'cm_id';
		$this->assertIdentical($id, $this->_field->set_id($id));
	}
	
	
	public function test_set_field_id()
	{
		$field_id = 'm_field_id';
		$this->assertIdentical($field_id, $this->_field->set_field_id($field_id));
	}
	
	
	public function test_to_array()
	{
		$id 		= 'cm_id';
		$field_id 	= 'm_field_id';
		
		$data = array(
			'field_id'	=> $field_id,
			'id'		=> $id
		);
		
		$this->_field->set_id($id);
		$this->_field->set_field_id($field_id);
		
		$this->assertIdentical($data, $this->_field->to_array());
	}
	
}


/* End of file		: test_Campaigner_custom_field.php */
/* File location	: third_party/campaigner/tests/test_Campaigner_custom_field.php */