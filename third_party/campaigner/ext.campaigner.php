<?php if ( ! defined('BASEPATH')) exit('Direct script access is not permitted.');

/**
 * Automatically add your EE members to Campaign Monitor mailing lists.
 * 
 * @author          : Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright       : Experience Internet
 * @package         : Campaigner
 */

require_once PATH_THIRD .'campaigner/helpers/EI_number_helper.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_exception.php';

class Campaigner_ext {

  private $EE;
  private $_connector;
  private $_model;

  public $description;
  public $docs_url;
  public $name;
  public $settings;
  public $settings_exist;
  public $version;



  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Class constructor.
   *
   * @access  public
   * @param   array       $settings       Previously-saved extension settings.
   * @return  void
   */
  public function __construct($settings = array())
  {
    $this->EE =& get_instance();

    // Load the model.
    $this->EE->load->add_package_path(PATH_THIRD .'campaigner/');
    $this->EE->load->model('campaigner_model');

    $this->_model = $this->EE->campaigner_model;

    // Load the language file.
    $this->EE->lang->loadfile('campaigner');

    // Set the instance properties.
    $this->description
      = $this->EE->lang->line('campaigner_extension_description');

    $this->docs_url = $this->_model->get_docs_url();
    $this->name     = $this->EE->lang->line('campaigner_extension_name');

    $this->settings       = $settings;
    $this->settings_exist = 'y';

    $this->version  = $this->_model->get_package_version();

    // Is the extension installed?
    if ( ! $this->_model->get_installed_extension_version())
    {
      return;
    }

    // Load the settings from the database, and update them with any input data.
    $this->settings = $this->_model->update_extension_settings_from_input(
      $this->_model->get_extension_settings());

    // Retrieve the API connector.
    $this->_connector = $this->_model->get_api_connector();
  }


  /**
   * Activates the extension.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension()
  {
    $this->_model->activate_extension();
  }


  /**
   * Disables the extension.
   *
   * @access  public
   * @return  void
   */
  public function disable_extension()
  {
    $this->_model->disable_extension();
  }


  /**
   * Displays the 'settings' page.
   *
   * @access  public
   * @return  string
   */
  public function display_settings()
  {
    // If this isn't an AJAX request, just display the "base" settings form.
    if ( ! $this->EE->input->is_ajax_request())
    {
      return $this->_display_base_settings();
    }

    /**
     * Handle AJAX requests. Both types of AJAX request require
     * valid API connector, so we perform that check here.
     */

    if ( ! $this->_connector)
    {
      $response = $this->_display_error(
        $this->EE->lang->line('error_no_api_connector')
      );
    }
    else
    {
      switch (strtolower($this->EE->input->get('request')))
      {
        case 'get_clients':
          $response = $this->_display_clients();
          break;

        case 'get_custom_fields':
          $response = $this->_display_custom_fields();
          break;

        case 'get_mailing_lists':
          $response = $this->_display_mailing_lists();
          break;

        default:
          $response = $this->_display_error(
            $this->EE->lang->line('error_unknown_ajax_request')
          );
          break;
      }
    }

    $this->EE->output->send_ajax_response($response);
  }


  /**
   * Saves the extension settings.
   *
   * @access  public
   * @return  void
   */
  public function save_settings()
  {
    try
    {
      $this->_model->save_extension_settings($this->settings);
      $this->EE->session->set_flashdata('message_success',
        $this->EE->lang->line('msg_settings_saved'));
    }
    catch (Campaigner_exception $e)
    {
      $message = $this->EE->lang->line('msg_settings_not_saved')
        .' (' .$e->getMessage() .')';

      $this->EE->session->set_flashdata('message_failure', $message);
    }
  }


