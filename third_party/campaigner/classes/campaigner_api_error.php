<?php

/**
 * Campaign Monitor API error.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/helpers/EI_number_helper' .EXT;

class Campaigner_api_error {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * API error code.
	 *
	 * @access	private
	 * @var		int
	 */
	private $_code = 0;
	
	/**
	 * API error message.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_message;
	
	
	
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
	 * Returns the API error code.
	 *
	 * @access	public
	 * @return	int
	 */
	public function get_code()
	{
		return $this->_code;
	}
	
	
	/**
	 * Returns the API error message.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_message()
	{
		return $this->_message;
	}
	
	
	/**
	 * Sets the API error code.
	 *
	 * @access	public
	 * @param 	int			$code			The API error code.
	 * @return	int
	 */
	public function set_code($code)
	{
		if (valid_int($code, 0))
		{
			$this->_code = $code;
		}
		
		return $this->get_code();
	}
	
	
	/**
	 * Sets the API error message.
	 *
	 * @access	public
	 * @param 	string		$message		The API error message.
	 * @return	string
	 */
	public function set_message($message)
	{
		if (is_string($message))
		{
			$this->_message = $message;
		}
		
		return $this->get_message();
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
			'code'		=> $this->get_code(),
			'message'	=> $this->get_message()
		);
	}
	
}

/* End of file		: campaigner_api_error.php */
/* File location	: third_party/campaigner/classes/campaigner_api_error.php */