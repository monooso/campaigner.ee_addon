<?php

/**
 * Campaigner Campaign Monitor API Connector tests.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_cm_api_connector' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/createsend-php/mock.csrest_general' .EXT;

class Test_campaigner_api_connector extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Dummy API key.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_api_key;

	/**
	 * Dummy CM API connector classes.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_cm_api_clients;
	private $_cm_api_general;
	private $_cm_api_lists;
	
	/**
	 * The test subject.
	 *
	 * @access	private
	 * @var		Campaigner_api_connector
	 */
	private $_subject;
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Utility method, to convert an array to a StdClass object.
	 *
	 * @access	private
	 * @param	array		$subject		The array to convert.
	 * @return	StdClass
	 */
	private function _convert_array_to_object($subject)
	{
		if ( ! is_array($subject))
		{
			return $subject;
		}
		
		return (object) array_map(array($this, '_convert_array_to_object'), $subject);
	}
	
	
	
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

		// Mock API connector classes.
		Mock::generate('Mock_CS_REST_Wrapper_Result', get_class($this) .'_mock_cm_api_result');

		Mock::generate('Mock_CS_REST_General', get_class($this) .'_mock_cm_api_general');
		$this->_cm_api_general = $this->_get_mock('cm_api_general');
		
		// Initialise some test properties.
		$this->_api_key	= '04f82350a845ey7y87y87y82091015a00';

		// Create the test subject.
		$this->_subject = new Campaigner_cm_api_connector($this->_api_key);

		// Set the mock API connector classes.
		$this->_subject->set_mock_api_connectors(array(
			'cm_api_general' => $this->_cm_api_general
		));
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test__get_clients__success()
	{
		// Dummy values.
		$http_status_code	= '200';
		$response			= array(
			$this->_convert_array_to_object(array('ClientID' => '4a397ccaaa55eb4e6aa1221e1e2d7122', 'Name' => 'Client A')),
			$this->_convert_array_to_object(array('ClientID' => 'a206def0582eec7dae47d937a4109cb2', 'Name' => 'Client B'))
		);

		$result = $this->_get_mock('cm_api_result');

		$return = array(
			new Campaigner_client(array('client_id' => $response[0]->ClientID, 'client_name' => $response[0]->Name)),
			new Campaigner_client(array('client_id' => $response[1]->ClientID, 'client_name' => $response[1]->Name))
		);

		// Expectations.
		$this->_cm_api_general->expectOnce('get_clients');

		$result->expectOnce('was_successful');
		
		// Return values.
		$this->_cm_api_general->setReturnReference('get_clients', $result);

		$result->setReturnValue('__get', $http_status_code, array('http_status_code'));
		$result->setReturnValue('__get', $response, array('response'));
		$result->setReturnValue('was_successful', TRUE);
		
		// Tests.
		$this->assertIdentical($return, $this->_subject->get_clients());
	}


	public function test__get_clients__api_error()
	{
		// Dummy values.
		$http_status_code	= '401';
		$response			= $this->_convert_array_to_object(array('Code' => '100', 'Message' => 'Invalid API Key'));
		$result				= $this->_get_mock('cm_api_result');

		// Return values.
		$this->_cm_api_general->setReturnReference('get_clients', $result);
		$result->setReturnValue('__get', $http_status_code, array('http_status_code'));
		$result->setReturnValue('__get', $response, array('response'));
		$result->setReturnValue('was_successful', FALSE);

		// Tests.
		$this->expectException(new Campaigner_api_exception($response->Message, $response->Code));
		$this->_subject->get_clients();
	}
}


/* End of file		: test_campaigner_api_connector.php */
/* File location	: third_party/campaigner/tests/test_campaigner_api_connector.php */
