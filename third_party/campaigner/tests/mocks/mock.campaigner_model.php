<?php

/**
 * Mock Campaigner Model.
 *
 * @see			http://www.simpletest.org/en/mock_objects_documentation.html
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package 	Campaigner
 */

class Mock_campaigner_model {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS.
	 * ------------------------------------------------------------ */
	
	public function activate_extension() {}
	public function disable_extension() {}
	public function fix_api_response($api_result = array(), $root_node) {}
	public function get_clients_from_api() {}
	public function get_custom_fields_from_input_array($custom_fields_data = array()) {}
	public function get_extension_class() {}
	public function get_extension_settings() {}
	public function get_installed_extension_version() {}
	public function get_mailing_lists_from_api() {}
	public function get_mailing_lists_from_db() {}
	public function get_mailing_lists_from_input() {}
	public function get_member_fields() {}
	public function get_package_name() {}
	public function get_package_version() {}
	public function get_settings_from_db() {}
	public function get_site_id() {}
	public function get_theme_url() {}
	public function save_extension_settings($settings) {}
	public function save_mailing_lists_to_db($settings) {}
	public function save_settings_to_db($settings) {}
	public function set_api_connector($api_connector) {}
	public function update_extension($installed_version = '', $package_version = '') {}
	public function update_extension_settings_from_input($settings) {}
	public function update_settings_from_input($settings) {}
	public function validate_api_response($api_response = array(), $root_node = '') {}
	
}


/* End of file		: mock.campaigner_model.php */
/* File location	: third_party/campaigner/tests/mocks/mock.campaigner_model.php */