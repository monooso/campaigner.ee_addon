<?php if ( ! defined('EXT')) exit('Invalid file request.');

/**
 * Campaigner add-on model.
 *
 * @author          : Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright       : Experience Internet
 * @package         : Campaigner
 * @version         : 4.1.0
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_cm_api_connector.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_exception.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_settings.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_trigger_field.php';
require_once PATH_THIRD .'campaigner/helpers/EI_number_helper.php';
require_once PATH_THIRD .'campaigner/helpers/EI_sanitize_helper.php';

/**
 * NOTE:
 * The Campaign Monitor API classes don't check whether a global variable has
 * already been declared before (re)declaring it. This causes problems with
 * mocks, when running the full test suite.
 *
 * It's not a high priority, just worthy of a note.
 */

require_once PATH_THIRD .'campaigner/libraries/createsend-php/csrest_clients.php';
require_once PATH_THIRD .'campaigner/libraries/createsend-php/csrest_general.php';
require_once PATH_THIRD .'campaigner/libraries/createsend-php/csrest_lists.php';
require_once PATH_THIRD .'campaigner/libraries/createsend-php/csrest_subscribers.php';

class Campaigner_model extends CI_Model {
    
  private $_ee;
  private $_extension_class;
  private $_extension_settings;
  private $_package_name;
  private $_package_version;
  private $_settings;
  private $_site_id;
  private $_theme_url;
  
  
  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @return  void
   */
  public function __construct()
  {
    parent::__construct();

    $this->_ee =& get_instance();
    
    $this->_package_name      = 'Campaigner';
    $this->_package_version   = '4.1.0';
    $this->_extension_class   = $this->get_package_name() .'_ext';

    // Load the OmniLogger class.
    if (file_exists(PATH_THIRD .'omnilog/classes/omnilogger.php'))
    {
      include_once PATH_THIRD .'omnilog/classes/omnilogger.php';
    }
  }
  
  
  /**
   * Activates the extension.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension()
  {
    $this->activate_extension_mailing_lists_table();
    $this->activate_extension_settings_table();
    $this->activate_extension_register_hooks();
  }
  
  
  /**
   * Creates the mailing lists table when the extension is activated.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension_mailing_lists_table()
  {
    // Shortcuts.
    $this->_ee->load->dbforge();
    $dbforge = $this->_ee->dbforge;
    
    // Table data.
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
    
    $dbforge->add_field($fields);
    $dbforge->add_key('list_id', TRUE);
    $dbforge->add_key('site_id', TRUE);
    $dbforge->create_table('campaigner_mailing_lists');
  }
  
  
  /**
   * Registers the extension hooks when the extension is activated.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension_register_hooks()
  {
    $hooks = array(
      'cp_members_member_create',
      'cp_members_validate_members',
      'member_member_register',
      'member_register_validate_members',
      'user_edit_end',
      'user_register_end',
      'zoo_visitor_cp_register_end',
      'zoo_visitor_cp_update_end',
      'zoo_visitor_register_end',
      'zoo_visitor_update_end'
    );
    
    $hook_data = array(
      'class'     => $this->get_extension_class(),
      'enabled'   => 'y',
      'hook'      => '',
      'method'    => '',
      'priority'  => 5,
      'settings'  => '',
      'version'   => $this->get_package_version()
    );
    
    foreach ($hooks AS $hook)
    {
      $hook_data['hook'] = $hook;
      $hook_data['method'] = 'on_' .$hook;
      
      $this->_ee->db->insert('extensions', $hook_data);
    }
  }
  
  
  /**
   * Creates the settings table when the extension is activated.
   *
   * @access  public
   * @return  void
   */
  public function activate_extension_settings_table()
  {
    // Shortcuts.
    $this->_ee->load->dbforge();
    $dbforge = $this->_ee->dbforge;
    
    // Table data.
    $fields = array(
      'site_id'   => array(
        'constraint'    => 5,
        'type'          => 'int',
        'unsigned'      => TRUE
      ),
      'api_key'   => array(
        'constraint'    => 50,
        'type'          => 'varchar'
      ),
      'client_id' => array(
        'constraint'    => 50,
        'type'          => 'varchar'
      )
    );
    
    $dbforge->add_field($fields);
    $dbforge->add_key('site_id', TRUE);
    $dbforge->create_table('campaigner_settings');
  }


