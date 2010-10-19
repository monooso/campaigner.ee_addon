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
		$custom_fields		= array(new Campaigner_custom_field());
		
		$data = array(
			'custom_fields'	=> $custom_fields,
			'list_id'			=> $list_id,
			'trigger_field_id'	=> $trigger_field_id,
			'trigger_value'		=> $trigger_value
		);
		
		$list = new Campaigner_mailing_list($data);
		
		$this->assertIdentical($list_id, $list->get_list_id());
		$this->assertIdentical($custom_fields, $list->get_custom_fields());
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
	
	
	public function test_add_custom_field__success()
	{
		$custom_field = new Campaigner_custom_field();
		$this->assertIdentical(array($custom_field), $this->_list->add_custom_field($custom_field));
	}
	
	
	public function test_add_custom_field__failure()
	{
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_custom_field/i'));
		$this->_list->add_custom_field('Invalid');
	}
	
	
	public function test_set_custom_fields__success()
	{
		$custom_field = new Campaigner_custom_field();
		$custom_fields = array($custom_field, $custom_field, $custom_field);
		
		$this->assertIdentical($custom_fields, $this->_list->set_custom_fields($custom_fields));
	}
	
	
	public function test_set_custom_fields__failure()
	{
		$custom_field = new Campaigner_custom_field();
		$custom_fields = array($custom_field, 'Invalid', $custom_field);
		
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_custom_field/i'));
		$this->_list->set_custom_fields($custom_fields);
	}
	
	
	public function test_get_custom_field_by_id__success()
	{
		$custom_fields = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$custom_fields[] = new Campaigner_custom_field(array('id' => 'cm_id_' .$count));
		}
		
		$this->_list->set_custom_fields($custom_fields);
		$this->assertIsA($this->_list->get_custom_field_by_id('cm_id_5'), 'Campaigner_custom_field');
	}
	
	
	public function test_get_custom_field_by_id__failure()
	{
		$custom_fields = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$custom_fields[] = new Campaigner_custom_field(array('id' => 'cm_id_' .$count));
		}
		
		$this->_list->set_custom_fields($custom_fields);
		$this->assertIdentical(FALSE, $this->_list->get_custom_field_by_id('wibble'));
	}
	
	
	public function test_to_array()
	{
		$list_id 			= 'List ID';
		$trigger_field_id 	= 'Trigger field ID';
		$trigger_value		= 'Trigger value';
		$custom_field 		= new Campaigner_custom_field();
		$custom_fields		= array($custom_field);
		
		$data = array(
			'custom_fields'		=> array($custom_field->to_array()),
			'list_id'			=> $list_id,
			'trigger_field_id'	=> $trigger_field_id,
			'trigger_value'		=> $trigger_value
		);
		
		$this->_list->set_custom_fields($custom_fields);
		$this->_list->set_list_id($list_id);
		$this->_list->set_trigger_field_id($trigger_field_id);
		$this->_list->set_trigger_value($trigger_value);
		
		$this->assertIdentical($data, $this->_list->to_array());
	}
	
}


/* End of file		: test_campaigner_mailing_list.php */
/* File location	: third_party/campaigner/tests/test_campaigner_mailing_list.php */