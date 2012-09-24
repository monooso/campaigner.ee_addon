<?php if ( ! defined('EXT')) exit('Invalid file request.');

/**
 * Campaigner add-on model.
 *
 * @author          Stephen Lewis (http://github.com/experience/)
 * @copyright       Experience Internet
 * @package         Campaigner
 */

require_once dirname(__FILE__) .'/../config.php';
require_once dirname(__FILE__) .'/../classes/campaigner_cm_api_connector.php';
require_once dirname(__FILE__) .'/../classes/campaigner_exception.php';
require_once dirname(__FILE__) .'/../classes/campaigner_mailing_list.php';
require_once dirname(__FILE__) .'/../classes/campaigner_settings.php';
require_once dirname(__FILE__) .'/../classes/campaigner_subscriber.php';
require_once dirname(__FILE__) .'/../classes/campaigner_trigger_field.php';
require_once dirname(__FILE__) .'/../helpers/EI_number_helper.php';
require_once dirname(__FILE__) .'/../helpers/EI_sanitize_helper.php';

// There may be other add-ons using the CreateSend library classes.
if ( ! class_exists('CS_REST_Clients'))
{
  require_once dirname(__FILE__) .'/../libraries/createsend-php/csrest_clients.php';
}

if ( ! class_exists('CS_REST_General'))
{
  require_once dirname(__FILE__) .'/../libraries/createsend-php/csrest_general.php';
}

if ( ! class_exists('CS_REST_Lists'))
{
  require_once dirname(__FILE__) .'/../libraries/createsend-php/csrest_lists.php';
}

if ( ! class_exists('CS_REST_Subscribers'))
{
  require_once dirname(__FILE__) .'/../libraries/createsend-php/csrest_subscribers.php';
}

class Campaigner_model extends CI_Model {

  private $EE;