  /**
   * Disables the extension.
   *
   * @access  public
   * @return  void
   */
  public function disable_extension()
  {
    $this->_ee->db->delete(
      'extensions',
      array('class' => $this->get_extension_class())
    );
    
    $this->_ee->load->dbforge();
    $this->_ee->dbforge->drop_table('campaigner_error_log');
    $this->_ee->dbforge->drop_table('campaigner_settings');
    $this->_ee->dbforge->drop_table('campaigner_mailing_lists');
  }


  /**
   * Returns an instance of the CM 'clients' API class. Used by the API
   * connector as a rather cumbersome factory, essentially. It will
   * suffice for now.
   *
   * @access  public
   * @param   string      $client_id      The client ID.
   * @return  CS_REST_Clients|FALSE
   */
  public function get_api_class_clients($client_id = '')
  {
    // Get out early.
    if ( ! $client_id OR ! is_string($client_id))
    {
      return FALSE;
    }

    $settings = $this->get_extension_settings();

    /**
     * Note that this method should only ever be called from
     * the API connector object, which wouldn't exist if the
     * API key wasn't set. Still, you can't be too careful.
     */

    return $settings->get_api_key()
      ? new CS_REST_Clients($client_id, $settings->get_api_key())
      : FALSE;
  }


  /**
   * Returns an instance of the CM 'general' API class.
   *
   * @access  public
   * @return  CS_REST_General|FALSE
   */
  public function get_api_class_general()
  {
    $settings = $this->get_extension_settings();

    /**
     * Note that this method should only ever be called from
     * the API connector object, which wouldn't exist if the
     * API key wasn't set. Still, you can't be too careful.
     */

    return $settings->get_api_key()
      ? new CS_REST_General($settings->get_api_key())
      : FALSE;
  }


  /**
   * Returns an instance of the CM 'lists' API class. 
   * 
   * @access  public
   * @param   string      $list_id        The list ID.
   * @return  CS_REST_Clients|FALSE
   */
  public function get_api_class_lists($list_id = '')
  {
    // Get out early.
    if ( ! $list_id OR ! is_string($list_id))
    {
      return FALSE;
    }

    $settings = $this->get_extension_settings();

    /**
     * Note that this method should only ever be called from
     * the API connector object, which wouldn't exist if the
     * API key wasn't set. Still, you can't be too careful.
     */

    return $settings->get_api_key()
      ? new CS_REST_Lists($list_id, $settings->get_api_key())
      : FALSE;
  }


  /**
   * Returns an instance of the CM 'subscribers' API class.
   * 
   * @access  public
   * @param   string      $list_id        The list ID.
   * @return  CS_REST_Subscribers|FALSE
   */
  public function get_api_class_subscribers($list_id = '')
  {
    // Get out early.
    if ( ! $list_id OR ! is_string($list_id))
    {
      return FALSE;
    }

    $settings = $this->get_extension_settings();

    /**
     * Note that this method should only ever be called from
     * the API connector object, which wouldn't exist if the
     * API key wasn't set. Still, you can't be too careful.
     */

    return $settings->get_api_key()
      ? new CS_REST_Subscribers($list_id, $settings->get_api_key())
      : FALSE;
  }


