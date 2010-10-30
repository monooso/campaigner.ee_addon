<?php

/**
 * Mailing List information, returned from the Campaign Monitor API.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_custom_field' .EXT;

class Campaigner_api_mailing_list {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * Custom fields.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_custom_fields = array();
	
	/**
	 * Mailing list ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_id = '';
	
	/**
	 * Mailing list name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_name = '';
	
	
	
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
	 * Adds a custom field.
	 *
	 * @access	public
	 * @param	Campaigner_api_custom_field		$custom_field		The custom field.
	 * @return	array
	 */
	public function add_custom_field(Campaigner_api_custom_field $custom_field)
	{
		$this->_custom_fields[] = $custom_field;
		return $this->get_custom_fields();
	}
	
	
	/**
	 * Returns the custom fields.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_custom_fields()
	{
		return $this->_custom_fields;
	}
	
	
	/**
	 * Returns the mailing list ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_id()
	{
		return $this->_id;
	}
	
	
	/**
	 * Returns the mailing list name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_name()
	{
		return $this->_name;
	}
	
	
	/**
	 * Sets the custom fields.
	 *
	 * @access	public
	 * @param 	array		$custom_fields		The custom fields.
	 * @return	array
	 */
	public function set_custom_fields(Array $custom_fields = array())
	{
		$this->_custom_fields = array();
		
		foreach ($custom_fields AS $custom_field)
		{
			if (is_array($custom_field))
			{
				$this->add_custom_field(new Campaigner_api_custom_field($custom_field));
				continue;
			}
			
			if ($custom_field instanceof Campaigner_api_custom_field)
			{
				$this->add_custom_field($custom_field);
				continue;
			}
		}
		
		return $this->get_custom_fields();
	}
	
	
	/**
	 * Sets the mailing list ID.
	 *
	 * @access	public
	 * @param 	string		$id			The mailing list ID.
	 * @return	string
	 */
	public function set_id($id = '')
	{
		if (is_string($id) OR is_int($id))
		{
			$this->_id = $id;
		}
		
		return $this->get_id();
	}
	
	
	/**
	 * Sets the mailing list name.
	 *
	 * @access	public
	 * @param 	string		$name		The mailing list name.
	 * @return	string
	 */
	public function set_name($name = '')
	{
		if (is_string($name))
		{
			$this->_name = $name;
		}
		
		return $this->get_name();
	}
	
	
	/**
	 * Returns the instance as an array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function to_array()
	{
		$custom_fields = array();
		
		foreach ($this->get_custom_fields() AS $custom_field)
		{
			$custom_fields[] = $custom_field->to_array();
		}
		
		return array(
			'custom_fields'	=> $custom_fields,
			'id'			=> $this->get_id(),
			'name'			=> $this->get_name()
		);
	}
	
}

/* End of file		: campaigner_api_mailing_list.php */
/* File location	: third_party/campaigner/classes/campaigner_api_mailing_list.php */