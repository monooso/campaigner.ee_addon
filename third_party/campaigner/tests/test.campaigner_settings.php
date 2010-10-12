<?php

/**
 * Tests for the Campaigner_settings class.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_settings' .EXT;

class Test_campaigner_settings extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The settings.
	 *
	 * @access	private
	 * @var		Campaigner_settings
	 */
	private $_settings;
	
	
	
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
		
		$this->_settings = new Campaigner_settings();
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor()
	{
		$api_key = 'API key';
		$client_id = 'Client ID';
		$mailing_lists = array(new Campaigner_mailing_list());
		
		$data = array(
			'api_key'		=> $api_key,
			'client_id'		=> $client_id,
			'mailing_lists'	=> $mailing_lists
		);
		
		$settings = new Campaigner_settings($data);
		
		$this->assertIdentical($api_key, $settings->get_api_key());
		$this->assertIdentical($client_id, $settings->get_client_id());
		$this->assertIdentical($mailing_lists, $settings->get_mailing_lists());
	}
	
	
	public function test_add_mailing_list__success()
	{
		$list = new Campaigner_mailing_list();
		$this->assertIdentical(array($list), $this->_settings->add_mailing_list($list));
	}
	
	
	public function test_add_mailing_list__failure()
	{
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_mailing_list/i'));
		$this->_settings->add_mailing_list('Invalid');
	}
	
	
	public function test_set_api_key()
	{
		$api_key = 'API key';
		$this->assertIdentical($api_key, $this->_settings->set_api_key($api_key));
	}
	
	
	public function test_set_client_id()
	{
		$client_id = 'Client ID';
		$this->assertIdentical($client_id, $this->_settings->set_client_id($client_id));
	}
	
	
	public function test_set_mailing_lists__success()
	{
		$list = new Campaigner_mailing_list();
		$lists = array($list, $list, $list);
		
		$this->assertIdentical($lists, $this->_settings->set_mailing_lists($lists));
	}
	
	
	public function test_set_mailing_lists__failure()
	{
		$list = new Campaigner_mailing_list();
		$lists = array($list, 'Invalid', $list);
		
		$this->expectError(new PatternExpectation('/must be an instance of Campaigner_mailing_list/i'));
		$this->_settings->set_mailing_lists($lists);
	}
	
	
	public function test_get_mailing_list_by_id()
	{
		$lists = array();
		
		for ($count = 1; $count < 10; $count++)
		{
			$lists[] = new Campaigner_mailing_list(array('list_id' => 'list_id_' .$count));
		}
		
		$this->_settings->set_mailing_lists($lists);
		
		$this->assertIsA($this->_settings->get_mailing_list_by_id('list_id_5'), 'Campaigner_mailing_list');
		$this->assertIdentical(FALSE, $this->_settings->get_mailing_list_by_id('list_id_100'));
	}
	
	
	public function test_to_array()
	{
		$api_key		= 'API key';
		$client_id		= 'Client ID';
		$mailing_list 	= new Campaigner_mailing_list();
		$mailing_lists	= array($mailing_list);
		
		$data = array(
			'api_key'		=> $api_key,
			'client_id'		=> $client_id,
			'mailing_lists'	=> array($mailing_list->to_array())
		);
		
		$this->_settings->set_api_key($api_key);
		$this->_settings->set_client_id($client_id);
		$this->_settings->set_mailing_lists($mailing_lists);
		
		$this->assertIdentical($data, $this->_settings->to_array());
	}
	
}


/* End of file		: test_campaigner_settings.php */
/* File location	: third_party/campaigner/tests/test_campaigner_settings.php */