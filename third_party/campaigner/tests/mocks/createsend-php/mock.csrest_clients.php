<?php

/**
 * Mock CreateSend 'clients' class.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once dirname(__FILE__).'/mock.base_classes.php';

if ( ! defined('CS_REST_CLIENT_ACCESS_NONE'))
{
	define('CS_REST_CLIENT_ACCESS_NONE', 0x0);
	define('CS_REST_CLIENT_ACCESS_REPORTS', 0x1);
	define('CS_REST_CLIENT_ACCESS_SUBSCRIBERS', 0x2);
	define('CS_REST_CLIENT_ACCESS_CREATESEND', 0x4);
	define('CS_REST_CLIENT_ACCESS_DESIGNSPAMTEST', 0x8);
	define('CS_REST_CLIENT_ACCESS_IMPORTSUBSCRIBERS', 0x10);
	define('CS_REST_CLIENT_ACCESS_IMPORTURL', 0x20);
}


class Mock_CS_REST_Clients extends Mock_CS_REST_Wrapper_Base {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function set_client_id($client_id) {}
	public function get_campaigns() {}
	public function get_drafts() {}
	public function get_lists() {}
	public function get_segments() {}
	public function get_suppressionlist($page_number = NULL, $page_size = NULL, $order_field = NULL, $order_direction = NULL) {}
	public function get_templates() {}
	public function get() {}
	public function delete() {}
	public function create($client) {}
	public function set_basics($client_basics) {}
	public function set_access($client_access) {}
	public function set_payg_billing($client_billing) {}
	public function set_monthly_billing($client_billing) {}
}



/* End of file			: mock.csrest_clients.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.csrest_clients.php */
