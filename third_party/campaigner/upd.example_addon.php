<?php if ( ! defined('BASEPATH')) exit('Invalid file request.');

/**
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Example Add-on
 */

class Example_addon_upd {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */

	/**
	 * Version.
	 *
	 * @access	public
	 * @var		string
	 */
	public $version;


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
		$this->_ee =& get_instance();

		// We need to explicitly set the package path.
		$this->_ee->load->add_package_path(PATH_THIRD .'example_addon/');
		$this->_ee->load->model('example_addon_model');
		
		$this->version = $this->_ee->example_addon_model->get_package_version();
	}


	/**
	 * Installs the module.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function install()
	{
		return $this->_ee->example_addon_model->install_module();
	}


	/**
	 * Uninstalls the module.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function uninstall()
	{
		return $this->_ee->example_addon_model->uninstall_module();
	}


	/**
	 * Updates the module.
	 *
	 * @access	public
	 * @param	string		$current_version		The current module version.
	 * @return	bool
	 */
	public function update($current_version = '')
	{
		return $this->_ee->example_addon_model->update_module();
	}

}


/* End of file		: upd.example_addon_model.php */
/* File location	: third_party/example_addon/upd.example_addon.php */