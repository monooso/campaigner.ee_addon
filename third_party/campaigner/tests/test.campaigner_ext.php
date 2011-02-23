<?php

/**
 * Tests for the Campaigner extension.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/ext.campaigner' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/mock.campaigner_cm_api_connector' .EXT;
require_once PATH_THIRD .'campaigner/tests/mocks/mock.campaigner_model' .EXT;

class Test_campaigner_ext extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
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

	/**
	 * Settings.
	 *
	 * @access	private
	 * @var		Campaigner_settings
	 */
	private $_settings;

	/**
	 * Test subject.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_subject;
	
	
	
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

		/**
		 * Mocks
		 */
		
		Mock::generate('Mock_campaigner_model', 'Mock_model');
		$this->_ee->campaigner_model = new Mock_model();

		Mock::generate('Mock_campaigner_cm_api_connector', 'Mock_api_connector');
		$this->_connector = new Mock_api_connector();
		
		/**
		 * Dummy return values. Called from subject constructor.
		 */

		$this->_settings 			= new Campaigner_settings(array('api_key' => 'API_KEY', 'client_id' => 'CLIENT_ID'));
		$this->_installed_version	= '1.0.0';
		$this->_package_version		= '1.0.0';

		$model = $this->_ee->campaigner_model;
		
		$model->setReturnReference('get_api_connector', $this->_connector);
		$model->setReturnReference('get_extension_settings', $this->_settings);
		$model->setReturnReference('update_extension_settings_from_input', $this->_settings);
		$model->setReturnValue('get_installed_extension_version', $this->_installed_version);
		$model->setReturnValue('get_package_version', $this->_package_version);
		
		// Test subject.
		$this->_subject = new Campaigner_ext();
	}

	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_activate_extension__success()
	{
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('activate_extension');
		
		// Tests.
		$this->_subject->activate_extension();
	}
	
	
	public function test_disable_extension__success()
	{
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('disable_extension');
		
		// Tests.
		$this->_subject->disable_extension();
	}
	
	
	public function test_save_settings__success()
	{
		$model 		= $this->_ee->campaigner_model;
		$session	= $this->_ee->session;
		
		// Expectations.
		$model->expectOnce('save_extension_settings', array($this->_settings));
		$session->expectOnce('set_flashdata', array('message_success', '*'));
		
		// Tests.
		$this->_subject->save_settings();
	}
	
	
	public function test_save_settings__failure()
	{
		$model 		= $this->_ee->campaigner_model;
		$session	= $this->_ee->session;
		
		// Expectations.
		$session->expectOnce('set_flashdata', array('message_failure', '*'));
		
		// Return values.
		$model->throwOn('save_extension_settings', new Exception('EXCEPTION'));
		$model->setReturnValue('update_extension_settings_from_input', new Campaigner_settings());
		
		// Tests.
		$this->_subject->save_settings();
	}
	
	
	public function test_update_extension__update_required()
	{
		// Shortcuts.
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$installed_version = '1.1.0';

		// Expectations.
		$model->expectOnce('update_extension', array($installed_version, $this->_package_version));

		// Return values.
		$model->setReturnValue('update_extension', TRUE);
		
		// Tests.
		$this->assertIdentical(TRUE, $this->_subject->update_extension($installed_version));
	}
	
	
	public function test_update_extension__no_update_required()
	{
		$model = $this->_ee->campaigner_model;
		
		// Dummy values.
		$installed_version = '1.1.0';		// Can be anything.
		
		// Return values.
		$model->setReturnValue('update_extension', FALSE);
		
		// Tests.
		$this->assertIdentical(FALSE, $this->_subject->update_extension($installed_version));
	}


	public function test_display_error__success()
	{
		// Shortcuts.
		$loader 		= $this->_ee->load;

		// Dummy values.
		$error_code		= 100;
		$error_message	= 'ERROR';
		$view_data		= 'API error message.';
		$view_vars 		= array('error_code' => $error_code, 'error_message' => $error_message);

		// Expectations.
		$loader->expectOnce('view', array('_error', $view_vars, TRUE));
		
		// Return values.
		$loader->setReturnValue('view', $view_data, array('_error', $view_vars, TRUE));

		// Tests.
		$this->_subject->display_error($error_message, $error_code);
	}


	public function test_display_error__unknown_error()
	{
		// Shortcuts.
		$lang	= $this->_ee->lang;
		$loader = $this->_ee->load;

		// Dummy values.
		$error_message	= 'ERROR';
		$view_data		= 'API error message.';
		$view_vars 		= array('error_code' => '', 'error_message' => $error_message);

		/**
		 * Expectations.
		 *
		 * NOTE:
		 * The lang::line method is called in the constructor, which scuppers attempts to
		 * set the expected call count here. Probably a glaring "bad design" signal, but
		 * tough titties. Instead we are more explicit with the return value (setting the
		 * required parameters), so we can be confident that the lang::line method was
		 * called.
		 */
		
		$loader->expectOnce('view', array('_error', $view_vars, TRUE));
		
		// Return values.
		$lang->setReturnValue('line', $error_message, array('error__unknown_error'));
		$loader->setReturnValue('view', $view_data, array('_error', $view_vars, TRUE));

		// Tests.
		$this->_subject->display_error();
	}
	
	
	public function test_display_settings_clients__success()
	{
		// Shortcuts.
		$loader	= $this->_ee->load;
		
		// Dummy values.
		$clients	= array();
		$view_vars 	= array('clients' => $clients, 'settings' => $this->_settings);
		
		// Expectations.
		$this->_connector->expectOnce('get_clients');
		$loader->expectOnce('view', array('_clients', $view_vars, TRUE));
		
		// Return values.
		$this->_connector->setReturnValue('get_clients', $clients);
		
		// Tests.
		$this->_subject->display_settings_clients();
	}
	
	
	
	public function test_display_settings_clients__api_error()
	{
		// Shortcuts.
		$loader = $this->_ee->load;
		
		// Dummy values.
		$exception = new Campaigner_api_exception('Invalid API key', 100);
		$view_vars = array(
			'error_code'	=> $exception->getCode(),
			'error_message'	=> $exception->getMessage()
		);
		
		// Expectations.
		$this->_connector->expectOnce('get_clients');
		$loader->expectOnce('view', array('_error', $view_vars, TRUE));
		
		// Return values.
		$this->_connector->throwOn('get_clients', $exception);
		
		// Tests.
		$this->_subject->display_settings_clients();
	}
	
	
	public function test_display_settings_mailing_lists__success()
	{
		// Shortcuts.
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
			'settings'				=> $this->_settings
		);
		
		// Expectations.
		$this->_connector->expectOnce('get_client_lists', array($this->_settings->get_client_id(), TRUE));
		$loader->expectOnce('view', array('_mailing_lists', $view_vars, TRUE));
		$model->expectOnce('get_member_fields');
		
		// Return values.
		$this->_connector->setReturnValue('get_client_lists', $lists);
		$model->setReturnValue('get_member_fields', $member_fields);
		
		// Tests.
		$this->_subject->display_settings_mailing_lists();
	}
	
	
	public function test_display_settings_mailing_lists__api_error()
	{
		// Shortcuts.
		$loader	= $this->_ee->load;
		
		// Dummy values.
		$exception = new Campaigner_api_exception('Invalid API key', 100);
		$view_vars = array(
			'error_code'	=> $exception->getCode(),
			'error_message'	=> $exception->getMessage()
		);
		
		// Return values.
		$this->_connector->throwOn('get_client_lists', $exception);
		
		// Expectations.
		$this->_connector->expectOnce('get_client_lists', array($this->_settings->get_client_id(), TRUE));
		$loader->expectOnce('view', array('_error', $view_vars, TRUE));

		// Tests.
		$this->_subject->display_settings_mailing_lists();
	}


	public function test__subscribe_member__success()
	{
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$member_id = 10;
		$member_subscribe_lists = array(
			new Campaigner_mailing_list(array(
				'list_id'	=> 'abc123',
				'list_name'	=> 'LIST A'
			)),
			new Campaigner_mailing_list(array(
				'list_id'	=> 'cde456',
				'list_name'	=> 'LIST B'
			))
		);

		$subscriber = new Campaigner_subscriber(array(
			'email'	=> 'me@here.com',
			'name'	=> 'John Doe'
		));

		// Expectations.
		$model->expectOnce('get_member_subscribe_lists', array($member_id));
		$model->expectCallCount('get_member_as_subscriber', count($member_subscribe_lists));
		$this->_connector->expectCallCount('add_list_subscriber', count($member_subscribe_lists));

		$count = 0;
		foreach ($member_subscribe_lists AS $list)
		{
			$model->expectAt($count, 'get_member_as_subscriber', array($member_id, $list->get_list_id()));
			$this->_connector->expectAt($count, 'add_list_subscriber', array($list->get_list_id(), $subscriber, FALSE));

			$count++;
		}

		// Return values.
		$model->setReturnValue('get_member_subscribe_lists', $member_subscribe_lists);
		$model->setReturnValue('get_member_as_subscriber', $subscriber);

		// Tests.
		$this->_subject->subscribe_member($member_id);
	}


	public function test__unsubscribe_member__success()
	{
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$member_id = 10;

		// Retrieve all the mailing lists.
		$mailing_lists = array(
			new Campaigner_mailing_list(array(
				'list_id'	=> 'list_a',
				'list_name'	=> 'LIST A'
			)),
			new Campaigner_mailing_list(array(
				'list_id'	=> 'list_b',
				'list_name'	=> 'LIST B'
			)),
			new Campaigner_mailing_list(array(
				'list_id'	=> 'list_c',
				'list_name'	=> 'LIST C'
			))
		);

		$model->expectOnce('get_all_mailing_lists');
		$model->setReturnValue('get_all_mailing_lists', $mailing_lists);

		// Retrieve the member information.
		$email = 'me@here.com';

		$member_data = array(
			'email'		=> $email,
			'member_id'	=> $member_id
		);

		$model->expectOnce('get_member_by_id', array($member_id));
		$model->setReturnValue('get_member_by_id', $member_data);	

		// For each mailing list, determine if the member is subscribed.
		$this->_connector->expectCallCount('get_is_subscribed', count($mailing_lists));
		$this->_connector->expectCallCount('remove_list_subscriber', ceil(count($mailing_lists) / 2));
		$this->_connector->setReturnValue('remove_list_subscriber', TRUE);

		$count = 0;
		$remove_count = 0;

		foreach ($mailing_lists AS $mailing_list)
		{
			$is_subscribed = (bool) $count % 2;

			$this->_connector->expectAt($count, 'get_is_subscribed', array($mailing_list->get_list_id(), $email));
			$this->_connector->setReturnValueAt($count, 'get_is_subscribed', $is_subscribed);

			if ($is_subscribed)
			{
				// Unsubscribe the member.
				$this->_connector->expectAt($remove_count++, 'remove_list_subscriber', array($mailing_list->get_list_id(), $email));
			}

			$count++;
		}
		
		// Run the tests.
		$this->_subject->unsubscribe_member($member_id);
	}


	public function test__unsubscribe_member__invalid_member_id()
	{
		// Shortcuts.
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$member_id	= 0;
		$message	= 'Invalid member ID.';

		// Expectations and return values.
		$this->_ee->lang->setReturnValue('line', $message, array('error_missing_or_invalid_member_id'));

		$model->expectNever('get_all_mailing_lists');
		$model->expectNever('get_member_by_id');

		$this->_connector->expectNever('get_is_subscribed');
		$this->_connector->expectNever('remove_list_subscriber');

		// Run the tests.
		$this->expectException(new Campaigner_exception($message));
		$this->_subject->unsubscribe_member($member_id);
	}


	public function test__unsubscribe_member__unknown_member()
	{
		// Shortcuts.
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$member_id		= 10;
		$member_data	= array();
		$message		= 'Unknown member';

		// Expectations and return values.
		$this->_ee->lang->setReturnValue('line', $message, array('error_unknown_member'));

		$model->expectOnce('get_member_by_id');
		$model->setReturnValue('get_member_by_id', $member_data);

		$model->expectNever('get_all_mailing_lists');
		$this->_connector->expectNever('get_is_subscribed');
		$this->_connector->expectNever('remove_list_subscriber');

		// Run the tests.
		$this->expectException(new Campaigner_exception($message));
		$this->_subject->unsubscribe_member($member_id);
	}
		
	
	public function test__unsubscribe_member__no_mailing_lists()
	{
		// Shortcuts.
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$email			= 'me@here.com';
		$member_id		= 10;
		$member_data = array(
			'email'		=> $email,
			'member_id'	=> $member_id
		);

		// Expectations and return values.
		$model->expectOnce('get_member_by_id');
		$model->setReturnValue('get_member_by_id', $member_data);

		$model->expectOnce('get_all_mailing_lists');
		$model->setReturnValue('get_all_mailing_lists', array());

		$this->_connector->expectNever('get_is_subscribed');
		$this->_connector->expectNever('remove_list_subscriber');

		// Run the tests.
		$this->_subject->unsubscribe_member($member_id);
	}
		
	
	public function test__unsubscribe_member__unable_to_unsubscribe()
	{
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$member_id = 10;
		$message = 'Unable to unsubscribe member.';

		// Retrieve all the mailing lists.
		$mailing_lists = array(
			new Campaigner_mailing_list(array(
				'list_id'	=> 'list_a',
				'list_name'	=> 'LIST A'
			))
		);

		$model->expectOnce('get_all_mailing_lists');
		$model->setReturnValue('get_all_mailing_lists', $mailing_lists);

		// Retrieve the member information.
		$email = 'me@here.com';

		$member_data = array(
			'email'		=> $email,
			'member_id'	=> $member_id
		);

		$model->expectOnce('get_member_by_id', array($member_id));
		$model->setReturnValue('get_member_by_id', $member_data);	

		// Expectations and return values.
		$this->_connector->expectOnce('get_is_subscribed');
		$this->_connector->setReturnValue('get_is_subscribed', TRUE);

		$this->_connector->expectOnce('remove_list_subscriber');
		$this->_connector->setReturnValue('remove_list_subscriber', FALSE);

		$this->_ee->lang->setReturnValue('line', $message, array('api_error_cannot_unsubscribe_member'));
		
		// Run the tests.
		$this->expectException(new Campaigner_api_exception($message));
		$this->_subject->unsubscribe_member($member_id);
	}


	public function xtest_on_cp_members_member_create__success()
	{
		// Shortcuts.
		$model = $this->_ee->campaigner_model;

		// Dummy values.
		$member_id		= 10;
		$member_data 	= array();
		
		// Expectations.
		$model->expectOnce('get_member_subscribe_lists', array($member_id));
		$this->_connector->expectCallCount('add_list_subscriber', count($lists));

		for ($count = 0, $list_count = count($lists); $count < $list_count; $count++)
		{
			// $this->_connector->expectAt($count, 'add_list_subscriber', array($lists[$count]->get_list_id(), $member_id));
		}

		// Return values.
		$model->setReturnValue('get_member_subscribe_lists', $lists);
		
		// Tests.
		$this->_subject->on_cp_members_member_create($member_id, $member_data);
	}
	
	
	public function xtest_on_cp_members_validate_members__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
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
		$this->_subject->on_cp_members_validate_members();
	}
	
	
	public function xtest_on_member_register_validate_members__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id = 10;
		
		// Expectations.
		$model->expectOnce('subscribe_member', array($member_id));
		
		// Return values.
		$config->setReturnValue('item', 'email', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_member_register_validate_members($member_id);
	}
	
	
	public function xtest_on_member_register_validate_members__no_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id = 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'none', array('req_mbr_activation'));
		
		// Tests.
		$ths->_subject->on_member_register_validate_members($member_id);
	}
	
	
	public function xtest_on_member_register_validate_members__manual_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id = 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'manual', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_member_register_validate_members($member_id);
	}
	
	
	public function xtest_on_user_edit_end__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id			= 10;
		$member_data 		= array();
		$member_custom_data	= array();
		
		// Expectations.
		$this->_ee->campaigner_model->expectOnce('subscribe_member', array($member_id, TRUE));
		
		// Tests.
		$this->_subject->on_user_edit_end($member_id, $member_data, $member_custom_data);
	}
	
	
	public function xtest_on_user_register_end__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id		= 10;
		$user 			= new StdClass();		// Not really important what this is.
		
		// Expectations.
		$config->expectOnce('item', array('req_mbr_activation'));
		$this->_ee->campaigner_model->expectOnce('subscribe_member', array($member_id));
		
		// Returns values.
		$config->setReturnValue('item', 'none', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_user_register_end($user, $member_id);
	}
	
	
	public function xtest_on_user_register_end__email_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id		= 10;
		$user 			= new StdClass();		// Not really important what this is.
		
		// Expectations.
		$this->_ee->campaigner_model->expectNever('subscribe_member');
		
		// Returns values.
		$config->setReturnValue('item', 'email', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_user_register_end($user, $member_id);
	}
	
	
	public function xtest_on_user_register_end__manual_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy values.
		$member_id		= 10;
		$user 			= new StdClass();		// Not really important what this is.
		
		// Expectations.
		$this->_ee->campaigner_model->expectNever('subscribe_member');
		
		// Returns values.
		$config->setReturnValue('item', 'manual', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_user_register_end($user, $member_id);
	}
	
	
	public function xtest_on_member_member_register__success()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
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
		$this->_subject->on_member_member_register($member_data, $member_id);
	}
	
	
	public function xtest_on_member_member_register__email_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy data.
		$member_data 	= array();
		$member_id		= 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'email', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_member_member_register($member_data, $member_id);
	}
	
	
	public function xtest_on_member_member_register__manual_activation()
	{
		// Shortcuts.
		$config	= $this->_ee->config;
		$model	= $this->_ee->campaigner_model;
		
		// Dummy data.
		$member_data 	= array();
		$member_id		= 10;
		
		// Expectations.
		$model->expectNever('subscribe_member');
		
		// Return values.
		$config->setReturnValue('item', 'manual', array('req_mbr_activation'));
		
		// Tests.
		$this->_subject->on_member_member_register($member_data, $member_id);
	}
	



}


/* End of file		: test.campaigner_ext.php */
/* File location	: third_party/campaigner/tests/test.campaigner_ext.php */
