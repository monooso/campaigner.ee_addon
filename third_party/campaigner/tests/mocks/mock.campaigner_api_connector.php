<?php

/**
 * Mock Campaigner API Connector.
 *
 * @see				: http://www.simpletest.org/en/mock_objects_documentation.html
 * @author 			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package 		: Campaigner
 */

class Mock_campaigner_api_connector {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS.
	 * ------------------------------------------------------------ */
	
	public function add_list_subscriber($list_id, Campaigner_subscriber $subscriber, $resubscribe = FALSE) {}
	public function get_clients() {}
	public function get_client_lists($client_id, $include_fields = FALSE) {}
	public function get_is_subscribed($list_id, $email) {}
	public function get_list_fields($list_id) {}
	public function remove_list_subscriber($list_id, $email) {}
	public function update_list_subscriber($list_id, Campaigner_subscriber $subscriber) {}
	
}


/* End of file		: mock.campaigner_api_connector.php */
/* File location	: third_party/campaigner/tests/mocks/mock.campaigner_api_connector.php */
