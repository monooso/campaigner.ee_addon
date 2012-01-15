<?php

/**
 * Campaigner Subscriber tests.
 *
 * @author 			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package 		: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber.php';

class Test_campaigner_subscriber extends Testee_unit_test_case {
	
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
			'custom_data'	=> array(new Campaigner_subscriber_custom_data(array('id' => 'location', 'value' => 'Caerphilly'))),
			'email'			=> 'john@doe.com',
			'name'			=> 'John Doe'
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$subject = new Campaigner_subscriber($this->_props);

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
		new Campaigner_subscriber($this->_props);
	}
	
	
	public function test_to_array__success()
	{
		$subject = new Campaigner_subscriber($this->_props);
		$subject_array = $subject->to_array();
		
		$custom_data = $this->_props['custom_data'];
		$this->_props['custom_data'] = array();
		
		foreach ($custom_data AS $field)
		{
			$this->_props['custom_data'][] = $field->to_array();
		}
		
		ksort($this->_props);
		ksort($subject_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $subject_array);
	}
	
}


/* End of file		: test.campaigner_subscriber.php */
/* File location	: third_party/campaigner/tests/test.campaigner_subscriber.php */
