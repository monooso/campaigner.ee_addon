<?php

/**
 * Campaigner Error Log Entry class.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/helpers/EI_number_helper' .EXT;

class Campaigner_error_log_entry {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * Error code.
	 *
	 * @access	private
	 * @var		int
	 */
	private $_error_code;
	
	/**
	 * Error date.
	 *
	 * @access	private
	 * @var		int
	 */
	private $_error_date;
	
	/**
	 * Error log entry ID.
	 *
	 * @access	private
	 * @var		int
	 */
	private $_error_log_id;
	
	/**
	 * Error message.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_error_message;
	
	
	
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
		$this->reset();
		
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
	 * Returns the error code.
	 *
	 * @access	public
	 * @return	int
	 */
	public function get_error_code()
	{
		return $this->_error_code;
	}
	
	
	/**
	 * Returns the error date.
	 *
	 * @access	public
	 * @return	int
	 */
	public function get_error_date()
	{
		return $this->_error_date;
	}
	
	
	/**
	 * Returns the error log ID.
	 *
	 * @access	public
	 * @return	int
	 */
	public function get_error_log_id()
	{
		return $this->_error_log_id;
	}
	
	
	/**
	 * Returns the error message.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_error_message()
	{
		return $this->_error_message;
	}
	
	
	/**
	 * Resets the instance variables.
	 *
	 * @access	public
	 * @return	Campaigner_error_log_entry
	 */
	public function reset()
	{
		$this->_error_code		= 0;
		$this->_error_date 		= 0;
		$this->_error_log_id	= 0;
		$this->_error_message	= '';
		
		return $this;
	}
	
	
	/**
	 * Sets the error code.
	 *
	 * @access	public
	 * @param 	int		$error_code		The error code.
	 * @return	int
	 */
	public function set_error_code($error_code)
	{
		if (valid_int($error_code, 1, 999))
		{
			$this->_error_code = $error_code;
		}
		
		return $this->get_error_code();
	}
	
	
	/**
	 * Sets the error date.
	 *
	 * @access	public
	 * @param 	int		$error_date		The error date.
	 * @return	int
	 */
	public function set_error_date($error_date)
	{
		if (valid_int($error_date, 0))
		{
			$this->_error_date = $error_date;
		}
		
		return $this->get_error_date();
	}
	
	
	/**
	 * Sets the error log ID.
	 *
	 * @access	public
	 * @param 	int		$error_log_id		The error log ID.
	 * @return	int
	 */
	public function set_error_log_id($error_log_id)
	{
		if (valid_int($error_log_id, 1))
		{
			$this->_error_log_id = $error_log_id;
		}
		
		return $this->get_error_log_id();
	}
	
	
	/**
	 * Sets the error message.
	 *
	 * @access	public
	 * @param 	string		$error_message		The error message.
	 * @return	string
	 */
	public function set_error_message($error_message)
	{
		if (is_string($error_message))
		{
			$this->_error_message = $error_message;
		}
		
		return $this->get_error_message();
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
			'error_code'	=> $this->get_error_code(),
			'error_date'	=> $this->get_error_date(),
			'error_log_id'	=> $this->get_error_log_id(),
			'error_message'	=> $this->get_error_message()
		);
	}
	
}

/* End of file		: campaigner_error_log_entry.php */
/* File location	: third_party/campaigner/classes/campaigner_error_log_entry.php */