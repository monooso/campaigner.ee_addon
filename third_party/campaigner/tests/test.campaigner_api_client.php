<?php

/**
 * Campaigner API Client tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package		Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_client' .EXT;

class Test_campaigner_api_client extends Testee_unit_test_case {
	
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
		$this->_props = array('id' => 'CLIENT_ID', 'name' => 'CLIENT_NAME');
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$client = new Campaigner_api_client($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['id'], $client->get_id());
		$this->assertIdentical($this->_props['name'], $client->get_name());
	}
	
	
	public function test_constructor__invalid_property()
	{
		// Dummy data.
		$this->_props['INVALID'] = 'INVALID';
		
		// Tests. If this doesn't throw an error, we're good.
		new Campaigner_api_client($this->_props);
	}
	
	
	public function test_set_id__invalid_values()
	{
		$client = new Campaigner_api_client($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['id'], $client->set_id(FALSE));
		$this->assertIdentical($this->_props['id'], $client->set_id(NULL));
		$this->assertIdentical($this->_props['id'], $client->set_id(new stdClass()));
	}
	
	
	public function test_set_name__invalid_values()
	{
		$client = new Campaigner_api_client($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['name'], $client->set_name(FALSE));
		$this->assertIdentical($this->_props['name'], $client->set_name(NULL));
		$this->assertIdentical($this->_props['name'], $client->set_name(100));
		$this->assertIdentical($this->_props['name'], $client->set_name(new stdClass()));
	}
	
	
	public function test_to_array()
	{
		$client = new Campaigner_api_client($this->_props);
		$client_array = $client->to_array();
		
		ksort($this->_props);
		ksort($client_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $client_array);
	}
	
}


/* End of file		: test.campaigner_api_client.php */
/* File location	: third_party/campaigner/tests/test.campaigner_api_client.php */