  /**
   * Returns an API connector. If the API key has not been saved, returns FALSE.
   *
   * @access  public
   * @return  Campaigner_api_connector|FALSE
   */
  public function get_api_connector()
  {
    $settings = $this->get_extension_settings();

    if ( ! $settings->get_api_key())
    {
      return FALSE;
    }

    /**
     * At present, this method simply returns the Campaigner Campaign Monitor
     * API connector subclass. In the future, it could return different
     * Campaigner_api_connector subclasses for different mailing list APIs,
     * based on the extension settings.
     */

    return new Campaigner_cm_api_connector(
      $this->_settings->get_api_key(),
      $this
    );
  }
  
  
  /**
   * Returns the documentation URL.
   *
   * @access  public
   * @return  string
   */
  public function get_docs_url()
  {
    return 'http://experienceinternet.co.uk/software/campaigner/docs/';
  }
  
  
  /**
   * Returns the extension class name. Assumed to be the package name,
   * with a `_ext` suffix.
   *
   * @access  public
   * @return  string
   */
  public function get_extension_class()
  {
    return $this->_extension_class;
  }
  
  
  /**
   * Returns the extension settings.
   *
   * @access  public
   * @return  Campaigner_settings
   */
  public function get_extension_settings()
  {
    if ( ! $this->_settings)
    {
      $this->_settings = $this->get_settings_from_db();
      $this->_settings->set_mailing_lists($this->get_all_mailing_lists());
    }
    
    return $this->_settings;
  }
  
  
  /**
   * Returns the installed extension version. If the extension is not
   * installed, returns an empty string.
   *
   * @access  public
   * @return  string
   */
  public function get_installed_extension_version()
  {
    $db = $this->_ee->db;
    
    $db_extension = $db->select('version')->get_where(
      'extensions',
      array('class' => $this->get_extension_class()),
      1
    );
    
    return $db_extension->num_rows()
      ? $db_extension->row()->version
      : '';
  }


  /**
   * Retrieves the mailing lists from the `campaigner_mailing_lists` table.
   *
   * @access  public
   * @return  array
   */
  public function get_all_mailing_lists()
  {
    $db_mailing_lists = $this->_ee->db->get_where(
      'campaigner_mailing_lists',
      array('site_id' => $this->get_site_id())
    );
    
    $mailing_lists = array();
    
    foreach ($db_mailing_lists->result_array() AS $db_mailing_list)
    {
      $mailing_lists[] = new Campaigner_mailing_list($db_mailing_list);
    }
    
    return $mailing_lists;
  }


  /**
   * Retrieves the mailing list with the specified ID. If no matching mailing
   * list is found, returns FALSE.
   *
   * @access  public
   * @param   string          $list_id        The mailing list ID.
   * @return  Campaigner_mailing_list|FALSE
   */
  public function get_mailing_list_by_id($list_id)
  {
    $site_id = $this->_ee->config->item('site_id');

    $db_list = $this->_ee->db
      ->select('custom_fields, list_id, site_id, trigger_field, trigger_value')
      ->get_where(
          'campaigner_mailing_lists',
          array('list_id' => $list_id, 'site_id' => $site_id),
          1
        );
    
    return $db_list->num_rows()
      ? new Campaigner_mailing_list($db_list->row_array())
      : FALSE;
  }
  

