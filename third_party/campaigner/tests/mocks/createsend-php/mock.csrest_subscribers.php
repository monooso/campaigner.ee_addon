<?php

/**
 * Mock CreateSend 'subscribers' class.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/tests/mocks/createsend-php/mock.base_classes.php';

if ( ! defined('CS_REST_CUSTOM_FIELD_TYPE_TEXT'))
{
	define('CS_REST_CUSTOM_FIELD_TYPE_TEXT', 'Text');
	define('CS_REST_CUSTOM_FIELD_TYPE_NUMBER', 'Number');
	define('CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTONE', 'MultiSelectOne');
	define('CS_REST_CUSTOM_FIELD_TYPE_MULTI_SELECTMANY', 'MultiSelectMany');
	define('CS_REST_CUSTOM_FIELD_TYPE_DATE', 'Date');
	define('CS_REST_CUSTOM_FIELD_TYPE_COUNTRY', 'Country');
	define('CS_REST_CUSTOM_FIELD_TYPE_USSTATE', 'USState');
	define('CS_REST_LIST_WEBHOOK_SUBSCRIBE', 'Subscribe');
	define('CS_REST_LIST_WEBHOOK_DEACTIVATE', 'Deactivate');
	define('CS_REST_LIST_WEBHOOK_UPDATE', 'Update');
}


class Mock_CS_REST_Subscribers extends Mock_CS_REST_Wrapper_Base {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function CS_REST_Subscribers($list_id, $api_key, $protocol = 'https', $debug_level = CS_REST_LOG_NONE, $host = 'api.createsend.com', $log = NULL, $serialiser = NULL, $transport = NULL) {}
	public function add($subscriber) {}
	public function get($email) {}
	public function get_history($email) {}
	public function import($subscribers, $resubscribe) {}
	public function unsubscribe($email) {}

}



/* End of file			: mock.csrest_subscribers.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.csrest_subscribers.php */
