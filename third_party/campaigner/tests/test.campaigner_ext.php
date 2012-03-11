<?php

/**
 * Tests for the Campaigner extension.
 *
 * @package     Campaigner
 * @author      Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright   Experience Internet
 */

require_once PATH_THIRD .'campaigner/ext.campaigner.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_cm_api_connector.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber.php';
require_once PATH_THIRD .'campaigner/models/campaigner_model.php';

class Test_campaigner_ext extends Testee_unit_test_case {

  private $_installed_extension_version;
  private $_model;
  private $_package_version;
  private $_settings;
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


    // Generate the mock model.
    Mock::generate('Campaigner_model', get_class($this) .'_mock_model');
    $this->EE->campaigner_model = $this->_model = $this->_get_mock('model');

    // Generate the mock API connector.
    Mock::generate('Campaigner_cm_api_connector',
      get_class($this) .'_mock_connector');

    $this->_connector = $this->_get_mock('connector');

    $this->_settings = new Campaigner_settings(array(
      'api_key'   => 'API_KEY',
      'client_id' => 'CLIENT_ID'
    ));

    $this->_installed_version   = '1.0.0';
    $this->_package_version     = '1.0.0';

    // Called from the constructor.
    $this->_model->returnsByReference('get_api_connector', $this->_connector);

    $this->_model->returnsByReference('get_extension_settings',
      $this->_settings);

    $this->_model->returnsByReference('update_extension_settings_from_input',
      $this->_settings);

    $this->_model->returns('get_installed_extension_version',
      $this->_installed_version);

    $this->_model->returns('get_package_version', $this->_package_version);

