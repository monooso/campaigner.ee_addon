<?php

/**
 * Campaigner Error Log Entry tests.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_error_log_entry' .EXT;

class Test_campaigner_error_log_entry extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Properies.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_props = array();
	
	
	
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
			'error_code'	=> 10,
			'error_date'	=> time() - 500,
			'error_log_id'	=> 100,
			'error_message'	=> 'Example error message'
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$log = new Campaigner_error_log_entry($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $log->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		$this->_props['INVALID'] = 'INVALID';
		
		// If this doesn't throw an error, we're golden.
		new Campaigner_error_log_entry($this->_props);
	}
	
	
	public function test_set_error_code__invalid_values()
	{
		$log = new Campaigner_error_log_entry($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['error_code'], $log->set_error_code(NULL));
		$this->assertIdentical($this->_props['error_code'], $log->set_error_code(FALSE));
		$this->assertIdentical($this->_props['error_code'], $log->set_error_code('String'));
		$this->assertIdentical($this->_props['error_code'], $log->set_error_code(-1));
		$this->assertIdentical($this->_props['error_code'], $log->set_error_code(1000));		// Too large.
		$this->assertIdentical($this->_props['error_code'], $log->set_error_code(new StdClass()));
	}
	
	
	public function test_set_error_date__invalid_values()
	{
		$log = new Campaigner_error_log_entry($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['error_date'], $log->set_error_date(NULL));
		$this->assertIdentical($this->_props['error_date'], $log->set_error_date(FALSE));
		$this->assertIdentical($this->_props['error_date'], $log->set_error_date('String'));
		$this->assertIdentical($this->_props['error_date'], $log->set_error_date(-1));
		$this->assertIdentical($this->_props['error_date'], $log->set_error_date(new StdClass()));
	}
	
	
	public function test_set_error_log_id__invalid_values()
	{
		$log = new Campaigner_error_log_entry($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['error_log_id'], $log->set_error_log_id(NULL));
		$this->assertIdentical($this->_props['error_log_id'], $log->set_error_log_id(FALSE));
		$this->assertIdentical($this->_props['error_log_id'], $log->set_error_log_id('String'));
		$this->assertIdentical($this->_props['error_log_id'], $log->set_error_log_id(-1));
		$this->assertIdentical($this->_props['error_log_id'], $log->set_error_log_id(new StdClass()));
	}
	
	
	public function test_set_error_message__invalid_values()
	{
		$log = new Campaigner_error_log_entry($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['error_message'], $log->set_error_message(NULL));
		$this->assertIdentical($this->_props['error_message'], $log->set_error_message(FALSE));
		$this->assertIdentical($this->_props['error_message'], $log->set_error_message(10));
		$this->assertIdentical($this->_props['error_message'], $log->set_error_message(new StdClass()));
	}
	
	
	public function test_to_array__success()
	{
		$log = new Campaigner_error_log_entry($this->_props);
		$log_array = $log->to_array();
		
		ksort($this->_props);
		ksort($log_array);
		
		$this->assertIdentical($this->_props, $log_array);
	}
	
}

/* End of file		: test.campaigner_error_log_entry.php */
/* File location	: third_party/campaigner/tests/test.campaigner_error_log_entry.php */