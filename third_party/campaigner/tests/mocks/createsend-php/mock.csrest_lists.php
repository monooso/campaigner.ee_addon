<?php

/**
 * Mock CreateSend 'lists' class.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once dirname(__FILE__).'/mock.base_classes.php';

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


class Mock_CS_REST_Lists extends Mock_CS_REST_Wrapper_Base {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function CS_REST_Lists ($list_id, $api_key, $protocol = 'https', $debug_level = CS_REST_LOG_NONE, $host = 'api.createsend.com', $log = NULL, $serialiser = NULL, $transport = NULL) {}
	public function set_list_id($list_id) {}
	public function create($client_id, $list_details) {}
	public function update($list_details) {}
	public function create_custom_field($custom_field_details) {}
	public function update_field_options($key, $new_options, $keep_existing) {}
	public function delete() {}
	public function delete_custom_field($key) {}
	public function get_custom_fields() {}
	public function get_segments() {}
	public function get_active_subscribers($added_since, $page_number = NULL, $page_size = NULL, $order_field = NULL, $order_direction = NULL) {}
	public function get_bounced_subscribers($bounced_since, $page_number = NULL, $page_size = NULL, $order_field = NULL, $order_direction = NULL) {}
	public function get_unsubscribed_subscribers($unsubscribed_since, $page_number = NULL, $page_size = NULL, $order_field = NULL, $order_direction = NULL) {}
	public function get() {}
	public function get_stats() {}
	public function get_webhooks() {}
	public function create_webhook($webhook) {}
	public function test_webhook($webhook_id) {}
	public function delete_webhook($webhook_id) {}
	public function activate_webhook($webhook_id) {}
	public function deactivate_webhook($webhook_id) {}
}



/* End of file			: mock.csrest_lists.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.csrest_lists.php */