    // Test subject.
    $this->_subject = new Campaigner_ext();
  }



  /* --------------------------------------------------------------
   * TEST METHODS
   * ------------------------------------------------------------ */

  public function test_activate_extension__success()
  {
    $this->_model->expectOnce('activate_extension');
    $this->_subject->activate_extension();
  }


  public function test_disable_extension__success()
  {
    $this->_model->expectOnce('disable_extension');
    $this->_subject->disable_extension();
  }


  public function test_save_settings__success()
  {
    $this->_model->expectOnce('save_extension_settings',
      array($this->_settings));

    $this->EE->session->expectOnce('set_flashdata',
      array('message_success', '*'));

    $this->_subject->save_settings();
  }


  public function test_save_settings__failure()
  {
    $this->EE->session->expectOnce('set_flashdata',
      array('message_failure', '*'));

    $this->_model->throwOn('save_extension_settings',
      new Campaigner_exception('EXCEPTION'));

    $this->_subject->save_settings();
  }


  public function test_update_extension__update_required()
  {
    $installed_version = '1.1.0';

    $this->_model->expectOnce('update_extension',
      array($installed_version, $this->_package_version));

    $this->_model->returns('update_extension', TRUE);

    $this->assertIdentical(TRUE,
      $this->_subject->update_extension($installed_version));
  }


  public function test_update_extension__no_update_required()
  {
    $installed_version = '1.1.0';       // Can be anything.
    $this->_model->returns('update_extension', FALSE);

    $this->assertIdentical(FALSE,
      $this->_subject->update_extension($installed_version));
  }


  public function test__display_clients__success()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_clients', array('request'));

    $clients    = array();
    $view_vars  = array('clients' => $clients, 'settings' => $this->_settings);

    $this->_connector->expectOnce('get_clients');
    $this->_connector->returns('get_clients', $clients);

    $this->EE->load->expectOnce('view', array('_clients', $view_vars, TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_clients__exception()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_clients', array('request'));

    $exception = new Campaigner_exception('Invalid API key', 100);
    $view_vars = array(
        'error_code'    => $exception->getCode(),
        'error_message' => $exception->getMessage()
    );

    $this->_connector->expectOnce('get_clients');
    $this->_connector->throwOn('get_clients', $exception);

    $this->_model->expectOnce('log_error', array('*'));
    $this->EE->load->expectOnce('view', array('_error', $view_vars, TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_custom_fields__works_with_custom_campaign_monitor_fields_and_default_custom_and_zoo_member_fields()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_custom_fields', array('request'));

    $list_id = 'abc123';

    $fields = array(
      new Campaigner_custom_field(array(
        'cm_key'  => 'xyz987',
        'label'   => 'Age',
        'member_field_id' => 'm_field_id_10'
      )),
      new Campaigner_custom_field(array(
        'cm_key'  => 'klm666',
        'label'   => 'Occupation',
        'member_field_id' => 'm_field_id_20'
      ))
    );

    // Retrieve the AJAX-supplied list ID.
    $this->EE->input->expectOnce('get_post', array('list_id'));
    $this->EE->input->returns('get_post', $list_id, array('list_id'));

    // Retrieve the custom list fields from the API.
    $this->_connector->expectOnce('get_list_fields', array($list_id));
    $this->_connector->returns('get_list_fields', $fields);

    // @todo Test restoration of saved custom field settings.

    $lbl_custom_fields  = 'Custom Member Fields';
    $lbl_default_fields = 'Standard Member Fields';
    $lbl_zoo_fields     = 'Zoo Visitor Fields';

    $default_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'screen_name',
        'label' => 'Screen Name', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'location',
        'label' => 'Location', 'type' => 'text'))
    );

    $custom_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'm_field_id_10',
        'label' => 'Profile', 'type' => 'textarea')),
      new Campaigner_trigger_field(array('id' => 'm_field_id_20',
        'label' => 'Favourite Colour', 'type' => 'text'))
    );

    $zoo_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'field_id_10',
        'label' => 'Age', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'field_id_20',
        'label' => 'Gender', 'type' => 'radio'))
    );

    $dd_data = array(
      $lbl_default_fields => array(
        'screen_name' => 'Screen Name',
        'location'    => 'Location'
      ),
      $lbl_custom_fields => array(
        'm_field_id_10' => 'Profile',
        'm_field_id_20' => 'Favourite Colour'
      ),
      $lbl_zoo_fields => array(
        'field_id_10' => 'Age',
        'field_id_20' => 'Gender'
      )
    );

    $this->EE->lang->returns('line', $lbl_default_fields,
      array('lbl_default_member'));

    $this->EE->lang->returns('line', $lbl_custom_fields,
      array('lbl_custom_member'));

    $this->EE->lang->returns('line', $lbl_zoo_fields,
      array('lbl_zoo_visitor'));

    $this->_model->expectOnce('get_member_fields__default_member');
    $this->_model->returns('get_member_fields__default_member',
      $default_member_fields);

    $this->_model->expectOnce('get_member_fields__custom_member');
    $this->_model->returns('get_member_fields__custom_member',
      $custom_member_fields);

    $this->_model->expectOnce('get_member_fields__zoo_visitor');
    $this->_model->returns('get_member_fields__zoo_visitor',
      $zoo_member_fields);

    // Load the view.
    $this->EE->load->expectOnce('view', array(
      '_custom_fields',
      array(
        'custom_fields' => $fields,
        'list_id'       => $list_id,
        'member_fields' => array_merge($default_member_fields,
          $custom_member_fields, $zoo_member_fields),

        'member_fields_dd_data' => $dd_data,
      ),
      TRUE
    ));

    $this->_subject->display_settings();
  }


  public function test__display_custom_fields__works_with_custom_campaign_monitor_fields_and_only_default_member_fields()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_custom_fields', array('request'));

    $list_id = 'abc123';

    $fields = array(
      new Campaigner_custom_field(array(
        'cm_key'  => 'xyz987',
        'label'   => 'Age',
        'member_field_id' => 'm_field_id_10'
      )),
      new Campaigner_custom_field(array(
        'cm_key'  => 'klm666',
        'label'   => 'Occupation',
        'member_field_id' => 'm_field_id_20'
      ))
    );

    // Retrieve the AJAX-supplied list ID.
    $this->EE->input->expectOnce('get_post', array('list_id'));
    $this->EE->input->returns('get_post', $list_id, array('list_id'));

    // Retrieve the custom list fields from the API.
    $this->_connector->expectOnce('get_list_fields', array($list_id));
    $this->_connector->returns('get_list_fields', $fields);

    $lbl_default_fields = 'Standard Member Fields';

    $default_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'screen_name',
        'label' => 'Screen Name', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'location',
        'label' => 'Location', 'type' => 'text'))
    );

    $dd_data = array(
      $lbl_default_fields => array(
        'screen_name' => 'Screen Name',
        'location'    => 'Location'
      )
    );

    $this->EE->lang->returns('line', $lbl_default_fields,
      array('lbl_default_member'));

    $this->_model->expectOnce('get_member_fields__default_member');
    $this->_model->returns('get_member_fields__default_member',
      $default_member_fields);

    $this->_model->expectOnce('get_member_fields__custom_member');
    $this->_model->returns('get_member_fields__custom_member', array());

    $this->_model->expectOnce('get_member_fields__zoo_visitor');
    $this->_model->returns('get_member_fields__zoo_visitor', array());

    // Load the view.
    $this->EE->load->expectOnce('view', array(
      '_custom_fields',
      array(
        'custom_fields'         => $fields,
        'list_id'               => $list_id,
        'member_fields'         => $default_member_fields,
        'member_fields_dd_data' => $dd_data,
      ),
      TRUE
    ));

    $this->_subject->display_settings();
  }


  public function test__display_custom_fields__works_with_no_custom_fields()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_custom_fields', array('request'));

    $list_id  = 'abc123';
    $fields   = array();

    // Retrieve the AJAX-supplied list ID.
    $this->EE->input->expectOnce('get_post', array('list_id'));
    $this->EE->input->returns('get_post', $list_id, array('list_id'));

    // Retrieve the custom list fields from the API.
    $this->_connector->expectOnce('get_list_fields', array($list_id));
    $this->_connector->returns('get_list_fields', $fields);

    // Retrieve the member fields. Don't really care about this.
    $this->_model->expectNever('get_member_fields__custom_member');
    $this->_model->expectNever('get_member_fields__default_member');
    $this->_model->expectNever('get_member_fields__zoo_visitor');

    // Load the view.
    $this->EE->load->expectOnce('view', array(
      '_custom_fields',
      array(
        'custom_fields' => $fields,
        'list_id'       => $list_id,
        'member_fields' => array(),
        'member_fields_dd_data' => array()
      ),
      TRUE
    ));

    $this->_subject->display_settings();
  }


  public function test__display_custom_fields__handles_missing_list_id()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_custom_fields', array('request'));

    $fields = array();

    // Retrieve the AJAX-supplied list ID.
    $this->EE->input->expectOnce('get_post', array('list_id'));
    $this->EE->input->returns('get_post', FALSE, array('list_id'));

    // Should never get this far.
    $this->_connector->expectNever('get_list_fields');
    $this->_model->expectNever('get_member_fields__custom_member');
    $this->_model->expectNever('get_member_fields__default_member');
    $this->_model->expectNever('get_member_fields__zoo_visitor');

    // Log the error, and display the error view.
    $error_message = 'Oh noes!';
    $this->EE->lang->returns('line', $error_message);

    /**
     * NOTE:
     * We can't test with a Campaigner_exception argument, as the test fails
     * due to the exception being created in a different file.
     */

    $this->_model->expectOnce('log_error');

    $this->EE->load->expectOnce('view',
      array('_custom_fields_error', array(), TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_custom_fields__handles_api_exception()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_custom_fields', array('request'));

    $list_id  = 'abc123';
    $fields   = array();

    // Retrieve the AJAX-supplied list ID.
    $this->EE->input->expectOnce('get_post', array('list_id'));
    $this->EE->input->returns('get_post', $list_id, array('list_id'));

    // Retrieve the custom list fields from the API.
    $api_exception = new Campaigner_exception('Oh noes!', 666);

    $this->_connector->expectOnce('get_list_fields', array($list_id));
    $this->_connector->throwOn('get_list_fields', $api_exception);

    // Should never get this far.
    $this->_model->expectNever('get_member_fields__custom_member');
    $this->_model->expectNever('get_member_fields__default_member');
    $this->_model->expectNever('get_member_fields__zoo_visitor');

    /**
     * NOTE:
     * Testing that the $api_exception object is supplied as the log_error
     * argument causing SimpleTest to enter into an infinite recursive loop.
     * Nice.
     */

    $this->_model->expectOnce('log_error');

    $this->EE->load->expectOnce('view',
      array('_custom_fields_error', array(), TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_mailing_lists__works_with_default_custom_and_zoo_visitor_member_fields()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_mailing_lists', array('request'));

    // Dummy values.
    $lists = array();

    $lbl_custom_fields  = 'Custom Member Fields';
    $lbl_default_fields = 'Standard Member Fields';
    $lbl_zoo_fields     = 'Zoo Visitor Fields';

    $default_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'screen_name',
        'label' => 'Screen Name', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'location',
        'label' => 'Location', 'type' => 'text'))
    );

    $custom_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'm_field_id_10',
        'label' => 'Profile', 'type' => 'textarea')),
      new Campaigner_trigger_field(array('id' => 'm_field_id_20',
        'label' => 'Favourite Colour', 'type' => 'text'))
    );

    $zoo_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'field_id_10',
        'label' => 'Age', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'field_id_20',
        'label' => 'Gender', 'type' => 'radio'))
    );

    $dd_data = array(
      $lbl_default_fields => array(
        'screen_name' => 'Screen Name',
        'location'    => 'Location'
      ),
      $lbl_custom_fields => array(
        'm_field_id_10' => 'Profile',
        'm_field_id_20' => 'Favourite Colour'
      ),
      $lbl_zoo_fields => array(
        'field_id_10' => 'Age',
        'field_id_20' => 'Gender'
      )
    );

    $view_vars = array(
      'mailing_lists' => $lists,
      'member_fields' => array_merge($default_member_fields,
        $custom_member_fields, $zoo_member_fields),

      'member_fields_dd_data' => $dd_data,
      'settings'              => $this->_settings
    );

    $this->_connector->expectOnce('get_client_lists',
      array($this->_settings->get_client_id()));

    $this->_connector->returns('get_client_lists', $lists);

    $this->EE->lang->returns('line', $lbl_default_fields,
      array('lbl_default_member'));

    $this->EE->lang->returns('line', $lbl_custom_fields,
      array('lbl_custom_member'));

    $this->EE->lang->returns('line', $lbl_zoo_fields,
      array('lbl_zoo_visitor'));

    $this->_model->expectOnce('get_member_fields__default_member');
    $this->_model->returns('get_member_fields__default_member',
      $default_member_fields);

    $this->_model->expectOnce('get_member_fields__custom_member');
    $this->_model->returns('get_member_fields__custom_member',
      $custom_member_fields);

    $this->_model->expectOnce('get_member_fields__zoo_visitor');
    $this->_model->returns('get_member_fields__zoo_visitor',
      $zoo_member_fields);

    $this->EE->load->expectOnce('view',
      array('_mailing_lists', $view_vars, TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_mailing_lists__works_with_no_custom_member_fields()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_mailing_lists', array('request'));

    // Dummy values.
    $lists = array();

    $lbl_default_fields = 'Standard Member Fields';
    $lbl_zoo_fields     = 'Zoo Visitor Fields';

    $default_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'screen_name',
        'label' => 'Screen Name', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'location',
        'label' => 'Location', 'type' => 'text'))
    );

    $zoo_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'field_id_10',
        'label' => 'Age', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'field_id_20',
        'label' => 'Gender', 'type' => 'radio'))
    );

    $dd_data = array(
      $lbl_default_fields => array(
        'screen_name' => 'Screen Name',
        'location'    => 'Location'
      ),
      $lbl_zoo_fields => array(
        'field_id_10' => 'Age',
        'field_id_20' => 'Gender'
      )
    );

    $view_vars = array(
      'mailing_lists' => $lists,
      'member_fields' => array_merge($default_member_fields,
        $zoo_member_fields),

      'member_fields_dd_data' => $dd_data,
      'settings'              => $this->_settings
    );

    $this->_connector->expectOnce('get_client_lists',
      array($this->_settings->get_client_id()));

    $this->_connector->returns('get_client_lists', $lists);

    $this->EE->lang->returns('line', $lbl_default_fields,
      array('lbl_default_member'));

    $this->EE->lang->returns('line', $lbl_zoo_fields,
      array('lbl_zoo_visitor'));

    $this->_model->expectOnce('get_member_fields__default_member');
    $this->_model->returns('get_member_fields__default_member',
      $default_member_fields);

    $this->_model->expectOnce('get_member_fields__custom_member');
    $this->_model->returns('get_member_fields__custom_member', array());

    $this->_model->expectOnce('get_member_fields__zoo_visitor');
    $this->_model->returns('get_member_fields__zoo_visitor',
      $zoo_member_fields);

    $this->EE->load->expectOnce('view',
      array('_mailing_lists', $view_vars, TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_mailing_lists__works_with_no_zoo_visitor_member_fields()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_mailing_lists', array('request'));

    // Dummy values.
    $lists = array();

    $lbl_custom_fields  = 'Custom Member Fields';
    $lbl_default_fields = 'Standard Member Fields';

    $default_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'screen_name',
        'label' => 'Screen Name', 'type' => 'text')),
      new Campaigner_trigger_field(array('id' => 'location',
        'label' => 'Location', 'type' => 'text'))
    );

    $custom_member_fields = array(
      new Campaigner_trigger_field(array('id' => 'm_field_id_10',
        'label' => 'Profile', 'type' => 'textarea')),
      new Campaigner_trigger_field(array('id' => 'm_field_id_20',
        'label' => 'Favourite Colour', 'type' => 'text'))
    );

    $dd_data = array(
      $lbl_default_fields => array(
        'screen_name' => 'Screen Name',
        'location'    => 'Location'
      ),
      $lbl_custom_fields => array(
        'm_field_id_10' => 'Profile',
        'm_field_id_20' => 'Favourite Colour'
      )
    );

    $view_vars = array(
      'mailing_lists' => $lists,
      'member_fields' => array_merge($default_member_fields,
        $custom_member_fields),

      'member_fields_dd_data' => $dd_data,
      'settings'              => $this->_settings
    );

    $this->_connector->expectOnce('get_client_lists',
      array($this->_settings->get_client_id()));

    $this->_connector->returns('get_client_lists', $lists);

    $this->EE->lang->returns('line', $lbl_default_fields,
      array('lbl_default_member'));

    $this->EE->lang->returns('line', $lbl_custom_fields,
      array('lbl_custom_member'));

    $this->_model->expectOnce('get_member_fields__default_member');
    $this->_model->returns('get_member_fields__default_member',
      $default_member_fields);

    $this->_model->expectOnce('get_member_fields__custom_member');
    $this->_model->returns('get_member_fields__custom_member',
      $custom_member_fields);

    $this->_model->expectOnce('get_member_fields__zoo_visitor');
    $this->_model->returns('get_member_fields__zoo_visitor', array());

    $this->EE->load->expectOnce('view',
      array('_mailing_lists', $view_vars, TRUE));

    $this->_subject->display_settings();
  }


  public function test__display_mailing_lists__exception()
  {
    $this->EE->input->returns('is_ajax_request', TRUE);
    $this->EE->input->returns('get', 'get_mailing_lists', array('request'));

    // Dummy values.
    $exception = new Campaigner_exception('Invalid API key', 100);
    $view_vars = array(
      'error_code'    => $exception->getCode(),
      'error_message' => $exception->getMessage()
    );

    $this->_connector->expectOnce('get_client_lists',
      array($this->_settings->get_client_id()));

    $this->_connector->throwOn('get_client_lists', $exception);

    $this->_model->expectNever('get_member_fields__custom_member');
    $this->_model->expectNever('get_member_fields__default_member');
    $this->_model->expectNever('get_member_fields__zoo_visitor');

    $this->_model->expectOnce('log_error', array('*'));
    $this->EE->load->expectOnce('view', array('_error', $view_vars, TRUE));

    $this->_subject->display_settings();
  }


  public function test__subscribe_member__fails_with_invalid_member_id_and_logs_error()
  {
    $this->_model->expectNever('get_member_by_id');
    $this->_model->expectCallCount('log_error', 5, array('*', 3));
  
    $s = $this->_subject;

    $this->assertIdentical(FALSE, $s->subscribe_member(0));
    $this->assertIdentical(FALSE, $s->subscribe_member('Invalid'));
    $this->assertIdentical(FALSE, $s->subscribe_member(NULL));
    $this->assertIdentical(FALSE, $s->subscribe_member(array()));
    $this->assertIdentical(FALSE, $s->subscribe_member(new StdClass()));
  }


  public function test__subscribe_member__fails_if_member_data_cannot_be_retrieved()
  {
    $member_id = 123;

    $this->_model->expectOnce('get_member_by_id', array($member_id));
    $this->_model->returns('get_member_by_id', array());

    $this->_model->expectOnce('log_error', array('*', 3));

    $this->_model->expectNever('get_all_mailing_lists');
    $this->_model->expectNever('get_member_subscribe_lists');
  
    $this->assertIdentical(FALSE,
      $this->_subject->subscribe_member($member_id));
  }


  public function test__subscribe_member__correctly_requests_member_subscribe_lists_from_model()
  {
    $all_lists = array(new Campaigner_mailing_list(array('list_id' => 'a')));
    $member_id = 123;

    $member_data = array(
      'email'       => 'charles.chaplin@talkies.com',
      'member_id'   => $member_id,
      'screen_name' => 'Charles Chaplin'
    );

    $this->_model->expectNever('log_error');

    $this->_model->expectOnce('get_member_by_id', array($member_id));
    $this->_model->returns('get_member_by_id', $member_data);

    $this->_model->expectOnce('get_all_mailing_lists');
    $this->_model->returns('get_all_mailing_lists', $all_lists);

    $this->_model->expectOnce('get_member_subscribe_lists',
      array($member_data, $all_lists));

    $this->_model->returns('get_member_subscribe_lists', array());
  
    $this->assertIdentical(TRUE,
      $this->_subject->subscribe_member($member_id));
  }


  public function test__subscribe_member__correctly_processes_member_subscribe_lists()
  {
    $all_lists = array(
      new Campaigner_mailing_list(array('list_id' => 'a')),
      new Campaigner_mailing_list(array('list_id' => 'b')),
      new Campaigner_mailing_list(array('list_id' => 'c'))
    );

    $force        = TRUE;
    $member_lists = array_slice($all_lists, 1);
    $member_id    = 123;

    $member_data = array(
      'email'       => 'charles.chaplin@talkies.com',
      'member_id'   => $member_id,
      'screen_name' => 'Charles Chaplin'
    );

    $subscriber = new Campaigner_subscriber();

    $this->_model->expectNever('log_error');

    $this->_model->expectOnce('get_member_by_id');
    $this->_model->returns('get_member_by_id', $member_data);

    $this->_model->expectOnce('get_all_mailing_lists');
    $this->_model->returns('get_all_mailing_lists', $all_lists);

    $this->_model->expectOnce('get_member_subscribe_lists');
    $this->_model->returns('get_member_subscribe_lists', $member_lists);
  
    // Process the member lists.
    $this->_model->expectCallCount('get_member_as_subscriber', 2);
    $this->_model->returns('get_member_as_subscriber', $subscriber);

    $this->_model->expectAt(0, 'get_member_as_subscriber',
      array($member_data, $member_lists[0]));

    $this->_model->expectAt(1, 'get_member_as_subscriber',
      array($member_data, $member_lists[1]));

    $this->_connector->expectCallCount('add_list_subscriber', 2);

    $this->_connector->expectAt(0, 'add_list_subscriber',
      array('b', $subscriber, $force));

    $this->_connector->expectAt(1, 'add_list_subscriber',
      array('c', $subscriber, $force));

    $this->assertIdentical(TRUE,
      $this->_subject->subscribe_member($member_id, $force));
  }


  public function test__subscribe_member__calls_extension_hook_and_honors_end_script()
  {
    $all_lists    = array(new Campaigner_mailing_list(array('list_id' => 'a')));
    $member_lists = $all_lists;
    $member_id    = 123;

    $member_data = array(
      'email'       => 'charles.chaplin@talkies.com',
      'member_id'   => $member_id,
      'screen_name' => 'Charles Chaplin'
    );

    $subscriber = new Campaigner_subscriber();

    $this->_model->expectNever('log_error');

    $this->_model->expectOnce('get_member_by_id');
    $this->_model->returns('get_member_by_id', $member_data);

    $this->_model->expectOnce('get_all_mailing_lists');
    $this->_model->returns('get_all_mailing_lists', $all_lists);

    $this->_model->expectOnce('get_member_subscribe_lists');
    $this->_model->returns('get_member_subscribe_lists', $member_lists);
  
    // Process the member lists.
    $this->_model->expectOnce('get_member_as_subscriber');
    $this->_model->returns('get_member_as_subscriber', $subscriber);

    $this->EE->extensions->expectOnce('active_hook',
      array('campaigner_subscribe_start'));

    $this->EE->extensions->returns('active_hook', TRUE,
      array('campaigner_subscribe_start'));

    $this->EE->extensions->expectOnce('call',
      array('campaigner_subscribe_start', $member_id, $subscriber));

    // Set the 'end script' trigger.
    $this->EE->extensions->end_script = TRUE;

    // Should never get this far.
    $this->_connector->expectNever('add_list_subscriber');

    $this->assertIdentical(FALSE,
      $this->_subject->subscribe_member($member_id));
  }


  public function test__subscribe_member__handles_connector_exception()
  {
    $all_lists    = array(new Campaigner_mailing_list(array('list_id' => 'a')));
    $member_lists = $all_lists;
    $member_id    = 123;

    $member_data = array(
      'email'       => 'charles.chaplin@talkies.com',
      'member_id'   => $member_id,
      'screen_name' => 'Charles Chaplin'
    );

    $subscriber = new Campaigner_subscriber();

    $this->_model->expectOnce('get_member_by_id');
    $this->_model->returns('get_member_by_id', $member_data);

    $this->_model->expectOnce('get_all_mailing_lists');
    $this->_model->returns('get_all_mailing_lists', $all_lists);

    $this->_model->expectOnce('get_member_subscribe_lists');
    $this->_model->returns('get_member_subscribe_lists', $member_lists);
  
    // Process the member lists.
    $this->_model->expectOnce('get_member_as_subscriber');
    $this->_model->returns('get_member_as_subscriber', $subscriber);

    // Throw the exception.
    $exception = new Campaigner_exception('EPIC FAIL!');

    $this->_connector->throwOn('add_list_subscriber', $exception);

    /**
     * STUPID:
     * SimpleTest has a fit if we specify the Exception instance as an 
     * expectation. Probably something to do with the EE superglobal and levels 
     * of nesting, it usually is. Whatever, it's dumb, and a ball-ache, and I 
     * really can't be arsed fixing other people's code right now.
     */

    $this->_model->expectOnce('log_error', array('*', 3));

    $this->assertIdentical(FALSE,
      $this->_subject->subscribe_member($member_id));
  }


  public function test__unsubscribe_member__success()
  {
      $model = $this->_model;

      // Dummy values.
      $member_id = 10;

      // Retrieve all the mailing lists.
      $mailing_lists = array(
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_a',
              'list_name' => 'LIST A'
          )),
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_b',
              'list_name' => 'LIST B'
          )),
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_c',
              'list_name' => 'LIST C'
          ))
      );

      $model->expectOnce('get_all_mailing_lists');
      $model->returns('get_all_mailing_lists', $mailing_lists);

      // Retrieve the member information.
      $email = 'me@here.com';

      $member_data = array(
          'email'     => $email,
          'member_id' => $member_id
      );

      $model->expectOnce('get_member_by_id', array($member_id));
      $model->returns('get_member_by_id', $member_data);   

      // For each mailing list, determine if the member should be subscribed.
      $model->expectCallCount('member_should_be_subscribed_to_mailing_list', count($mailing_lists));
      $model->returns('member_should_be_subscribed_to_mailing_list', FALSE);

      // For each mailing list, determine if the member is subscribed.
      $this->_connector->expectCallCount('get_is_subscribed', count($mailing_lists));
      $this->_connector->expectCallCount('remove_list_subscriber', ceil(count($mailing_lists) / 2));

      $count = 0;
      $remove_count = 0;

      foreach ($mailing_lists AS $mailing_list)
      {
          $is_subscribed = (bool) $count % 2;

          $this->_connector->expectAt($count, 'get_is_subscribed', array($mailing_list->get_list_id(), $email));
          $this->_connector->returnsAt($count, 'get_is_subscribed', $is_subscribed);

          if ($is_subscribed)
          {
              // Unsubscribe the member.
              $this->_connector->expectAt($remove_count++, 'remove_list_subscriber', array($mailing_list->get_list_id(), $email));
          }

          $count++;
      }

      // Run the tests.
      $this->assertIdentical(TRUE, $this->_subject->unsubscribe_member($member_id));
  }


  public function test__unsubscribe_member__should_be_subscribed_to_all_mailing_lists()
  {
      $model = $this->_model;

      // Dummy values.
      $member_id = 10;

      // Retrieve all the mailing lists.
      $mailing_lists = array(
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_a',
              'list_name' => 'LIST A'
          )),
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_b',
              'list_name' => 'LIST B'
          )),
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_c',
              'list_name' => 'LIST C'
          ))
      );

      $model->expectOnce('get_all_mailing_lists');
      $model->returns('get_all_mailing_lists', $mailing_lists);

      // Retrieve the member information.
      $email = 'me@here.com';

      $member_data = array(
          'email'     => $email,
          'member_id' => $member_id
      );

      $model->expectOnce('get_member_by_id', array($member_id));
      $model->returns('get_member_by_id', $member_data);   

      $model->expectCallCount('member_should_be_subscribed_to_mailing_list', count($mailing_lists));
      $model->returns('member_should_be_subscribed_to_mailing_list', TRUE);

      $this->_connector->expectNever('get_is_subscribed');
      $this->_connector->expectNever('remove_list_subscriber');

      // Run the tests.
      $this->assertIdentical(TRUE, $this->_subject->unsubscribe_member($member_id));
  }


  public function test__unsubscribe_member__invalid_member_id()
  {
      // Shortcuts.
      $model = $this->_model;

      // Dummy values.
      $member_id  = 0;
      $message    = 'Invalid member ID.';

      // Expectations and return values.
      $this->EE->lang->returns('line', $message, array('error_missing_or_invalid_member_id'));
      $model->expectOnce('log_error', array('*', 3));

      $model->expectNever('get_all_mailing_lists');
      $model->expectNever('get_member_by_id');

      $this->_connector->expectNever('get_is_subscribed');
      $this->_connector->expectNever('remove_list_subscriber');

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->unsubscribe_member($member_id));
  }


  public function test__unsubscribe_member__unknown_member()
  {
      // Shortcuts.
      $model = $this->_model;

      // Dummy values.
      $member_id      = 10;
      $member_data    = array();
      $message        = 'Unknown member';

      // Expectations and return values.
      $this->EE->lang->returns('line', $message, array('error_unknown_member'));

      $model->expectOnce('log_error', array('*', 3));
      $model->expectOnce('get_member_by_id');
      $model->returns('get_member_by_id', $member_data);

      $model->expectNever('get_all_mailing_lists');
      $this->_connector->expectNever('get_is_subscribed');
      $this->_connector->expectNever('remove_list_subscriber');

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->unsubscribe_member($member_id));
  }


  public function test__unsubscribe_member__no_mailing_lists()
  {
      // Shortcuts.
      $model = $this->_model;

      // Dummy values.
      $email          = 'me@here.com';
      $member_id      = 10;
      $member_data = array(
          'email'     => $email,
          'member_id' => $member_id
      );

      // Expectations and return values.
      $model->expectOnce('get_member_by_id');
      $model->returns('get_member_by_id', $member_data);

      $model->expectOnce('get_all_mailing_lists');
      $model->returns('get_all_mailing_lists', array());

      $this->_connector->expectNever('get_is_subscribed');
      $this->_connector->expectNever('remove_list_subscriber');

      // Run the tests.
      $this->assertIdentical(TRUE, $this->_subject->unsubscribe_member($member_id));
  }


  public function test__unsubscribe_member__exception()
  {
      $model = $this->_model;

      // Dummy values.
      $member_id = 10;

      // Retrieve all the mailing lists.
      $mailing_lists = array(
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_a',
              'list_name' => 'LIST A'
          )),
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_b',
              'list_name' => 'LIST B'
          )),
          new Campaigner_mailing_list(array(
              'list_id'   => 'list_c',
              'list_name' => 'LIST C'
          ))
      );

      $model->returns('get_all_mailing_lists', $mailing_lists);

      // Retrieve the member information.
      $email = 'me@here.com';

      $member_data = array(
          'email'     => $email,
          'member_id' => $member_id
      );

      $model->returns('get_member_by_id', $member_data);   

      // For each mailing list, determine if the member is subscribed.
      $connector_exception = new Campaigner_exception('Error');

      $this->_connector->returns('get_is_subscribed', TRUE);
      $this->_connector->throwOn('remove_list_subscriber', $connector_exception);

      /**
       * Handle the exception.
       *
       * NOTE:
       * Specifying the $connector_exception object as the expected parameter
       * causes out of memory errors in SimpleTest. Goodness knows why, and
       * I'm not inclined to waste time finding out. This will suffice.
       */

      $model->expectOnce('log_error', array('*', 3));

      // Run the tests.
      $this->assertIdentical(FALSE, $this->_subject->unsubscribe_member($member_id));
  }


}


/* End of file      : test.campaigner_ext.php */
/* File location    : third_party/campaigner/tests/test.campaigner_ext.php */
