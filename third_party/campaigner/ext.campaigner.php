<?php if ( ! defined('BASEPATH')) exit('Direct script access is not permitted.');

/**
 * Automatically add your EE members to Campaign Monitor mailing lists.
 * 
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_error' .EXT;
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
		
		if( ! class_exists('EE_Logger') )
		{
		   require APPPATH . 'libraries/Logger' . EXT;
		}

		$this->_ee->logger = new EE_Logger;
		
		// Load the model.
		$this->_ee->load->add_package_path(PATH_THIRD .'campaigner/');
		$this->_ee->load->model('campaigner_model');
		
		// Shortcut.
		$model = $this->_ee->campaigner_model;
		
		// Load the language file.
		$this->_ee->lang->loadfile('campaigner');
		
		// Set the instance properties.
		$this->description	= $this->_ee->lang->line('extension_description');
		$this->docs_url		= 'http://experienceinternet.co.uk/software/campaigner/';
		$this->name			= $this->_ee->lang->line('extension_name');
		$this->settings		= $settings;
		$this->version		= $model->get_package_version();
		
		// Is the extension installed?
		if ($model->get_installed_extension_version())
		{
			// Load the settings from the database, and update them with any input data.
			$this->settings = $model->update_extension_settings_from_input($model->get_extension_settings());
			
			// If the API key has been set, initialise the API connector.
			if ($this->settings->get_api_key())
			{
				$model->set_api_connector(new CampaignMonitor($this->settings->get_api_key()));
			}
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
	 * Displays the 'settings' page.
	 *
	 * @access	public
	 * @return	string
	 */
	public function display_settings()
	{
		// If this isn't an AJAX request, just display the "base" settings form.
		if ( ! isset($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest')
		{
			return $this->display_settings_base();
		}
		
		// Handle AJAX requests.
		switch (strtolower($this->_ee->input->get('request')))
		{
			case 'get_clients':
				$this->_ee->output->send_ajax_response($this->display_settings_clients());
				break;
				
			case 'get_mailing_lists':
				$this->_ee->output->send_ajax_response($this->display_settings_mailing_lists());
				break;
			
			default:
				// Unknown request. Do nothing.
				break;
		}
	}
	
	
	/**
	 * Displays the "base" settings form.
	 *
	 * @access	public
	 * @return	string
	 */
	public function display_settings_base()
	{
		// Shortcuts.
		$cp		= $this->_ee->cp;
		$lang	= $this->_ee->lang;
		$model	= $this->_ee->campaigner_model;
		
		$lower_package_name = strtolower($model->get_package_name());
		
		// View variables.
		$view_vars = array(
			'action_url'		=> 'C=addons_extensions' .AMP .'M=save_extension_settings',
			'cp_page_title'		=> $lang->line('extension_name'),
			'hidden_fields'		=> array('file' => $lower_package_name),
			'settings'			=> $this->settings		// Loaded in the constructor.
		);
		
		// Theme URL.
		$theme_url = $model->get_theme_url();
		
		// Add the CSS.
		$cp->add_to_foot('<link media="screen, projection" rel="stylesheet" type="text/css" href="' .$theme_url .'css/cp.css" />');

		// Load the JavaScript library, and set a shortcut.
		$this->_ee->load->library('javascript');
		$js = $this->_ee->javascript;
		
		$cp->add_to_foot('<script type="text/javascript" src="' .$theme_url .'js/cp.js"></script>');

		// JavaScript globals.
		$js->set_global('campaigner.lang', array(
				'missingApiKey' 	=> $lang->line('msg_missing_api_key'),
				'missingClientId'	=> $lang->line('msg_missing_client_id')
		));
		
		// Prepare the member fields.
		$member_fields = $model->get_member_fields();
		$js_member_fields = array();
		
		foreach ($member_fields AS $member_field)
		{
			$js_member_fields[$member_field->get_id()] = $member_field->to_array();
		}
		
		$js->set_global('campaigner.memberFields', $js->generate_json($js_member_fields));

		$js->set_global('campaigner.ajaxUrl',
			str_replace(AMP, '&', BASE) .'&C=addons_extensions&M=extension_settings&file=' .$lower_package_name
		);

		// Compile the JavaScript.
		$js->compile();
		
		// Load the view.
		return $this->_ee->load->view('settings', $view_vars, TRUE);
	}
	
	
	/**
	 * Displays the "clients" settings form fragment.
	 *
	 * @access	public
	 * @return	string
	 */
	public function display_settings_clients()
	{
		try
		{
			$view_vars = array(
				'clients'	=> $this->_ee->campaigner_model->get_clients_from_api(),
				'settings'	=> $this->settings
			);
			
			$view_name = '_clients';
		}
		catch (Exception $e)
		{
			// Something went wrong with the API call.
			$view_vars = array('api_error' => new Campaigner_api_error(array(
				'code'		=> $e->getCode(),
				'message'	=> $e->getMessage()
			)));
			
			$view_name = '_api_error';
		}
		
		return $this->_ee->load->view($view_name, $view_vars, TRUE);
	}
	
	
	/**
	 * Displays the "mailing lists" settings form fragment.
	 *
	 * @access	public
	 * @return	string
	 */
	public function display_settings_mailing_lists()
	{
		// Shortcut.
		$model = $this->_ee->campaigner_model;
		
		try
		{
			// Retrieve all the available mailing lists from the API.
			$mailing_lists = $model->get_mailing_lists_from_api($this->settings->get_client_id());
			
			// Loop through the mailing lists. If we have settings for a list, make a note of them.
			foreach ($mailing_lists AS $mailing_list)
			{
				if (($saved_mailing_list = $this->settings->get_mailing_list_by_id($mailing_list->get_list_id())))
				{
					$mailing_list->set_active(TRUE);
					$mailing_list->set_trigger_field($saved_mailing_list->get_trigger_field());
					$mailing_list->set_trigger_value($saved_mailing_list->get_trigger_value());
					
					// Custom fields.
					if (($custom_fields = $mailing_list->get_custom_fields()))
					{
						foreach ($custom_fields AS $custom_field)
						{
							if (($saved_custom_field = $saved_mailing_list->get_custom_field_by_cm_key($custom_field->get_cm_key())))
							{
								$custom_field->set_member_field_id($saved_custom_field->get_member_field_id());
							}
						}
					}
				}
			}
			
			// Retrieve the member fields.
			$member_fields = $model->get_member_fields();
			
			// Prepare the member fields data for use in a dropdown.
			$member_fields_dd_data = array();

			foreach ($member_fields AS $member_field)
			{
				$member_fields_dd_data[$member_field->get_id()] = $member_field->get_label();
			}
			
			// Define the view variables.
			$view_vars = array(
				'mailing_lists'			=> $mailing_lists,
				'member_fields'			=> $member_fields,
				'member_fields_dd_data'	=> $member_fields_dd_data,
				'settings'				=> $this->settings
			);
		
			$view_name = '_mailing_lists';
		}
		catch (Exception $e)
		{
			$view_vars = array('api_error' => new Campaigner_api_error(array('code' => $e->getCode(), 'message' => $e->getMessage())));
			$view_name = '_api_error';
		}
		
		return $this->_ee->load->view($view_name, $view_vars, TRUE);
	}
	
	
	/**
	 * Saves the extension settings.
	 *
	 * @access	public
	 * @return	void
	 */
	public function save_settings()
	{
		// Save the settings.
		try
		{
			$this->_ee->campaigner_model->save_extension_settings($this->settings);
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
		// Load our glamorous assistants.
		$this->_ee->load->helper('form');
		$this->_ee->load->library('table');
		
		// Define the navigation.
		$base_url = BASE .AMP .'C=addons_extensions' .AMP .'M=extension_settings' .AMP .'file=campaigner' .AMP .'tab=';
		
		$this->_ee->cp->set_right_nav(array(
			'nav_settings'	=> $base_url .'settings',
			'nav_errors'	=> $base_url .'errors',
			'nav_support'	=> $this->_ee->campaigner_model->get_support_url()
		));
		
		switch ($this->_ee->input->get('tab'))
		{
			case 'errors':
				return $this->display_errors();
				break;
				
			case 'help':
				return $this->display_help();
				break;
			
			case 'settings':
			default:
				return $this->display_settings();
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
		return $this->_ee->campaigner_model->update_extension($current_version, $this->version);
	}
	
	
	
	/* --------------------------------------------------------------
	 * HOOK HANDLERS
	 * ------------------------------------------------------------ */

	/**
	 * Handles the `cp_members_member_create` hook. Used when a member is created via
	 * the control panel.
	 *
	 * @see		http://expressionengine.com/developers/extension_hooks/cp_members_member_create/
	 * @access	public
	 * @param 	int|string 		$member_id			The member ID.
	 * @param	array 			$member_data		Additional member data.
	 * @return	void
	 */
	public function on_cp_members_member_create($member_id, Array $member_data)
	{
		$this->_ee->campaigner_model->subscribe_member($member_id);
	}
	
	
	/**
	 * Handles the `cp_members_validate_members` hook. Used when the membership preferences
	 * are set to "Manual activation by an administrator" (i.e. req_mbr_activation = 'manual').
	 *
	 * @see		http://expressionengine.com/developers/extension_hooks/cp_members_validate_members/
	 * @access	public
	 * @return	void
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
			$this->_ee->campaigner_model->subscribe_member($member_id);
		}
	}
	
	
	/**
	 * Handles the `member_member_register` hook. Used when the membership preferences
	 * are set to "No activation required" (i.e. req_mbr_activation = 'none').
	 *
	 * @see		http://expressionengine.com/developers/extension_hooks/member_member_register/
	 * @access	public
	 * @param	array 			$member_data		Member data.
	 * @param 	int|string		$member_id			The member ID (added in 2.0.1).
	 * @return	void
	 */
	public function on_member_member_register(Array $member_data, $member_id)
	{
		if ($this->_ee->config->item('req_mbr_activation') != 'none')
		{
			return;
		}
		
		$this->_ee->campaigner_model->subscribe_member($member_id);
	}
	
	
	/**
	 * Handles the `member_register_validate_members` hook. Used when the membership
	 * preferences are set to "Self-activation via email" (i.e. req_mbr_activation = 'email').
	 *
	 * @see		http://expressionengine.com/developers/extension_hooks/member_register_validate_members/
	 * @access	public
	 * @param	int|string 		$member_id			The member ID.
	 * @return	void
	 */
	public function on_member_register_validate_members($member_id)
	{
		if ($this->_ee->config->item('req_mbr_activation') != 'email')
		{
			return;
		}
		
		$this->_ee->campaigner_model->subscribe_member($member_id);
	}
	
	
	/**
	 * Handles the `user_edit_end` hook.
	 *
	 * @see		http://www.solspace.com/docs/detail/user_user_edit_end/
	 * @access	public
	 * @param	int|string 		$member_id				The member ID.
	 * @param 	array 			$member_data 			Additional member data.
	 * @param 	array 			$member_custom_data		Member custom field data.
	 * @return	void
	 */
	public function on_user_edit_end($member_id, Array $member_data, Array $member_custom_data)
	{
		$this->_ee->campaigner_model->subscribe_member($member_id);
	}
	
	
	/**
	 * Handles the `user_register_end` hook.
	 *
	 * @see		http://www.solspace.com/docs/detail/user_user_register_end/
	 * @access	public
	 * @param	object 			$user				Instance of the User class.
	 * @param 	int|string		$member_id 			The member ID.
	 * @return	void
	 */
	public function on_user_register_end($user, $member_id)
	{
		$this->_ee->campaigner_model->subscribe_member($member_id);
	}
	
}

/* End of file		: ext.campaigner.php */
/* File location	: third_party/campaigner/ext.campaigner.php */