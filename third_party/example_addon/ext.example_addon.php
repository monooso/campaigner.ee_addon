<?php if ( ! defined('BASEPATH')) exit('Direct script access is not permitted.');

/**
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Example Add-on
 */

class Example_addon_ext {
	
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
	
	
	/* --------------------------------------------------------------
	 * PUBLIC PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Description.
	 *
	 * @access	public
	 * @var		string
	 */
	public $description;
	
	/**
	 * Documentation URL.
	 *
	 * @access	public
	 * @var		string
	 */
	public $docs_url;
	
	/**
	 * Extension name.
	 *
	 * @access	public
	 * @var		string
	 */
	public $name;
	
	/**
	 * Settings.
	 *
	 * @access	public
	 * @var		array
	 */
	public $settings = array();
	
	/**
	 * Does this extension have a settings screen?
	 *
	 * @access	public
	 * @var		string
	 */
	public $settings_exist = 'y';
	
	/**
	 * Version.
	 *
	 * @access	public
	 * @var		string
	 */
	public $version;
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Class constructor.
	 *
	 * @access	public
	 * @param	array 		$settings		Previously-saved extension settings.
	 * @return	void
	 */
	public function __construct($settings = array())
	{
		$this->_ee =& get_instance();
		
		// Load our glamorous assistants.
		$this->_ee->load->helper('form');
		$this->_ee->load->library('table');
		
		// Load the model.
		$this->_ee->load->add_package_path(PATH_THIRD .'example_addon/');
		$this->_ee->load->model('example_addon_model');
		
		// Load the language file.
		$this->_ee->lang->loadfile('example_addon');
		
		// Set the instance properties.
		$this->description	= $this->_ee->lang->line('extension_description');
		$this->docs_url		= 'http://experienceinternet.co.uk/software/example_addon/';
		$this->name			= $this->_ee->lang->line('extension_name');
		$this->settings		= $settings;
		$this->version		= $this->_ee->example_addon_model->get_package_version();
	}
	
	
	/**
	 * Activates the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$this->_ee->example_addon_model->activate_extension();
	}
	
	
	/**
	 * Disables the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->_ee->example_addon_model->disable_extension();
	}
	
	
	/**
	 * Saves the extension settings.
	 *
	 * @access	public
	 * @return	void
	 */
	public function save_settings()
	{
		// Update the settings with any input data.
		$this->_ee->example_addon_model->update_extension_settings_from_input();
		
		// Save the settings.
		if ($this->_ee->example_addon_model->save_extension_settings())
		{
			$this->_ee->session->set_flashdata('message_success', $this->_ee->lang->line('settings_saved'));
		}
		else
		{
			$this->_ee->session->set_flashdata('message_failure', $this->_ee->lang->line('settings_not_saved'));
		}
	}
	
	
	/**
	 * Displays the extension settings form.
	 *
	 * @access	public
	 * @return	string
	 */
	public function settings_form()
	{
		// Define the navigation.
		$base_url = BASE .AMP .'C=addons_extensions' .AMP .'M=extension_settings' .AMP .'file=example_addon' .AMP .'tab=';
		
		$this->_ee->cp->set_right_nav(array(
			'nav_settings'	=> $base_url .'settings',
			'nav_errors'	=> $base_url .'errors',
			'nav_help'		=> $base_url .'help'
		));
		
		switch ($this->_ee->input->get('tab'))
		{
			case 'errors':
				return $this->_display_errors();
				break;
				
			case 'help':
				return $this->_display_help();
				break;
			
			case 'settings':
			default:
				return $this->_display_settings();
				break;
		}
	}
	
	
	/**
	 * Updates the extension.
	 *
	 * @access	public
	 * @param	string		$current_version	The current version.
	 * @return	bool
	 */
	public function update_extension($current_version = '')
	{
		return $this->_ee->example_addon_model->update_extension($current_version);
	}
	
	
	
	/* --------------------------------------------------------------
	 * HOOK HANDLERS
	 * ------------------------------------------------------------ */

	/**
	 * Handles the `example_hook` hook.
	 *
	 * @see		http://expressionengine.com/developers/extension_hooks/on_example_hook/
	 * @access	public
	 * @param 	string 		$example_data		Data passed to the hook handler.
	 * @return	void
	 */
	public function on_example_hook($example_data = '')
	{
		// Check for previous handlers.
		$example_data = $this->_ee->extensions->last_call
			? $this->_ee->extensions->last_call
			: $example_data;
	}
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Displays tab 'c'.
	 *
	 * @access	private
	 * @return	string
	 */
	private function _display_settings()
	{
		// Collate the view variables.
		$vars = array(
			'action_url'	=> 'C=addons_extensions' .AMP .'M=save_extension_settings',
			'cp_page_title'	=> $this->_ee->lang->line('nav_settings') .' | ' .$this->_ee->lang->line('extension_name'),
			'hidden_fields'	=> array('file' => strtolower($this->_ee->example_addon_model->get_package_name())),
			'settings'		=> $this->_ee->example_addon_model->get_extension_settings()
		);
			
		// Load the view.
		return $this->_ee->load->view('settings', $vars, TRUE);
	}
	
}

/* End of file		: ext.example_addon.php */
/* File location	: third_party/example_addon/ext.example_addon.php */