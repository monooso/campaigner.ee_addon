<?php

/**
 * Campaigner mailing list.
 *
 * @author			: Stephen Lewis
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_merge_variable' .EXT;

class Campaigner_mailing_list {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * List ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_list_id = '';
	
	/**
	 * Merge variables.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_merge_variables = array();
	
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
	 * Adds a merge variable to the merge variables array.
	 *
	 * @access	public
	 * @param	Campaigner_merge_variable	$merge_variable		The merge variable.
	 * @return	array
	 */
	public function add_merge_variable(Campaigner_merge_variable $merge_variable)
	{
		$this->_merge_variables[] = $merge_variable;
		return $this->get_merge_variables();
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
	 * Returns the merge variables.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_merge_variables()
	{
		return $this->_merge_variables;
	}
	
	
	/**
	 * Returns the specified merge variable.
	 *
	 * @access	public
	 * @param	string		$merge_variable_id		The merge variable ID.
	 * @return	Campaigner_merge_variable|FALSE
	 */
	public function get_merge_variable_by_id($merge_variable_id)
	{
		if ( ! $merge_variable_id OR ! is_string($merge_variable_id))
		{
			return FALSE;
		}
		
		foreach ($this->_merge_variables AS $var)
		{
			if ($var->get_id() == $merge_variable_id)
			{
				return $var;
			}
		}
		
		return FALSE;
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
	 * Sets the merge variables.
	 *
	 * @access	public
	 * @param 	array		$merge_variables		The merge variables.
	 * @return	array
	 */
	public function set_merge_variables(Array $merge_variables = array())
	{
		$this->_merge_variables = array();
		
		foreach ($merge_variables AS $var)
		{
			$this->add_merge_variable($var);
		}
		
		return $this->get_merge_variables();
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
			'list_id'			=> $this->get_list_id(),
			'merge_variables'	=> array(),
			'trigger_field_id'	=> $this->get_trigger_field_id(),
			'trigger_value'		=> $this->get_trigger_value()
		);
		
		// Merge variables.
		$merge_vars = $this->get_merge_variables();
		foreach ($merge_vars AS $merge_var)
		{
			$return_data['merge_variables'][] = $merge_var->to_array();
		}
		
		return $return_data;
	}
	
}

/* End of file		: campaigner_mailing_list.php */
/* File location	: third_party/campaigner/classes/campaigner_mailing_list.php */