<?php

/**
 * Tests for the Campaigner_settings class.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_settings.php';

class Test_campaigner_settings extends Testee_unit_test_case {
	
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
			'api_key'		=> 'API_KEY',
			'client_id'		=> 'CLIENT_ID',
			'mailing_lists'	=> array(new Campaigner_mailing_list())
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor()
	{
		$settings = new Campaigner_settings($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $settings->$method());
		}
	}
	
	
	public function test_add_mailing_list__failure()
	{
		$settings = new Campaigner_settings($this->_props);
		
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_mailing_list/i'));
		$settings->add_mailing_list('Invalid');
	}
	
	
	public function test_set_mailing_lists__failure()
	{
		$settings	= new Campaigner_settings($this->_props);
		$list 		= new Campaigner_mailing_list();
		$lists 		= array($list, 'Invalid', $list);
		
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_mailing_list/i'));
		$settings->set_mailing_lists($lists);
	}
	
	
	public function test_get_mailing_list_by_id()
	{
		$settings 	= new Campaigner_settings($this->_props);
		$lists 		= array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$settings->add_mailing_list(new Campaigner_mailing_list(array('list_id' => 'list_id_' .$count)));
		}
		
		$this->assertIsA($settings->get_mailing_list_by_id('list_id_5'), 'Campaigner_mailing_list');
		$this->assertIdentical(FALSE, $settings->get_mailing_list_by_id('list_id_100'));
	}
	
	
	public function test_to_array()
	{
		$settings = new Campaigner_settings($this->_props);
		$settings_array = $settings->to_array();
		
		$mailing_lists = $this->_props['mailing_lists'];
		$this->_props['mailing_lists'] = array();
		
		foreach ($mailing_lists AS $mailing_list)
		{
			$this->_props['mailing_lists'][] = $mailing_list->to_array();
		}
		
		ksort($this->_props);
		ksort($settings_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $settings_array);
	}
	
}


/* End of file		: test.campaigner_settings.php */
/* File location	: third_party/campaigner/tests/test.campaigner_settings.php */
