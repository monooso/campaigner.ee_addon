<?php

/**
 * Campaigner Subscriber Custom Data tests.
 *
 * @author 			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package 		: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber_custom_data.php';

class Test_campaigner_subscriber_custom_data extends Testee_unit_test_case {
	
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
			'key'	=> '[location]',
			'value'	=> 'Caerphilly'
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$subject = new Campaigner_subscriber_custom_data($this->_props);

		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $subject->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		$this->_props['INVALID'] = 'INVALID';
		
		// No error means it worked.
		new Campaigner_subscriber_custom_data($this->_props);
	}
	
	
	public function test_to_array__success()
	{
		$subject = new Campaigner_subscriber_custom_data($this->_props);
		$subject_array = $subject->to_array();
		
		ksort($this->_props);
		ksort($subject_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $subject_array);
	}
	
}


/* End of file		: test.campaigner_subscriber_custom_data.php */
/* File location	: third_party/campaigner/tests/test.campaigner_subscriber_custom_data.php */
