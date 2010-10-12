<?php

/**
 * Tests for the Campaigner_mailing_list class.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list' .EXT;

class Test_campaigner_mailing_list extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The mailing list.
	 *
	 * @access	private
	 * @var		Campaigner_mailing_list
	 */
	private $_list;
	
	
	
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
		
		$this->_list = new Campaigner_mailing_list();
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor()
	{
		$list_id 			= 'List ID';
		$trigger_field_id 	= 'Trigger field ID';
		$trigger_value		= 'Trigger value';
		$merge_variables	= array(new Campaigner_merge_variable());
		
		$data = array(
			'list_id'			=> $list_id,
			'merge_variables'	=> $merge_variables,
			'trigger_field_id'	=> $trigger_field_id,
			'trigger_value'		=> $trigger_value
		);
		
		$list = new Campaigner_mailing_list($data);
		
		$this->assertIdentical($list_id, $list->get_list_id());
		$this->assertIdentical($merge_variables, $list->get_merge_variables());
		$this->assertIdentical($trigger_field_id, $list->get_trigger_field_id());
		$this->assertIdentical($trigger_value, $list->get_trigger_value());
	}
	
	
	public function test_set_list_id()
	{
		$list_id = 'List ID';
		$this->assertIdentical($list_id, $this->_list->set_list_id($list_id));
	}
	
	
	public function test_set_trigger_field_id()
	{
		$field_id = 'm_field_id_20';
		$this->assertIdentical($field_id, $this->_list->set_trigger_field_id($field_id));
	}
	
	
	public function test_set_trigger_value()
	{
		$trigger_value = 'Spamalot';
		$this->assertIdentical($trigger_value, $this->_list->set_trigger_value($trigger_value));
	}
	
	
	public function test_add_merge_variable__success()
	{
		$merge_var = new Campaigner_merge_variable();
		$this->assertIdentical(array($merge_var), $this->_list->add_merge_variable($merge_var));
	}
	
	
	public function test_add_merge_variable__failure()
	{
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_merge_variable/i'));
		$this->_list->add_merge_variable('Invalid');
	}
	
	
	public function test_set_merge_variables__success()
	{
		$merge_var = new Campaigner_merge_variable();
		$merge_vars = array($merge_var, $merge_var, $merge_var);
		
		$this->assertIdentical($merge_vars, $this->_list->set_merge_variables($merge_vars));
	}
	
	
	public function test_set_merge_variables__failure()
	{
		$merge_var = new Campaigner_merge_variable();
		$merge_vars = array($merge_var, 'Invalid', $merge_var);
		
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_merge_variable/i'));
		$this->_list->set_merge_variables($merge_vars);
	}
	
	
	public function test_get_merge_variable_by_id__success()
	{
		$merge_vars = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$merge_vars[] = new Campaigner_merge_variable(array('id' => 'merge_var_id_' .$count));
		}
		
		$this->_list->set_merge_variables($merge_vars);
		$this->assertIsA($this->_list->get_merge_variable_by_id('merge_var_id_5'), 'Campaigner_merge_variable');
	}
	
	
	public function test_get_merge_variable_by_id__failure()
	{
		$merge_vars = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$merge_vars[] = new Campaigner_merge_variable(array('id' => 'merge_var_id_' .$count));
		}
		
		$this->_list->set_merge_variables($merge_vars);
		$this->assertIdentical(FALSE, $this->_list->get_merge_variable_by_id('merge_var_id_100'));
	}
	
	
	public function test_to_array()
	{
		$list_id 			= 'List ID';
		$trigger_field_id 	= 'Trigger field ID';
		$trigger_value		= 'Trigger value';
		$merge_variable 	= new Campaigner_merge_variable();
		$merge_variables	= array($merge_variable);
		
		$data = array(
			'list_id'			=> $list_id,
			'merge_variables'	=> array($merge_variable->to_array()),
			'trigger_field_id'	=> $trigger_field_id,
			'trigger_value'		=> $trigger_value
		);
		
		$this->_list->set_list_id($list_id);
		$this->_list->set_trigger_field_id($trigger_field_id);
		$this->_list->set_trigger_value($trigger_value);
		$this->_list->set_merge_variables($merge_variables);
		
		$this->assertIdentical($data, $this->_list->to_array());
	}
	
}


/* End of file		: test_campaigner_mailing_list.php */
/* File location	: third_party/campaigner/tests/test_campaigner_mailing_list.php */