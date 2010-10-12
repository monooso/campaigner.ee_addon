<?php

/**
 * Tests for the Campaigner_merge_variable class.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_merge_variable' .EXT;

class Test_campaigner_merge_variable extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The merge variable.
	 *
	 * @access	private
	 * @var		Campaigner_merge_variable
	 */
	private $_var;
	
	
	
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
		
		$this->_var = new Campaigner_merge_variable();
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor()
	{
		$id = 'Merge variable ID';
		$field_id = 'Member field ID';
		
		$data = array(
			'field_id'	=> $field_id,
			'id'		=> $id
		);
		
		$var = new Campaigner_merge_variable($data);
		
		$this->assertIdentical($field_id, $var->get_field_id());
		$this->assertIdentical($id, $var->get_id());
	}
	
	
	public function test_set_id()
	{
		$id = 'Merge variable ID';
		$this->assertIdentical($id, $this->_var->set_id($id));
	}
	
	
	public function test_set_field_id()
	{
		$field_id = 'Member field ID';
		$this->assertIdentical($field_id, $this->_var->set_field_id($field_id));
	}
	
	
	public function test_to_array()
	{
		$id = 'Merge variable ID';
		$field_id = 'Member field ID';
		
		$data = array(
			'field_id'	=> $field_id,
			'id'		=> $id
		);
		
		$this->_var->set_id($id);
		$this->_var->set_field_id($field_id);
		
		$this->assertIdentical($data, $this->_var->to_array());
	}
	
}


/* End of file		: test_campaigner_merge_variable.php */
/* File location	: third_party/campaigner/tests/test_campaigner_merge_variable.php */