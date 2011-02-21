<?php

/**
 * Campaigner Campaign Monitor API Connector tests.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_cm_api_connector' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/createsend-php/mock.csrest_clients' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/createsend-php/mock.csrest_general' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/createsend-php/mock.csrest_lists' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/mock.campaigner_model' .EXT;

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
	 * Model.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_model;
	
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

		Mock::generate('Mock_CS_REST_Clients', get_class($this) .'_mock_cm_api_clients');
		$this->_cm_api_clients = $this->_get_mock('cm_api_clients');

		Mock::generate('Mock_CS_REST_General', get_class($this) .'_mock_cm_api_general');
		$this->_cm_api_general = $this->_get_mock('cm_api_general');

		Mock::generate('Mock_CS_REST_Lists', get_class($this) .'_mock_cm_api_lists');
		$this->_cm_api_lists = $this->_get_mock('cm_api_lists');

		Mock::generate('Mock_campaigner_model', get_class($this) .'_mock_campaigner_model');
		$this->_model = $this->_get_mock('campaigner_model');

		$this->_model->setReturnReference('get_api_class_clients', $this->_cm_api_clients);
		$this->_model->setReturnReference('get_api_class_general', $this->_cm_api_general);
		$this->_model->setReturnReference('get_api_class_lists', $this->_cm_api_lists);
		
		// Initialise some test properties.
		$this->_api_key	= '04f82350a845ey7y87y87y82091015a00';

		// Create the test subject.
		$this->_subject = new Campaigner_cm_api_connector($this->_api_key, $this->_model);
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
		$this->_model->expectOnce('get_api_class_general');
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


	public function test__get_client_lists__do_not_include_fields_success()
	{
		// Dummy values.
		$client_id			= 'a58ee1d3039b8bec838e6d1482a8a966';
		$http_status_code	= '200';
		$response			= array(
			$this->_convert_array_to_object(array('ListID' => 'a58ee1d3039b8bec838e6d1482a8a965', 'Name' => 'List A')),
			$this->_convert_array_to_object(array('ListID' => '99bc35084a5739127a8ab81eae5bd305', 'Name' => 'List B'))
		);

		$result = $this->_get_mock('cm_api_result');

		$return = array(
			new Campaigner_mailing_list(array('list_id' => $response[0]->ListID, 'list_name' => $response[0]->Name)),
			new Campaigner_mailing_list(array('list_id' => $response[1]->ListID, 'list_name' => $response[1]->Name))
		);

		// Expectations.
		$this->_model->expectOnce('get_api_class_clients', array($client_id));
		$this->_cm_api_clients->expectOnce('get_lists');
		$result->expectOnce('was_successful');
		
		// Return values.
		$this->_cm_api_clients->setReturnReference('get_lists', $result);

		$result->setReturnValue('__get', $http_status_code, array('http_status_code'));
		$result->setReturnValue('__get', $response, array('response'));
		$result->setReturnValue('was_successful', TRUE);
		
		// Tests.
		$this->assertIdentical($return, $this->_subject->get_client_lists($client_id, FALSE));
	}


	public function test__get_client_lists__api_error()
	{
		// Dummy values.
		$client_id			= 'a58ee1d3039b8bec838e6d1482a8a966';
		$http_status_code	= '401';
		$response			= $this->_convert_array_to_object(array('Code' => '100', 'Message' => 'Invalid API Key'));
		$result				= $this->_get_mock('cm_api_result');

		// Return values.
		$this->_cm_api_clients->setReturnReference('get_lists', $result);
		$result->setReturnValue('__get', $http_status_code, array('http_status_code'));
		$result->setReturnValue('__get', $response, array('response'));
		$result->setReturnValue('was_successful', FALSE);

		// Tests.
		$this->expectException(new Campaigner_api_exception($response->Message, $response->Code));
		$this->_subject->get_client_lists($client_id);
	}


	public function test__get_list_fields__success()
	{
		$list_id = 'a58ee1d3039b8bec838e6d1482a8a966';
		$http_status_code = '200';

		$response = array(
			$this->_convert_array_to_object(array('FieldName' => 'website', 'Key' => '[website]', 'DataType' => 'Text', 'FieldOptions' => array())),
			$this->_convert_array_to_object(array('FieldName' => 'age', 'Key' => '[age]', 'DataType' => 'Number', 'FieldOptions' => array())),
			$this->_convert_array_to_object(array('FieldName' => 'subscription_date', 'Key' => '[subscriptiondate]', 'DataType' => 'Date', 'FieldOptions' => array())),
			$this->_convert_array_to_object(array('FieldName' => 'newsletterformat', 'Key' => '[newsletterformat]', 'DataType' => 'MultiSelectOne', 'FieldOptions' => array('HTML', 'Text'))),
		);

		$result = $this->_get_mock('cm_api_result');

		$this->_model->expectOnce('get_api_class_lists', array($list_id));
		$this->_cm_api_lists->expectOnce('get_custom_fields');
		$this->_cm_api_lists->setReturnValue('get_custom_fields', $result);

		$result->expectOnce('was_successful');

		$result->setReturnValue('__get', $http_status_code, array('http_status_code'));
		$result->setReturnValue('__get', $response, array('response'));
		$result->setReturnValue('was_successful', TRUE);

		// Tests.
		$return = array(
			new Campaigner_custom_field(array('cm_key' => $response[0]->Key, 'label' => $response[0]->FieldName)),
			new Campaigner_custom_field(array('cm_key' => $response[1]->Key, 'label' => $response[1]->FieldName)),
			new Campaigner_custom_field(array('cm_key' => $response[2]->Key, 'label' => $response[2]->FieldName)),
			new Campaigner_custom_field(array('cm_key' => $response[3]->Key, 'label' => $response[3]->FieldName))
		);

		$this->assertIdentical($return, $this->_subject->get_list_fields($list_id, TRUE));
	}

}


/* End of file		: test_campaigner_api_connector.php */
/* File location	: third_party/campaigner/tests/test_campaigner_api_connector.php */
