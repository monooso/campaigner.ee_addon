<?php

/**
 * Trigger field options.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Campaigner_trigger_field_option {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	private $_id;
	private $_label;

	
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
	 * Returns the option ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_id()
	{
		return $this->_id;
	}
	
	
	/**
	 * Returns the option label.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_label()
	{
		return $this->_label;
	}
	
	
	/**
	 * Resets all the properties to their default values.
	 *
	 * @access	public
	 * @return	EI_member_field
	 */
	public function reset()
	{
		$this->_id 		= '';
		$this->_label	= '';
	}
	
	
	/**
	 * Sets the option ID.
	 *
	 * @access	public
	 * @param 	string	    $id			The string ID.
	 * @return	string
	 */
	public function set_id($id)
	{
		if (is_string($id) OR is_numeric($id))
		{
			$this->_id = (string) $id;
		}
		
		return $this->get_id();
	}
	
	
	/**
	 * Sets the option label.
	 *
	 * @access	public
	 * @param 	string		$label		The option label.
	 * @return	string
	 */
	public function set_label($label)
	{
		if (is_string($label))
		{
			$this->_label = $label;
		}
		
		return $this->get_label();
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
			'id'		=> $this->get_id(),
			'label'		=> $this->get_label()
		);
	}
	
	
}

/* End of file		: campaigner_trigger_field_option.php */
/* File location	: third_party/campaigner/classes/campaigner_trigger_field_option.php */
