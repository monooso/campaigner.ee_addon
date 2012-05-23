<?php

/**
 * Tests for the Campaigner model.
 *
 * @package     Campaigner
 * @author      Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright   Experience Internet
 */

require_once PATH_THIRD .'campaigner/models/campaigner_model.php';

class Test_campaigner_model extends Testee_unit_test_case {

  private $_api_connector;
  private $_namespace;
  private $_package_name;
  private $_package_version;
  private $_site_id;
  private $_subject;


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

    $this->_namespace       = 'test_namespace';
    $this->_package_name    = 'test_package_name';
    $this->_package_version = '10.1.0';

    // The site ID is referenced so frequently that we just mock it here.
    $this->_site_id = 123;
    $this->EE->config->returns('item', $this->_site_id, array('site_id'));

    // Create the test subject.
    $this->_subject = new Campaigner_model(
      $this->_package_name, $this->_package_version, $this->_namespace);
  }


  /* --------------------------------------------------------------
   * TEST METHODS
   * ------------------------------------------------------------ */

  public function test__activate_extension_settings_table__success()
  {
    // Shortcuts.
    $dbf    = $this->EE->dbforge;
    $loader = $this->EE->load;

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
    $this->_subject->activate_extension_settings_table();
  }


  public function test__activate_extension_mailing_lists_table__success()
  {
    // Shortcuts.
    $dbf    = $this->EE->dbforge;
    $loader = $this->EE->load;

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
    $dbf->expectCallCount('add_key', 2);
    $dbf->expectAt(0, 'add_key', array('list_id', TRUE));
    $dbf->expectAt(1, 'add_key', array('site_id', TRUE));
    $dbf->expectOnce('create_table', array('campaigner_mailing_lists'));
    $loader->expectOnce('dbforge', array());

    // Tests.
    $this->_subject->activate_extension_mailing_lists_table();
  }


  public function test__activate_extension_register_hooks__success()
  {
    // Shortcuts.
    $db = $this->EE->db;

    // Dummy data.
    $class      = ucfirst($this->_subject->get_extension_class());
    $version    = $this->_subject->get_package_version();

    $hooks = array(
      'cartthrob_on_authorize',
      'cp_members_member_create',
      'cp_members_validate_members',
      'member_member_register',
      'member_register_validate_members',
      'membrr_subscribe',
      'user_edit_end',
      'user_register_end',
      'zoo_visitor_cp_register_end',
      'zoo_visitor_cp_update_end',
      'zoo_visitor_register_end',
      'zoo_visitor_update_end'
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
      $hook_data['hook']    = $hooks[$count];
      $hook_data['method']  = 'on_' .$hooks[$count];

      $db->expectAt($count, 'insert', array('extensions', $hook_data));
    }

    // Tests.
    $this->_subject->activate_extension_register_hooks();
  }


  public function test__disable_extension()
  {
    $db = $this->EE->db;
    $dbf = $this->EE->dbforge;

    /**
     * - Delete the extension hooks.
     * - Drop the error log table.
     * - Drop the settings table.
     * - Drop the mailing lists table.
     */

    $db->expectOnce('delete', array('extensions', array('class' => $this->_subject->get_extension_class())));

    $dbf->expectCallCount('drop_table', 3);
    $dbf->expectAt(0, 'drop_table', array('campaigner_error_log'));
    $dbf->expectAt(1, 'drop_table', array('campaigner_settings'));
    $dbf->expectAt(2, 'drop_table', array('campaigner_mailing_lists'));

    $this->_subject->disable_extension();
  }


  public function test__get_all_mailing_lists__success()
  {
    $db       = $this->EE->db;
    $db_query = $this->_get_mock('db_query');

    // Custom fields.
    $custom_fields_data = array();
    $custom_fields      = array();

    for ($list_count = 0; $list_count < 10; $list_count++)
    {
      $data = array(
        'member_field_id' => 'm_field_id_' .$list_count,
        'cm_key'          => 'cm_key_' .$list_count
      );

      $custom_fields_data[] = $data;
      $custom_fields[]      = new Campaigner_custom_field($data);
    }

    $custom_fields_data = serialize($custom_fields_data);

    // Rows / mailing lists.
    $db_rows        = array();
    $mailing_lists  = array();

    for ($list_count = 0; $list_count < 10; $list_count++)
    {
      $db_rows[] = array(
        'site_id'       => $this->_site_id,
        'custom_fields' => $custom_fields_data,
        'list_id'       => 'list_id_' .$list_count,
        'trigger_field' => 'm_field_id_' .$list_count,
        'trigger_value' => 'trigger_value_' .$list_count
      );

      $mailing_lists[] = new Campaigner_mailing_list(array(
        'site_id'       => $this->_site_id,
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

    $db->expectOnce('get_where',
      array('campaigner_mailing_lists', array('site_id' => $this->_site_id)));

    $this->assertIdentical($mailing_lists,
      $this->_subject->get_all_mailing_lists());
  }


  public function test__get_all_mailing_lists__no_mailing_lists()
  {
      $db = $this->EE->db;
      $db_query = $this->_get_mock('db_query');

      // Retun values.
      $db_query->setReturnValue('result_array', array());
      $db->setReturnReference('get_where', $db_query);

      // Run the test.
      $this->assertIdentical(array(), $this->_subject->get_all_mailing_lists());
  }


  public function test__get_all_mailing_lists__no_custom_fields()
  {
      $db         = $this->EE->db;
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
      $this->assertIdentical($mailing_lists, $this->_subject->get_all_mailing_lists());
  }


  public function test__get_docs_url__success()
  {
    $pattern = '#^http://experienceinternet.co.uk/#';
    $this->assertPattern($pattern, $this->_subject->get_docs_url());
  }


  public function test__get_extension_class()
  {
    $this->assertEqual($this->_subject->get_extension_class(),
      $this->_package_name .'_ext');

    $this->assertNotEqual($this->_subject->get_extension_class(),
      $this->_package_name);
  }


  public function test__get_installed_extension_version__installed()
  {
      $db = $this->EE->db;

      // Dummy values.
      $criteria   = array('class' => $this->_subject->get_extension_class());
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
      $this->assertIdentical($version, $this->_subject->get_installed_extension_version());
  }


  public function test__get_installed_extension_version__not_installed()
  {
      $db = $this->EE->db;

      // Dummy values.
      $db_result  = $this->_get_mock('db_query');

      // Expectations.
      $db_result->expectNever('row');

      // Return values.
      $db->setReturnReference('select', $db);
      $db->setReturnReference('get_where', $db_result);
      $db_result->setReturnValue('num_rows', 0);

      // Tests.
      $this->assertIdentical('', $this->_subject->get_installed_extension_version());
  }


  /**
   * @TODO : proper tests for get_member_by_id.
   */

  public function test__get_member_by_id__returns_empty_array_if_passed_invalid_member_id()
  {
    $this->EE->db->expectNever('select');
    $this->EE->db->expectNever('where');
    $this->EE->db->expectNever('get');
    $this->EE->db->expectNever('get_where');

    $s = $this->_subject;
  
    $this->assertIdentical(array(), $s->get_member_by_id(0));
    $this->assertIdentical(array(), $s->get_member_by_id('Invalid'));
    $this->assertIdentical(array(), $s->get_member_by_id(NULL));
    $this->assertIdentical(array(), $s->get_member_by_id(array()));
    $this->assertIdentical(array(), $s->get_member_by_id(new StdClass()));
  }


  public function test__get_package_name()
  {
    $this->assertEqual($this->_package_name,
      $this->_subject->get_package_name());
  }


  public function test__get_package_version()
  {
    $this->assertIdentical($this->_package_version,
      $this->_subject->get_package_version());
  }


  public function test__get_settings_from_db__success()
  {
    $config = $this->EE->config;
    $db     = $this->EE->db;

    $api_key    = 'api_key';
    $client_id  = 'client_id';

    // Settings db row.
    $db_row = array(
      'site_id'   => $this->_site_id,
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

    $db->expectOnce('get_where',
      array('campaigner_settings', array('site_id' => $this->_site_id), 1));

    // Create the settings object.
    $settings = new Campaigner_settings($db_row);

    $this->assertIdentical($settings,
      $this->_subject->get_settings_from_db());
  }


  public function test__get_settings_from_db__no_settings()
  {
    $db = $this->EE->db;
    $db_query = $this->_get_mock('db_query');

    // Return values.
    $db_query->setReturnValue('num_rows', 0);
    $db->setReturnReference('get_where', $db_query);

    // Expectations.
    $db_query->expectNever('row_array');

    $this->assertIdentical(new Campaigner_settings(),
      $this->_subject->get_settings_from_db());
  }


  public function test__get_settings_from_db__empty_settings()
  {
    $config     = $this->EE->config;
    $db         = $this->EE->db;
    $api_key    = 'api_key';
    $client_id  = 'client_id';

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

    $db->expectOnce('get_where',
      array('campaigner_settings', array('site_id' => $this->_site_id), 1));

    // Create the settings object.
    $settings = new Campaigner_settings();

    $this->assertIdentical($settings, $this->_subject->get_settings_from_db());
  }


  public function test__get_site_id()
  {
    $this->assertIdentical($this->_site_id, $this->_subject->get_site_id());
  }


  public function test__get_support_url__success()
  {
    $pattern = '#^mailto\:[a-z_\-]+@experienceinternet\.co\.uk#';
    $this->assertPattern($pattern, $this->_subject->get_support_url());
  }


  public function test__get_theme_url__no_slash()
  {
    // Dummy values.
    $theme_url    = '/path/to/themes';
    $package_url  = $theme_url .'/third_party/'
      .strtolower($this->_subject->get_package_name()) .'/';

    $this->EE->config->setReturnValue('item', $theme_url,
      array('theme_folder_url'));

    $this->assertIdentical($package_url, $this->_subject->get_theme_url());
  }


  public function test__get_member_fields__custom_member__returns_an_array_of_trigger_fields()
  {
    $db_result = $this->_get_mock('db_query');
    $db_rows = array(
      (object) array(
        'm_field_id'          => '10',
        'm_field_label'       => 'Name',
        'm_field_list_items'  => '',
        'm_field_type'        => 'text'
      ),
      (object) array(
        'm_field_id'          => '20',
        'm_field_label'       => 'Address',
        'm_field_list_items'  => '',
        'm_field_type'        => 'textarea'
      ),
      (object) array(
        'm_field_id'          => '30',
        'm_field_label'       => 'Gender',
        'm_field_list_items'  => "Male\nFemale",
        'm_field_type'        => 'select'
      )
    );

    $this->EE->db->expectOnce('select',
      array('m_field_id, m_field_label, m_field_list_items, m_field_type'));

    $this->EE->db->expectOnce('get', array('member_fields'));
    $this->EE->db->returnsByReference('get', $db_result);

    $db_result->expectOnce('result');
    $db_result->returns('result', $db_rows);

    $expected_result = array(
      new Campaigner_trigger_field(array(
        'id'    => 'm_field_id_10',
        'label' => 'Name',
        'type'  => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'm_field_id_20',
        'label' => 'Address',
        'type'  => 'textarea'
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'm_field_id_30',
        'label'   => 'Gender',
        'options' => array(
          new Campaigner_trigger_field_option(array(
            'id'    => 'Male',
            'label' => 'Male'
          )),
          new Campaigner_trigger_field_option(array(
            'id'    => 'Female',
            'label' => 'Female'
          ))
        ),
        'type' => 'select'
      ))
    );

    $this->assertIdentical($expected_result,
      $this->_subject->get_member_fields__custom_member());
  }


  public function test__get_member_fields__custom_member__returns_empty_array_if_no_custom_member_fields_exist()
  {
    $db_result = $this->_get_mock('db_query');
    $db_rows = array();

    $this->EE->db->expectOnce('select');
    $this->EE->db->expectOnce('get');
    $this->EE->db->returnsByReference('get', $db_result);

    $db_result->expectOnce('result');
    $db_result->returns('result', $db_rows);

    $this->assertIdentical(array(),
      $this->_subject->get_member_fields__custom_member());
  }


  public function test__get_member_fields__default_member__returns_an_array_of_trigger_fields()
  {
    $dummy_label = 'Label';
    $this->EE->lang->setReturnValue('line', $dummy_label);

    // Retrieve the member groups.
    $db_result = $this->_get_mock('db_query');
    $db_rows = array(
      (object) array('group_id' => '5', 'group_title' => 'Super Admins'),
      (object) array('group_id' => '10', 'group_title' => 'Authors'),
      (object) array('group_id' => '15', 'group_title' => 'Editors')
    );

    $this->EE->db->expectOnce('select', array('group_id, group_title'));
    $this->EE->db->expectOnce('get', array('member_groups'));
    $this->EE->db->returnsByReference('get', $db_result);

    $db_result->returns('result', $db_rows);

    $expected_result = array(
      new Campaigner_trigger_field(array(
        'id'      => 'group_id',
        'label'   => $dummy_label,
        'options' => array(
          new Campaigner_trigger_field_option(array(
            'id'    => '5',
            'label' => 'Super Admins'
          )),
          new Campaigner_trigger_field_option(array(
            'id'    => '10',
            'label' => 'Authors'
          )),
          new Campaigner_trigger_field_option(array(
            'id'    => '15',
            'label' => 'Editors'
          ))
        ),
        'type' => 'select'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'email',
        'label' => $dummy_label,
        'type'  => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'location',
        'label' => $dummy_label,
        'type'  => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'occupation',
        'label' => $dummy_label,
        'type'  => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'screen_name',
        'label' => $dummy_label,
        'type'  => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'url',
        'label' => $dummy_label,
        'type'  => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'    => 'username',
        'label' => $dummy_label,
        'type'  => 'text'
      ))
    );

    $this->assertIdentical($expected_result,
      $this->_subject->get_member_fields__default_member());
  }


  public function test__get_member_fields__zoo_visitor__returns_an_empty_array_if_zoo_visitor_not_installed()
  {
    // Set the cache.
    $this->EE->session->cache[$this->_namespace][$this->_package_name]
      ['is_zoo_visitor_installed'][$this->_site_id] = FALSE;

    $this->EE->db->expectNever('get_where');
  
    $this->assertIdentical(array(),
      $this->_subject->get_member_fields__zoo_visitor());
  }


  public function test__get_member_fields__zoo_visitor__returns_an_array_of_campaigner_trigger_fields()
  {
    // Set the cache.
    $this->EE->session->cache[$this->_namespace][$this->_package_name]
      [$this->_site_id]['is_zoo_visitor_installed'] = TRUE;

    $fields = array(
      'channel_fields.field_id',
      'channel_fields.field_label',
      'channel_fields.field_list_items',
      'channel_fields.field_name',
      'channel_fields.field_type'
    );

    $this->EE->db->expectOnce('select', array(implode(', ', $fields)));
    $this->EE->db->expectOnce('from', array('channel_fields'));

    $this->EE->db->expectCallCount('join', 2);

    $this->EE->db->expectAt(0, 'join', array('channels',
      'channels.field_group = channel_fields.group_id', 'inner'));

    $this->EE->db->expectAt(1, 'join', array('zoo_visitor_settings',
      'zoo_visitor_settings.var_value = channels.channel_id', 'inner'));

    $this->EE->db->expectCallCount('where', 3);

    $this->EE->db->expectAt(0, 'where',
      array('zoo_visitor_settings.site_id', $this->_site_id));

    $this->EE->db->expectAt(1, 'where',
      array('zoo_visitor_settings.var', 'member_channel_id'));

    $this->EE->db->expectAt(2, 'where',
      array('channel_fields.field_type !=', 'zoo_visitor'));

    $this->EE->db->expectOnce('get');

    // Query result.
    $db_result = $this->_get_mock('db_query');

    $db_rows = array(
      (object) array(
        'field_id'          => '10',
        'field_label'       => 'Date of Birth',
        'field_list_items'  => '',
        'field_name'        => 'dob',
        'field_type'        => 'date'
      ),
      (object) array(
        'field_id'          => '20',
        'field_label'       => 'City',
        'field_list_items'  => '',
        'field_name'        => 'city',
        'field_type'        => 'text'
      ),
      (object) array(
        'field_id'          => '30',
        'field_label'       => 'Country',
        'field_list_items'  => "Canada\nUSA",
        'field_name'        => 'country',
        'field_type'        => 'select'
      )
    );

    $this->EE->db->returnsByReference('get', $db_result);
    $db_result->returns('num_rows', count($db_rows));
    $db_result->returns('result', $db_rows);

    $expected_result = array(
      new Campaigner_trigger_field(array(
        'id'      => 'field_id_' .$db_rows[0]->field_id,
        'label'   => $db_rows[0]->field_label,
        'options' => array(),
        'type'    => $db_rows[0]->field_type
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'field_id_' .$db_rows[1]->field_id,
        'label'   => $db_rows[1]->field_label,
        'options' => array(),
        'type'    => $db_rows[1]->field_type
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'field_id_' .$db_rows[2]->field_id,
        'label'   => $db_rows[2]->field_label,
        'options' => array(
          new Campaigner_trigger_field_option(array(
            'id' => 'Canada', 'label' => 'Canada')),
          new Campaigner_trigger_field_option(array(
            'id' => 'USA', 'label' => 'USA'))
        ),
        'type'    => $db_rows[2]->field_type
      ))
    );

    // Run the tests.
    $this->assertIdentical($expected_result,
      $this->_subject->get_member_fields__zoo_visitor());
  }


  public function test__get_member_fields__zoo_visitor__returns_an_empty_array_if_no_member_fields_are_found()
  {
    // Set the cache.
    $this->EE->session->cache[$this->_namespace][$this->_package_name]
      [$this->_site_id]['is_zoo_visitor_installed'] = TRUE;

    $this->EE->db->expectOnce('select');
    $this->EE->db->expectOnce('from');
    $this->EE->db->expectCallCount('join', 2);
    $this->EE->db->expectCallCount('where', 3);
    $this->EE->db->expectOnce('get');

    // Query result.
    $db_result = $this->_get_mock('db_query');

    $this->EE->db->returnsByReference('get', $db_result);
    $db_result->returns('num_rows', 0);

    // Run the tests.
    $this->assertIdentical(array(),
      $this->_subject->get_member_fields__zoo_visitor());
  }


  public function test__is_zoo_visitor_installed__not_installed()
  {
    // Is the Zoo Visitor module installed?
    $this->EE->db->expectOnce('where',
      array('LOWER(module_name)', 'zoo_visitor'));

    $this->EE->db->expectOnce('count_all_results', array('modules'));
    $this->EE->db->returns('count_all_results', 0);

    $this->EE->db->expectNever('table_exists');
  
    $this->assertIdentical(FALSE, $this->_subject->is_zoo_visitor_installed());
  }


  public function test__is_zoo_visitor_installed__caches_result()
  {
    $cache =& $this->EE->session->cache[$this->_namespace][$this->_package_name]
      [$this->_site_id];

    // The cached value should not exist at this point.
    $this->assertIdentical(FALSE,
      array_key_exists('is_zoo_visitor_installed', $cache));

    $this->EE->db->expectOnce('where');
    $this->EE->db->expectOnce('count_all_results', array('modules'));
    $this->EE->db->returns('count_all_results', 0);

    $this->assertIdentical(FALSE, $this->_subject->is_zoo_visitor_installed());

    // The cached value should now be set.
    $this->assertIdentical(TRUE,
      array_key_exists('is_zoo_visitor_installed', $cache));

    $this->assertIdentical(FALSE, $cache['is_zoo_visitor_installed']);
  }


  public function test__is_zoo_visitor_installed__uses_cached_result()
  {
    // Set the cache.
    $this->EE->session->cache[$this->_namespace][$this->_package_name]
      [$this->_site_id]['is_zoo_visitor_installed'] = TRUE;

    // The method should just use the cached value.
    $this->EE->db->expectNever('where');
    $this->EE->db->expectNever('count_all_results');
    $this->EE->db->expectNever('table_exists');

    $this->assertIdentical(TRUE, $this->_subject->is_zoo_visitor_installed());
  }


  public function test__is_zoo_visitor_installed__installed_in_modules_table_but_zoo_visitor_settings_table_does_not_exist()
  {
    // Is the Zoo Visitor module installed?
    $this->EE->db->expectOnce('where',
      array('LOWER(module_name)', 'zoo_visitor'));

    $this->EE->db->expectOnce('count_all_results', array('modules'));
    $this->EE->db->returns('count_all_results', 1);

    // Does the Zoo Visitor settings table exist?
    $this->EE->db->expectOnce('table_exists', array('zoo_visitor_settings'));
    $this->EE->db->returns('table_exists', FALSE);
  
    $this->assertIdentical(FALSE, $this->_subject->is_zoo_visitor_installed());
  }


  public function test__is_zoo_visitor_installed__installed_but_not_configured()
  {
    $this->EE->db->expectCallCount('where', 4);
    $this->EE->db->expectCallCount('count_all_results', 2);

    // Is the Zoo Visitor module installed?
    $this->EE->db->expectAt(0, 'where',
      array('LOWER(module_name)', 'zoo_visitor'));

    $this->EE->db->expectAt(0, 'count_all_results', array('modules'));
    $this->EE->db->returnsAt(0, 'count_all_results', 1);

    // Does the Zoo Visitor settings table exist?
    $this->EE->db->expectOnce('table_exists', array('zoo_visitor_settings'));
    $this->EE->db->returns('table_exists', TRUE);

    // Is Zoo Visitor configured?
    $this->EE->db->expectAt(1, 'where', array('site_id', $this->_site_id));
    $this->EE->db->expectAt(2, 'where', array('var', 'member_channel_id'));
    $this->EE->db->expectAt(3, 'where', array('var_value !=', ''));

    $this->EE->db->expectAt(1, 'count_all_results',
      array('zoo_visitor_settings'));

    $this->EE->db->returnsAt(1, 'count_all_results', 0);

    $this->assertIdentical(FALSE, $this->_subject->is_zoo_visitor_installed());
  }


  public function test__is_zoo_visitor_installed__installed_and_configured()
  {
    $this->EE->db->expectCallCount('where', 4);
    $this->EE->db->expectCallCount('count_all_results', 2);

    // Is the Zoo Visitor module installed?
    $this->EE->db->expectAt(0, 'where',
      array('LOWER(module_name)', 'zoo_visitor'));

    $this->EE->db->expectAt(0, 'count_all_results', array('modules'));
    $this->EE->db->returnsAt(0, 'count_all_results', 1);

    // Does the Zoo Visitor settings table exist?
    $this->EE->db->expectOnce('table_exists', array('zoo_visitor_settings'));
    $this->EE->db->returns('table_exists', TRUE);

    // Is Zoo Visitor configured?
    $this->EE->db->expectAt(1, 'where', array('site_id', $this->_site_id));
    $this->EE->db->expectAt(2, 'where', array('var', 'member_channel_id'));
    $this->EE->db->expectAt(3, 'where', array('var_value !=', ''));

    $this->EE->db->expectAt(1, 'count_all_results',
      array('zoo_visitor_settings'));

    $this->EE->db->returnsAt(1, 'count_all_results', 1);

    $this->assertIdentical(TRUE, $this->_subject->is_zoo_visitor_installed());
  }


  public function test__update_extension__update()
  {
    $db = $this->EE->db;

    $class              = ucfirst($this->_subject->get_extension_class());
    $installed_version  = '4.0.0';
    $package_version    = '4.1.0';

    // Update the extension version number in the database.
    $data = array('version' => $package_version);
    $criteria = array('class' => $class);

    $db->expectOnce('update', array('extensions', $data, $criteria));

    $this->assertIdentical(NULL, $this->_subject->update_extension($installed_version, $package_version));
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
      $this->assertIdentical(TRUE, $this->_subject->member_should_be_subscribed_to_mailing_list($member_data, $list));
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
    $this->assertIdentical(FALSE, $this->_subject->member_should_be_subscribed_to_mailing_list($member_data, $list));
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
    $this->assertIdentical(TRUE, $this->_subject->member_should_be_subscribed_to_mailing_list($member_data, $list));
  }


  public function test__update_extension__no_update()
  {
    $installed_version  = '1.0.0';
    $package_version    = '1.0.0';

    $this->assertIdentical(FALSE, $this->_subject->update_extension($installed_version, $package_version));
  }


  public function test__update_extension__not_installed()
  {
    $installed_version  = '';
    $package_version    = '1.0.0';

    $this->assertIdentical(FALSE, $this->_subject->update_extension($installed_version, $package_version));
  }


  public function test__update_extension__upgrade_to_version_4()
  {
    // Shortcuts.
    $db = $this->EE->db;

    // Dummy values.
    $class              = ucfirst($this->_subject->get_extension_class());
    $installed_version  = '3.0.0';
    $package_version    = '4.0.0';
    $criteria           = array('class' => $class);
    $data               = array('priority' => 5);

    $db->expectCallCount('update', 2);
    $db->expectAt(0, 'update', array('extensions', $data, $criteria));

    // Run the tests.
    $this->_subject->update_extension($installed_version, $package_version);
  }


  public function test__update_extension__upgrade_to_version_4_1()
  {
    // Shortcuts.
    $db = $this->EE->db;

    // Dummy values.
    $installed_version  = '4.0.0';
    $package_version    = '4.1.0';

    $sql_drop = 'ALTER TABLE exp_campaigner_mailing_lists DROP PRIMARY KEY';
    $sql_add = 'ALTER TABLE exp_campaigner_mailing_lists ADD PRIMARY KEY (list_id, site_id)';

    $db->expectCallCount('query', 2);
    $db->expectAt(0, 'query', array(new EqualWithoutWhitespaceExpectation($sql_drop)));
    $db->expectAt(1, 'query', array(new EqualWithoutWhitespaceExpectation($sql_add)));

    $this->_subject->update_extension($installed_version, $package_version);
  }


  public function test__update_extension__upgrade_to_version_4_2()
  {
    $db = $this->EE->db;

    $installed_version  = '4.1.0';
    $package_version    = '4.2.0';

    $hook_data = array(
      'class'     => ucfirst($this->_subject->get_extension_class()),
      'enabled'   => 'y',
      'hook'      => '',
      'method'    => '',
      'priority'  => 5,
      'settings'  => '',
      'version'   => $package_version
    );

    $hooks = array(
      'zoo_visitor_cp_register_end',
      'zoo_visitor_cp_update_end',
      'zoo_visitor_register_end',
      'zoo_visitor_update_end'
    );

    $call_count = 0;

    foreach ($hooks AS $hook)
    {
      $insert_data = array_merge(
        $hook_data,
        array(
          'hook'    => $hook,
          'method'  => 'on_' .$hook
        )
      );

      $this->EE->db->expectAt($call_count++, 'insert', array(
        'extensions',
        $insert_data
      ));
    }

    $this->_subject->update_extension($installed_version, $package_version);
  }


  public function test__update_extension__upgrade_to_version_4_4()
  {
    $db = $this->EE->db;

    $installed_version  = '4.3.0';
    $package_version    = '4.4.0';

    $hook_data = array(
      'class'     => ucfirst($this->_subject->get_extension_class()),
      'enabled'   => 'y',
      'hook'      => 'cartthrob_on_authorize',
      'method'    => 'on_cartthrob_on_authorize',
      'priority'  => 5,
      'settings'  => '',
      'version'   => $package_version
    );

    // Only testing the first 'insert' statement, as later updates may also run.
    $this->EE->db->expectAtLeastOnce('insert');
    $this->EE->db->expectAt(0, 'insert', array('extensions', $hook_data));

    $this->_subject->update_extension($installed_version, $package_version);
  }


  public function test__update_extension__upgrade_to_version_4_5()
  {
    $db = $this->EE->db;

    $installed_version  = '4.4.0';
    $package_version    = '4.5.0';

    $hook_data = array(
      'class'     => ucfirst($this->_subject->get_extension_class()),
      'enabled'   => 'y',
      'hook'      => 'membrr_subscribe',
      'method'    => 'on_membrr_subscribe',
      'priority'  => 5,
      'settings'  => '',
      'version'   => $package_version
    );

    $this->EE->db->expectOnce('insert', array('extensions', $hook_data));
    $this->_subject->update_extension($installed_version, $package_version);
  }


  public function test__save_settings_to_db__success()
  {
    $config = $this->EE->config;
    $db     = $this->EE->db;

    // Settings.
    $settings = new Campaigner_settings(array(
      'api_key'   => 'API key',
      'client_id' => 'Client ID'
    ));

    $settings_data = $settings->to_array();
    unset($settings_data['mailing_lists']);

    $settings_data = array_merge(array('site_id' => $this->_site_id),
      $settings_data);

    $db->setReturnValue('affected_rows', 1);

    $db->expectOnce('delete',
      array('campaigner_settings', array('site_id' => $this->_site_id)));

    $db->expectOnce('insert', array('campaigner_settings', $settings_data));

    $this->assertIdentical(TRUE,
      $this->_subject->save_settings_to_db($settings));
  }


  public function test__save_settings_to_db__failure()
  {
      $this->EE->db->setReturnValue('affected_rows', 0);
      $this->assertIdentical(FALSE, $this->_subject->save_settings_to_db(new Campaigner_settings()));
  }


  public function test__save_mailing_lists_to_db__success()
  {
    $config = $this->EE->config;
    $db     = $this->EE->db;

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
      'site_id'       => $this->_site_id,
      'trigger_field' => $mailing_list_a_data['trigger_field'],
      'trigger_value' => $mailing_list_a_data['trigger_value']
    );

    $insert_array_b = array(
      'custom_fields' => serialize(array($custom_field_data)),
      'list_id'       => $mailing_list_b_data['list_id'],
      'site_id'       => $this->_site_id,
      'trigger_field' => $mailing_list_b_data['trigger_field'],
      'trigger_value' => $mailing_list_b_data['trigger_value']
    );

    // Settings.
    $settings = new Campaigner_settings(
      array('mailing_lists' => $mailing_lists));

    $db->expectOnce('delete',
      array('campaigner_mailing_lists', array('site_id' => $this->_site_id)));

    $db->expectCallCount('insert', count($mailing_lists));

    $db->expectAt(0, 'insert',
      array('campaigner_mailing_lists', $insert_array_a));

    $db->expectAt(1, 'insert',
      array('campaigner_mailing_lists', $insert_array_b));

    $db->setReturnValue('affected_rows', 1);

    $this->assertIdentical(TRUE,
      $this->_subject->save_mailing_lists_to_db($settings));
  }


  public function test__save_mailing_lists_to_db__no_custom_fields()
  {
    $config = $this->EE->config;
    $db     = $this->EE->db;

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
      'site_id'       => $this->_site_id,
      'trigger_field' => $mailing_list_a_data['trigger_field'],
      'trigger_value' => $mailing_list_a_data['trigger_value']
    );

    // Settings.
    $settings = new Campaigner_settings(
      array('mailing_lists' => $mailing_lists));

    $db->expectAt(0, 'insert',
      array('campaigner_mailing_lists', $insert_array_a));

    $db->setReturnValue('affected_rows', 1);

    $this->assertIdentical(TRUE,
      $this->_subject->save_mailing_lists_to_db($settings));
  }


  public function test__save_mailing_lists_to_db__failure()
  {
      $config     = $this->EE->config;
      $db         = $this->EE->db;
      $site_id    = '10';

      // Settings.
      $settings = new Campaigner_settings(array('mailing_lists' => array(new Campaigner_mailing_list())));

      // Return values.
      $config->setReturnValue('item', $site_id, array('site_id'));
      $db->setReturnValue('affected_rows', 0);

      // Expectations.
      $db->expectCallCount('delete', 2, array('campaigner_mailing_lists', array('site_id' => $site_id)));

      // Run the test.
      $this->assertIdentical(FALSE, $this->_subject->save_mailing_lists_to_db($settings));
  }


  public function test__save_extension_settings__settings_error()
  {
      $db     = $this->EE->db;
      $lang   = $this->EE->lang;
      $error  = 'Settings not saved';

      // Return values.
      $db->setReturnValue('affected_rows', 0);
      $lang->setReturnValue('line', $error);

      // Run the test.
      try
      {
          $this->_subject->save_extension_settings(new Campaigner_settings());
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
      $db     = $this->EE->db;
      $config = $this->EE->config;
      $lang   = $this->EE->lang;
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
          $this->_subject->save_extension_settings($settings);
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
      $input      = $this->EE->input;
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
      $this->assertIdentical($new_settings, $this->_subject->update_basic_settings_from_input($old_settings));
  }


  public function test__update_basic_settings_from_input__invalid_input()
  {
      $input      = $this->EE->input;
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
      $this->assertIdentical($settings, $this->_subject->update_basic_settings_from_input(new Campaigner_settings()));
  }


  public function test__update_basic_settings_from_input__missing_input()
  {
      // Return values.
      $this->EE->input->setReturnValue('get_post', FALSE);

      // Settings.
      $settings = new Campaigner_settings(array('api_key' => 'old_api_key', 'client_id' => 'old_client_id'));

      // Run the test.
      $this->assertIdentical($settings, $this->_subject->update_basic_settings_from_input($settings));
  }


  public function test__update_mailing_list_settings_from_input__success()
  {
      // Shortcuts.
      $input = $this->EE->input;

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
      $updated_settings = $this->_subject->update_mailing_list_settings_from_input($settings);
      $this->assertIdentical($settings, $updated_settings);

      // Need to check the mailing lists separately. Bah.
      $updated_mailing_lists = $updated_settings->get_mailing_lists();
      $this->assertIdentical(count($mailing_lists), count($updated_mailing_lists));

      for ($count = 0; $count < count($mailing_lists); $count++)
      {
          $this->assertIdentical($mailing_lists[$count], $updated_mailing_lists[$count]);
      }
  }


  public function test__get_api_connector__success()
  {
      // Shortcuts.
      $config     = $this->EE->config;
      $db         = $this->EE->db;

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
      $connector = $this->_subject->get_api_connector($api_key, new Campaigner_model());
      $this->assertIsA($connector, 'Campaigner_api_connector');
  }


  public function test__get_api_connector__no_settings()
  {
      // Shortcuts.
      $config     = $this->EE->config;
      $db         = $this->EE->db;

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
      $this->assertIdentical(FALSE, $this->_subject->get_api_connector());
  }


  public function test__get_member_as_subscriber__fails_if_member_data_does_not_include_email()
  {
    $list = new Campaigner_mailing_list();

    $member_data = array(
      'activity'    => 'looking for',
      'email'       => '',
      'screen_name' => 'Jimmy Jazz'
    );
  
    $this->assertIdentical(FALSE,
      $this->_subject->get_member_as_subscriber($member_data, $list));
  }


  public function test__get_member_as_subscriber__fails_if_member_data_does_not_include_name()
  {
    $list = new Campaigner_mailing_list();

    $member_data = array(
      'activity'    => 'looking for',
      'email'       => 'jimmy@jazz.com',
      'screen_name' => ''
    );
  
    $this->assertIdentical(FALSE,
      $this->_subject->get_member_as_subscriber($member_data, $list));
  }


  public function test__get_member_as_subscriber__works_with_custom_fields()
  {
    $list = new Campaigner_mailing_list(array(
      'list_id'       => '123',
      'list_name'     => 'Example List',
      'trigger_field' => 'm_field_id_10',
      'trigger_value' => 'y'
    ));

    $list->add_custom_field(new Campaigner_custom_field(array(
      'cm_key'          => 'cm_key_12345',
      'label'           => 'Gender',
      'member_field_id' => 'm_field_id_10'
    )));

    $list->add_custom_field(new Campaigner_custom_field(array(
      'cm_key'          => 'cm_key_234567',
      'label'           => 'Favourite Colour',
      'member_field_id' => 'm_field_id_20'
    )));

    $list->add_custom_field(new Campaigner_custom_field(array(
      'cm_key'          => 'cm_key_345678',
      'label'           => 'Preferred Artist',
      'member_field_id' => 'field_id_30'
    )));

    // NOTE: the member data does not include m_field_id_10.
    $member_data = array(
      'activity'      => 'looking for',
      'email'         => 'jimmy@jazz.com',
      'screen_name'   => 'Jimmy Jazz',
      'm_field_id_20' => 'Green',
      'field_id_30'   => 'Bob Dylan'
    );

    $expected_result = new Campaigner_subscriber(array(
      'email' => $member_data['email'],
      'name'  => $member_data['screen_name']
    ));

    $expected_result->add_custom_data(new Campaigner_subscriber_custom_data(
      array('key' => 'cm_key_234567', 'value' => 'Green')));
  
    $expected_result->add_custom_data(new Campaigner_subscriber_custom_data(
      array('key' => 'cm_key_345678', 'value' => 'Bob Dylan')));
  
    $this->assertIdentical($expected_result,
      $this->_subject->get_member_as_subscriber($member_data, $list));
  }


  public function test__get_mailing_list_by_id__success()
  {
    $config = $this->EE->config;
    $db     = $this->EE->db;

    $db_result    = $this->_get_mock('db_query');
    $fields_data  = array();
    $fields       = array();
    $list_id      = 'abc123';

    for ($count = 1; $count <= 10; $count++)
    {
      $data = array(
        'member_field_id' => 'm_field_id_' .$count,
        'cm_key'          => 'cm_key_' .$count
      );

      $fields_data[]  = $data;
      $fields[]       = new Campaigner_custom_field($data);
    }

    $db_row = array(
      'custom_fields' => serialize($fields_data),
      'list_id'       => $list_id,
      'site_id'       => '1',
      'trigger_field' => 'm_field_id_10',
      'trigger_value' => 'y'
    );

    $list_object = new Campaigner_mailing_list(array(
      'custom_fields' => $fields,
      'list_id'       => $db_row['list_id'],
      'trigger_field' => $db_row['trigger_field'],
      'trigger_value' => $db_row['trigger_value']
    ));

    // Expectations.
    $db->expectOnce('select',
      array('custom_fields, list_id, site_id, trigger_field, trigger_value'));

    $db->expectOnce('get_where', array('campaigner_mailing_lists',
      array('list_id' => $list_id, 'site_id' => $this->_site_id), 1));

    $db_result->expectOnce('num_rows');
    $db_result->expectOnce('row_array');

    $db->setReturnReference('get_where', $db_result);
    $db_result->setReturnReference('row_array', $db_row);
    $db_result->setReturnValue('num_rows', 1);

    $this->assertIdentical($list_object, $this->_subject->get_mailing_list_by_id($list_id));
  }


  public function test__get_mailing_list_by_id__no_matching_list()
  {
    $config = $this->EE->config;
    $db     = $this->EE->db;

    $db_result  = $this->_get_mock('db_query');
    $list_id    = 'abc123';
    $db_row     = array();

    $db_result->expectOnce('num_rows');
    $db_result->expectNever('row_array');

    $db->setReturnReference('get_where', $db_result);
    $db_result->setReturnValue('num_rows', 0);

    $this->assertIdentical(FALSE, $this->_subject->get_mailing_list_by_id($list_id));
  }


  public function test__get_api_class_clients__success()
  {
    // Dummy values.
    $api_key    = 'API_KEY';
    $client_id  = 'abc123';

    // Method calls `get_extension_settings`, so we need to mock that. Boo.
    $db_lists         = $this->_get_mock('db_query');
    $db_settings      = $this->_get_mock('db_query');
    $db_settings_row  = array('api_key' => $api_key);

    $db_lists->setReturnValue('result_array', array());
    $db_settings->setReturnValue('num_rows', 1);
    $db_settings->setReturnValue('row_array', $db_settings_row);

    $this->EE->db->setReturnReference('get_where', $db_lists,
      array('campaigner_mailing_lists', '*'));

    $this->EE->db->setReturnReference('get_where', $db_settings,
      array('campaigner_settings', '*', '*'));

    $this->assertIdentical(new CS_REST_Clients($client_id, $api_key),
      $this->_subject->get_api_class_clients($client_id));
  }


  public function test__get_api_class_clients__no_settings()
  {
    $client_id = 'abc123';

    // Method calls `get_extension_settings`, so we need to mock that. Boo.
    $db_lists     = $this->_get_mock('db_query');
    $db_settings  = $this->_get_mock('db_query');

    $db_lists->setReturnValue('result_array', array());
    $db_settings->setReturnValue('num_rows', 0);

    $this->EE->db->setReturnReference('get_where', $db_lists,
      array('campaigner_mailing_lists', '*'));

    $this->EE->db->setReturnReference('get_where', $db_settings,
      array('campaigner_settings', '*', '*'));

    $this->assertIdentical(FALSE,
      $this->_subject->get_api_class_clients($client_id));
  }


  public function test__get_api_class_clients__invalid_client_id()
  {
      // Dummy values.
      $client_id  = '';

      // Method calls `get_extension_settings`, so we need to mock that. Boo.
      $this->EE->config->expectNever('item');
      $this->EE->db->expectNever('get_where');

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->get_api_class_clients($client_id));
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

      $this->EE->config->setReturnValue('item', $site_id, array('site_id'));
      $this->EE->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
      $this->EE->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

      // Run the tests.
      $this->assertIdentical(new CS_REST_General($api_key), $this->_subject->get_api_class_general());
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

      $this->EE->config->setReturnValue('item', $site_id, array('site_id'));
      $this->EE->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
      $this->EE->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->get_api_class_general());
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

      $this->EE->config->setReturnValue('item', $site_id, array('site_id'));
      $this->EE->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
      $this->EE->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

      // Run the tests.
      $this->assertIdentical(new CS_REST_Lists($list_id, $api_key), $this->_subject->get_api_class_lists($list_id));
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

      $this->EE->config->setReturnValue('item', $site_id, array('site_id'));
      $this->EE->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
      $this->EE->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->get_api_class_lists($list_id));
  }


  public function test__get_api_class_lists__invalid_list_id()
  {
      // Dummy values.
      $list_id    = '';

      // Method calls `get_extension_settings`, so we need to mock that. Boo.
      $this->EE->config->expectNever('item');
      $this->EE->db->expectNever('get_where');

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->get_api_class_lists($list_id));
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

      $this->EE->config->setReturnValue('item', $site_id, array('site_id'));
      $this->EE->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
      $this->EE->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

      // Run the tests.
      $this->assertIdentical(new CS_REST_Subscribers($list_id, $api_key), $this->_subject->get_api_class_subscribers($list_id));
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

      $this->EE->config->setReturnValue('item', $site_id, array('site_id'));
      $this->EE->db->setReturnReference('get_where', $db_lists, array('campaigner_mailing_lists', '*'));
      $this->EE->db->setReturnReference('get_where', $db_settings, array('campaigner_settings', '*', '*'));

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->get_api_class_subscribers($list_id));
  }


  public function test__get_api_class_subscribers__invalid_list_id()
  {
      // Dummy values.
      $list_id    = '';

      // Method calls `get_extension_settings`, so we need to mock that. Boo.
      $this->EE->config->expectNever('item');
      $this->EE->db->expectNever('get_where');

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->get_api_class_subscribers($list_id));
  }


}


/* End of file      : test_campaigner_model.php */
/* File location    : third_party/campaigner/tests/test_campaigner_model.php */
