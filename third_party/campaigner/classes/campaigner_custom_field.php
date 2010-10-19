<?php

/**
 * Campaigner custom field.
 *
 * @author			: Stephen Lewis
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Campaigner_custom_field {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * The member field ID associated with the custom field.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_field_id = '';
	
	/**
	 * The Campaign Monitor custom field ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_id = '';
	
	
	
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
	 * Returns the member field ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_field_id()
	{
		return $this->_field_id;
	}
	
	
	/**
	 * Returns the Campaign Monitor custom field ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_id()
	{
		return $this->_id;
	}
	
	
	/**
	 * Sets the member field ID.
	 *
	 * @access	public
	 * @param 	string		$field_id		The member field ID.
	 * @return	string
	 */
	public function set_field_id($field_id)
	{
		$this->_field_id = $field_id;
		return $this->get_field_id();
	}
	
	
	/**
	 * Sets the Campaign Monitor custom field ID.
	 *
	 * @access	public
	 * @param 	string		$id		The Campaign Monitor custom field ID.
	 * @return	string
	 */
	public function set_id($id)
	{
		$this->_id = $id;
		return $this->get_id();
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
			'field_id'	=> $this->get_field_id(),
			'id'		=> $this->get_id()
		);
	}
	
}

/* End of file		: campaigner_custom_field.php */
/* File location	: third_party/campaigner/classes/campaigner_custom_field.php */