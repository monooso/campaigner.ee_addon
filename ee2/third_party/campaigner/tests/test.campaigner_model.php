<?php

/**
 * Tests for the Campaigner model.
 *
 * @package     Campaigner
 * @author      Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright   Experience Internet
 */

require_once PATH_THIRD .'campaigner/models/campaigner_model' .EXT;

class Test_campaigner_model extends Testee_unit_test_case {
    
    private $_api_connector;
    private $_model;
    
    
    
    /* --------------------------------------------------------------
     * PUBLIC METHODS
     * ------------------------------------------------------------ */
    
    /**
     * Runs before each test.
     *
     * @access  public
     * @return  void
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
    
    
    public function test_activate_extension_settings_table__success()
    {
        // Shortcuts.
        $dbf    = $this->_ee->dbforge;
        $loader = $this->_ee->load;
        
        // Dummy data.
        $fields = array(
            'site_id' => array(
                'constraint'    => 5,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'api_key' => array(
                'constraint'    => 50,
                'type'          => 'varchar'
            ),
            'client_id' => array(
                'constraint'    => 50,
                'type'          => 'varchar'
            )
        );
        
        // Expectations.
        $dbf->expectOnce('add_field', array($fields));
        $dbf->expectOnce('add_key', array('site_id', TRUE));
        $dbf->expectOnce('create_table', array('campaigner_settings'));
        $loader->expectOnce('dbforge', array());
        
        // Tests.
        $this->_model->activate_extension_settings_table();
    }
    
    
    public function test_activate_extension_mailing_lists_table__success()
    {
        // Shortcuts.
        $dbf    = $this->_ee->dbforge;
        $loader = $this->_ee->load;
        
        // Dummy data.
        $fields = array(
            'list_id' => array(
                'constraint'    => 50,
                'type'          => 'varchar'
            ),
            'site_id' => array(
                'constraint'    => 5,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'custom_fields' => array(
                'type'          => 'text'
            ),
            'trigger_field' => array(
                'constraint'    => 50,
                'type'          => 'varchar'
            ),
            'trigger_value' => array(
                'constraint'    => 255,
                'type'          => 'varchar'
            )
        );
        
        // Expectations.
        $dbf->expectOnce('add_field', array($fields));
        $dbf->expectOnce('add_key', array('list_id', TRUE));
        $dbf->expectOnce('create_table', array('campaigner_mailing_lists'));
        $loader->expectOnce('dbforge', array());
        
        // Tests.
        $this->_model->activate_extension_mailing_lists_table();
    }
    
    
    public function test_activate_extension_error_log_table__success()
    {
        // Shortcuts.
        $dbf    = $this->_ee->dbforge;
        $loader = $this->_ee->load;
        
        // Dummy data.
        $fields = array(
            'error_log_id' => array(
                'auto_increment' => TRUE,
                'constraint'    => 10,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'site_id' => array(
                'constraint'    => 5,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'error_date' => array(
                'constraint'    => 10,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'error_code' => array(
                'constraint'    => 3,
                'type'          => 'int',
                'unsigned'      => TRUE
            ),
            'error_message' => array(
                'constraint'    => 255,
                'type'          => 'varchar'
            )
        );
        
        // Expectations.
        $dbf->expectOnce('add_field', array($fields));
        $dbf->expectOnce('add_key', array('error_log_id', TRUE));
        $dbf->expectOnce('create_table', array('campaigner_error_log'));
        $loader->expectOnce('dbforge', array());
        
        // Tests.
        $this->_model->activate_extension_error_log_table();
    }
    
    
    public function test_activate_extension_register_hooks__success()
    {
        // Shortcuts.
        $db = $this->_ee->db;
        
        // Dummy data.
        $class      = $this->_model->get_extension_class();
        $version    = $this->_model->get_package_version();
        
        $hooks = array(
            'cp_members_member_create',
            'cp_members_validate_members',
            'member_member_register',
            'member_register_validate_members',
            'user_edit_end',
            'user_register_end'
        );
        
        $hook_data = array(
            'class'     => $class,
            'enabled'   => 'y',
            'hook'      => '',
            'method'    => '',
            'priority'  => 5,
            'settings'  => '',
            'version'   => $version
        );
        
        // Expectations.
        $db->expectCallCount('insert', count($hooks));
        
        for ($count = 0; $count < count($hooks); $count++)
        {
            $hook_data['hook']      = $hooks[$count];
            $hook_data['method']    = 'on_' .$hooks[$count];
            
            $db->expectAt($count, 'insert', array('extensions', $hook_data));
        }
        
        // Tests.
        $this->_model->activate_extension_register_hooks();
    }
    
    
    public function test_disable_extension()
    {
        $db = $this->_ee->db;
        $dbf = $this->_ee->dbforge;
        
        /**
         * - Delete the extension hooks.
         * - Drop the error log table.
         * - Drop the settings table.
         * - Drop the mailing lists table.
         */
        
        $db->expectOnce('delete', array('extensions', array('class' => $this->_model->get_extension_class())));
        
        $dbf->expectCallCount('drop_table', 3);
        $dbf->expectAt(0, 'drop_table', array('campaigner_error_log'));
        $dbf->expectAt(1, 'drop_table', array('campaigner_settings'));
        $dbf->expectAt(2, 'drop_table', array('campaigner_mailing_lists'));
        
