<?php

/**
 * Mock CreateSend base classes.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

if ( ! defined('CS_REST_WRAPPER_VERSION'))
{
	define('CS_REST_WRAPPER_VERSION', '1.0.4');
	define('CS_REST_WEBHOOK_FORMAT_JSON', 'json');
	define('CS_REST_WEBHOOK_FORMAT_XML', 'xml');
}


class Mock_CS_REST_Wrapper_Result {
	
	/* --------------------------------------------------------------
	 * PUBLIC PROPERTIES
	 * 	$http_status_code
	 *	$response
	 * ------------------------------------------------------------ */
	
	// Magic methods to access properties.
	public function __get($prop_name) {}
	public function __set($prop_name, $prop_value) {}
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function was_successful() {}
}



class Mock_CS_REST_Wrapper_Base {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function is_secure() {}
	public function put_request($route, $data, $call_options = array()) {}
	public function post_request($route, $data, $call_options = array()) {}
	public function delete_request($route, $call_options = array()) {}
	public function get_request($route, $call_options = array()) {}
	public function get_request_paged($route, $page_number, $page_size, $order_field, $order_direction, $join_char = '&') {}
}



/* End of file			: mock.base_classes.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.base_classes.php */