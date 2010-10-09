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
				'contraint'			=> 20,
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
		 * - merge_variables
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
			'merge_variables' => array(
				'type'			=> 'text'
			),
			'trigger_field_id' => array(
				'constraint'	=> 4,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'trigger_field_value' => array(
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
		
		for ($count = 0; $count < count($hooks); $count++)
		{
			$data = array(
				'class'		=> $class,
				'enabled'	=> 'y',
				'hook'		=> $hooks[$count],
				'method'	=> 'on_' .$hooks[$count],
				'priority'	=> 10,
				'settings'	=> '',
				'version'	=> $version
			);
			
			$db->expectAt($count, 'insert', array('extensions', $data));
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
	
	
	public function test_get_extension_settings()
	{
		
	}
	
}


/* End of file		: Campaigner_mock_api.php */
/* File location	: third_party/campaigner/tests/mocks/campaigner_mock_api.php */