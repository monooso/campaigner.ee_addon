<?php

/**
 * Campaigner mailing list.
 *
 * @author			: Stephen Lewis
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field' .EXT;

class Campaigner_mailing_list {
	
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
	 * List ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_list_id = '';
	
	/**
	 * Trigger field ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_trigger_field_id = '';
	
	/**
	 * Trigger value.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_trigger_value = '';
	
	
	
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
	 * Adds a merge variable to the custom fields array.
	 *
	 * @access	public
	 * @param	Campaigner_custom_field		$custom_field		The custom field.
	 * @return	array
	 */
	public function add_custom_field(Campaigner_custom_field $custom_field)
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
	 * Returns the specified custom field.
	 *
	 * @access	public
	 * @param	string		$field_id		The custom field ID.
	 * @return	Campaigner_custom_field|FALSE
	 */
	public function get_custom_field_by_id($field_id)
	{
		if ( ! $field_id OR ! is_string($field_id))
		{
			return FALSE;
		}
		
		foreach ($this->_custom_fields AS $field)
		{
			if ($field->get_id() == $field_id)
			{
				return $field;
			}
		}
		
		return FALSE;
	}
	
	
	/**
	 * Returns the list ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_list_id()
	{
		return $this->_list_id;
	}
	
	
	/**
	 * Returns the trigger field ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_trigger_field_id()
	{
		return $this->_trigger_field_id;
	}
	
	
	/**
	 * Returns the trigger value.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_trigger_value()
	{
		return $this->_trigger_value;
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
		
		foreach ($custom_fields AS $field)
		{
			$this->add_custom_field($field);
		}
		
		return $this->get_custom_fields();
	}
	
	
	/**
	 * Sets the list ID.
	 *
	 * @access	public
	 * @param 	string		$list_id		The list ID.
	 * @return	string
	 */
	public function set_list_id($list_id)
	{
		$this->_list_id = $list_id;
		return $this->get_list_id();
	}
	
	
	/**
	 * Sets the trigger field ID.
	 *
	 * @access	public
	 * @param 	string		$trigger_field_id		The trigger field ID.
	 * @return	string
	 */
	public function set_trigger_field_id($trigger_field_id)
	{
		$this->_trigger_field_id = $trigger_field_id;
		return $this->get_trigger_field_id();
	}
	
	
	/**
	 * Sets the trigger value.
	 *
	 * @access	public
	 * @param 	string	$trigger_value		The trigger value.
	 * @return	string
	 */
	public function set_trigger_value($trigger_value)
	{
		$this->_trigger_value = $trigger_value;
		return $this->get_trigger_value();
	}
	
	
	/**
	 * Returns the instance as an array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function to_array()
	{
		$return_data = array(
			'custom_fields'		=> array(),
			'list_id'			=> $this->get_list_id(),
			'trigger_field_id'	=> $this->get_trigger_field_id(),
			'trigger_value'		=> $this->get_trigger_value()
		);
		
		// Custom fields.
		$custom_fields = $this->get_custom_fields();
		foreach ($custom_fields AS $custom_field)
		{
			$return_data['custom_fields'][] = $custom_field->to_array();
		}
		
		return $return_data;
	}
	
}

/* End of file		: campaigner_mailing_list.php */
/* File location	: third_party/campaigner/classes/campaigner_mailing_list.php */