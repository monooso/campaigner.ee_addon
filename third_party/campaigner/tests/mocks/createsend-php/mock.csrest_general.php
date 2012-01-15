<?php

/**
 * Mock CreateSend 'general' class.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/tests/mocks/createsend-php/mock.base_classes.php';

class Mock_CS_REST_General extends Mock_CS_REST_Wrapper_Base {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_timezones() {}
	public function get_systemdate() {}
	public function get_countries() {}
	public function get_apikey($username, $password, $site_url) {}
	public function get_clients() {}
}



/* End of file			: mock.csrest_general.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.csrest_general.php */
