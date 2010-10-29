<?php if ( ! defined('BASEPATH')) exit('Direct script access is not permitted.');

/**
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/libraries/CMBase' .EXT;

class Campaigner_ext {
	
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
		$this->_ee->load->add_package_path(PATH_THIRD .'campaigner/');
		$this->_ee->load->model('campaigner_model');
		
		// Load the language file.
		$this->_ee->lang->loadfile('campaigner');
		
		// Set the instance properties.
		$this->description	= $this->_ee->lang->line('extension_description');
		$this->docs_url		= 'http://experienceinternet.co.uk/software/campaigner/';
		$this->name			= $this->_ee->lang->line('extension_name');
		$this->settings		= $settings;
		$this->version		= $this->_ee->campaigner_model->get_package_version();
	}
	
	
	/**
	 * Activates the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function activate_extension()
	{
		$this->_ee->campaigner_model->activate_extension();
	}
	
	
	/**
	 * Disables the extension.
	 *
	 * @access	public
	 * @return	void
	 */
	public function disable_extension()
	{
		$this->_ee->campaigner_model->disable_extension();
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
		$settings = $this->_ee->campaigner_model->update_extension_settings_from_input(new Campaigner_settings());
		
		// Save the settings.
		try
		{
			$this->_ee->campaigner_model->save_extension_settings($settings);
			$this->_ee->session->set_flashdata('message_success', $this->_ee->lang->line('msg_settings_saved'));
		}
		catch (Exception $e)
		{
			$this->_ee->session->set_flashdata(
				'message_failure',
				$this->_ee->lang->line('msg_settings_not_saved') .' (' .$e->getMessage() .')'
			);
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
		$base_url = BASE .AMP .'C=addons_extensions' .AMP .'M=extension_settings' .AMP .'file=campaigner' .AMP .'tab=';
		
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
		return $this->_ee->campaigner_model->update_extension($current_version);
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
	 * Displays the 'settings' tab.
	 *
	 * @access	private
	 * @return	string
	 */
	private function _display_settings()
	{
		// Theme URL.
		$theme_url = $this->_ee->campaigner_model->get_theme_url();
		
		// Collate the view variables.
		$view_vars = array(
			'action_url'	=> 'C=addons_extensions' .AMP .'M=save_extension_settings',
			'cp_page_title'	=> $this->_ee->lang->line('extension_name'),
			'hidden_fields'	=> array('file' => strtolower($this->_ee->campaigner_model->get_package_name())),
			'settings'		=> $this->_ee->campaigner_model->get_extension_settings()
		);
		
		
		/**
		 * Is this an AJAX request?
		 */
		
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			switch (strtolower($this->_ee->input->get('request')))
			{
				case 'get_clients':
					$this->_ee->output->send_ajax_response($this->_ee->load->view('_clients', $view_vars, TRUE));
					break;
					
				case 'get_mailing_lists':
					$this->_ee->output->send_ajax_response($this->_ee->load->view('_mailing_lists', $view_vars, TRUE));
					break;
				
				default:
					break;
			}
		}
		else
		{
			// Add the CSS.
			$this->_ee->cp->add_to_foot('<link media="screen, projection" rel="stylesheet" type="text/css" href="'
				.$theme_url .'css/cp.css" />');

			// JavaScript.
			$this->_ee->load->library('javascript');
			
			$this->_ee->cp->add_to_foot('<script type="text/javascript" src="'
				.$theme_url .'js/cp.js"></script>');

			// JavaScript globals.
			$this->_ee->javascript->set_global(
				'campaigner.lang',
				array(
					'missingApiKey' 	=> $this->_ee->lang->line('msg_missing_api_key'),
					'missingClientId'	=> $this->_ee->lang->line('msg_missing_client_id')
				)
			);

			/*
			$this->_ee->javascript->set_global(
				'campaigner.memberFields',
				$this->_ee->javascript->generate_json($member_fields)
			);
			*/

			$this->_ee->javascript->set_global(
				'campaigner.ajaxUrl',
				str_replace(AMP, '&', BASE)
					.'&C=addons_extensions&M=extension_settings&file='
					.strtolower($this->_ee->campaigner_model->get_package_name())
			);

			// Compile the JavaScript.
			$this->_ee->javascript->compile();
			
			// Load the view.
			return $this->_ee->load->view('settings', $view_vars, TRUE);
		}
	}
	
}

/* End of file		: ext.campaigner.php */
/* File location	: third_party/campaigner/ext.campaigner.php */