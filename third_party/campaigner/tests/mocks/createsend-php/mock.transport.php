<?php

/**
 * Mock CreateSend Transport classes.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

if ( ! defined('CS_REST_GET'))
{
	define('CS_REST_GET', 'GET');
	define('CS_REST_POST', 'POST');
	define('CS_REST_PUT', 'PUT');
	define('CS_REST_DELETE', 'DELETE');
	define('CS_REST_SOCKET_TIMEOUT', 1);
}


class Mock_CS_REST_TransportFactory {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_available_transport($requires_ssl, $log) {}
}



class Mock_CS_REST_CurlTransport {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_type() {}
	public function is_available($requires_ssl = FALSE) {}
	public function make_call(Array $call_options) {}
}



class Mock_CS_REST_SocketWrapper {
	
	/* --------------------------------------------------------------
	 * PUBLIC PROPERTIES
	 *	$socket
	 * ------------------------------------------------------------ */
	
	// Magic methods to access properties.
	public function __get($prop_name) {}
	public function __set($prop_name, $prop_value) {}
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function open($domain, $port) {}
	public function write($data) {}
	public function read() {}
	public function close() {}
}



class Mock_CS_REST_SocketTransport {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_type() {}
	public function is_available($requires_ssl = FALSE) {}
	public function make_call(Array $call_options) {}
	public function _get_status_code($headers) {}
}



/* End of file			: mock.transport.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.transport.php */
