<?php

/**
 * Mock CreateSend JSON Services classes.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

if ( ! defined('SERVICES_JSON_SLICE'))
{
	define('SERVICES_JSON_SLICE',   1);
	define('SERVICES_JSON_IN_STR',  2);
	define('SERVICES_JSON_IN_ARR',  3);
	define('SERVICES_JSON_IN_OBJ',  4);
	define('SERVICES_JSON_IN_CMT', 5);
	define('SERVICES_JSON_LOOSE_TYPE', 16);
	define('SERVICES_JSON_SUPPRESS_ERRORS', 32);
}


class Mock_Services_JSON {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function utf162utf8($utf16) {}
	public function utf82utf16($utf8) {}
	public function encode($var) {}
	public function name_value($name, $value) {}
	public function reduce_string($str) {}
	public function decode($str) {}
	public function isError($data, $code = NULL) {}
}



class Mock_Services_JSON_Error {}



/* End of file			: mock.services_json.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.services_json.php */
