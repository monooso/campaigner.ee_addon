<?php

/**
 * Tests for the Campaigner model.
 *
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/models/campaigner_model' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_settings' .EXT;

class Test_campaigner_model extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The model.
	 *
	 * @access	private
	 * @var		Campaigner_model
	 */
	private $_model;
	
	
	
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
		
		$this->_model = new Campaigner_model();
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_get_package_name()
	{
		$this->assertEqual(
			strtolower($this->_model->get_package_name()),
			'campaigner'
		);
		
		$this->assertNotEqual(
			strtolower($this->_model->get_package_name()),
			'wibble'
		);
	}
	
	
	public function test_get_package_version()
	{
		$this->assertPattern(
			'/^[0-9abcdehlprtv\.]+$/i',
			$this->_model->get_package_version()
		);
	}
	
	
	public function test_get_extension_class()
	{
		$this->assertEqual(
			strtolower($this->_model->get_extension_class()),
			'campaigner_ext'
		);
		
		$this->assertNotEqual(
			strtolower($this->_model->get_extension_class()),
			'campaigner'
		);
	}
	
	
	public function test_get_site_id()
	{
		$config = $this->_ee->config;
		$site_id = '10';
		
		$config->expectOnce('item', array('site_id'));
		$config->setReturnValue('item', $site_id, array('site_id'));
		
		$this->assertIdentical($site_id, $this->_model->get_site_id());
	}
	
	
	public function test_activate_extension__create_settings_table()
	{
		$db 	= $this->_ee->db;
		$dbf	= $this->_ee->dbforge;
		$loader	= $this->_ee->load;
		
		/**
		 * Create the settings table.
		 * - site_id
		 * - api_key
		 * - client_id
		 */
		
		$fields = array(
			'site_id'	=> array(
				'constraint'		=> 5,
				'type'				=> 'int',
				'unsigned'			=> TRUE
			),
			'api_key'	=> array(
				'constraint'			=> 20,
				'type'				=> 'varchar'
			),
			'client_id'	=> array(
				'constraint'		=> 20,
				'type'				=> 'varchar'
			)
		);
		
		$loader->expectOnce('dbforge', array());
		
		$dbf->expectAt(0, 'add_field', array($fields));
		$dbf->expectAt(0, 'add_key', array('site_id', TRUE));
		$dbf->expectAt(0, 'create_table', array('campaigner_settings'));
		
		// Tests for the _total_ call count.
		$dbf->expectCallCount('add_field', 2);
		$dbf->expectCallCount('add_key', 2);
		$dbf->expectCallCount('create_table', 2);
		
		$this->_model->activate_extension();
	}
	
	
	public function test_activate_extension__create_mailing_lists_table()
	{
		$db		= $this->_ee->db;
		$dbf	= $this->_ee->dbforge;
		$loader	= $this->_ee->load;
		
		/**
		 * Create the mailing lists table.
		 * - list_id
		 * - site_id
		 * - custom_fields
		 * 		serialised array: array($merge_variable => $member_field_id) --> simplest solution, for now.
		 * - trigger_field_id
		 * - trigger_field_value
		 */
		
		$fields = array(
			'list_id' => array(
				'constraint'	=> 20,
				'type'			=> 'varchar'
			),
			'site_id' => array(
				'constraint'	=> 5,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'custom_fields' => array(
				'type'			=> 'text'
			),
			'trigger_field_id' => array(
				'constraint'	=> 4,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'trigger_value' => array(
				'constraint'	=> 255,
				'type'			=> 'varchar'
			)
		);
		
		$loader->expectOnce('dbforge', array());
		
		$dbf->expectAt(1, 'add_field', array($fields));
		$dbf->expectAt(1, 'add_key', array('list_id', TRUE));
		$dbf->expectAt(1, 'create_table', array('campaigner_mailing_lists'));
		
		$this->_model->activate_extension();
	}
	
	
	public function test_activate_extension__register_extension_hooks()
	{
		$db = $this->_ee->db;
		
		/**
		 * Register the extension hooks:
		 * - cp_members_validate_members
		 * - member_member_register
		 * - member_register_validate_members
		 * - user_edit_end
		 * - user_register_end
		 */
		
		$class = $this->_model->get_extension_class();
		$version = $this->_model->get_package_version();
		
		$hooks = array(
			'cp_members_validate_members',
			'member_member_register',
			'member_register_validate_members',
			'user_edit_end',
			'user_register_end'
		);
		
		for ($list_count = 0; $list_count < count($hooks); $list_count++)
		{
			$data = array(
				'class'		=> $class,
				'enabled'	=> 'y',
				'hook'		=> $hooks[$list_count],
				'method'	=> 'on_' .$hooks[$list_count],
				'priority'	=> 10,
				'settings'	=> '',
				'version'	=> $version
			);
			
			$db->expectAt($list_count, 'insert', array('extensions', $data));
		}
		
		$db->expectCallCount('insert', count($hooks));
		$this->_model->activate_extension();
	}
	
	
	public function test_disable_extension()
	{
		$db = $this->_ee->db;
		$dbf = $this->_ee->dbforge;
		
		/**
		 * 1. Delete the extension hooks.
		 * 2. Drop the settings table.
		 * 3. Drop the mailing lists table.
		 */
		
		$db->expectOnce('delete', array('extensions', array('class' => $this->_model->get_extension_class())));
		
		$dbf->expectCallCount('drop_table', 2);
		$dbf->expectAt(0, 'drop_table', array('campaigner_settings'));
		$dbf->expectAt(1, 'drop_table', array('campaigner_mailing_lists'));
		
		$this->_model->disable_extension();
	}
	
	
	public function test_update_extension__update()
	{
		$db = $this->_ee->db;
		
		$installed_version	= '1.0.0';
		$package_version	= '1.1.0';
		
		// Update the extension version number in the database.
		$data = array('version' => $package_version);
		$criteria = array('class' => $this->_model->get_extension_class());
		
		$db->expectOnce('update', array('extensions', $data, $criteria));
		
		$this->assertIdentical(NULL, $this->_model->update_extension($installed_version, $package_version));
	}
	
	
	public function test_update_extension__no_update()
	{
		$installed_version	= '1.0.0';
		$package_version	= '1.0.0';
		
		$this->assertIdentical(FALSE, $this->_model->update_extension($installed_version, $package_version));
	}
	
	
	public function test_update_extension__not_installed()
	{
		$installed_version	= '';
		$package_version	= '1.0.0';
		
		$this->assertIdentical(FALSE, $this->_model->update_extension($installed_version, $package_version));
	}
	
	
	public function test_get_settings_from_db__success()
	{
		$config		= $this->_ee->config;
		$db			= $this->_ee->db;
		
		$site_id	= '10';
		$api_key	= 'api_key';
		$client_id	= 'client_id';
		
		// Return the site ID.
		$config->expectOnce('item', array('site_id'));
		$config->setReturnValue('item', $site_id, array('site_id'));
		
		// Settings db row.
		$db_row = array(
			'site_id'	=> $site_id,
			'api_key'	=> $api_key,
			'client_id'	=> $client_id
		);
		
		$db_query = $this->_get_mock('db_query');
		
		// Return values.
		$db_query->setReturnValue('num_rows', 1);
		$db_query->setReturnValue('row_array', $db_row);
		$db->setReturnReference('get_where', $db_query);
		
		// Expectations.
		$db_query->expectOnce('num_rows');
		$db_query->expectOnce('row_array');
		$db->expectOnce('get_where', array('campaigner_settings', array('site_id' => $site_id), 1));
		
		// Create the settings object.
		$settings = new Campaigner_settings($db_row);
		
		// Run the test.
		$this->assertIdentical($settings, $this->_model->get_settings_from_db());
	}
	
	
	public function test_get_settings_from_db__no_settings()
	{
		$db = $this->_ee->db;
		$db_query = $this->_get_mock('db_query');
		
		// Return values.
		$db_query->setReturnValue('num_rows', 0);
		$db->setReturnReference('get_where', $db_query);
		
		// Expectations.
		$db_query->expectNever('row_array');
		
		// Run the test.
		$this->assertIdentical(new Campaigner_settings(), $this->_model->get_settings_from_db());
	}
	
	
	public function test_get_mailing_lists_from_db__success()
	{
		$db			= $this->_ee->db;
		$db_query 	= $this->_get_mock('db_query');
		$site_id 	= '10';
		
		// Site ID.
		$this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
		
		// Custom fields.
		$custom_fields_data	= array();
		$custom_fields 		= array();
		
		for ($list_count = 0; $list_count < 10; $list_count++)
		{
			$data = array('field_id' => 'm_field_id_' .$list_count, 'id' => 'merge_var_id_' .$list_count);
			
			$custom_fields_data[] 	= $data;
			$custom_fields[] 		= new Campaigner_custom_field($data);
		}
		
		$custom_fields_data = serialize($custom_fields_data);
		
		// Rows / mailing lists.
		$db_rows 		= array();
		$mailing_lists 	= array();
		
		for ($list_count = 0; $list_count < 10; $list_count++)
		{
			$data = array(
				'site_id'			=> $site_id,
				'custom_fields'		=> $custom_fields_data,
				'list_id'			=> 'list_id_' .$list_count,
				'trigger_field_id'	=> 'm_field_id_' .$list_count,
				'trigger_value'		=> 'trigger_value_' .$list_count
			);
			
			$db_rows[] = $data;
			
			$data['custom_fields']	= $custom_fields;
			$mailing_lists[]		= new Campaigner_mailing_list($data);
		}
		
		// Return values.
		$db_query->setReturnValue('num_rows', count($db_rows));
		$db_query->setReturnValue('result_array', $db_rows);
		$db->setReturnReference('get_where', $db_query);
		
		// Expectations.
		$db_query->expectOnce('result_array');
		$db->expectOnce('get_where', array('campaigner_mailing_lists', array('site_id' => $site_id)));
		
		// Run the test.
		$this->assertIdentical($mailing_lists, $this->_model->get_mailing_lists_from_db());
	}
	
	
	public function test_get_mailing_lists_from_db__no_mailing_lists()
	{
		$db = $this->_ee->db;
		$db_query = $this->_get_mock('db_query');
		
		// Retun values.
		$db_query->setReturnValue('result_array', array());
		$db->setReturnReference('get_where', $db_query);
		
		// Run the test.
		$this->assertIdentical(array(), $this->_model->get_mailing_lists_from_db());
	}
	
	
	public function test_get_mailing_lists_from_db__no_merge_variables()
	{
		$db			= $this->_ee->db;
		$db_query 	= $this->_get_mock('db_query');
		$site_id 	= '10';
		
		// Rows / mailing lists.
		$db_rows = array();
		$mailing_lists = array();
		
		for ($list_count = 0; $list_count < 10; $list_count++)
		{
			$data = array(
				'site_id'			=> $site_id,
				'custom_fields'		=> NULL,
				'list_id'			=> 'list_id_' .$list_count,
				'trigger_field_id'	=> 'm_field_id_' .$list_count,
				'trigger_value'		=> 'trigger_value_' .$list_count
			);
			
			$db_rows[] = $data;
			
			unset($data['custom_fields']);
			$mailing_lists[] = new Campaigner_mailing_list($data);
		}
		
		// Return values.
		$db_query->setReturnValue('num_rows', count($db_rows));
		$db_query->setReturnValue('result_array', $db_rows);
		$db->setReturnReference('get_where', $db_query);
		
		// Run the test.
		$this->assertIdentical($mailing_lists, $this->_model->get_mailing_lists_from_db());
	}
	
	
	public function test_save_settings_to_db__success()
	{
		$config		= $this->_ee->config;
		$db			= $this->_ee->db;
		$site_id	= '10';
		
		// Settings.
		$settings = new Campaigner_settings(array(
			'api_key'	=> 'API key',
			'client_id'	=> 'Client ID'
		));
		
		$settings_data = $settings->to_array();
		unset($settings_data['mailing_lists']);
		$settings_data = array_merge(array('site_id' => $site_id), $settings_data);
		
		// Return values.
		$config->setReturnValue('item', $site_id, array('site_id'));
		$db->setReturnValue('affected_rows', 1);
		
		// Expectations.
		$config->expectOnce('item', array('site_id'));
		$db->expectOnce('delete', array('campaigner_settings', array('site_id' => $site_id)));
		$db->expectOnce('insert', array('campaigner_settings', $settings_data));
		
		// Run the test.
		$this->assertIdentical(TRUE, $this->_model->save_settings_to_db($settings));
	}
	
	
	public function test_save_settings_to_db__failure()
	{
		$this->_ee->db->setReturnValue('affected_rows', 0);
		$this->assertIdentical(FALSE, $this->_model->save_settings_to_db(new Campaigner_settings()));
	}
	
	
	public function test_save_mailing_lists_to_db__success()
	{
		$config		= $this->_ee->config;
		$db 		= $this->_ee->db;
		$site_id	= '10';
		
		// Merge variables.
		for ($list_count = 0; $list_count < 10; $list_count++)
		{
			$custom_fields[] = new Campaigner_custom_field(array(
				'field_id'	=> 'm_field_id_' .$list_count,
				'id'		=> 'id_' .$list_count
			));
		}
		
		// Mailing lists.
		for ($list_count = 0; $list_count < 10; $list_count++)
		{
			$mailing_lists[] = new Campaigner_mailing_list(array(
				'custom_fields'		=> $custom_fields,
				'list_id'			=> 'list_id_' .$list_count,
				'trigger_field_id'	=> 'm_field_id_' .$list_count,
				'trigger_value'		=> 'trigger_value_' .$list_count
			));
		}
		
		// Settings.
		$settings = new Campaigner_settings(array('mailing_lists' => $mailing_lists));
		
		// Return values.
		$config->setReturnValue('item', $site_id, array('site_id'));
		$db->setReturnValue('affected_rows', 1);
		
		// Expectations.
		$config->expectOnce('item', array('site_id'));
		$db->expectOnce('delete', array('campaigner_mailing_lists', array('site_id' => $site_id)));
		$db->expectCallCount('insert', count($mailing_lists));
		
		for ($list_count = 0; $list_count < count($mailing_lists); $list_count++)
		{
			$data					= $mailing_lists[$list_count]->to_array();
			$data['custom_fields']	= serialize($data['custom_fields']);
			$data					= array_merge(array('site_id' => $site_id), $data);
			
			$db->expectAt($list_count, 'insert', array('campaigner_mailing_lists', $data));
		}
		
		// Run the test.
		$this->assertIdentical(TRUE, $this->_model->save_mailing_lists_to_db($settings));
	}
	
	
	public function test_save_mailing_lists_to_db__failure()
	{
		$config		= $this->_ee->config;
		$db 		= $this->_ee->db;
		$site_id	= '10';
		
		// Settings.
		$settings = new Campaigner_settings(array('mailing_lists' => array(new Campaigner_mailing_list())));
		
		// Return values.
		$config->setReturnValue('item', $site_id, array('site_id'));
		$db->setReturnValue('affected_rows', 0);
		
		// Expectations.
		$db->expectCallCount('delete', 2, array('campaigner_mailing_lists', array('site_id' => $site_id)));
		
		// Run the test.
		$this->assertIdentical(FALSE, $this->_model->save_mailing_lists_to_db($settings));
	}
	
	
	public function test_save_extension_settings__settings_error()
	{
		$db		= $this->_ee->db;
		$lang	= $this->_ee->lang;
		$error	= 'Settings not saved';
		
		// Return values.
		$db->setReturnValue('affected_rows', 0);
		$lang->setReturnValue('line', $error);
		
		// Run the test.
		try
		{
			$this->_model->save_extension_settings(new Campaigner_settings());
			$this->fail();
		}
		catch (Exception $e)
		{
			$e->getMessage() == $error
				? $this->pass()
				: $this->fail();
		}
	}
	
	
	public function test_save_extension_settings__mailing_lists_error()
	{
		$db		= $this->_ee->db;
		$config	= $this->_ee->config;
		$lang	= $this->_ee->lang;
		$error	= 'Mailing lists not saved';
		
		// Return values.
		$db->setReturnValueAt(0, 'affected_rows', 1);
		$db->setReturnValue('affected_rows', 0);
		$lang->setReturnValue('line', $error);
		
		// Settings.
		$settings = new Campaigner_settings(array('mailing_lists' => array(new Campaigner_mailing_list())));
		
		// Run the test.
		try
		{
			$this->_model->save_extension_settings($settings);
			$this->fail();
		}
		catch (Exception $e)
		{
			$e->getMessage() == $error
				? $this->pass()
				: $this->fail();
		}
	}
	
	
	public function test_update_settings_from_input__success()
	{
		$input 		= $this->_ee->input;
		$api_key	= 'API key';
		$client_id	= 'Client ID';
		
		// Return values.
		$input->setReturnValue('post', $api_key, array('api_key'));
		$input->setReturnValue('post', $client_id, array('client_id'));
		
		// Expectations
		$input->expectCallCount('post', 2);
		
		// Settings.
		$old_settings = new Campaigner_settings(array('api_key' => 'old_api_key'));
		$new_settings = new Campaigner_settings(array('api_key' => $api_key, 'client_id' => $client_id));
		
		// Run the test.
		$this->assertIdentical($new_settings, $this->_model->update_settings_from_input($old_settings));
	}
	
	
	public function test_update_settings_from_input__missing_input()
	{
		// Return values.
		$this->_ee->input->setReturnValue('post', FALSE);
		
		// Settings.
		$settings = new Campaigner_settings(array('api_key' => 'old_api_key', 'client_id' => 'old_client_id'));
		
		// Run the test.
		$this->assertIdentical($settings, $this->_model->update_settings_from_input($settings));
	}
	
	
	public function test_get_mailing_lists_from_input__success()
	{
		/**
		 * NOTE:
		 * We test the custom fields separately.
		 */
		
		$input = $this->_ee->input;
		
		// Dummy data.
		/*
		- active_mailing_lists[] (value = list_id)
		- all_mailing_lists[list_id][trigger_field_id]
		- all_mailing_lists[list_id][trigger_value]
		- all_mailing_lists[list_id][custom_fields]
		- all_mailing_lists[list_id][custom_fields][cm_field_id] (value = m_field_id)
		*/
		
		$all_mailing_lists_data		= array();
		$active_mailing_list_ids	= array();
		$active_mailing_lists 		= array();
		
		for ($list_count = 1; $list_count <= 10; $list_count++)
		{
			$mailing_list_data = array(
				'custom_fields'		=> array(),
				'trigger_field_id'	=> 'm_field_id_' .$list_count,
				'trigger_value'		=> 'trigger_value_' .$list_count
			);
			
			$all_mailing_lists_data['list_id_' .$list_count] = $mailing_list_data;
			
			// Active mailing lists.
			if ($list_count <= 5)
			{
				$active_mailing_list_ids[]		= 'list_id_' .$list_count;
				$mailing_list_data['list_id']	= 'list_id_' .$list_count;
				$active_mailing_lists[]			= new Campaigner_mailing_list($mailing_list_data);
			}
		}
		
		// Expectations.
		$input->expectCallCount('post', 2);
		
		// Return values.
		$input->setReturnValue('post', $active_mailing_list_ids, array('active_mailing_lists'));
		$input->setReturnValue('post', $all_mailing_lists_data, array('all_mailing_lists'));
		
		// Run the tests.
		$test_mailing_lists = $this->_model->get_mailing_lists_from_input();
		$this->assertIsA($test_mailing_lists, 'Array');
		$this->assertIdentical(count($active_mailing_lists), count($test_mailing_lists));
		
		for ($list_count = 0; $list_count < count($active_mailing_lists); $list_count++)
		{
			$this->assertIsA($test_mailing_lists[$list_count], 'Campaigner_mailing_list');
			$this->assertIdentical($active_mailing_lists[$list_count], $test_mailing_lists[$list_count]);
		}
	}
	
	
	public function test_get_custom_fields_from_input_array__success()
	{
		// Dummy data.
		$custom_fields		= array();
		$custom_fields_data	= array();
		
		for ($field_count = 1; $field_count <= 5; $field_count++)
		{
			$custom_fields_data['cm_field_id_' .$field_count] = 'm_field_id_' .$field_count;
			
			$custom_fields[] = new Campaigner_custom_field(array(
				'id'		=> 'cm_field_id_' .$field_count,
				'field_id'	=> 'm_field_id_' .$field_count
			));
		}
		
		// Run the tests.
		$returned_data = $this->_model->get_custom_fields_from_input_array($custom_fields_data);
		
		$this->assertIsA($returned_data, 'Array');
		$this->assertIdentical(count($returned_data), count($custom_fields));
		$this->assertIdentical($returned_data, $custom_fields);
	}
	
	
	/**
	 * Testing for custom fields that have not been mapped to member fields.
	 */
	public function test_get_custom_fields_from_input_array__custom_field_not_mapped()
	{
		// Dummy data.
		$custom_fields		= array();
		$custom_fields_data	= array();
		
		for ($field_count = 1; $field_count <= 5; $field_count++)
		{
			$custom_fields_data['cm_field_id_' .$field_count] = '';
		}
		
		// Run the tests.
		$returned_data = $this->_model->get_custom_fields_from_input_array($custom_fields_data);
		
		$this->assertIsA($returned_data, 'Array');
		$this->assertIdentical($returned_data, $custom_fields);
	}
	
}


/* End of file		: test_campaigner_model.php */
/* File location	: third_party/campaigner/tests/test_campaigner_model.php */