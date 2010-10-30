<?php

/**
 * Mailing List Custom Field, returned from the Campaign Monitor API.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/helpers/EI_sanitize_helper' .EXT;

class Campaigner_api_custom_field {
	
	/* --------------------------------------------------------------
	 * CONSTANTS
	 * ------------------------------------------------------------ */
	
	/**
	 * Data types.
	 *
	 * @access	private
	 * @var		string
	 */
	const DATATYPE_MULTI_SELECT_MANY	= 'MultiSelectMany';
	const DATATYPE_MULTI_SELECT_ONE		= 'MultiSelectOne';
	const DATATYPE_NUMBER				= 'Number';
	const DATATYPE_TEXT					= 'Text';
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Key.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_key;
	
	/**
	 * Name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_name;
	
	/**
	 * Options.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_options = array();
	
	/**
	 * Type.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_type;
	
	
	
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
	 * Adds an option.
	 *
	 * @access	public
	 * @param	string	$option	The option.
	 * @return	array
	 */
	public function add_option($option)
	{
		if (is_string($option))
		{
			$this->_options[] = $option;
		}
		
		return $this->get_options();
	}
	
	
	/**
	 * Returns the key.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_key()
	{
		return $this->_key;
	}
	
	
	/**
	 * Returns the name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_name()
	{
		return $this->_name;
	}
	
	
	/**
	 * Returns the options.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_options()
	{
		return $this->_options;
	}
	
	
	/**
	 * Returns the key, sanitised for use in a form or query string.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_sanitized_key()
	{
		return sanitize_string($this->get_key());
	}
	
	
	/**
	 * Returns the type.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_type()
	{
		return $this->_type;
	}
	
	
	/**
	 * Sets the key.
	 *
	 * @access	public
	 * @param 	string		$key		The key.
	 * @return	string
	 */
	public function set_key($key)
	{
		if (is_string($key))
		{
			$this->_key = $key;
		}
		
		return $this->get_key();
	}
	
	
	/**
	 * Sets the name.
	 *
	 * @access	public
	 * @param 	string		$name		The name.
	 * @return	string
	 */
	public function set_name($name)
	{
		if (is_string($name))
		{
			$this->_name = $name;
		}
		
		return $this->get_name();
	}
	
	
	/**
	 * Sets the options.
	 *
	 * @access	public
	 * @param 	array		$options		The options.
	 * @return	array
	 */
	public function set_options(Array $options = array())
	{
		$this->_options = array();
		
		foreach ($options AS $option)
		{
			$this->add_option($option);
		}
		
		return $this->get_options();
	}
	
	
	/**
	 * Sets the type.
	 *
	 * @access	public
	 * @param 	string		$type		The type.
	 * @return	string
	 */
	public function set_type($type)
	{
		if ($this->_validate_type($type))
		{
			$this->_type = $type;
		}
		
		return $this->get_type();
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
			'key'		=> $this->get_key(),
			'name'		=> $this->get_name(),
			'options'	=> $this->get_options(),
			'type'		=> $this->get_type()
		);
	}
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Checks that the supplied string is a known data type.
	 *
	 * @access	private
	 * @param	string		$type		The string to validate.
	 * @return	bool
	 */
	private function _validate_type($type)
	{
		$valid_types = array(
			self::DATATYPE_MULTI_SELECT_MANY,
			self::DATATYPE_MULTI_SELECT_ONE,
			self::DATATYPE_NUMBER,
			self::DATATYPE_TEXT
		);
		
		return in_array($type, $valid_types);
	}
	
}

/* End of file		: campaigner_api_custom_field.php */
/* File location	: third_party/campaigner/classes/campaigner_api_custom_field.php */