  /**
   * Displays the extension settings form.
   *
   * @access  public
   * @return  string
   */
  public function settings_form()
  {
    // Load our glamorous assistants.
    $this->EE->load->helper('form');
    $this->EE->load->library('table');

    // Define the navigation.
    $base_url = BASE .AMP .'C=addons_extensions' .AMP .'M=extension_settings'
      .AMP .'file=campaigner' .AMP .'tab=';

    $this->EE->cp->set_right_nav(array(
      'nav_settings'  => $base_url .'settings',
      'nav_support'   => $this->_model->get_support_url()
    ));

    return $this->display_settings();
  }


  /**
   * Subscribes the specified member to the configured mailing lists.
   *
   * @access  public
   * @param   int|string  $member_id          The member ID.
   * @param   bool        $force_resubscribe  Forcibly resubscribe the member?
   * @return  bool
   */
  public function subscribe_member($member_id, $force_resubscribe = FALSE)
  {
    // Get out early.
    if ( ! valid_int($member_id, 1))
    {
      $log_message
        = $this->EE->lang->line('error_missing_or_invalid_member_id')
          .' ' .__METHOD__ .' (' .__LINE__ .')';

      $this->_model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    // Retrieve the member data.
    if ( ! $member_data = $this->_model->get_member_by_id($member_id))
    {
      $log_message = $this->EE->lang->line('error_unknown_member')
        .' ' .__METHOD__ .' (' .__LINE__ .')';

      $this->_model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    // Retrieve the mailing lists to which the member should be subscribed.
    $lists = $this->_model->get_member_subscribe_lists($member_data,
      $this->_model->get_all_mailing_lists());

    foreach ($lists AS $list)
    {
      try
      {
        if ( ! $subscriber = $this->_model->get_member_as_subscriber(
          $member_data, $list)
        )
        {
          continue;
        }

        if ($this->EE->extensions->active_hook(
          'campaigner_subscribe_start') === TRUE
        )
        {
          $subscriber = $this->EE->extensions->call(
            'campaigner_subscribe_start', $member_id, $subscriber);

          if ($this->EE->extensions->end_script === TRUE)
          {
            return FALSE;
          }
        }

        $this->_connector->add_list_subscriber(
          $list->get_list_id(), $subscriber, $force_resubscribe);
      }
      catch (Campaigner_exception $e)
      {
        $this->_model->log_error($e, 3);
        return FALSE;
      }
    }

    return TRUE;
  }


  /**
   * Unsubscribes the specified member from all mailing lists.
   *
   * @access  public
   * @param   int|string      $member_id      The member ID.
   * @return  bool
   */
  public function unsubscribe_member($member_id)
  {
    // Get out early.
    if ( ! valid_int($member_id, 1))
    {
      $log_message
        = $this->EE->lang->line('error_missing_or_invalid_member_id')
          .' ' .__METHOD__ .' (' .__LINE__ .')';

      $this->_model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    // Retrieve the member information.
    if ( ! $member_data = $this->_model->get_member_by_id($member_id))
    {
      $log_message = $this->EE->lang->line('error_unknown_member')
        .' ' .__METHOD__ .' (' .__LINE__ .')';

      $this->_model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    $lists = $this->_model->get_all_mailing_lists();
    $email = $member_data['email'];

    foreach ($lists AS $list)
    {
      /**
       * TRICKY:
       * The member should not be able to unsubscribe from lists with no
       * trigger field. This sounds draconian, but makes sense. Without a
       * trigger field, the edit form can't include an opt-in / opt-out field
       * for this mailing list anyway, so it makes no sense for us to process
       * such lists here.
       */

      if ($this->_model->member_should_be_subscribed_to_mailing_list(
        $member_data, $list)
      )
      {
        continue;
      }

      // Unsubscribe the member.
      try
      {
        if ($this->_connector->get_is_subscribed($list->get_list_id(), $email))
        {
          $this->_connector->remove_list_subscriber(
            $list->get_list_id(), $email);
        }
      }
      catch (Campaigner_exception $e)
      {
        $this->_model->log_error($e, 3);
        return FALSE;
      }
    }

    return TRUE;
  }


  /**
   * Updates the extension.
   *
   * @access  public
   * @param   string      $installed_version      The installed version.
   * @return  bool
   */
  public function update_extension($installed_version = '')
  {
    return $this->_model->update_extension(
      $installed_version, $this->version);
  }



  /* --------------------------------------------------------------
   * HOOK HANDLERS
   * ------------------------------------------------------------ */

  /**
   * Handles the `cartthrob_on_authorize` hook.
   *
   * @access  public
   * @return  void
   */
  public function on_cartthrob_on_authorize()
  {
    /**
     * Retrieve the member ID from the CartThrob object. Many thanks to Rob 
     * Sanchez for pointing me to this, I'd never have found it otherwise.
     */

    if ( ! $member_id = $this->EE->cartthrob->cart->order('create_user')
      OR ! valid_int($member_id, 1)
    )
    {
      return;
    }

    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `cp_members_member_create` hook. Used when a member is created
   * via the control panel.
   *
   * @see     http://expressionengine.com/developers/extension_hooks/cp_members_member_create/
   * @access  public
   * @param   int|string      $member_id          The member ID.
   * @param   array           $member_data        Additional member data.
   * @return  void
   */
  public function on_cp_members_member_create($member_id, Array $member_data)
  {
    if ($this->_model->is_zoo_visitor_installed() === TRUE)
    {
      return;
    }

    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `cp_members_validate_members` hook.
   *
   * @see     http://expressionengine.com/developers/extension_hooks/cp_members_validate_members/
   * @access  public
   * @return  void
   */
  public function on_cp_members_validate_members()
  {
    /**
     * NOTE:
     * We no longer check that the 'require member activation' preference is set 
     * to 'manual', as Admin may conceivably wish to manually activate a Member 
     * who has neglected to activate his Membership via email.
     */

    if ( ! $member_ids = $this->EE->input->post('toggle'))
    {
      return;
    }

    foreach ($member_ids AS $member_id)
    {
      $this->subscribe_member($member_id);
    }
  }


  /**
   * Handles the `member_member_register` hook. Used when the membership
   * preferences are set to "No activation required"
   * (i.e. req_mbr_activation = 'none').
   *
   * @see     http://expressionengine.com/developers/extension_hooks/member_member_register/
   * @access  public
   * @param   array           $member_data        Member data.
   * @param   int|string      $member_id          The member ID (added in 2.0.1).
   * @return  void
   */
  public function on_member_member_register(Array $member_data, $member_id)
  {
    /**
     * TRICKY:
     * The Zoo Visitor module calls this hook, in addition to its own 
     * zoo_visitor_register_end hook. If ZV is installed, we ignore this hook,
     * because it performs additional tasks between this hook, and the custom ZV 
     * hook.
     */

    if ($this->EE->config->item('req_mbr_activation') != 'none'
      OR $this->_model->is_zoo_visitor_installed() === TRUE
    )
    {
      return;
    }

    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `member_register_validate_members` hook. Used when the
   * membership preferences are set to "Self-activation via email"
   * (i.e. req_mbr_activation = 'email').
   *
   * @see     http://expressionengine.com/developers/extension_hooks/member_register_validate_members/
   * @access  public
   * @param   int|string      $member_id          The member ID.
   * @return  void
   */
  public function on_member_register_validate_members($member_id)
  {
    if ($this->EE->config->item('req_mbr_activation') != 'email')
    {
      return;
    }

    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `membrr_subscribe` hook.
   *
   * @access  public
   * @param   int|string  $member_id  The member ID.
   * @param   int|string  $sub_id     The Membrr subscription ID.
   * @param   int|string  $plan_id    The Membrr plan ID.
   * @param   string      $end_date   The subscription end date.
   * @return  void
   */
  public function on_membrr_subscribe($member_id, $sub_id, $plan_id, $end_date)
  {
    /**
     * The Member may have been created previously, so we need to update 
     * subscriptions.
     */

    $this->unsubscribe_member($member_id);
    $this->subscribe_member($member_id, TRUE);
  }


  /**
   * Handles the `user_edit_end` hook.
   *
   * @see     http://www.solspace.com/docs/detail/user_user_edit_end/
   * @access  public
   * @param   int|string      $member_id              The member ID.
   * @param   array           $member_data            Additional member data.
   * @param   array           $member_custom_data     Member custom field data.
   * @return  void
   */
  public function on_user_edit_end($member_id, Array $member_data,
    Array $member_custom_data
  )
  {
    $this->unsubscribe_member($member_id);
    $this->subscribe_member($member_id, TRUE);
  }


  /**
   * Handles the `user_register_end` hook. Used when registering via the User
   * module's {exp:user:register} form, with the membership preferences set
   * to "No activation required" (i.e. req_mbr_activation = 'none').
   *
   * @see     http://www.solspace.com/docs/detail/user_user_register_end/
   * @access  public
   * @param   object          $user               Instance of the User class.
   * @param   int|string      $member_id          The member ID.
   * @return  void
   */
  public function on_user_register_end($user, $member_id)
  {
    if ($this->EE->config->item('req_mbr_activation') != 'none')
    {
      return;
    }

    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `zoo_visitor_cp_register_end` hook.
   *
   * @access  public
   * @param   Array         $member_data    The member data.
   * @param   int|string    $member_id      The member ID.
   * @return  void
   */
  public function on_zoo_visitor_cp_register_end(Array $member_data = array(),
    $member_id = 0
  )
  {
    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `zoo_visitor_cp_update_end` hook.
   *
   * @access  public
   * @param   Array         $member_data    The member data.
   * @param   int|string    $member_id      The member ID.
   * @return  void
   */
  public function on_zoo_visitor_cp_update_end(Array $member_data = array(),
    $member_id = 0
  )
  {
    $this->unsubscribe_member($member_id);
    $this->subscribe_member($member_id, TRUE);
  }


  /**
   * Handles the `zoo_visitor_update_end` hook.
   *
   * @access  public
   * @param   Array         $member_data    The member data.
   * @param   int|string    $member_id      The member ID.
   * @return  void
   */
  public function on_zoo_visitor_update_end(Array $member_data = array(),
    $member_id = 0
  )
  {
    $this->unsubscribe_member($member_id);
    $this->subscribe_member($member_id, TRUE);
  }


  /**
   * Handles the `zoo_visitor_register_end` hook.
   *
   * @access  public
   * @param   Array         $member_data    The member data.
   * @param   int|string    $member_id      The member ID.
   * @return  void
   */
  public function on_zoo_visitor_register_end(Array $member_data = array(),
    $member_id = 0
  )
  {
    if ($this->EE->config->item('req_mbr_activation') != 'none')
    {
      return;
    }

    $this->subscribe_member($member_id, TRUE);
  }



  /* --------------------------------------------------------------
   * PRIVATE METHODS
   * ------------------------------------------------------------ */

  /**
   * Converts an array of member field objects for use in a dropdown menu.
   *
   * @access  private
   * @param   Array     $member_fields    An array of member field objects.
   * @return  Array
   */
  private function _build_member_fields_dropdown(Array $member_fields)
  {
    $dropdown = array();

    foreach ($member_fields AS $member_field)
    {
      $dropdown[$member_field->get_id()] = $member_field->get_label();
    }

    return $dropdown;
  }


  /**
   * Displays the "base" settings form.
   *
   * @access  private
   * @return  string
   */
  private function _display_base_settings()
  {
    $lower_package_name = strtolower($this->_model->get_package_name());

    $view_vars = array(
      'action_url'  => 'C=addons_extensions' .AMP .'M=save_extension_settings',
      'cp_page_title' => $this->EE->lang->line('campaigner_extension_name'),
      'hidden_fields' => array('file' => $lower_package_name),
      'settings'      => $this->settings
    );

    $theme_url = $this->_model->get_theme_url();

    // Add the CSS.
    $this->EE->cp->add_to_foot('<link media="screen, projection"'
      .' rel="stylesheet" type="text/css"'
      .' href="' .$theme_url .'css/cp.css" />');

    // Load the JavaScript library, and set a shortcut.
    $this->EE->load->library('javascript');

    $this->EE->cp->add_to_foot('<script type="text/javascript" src="'
      .$theme_url .'js/cp.js"></script>');

    $this->EE->cp->add_to_foot('<script type="text/javascript" src="'
      .$theme_url .'js/jquery.activity-indicator.min.js"></script>');

    // JavaScript globals.
    $this->EE->javascript->set_global('campaigner.lang', array(
      'missingApiKey'   => $this->EE->lang->line('msg_missing_api_key'),
      'missingClientId' => $this->EE->lang->line('msg_missing_client_id')
    ));

    // Prepare the member fields.
    $js_member_fields = array();
    $member_fields    = $this->_get_member_fields();

    foreach ($member_fields AS $m_field_group => $m_fields)
    {
      foreach ($m_fields AS $m_field)
      {
        $js_member_fields[$m_field->get_id()] = $m_field->to_array();
      }
    }

    $this->EE->javascript->set_global('campaigner.memberFields',
      $this->EE->javascript->generate_json($js_member_fields));

    $this->EE->javascript->set_global('campaigner.ajaxUrl',
      str_replace(AMP, '&', BASE)
      .'&C=addons_extensions&M=extension_settings&file='
      .$lower_package_name
    );

    // Compile the JavaScript.
    $this->EE->javascript->compile();

    // Load the view.
    return $this->EE->load->view('settings', $view_vars, TRUE);
  }


  /**
   * Displays the "clients" settings form fragment.
   *
   * @access  private
   * @return  string
   */
  private function _display_clients()
  {
    try
    {
      $view_vars = array(
        'clients'   => $this->_connector->get_clients(),
        'settings'  => $this->settings
      );  

      $view_name = '_clients';
      return $this->EE->load->view($view_name, $view_vars, TRUE);
    }
    catch (Campaigner_exception $e)
    {
      $this->_model->log_error($e);
      return $this->_display_error($e->getMessage(), $e->getCode());
    }
  }


  /**
   * Displays the "custom fields" settings form fragment.
   *
   * @access  public
   * @return  string
   */
  private function _display_custom_fields()
  {
    // At the very least, we need a list ID.
    if ( ! ($list_id = $this->EE->input->get_post('list_id')))
    {
      $error_message = $this->EE->lang->line(
        'error_missing_or_invalid_list_id');

      $this->_model->log_error(new Campaigner_exception($error_message));
      return $this->_display_custom_fields_error();
    }

    try
    {
      $cm_fields = $this->_connector->get_list_fields($list_id);
    }
    catch (Campaigner_exception $e)
    {
      $this->_model->log_error($e);
      return $this->_display_custom_fields_error($list_id);
    }

    // Declare the 'field' view variables here, in case there are no CM fields.
    $view_fields = $view_fields_dd = array();

    if ($cm_fields)
    {
      // Restore any saved field settings.
      if ($saved_list = $this->settings->get_mailing_list_by_id($list_id))
      {
        // Restore the saved custom field settings.
        foreach ($cm_fields AS $cm_field)
        {
          if (($saved_field = $saved_list->get_custom_field_by_cm_key(
            $cm_field->get_cm_key())
          ))
          {
            $cm_field->set_member_field_id($saved_field->get_member_field_id());
          }
        }
      }

      // Retrieve the member fields.
      $member_fields = $this->_get_member_fields();

      foreach ($member_fields AS $m_field_group => $m_fields)
      {
        $field_group_label = $this->EE->lang->line('lbl_' .$m_field_group);

        $view_fields = array_merge($view_fields, $m_fields);

        $view_fields_dd[$field_group_label]
          = $this->_build_member_fields_dropdown($m_fields);
      }
    }

    // Define the view variables.
    $view_vars = array(
      'custom_fields'         => $cm_fields,
      'list_id'               => $list_id,
      'member_fields'         => $view_fields,
      'member_fields_dd_data' => $view_fields_dd
    );

    $view_name = '_custom_fields';
    return $this->EE->load->view($view_name, $view_vars, TRUE);
  }


  /**
   * Displays the custom fields 'error' view.
   *
   * @access  private
   * @return  string
   */
  private function _display_custom_fields_error()
  {
    return $this->EE->load->view('_custom_fields_error', array(), TRUE);
  }


  /**
   * Displays the 'error message' view.
   *
   * @access  private
   * @param   string      $error_message      The error message.
   * @param   string      $error_code         The error code.
   * @return  string
   */
  private function _display_error($error_message = '', $error_code = '')
  {
    $view_vars = array(
      'error_code'    => $error_code,
      'error_message' => $error_message
        ? $error_message
        : $this->EE->lang->line('error_unknown')
    );

    return $this->EE->load->view('_error', $view_vars, TRUE);
  }


  /**
   * Displays the "mailing lists" settings form fragment.
   *
   * @access  private
   * @return  string
   */
  private function _display_mailing_lists()
  {
    // Retrieve all the available mailing lists from the API.
    try
    {
      $lists = $this->_connector->get_client_lists(
        $this->settings->get_client_id());
    }
    catch (Campaigner_exception $e)
    {
      $this->_model->log_error($e);
      return $this->_display_error($e->getMessage(), $e->getCode());
    }

    // Loop through the lists. Note any list settings.
    foreach ($lists AS $list)
    {
      // If this list has not been previously saved, we're done.
      if ( ! ($saved_list = $this->settings->get_mailing_list_by_id(
        $list->get_list_id()))
      )
      {
        continue;
      }

      // Restore the saved list settings.
      $list->set_active(TRUE);
      $list->set_trigger_field($saved_list->get_trigger_field());
      $list->set_trigger_value($saved_list->get_trigger_value());
    }

    // Retrieve the member fields.
    $member_fields = $this->_get_member_fields();
    $view_fields = $view_fields_dd = array();

    foreach ($member_fields AS $field_group => $fields)
    {
      $field_group_label = $this->EE->lang->line('lbl_' .$field_group);

      $view_fields = array_merge($view_fields, $fields);

      $view_fields_dd[$field_group_label]
        = $this->_build_member_fields_dropdown($fields);
    }

    // Define the view variables.
    $view_vars = array(
      'mailing_lists'         => $lists,
      'member_fields'         => $view_fields,
      'member_fields_dd_data' => $view_fields_dd,
      'settings'              => $this->settings
    );

    $view_name = '_mailing_lists';
    return $this->EE->load->view($view_name, $view_vars, TRUE);
  }


  /**
   * Returns an array of member fields, grouped by 'type' (default, custom, or 
   * Zoo Visitor).
   *
   * @access  private
   * @return  array
   */
  private function _get_member_fields()
  {
    $fields = array();

    $fields['default_member']
      = $this->_model->get_member_fields__default_member();

    if ($custom_fields = $this->_model->get_member_fields__custom_member())
    {
      $fields['custom_member'] = $custom_fields;
    }

    if ($zoo_fields = $this->_model->get_member_fields__zoo_visitor())
    {
      $fields['zoo_visitor'] = $zoo_fields;
    }

    return $fields;
  }


}


/* End of file      : ext.campaigner.php */
/* File location    : third_party/campaigner/ext.campaigner.php */
