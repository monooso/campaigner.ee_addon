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
    
  private $_connector;
  private $_ee;
  
  public $description;
  public $docs_url;
  public $name;
  public $settings = array();
  public $settings_exist = 'y';
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
    $this->_ee =& get_instance();
    
    // Load the model.
    $this->_ee->load->add_package_path(PATH_THIRD .'campaigner/');
    $this->_ee->load->model('campaigner_model');
    
    // Shortcut.
    $model = $this->_ee->campaigner_model;
    
    // Load the language file.
    $this->_ee->lang->loadfile('campaigner');
    
    // Set the instance properties.
    $this->description  = $this->_ee->lang->line('campaigner_extension_description');
    $this->docs_url     = $model->get_docs_url();
    $this->name         = $this->_ee->lang->line('campaigner_extension_name');
    $this->settings     = $settings;
    $this->version      = $model->get_package_version();
    
    // Is the extension installed?
    if ( ! $model->get_installed_extension_version())
    {
      return;
    }

    // Load the settings from the database, and update them with any input data.
    $this->settings = $model->update_extension_settings_from_input($model->get_extension_settings());

    // Retrieve the API connector.
    $this->_connector = $model->get_api_connector();
  }
  
  
  /**
   * Activates the extension.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension()
  {
    $this->_ee->campaigner_model->activate_extension();
  }
  
  
  /**
   * Disables the extension.
   *
   * @access  public
   * @return  void
   */
  public function disable_extension()
  {
    $this->_ee->campaigner_model->disable_extension();
  }


  /**
   * Displays the 'error message' view.
   *
   * @access  public
   * @param   string      $error_message      The error message.
   * @param   string      $error_code         The error code.
   * @return  string
   */
  public function display_error($error_message = '', $error_code = '')
  {
    $view_vars = array(
        'error_code'    => $error_code,
        'error_message' => $error_message
          ? $error_message
          : $this->_ee->lang->line('error_unknown')
    );

    return $this->_ee->load->view('_error', $view_vars, TRUE);
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
    if ( ! isset($_SERVER['HTTP_X_REQUESTED_WITH'])
      OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest'
    )
    {
      return $this->display_settings_base();
    }
    
    /**
     * Handle AJAX requests. Both types of AJAX request require
     * valid API connector, so we perform that check here.
     */

    if ( ! $this->_connector)
    {
      $response = $this->display_error(
        $this->_ee->lang->line('error_no_api_connector')
      );
    }
    else
    {
      switch (strtolower($this->_ee->input->get('request')))
      {
        case 'get_clients':
          $response = $this->display_settings_clients();
          break;
            
        case 'get_mailing_lists':
          $response = $this->display_settings_mailing_lists();
          break;
        
        default:
          $response = $this->display_error(
            $this->_ee->lang->line('error_unknown_ajax_request')
          );
          break;
      }
    }

    $this->_ee->output->send_ajax_response($response);
  }
  
  
  /**
   * Displays the "base" settings form.
   *
   * @access  public
   * @return  string
   */
  public function display_settings_base()
  {
    // Shortcuts.
    $cp     = $this->_ee->cp;
    $lang   = $this->_ee->lang;
    $model  = $this->_ee->campaigner_model;
    
    $lower_package_name = strtolower($model->get_package_name());
    
    // View variables.
    $view_vars = array(
      'action_url'    => 'C=addons_extensions' .AMP .'M=save_extension_settings',
      'cp_page_title' => $lang->line('campaigner_extension_name'),
      'hidden_fields' => array('file' => $lower_package_name),
      'settings'      => $this->settings      // Loaded in the constructor.
    );
    
    // Theme URL.
    $theme_url = $model->get_theme_url();
    
    // Add the CSS.
    $cp->add_to_foot('<link media="screen, projection" rel="stylesheet"
      type="text/css" href="' .$theme_url .'css/cp.css" />');

    // Load the JavaScript library, and set a shortcut.
    $this->_ee->load->library('javascript');
    $js = $this->_ee->javascript;
    
    $cp->add_to_foot('<script type="text/javascript" src="' .$theme_url
      .'js/cp.js"></script>');

    // JavaScript globals.
    $js->set_global('campaigner.lang', array(
      'missingApiKey'     => $lang->line('msg_missing_api_key'),
      'missingClientId'   => $lang->line('msg_missing_client_id')
    ));
    
    // Prepare the member fields.
    $member_fields = $model->get_member_fields();
    $js_member_fields = array();
    
    foreach ($member_fields AS $member_field)
    {
      $js_member_fields[$member_field->get_id()] = $member_field->to_array();
    }
    
    $js->set_global('campaigner.memberFields',
      $js->generate_json($js_member_fields));

    $js->set_global('campaigner.ajaxUrl',
      str_replace(AMP, '&', BASE)
      .'&C=addons_extensions&M=extension_settings&file='
      .$lower_package_name
    );

    // Compile the JavaScript.
    $js->compile();
    
    // Load the view.
    return $this->_ee->load->view('settings', $view_vars, TRUE);
  }
  
  
  /**
   * Displays the "clients" settings form fragment. Should only ever be called from the
   * display_settings method, which takes care of testing for a valid API connector.
   *
   * @access  public
   * @return  string
   */
  public function display_settings_clients()
  {
    try
    {
      $view_vars = array(
        'clients'   => $this->_connector->get_clients(),
        'settings'  => $this->settings
      );  

      $view_name = '_clients';
      return $this->_ee->load->view($view_name, $view_vars, TRUE);
    }
    catch (Campaigner_exception $e)
    {
      $this->_ee->campaigner_model->log_error($e);
      return $this->display_error($e->getMessage(), $e->getCode());
    }
  }
  
  
  /**
   * Displays the "mailing lists" settings form fragment. Should only ever be called from
   * the display_settings method, which takes care of testing for a valid API connector.
   *
   * @access  public
   * @return  string
   */
  public function display_settings_mailing_lists()
  {
    $model = $this->_ee->campaigner_model;
    
    // Retrieve all the available mailing lists from the API.
    try
    {
      $lists = $this->_connector->get_client_lists($this->settings->get_client_id(), TRUE);
    }
    catch (Campaigner_exception $e)
    {
      $model->log_error($e);
      return $this->display_error($e->getMessage(), $e->getCode());
    }
        
    // Loop through the mailing lists. If we have settings for a list, make a note of them.
    foreach ($lists AS $list)
    {
      // If this list has not been previously saved, we're done.
      if ( ! ($saved_list = $this->settings->get_mailing_list_by_id($list->get_list_id())))
      {
        continue;
      }

      // Restore the saved list settings.
      $list->set_active(TRUE);
      $list->set_trigger_field($saved_list->get_trigger_field());
      $list->set_trigger_value($saved_list->get_trigger_value());

      // If this list has no custom fields, we're done.
      if ( ! ($fields = $list->get_custom_fields()))
      {
        continue;
      }

      // Restore the saved custom field settings.
      foreach ($fields AS $field)
      {
        if (($saved_field = $saved_list->get_custom_field_by_cm_key(
          $field->get_cm_key())
        ))
        {
          $field->set_member_field_id($saved_field->get_member_field_id());
        }
      }
    }
        
    // Retrieve the member fields.
    $member_fields = $model->get_member_fields();
        
    // Prepare the member fields data for use in a dropdown.
    $member_fields_dd_data = array();

    foreach ($member_fields AS $member_field)
    {
      $member_fields_dd_data[$member_field->get_id()] =
        $member_field->get_label();
    }
        
    // Define the view variables.
    $view_vars = array(
      'mailing_lists'         => $lists,
      'member_fields'         => $member_fields,
      'member_fields_dd_data' => $member_fields_dd_data,
      'settings'              => $this->settings
    );
    
    $view_name = '_mailing_lists';
    
    return $this->_ee->load->view($view_name, $view_vars, TRUE);
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
      $this->_ee->campaigner_model->save_extension_settings($this->settings);
      $this->_ee->session->set_flashdata('message_success', $this->_ee->lang->line('msg_settings_saved'));
    }
    catch (Campaigner_exception $e)
    {
      $this->_ee->session->set_flashdata(
          'message_failure',
          $this->_ee->lang->line('msg_settings_not_saved')
            .' (' .$e->getMessage() .')'
      );
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
    $this->_ee->load->helper('form');
    $this->_ee->load->library('table');
    
    // Define the navigation.
    $base_url = BASE .AMP .'C=addons_extensions' .AMP .'M=extension_settings'
      .AMP .'file=campaigner' .AMP .'tab=';
    
    $this->_ee->cp->set_right_nav(array(
      'nav_settings'  => $base_url .'settings',
      'nav_support'   => $this->_ee->campaigner_model->get_support_url()
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
    $model = $this->_ee->campaigner_model;

    // Get out early.
    if ( ! valid_int($member_id, 1))
    {
      $log_message = $this->_ee->lang->line('error_missing_or_invalid_member_id');
      $log_message .= ' ' .__METHOD__ .' (' .__LINE__ .')';

      $model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    // Retrieve the mailing lists to which the member should be subscribed.
    $lists = $model->get_member_subscribe_lists($member_id);

    foreach ($lists AS $list)
    {
      try
      {
        if ($subscriber = $model->get_member_as_subscriber(
          $member_id, $list->get_list_id())
        )
        {
          if ($this->_ee->extensions->active_hook('campaigner_subscribe_start') === TRUE)
          {
            $subscriber = $this->_ee->extensions->call(
              'campaigner_subscribe_start',
              $member_id, $subscriber
            );

            if ($this->_ee->extensions->end_script === TRUE)
            {
              return FALSE;
            }
          }

          $this->_connector->add_list_subscriber(
            $list->get_list_id(), $subscriber, $force_resubscribe);
        }
      }
      catch (Campaigner_exception $e)
      {
          $model->log_error($e, 3);
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
    $model = $this->_ee->campaigner_model;

    // Get out early.
    if ( ! valid_int($member_id, 1))
    {
      $log_message = $this->_ee->lang->line('error_missing_or_invalid_member_id');
      $log_message .= ' ' .__METHOD__ .' (' .__LINE__ .')';

      $model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    // Retrieve the member information.
    if ( ! $member_data = $model->get_member_by_id($member_id))
    {
      $log_message = $this->_ee->lang->line('error_unknown_member');
      $log_message .= ' ' .__METHOD__ .' (' .__LINE__ .')';

      $model->log_error(new Campaigner_exception($log_message), 3);
      return FALSE;
    }

    $lists = $model->get_all_mailing_lists();
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

      if ($model->member_should_be_subscribed_to_mailing_list(
        $member_data, $list))
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
        $model->log_error($e, 3);
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
    return $this->_ee->campaigner_model->update_extension(
      $installed_version, $this->version);
  }
  
  
  
  /* --------------------------------------------------------------
   * HOOK HANDLERS
   * ------------------------------------------------------------ */

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
    $this->subscribe_member($member_id);
  }
  
  
  /**
   * Handles the `cp_members_validate_members` hook. Used when the membership
   * preferences are set to "Manual activation by an administrator"
   * (i.e. req_mbr_activation = 'manual').
   *
   * @see     http://expressionengine.com/developers/extension_hooks/cp_members_validate_members/
   * @access  public
   * @return  void
   */
  public function on_cp_members_validate_members()
  {
    if ($this->_ee->config->item('req_mbr_activation') != 'manual'
      OR ! ($member_ids = $this->_ee->input->post('toggle')))
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
    if ($this->_ee->config->item('req_mbr_activation') != 'none')
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
    if ($this->_ee->config->item('req_mbr_activation') != 'email')
    {
      return;
    }
    
    $this->subscribe_member($member_id);
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
  public function on_user_edit_end(
    $member_id,
    Array $member_data,
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
    if ($this->_ee->config->item('req_mbr_activation') != 'none')
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
  public function on_zoo_visitor_cp_register_end(
    Array $member_data = array(),
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
  public function on_zoo_visitor_cp_update_end(
    Array $member_data = array(),
    $member_id = 0
  )
  {
    $this->unsubscribe_member($member_id);
    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `zoo_visitor_update_end` hook.
   *
   * @access  public
   * @param   Array         $member_data    The member data.
   * @param   int|string    $member_id      The member ID.
   * @return  void
   */
  public function on_zoo_visitor_update_end(
    Array $member_data = array(),
    $member_id = 0
  )
  {
    $this->unsubscribe_member($member_id);
    $this->subscribe_member($member_id);
  }


  /**
   * Handles the `zoo_visitor_register_end` hook.
   *
   * @access  public
   * @param   Array         $member_data    The member data.
   * @param   int|string    $member_id      The member ID.
   * @return  void
   */
  public function on_zoo_visitor_register_end(
    Array $member_data = array(),
    $member_id = 0
  )
  {
    /**
     * @todo  Check for activation.
     */

    $this->subscribe_member($member_id, TRUE);
  }


}


/* End of file      : ext.campaigner.php */
/* File location    : third_party/campaigner/ext.campaigner.php */
