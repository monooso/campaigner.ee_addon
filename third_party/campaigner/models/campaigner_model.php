<?php if ( ! defined('EXT')) exit('Invalid file request.');

/**
 * Example add-on model.
 *
 * @author			: Stephen Lewis
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_settings' .EXT;

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
	 * The site ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_site_id;
	
	
	
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
		
		$this->_site_id 			= $this->_ee->config->item('site_id');
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
		$hooks = array(
			array(
				'hook'		=> 'example_hook',
				'method'	=> 'on_example_hook',
				'priority'	=> 10
			)
		);
		
		foreach ($hooks AS $hook)
		{
			$this->_ee->db->insert(
				'extensions',
				array(
					'class'		=> $this->get_extension_class(),
					'enabled'	=> 'y',
					'hook'		=> $hook['hook'],
					'method'	=> $hook['method'],
					'priority'	=> $hook['priority'],
					'version'	=> $this->get_package_version()
				)
			);
		}
		
		// Create the settings table.
		$fields = array(
			'site_id' => array(
				'constraint'	=> 8,
				'null'			=> FALSE,
				'type'			=> 'int',
				'unsigned'		=> TRUE
			),
			'setting_a' => array(
				'constraint'	=> 255,
				'null'			=> FALSE,
				'type'			=> 'varchar'
			),
			'setting_b' => array(
				'constraint'	=> 255,
				'null'			=> FALSE,
				'type'			=> 'varchar'
			)
		);
		
		$this->load->dbforge();
		$this->_ee->dbforge->add_field($fields);
		$this->_ee->dbforge->add_key('site_id', TRUE);
		$this->_ee->dbforge->create_table('campaigner_settings', TRUE);
	}
	
	
	/**
	 * Disables the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		// Delete all the extension hooks.
		$this->_ee->db->delete('extensions', array('class' => $this->get_extension_class()));
		
		// Delete the settings table.
		$this->load->dbforge();
		$this->_ee->dbforge->drop_table('campaigner_settings');
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
	 * Returns the extension settings.
	 *
	 * @access	public
	 * @return	Campaigner_settings
	 */
	public function get_extension_settings()
	{
		if ( ! isset($this->_extension_settings))
		{
			$db_settings = $this->_ee->db
				->select('setting_a, setting_b')
				->get_where('campaigner_settings', array('site_id' => $this->get_site_id()), 1);
			
			$settings_array = $db_settings->num_rows() == 1
				? $db_settings->row_array()
				: array();
			
			$this->_extension_settings = new Campaigner_settings($settings_array);
		}
		
		return $this->_extension_settings;
	}
	
	
	/**
	 * Returns the site ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_site_id()
	{
		return $this->_site_id;
	}
	
	
	/**
	 * Saves the extension settings.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function save_extension_settings()
	{
		$settings = array_merge(
			array('site_id' => $this->get_site_id()),
			$this->get_extension_settings()->to_array()
		);
		
		$this->_ee->db->delete('campaigner_settings', array('site_id' => $this->get_site_id()));
		$this->_ee->db->insert('campaigner_settings', $settings);
		
		return TRUE;
	}
	
	
	/**
	 * Updates the extension.
	 *
	 * @access	public
	 * @param	string	$current_version	The current extension version.
	 * @return	bool
	 */
	public function update_extension($current_version = '')
	{
		if ( ! $current_version
			OR version_compare($current_version, $this->get_package_version(), '>='))
		{
			return FALSE;
		}
		
		// Update the extension.
		$this->_ee->db->update(
			'extensions',
			array('version' => $this->get_package_version()),
			array('class' => $this->get_extension_class())
		);
		
		return TRUE;
	}
	
	
	/**
	 * Updates the settings from any POST input.
	 *
	 * @access	public
	 * @return	array
	 */
	public function update_extension_settings_from_input()
	{
		$settings = $this->get_extension_settings();
		
		// Works for simple data.
		$fields = array('setting_a', 'setting_b');
		
		foreach ($fields AS $field_name)
		{
			$set_method = 'set_' .$field_name;
			
			if (method_exists($settings, $set_method)
				&& ($field_value = $this->_ee->input->get_post($field_name)) !== FALSE)
			{
				$settings->$set_method($field_value);
			}
		}
		
		$this->_extension_settings = $settings;
	}
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */


}

/* End of file		: campaigner_model.php */
/* File location	: third_party/campaigner/models/campaigner_model.php */