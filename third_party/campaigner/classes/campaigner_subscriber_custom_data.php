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
	 * ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_id;
	
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
	 * Returns the ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_id()
	{
		return $this->_id;
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
		$this->_id 		= '';
		$this->_value	= '';

		return $this;
	}
	
	
	/**
	 * Sets the ID.
	 *
	 * @access	public
	 * @param 	string		$id		The ID.
	 * @return	string
	 */
	public function set_id($id)
	{
		if (is_string($id))
		{
			$this->_id = $id;
		}

		return $this->get_id();
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
			'id'	=> $this->get_id(),
			'value'	=> $this->get_value()
		);
	}

}


/* End of file		: campaigner_subscriber_custom_data.php */
/* File location	: third_party/campaigner/classes/campaigner_subscriber_custom_data.php */