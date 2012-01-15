<?php

/**
 * Campaigner Client tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package		Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_client.php';

class Test_campaigner_client extends Testee_unit_test_case {
	
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
		$this->_props = array('client_id' => 'CLIENT_ID', 'client_name' => 'CLIENT_NAME');
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$client = new Campaigner_client($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $client->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		// Dummy data.
		$this->_props['INVALID'] = 'INVALID';
		
		// Tests. If this doesn't throw an error, we're good.
		new Campaigner_client($this->_props);
	}
	
	
	public function test_set_client_id__invalid_values()
	{
		$client = new Campaigner_client($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['client_id'], $client->set_client_id(FALSE));
		$this->assertIdentical($this->_props['client_id'], $client->set_client_id(NULL));
		$this->assertIdentical($this->_props['client_id'], $client->set_client_id(new stdClass()));
	}
	
	
	public function test_set_client_name__invalid_values()
	{
		$client = new Campaigner_client($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['client_name'], $client->set_client_name(FALSE));
		$this->assertIdentical($this->_props['client_name'], $client->set_client_name(NULL));
		$this->assertIdentical($this->_props['client_name'], $client->set_client_name(100));
		$this->assertIdentical($this->_props['client_name'], $client->set_client_name(new stdClass()));
	}
	
	
	public function test_to_array()
	{
		$client = new Campaigner_client($this->_props);
		$client_array = $client->to_array();
		
		ksort($this->_props);
		ksort($client_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $client_array);
	}
	
}


/* End of file		: test.campaigner_client.php */
/* File location	: third_party/campaigner/tests/test.campaigner_client.php */
