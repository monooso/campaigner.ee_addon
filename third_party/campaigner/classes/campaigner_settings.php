<?php

/**
 * Settings.
 *
 * @author			: Stephen Lewis
 * @copyright		: Experience Internet
 * @package			: Example Add-on
 */

class Example_addon_settings {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * Setting A.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_setting_a = '';
	
	/**
	 * Setting B.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_setting_b = '';
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Constructor.
	 *
	 * @access	public
	 * @param 	array 		$properties		Initial settings.
	 * @return	void
	 */
	public function __construct(Array $properties = array())
	{
		foreach ($properties AS $property => $value)
		{
			$method_name = 'set_' .$property;
			
			if (method_exists($this, $method_name))
			{
				$this->$method_name($value);
			}
		}
	}
	
	
	/**
	 * Returns setting A.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_setting_a()
	{
		return $this->_setting_a;
	}
	
	
	/**
	 * Returns setting B.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_setting_b()
	{
		return $this->_setting_b;
	}
	
	
	/**
	 * Sets setting A.
	 *
	 * @access	public
	 * @param 	string	$setting_a		Setting A.
	 * @return	string
	 */
	public function set_setting_a($setting_a)
	{
		$this->_setting_a = $setting_a;
		return $this->get_setting_a();
	}
	
	
	/**
	 * Sets setting B.
	 *
	 * @access	public
	 * @param 	string	$setting_b		Setting B.
	 * @return	string
	 */
	public function set_setting_b($setting_b)
	{
		$this->_setting_b = $setting_b;
		return $this->get_setting_b();
	}
	
	
	/**
	 * Returns the instance as an array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function to_array()
	{
		return array(
			'setting_a' => $this->get_setting_a(),
			'setting_b' => $this->get_setting_b()
		);
	}
	
}

/* End of file		: example_addon_settings.php */
/* File location	: third_party/example_addon/classes/example_addon_settings.php */