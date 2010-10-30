<?php

/**
 * Campaigner API Error tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package		Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_error' .EXT;

class Test_campaigner_api_error extends Testee_unit_test_case {
	
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
		$this->_props = array('code' => 100, 'message' => 'Invalid API key');
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$error = new Campaigner_api_error($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $error->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		// Dummy data.
		$this->_props['INVALID'] = 'INVALID';
		
		// Tests. If this doesn't throw an error, we're good.
		new Campaigner_api_error($this->_props);
	}
	
	
	public function test_set_code__invalid_values()
	{
		$error = new Campaigner_api_error($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['code'], $error->set_code(FALSE));
		$this->assertIdentical($this->_props['code'], $error->set_code(NULL));
		$this->assertIdentical($this->_props['code'], $error->set_code('string'));
		$this->assertIdentical($this->_props['code'], $error->set_code(new stdClass()));
	}
	
	
	public function test_set_message__invalid_values()
	{
		$error = new Campaigner_api_error($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['message'], $error->set_message(FALSE));
		$this->assertIdentical($this->_props['message'], $error->set_message(NULL));
		$this->assertIdentical($this->_props['message'], $error->set_message(100));
		$this->assertIdentical($this->_props['message'], $error->set_message(new stdClass()));
	}
	
	
	public function test_to_array()
	{
		$error = new Campaigner_api_error($this->_props);
		$error_array = $error->to_array();
		
		ksort($this->_props);
		ksort($error_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $error_array);
	}
	
}


/* End of file		: test.campaigner_api_error.php */
/* File location	: third_party/campaigner/tests/test.campaigner_api_error.php */