  private $_extension_class;
  private $_extension_settings;
  private $_namespace;
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
   * @param   string    $package_name     Package name. Used for testing.
   * @param   string    $package_version  Package version. Used for testing.
   * @param   string    $namespace        Session namespace. Used for testing.
   * @return  void
   */
  public function __construct($package_name = '', $package_version = '',
    $namespace = ''
  )
  {
    parent::__construct();

    $this->EE =& get_instance();

    // Load the OmniLogger class.
    if (file_exists(PATH_THIRD .'omnilog/classes/omnilogger.php'))
    {
      include_once PATH_THIRD .'omnilog/classes/omnilogger.php';
    }

    $this->_namespace = $namespace
      ? strtolower($namespace)
      : 'experience';

    /**
     * Constants defined in the NSM Add-on Updater config.php file, so we don't 
     * have the package name and version defined in multiple locations.
     */

    $this->_package_name = $package_name
      ? strtolower($package_name)
      : strtolower(CAMPAIGNER_NAME);

    $this->_package_version = $package_version
      ? $package_version
      : CAMPAIGNER_VERSION;

    $this->_extension_class = $this->get_package_name() .'_ext';

    // Initialise the add-on cache.
    $cache =& $this->EE->session->cache;

    if ( ! array_key_exists($this->_namespace, $cache))
    {
      $cache[$this->_namespace] = array();
    }

    if ( ! array_key_exists($this->_package_name, $cache[$this->_namespace]))
    {
      $cache[$this->_namespace][$this->_package_name] = array();
    }

    if ( ! array_key_exists($this->get_site_id(),
      $cache[$this->_namespace][$this->_package_name])
    )
    {
      $cache[$this->_namespace][$this->_package_name][$this->get_site_id()]
        = array();
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
    $this->EE->load->dbforge();
    $dbforge = $this->EE->dbforge;

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
      'class'     => ucfirst($this->get_extension_class()),
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

      $this->EE->db->insert('extensions', $hook_data);
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
    $this->EE->load->dbforge();
    $dbforge = $this->EE->dbforge;

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
    $this->EE->db->delete(
      'extensions',
      array('class' => $this->get_extension_class())
    );

    $this->EE->load->dbforge();
    $this->EE->dbforge->drop_table('campaigner_error_log');
    $this->EE->dbforge->drop_table('campaigner_settings');
    $this->EE->dbforge->drop_table('campaigner_mailing_lists');
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
    $db = $this->EE->db;

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
    $db_mailing_lists = $this->EE->db->get_where(
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
    $site_id = $this->EE->config->item('site_id');

    $db_list = $this->EE->db
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
   * @param   Array                     $member_data    Member data array.
   * @param   Campaigner_mailing_list   $list_id        The target mailing list.
   * @return  Campaigner_subscriber|FALSE
   */
  public function get_member_as_subscriber(Array $member_data,
    Campaigner_mailing_list $list
  )
  {
    // At the bare minimum, we need an email and screen name.
    if ( ! array_key_exists('email', $member_data)
      OR ! array_key_exists('screen_name', $member_data)
      OR ! $member_data['email']
      OR ! $member_data['screen_name']
    )
    {
      return FALSE;
    }

    // Create the basic subscriber object.
    $subscriber = new Campaigner_subscriber(array(
      'email' => $member_data['email'],
      'name'  => utf8_decode($member_data['screen_name'])
    ));

    // Add the custom field data.
    if ($custom_fields = $list->get_custom_fields())
    {
      foreach ($custom_fields AS $custom_field)
      {
        if ( ! isset($member_data[$custom_field->get_member_field_id()]))
        {
          continue;
        }

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

    /**
     * Retrieve the member fields. We do all of this up-front, because the CI 
     * Active Record class doesn't handle "overlapping" queries.
     */

    $default_fields = $this->get_member_fields__default_member();
    $custom_fields  = $this->get_member_fields__custom_member();
    $zoo_fields     = $this->get_member_fields__zoo_visitor();

    /**
     * Start building the query.
     */

    // Default fields.
    foreach ($default_fields AS $m_field)
    {
      $this->EE->db->select('members.' .$m_field->get_id());
    }

    // Custom fields.
    if ($custom_fields)
    {
      $this->EE->db->join('member_data',
        'member_data.member_id = members.member_id', 'inner');

      foreach ($custom_fields AS $m_field)
      {
        $this->EE->db->select('member_data.' .$m_field->get_id());
      }
    }

    // Zoo Visitor fields.
    if ($this->is_zoo_visitor_installed() && $zoo_fields)
    {
      $this->EE->db->join('channel_titles',
        'channel_titles.author_id = members.member_id', 'inner');

      $this->EE->db->join('channel_data',
        'channel_data.entry_id = channel_titles.entry_id', 'inner');

      foreach ($zoo_fields AS $m_field)
      {
        $this->EE->db->select('channel_data.' .$m_field->get_id());
      }
    }

    // Run the query.
    $db_result = $this->EE->db->get_where('members',
      array('members.member_id' => $member_id), 1);

    return $db_result->num_rows()
      ? $db_result->row_array()
      : $member_data;
  }


  /**
   * Retrieves the custom member fields.
   *
   * @access  public
   * @return  array
   */
  public function get_member_fields__custom_member()
  {
    $fields = array();

    $db_fields = $this->EE->db
      ->select('m_field_id, m_field_label, m_field_list_items, m_field_type')
      ->get('member_fields');

    foreach ($db_fields->result() AS $db_row)
    {
      $field = new Campaigner_trigger_field(array(
        'id'    => 'm_field_id_' .$db_row->m_field_id,
        'label' => $db_row->m_field_label,
        'type'  => $db_row->m_field_type
      ));

      if ($db_row->m_field_type == Campaigner_trigger_field::DATATYPE_SELECT)
      {
        $list_items = explode("\n", $db_row->m_field_list_items);

        foreach ($list_items AS $list_item)
        {
          $field->add_option(new Campaigner_trigger_field_option(array(
            'id'    => $list_item,
            'label' => $list_item
          )));
        }
      }

      $fields[] = $field;
    }

    return $fields;
  }


  /**
   * Retrieves the default member fields.
   *
   * @access  public
   * @return  array
   */
  public function get_member_fields__default_member()
  {
    // Retrieve the Member Group IDs.
    $group_id_field = new Campaigner_trigger_field(array(
      'id'      => 'group_id',
      'label'   => $this->EE->lang->line('mbr_group_id'),
      'options' => array(),
      'type'    => 'select'
    ));

    $db_groups = $this->EE->db
      ->select('group_id, group_title')
      ->get('member_groups');

    foreach ($db_groups->result() AS $db_group)
    {
      $group_id_field->add_option(new Campaigner_trigger_field_option(array(
        'id'    => $db_group->group_id,
        'label' => $db_group->group_title
      )));
    }

    // ExpressionEngine hard-codes these member fields, so we must do the same.
    return array(
      $group_id_field,
      new Campaigner_trigger_field(array(
        'id'      => 'email',
        'label'   => $this->EE->lang->line('mbr_email'),
        'type'    => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'location',
        'label'   => $this->EE->lang->line('mbr_location'),
        'type'    => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'occupation',
        'label'   => $this->EE->lang->line('mbr_occupation'),
        'type'    => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'screen_name',
        'label'   => $this->EE->lang->line('mbr_screen_name'),
        'type'    => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'url',
        'label'   => $this->EE->lang->line('mbr_url'),
        'type'    => 'text'
      )),
      new Campaigner_trigger_field(array(
        'id'      => 'username',
        'label'   => $this->EE->lang->line('mbr_username'),
        'type'    => 'text'
      ))
    );
  }


  /**
   * Retrieves the Zoo Visitor member fields.
   *
   * @access  public
   * @return  array
   */
  public function get_member_fields__zoo_visitor()
  {
    $fields = array();

    if ( ! $this->is_zoo_visitor_installed())
    {
      return $fields;
    }

    $query_fields = array(
      'channel_fields.field_id',
      'channel_fields.field_label',
      'channel_fields.field_list_items',
      'channel_fields.field_name',
      'channel_fields.field_type'
    );

    $db_result = $this->EE->db
      ->select(implode(', ', $query_fields))
      ->from('channel_fields')
      ->join('channels',
        'channels.field_group = channel_fields.group_id', 'inner')
      ->join('zoo_visitor_settings',
        'zoo_visitor_settings.var_value = channels.channel_id', 'inner')
      ->where('zoo_visitor_settings.site_id', $this->get_site_id())
      ->where('zoo_visitor_settings.var', 'member_channel_id')
      ->where('channel_fields.field_type !=', 'zoo_visitor')
      ->get();

    if ( ! $db_result->num_rows())
    {
      return $fields;
    }

    foreach ($db_result->result() AS $db_row)
    {
      $field = new Campaigner_trigger_field(array(
        'id'      => 'field_id_' .$db_row->field_id,
        'label'   => $db_row->field_label,
        'options' => array(),
        'type'    => $db_row->field_type
      ));

      if ($db_row->field_type === Campaigner_trigger_field::DATATYPE_SELECT)
      {
        $field_options = explode("\n", $db_row->field_list_items);

        foreach ($field_options AS $field_option)
        {
          $field->add_option(new Campaigner_trigger_field_option(array(
            'id'    => $field_option,
            'label' => $field_option
          )));
        }
      }

      $fields[] = $field;
    }

    return $fields;
  }


  /**
   * Returns an array of mailing list IDs to which the specified member
   * should be subscribed.
   *
   * @access  public
   * @param   array     $member_data    Associative array of member data.
   * @param   array     $lists          Array of Campaigner_mailing_list.
   * @return  array
   */
  public function get_member_subscribe_lists(Array $member_data, Array $lists)
  {
    $subscribe_lists = array();

    foreach ($lists AS $list)
    {
      if ( ! $list instanceof Campaigner_mailing_list)
      {
        continue;
      }

      if ($this->member_should_be_subscribed_to_mailing_list(
        $member_data, $list)
      )
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
    $db_settings = $this->EE->db->get_where(
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
      $this->_site_id = $this->EE->config->item('site_id');
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
    return 'mailto:support@experienceinternet.co.uk?subject=Campaigner Support';
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
      $theme_url = $this->EE->config->item('theme_folder_url');
      $theme_url = substr($theme_url, -1) == '/'
        ? $theme_url .'third_party/'
        : $theme_url .'/third_party/';

      $this->_theme_url = $theme_url .strtolower($this->get_package_name()) .'/';
    }

    return $this->_theme_url;
  }


  /**
   * Determines whether the Zoo Visitor module is installed and activated
   * for the current site.
   *
   * @access  public
   * @return  bool
   */
  public function is_zoo_visitor_installed()
  {
    $cache =& $this->_get_package_cache();

    // Use the cache whenever possible.
    if (array_key_exists('is_zoo_visitor_installed', $cache))
    {
      return $cache['is_zoo_visitor_installed'];
    }

    // Is the Zoo Visitor module installed?
    if ($this->EE->db
      ->where('LOWER(module_name)', 'zoo_visitor')
      ->count_all_results('modules') !== 1
    )
    {
      return $cache['is_zoo_visitor_installed'] = FALSE;
    }

    // Does the Zoo Visitor settings table exist?
    if ( ! $this->EE->db->table_exists('zoo_visitor_settings'))
    {
      return $cache['is_zoo_visitor_installed'] = FALSE;
    }

    // Is Zoo Visitor configured?
    if ($this->EE->db
      ->where('site_id', $this->get_site_id())
      ->where('var', 'member_channel_id')
      ->where('var_value !=', '')
      ->count_all_results('zoo_visitor_settings') !== 1
    )
    {
      return $cache['is_zoo_visitor_installed'] = FALSE;
    }

    return $cache['is_zoo_visitor_installed'] = TRUE;
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
   * @param   array     $member_data    The member data, as returned from 
   *                                    'get_member_by_id'.
   * @param   Campaigner_mailing_list   $list     The mailing list.
   * @return  bool
   */
  public function member_should_be_subscribed_to_mailing_list(
    Array $member_data,
    Campaigner_mailing_list $mailing_list
  )
  {
    /**
     * Third-party extensions can override the behaviour of this method.
     * This is particularly handy when dealing with custom member fields
     * that may contain multiple values. For example:
     *
     * $preferred_colors = 'R|G|B';
     *
     * By default, trigger value of 'R' will fail, even though it is one
     * of the preferred colours. A third-party extension can be used to
     * handle such situations.
     */

    if ($this->EE->extensions->active_hook(
      'campaigner_should_subscribe_member') === TRUE)
    {
      $subscribe = $this->EE->extensions->call(
        'campaigner_should_subscribe_member',
        $member_data, $mailing_list
      );

      return (bool) $subscribe;
    }

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
      throw new Campaigner_exception($this->EE->lang->line('settings_not_saved'));
    }

    if ( ! $this->save_mailing_lists_to_db($settings))
    {
      throw new Campaigner_exception($this->EE->lang->line('mailing_lists_not_saved'));
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
    $db = $this->EE->db;
    $site_id = $this->EE->config->item('site_id');

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
    $db = $this->EE->db;
    $site_id = $this->EE->config->item('site_id');

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
    $input  = $this->EE->input;
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
    if ( ! $installed_version
      OR version_compare($installed_version, $package_version, '>=')
    )
    {
      return FALSE;
    }

    $class = ucfirst($this->get_extension_class());

    // Version 4.0.0
    if (version_compare($installed_version, '4.0.0', '<'))
    {
      $this->EE->db->update('extensions', array('priority' => 5),
        array('class' => $class));
    }

    // Version 4.1.0
    if (version_compare($installed_version, '4.1.0', '<'))
    {
      $this->EE->db->query('ALTER TABLE exp_campaigner_mailing_lists
        DROP PRIMARY KEY');

      $this->EE->db->query('ALTER TABLE exp_campaigner_mailing_lists
        ADD PRIMARY KEY (list_id, site_id)');
    }

    // Version 4.2.0 adds support for Zoo Visitor.
    if (version_compare($installed_version, '4.2.0', '<'))
    {
      $hooks = array(
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
        'version'   => $package_version
      );

      foreach ($hooks AS $hook)
      {
        $hook_data['hook'] = $hook;
        $hook_data['method'] = 'on_' .$hook;

        $this->EE->db->insert('extensions', $hook_data);
      }
    }

    // Version 4.4.0 adds support for CartThrob.
    if (version_compare($installed_version, '4.4.0b1', '<'))
    {
      $this->EE->db->insert('extensions', array(
        'class'     => $class,
        'enabled'   => 'y',
        'hook'      => 'cartthrob_on_authorize',
        'method'    => 'on_cartthrob_on_authorize',
        'priority'  => 5,
        'settings'  => '',
        'version'   => $package_version
      ));
    }

    // Version 4.5.0 adds support for Membrr.
    if (version_compare($installed_version, '4.5.0b1', '<'))
    {
      $this->EE->db->insert('extensions', array(
        'class'     => $class,
        'enabled'   => 'y',
        'hook'      => 'membrr_subscribe',
        'method'    => 'on_membrr_subscribe',
        'priority'  => 5,
        'settings'  => '',
        'version'   => $package_version
      ));
    }

    // Update the extension version in the database.
    $this->EE->db->update(
      'extensions',
      array('version' => $package_version),
      array('class' => $class)
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
    if ( ! ($input_lists = $this->EE->input->get_post('mailing_lists')))
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


  /* --------------------------------------------------------------
   * PROTECTED METHODS
   * ------------------------------------------------------------ */

  /**
   * Returns a reference to the package cache for the current site. Should be 
   * called as follows: $cache =& $this->_get_package_cache();
   *
   * @access  protected
   * @return  array
   */
  protected function &_get_package_cache()
  {
    return $this->EE->session->cache[$this->_namespace]
      [$this->_package_name][$this->get_site_id()];
  }


}


/* End of file      : campaigner_model.php */
/* File location    : third_party/campaigner/models/campaigner_model.php */
