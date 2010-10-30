<?php

/**
 * Campaigner custom field.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Campaigner_custom_field {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * The Campaign Monitor key.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_cm_key = '';
	
	/**
	 * The member field ID associated with the custom field.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_member_field_id = '';
	
	
	
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
	 * Returns the Campaign Monitor key.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_cm_key()
	{
		return $this->_cm_key;
	}
	
	
	/**
	 * Returns the member field ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_member_field_id()
	{
		return $this->_member_field_id;
	}
	
	
	/**
	 * Sets the Campaign Monitor key.
	 *
	 * @access	public
	 * @param 	string		$cm_key		The Campaign Monitor key.
	 * @return	string
	 */
	public function set_cm_key($cm_key)
	{
		$this->_cm_key = $cm_key;
		return $this->get_cm_key();
	}
	
	
	/**
	 * Sets the member field ID.
	 *
	 * @access	public
	 * @param 	string		$member_field_id		The member field ID.
	 * @return	string
	 */
	public function set_member_field_id($member_field_id)
	{
		$this->_member_field_id = $member_field_id;
		return $this->get_member_field_id();
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
			'cm_key'			=> $this->get_cm_key(),
			'member_field_id'	=> $this->get_member_field_id()
		);
	}
	
}

/* End of file		: campaigner_custom_field.php */
/* File location	: third_party/campaigner/classes/campaigner_custom_field.php */