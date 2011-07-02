<?php

/**
 * Campaigner mailing list.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field.php';

class Campaigner_mailing_list {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * Active list?
	 *
	 * @access	private
	 * @var		bool
	 */
	private $_active;
	
	/**
	 * Custom fields.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_custom_fields;
	
	/**
	 * List ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_list_id;
	
	/**
	 * List name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_list_name;
	
	
	/**
	 * Trigger field.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_trigger_field;
	
	/**
	 * Trigger value.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_trigger_value;
	
	
	
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
			/**
			 * If the `custom_fields` property is passed as a string,
			 * chances are it's a serialised array, direct from the database.
			 *
			 * We attempt to handle this gracefully, by unserialising the
			 * array, and creating the necessary Campaigner_custom_field objects.
			 */

			if ($property == 'custom_fields' && ! is_array($value))
			{
				$custom_fields = array();

				if (is_string($value))
				{
					$fields_data = unserialize($value);

					if (is_array($fields_data))
					{
						foreach ($fields_data AS $field_data)
						{
							$custom_fields[] = new Campaigner_custom_field($field_data);
						}
					}
				}

				$value = $custom_fields;
			}

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
	 * Returns whether the list is active.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function get_active()
	{
		return $this->_active;
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
	 * @param	string		$cm_key		The Campaign Monitor key.
	 * @return	Campaigner_custom_field|FALSE
	 */
	public function get_custom_field_by_cm_key($cm_key)
	{
		if ( ! $cm_key OR ! is_string($cm_key))
		{
			return FALSE;
		}
		
		foreach ($this->_custom_fields AS $field)
		{
			if ($field->get_cm_key() == $cm_key)
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
	 * Returns the list name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_list_name()
	{
		return $this->_list_name;
	}
	
	
	/**
	 * Returns the trigger field.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_trigger_field()
	{
		return $this->_trigger_field;
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
	 * Resets the instance variables.
	 *
	 * @access	public
	 * @return	Campaigner_mailing_list
	 */
	public function reset()
	{
		$this->_active 			= FALSE;
		$this->_custom_fields	= array();
		$this->_list_id 		= '';
		$this->_list_name		= '';
		$this->_trigger_field	= '';
		$this->_trigger_value	= '';
		
		return $this;
	}
	
	
	/**
	 * Sets whether the list is active.
	 *
	 * @access	public
	 * @param 	bool		$active		Is the list active?
	 * @return	bool
	 */
	public function set_active($active)
	{
		if (is_bool($active))
		{
			$this->_active = $active;
		}
		
		return $this->get_active();
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
	 * Sets the list name.
	 *
	 * @access	public
	 * @param 	string		$list_name		The list name.
	 * @return	string
	 */
	public function set_list_name($list_name)
	{
		if (is_string($list_name))
		{
			$this->_list_name = $list_name;
		}
		
		return $this->get_list_name();
	}
	
	
	/**
	 * Sets the trigger field.
	 *
	 * @access	public
	 * @param 	string		$trigger_field		The trigger field.
	 * @return	string
	 */
	public function set_trigger_field($trigger_field)
	{
		$this->_trigger_field = $trigger_field;
		return $this->get_trigger_field();
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
			'active'			=> $this->get_active(),
			'custom_fields'		=> array(),
			'list_id'			=> $this->get_list_id(),
			'list_name'			=> $this->get_list_name(),
			'trigger_field'		=> $this->get_trigger_field(),
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
