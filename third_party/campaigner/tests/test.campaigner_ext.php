<?php

/**
 * Tests for the Campaigner model.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/ext.campaigner' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_settings' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/mock.campaigner_model' .EXT;

class Test_campaigner_ext extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Extension settings.
	 *
	 * @access	private
	 * @var		Campaigner_settings
	 */
	private $_ext_settings;
	
	/**
	 * Installed extension version.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_installed_extension_version;
	
	/**
	 * Package version.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_package_version;
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Runs before each test.
	 *
	 * @access	public
	 * @return	void
	 */
	public function setUp()
	{
		parent::setUp();
		
		Mock::generate('Mock_campaigner_model', 'Mock_model');
		$this->_ee->campaigner_model = new Mock_model();
		
		// Set the default model return values (used in _get_ext).
		$this->_ext_settings = new Campaigner_settings(array(
			'api_key'	=> 'API_KEY',
			'client_id'	=> 'CLIENT_ID'
		));
		
		$this->_installed_extension_version	= '1.0.0';
		$this->_package_version				= '1.0.0';
	}
	
	
	/**
	 * Creates a new Extension instance, using the class settings.
	 *
	 * @access	private
	 * @return	Campaigner_ext
	 */
	private function _get_ext()
	{
		// Shortcuts.
		$model = $this->_ee->campaigner_model;
		
		// Return values.
		$model->setReturnReference('get_extension_settings', $this->_ext_settings);
		$model->setReturnReference('update_extension_settings_from_input', $this->_ext_settings);
		$model->setReturnValue('get_installed_extension_version', $this->_installed_extension_version);
		$model->setReturnValue('get_package_version', $this->_package_version);
		
		return new Campaigner_ext();
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_activate_extension__success()
	{
		// Load the extension.
		$ext = $this->_get_ext();
		
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('activate_extension');
		
		// Tests.
		$ext->activate_extension();
	}
	
	
	public function test_disable_extension__success()
	{
		// Load the extension.
		$ext = $this->_get_ext();
		
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('disable_extension');
		
		// Tests.
		$ext->disable_extension();
	}
	
	
	public function test_save_settings__success()
	{
		$ext		= $this->_get_ext();
		$model 		= $this->_ee->campaigner_model;
		$session	= $this->_ee->session;
		
		// Expectations.
		$model->expectOnce('save_extension_settings', array($this->_ext_settings));
		$session->expectOnce('set_flashdata', array('message_success', '*'));
		
		// Tests.
		$ext->save_settings();
	}
	
	
	public function test_save_settings__failure()
	{
		$ext		= $this->_get_ext();
		$model 		= $this->_ee->campaigner_model;
		$session	= $this->_ee->session;
		
		// Expectations.
		$session->expectOnce('set_flashdata', array('message_failure', '*'));
		
		// Return values.
		$model->throwOn('save_extension_settings', new Exception('EXCEPTION'));
		$model->setReturnValue('update_extension_settings_from_input', new Campaigner_settings());
		
		// Tests.
		$ext->save_settings();
	}
	
	
	public function test_update_extension__update_required()
	{
		// Change the model return values before instantiating the extension.
		$this->_package_version = '1.1.1';
		
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$installed_version	= '1.1.0';
		
		// Expectations.
		$model->expectOnce('update_extension', array($installed_version, $this->_package_version));
		
		// Return values.
		$model->setReturnValue('update_extension', TRUE);
		
		// Tests.
		$this->assertIdentical(TRUE, $ext->update_extension($installed_version));
	}
	
	
	public function test_update_extension__no_update_required()
	{
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$installed_version = '1.1.0';		// Can be anything.
		
		// Return values.
		$model->setReturnValue('update_extension', FALSE);
		
		// Tests.
		$this->assertIdentical(FALSE, $ext->update_extension($installed_version));
	}
	
	
	public function test_display_settings_clients__success()
	{
		// Shortcuts.
		$ext	= $this->_get_ext();
		$loader	= $this->_ee->load;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$clients	= array();
		$view_vars 	= array('clients' => $clients, 'settings' => $this->_ext_settings);
		
		// Expectations.
		$loader->expectOnce('view', array('_clients', $view_vars, TRUE));
		$model->expectOnce('get_clients_from_api');
		
		// Return values.
		$model->setReturnValue('get_clients_from_api', $clients);
		
		// Tests.
		$ext->display_settings_clients();
	}
	
	
	public function test_display_settings_clients__api_error()
	{
		// Shortcuts.
		$ext	= $this->_get_ext();
		$loader = $this->_ee->load;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$exception = new Exception('Invalid API key', 100);
		$view_vars = array('api_error' => new Campaigner_api_error(array(
			'code'		=> $exception->getCode(),
			'message'	=> $exception->getMessage()
		)));
		
		// Expectations.
		$loader->expectOnce('view', array('_api_error', $view_vars, TRUE));
		$model->expectOnce('get_clients_from_api');
		
		// Return values.
		$model->throwOn('get_clients_from_api', $exception);
		
		// Tests.
		$ext->display_settings_clients();
	}
	
	
	public function test_display_settings_mailing_lists__success()
	{
		// Shortcuts.
		$ext	= $this->_get_ext();
		$loader	= $this->_ee->load;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$lists					= array();
		$member_fields			= array();
		$member_fields_dd_data	= array();
		
		$view_vars = array(
			'mailing_lists'			=> $lists,
			'member_fields'			=> $member_fields,
			'member_fields_dd_data' => $member_fields_dd_data,
			'settings'				=> $this->_ext_settings
		);
		
		// Expectations.
		$loader->expectOnce('view', array('_mailing_lists', $view_vars, TRUE));
		$model->expectOnce('get_mailing_lists_from_api', array($this->_ext_settings->get_client_id()));
		$model->expectOnce('get_member_fields');
		
		// Return values.
		$model->setReturnValue('get_mailing_lists_from_api', $lists);
		$model->setReturnValue('get_member_fields', $member_fields);
		
		// Tests.
		$ext->display_settings_mailing_lists();
	}
	
	
	public function test_display_settings_mailing_lists__api_error()
	{
		// Shortcuts.
		$ext	= $this->_get_ext();
		$loader	= $this->_ee->load;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$exception = new Exception('Invalid API key', 100);
		$view_vars = array('api_error' => new Campaigner_api_error(array(
			'code'		=> $exception->getCode(),
			'message'	=> $exception->getMessage()
		)));
		
		// Return values.
		$model->throwOn('get_mailing_lists_from_api', $exception);
		
		// Expectations.
		$loader->expectOnce('view', array('_api_error', $view_vars, TRUE));
		$model->expectOnce('get_mailing_lists_from_api');
		
		// Tests.
		$ext->display_settings_mailing_lists();
	}
	
	
	public function test_on_cp_members_member_create__success()
	{
		$ext = $this->_get_ext();
		
		// Dummy values.
		$member_id		= 10;
		$member_data 	= array();
		
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('subscribe_member', array($member_id));
		
		// Tests.
		$ext->on_cp_members_member_create($member_id, $member_data);
	}
	
	
	public function test_on_cp_members_validate_members__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$input 	= $this->_ee->input;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_ids = array(10, 20, 30);
		
		// Expectations.
		$input->expectOnce('post', array('toggle'));
		$model->expectCallCount('subscribe_member', count($member_ids));
		
		for ($count = 0; $count < count($member_ids); $count++)
		{
			$model->expectAt($count, 'subscribe_member', array($member_ids[$count]));
		}
		
		// Return values.
		$config->setReturnValue('item', 'manual', array('req_mbr_activation'));
		$input->setReturnValue('post', $member_ids, array('toggle'));
		
		// Tests.
		$ext->on_cp_members_validate_members();
	}
	
	
	public function test_on_member_register_validate_members__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id = 10;
		
		// Expectations.
		$model->expectOnce('subscribe_member', array($member_id));
		
		// Return values.
		$config->setReturnValue('item', 'email', array('req_mbr_activation'));
		
		// Tests.
		$ext->on_member_register_validate_members($member_id);
	}
	
	
	public function test_on_member_register_validate_members__no_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id = 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'none', array('req_mbr_activation'));
		
		// Tests.
		$ext->on_member_register_validate_members($member_id);
	}
	
	
	public function test_on_member_register_validate_members__manual_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id = 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'manual', array('req_mbr_activation'));
		
		// Tests.
		$ext->on_member_register_validate_members($member_id);
	}
	
	
	public function test_on_user_edit_end__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id			= 10;
		$member_data 		= array();
		$member_custom_data	= array();
		
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('subscribe_member', array($member_id, TRUE));
		
		// Tests.
		$ext->on_user_edit_end($member_id, $member_data, $member_custom_data);
	}
	
	
	public function test_on_user_register_end__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id		= 10;
		$user 			= new StdClass();		// Not really important what this is.
		
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('subscribe_member', array($member_id));
		
		// Tests.
		$ext->on_user_register_end($user, $member_id);
	}
	
	
	public function test_on_member_member_register__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext 	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy data.
		$member_data	= array();
		$member_id		= 10;
		
		// Expectations.
		$config->expectOnce('item', array('req_mbr_activation'));
		$model->expectOnce('subscribe_member', array($member_id));
		
		// Return values.
		$config->setReturnValue('item', 'none', array('req_mbr_activation'));
		
		// Tests.
		$ext->on_member_member_register($member_data, $member_id);
	}
	
	
	public function test_on_member_member_register__email_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy data.
		$member_data 	= array();
		$member_id		= 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'email', array('req_mbr_activation'));
		
		// Tests.
		$ext->on_member_member_register($member_data, $member_id);
	}
	
	
	public function test_on_member_member_register__manual_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$ext	= $this->_get_ext();
		$model	= $this->_ee->campaigner_model;
		
		// Dummy data.
		$member_data 	= array();
		$member_id		= 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'manual', array('req_mbr_activation'));
		
		// Tests.
		$ext->on_member_member_register($member_data, $member_id);
	}
	
}


/* End of file		: test.campaigner_ext.php */
/* File location	: third_party/campaigner/tests/test.campaigner_ext.php */