<?php if ( ! defined('EXT')) exit('Invalid file request.');

/**
 * Example add-on model.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_error' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_client' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_error_log_entry' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_settings' .EXT;
require_once PATH_THIRD .'campaigner/classes/EI_member_field' .EXT;
require_once PATH_THIRD .'campaigner/helpers/EI_number_helper' .EXT;
require_once PATH_THIRD .'campaigner/helpers/EI_sanitize_helper' .EXT;

class Campaigner_model extends CI_Model {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * API connector.
	 *
	 * @access	private
	 * @var		CMBase
	 */
	private $_api_connector;
	
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
		parent::CI_Model();

		$this->_ee =& get_instance();
		
		$this->_package_name		= 'Campaigner';
		$this->_package_version		= '0.1.0';
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
				'constraint'		=> 5,
				'type'				=> 'int',
				'unsigned'			=> TRUE
			),
			'api_key'	=> array(
				'constraint'		=> 50,
				'type'				=> 'varchar'
			),
			'client_id'	=> array(
				'constraint'		=> 50,
				'type'				=> 'varchar'
			)
		);
		
		$dbforge->add_field($fields);
		$dbforge->add_key('site_id', TRUE);
		$dbforge->create_table('campaigner_settings');
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
	 * Retrieves clients from the Campaign Monitor API. Wrapper for the
	 * Campaigner::userGetClients method.
	 *
	 * @access	public
	 * @return	void
	 */
	public function get_clients_from_api()
	{
		$root_node		= 'Client';
		$api_clients 	= $this->prep_api_response($this->make_api_call('userGetClients', array()), $root_node);
		$clients		= array();
		
		foreach ($api_clients AS $api_client)
		{
			/**
			 * Ensure we're not tripped-up by an empty array (i.e. no clients),
			 * or missing data (for some unforeseen reason).
			 */
			
			if ( ! isset($api_client['ClientID']) OR ! isset($api_client['Name']))
			{
				continue;
			}
			
			$clients[] = new Campaigner_client(array(
				'client_id'		=> $api_client['ClientID'],
				'client_name'	=> $api_client['Name']
			));
		}
		
		return $clients;
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
	 * Retrieves the mailing list custom fields from the Campaign Monitor API.
	 * Wrapper for the CampaignMonitor::listGetCustomFields method.
	 *
	 * @access	public
	 * @param	string		$list_id		The list ID.
	 * @return	array
	 */
	public function get_mailing_list_custom_fields_from_api($list_id)
	{
		$fields		= array();
		$root_node	= 'ListCustomField';
		
		$api_fields = $this->prep_api_response($this->make_api_call('listGetCustomFields', array($list_id)), $root_node);
		
		foreach ($api_fields AS $api_field)
		{
			/**
			 * Ensure we're not tripped-up by an empty array (i.e. no custom fields),
			 * or missing data (for some unforeseen reason).
			 */
			
			if ( ! isset($api_field['Key']) OR ! isset($api_field['FieldName']))
			{
				continue;
			}
			
			$fields[] = new Campaigner_custom_field(array(
				'cm_key'	=> $api_field['Key'],
				'label'		=> $api_field['FieldName']
			));
		}
		
		return $fields;
	}
	
	
	/**
	 * Retrieves mailing lists from the Campaign Monitor API. Wrapper for the
	 * CampaignMonitor::clientGetLists method.
	 *
	 * @access	public
	 * @param	string		$client_id					The client ID.
	 * @param	string		$include_custom_fields		Automatically retrieve the list custom fields?
	 * @return	array
	 */
	public function get_mailing_lists_from_api($client_id, $include_custom_fields = TRUE)
	{
		$mailing_lists	= array();
		$api_lists		= $this->prep_api_response($this->make_api_call('clientGetLists', array($client_id)), 'List');
		
		foreach ($api_lists AS $api_list)
		{
			/**
			 * Ensure we're not tripped-up by an empty array (i.e. no mailing lists),
			 * or missing data (for some unforeseen reason).
			 */
			
			if ( ! isset($api_list['ListID']) OR ! isset($api_list['Name']))
			{
				continue;
			}
			
			$mailing_list = new Campaigner_mailing_list(array(
				'list_id'		=> $api_list['ListID'],
				'list_name'		=> $api_list['Name']
			));
			
			if ($include_custom_fields)
			{
				$mailing_list->set_custom_fields($this->get_mailing_list_custom_fields_from_api($api_list['ListID']));
			}
			
			$mailing_lists[] = $mailing_list;
		}
		
		return $mailing_lists;
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
			// Extract the custom fields data.
			$custom_fields = unserialize($db_mailing_list['custom_fields']);
			unset($db_mailing_list['custom_fields']);
			
			// Create the basic mailing list object.
			$mailing_list = new Campaigner_mailing_list($db_mailing_list);
			
			// Add the custom fields.
			if (is_array($custom_fields))
			{
				foreach ($custom_fields AS $custom_field)
				{
					$mailing_list->add_custom_field(new Campaigner_custom_field($custom_field));
				}
			}
			
			$mailing_lists[] = $mailing_list;
		}
		
		return $mailing_lists;
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
	 * Determines whether the specified member is subscribed to the specified
	 * mailing list.
	 *
	 * @access	public
	 * @param	array		$member_data		The member data.
	 * @param	string		$list_id			The mailing list ID.
	 * @return	bool
	 */
	public function get_member_is_subscribed_to_mailing_list(Array $member_data, $list_id)
	{
		$api_result = $this->make_api_call('subscribersGetIsSubscribed', array($member_data['email'], $list_id));
		return (is_string($api_result) && strtolower($api_result) == 'true');
	}
	
	
	/**
	 * Filters the supplied mailing lists, and returns only those to which
	 * the specified member should be subscribed.
	 *
	 * @access	public
	 * @param	array		$member_data		The member data.
	 * @param 	array 		$mailing_lists		The mailing lists.
	 * @return	array
	 */
	public function get_member_mailing_lists_to_process(Array $member_data, Array $mailing_lists)
	{
		$lists_to_process = array();
		
		foreach ($mailing_lists AS $mailing_list)
		{
			// Check the trigger.
			if ( ! $mailing_list->get_trigger_field() OR ! $mailing_list->get_trigger_value())
			{
				$lists_to_process[] = $mailing_list;
				continue;
			}
			
			// We have a trigger.
			if (isset($member_data[$mailing_list->get_trigger_field()])
				&& $member_data[$mailing_list->get_trigger_field()] == $mailing_list->get_trigger_value())
			{
				$lists_to_process[] = $mailing_list;
			}
		}
		
		return $lists_to_process;
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
	 * Makes the specified API call. Wrapper for the CampaignerMonitor methods.
	 *
	 * @access	public
	 * @param	string		$method			The method to call.
	 * @param	array		$params			The method parameters.
	 * @return	array
	 */
	public function make_api_call($method, $params = array())
	{
		// Can't do anything without an API connector.
		if ( ! $this->_api_connector)
		{
			$error_message = 'API connector not set.';
			
			$this->log_error(new Campaigner_api_error(array('message' => $error_message)));
			throw new Exception($error_message);
		}
		
		// Confirm that the method exists.
		if ( ! method_exists($this->_api_connector, $method))
		{
			$error_message = 'Unknown API method: ' .$method;
			
			$this->log_error(new Campaigner_api_error(array('message' => $error_message)));
			throw new Exception($error_message);
		}
		
		// Call the API method.
	 	return call_user_func_array(array($this->_api_connector, $method), $params);
	}
	
	
	/**
	 * Validates and preps an API result array.
	 *
	 * @access	public
	 * @param	array		$api_result		The API result.
	 * @param	string		$root_node		The result root node.
	 * @return	array
	 */
	public function prep_api_response($api_result, $root_node = '')
	{
		/**
		 * If the API result is not an array, is an empty array, or the
		 * root node does not exist, the method call returned no results.
		 */
		
		if ( ! $api_result OR ! is_array($api_result) OR ($root_node && ! isset($api_result[$root_node])))
		{
			return array();
		}
		
		/**
		 * Validate the result. Throws an exception if it's invalid.
		 * We just let it bubble.
		 */
		
		$this->_validate_api_response($api_result);
		
		if ($root_node)
		{
			// Fix the result array structure.
			$api_result = $this->_fix_api_response_structure($api_result, $root_node);
			return $api_result[$root_node];
		}
		else
		{
			return $api_result;
		}
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
	 * Sets the API connector object.
	 *
	 * @access	public
	 * @param	CampaignMonitor		$api_connector		The API connector object.
	 * @return	void
	 */
	public function set_api_connector(CampaignMonitor $api_connector)
	{
		$this->_api_connector = $api_connector;
	}
	
	
	/**
	 * Subscribes the specified member to the configured mailing lists.
	 *
	 * @access	public
	 * @param	int|string		$member_id		The member ID.
	 * @param	bool			$update 		Are we updating a member's subscription preferences?
	 * @return	void
	 */
	public function subscribe_member($member_id, $update = FALSE)
	{
		// Get out early.
		if ( ! valid_int($member_id, 1) OR ! ($member_data = $this->get_member_by_id($member_id)))
		{
			error_log('Invalid member ID "' .$member_id .'" in subscribe_member');
			return;
		}
		
		// Ensure that the extension settings are loaded.
		$this->get_extension_settings();
		
		/**
		 * Any exceptions bubble up to this point, and are ignored. We can't "solve" the problem,
		 * and the error logging is handled elsewhere.
		 */
		
		try
		{
			/**
			 * If we're updating a member's existing preferences, we also need to unsubscribe
			 * him from any lists which he has not explicitly joined.
			 *
			 * This is the easy way to accomplish that goal. We just unsubscribe the member from
			 * _all_ of the mailing lists to which he is currently subscribed, and then resubscribe
			 * him to only those he has explicitly opted-in to.
			 */
			
			if ($update)
			{
				$this->unsubscribe_member_from_mailing_lists(
					$member_data,
					$this->get_mailing_lists_from_api($this->_settings->get_client_id(), FALSE)
				);
			}
			
			// Determine which mailing lists to process, and do so.
			$this->subscribe_member_to_mailing_lists(
				$member_data,
				$this->get_member_mailing_lists_to_process($member_data, $this->_settings->get_mailing_lists()),
				$update
			);
			
		}
		catch (Exception $e)
		{
			// Do nothing.
			error_log('Exception: ' .$e->getMessage() .' (' .$e->getCode .')');
		}
	}
	
	
	/**
	 * Subscribes the specified member to the specified mailing lists.
	 *
	 * @access	public
	 * @param	array		$member_data		The member data.
	 * @param	array		$mailing_lists		The mailing lists.
	 * @param	bool		$update 			Are we updating a member's subscription preferences?
	 * @return	void
	 */
	public function subscribe_member_to_mailing_lists(Array $member_data, Array $mailing_lists, $update = FALSE)
	{
		// Do it once.
		$email = $member_data['email'];
		$screen_name = utf8_decode($member_data['screen_name']);
		
		foreach ($mailing_lists AS $mailing_list)
		{
			// Custom fields.
			$custom_field_data = array();
			
			foreach ($mailing_list->get_custom_fields() AS $custom_field)
			{
				if (isset($member_data[$custom_field->get_member_field_id()]))
				{
					$custom_field_data[$custom_field->get_cm_key()] = utf8_decode($member_data[$custom_field->get_member_field_id()]);
				}
			}
			
			$this->prep_api_response($this->make_api_call('subscriberAddWithCustomFields', array(
				$email,
				$screen_name,
				$custom_field_data,
				$mailing_list->get_list_id(),
				$update
			)));
		}
	}
	
	
	/**
	 * Unsubscribes the specified member from the specified mailing lists.
	 *
	 * @access	public
	 * @param	array		$member_data		The member data.
	 * @param	array		$mailing_lists		The mailing lists.
	 * @return	void
	 */
	public function unsubscribe_member_from_mailing_lists(Array $member_data, Array $mailing_lists)
	{
		$email = $member_data['email'];
		
		foreach ($mailing_lists AS $mailing_list)
		{
			/**
			 * We need to check that the member is subscribed to the mailing list before attempting
			 * to unsubscribe him, otherwise Campaign Monitor reports an API error, which triggers
			 * an exception in our code, which causes all subsequent actions to stop.
			 *
			 * Which would be bad.
			 */
			
			if ($this->get_member_is_subscribed_to_mailing_list($member_data, $mailing_list->get_list_id()))
			{
				$this->prep_api_response($this->make_api_call('subscriberUnsubscribe', array(
					$email,
					$mailing_list->get_list_id()
				)));
			}
		}
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
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Fixes the API result structure, if required.
	 *
	 * @see 	http://www.campaignmonitor.com/forums/viewtopic.php?id=2765
	 * @access	private
	 * @param	array		$api_result		The API result array.
	 * @param	string		$root_node		The root node.
	 * @return	array
	 */
	private function _fix_api_response_structure(Array $api_result = array(), $root_node)
	{
		if ( ! isset($api_result[$root_node][0]))
		{
			$fake_item = array();
			
			foreach ($api_result[$root_node] AS $key => $val)
			{
				$fake_item[$key] = $val;
			}
			
			$api_result[$root_node] = array($fake_item);
		}
		
		return $api_result;
	}
	
	
	/**
	 * Validates an API response. Throws an exception if the API returned
	 * an error.
	 *
	 * @access	private
	 * @param	array		$api_response	The API response.
	 * @return	bool
	 */
	private function _validate_api_response(Array $api_response = array())
	{
		// Check for errors.
		if (isset($api_response['Code']) && $api_response['Code'] != '0')
		{
			$error_code = (int) $api_response['Code'];
			
			$error_message = isset($api_response['Message'])
				? $this->_ee->lang->line('api_error_preamble') .$api_response['Message']
				: $this->_ee->lang->line('api_error_unknown');
			
			$this->log_error(new Campaigner_api_error(array(
				'code'		=> $error_code,
				'message'	=> $error_message
			)));
			
			throw new Exception($error_message, $error_code);
		}
		
		return TRUE;
	}

}

/* End of file		: campaigner_model.php */
/* File location	: third_party/campaigner/models/campaigner_model.php */