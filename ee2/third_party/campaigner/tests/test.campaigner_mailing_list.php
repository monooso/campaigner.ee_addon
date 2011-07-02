<?php

/**
 * Campaigner Mailing List tests.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list.php';

class Test_campaigner_mailing_list extends Testee_unit_test_case {
	
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
			'active'		=> TRUE,
			'custom_fields'	=> array(new Campaigner_custom_field()),
			'list_id'		=> 'LIST_ID',
			'list_name'		=> 'Example Mailing List',
			'trigger_field'	=> 'm_field_id_10',
			'trigger_value'	=> 'y'
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$list = new Campaigner_mailing_list($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $list->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		$this->_props['INVALID'] = 'INVALID';
		
		// No error means it worked.
		new Campaigner_mailing_list($this->_props);
	}
	
	
	public function test_add_custom_field__failure()
	{
		$list = new Campaigner_mailing_list($this->_props);
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_custom_field/i'));
		$list->add_custom_field('Invalid');
	}
	
	
	public function test_set_custom_fields__failure()
	{
		$list = new Campaigner_mailing_list($this->_props);
		
		$custom_field = new Campaigner_custom_field();
		$custom_fields = array($custom_field, 'Invalid', $custom_field);
		
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_custom_field/i'));
		$list->set_custom_fields($custom_fields);
	}
	
	
	public function test_get_custom_field_by_cm_key__success()
	{
		$this->_props['custom_fields'] = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$this->_props['custom_fields'][] = new Campaigner_custom_field(array('cm_key' => 'cm_id_' .$count));
		}
		
		$list = new Campaigner_mailing_list($this->_props);
		$this->assertIsA($list->get_custom_field_by_cm_key('cm_id_5'), 'Campaigner_custom_field');
	}
	
	
	public function test_get_custom_field_by_cm_key__failure()
	{
		$this->_props['custom_fields'] = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$this->_props['custom_fields'][] = new Campaigner_custom_field(array('cm_key' => 'cm_id_' .$count));
		}
		
		$list = new Campaigner_mailing_list($this->_props);
		$this->assertIdentical(FALSE, $list->get_custom_field_by_cm_key('wibble'));
	}
	
	
	public function test_set_active__invalid_values()
	{
		$list = new Campaigner_mailing_list($this->_props);
		
		$this->assertIdentical($this->_props['active'], $list->set_active(0));
		$this->assertIdentical($this->_props['active'], $list->set_active('y'));
		$this->assertIdentical($this->_props['active'], $list->set_active(NULL));
		$this->assertIdentical($this->_props['active'], $list->set_active(new StdClass()));
	}
	
	
	public function test_to_array__success()
	{
		$list = new Campaigner_mailing_list($this->_props);
		$list_array = $list->to_array();
		
		$custom_fields = $this->_props['custom_fields'];
		$this->_props['custom_fields'] = array();
		
		foreach ($custom_fields AS $custom_field)
		{
			$this->_props['custom_fields'][] = $custom_field->to_array();
		}
		
		ksort($this->_props);
		ksort($list_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $list_array);
	}
	
}


/* End of file		: test.campaigner_mailing_list.php */
/* File location	: third_party/campaigner/tests/test.campaigner_mailing_list.php */
