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
	public function activate_extension_error_log_table() {}
	public function activate_extension_mailing_lists_table() {}
	public function activate_extension_register_hooks() {}
	public function activate_extension_settings_table() {}
	public function disable_extension() {}
	public function get_api_connector() {}
	public function get_error_log() {}
	public function get_extension_class() {}
	public function get_extension_settings() {}
	public function get_installed_extension_version() {}
	public function get_mailing_lists_from_db() {}
	public function get_member_by_id($member_id) {}
	public function get_member_fields() {}
	public function get_member_subscribe_lists($member_id) {}
	public function get_package_name() {}
	public function get_package_version() {}
	public function get_settings_from_db() {}
	public function get_site_id() {}
	public function get_support_url() {}
	public function get_theme_url() {}
	public function log_error(Campaigner_api_error $error) {}
	public function save_extension_settings($settings) {}
	public function save_mailing_lists_to_db($settings) {}
	public function save_settings_to_db($settings) {}
	public function update_basic_settings_from_input($settings) {}
	public function update_extension($installed_version = '', $package_version = '') {}
	public function update_extension_settings_from_input($settings) {}
	public function update_mailing_list_settings_from_input($settings) {}
	
}


/* End of file		: mock.campaigner_model.php */
/* File location	: third_party/campaigner/tests/mocks/mock.campaigner_model.php */