  /**
   * Returns a Campaigner_subscriber object for the specified member
   * and mailing list.
   *
   * @access  public
   * @param   int|string      $member_id      The member ID.
   * @param   string          $list_id        The list to which the member is
   *                                          being subscribed.
   * @return  Campaigner_subscriber|FALSE
   */
  public function get_member_as_subscriber($member_id, $list_id)
  {
    if ( ! $member_data = $this->get_member_by_id($member_id)
        OR ! $list = $this->get_mailing_list_by_id($list_id))
    {
      return FALSE;
    }

    if ( ! $this->member_should_be_subscribed_to_mailing_list(
      $member_data, $list))
    {
      return FALSE;
    }

    // Create the basic subscriber object.
    $subscriber = new Campaigner_subscriber(array(
      'email'     => $member_data['email'],
      'name'      => utf8_decode($member_data['screen_name'])
    ));

    // Add the custom field data.
    if ($custom_fields = $list->get_custom_fields())
    {
      foreach ($custom_fields AS $custom_field)
      {
        if (array_key_exists($custom_field->get_member_field_id(), $member_data))
        {
          $subscriber->add_custom_data(
            new Campaigner_subscriber_custom_data(array(
              'key'   => $custom_field->get_cm_key(),
              'value' => utf8_decode(
                $member_data[$custom_field->get_member_field_id()]
              )
            ))
          );
        }
      }
    }

    return $subscriber;
  }
  
  
  /**
   * Retrieves the information about the specified member from the database.
   *
   * @todo    Use a 'member' object, instead of the raw array.
   * @access  public
   * @param   int|string  $member_id  The member ID.
   * @return  array
   */
  public function get_member_by_id($member_id)
  {
    $member_data = array();
    
    // Get out early.
    if ( ! valid_int($member_id, 1))
    {
      return $member_data;
    }
    
    // Construct the query.
    $db_member = $this->_ee->db
      ->select(
        'members.email, members.group_id, members.location, members.member_id,
        members.occupation, members.screen_name, members.url, members.username,
        member_data.*')
      ->join('member_data', 'member_data.member_id = members.member_id', 'inner')
      ->get_where('members', array('members.member_id' => $member_id), 1);
    
    // Retrieve the member data.
    if ($db_member->num_rows())
    {
      $member_data = $db_member->row_array();
    }
    
    return $member_data;
  }
  
  
  /**
   * Retrieves the member fields from the database.
   *
   * @access  public
   * @return  array
   */
  public function get_member_fields()
  {
    // Shortcuts.
    $lang = $this->_ee->lang;
    
    $trigger_fields = array();
    $member_groups = array();

    // Retrieve the member groups.
    $db_member_groups = $this->_ee->db
      ->select('group_id, group_title')
      ->get('member_groups');

    foreach ($db_member_groups->result_array() AS $db_member_group)
    {
      $member_groups[] = new Campaigner_trigger_field_option(array(
        'id'    => $db_member_group['group_id'],
        'label' => $db_member_group['group_title']
      ));
    }
    
    // ExpressionEngine hard-codes these member fields, so we must do the same.
    $standard_member_fields = array(
      array(
        'id'        => 'group_id',
        'label'     => $lang->line('mbr_group_id'),
        'options'   => $member_groups,
        'type'      => 'select'
      ),
      array(
        'id'        => 'location',
        'label'     => $lang->line('mbr_location'),
        'options'   => array(),
        'type'      => 'text'
      ),
      array(
        'id'        => 'occupation',
        'label'     => $lang->line('mbr_occupation'),
        'options'   => array(),
        'type'      => 'text'
      ),
      array(
        'id'        => 'screen_name',
        'label'     => $lang->line('mbr_screen_name'),
        'options'   => array(),
        'type'      => 'text'
      ),
      array(
        'id'        => 'url',
        'label'     => $lang->line('mbr_url'),
        'options'   => array(),
        'type'      => 'text'
      ),
      array(
        'id'        => 'username',
        'label'     => $lang->line('mbr_username'),
        'options'   => array(),
        'type'      => 'text'
      )
    );
    
    foreach ($standard_member_fields AS $member_field_data)
    {
      $trigger_fields[] = new Campaigner_trigger_field($member_field_data);
    }
    
    // Load the custom member fields from the database.
    $db_member_fields = $this->_ee->db
      ->select("m_field_id, m_field_label, m_field_list_items, m_field_type")
      ->get('member_fields');
    
    foreach ($db_member_fields->result_array() AS $db_row)
    {
      $trigger_field_options = array();

      if ($db_row['m_field_type'] == Campaigner_trigger_field::DATATYPE_SELECT)
      {
        $list_items = explode("\n", $db_row['m_field_list_items']);
        foreach ($list_items AS $list_item)
        {
          $trigger_field_options[] = new Campaigner_trigger_field_option(array(
            'id'    => $list_item,
            'label' => $list_item
          ));
        }
      }

      $trigger_field = new Campaigner_trigger_field(array(
        'id'        => 'm_field_id_' .$db_row['m_field_id'],
        'label'     => $db_row['m_field_label'],
        'options'   => $trigger_field_options,
        'type'      => $db_row['m_field_type']
      ));

      $trigger_fields[] = $trigger_field;
    }
    
    return $trigger_fields;
  }


  /**
   * Returns an array of mailing list IDs to which the specified member
   * should be subscribed.
   *
   * @access  public
   * @param   int|string      $member_id      The member ID.
   * @return  array
   */
  public function get_member_subscribe_lists($member_id)
  {
    if ( ! ($member_data = $this->get_member_by_id($member_id))
      OR ! ($lists = $this->get_all_mailing_lists()))
    {
      return array();
    }

    $subscribe_lists = array();
    
    foreach ($lists AS $list)
    {
      if ($this->member_should_be_subscribed_to_mailing_list(
        $member_data,
        $list
      ))
      {
        $subscribe_lists[] = $list;
      }
    }
    
    return $subscribe_lists;
  }


  /**
   * Returns the package name.
   *
   * @access  public
   * @return  string
   */
  public function get_package_name()
  {
    return $this->_package_name;
  }
  
  
  /**
   * Returns the package version.
   *
   * @access  public
   * @return  string
   */
  public function get_package_version()
  {
    return $this->_package_version;
  }
  
  
  /**
   * Retrieves the settings from the `campaigner_settings` table.
   *
   * @access  public
   * @return  Campaigner_settings
   */
  public function get_settings_from_db()
  {
    $db_settings = $this->_ee->db->get_where(
      'campaigner_settings',
      array('site_id' => $this->get_site_id()),
      1
    );
    
    $settings_data = $db_settings->num_rows()
      ? $db_settings->row_array()
      : array();
    
    return new Campaigner_settings($settings_data);
  }
  
  
  /**
   * Returns the site ID.
   *
   * @access  public
   * @return  string
   */
  public function get_site_id()
  {
    if ( ! $this->_site_id)
    {
      $this->_site_id = $this->_ee->config->item('site_id');
    }
    
    return $this->_site_id;
  }
  
  
  /**
   * Returns the support URL.
   *
   * @access  public
   * @return  string
   */
  public function get_support_url()
  {
    return 'http://support.experienceinternet.co.uk/discussions/campaigner/';
  }
  
  
  /**
   * Returns the package theme URL.
   *
   * @access  public
   * @return  string
   */
  public function get_theme_url()
  {
    if ( ! $this->_theme_url)
    {
      $theme_url = $this->_ee->config->item('theme_folder_url');
      $theme_url = substr($theme_url, -1) == '/'
        ? $theme_url .'third_party/'
        : $theme_url .'/third_party/';
      
      $this->_theme_url = $theme_url .strtolower($this->get_package_name()) .'/';
    }
    
    return $this->_theme_url;
  }


  /**
   * Logs an error to OmniLog.
   *
   * @access  public
   * @param   Campaigner_exception        $exception          The error details.
   * @param   int                         $severity           The error 'level'.
   * @return  void
   */
  public function log_error(Campaigner_exception $exception, $severity = 1)
  {
    if (class_exists('Omnilog_entry') && class_exists('Omnilogger'))
    {
      switch ($severity)
      {
        case 3:
          $notify = TRUE;
          $type   = Omnilog_entry::ERROR;
          break;

        case 2:
          $notify = FALSE;
          $type   = Omnilog_entry::WARNING;
          break;

        case 1:
        default:
          $notify = FALSE;
          $type   = Omnilog_entry::NOTICE;
          break;
      }

      $omnilog_entry = new Omnilog_entry(array(
        'addon_name'    => $this->get_package_name(),
        'date'          => time(),
        'message'       => $exception->getMessage(),
        'notify_admin'  => $notify,
        'type'          => $type
      ));

      OmniLogger::log($omnilog_entry);
    }
  }
  
  
  /**
   * Determines whether the specified member should be subscribed to the
   * specified mailing list, based on the value (or absence of) the list
   * trigger field.
   *
   * @access  public
   * @param   array                     $member_data    The member data, as
   *                                                    returned from
   *                                                    'get_member_by_id'.
   * @param   Campaigner_mailing_list   $list           The mailing list.
   * @return  bool
   */
  public function member_should_be_subscribed_to_mailing_list(
    Array $member_data,
    Campaigner_mailing_list $mailing_list
  )
  {
    // If there is no trigger field, our job is easy.
    if ( ! $mailing_list->get_trigger_field())
    {
      return TRUE;
    }

    // Check the trigger field.
    return isset($member_data[$mailing_list->get_trigger_field()])
      && $member_data[$mailing_list->get_trigger_field()] == $mailing_list->get_trigger_value();
  }
  
  
  /**
   * Saves the extension settings.
   *
   * @access  public
   * @param   Campaigner_settings     $settings       The settings to save.
   * @return  void
   */
  public function save_extension_settings(Campaigner_settings $settings)
  {
    if ( ! $this->save_settings_to_db($settings))
    {
      throw new Campaigner_exception($this->_ee->lang->line('settings_not_saved'));
    }
    
    if ( ! $this->save_mailing_lists_to_db($settings))
    {
      throw new Campaigner_exception($this->_ee->lang->line('mailing_lists_not_saved'));
    }
  }
  
  
  /**
   * Saves the supplied mailing lists to the database.
   *
   * @access  public
   * @param   Campaigner_settings     $settings       The settings.
   * @return  bool
   */
  public function save_mailing_lists_to_db(Campaigner_settings $settings)
  {
    $db = $this->_ee->db;
    $site_id = $this->_ee->config->item('site_id');
    
    // Delete the existing settings.
    $db->delete('campaigner_mailing_lists', array('site_id' => $site_id));
    
    // Add the mailing lists.
    $mailing_lists = $settings->get_mailing_lists();
    $success = TRUE;
    
    foreach ($mailing_lists AS $mailing_list)
    {
      $custom_field_data = array();
      
      foreach ($mailing_list->get_custom_fields() AS $custom_field)
      {
        $custom_field_array = $custom_field->to_array();
        unset($custom_field_array['label']);
        
        $custom_field_data[] = $custom_field_array;
      }
      
      $mailing_list_data = array(
        'custom_fields' => serialize($custom_field_data),
        'list_id'       => $mailing_list->get_list_id(),
        'site_id'       => $site_id,
        'trigger_field' => $mailing_list->get_trigger_field(),
        'trigger_value' => $mailing_list->get_trigger_value()
      );
      
      $db->insert('campaigner_mailing_lists', $mailing_list_data);
      
      if ($db->affected_rows() !== 1)
      {
        $success = FALSE;
        break;
      }
    }
    
    // One bad badger sullies the set.
    if ( ! $success)
    {
      $db->delete('campaigner_mailing_lists', array('site_id' => $site_id));
    }
    
    return $success;
  }
  
  
  /**
   * Saves the supplied settings to the database.
   *
   * @access  public
   * @param   Campaigner_settings     $settings       The settings.
   * @return  bool
   */
  public function save_settings_to_db(Campaigner_settings $settings)
  {
    $db = $this->_ee->db;
    $site_id = $this->_ee->config->item('site_id');
    
    // Delete any existing site settings.
    $db->delete('campaigner_settings', array('site_id' => $site_id));
    
    /**
     * Retrieve the basic settings, remove the mailing lists (which
     * are handled separately), and add the site ID.
     */
    
    $settings_data = $settings->to_array();
    unset($settings_data['mailing_lists']);
    $settings_data = array_merge(array('site_id' => $site_id), $settings_data);
    
    // Save the settings to the database.
    $db->insert('campaigner_settings', $settings_data);
    
    return (bool) $db->affected_rows();
  }


  /**
   * Updates the basic settings using input data.
   *
   * @access  public
   * @param   Campaigner_settings   $settings   The settings object to update.
   * @return  Campaigner_settings
   */
  public function update_basic_settings_from_input(Campaigner_settings $settings)
  {
    $input  = $this->_ee->input;
    $props  = array('api_key', 'client_id');
    
    foreach ($props AS $prop)
    {
      if ($prop_val = $input->get_post($prop))
      {
        $prop_method = 'set_' .$prop;
        $settings->$prop_method($prop_val);
      }
    }
    
    return $settings;
  }
  
  
  /**
   * Updates the extension.
   *
   * @access  public
   * @param   string      $installed_version      The installed version.
   * @param   string      $package_version        The package version.
   * @return  bool|void
   */
  public function update_extension($installed_version = '', $package_version = '')
  {
    if ( ! $installed_version OR version_compare($installed_version, $package_version, '>='))
    {
      return FALSE;
    }

    // Version 4.0.
    if (version_compare($installed_version, '4.0', '<'))
    {
      $this->_ee->db->update(
        'extensions',
        array('priority' => 5),
        array('class' => $this->get_extension_class())
      );
    }

    // Version 4.1.
    if (version_compare($installed_version, '4.1', '<'))
    {
      $this->_ee->db->query('ALTER TABLE exp_campaigner_mailing_lists
        DROP PRIMARY KEY');

      $this->_ee->db->query('ALTER TABLE exp_campaigner_mailing_lists
        ADD PRIMARY KEY (list_id, site_id)');
    }
    
    // Update the extension version in the database.
    $this->_ee->db->update(
      'extensions',
      array('version' => $package_version),
      array('class' => $this->get_extension_class())
    );
  }
  
  
  /**
   * Updates the settings from any input data.
   *
   * @access  public
   * @param   Campaigner_settings   $settings   The settings object to update.
   * @return  Campaigner_settings
   */
  public function update_extension_settings_from_input(
    Campaigner_settings $settings
  )
  {
    return $this->update_mailing_list_settings_from_input(
      $this->update_basic_settings_from_input($settings));
  }
  
  
  /**
   * Updates the mailing list settings using input (GET / POST) data. Note that
   * "update" is something of a misnomer, used for consistency. In actuality,
   * any existing mailing lists are discarded.
   *
   * @access  public
   * @param   Campaigner_settings   $settings   The settings object to update.
   * @return  Campaigner_settings
   */
  public function update_mailing_list_settings_from_input(
    Campaigner_settings $settings
  )
  {
    // Get out early.
    if ( ! ($input_lists = $this->_ee->input->get_post('mailing_lists')))
    {
      return $settings;
    }
    
    $mailing_lists = array();
    
    foreach ($input_lists AS $input_list)
    {
      // Only interested in "selected" mailing lists.
      if ( ! is_array($input_list) OR ! isset($input_list['checked']))
      {
        continue;
      }
      
      // The basics.
      $mailing_list = new Campaigner_mailing_list(array(
        'list_id'       => $input_list['checked'],
        'trigger_field' => isset($input_list['trigger_field'])
          ? $input_list['trigger_field']
          : '',
        'trigger_value' => isset($input_list['trigger_value'])
          ? $input_list['trigger_value']
          : ''
      ));
      
      // Custom fields.
      if (isset($input_list['custom_fields']))
      {
        $input_custom_fields = $input_list['custom_fields'];
        
        foreach ($input_custom_fields AS $cm_key => $member_field_id)
        {
          // If no member field is associated with the custom field, ignore it.
          if ( ! $member_field_id)
          {
            continue;
          }
          
          // "Desanitize" the Campaign Monitor key, and create the custom field.
          $mailing_list->add_custom_field(new Campaigner_custom_field(array(
            'cm_key'            => desanitize_string($cm_key),
            'member_field_id'   => $member_field_id
          )));
        }
      }
      
      $mailing_lists[] = $mailing_list;
    }
    
    $settings->set_mailing_lists($mailing_lists);
    return $settings;
  }


}


/* End of file      : campaigner_model.php */
/* File location    : third_party/campaigner/models/campaigner_model.php */
