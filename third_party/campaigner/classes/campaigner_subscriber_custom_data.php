<?php

/**
 * Subscriber custom data datatype.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Campaigner_subscriber_custom_data {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * The Campaign Monitor custom field key.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_key;
	
	/**
	 * Value.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_value;
	


	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Constructor.
	 *
	 * @access	public
	 * @param	array 		$props		Instance properties.
	 * @return	void
	 */
	public function __construct(Array $props = array())
	{
		$this->reset();

		foreach ($props AS $key => $val)
		{
			$method_name = 'set_' .$key;
			if (method_exists($this, $method_name))
			{
				$this->$method_name($val);
			}
		}
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
	 * Returns the value.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_value()
	{
		return $this->_value;
	}


	/**
	 * Resets the instance properties.
	 *
	 * @access	public
	 * @return	Campaigner_subscriber_custom_data
	 */
	public function reset()
	{
		$this->_key		= '';
		$this->_value	= '';

		return $this;
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
	 * Sets the value.
	 *
	 * @access	public
	 * @param 	string	$value		The value.
	 * @return	string
	 */
	public function set_value($value)
	{
		if (is_string($value))
		{
			$this->_value = $value;
		}
		
		return $this->get_value();
	}
	

	/**
	 * Converts the instance to an array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function to_array()
	{
		return array(
			'key'	=> $this->get_key(),
			'value'	=> $this->get_value()
		);
	}

}


/* End of file		: campaigner_subscriber_custom_data.php */
/* File location	: third_party/campaigner/classes/campaigner_subscriber_custom_data.php */