        $this->_model->disable_extension();
    }
    
    
    public function test__update_extension__update()
    {
        $db = $this->_ee->db;
        
        $installed_version  = '4.0.0';
        $package_version    = '4.1.0';
        
        // Update the extension version number in the database.
        $data = array('version' => $package_version);
        $criteria = array('class' => $this->_model->get_extension_class());
        
        $db->expectOnce('update', array('extensions', $data, $criteria));
        
        $this->assertIdentical(NULL, $this->_model->update_extension($installed_version, $package_version));
    }
    
    
    public function test__update_extension__no_update()
    {
        $installed_version  = '1.0.0';
        $package_version    = '1.0.0';
        
        $this->assertIdentical(FALSE, $this->_model->update_extension($installed_version, $package_version));
    }
    
    
    public function test__update_extension__not_installed()
    {
        $installed_version  = '';
        $package_version    = '1.0.0';
        
        $this->assertIdentical(FALSE, $this->_model->update_extension($installed_version, $package_version));
    }


    public function test__update_extension__upgrade_to_version_4()
    {
        // Shortcuts.
        $db = $this->_ee->db;

        // Dummy values.
        $installed_version  = '3.0.0';
        $package_version    = '4.0.0';
        $criteria           = array('class' => $this->_model->get_extension_class());
        $data               = array('priority' => 5);

        $db->expectCallCount('update', 2);
        $db->expectAt(0, 'update', array('extensions', $data, $criteria));

        // Run the tests.
        $this->_model->update_extension($installed_version, $package_version);
    }
    
    
    public function test__get_theme_url__no_slash()
    {
        // Dummy values.
        $theme_url      = '/path/to/themes';
        $package_url    = $theme_url .'/third_party/' .strtolower($this->_model->get_package_name()) .'/';
        
        // Expectations.
        $this->_ee->config->expectOnce('item', array('theme_folder_url'));
        
        // Return values.
        $this->_ee->config->setReturnValue('item', $theme_url, array('theme_folder_url'));
        
        // Tests.
        $this->assertIdentical($package_url, $this->_model->get_theme_url());
    }
    
    
    public function test__get_support_url__success()
    {
        $pattern = '#^http://support.experienceinternet.co.uk/#';
        $this->assertPattern($pattern, $this->_model->get_support_url());
    }
    
    
    public function test__get_docs_url__success()
    {
        $pattern = '#^http://experienceinternet.co.uk/#';
        $this->assertPattern($pattern, $this->_model->get_docs_url());
    }
    
    
    public function test__get_member_by_id__success()
    {
        // Shortcuts.
        $db = $this->_ee->db;
        
        // Dummy values.
        $member_id  = 10;
        $db_result  = $this->_get_mock('db_query');
        $db_row     = array(
            'email'         => 'billy.bob@chickslovehicks.com',
            'group_id'      => '8',
            'location'      => 'Hicksville',
            'member_id'     => '10',
            'occupation'    => 'Hick',
            'screen_name'   => 'Billy Bob',
            'url'           => 'http://example.com/',
            'username'      => 'Billy Bob',
            'm_field_id_1'  => 'No',
            'm_field_id_2'  => 'Yes'
        );
        
        // Expectations.
        $db->expectOnce('select', array('members.email, members.group_id, members.location, members.member_id, members.occupation, members.screen_name, members.url, members.username, member_data.*'));
        $db->expectOnce('join', array('member_data', 'member_data.member_id = members.member_id', 'inner'));
        $db->expectOnce('get_where', array('members', array('members.member_id' => $member_id), 1));
        
        $db_result->expectOnce('num_rows');
        $db_result->expectOnce('row_array');
        
        // Returns values.
        $db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', 1);
        $db_result->setReturnValue('row_array', $db_row);
        
        // Tests.
        $this->assertIdentical($db_row, $this->_model->get_member_by_id($member_id));
    }
    
    
    public function test__get_member_by_id__no_member()
    {
        // Dummy values.
        $db_result = $this->_get_mock('db_query');
        
        // Expectations.
        $db_result->expectNever('row_array');
        
        // Return values.
        $this->_ee->db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', 0);
        
        // Tests.
        $this->assertIdentical(array(), $this->_model->get_member_by_id(10));
    }
    
    
    public function test__get_member_by_id__invalid_member()
    {
        // Expectations.
        $this->_ee->db->expectNever('get_where');
        
        // Tests.
        $this->_model->get_member_by_id(NULL);
    }
    
    
    public function test__get_member_fields__success()
    {
        // Dummy values.
        $db_result  = $this->_get_mock('db_query');
        $db_rows    = array(
            array('m_field_id' => '10', 'm_field_label' => 'Name', 'm_field_list_items' => '', 'm_field_type' => 'text'),
            array('m_field_id' => '20', 'm_field_label' => 'Email', 'm_field_list_items' => '', 'm_field_type' => 'text'),
            array('m_field_id' => '30', 'm_field_label' => 'Address', 'm_field_list_items' => '', 'm_field_type' => 'textarea'),
            array('m_field_id' => '40', 'm_field_label' => 'Gender', 'm_field_list_items' => "Male\nFemale", 'm_field_type' => 'select')
        );
        
        $member_fields  = array();
        $dummy_label    = 'Label';
        
        $standard_member_fields = array(
            array('id' => 'group_id', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'location', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'occupation', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'screen_name', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'url', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'username', 'label' => $dummy_label, 'options' => array(), 'type' => 'text')
        );
        
        foreach ($standard_member_fields AS $member_field_data)
        {
            $member_fields[] = new EI_member_field($member_field_data);
        }
        
        foreach ($db_rows AS $db_row)
        {
            $member_field = new EI_member_field();
            $member_field->populate_from_db_array($db_row);
            
            $member_fields[] = $member_field;
        }
        
        // Expectations.
        $this->_ee->db->expectOnce('select');
        $this->_ee->db->expectOnce('get', array('member_fields'));
        $db_result->expectOnce('result_array');
        
        // Return values.
        $this->_ee->db->setReturnReference('get', $db_result);
        $this->_ee->lang->setReturnValue('line', $dummy_label);
        $db_result->setReturnValue('result_array', $db_rows);
        
        // Tests.
        $this->assertIdentical($member_fields, $this->_model->get_member_fields());
    }
    
    
    public function test__get_member_fields__no_custom_member_fields()
    {
        // Dummy values.
        $db_result      = $this->_get_mock('db_query');
        $db_rows        = array();
        $member_fields  = array();
        $dummy_label    = 'Label';

        $standard_member_fields = array(
            array('id' => 'group_id', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'location', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'occupation', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'screen_name', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'url', 'label' => $dummy_label, 'options' => array(), 'type' => 'text'),
            array('id' => 'username', 'label' => $dummy_label, 'options' => array(), 'type' => 'text')
        );

        foreach ($standard_member_fields AS $member_field_data)
        {
            $member_fields[] = new EI_member_field($member_field_data);
        }

        // Return values.
        $this->_ee->db->setReturnReference('get', $db_result);
        $this->_ee->lang->setReturnValue('line', $dummy_label);
        $db_result->setReturnValue('result_array', $db_rows);

        // Tests.
        $this->assertIdentical($member_fields, $this->_model->get_member_fields());
    }
    
    
    public function test__get_installed_extension_version__installed()
    {
        $db = $this->_ee->db;
        
        // Dummy values.
        $criteria   = array('class' => $this->_model->get_extension_class());
        $limit      = 1;
        $table      = 'extensions';
        $version    = '1.1.0';
        
        $db_result          = $this->_get_mock('db_query');
        $db_row             = new stdClass();
        $db_row->version    = $version;
        
        // Expectations.
        $db->expectOnce('select', array('version'));
        $db->expectOnce('get_where', array($table, $criteria, $limit));
        $db_result->expectOnce('num_rows');
        $db_result->expectOnce('row');
        
        // Return values.
        $db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', 1);
        $db_result->setReturnValue('row', $db_row);
        
        // Tests.
        $this->assertIdentical($version, $this->_model->get_installed_extension_version());
    }
    
    
    public function test__get_installed_extension_version__not_installed()
    {
        $db = $this->_ee->db;
        
        // Dummy values.
        $db_result  = $this->_get_mock('db_query');
        
        // Expectations.
        $db_result->expectNever('row');
        
        // Return values.
        $db->setReturnReference('select', $db);
        $db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', 0);
        
        // Tests.
        $this->assertIdentical('', $this->_model->get_installed_extension_version());
    }
    
    
    public function test__get_settings_from_db__success()
    {
        $config     = $this->_ee->config;
        $db         = $this->_ee->db;
        
        $site_id    = '10';
        $api_key    = 'api_key';
        $client_id  = 'client_id';
        
        // Return the site ID.
        $config->expectOnce('item', array('site_id'));
        $config->setReturnValue('item', $site_id, array('site_id'));
        
        // Settings db row.
        $db_row = array(
            'site_id'   => $site_id,
            'api_key'   => $api_key,
            'client_id' => $client_id
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
    
    
    public function test__get_settings_from_db__no_settings()
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


    public function test__get_settings_from_db__empty_settings()
    {
        $config     = $this->_ee->config;
        $db         = $this->_ee->db;
        
        $site_id    = '10';
        $api_key    = 'api_key';
        $client_id  = 'client_id';
        
        // Return the site ID.
        $config->expectOnce('item', array('site_id'));
        $config->setReturnValue('item', $site_id, array('site_id'));
        
        // Settings db row.
        $db_row     = array();
        $db_query   = $this->_get_mock('db_query');
        
        // Return values.
        $db_query->setReturnValue('num_rows', 1);
        $db_query->setReturnValue('row_array', $db_row);
        $db->setReturnReference('get_where', $db_query);
        
        // Expectations.
        $db_query->expectOnce('num_rows');
        $db_query->expectOnce('row_array');
        $db->expectOnce('get_where', array('campaigner_settings', array('site_id' => $site_id), 1));
        
        // Create the settings object.
        $settings = new Campaigner_settings();
        
        // Run the test.
        $this->assertIdentical($settings, $this->_model->get_settings_from_db());
    }
    
    
    public function test__get_all_mailing_lists__success()
    {
        $db         = $this->_ee->db;
        $db_query   = $this->_get_mock('db_query');
        $site_id    = '10';
        
        // Site ID.
        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        
        // Custom fields.
        $custom_fields_data = array();
        $custom_fields      = array();
        
        for ($list_count = 0; $list_count < 10; $list_count++)
        {
            $data = array('member_field_id' => 'm_field_id_' .$list_count, 'cm_key' => 'cm_key_' .$list_count);
            
            $custom_fields_data[]   = $data;
            $custom_fields[]        = new Campaigner_custom_field($data);
        }
        
        $custom_fields_data = serialize($custom_fields_data);
        
        // Rows / mailing lists.
        $db_rows        = array();
        $mailing_lists  = array();
        
        for ($list_count = 0; $list_count < 10; $list_count++)
        {
            $db_rows[] = array(
                'site_id'       => $site_id,
                'custom_fields' => $custom_fields_data,
                'list_id'       => 'list_id_' .$list_count,
                'trigger_field' => 'm_field_id_' .$list_count,
                'trigger_value' => 'trigger_value_' .$list_count
            );
            
            $mailing_lists[] = new Campaigner_mailing_list(array(
                'site_id'       => $site_id,
                'custom_fields' => $custom_fields,
                'list_id'       => 'list_id_' .$list_count,
                'trigger_field' => 'm_field_id_' .$list_count,
                'trigger_value' => 'trigger_value_' .$list_count
            ));
        }
        
        // Return values.
        $db_query->setReturnValue('num_rows', count($db_rows));
        $db_query->setReturnValue('result_array', $db_rows);
        $db->setReturnReference('get_where', $db_query);
        
        // Expectations.
        $db_query->expectOnce('result_array');
        $db->expectOnce('get_where', array('campaigner_mailing_lists', array('site_id' => $site_id)));
        
        // Run the test.
        $this->assertIdentical($mailing_lists, $this->_model->get_all_mailing_lists());
    }
    
    
    public function test__get_all_mailing_lists__no_mailing_lists()
    {
        $db = $this->_ee->db;
        $db_query = $this->_get_mock('db_query');
        
        // Retun values.
        $db_query->setReturnValue('result_array', array());
        $db->setReturnReference('get_where', $db_query);
        
        // Run the test.
        $this->assertIdentical(array(), $this->_model->get_all_mailing_lists());
    }
    
    
    public function test__get_all_mailing_lists__no_custom_fields()
    {
        $db         = $this->_ee->db;
        $db_query   = $this->_get_mock('db_query');
        $site_id    = '10';
        
        // Rows / mailing lists.
        $db_rows = array();
        $mailing_lists = array();
        
        for ($list_count = 0; $list_count < 10; $list_count++)
        {
            $data = array(
                'site_id'       => $site_id,
                'custom_fields' => NULL,
                'list_id'       => 'list_id_' .$list_count,
                'trigger_field' => 'm_field_id_' .$list_count,
                'trigger_value' => 'trigger_value_' .$list_count
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
        $this->assertIdentical($mailing_lists, $this->_model->get_all_mailing_lists());
    }
    
    
    public function test__save_settings_to_db__success()
    {
        $config     = $this->_ee->config;
        $db         = $this->_ee->db;
        $site_id    = '10';
        
        // Settings.
        $settings = new Campaigner_settings(array(
            'api_key'   => 'API key',
            'client_id' => 'Client ID'
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
    
    
    public function test__save_settings_to_db__failure()
    {
        $this->_ee->db->setReturnValue('affected_rows', 0);
        $this->assertIdentical(FALSE, $this->_model->save_settings_to_db(new Campaigner_settings()));
    }
    
    
    public function test__save_mailing_lists_to_db__success()
    {
        // Shortcuts.
        $config = $this->_ee->config;
        $db     = $this->_ee->db;
        
        // Dummy values.
        $site_id = '10';
        
        $custom_field_data = array(
            'cm_key'            => '[Gender]',
            'member_field_id'   => 'm_field_id_100'
        );
        
        $custom_fields = array(new Campaigner_custom_field($custom_field_data));
        
        $mailing_list_a_data = array(
            'custom_fields' => $custom_fields,
            'list_id'       => 'ABC123',
            'trigger_field' => 'm_field_id_10',
            'trigger_value' => 'Yes'
        );
        
        $mailing_list_b_data = array(
            'custom_fields' => $custom_fields,
            'list_id'       => 'XYZ987',
            'trigger_field' => 'm_field_id_20',
            'trigger_value' => 'Octopus'
        );
        
        // Mailing lists.
        $mailing_lists = array(
            new Campaigner_mailing_list($mailing_list_a_data),
            new Campaigner_mailing_list($mailing_list_b_data)
        );
        
        $insert_array_a = array(
            'custom_fields' => serialize(array($custom_field_data)),
            'list_id'       => $mailing_list_a_data['list_id'],
            'site_id'       => $site_id,
            'trigger_field' => $mailing_list_a_data['trigger_field'],
            'trigger_value' => $mailing_list_a_data['trigger_value']
        );
        
        $insert_array_b = array(
            'custom_fields' => serialize(array($custom_field_data)),
            'list_id'       => $mailing_list_b_data['list_id'],
            'site_id'       => $site_id,
            'trigger_field' => $mailing_list_b_data['trigger_field'],
            'trigger_value' => $mailing_list_b_data['trigger_value']
        );
        
        // Settings.
        $settings = new Campaigner_settings(array('mailing_lists' => $mailing_lists));
        
        
        // Expectations.
        $config->expectOnce('item', array('site_id'));
        $db->expectOnce('delete', array('campaigner_mailing_lists', array('site_id' => $site_id)));
        $db->expectCallCount('insert', count($mailing_lists));
        $db->expectAt(0, 'insert', array('campaigner_mailing_lists', $insert_array_a));
        $db->expectAt(1, 'insert', array('campaigner_mailing_lists', $insert_array_b));
        
        // Return values.
        $config->setReturnValue('item', $site_id, array('site_id'));
        $db->setReturnValue('affected_rows', 1);
        
        // Run the test.
        $this->assertIdentical(TRUE, $this->_model->save_mailing_lists_to_db($settings));
    }
    
    
    public function test__save_mailing_lists_to_db__no_custom_fields()
    {
        // Shortcuts.
        $config = $this->_ee->config;
        $db     = $this->_ee->db;
        
        // Dummy values.
        $site_id = '10';
        
        $mailing_list_a_data = array(
            'list_id'       => 'ABC123',
            'trigger_field' => 'm_field_id_10',
            'trigger_value' => 'Yes'
        );
        
        // Mailing lists.
        $mailing_lists = array(new Campaigner_mailing_list($mailing_list_a_data));
        
        $insert_array_a = array(
            'custom_fields' => serialize(array()),
            'list_id'       => $mailing_list_a_data['list_id'],
            'site_id'       => $site_id,
            'trigger_field' => $mailing_list_a_data['trigger_field'],
            'trigger_value' => $mailing_list_a_data['trigger_value']
        );
        
        // Settings.
        $settings = new Campaigner_settings(array('mailing_lists' => $mailing_lists));
        
        
        // Expectations.
        $db->expectAt(0, 'insert', array('campaigner_mailing_lists', $insert_array_a));
        
        // Return values.
        $config->setReturnValue('item', $site_id, array('site_id'));
        $db->setReturnValue('affected_rows', 1);
        
        // Run the test.
        $this->assertIdentical(TRUE, $this->_model->save_mailing_lists_to_db($settings));
    }
    
    
    public function test__save_mailing_lists_to_db__failure()
    {
        $config     = $this->_ee->config;
        $db         = $this->_ee->db;
        $site_id    = '10';
        
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
    
    
    public function test__save_extension_settings__settings_error()
    {
        $db     = $this->_ee->db;
        $lang   = $this->_ee->lang;
        $error  = 'Settings not saved';
        
        // Return values.
        $db->setReturnValue('affected_rows', 0);
        $lang->setReturnValue('line', $error);
        
        // Run the test.
        try
        {
            $this->_model->save_extension_settings(new Campaigner_settings());
            $this->fail();
        }
        catch (Campaigner_exception $e)
        {
            $e->getMessage() == $error
                ? $this->pass()
                : $this->fail();
        }
    }
    
    
    public function test__save_extension_settings__mailing_lists_error()
    {
        $db     = $this->_ee->db;
        $config = $this->_ee->config;
        $lang   = $this->_ee->lang;
        $error  = 'Mailing lists not saved';
        
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
        catch (Campaigner_exception $e)
        {
            $e->getMessage() == $error
                ? $this->pass()
                : $this->fail();
        }
    }

    
    public function test__update_basic_settings_from_input__success()
    {
        $input      = $this->_ee->input;
        $api_key    = 'API key';
        $client_id  = 'Client ID';
        
        // Return values.
        $input->setReturnValue('get_post', $api_key, array('api_key'));
        $input->setReturnValue('get_post', $client_id, array('client_id'));
        
        // Expectations
        $input->expectCallCount('get_post', 2);
        
        // Settings.
        $old_settings = new Campaigner_settings(array('api_key' => 'old_api_key'));
        $new_settings = new Campaigner_settings(array('api_key' => $api_key, 'client_id' => $client_id));
        
        // Run the test.
        $this->assertIdentical($new_settings, $this->_model->update_basic_settings_from_input($old_settings));
    }
    
    
    public function test__update_basic_settings_from_input__invalid_input()
    {
        $input      = $this->_ee->input;
        $api_key    = 'API key';
        $client_id  = 'Client ID';
        $invalid    = 'Wibble';
        
        // Return values.
        $input->setReturnValue('get_post', $api_key, array('api_key'));
        $input->setReturnValue('get_post', $client_id, array('client_id'));
        $input->setReturnValue('get_post', $invalid, array('invalid'));
        
        // Settings.
        $settings = new Campaigner_settings(array('api_key' => $api_key, 'client_id' => $client_id));
        
        // Run the test.
        $this->assertIdentical($settings, $this->_model->update_basic_settings_from_input(new Campaigner_settings()));
    }
    
    
    public function test__update_basic_settings_from_input__missing_input()
    {
        // Return values.
        $this->_ee->input->setReturnValue('get_post', FALSE);
        
        // Settings.
        $settings = new Campaigner_settings(array('api_key' => 'old_api_key', 'client_id' => 'old_client_id'));
        
        // Run the test.
        $this->assertIdentical($settings, $this->_model->update_basic_settings_from_input($settings));
    }
    
    
    public function test__update_mailing_list_settings_from_input__success()
    {
        // Shortcuts.
        $input = $this->_ee->input;
        
        // Dummy data.
        $cm_key         = '[CampaignMonitorKey]';
        $clean_cm_key   = sanitize_string($cm_key);
        
        $mailing_list_data = array(
            'mailing_list_id_1' => array(
                'checked'       => 'mailing_list_id_1',
                'trigger_field' => 'group_id',
                'trigger_value' => '10',
                'custom_fields' => array($clean_cm_key => 'm_field_id_1')
            ),
            'mailing_list_id_2' => array(
                'checked'       => 'mailing_list_id_2',
                'trigger_field' => 'location',
                'trigger_value' => 'Cardiff'
            ),
            'mailing_list_id_3' => array(
                'trigger_field' => '',
                'trigger_value' => '',
                'custom_fields' => array($clean_cm_key => '')
            ),
            'mailing_list_id_4' => array(
                'trigger_field' => '',
                'trigger_value' => '',
                'custom_fields' => array($clean_cm_key => '')
            )
        );
        
        $mailing_lists = array(
            new Campaigner_mailing_list(array(
                'list_id'       => 'mailing_list_id_1',
                'trigger_field' => 'group_id',
                'trigger_value' => '10',
                'custom_fields' => array(new Campaigner_custom_field(array('cm_key' => $cm_key, 'member_field_id' => 'm_field_id_1')))
            )),
            new Campaigner_mailing_list(array(
                'list_id'       => 'mailing_list_id_2',
                'trigger_field' => 'location',
                'trigger_value' => 'Cardiff'
            ))
        );
        
        $settings = new Campaigner_settings();
        $settings->set_mailing_lists($mailing_lists);
        
        // Expectations.
        $input->expectOnce('get_post', array('mailing_lists'));
        
        // Return values.
        $input->setReturnValue('get_post', $mailing_list_data, array('mailing_lists'));
        
        // Tests.
        $updated_settings = $this->_model->update_mailing_list_settings_from_input($settings);
        $this->assertIdentical($settings, $updated_settings);
        
        // Need to check the mailing lists separately. Bah.
        $updated_mailing_lists = $updated_settings->get_mailing_lists();
        $this->assertIdentical(count($mailing_lists), count($updated_mailing_lists));
        
        for ($count = 0; $count < count($mailing_lists); $count++)
        {
            $this->assertIdentical($mailing_lists[$count], $updated_mailing_lists[$count]);
        }
    }


    public function test_get_api_connector__success()
    {
        // Shortcuts.
        $config     = $this->_ee->config;
        $db         = $this->_ee->db;

        // Dummy values.
        $site_id        = '10';
        $api_key        = 'api_key';
        $client_id      = 'client_id';
        $db_lists       = $this->_get_mock('db_query');
        $db_settings    = $this->_get_mock('db_query');

        $db_settings_row = array(
            'site_id'   => $site_id,
            'api_key'   => $api_key,
            'client_id' => $client_id
        );

        /**
         * The `get_api_connector` method calls `get_extension_settings`,
         * so we need to mock the database return values.
         */
        
        // Return values.
        $config->setReturnValue('item', $site_id, array('site_id'));

        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 1);
        $db_settings->setReturnValue('row_array', $db_settings_row);

        $db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));

        $db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));
        
        // Run the test.
        $connector = $this->_model->get_api_connector($api_key, new Campaigner_model());
        $this->assertIsA($connector, 'Campaigner_api_connector');
    }


    public function test__get_api_connector__no_settings()
    {
        // Shortcuts.
        $config     = $this->_ee->config;
        $db         = $this->_ee->db;

        // Dummy values.
        $site_id        = '10';
        $db_lists       = $this->_get_mock('db_query');
        $db_settings    = $this->_get_mock('db_query');

        // Return values.
        $config->setReturnValue('item', $site_id, array('site_id'));

        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 0);

        $db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));

        $db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_connector());
    }


    public function test__get_member_as_subscriber__success()
    {
        // Shortcuts.
        $db = $this->_ee->db;

        // Dummy values.
        $list_id    = 'abcdefgh12345678';
        $member_id  = 20;
        $name       = 'John Doe';
        $email      = 'john@doe.com';

        /**
         * The method calls other methods for most of the heavy lifting.
         * Don't really want to be writing a massive test covering multiple
         * methods, so we do the bare minimum.
         *
         * For reference, the methods called are:
         * - get_member_by_id
         * - get_mailing_list_by_id
         */
        
        // get_member_by_id
        $member_result = $this->_get_mock('db_query');
        $member_row = array(
            'email'         => $email,
            'group_id'      => '8',
            'location'      => 'Hicksville',
            'member_id'     => '10',
            'occupation'    => 'Hick',
            'screen_name'   => $name,
            'url'           => 'http://example.com/',
            'username'      => $name,
            'm_field_id_1'  => 'Auburn',
            'm_field_id_10' => 'y'
        );

        $db->setReturnReferenceAt(0, 'get_where', $member_result);
        $member_result->setReturnValue('num_rows', 1);
        $member_result->setReturnValue('row_array', $member_row);

        // get_mailing_list_by_id
        $list_result = $this->_get_mock('db_query');
        $fields_data = array();

        for ($count = 1; $count <= 5; $count++)
        {
            $fields_data[] = array('member_field_id' => 'm_field_id_' .$count, 'cm_key' => 'cm_key_' .$count);
        }

        $list_row = array(
            'custom_fields'     => serialize($fields_data),
            'list_id'           => $list_id,
            'site_id'           => '1',
            'trigger_field'     => 'm_field_id_10',
            'trigger_value'     => 'y'
        );

        $db->setReturnReferenceAt(1, 'get_where', $list_result);
        $list_result->setReturnValue('num_rows', 1);
        $list_result->setReturnValue('row_array', $list_row);

        // Tests.
        $subscriber = new Campaigner_subscriber(array(
            'email'         => $email,
            'name'          => $name,
            'custom_data'   => array(
                new Campaigner_subscriber_custom_data(array(
                    'key'   => 'cm_key_1',
                    'value' => 'Auburn'
                ))
            )
        ));

        $this->assertIdentical($subscriber, $this->_model->get_member_as_subscriber($member_id, $list_id));
    }


    public function test__get_member_as_subscriber__trigger_does_not_match()
    {
        // Shortcuts.
        $db = $this->_ee->db;

        // Dummy values.
        $list_id    = 'abcdefgh12345678';
        $member_id  = 20;
        $name       = 'John Doe';
        $email      = 'john@doe.com';

        // get_member_by_id
        $member_result = $this->_get_mock('db_query');
        $member_row = array(
            'email'         => $email,
            'group_id'      => '8',
            'location'      => 'Hicksville',
            'member_id'     => '10',
            'occupation'    => 'Hick',
            'screen_name'   => $name,
            'url'           => 'http://example.com/',
            'username'      => $name,
            'm_field_id_1'  => 'Auburn',
            'm_field_id_10' => 'n'              // The non-matching trigger.
        );

        $db->setReturnReferenceAt(0, 'get_where', $member_result);
        $member_result->setReturnValue('num_rows', 1);
        $member_result->setReturnValue('row_array', $member_row);

        // get_mailing_list_by_id
        $list_result = $this->_get_mock('db_query');
        $fields_data = array();

        for ($count = 1; $count <= 5; $count++)
        {
            $fields_data[] = array('member_field_id' => 'm_field_id_' .$count, 'cm_key' => 'cm_key_' .$count);
        }

        $list_row = array(
            'custom_fields'     => serialize($fields_data),
            'list_id'           => $list_id,
            'site_id'           => '1',
            'trigger_field'     => 'm_field_id_10',
            'trigger_value'     => 'y'
        );

        $db->setReturnReferenceAt(1, 'get_where', $list_result);
        $list_result->setReturnValue('num_rows', 1);
        $list_result->setReturnValue('row_array', $list_row);

        // Tests.
        $this->assertIdentical(FALSE, $this->_model->get_member_as_subscriber($member_id, $list_id));
    }


    public function test__get_member_as_subscriber__unknown_member()
    {
        // Shortcuts.
        $db = $this->_ee->db;

        // Dummy values.
        $list_id    = 'abcdefgh12345678';
        $member_id  = 20;

        // get_member_by_id
        $member_result = $this->_get_mock('db_query');

        $db->setReturnReferenceAt(0, 'get_where', $member_result);
        $member_result->setReturnValue('num_rows', 0);

        // get_mailing_list_by_id
        $list_result = $this->_get_mock('db_query');
        $fields_data = array();

        for ($count = 1; $count <= 5; $count++)
        {
            $fields_data[] = array('member_field_id' => 'm_field_id_' .$count, 'cm_key' => 'cm_key_' .$count);
        }

        $list_row = array(
            'custom_fields'     => serialize($fields_data),
            'list_id'           => $list_id,
            'site_id'           => '1',
            'trigger_field'     => 'm_field_id_10',
            'trigger_value'     => 'y'
        );

        $db->setReturnReferenceAt(1, 'get_where', $list_result);
        $list_result->setReturnValue('num_rows', 1);
        $list_result->setReturnValue('row_array', $list_row);

        // Tests.
        $this->assertIdentical(FALSE, $this->_model->get_member_as_subscriber($member_id, $list_id));
    }


    public function test__get_member_as_subscriber__unknown_list()
    {
        // Shortcuts.
        $db = $this->_ee->db;

        // Dummy values.
        $email      = 'me@here.com';
        $list_id    = 'abcdefgh12345678';
        $member_id  = 20;
        $name       = 'Adam Adamson';

        // get_member_by_id
        $member_result = $this->_get_mock('db_query');
        $member_row = array(
            'email'         => $email,
            'group_id'      => '8',
            'location'      => 'Hicksville',
            'member_id'     => '10',
            'occupation'    => 'Hick',
            'screen_name'   => $name,
            'url'           => 'http://example.com/',
            'username'      => $name,
            'm_field_id_1'  => 'Auburn',
            'm_field_id_10' => 'y'
        );

        $db->setReturnReferenceAt(0, 'get_where', $member_result);
        $member_result->setReturnValue('num_rows', 1);
        $member_result->setReturnValue('row_array', $member_row);

        // get_mailing_list_by_id
        $list_result = $this->_get_mock('db_query');
        $db->setReturnReferenceAt(1, 'get_where', $list_result);
        $list_result->setReturnValue('num_rows', 0);

        // Tests.
        $this->assertIdentical(FALSE, $this->_model->get_member_as_subscriber($member_id, $list_id));
    }


    public function test__get_mailing_list_by_id__success()
    {
        // Shortcuts.
        $config = $this->_ee->config;
        $db     = $this->_ee->db;

        // Dummy values.
        $db_result      = $this->_get_mock('db_query');
        $fields_data    = array();
        $fields         = array();
        $list_id        = 'abc123';
        $site_id        = 10;

        for ($count = 1; $count <= 10; $count++)
        {
            $data           = array('member_field_id' => 'm_field_id_' .$count, 'cm_key' => 'cm_key_' .$count);
            $fields_data[]  = $data;
            $fields[]       = new Campaigner_custom_field($data);
        }

        $db_row = array(
            'custom_fields'     => serialize($fields_data),
            'list_id'           => $list_id,
            'site_id'           => '1',
            'trigger_field'     => 'm_field_id_10',
            'trigger_value'     => 'y'
        );

        $list_object = new Campaigner_mailing_list(array(
            'custom_fields'     => $fields,
            'list_id'           => $db_row['list_id'],
            'trigger_field'     => $db_row['trigger_field'],
            'trigger_value'     => $db_row['trigger_value']
        ));
    
        // Expectations.
        $db->expectOnce('select', array('custom_fields, list_id, site_id, trigger_field, trigger_value'));
        $db->expectOnce('get_where', array('campaigner_mailing_lists', array('list_id' => $list_id, 'site_id' => $site_id), 1));
        $db_result->expectOnce('num_rows');
        $db_result->expectOnce('row_array');

        // Return values.
        $config->setReturnValue('item', $site_id, array('site_id'));
        $db->setReturnReference('get_where', $db_result);
        $db_result->setReturnReference('row_array', $db_row);
        $db_result->setReturnValue('num_rows', 1);

        // Tests.
        $this->assertIdentical($list_object, $this->_model->get_mailing_list_by_id($list_id));
    }


    public function test__get_mailing_list_by_id__no_matching_list()
    {
        // Shortcuts.
        $config = $this->_ee->config;
        $db     = $this->_ee->db;

        // Dummy values.
        $db_result      = $this->_get_mock('db_query');
        $list_id        = 'abc123';
        $site_id        = 10;

        $db_row = array();
    
        // Expectations.
        $db_result->expectOnce('num_rows');
        $db_result->expectNever('row_array');

        // Return values.
        $config->setReturnValue('item', $site_id, array('site_id'));
        $db->setReturnReference('get_where', $db_result);
        $db_result->setReturnValue('num_rows', 0);

        // Tests.
        $this->assertIdentical(FALSE, $this->_model->get_mailing_list_by_id($list_id));
    }


    public function test__get_api_class_clients__success()
    {
        // Dummy values.
        $api_key    = 'API_KEY';
        $client_id  = 'abc123';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        $db_settings_row    = array('api_key' => $api_key);
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 1);
        $db_settings->setReturnValue('row_array', $db_settings_row);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(new CS_REST_Clients($client_id, $api_key), $this->_model->get_api_class_clients($client_id));
    }
    

    public function test__get_api_class_clients__no_settings()
    {
        // Dummy values.
        $client_id  = 'abc123';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 0);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_clients($client_id));
    }


    public function test__get_api_class_clients__invalid_client_id()
    {
        // Dummy values.
        $client_id  = '';

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $this->_ee->config->expectNever('item');
        $this->_ee->db->expectNever('get_where');

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_clients($client_id));
    }


    public function test__get_api_class_general__success()
    {
        // Dummy values.
        $api_key    = 'API_KEY';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        $db_settings_row    = array('api_key' => $api_key);
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 1);
        $db_settings->setReturnValue('row_array', $db_settings_row);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(new CS_REST_General($api_key), $this->_model->get_api_class_general());
    }
    

    public function test__get_api_class_general__not_settings()
    {
        // Dummy values.
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 0);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_general());
    }


    public function test__get_api_class_lists__success()
    {
        // Dummy values.
        $api_key    = 'API_KEY';
        $list_id    = 'abc123';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        $db_settings_row    = array('api_key' => $api_key);
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 1);
        $db_settings->setReturnValue('row_array', $db_settings_row);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(new CS_REST_Lists($list_id, $api_key), $this->_model->get_api_class_lists($list_id));
    }
    

    public function test__get_api_class_lists__no_settings()
    {
        // Dummy values.
        $list_id    = 'abc123';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 0);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_lists($list_id));
    }


    public function test__get_api_class_lists__invalid_list_id()
    {
        // Dummy values.
        $list_id    = '';

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $this->_ee->config->expectNever('item');
        $this->_ee->db->expectNever('get_where');

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_lists($list_id));
    }


    public function test__get_api_class_subscribers__success()
    {
        // Dummy values.
        $api_key    = 'API_KEY';
        $list_id    = 'abc123';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        $db_settings_row    = array('api_key' => $api_key);
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 1);
        $db_settings->setReturnValue('row_array', $db_settings_row);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(new CS_REST_Subscribers($list_id, $api_key), $this->_model->get_api_class_subscribers($list_id));
    }
    

    public function test__get_api_class_subscribers__no_settings()
    {
        // Dummy values.
        $list_id    = 'abc123';
        $site_id    = 10;

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $db_lists           = $this->_get_mock('db_query');
        $db_settings        = $this->_get_mock('db_query');
        
        $db_lists->setReturnValue('result_array', array());
        $db_settings->setReturnValue('num_rows', 0);

        $this->_ee->config->setReturnValue('item', $site_id, array('site_id'));
        $this->_ee->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
        $this->_ee->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_subscribers($list_id));
    }


    public function test__get_api_class_subscribers__invalid_list_id()
    {
        // Dummy values.
        $list_id    = '';

        // Method calls `get_extension_settings`, so we need to mock that. Boo.
        $this->_ee->config->expectNever('item');
        $this->_ee->db->expectNever('get_where');

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->get_api_class_subscribers($list_id));
    }


    public function test__member_should_be_subscribed_to_mailing_list__trigger_field_yes()
    {
        // Dummy values.
        $list = new Campaigner_mailing_list(array(
            'list_id'       => 'abc123',
            'list_name'     => 'List Name',
            'trigger_field' => 'm_field_id_10',
            'trigger_value' => 'y'
        ));

        $member_data = array(
            'member_id'     => 10,
            'm_field_id_10' => 'y',
            'm_field_id_20' => 'n'
        );

        // Run the tests.
        $this->assertIdentical(TRUE, $this->_model->member_should_be_subscribed_to_mailing_list($member_data, $list));
    }


    public function test__member_should_be_subscribed_to_mailing_list__trigger_field_no()
    {
        // Dummy values.
        $list = new Campaigner_mailing_list(array(
            'list_id'       => 'abc123',
            'list_name'     => 'List Name',
            'trigger_field' => 'm_field_id_10',
            'trigger_value' => 'y'
        ));

        $member_data = array(
            'member_id'     => 10,
            'm_field_id_10' => 'n',
            'm_field_id_20' => 'n'
        );

        // Run the tests.
        $this->assertIdentical(FALSE, $this->_model->member_should_be_subscribed_to_mailing_list($member_data, $list));
    }
    

    public function test__member_should_be_subscribed_to_mailing_list__no_trigger_field()
    {
        // Dummy values.
        $list = new Campaigner_mailing_list(array(
            'list_id'       => 'abc123',
            'list_name'     => 'List Name',
        ));

        $member_data = array('member_id' => 10);

        // Run the tests.
        $this->assertIdentical(TRUE, $this->_model->member_should_be_subscribed_to_mailing_list($member_data, $list));
    }

}


/* End of file      : test_campaigner_model.php */
/* File location    : third_party/campaigner/tests/test_campaigner_model.php */