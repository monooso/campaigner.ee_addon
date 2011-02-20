<?php if ( ! defined('EXT')) exit('Invalid file request.');

/**
 * Campaigner add-on model.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 * @version 		: 3.0.2
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_cm_api_connector' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_error_log_entry' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_exception' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_settings' .EXT;

require_once PATH_THIRD .'campaigner/classes/EI_member_field' .EXT;
require_once PATH_THIRD .'campaigner/helpers/EI_number_helper' .EXT;
require_once PATH_THIRD .'campaigner/helpers/EI_sanitize_helper' .EXT;

class Campaigner_model extends CI_Model {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * ExpressionEngine object reference.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_ee;
	
	/**
	 * Extension class. Assumed to be the package name,
	 * with an `_ext` suffix.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_extension_class;
	
	/**
	 * Extension settings.
	 *
	 * @access	private
	 * @var		Campaigner_settings
	 */
	private $_extension_settings;
	
	/**
	 * Package name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_package_name;
	
	/**
	 * Package version.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_package_version;
	
	/**
	 * The extension settings.
	 *
	 * @access	private
	 * @var		Campaigner_settings
	 */
	private $_settings;
	
	/**
	 * The site ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_site_id;
	
	/**
	 * Package theme URL.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_theme_url;
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Constructor.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_ee =& get_instance();
		
		$this->_package_name		= 'Campaigner';
		$this->_package_version		= '3.0.2';
		$this->_extension_class 	= $this->get_package_name() .'_ext';
		
		/**
		 * The model is still loaded even if the extension isn't installed.
		 * If any database action is taking place in the constructor, first
		 * check to ensure that we're live.
		 */
		
		if ( ! isset($this->_ee->extensions->version_numbers[$this->get_extension_class()]))
		{
			return;
		}
	}
	
	
	/**
	 * Activates the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$this->activate_extension_error_log_table();
		$this->activate_extension_mailing_lists_table();
		$this->activate_extension_settings_table();
		$this->activate_extension_register_hooks();
	}
	
	
	/**
	 * Creates the error log table when the extension is activated.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension_error_log_table()
	{
		// Shortcuts.
		$this->_ee->load->dbforge();
		$dbforge = $this->_ee->dbforge;
		
		// Table data.
		$fields = array(
			'error_log_id' => array(
				'auto_increment' => TRUE,
				'constraint'	=> 10,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'site_id' => array(
				'constraint'	=> 5,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'error_date' => array(
				'constraint'	=> 10,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'error_code' => array(
				'constraint'	=> 3,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'error_message' => array(
				'constraint'	=> 255,
				'type'			=> 'varchar'
			)
		);
		
		$dbforge->add_field($fields);
		$dbforge->add_key('error_log_id', TRUE);
		$dbforge->create_table('campaigner_error_log');
	}
	
	
	/**
	 * Creates the mailing lists table when the extension is activated.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension_mailing_lists_table()
	{
		// Shortcuts.
		$this->_ee->load->dbforge();
		$dbforge = $this->_ee->dbforge;
		
		// Table data.
		$fields = array(
			'list_id' => array(
				'constraint'	=> 50,
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
			'trigger_field' => array(
				'constraint'	=> 50,
				'type'			=> 'varchar'
			),
			'trigger_value' => array(
				'constraint'	=> 255,
				'type'			=> 'varchar'
			)
		);
		
		$dbforge->add_field($fields);
		$dbforge->add_key('list_id', TRUE);
		$dbforge->create_table('campaigner_mailing_lists');
	}
	
	
	/**
	 * Registers the extension hooks when the extension is activated.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension_register_hooks()
	{
		$hooks = array(
			'cp_members_member_create',
			'cp_members_validate_members',
			'member_member_register',
			'member_register_validate_members',
			'user_edit_end',
			'user_register_end'
		);
		
		$hook_data = array(
			'class'		=> $this->get_extension_class(),
			'enabled'	=> 'y',
			'hook'		=> '',
			'method'	=> '',
			'priority'	=> 10,
			'settings'	=> '',
			'version'	=> $this->get_package_version()
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
	 * @access	public
	 * @return	void
	 */
	public function activate_extension_settings_table()
	{
		// Shortcuts.
		$this->_ee->load->dbforge();
		$dbforge = $this->_ee->dbforge;
		
		// Table data.
		$fields = array(
			'site_id'	=> array(
				'constraint'	=> 5,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'api_key'	=> array(
				'constraint'	=> 50,
				'type'			=> 'varchar'
			),
			'client_id'	=> array(
				'constraint'	=> 50,
				'type'			=> 'varchar'
			)
		);
		
		$dbforge->add_field($fields);
		$dbforge->add_key('site_id', TRUE);
		$dbforge->create_table('campaigner_settings');
	}


	/**
	 * Converts a mailing list database row to a Camapigner_mailing_list object.
	 *
	 * @access	public
	 * @param	array 		$row		The database row.
	 * @return	Campaigner_mailing_list
	 */
	public function convert_mailing_list_row_to_object(Array $row)
	{
		$fields = array();
		$fields_data = unserialize($row['custom_fields']);

		if (is_array($fields_data))
		{
			foreach ($fields_data AS $field_data)
			{
				$fields[] = new Campaigner_custom_field($field_data);
			}
		}

		return new Campaigner_mailing_list(array(
			'custom_fields'		=> $fields,
			'list_id'			=> array_key_exists('list_id', $row) ? $row['list_id'] : '',
			'trigger_field'		=> array_key_exists('trigger_field', $row) ? $row['trigger_field'] : '',
			'trigger_value'		=> array_key_exists('trigger_value', $row) ? $row['trigger_value'] : ''
		));
	}
	
	
	/**
	 * Disables the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->_ee->db->delete('extensions', array('class' => $this->get_extension_class()));
		
		$this->_ee->load->dbforge();
		$this->_ee->dbforge->drop_table('campaigner_error_log');
		$this->_ee->dbforge->drop_table('campaigner_settings');
		$this->_ee->dbforge->drop_table('campaigner_mailing_lists');
	}


	/**
	 * Returns an API connector. If the API key has not been saved, returns FALSE.
	 *
	 * @access	public
	 * @return	Campaigner_api_connector|FALSE
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

		return new Campaigner_cm_api_connector($this->_settings->get_api_key());
	}
	
	
	/**
	 * Returns the documentation URL.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_docs_url()
	{
		return 'http://experienceinternet.co.uk/software/campaigner/docs/';
	}
	
	
	/**
	 * Returns the full error log from the database.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_error_log()
	{
		$error_log = array();
		
		$db_log = $this->_ee->db->select('error_code, error_date, error_log_id, error_message')
			->order_by('error_date desc')
			->get_where('campaigner_error_log', array('site_id' => $this->get_site_id()));
		
		foreach ($db_log->result_array() AS $db_log_entry)
		{
			$error_log[] = new Campaigner_error_log_entry($db_log_entry);
		}
		
		return $error_log;
	}
	
	
	/**
	 * Returns the extension class name. Assumed to be the package name,
	 * with a `_ext` suffix.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_extension_class()
	{
		return $this->_extension_class;
	}
	
	
	/**
	 * Returns the extension settings.
	 *
	 * @access	public
	 * @return	Campaigner_settings
	 */
	public function get_extension_settings()
	{
		if ( ! $this->_settings)
		{
			$this->_settings = $this->get_settings_from_db();
			$this->_settings->set_mailing_lists($this->get_mailing_lists_from_db());
		}
		
		return $this->_settings;
	}
	
	
	/**
	 * Returns the installed extension version. If the extension is not
	 * installed, returns an empty string.
	 *
	 * @access	public
	 * @return	string
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
	 * Retrieves the mailing list with the specified ID. If no matching mailing
	 * list is found, returns FALSE.
	 *
	 * @access	public
	 * @param	string			$list_id		The mailing list ID.
	 * @return	Campaigner_mailing_list|FALSE
	 */
	public function get_mailing_list_by_id($list_id)
	{
		$site_id = $this->_ee->config->item('site_id');

		$db_list = $this->_ee->db
			->select('custom_fields, list_id, site_id, trigger_field, trigger_value')
			->get_where('campaigner_mailing_lists', array('list_id' => $list_id, 'site_id' => $site_id), 1);
		
		return $db_list->num_rows()
			? new Campaigner_mailing_list($db_list->row_array())
			: FALSE;
	}
	
	
	/**
	 * Retrieves the mailing lists from the `campaigner_mailing_lists` table.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_mailing_lists_from_db()
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
	 * Returns a Campaigner_subscriber object for the specified member and mailing list.
	 *
	 * @access	public
	 * @param	int|string		$member_id		The member ID.
	 * @return	Campaigner_subscriber
	 */
	public function get_member_as_subscriber($member_id)
	{
		$member_data = $this->get_member_by_id($member_id);

		return new Campaigner_subscriber(array(
			'email'		=> $member_data['email'],
			'name'		=> $member_data['screen_name']
		));
	}
	
	
	/**
	 * Retrieves the information about the specified member from the database.
	 *
	 * @todo 	Use a 'member' object, instead of the raw array.
	 * @access	public
	 * @param	int|string	$member_id	The member ID.
	 * @return	array
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
			->select('members.email, members.group_id, members.location, members.member_id, members.occupation, members.screen_name, members.url, members.username, member_data.*')
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
	 * @access	public
	 * @return	array
	 */
	public function get_member_fields()
	{
		// Shortcuts.
		$lang = $this->_ee->lang;
		
		$member_fields = array();
		
		// ExpressionEngine hard-codes these member fields, so we must do the same.
		$standard_member_fields = array(
			array('id' => 'group_id', 'label' => $lang->line('mbr_group_id'), 'options' => array(), 'type' => 'text'),
			array('id' => 'location', 'label' => $lang->line('mbr_location'), 'options' => array(), 'type' => 'text'),
			array('id' => 'occupation', 'label' => $lang->line('mbr_occupation'), 'options' => array(), 'type' => 'text'),
			array('id' => 'screen_name', 'label' => $lang->line('mbr_screen_name'), 'options' => array(), 'type' => 'text'),
			array('id' => 'url', 'label' => $lang->line('mbr_url'), 'options' => array(), 'type' => 'text'),
			array('id' => 'username', 'label' => $lang->line('mbr_username'), 'options' => array(), 'type' => 'text')
		);
		
		foreach ($standard_member_fields AS $member_field_data)
		{
			$member_fields[] = new EI_member_field($member_field_data);
		}
		
		// Load the custom member fields from the database.
		$db_member_fields = $this->_ee->db
			->select("m_field_id, m_field_label, m_field_list_items, m_field_type")
			->get('member_fields');
		
		foreach ($db_member_fields->result_array() AS $db_row)
		{
			$member_field = new EI_member_field();
			$member_field->populate_from_db_array($db_row);
			
			$member_fields[] = $member_field;
		}
		
		return $member_fields;
	}


	/**
	 * Returns an array of mailing list IDs to which the specified member should be subscribed.
	 *
	 * @access	public
	 * @param	int|string		$member_id		The member ID.
	 * @return	array
	 */
	public function get_member_subscribe_lists($member_id)
	{
		if ( ! ($member_data = $this->get_member_data($member_id))
			OR ! ($lists = $this->get_mailing_lists_from_db()))
		{
			return array();
		}

		$subscribe_lists = array();
		
		foreach ($lists AS $list)
		{
			// Check the trigger.
			if ( ! $list->get_trigger_field() OR ! $list->get_trigger_value())
			{
				$subscribe_lists[] = $list;
				continue;
			}
			
			// We have a trigger.
			if (isset($member_data[$list->get_trigger_field()])
				&& $member_data[$list->get_trigger_field()] == $list->get_trigger_value())
			{
				$subscribe_lists[] = $list;
			}
		}
		
		return $subscribe_lists;
	}
	
	
	/**
	 * Returns the package name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_package_name()
	{
		return $this->_package_name;
	}
	
	
	/**
	 * Returns the package version.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_package_version()
	{
		return $this->_package_version;
	}
	
	
	/**
	 * Retrieves the settings from the `campaigner_settings` table.
	 *
	 * @access	public
	 * @return	Campaigner_settings
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
	 * @access	public
	 * @return	string
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
	 * @access	public
	 * @return	string
	 */
	public function get_support_url()
	{
		return 'http://support.experienceinternet.co.uk/discussions/campaigner/';
	}
	
	
	/**
	 * Returns the package theme URL.
	 *
	 * @access	public
	 * @return	string
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
	 * Writes an error to the error log.
	 *
	 * @access	public
	 * @param	Campaigner_api_error	$error		The error to log.
	 * @return	void
	 */
	public function log_error(Campaigner_api_error $error)
	{
		$insert_data = array(
			'error_code'	=> $error->get_code(),
			'error_date'	=> time(),
			'error_message'	=> $error->get_message(),
			'site_id'		=> $this->get_site_id()
		);
		
		$this->_ee->db->insert('campaigner_error_log', $insert_data);
	}
	
	
	/**
	 * Saves the extension settings.
	 *
	 * @access	public
	 * @param 	Campaigner_settings 	$settings		The settings to save.
	 * @return	void
	 */
	public function save_extension_settings(Campaigner_settings $settings)
	{
		if ( ! $this->save_settings_to_db($settings))
		{
			throw new Exception($this->_ee->lang->line('settings_not_saved'));
		}
		
		if ( ! $this->save_mailing_lists_to_db($settings))
		{
			throw new Exception($this->_ee->lang->line('mailing_lists_not_saved'));
		}
	}
	
	
	/**
	 * Saves the supplied mailing lists to the database.
	 *
	 * @access	public
	 * @param	Campaigner_settings		$settings		The settings.
	 * @return	bool
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
				'custom_fields'	=> serialize($custom_field_data),
				'list_id'		=> $mailing_list->get_list_id(),
				'site_id'		=> $site_id,
				'trigger_field'	=> $mailing_list->get_trigger_field(),
				'trigger_value'	=> $mailing_list->get_trigger_value()
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
	 * @access	public
	 * @param	Campaigner_settings		$settings		The settings.
	 * @return	bool
	 */
	public function save_settings_to_db(Campaigner_settings $settings)
	{
		$db	= $this->_ee->db;
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
	 * Subscribes the specified member to the configured mailing lists.
	 *
	 * @access	public
	 * @param	int|string		$member_id		The member ID.
	 * @param	bool			$update			Update a member's existing subscription preferences?
	 * @return	void
	 */
	public function subscribe_member($member_id, $update = FALSE)
	{

	}
	
	
	/**
	 * Updates the basic settings using input data.
	 *
	 * @access	public
	 * @param	Campaigner_settings		$settings		The settings object to update.
	 * @return	Campaigner_settings
	 */
	public function update_basic_settings_from_input(Campaigner_settings $settings)
	{
		$input 	= $this->_ee->input;
		$props	= array('api_key', 'client_id');
		
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
	 * @access	public
	 * @param	string		$installed_version		The installed version.
	 * @param 	string		$package_version		The package version.
	 * @return	bool|void
	 */
	public function update_extension($installed_version = '', $package_version = '')
	{
		if ( ! $installed_version OR version_compare($installed_version, $package_version, '>='))
		{
			return FALSE;
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
	 * @access	public
	 * @param 	Campaigner_settings 	$settings 		The settings object to update.
	 * @return	Campaigner_settings
	 */
	public function update_extension_settings_from_input(Campaigner_settings $settings)
	{
		return $this->update_mailing_list_settings_from_input($this->update_basic_settings_from_input($settings));
	}
	
	
	/**
	 * Updates the mailing list settings using input (GET / POST) data. Note that "update"
	 * is something of a misnomer, used for consistency. In actuality, any existing mailing
	 * lists are discarded.
	 *
	 * @access	public
	 * @param	Campaigner_settings		$settings		The settings object to update.
	 * @return	Campaigner_settings
	 */
	public function update_mailing_list_settings_from_input(Campaigner_settings $settings)
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
				'list_id'		=> $input_list['checked'],
				'trigger_field'	=> isset($input_list['trigger_field']) ? $input_list['trigger_field'] : '',
				'trigger_value'	=> isset($input_list['trigger_value']) ? $input_list['trigger_value'] : ''
			));
			
			// Custom fields.
			if (isset($input_list['custom_fields']))
			{
				$input_custom_fields = $input_list['custom_fields'];
				
				foreach ($input_custom_fields AS $cm_key => $member_field_id)
				{
					// If no member field has been associated with the custom field, ignore it.
					if ( ! $member_field_id)
					{
						continue;
					}
					
					// "Desanitize" the Campaign Monitor key, and create the custom field.
					$mailing_list->add_custom_field(new Campaigner_custom_field(array(
						'cm_key' 			=> desanitize_string($cm_key),
						'member_field_id'	=> $member_field_id
					)));
				}
			}
			
			$mailing_lists[] = $mailing_list;
		}
		
		$settings->set_mailing_lists($mailing_lists);
		return $settings;
	}


	/**
	 * Updates the specified member's subscriptions. Convenience wrapper for the `subscribe_member`
	 * method, with the `update` flag set to TRUE.
	 *
	 * @access	public
	 * @param	int|string		$member_id		The member ID.
	 * @return	void
	 */
	public function update_member_subscriptions($member_id)
	{
		$this->subscribe_member($member_id, TRUE);
	}
}


/* End of file		: campaigner_model.php */
/* File location	: third_party/campaigner/models/campaigner_model.php */
