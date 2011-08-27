<?php

/**
 * Campaigner "trigger" field.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_trigger_field_option.php';
require_once PATH_THIRD .'campaigner/helpers/EI_number_helper.php';

class Campaigner_trigger_field {
	
	/* --------------------------------------------------------------
	 * CONSTANTS
	 * ------------------------------------------------------------ */
	
	/**
	 * Data type constants.
	 *
	 * @access	public
	 * @var		string
	 */
	const DATATYPE_SELECT	= 'select';
	const DATATYPE_TEXT		= 'text';
	const DATATYPE_TEXTAREA	= 'textarea';
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	private $_id;
	private $_label;
	private $_options;
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
	 * Add a field option.
	 *
	 * @access	public
	 * @param	Campaigner_trigger_field_option     $option     A trigger field option.
	 * @return	array
	 */
	public function add_option(Campaigner_trigger_field_option $option)
	{
		$this->_options[] = $option;
		return $this->get_options();
	}
	
	
	/**
	 * Returns the field ID.
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function get_id()
	{
		return $this->_id;
	}
	
	
	/**
	 * Returns the field label.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_label()
	{
		return $this->_label;
	}
	
	
	/**
	 * Returns the field options.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_options()
	{
		return $this->_options;
	}
	
	
	/**
	 * Returns the field type.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_type()
	{
		return $this->_type;
	}
	
	
	/**
	 * Populates the instance from a DB row array.
	 *
	 * @access	public
	 * @param	array		$db_row		The database row.
	 * @return	EI_member_field
	 */
	public function populate_from_db_array(Array $db_row)
	{
		$this->reset();
		
		foreach ($db_row AS $key => $val)
		{
			switch (strtolower($key))
			{
				case 'm_field_id':
					$this->set_id($val);
					break;
					
				case 'm_field_label':
					$this->set_label($val);
					break;
					
				case 'm_field_list_items':
					$this->set_options(explode("\n", $val));
					break;
					
				case 'm_field_type':
					$this->set_type($val);
					break;
			}
		}
		
		return $this;
	}
	
	
	/**
	 * Resets all the properties to their default values.
	 *
	 * @access	public
	 * @return	EI_member_field
	 */
	public function reset()
	{
		$this->_id 		= NULL;
		$this->_label	= NULL;
		$this->_options = array();
		$this->_type	= NULL;
	}
	
	
	/**
	 * Sets the field ID.
	 *
	 * @access	public
	 * @param 	mixed		$id			The field ID.
	 * @return	mixed
	 */
	public function set_id($id)
	{
		if (is_string($id) OR is_int($id))
		{
			if (valid_int($id, 1))
			{
				$id = 'm_field_id_' .$id;
			}
			
			$this->_id = $id;
		}
		
		return $this->get_id();
	}
	
	
	/**
	 * Sets the field label.
	 *
	 * @access	public
	 * @param 	string		$label		The field label.
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
	 * Sets the field options.
	 *
	 * @access	public
	 * @param	array		$options		The field options.
	 * @return	array
	 */
	public function set_options(Array $options)
	{
		$this->_options = array();
		
		foreach ($options AS $option)
		{
			$this->add_option($option);
		}
		
		return $this->get_options();
	}
	
	
	/**
	 * Sets the field type.
	 *
	 * @access	public
	 * @param	string		$type		The field type.
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
		$return = array(
			'id'		=> $this->get_id(),
			'label'		=> $this->get_label(),
			'options'	=> array(),
			'type'		=> $this->get_type()
		);

        foreach ($this->_options AS $option)
        {
            $return['options'][] = $option->to_array();
        }

        return $return;
	}
	
	
	/**
	 * Returns the instance as a database row array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function to_db_array()
	{
		return array(
			'm_field_id'			=> $this->get_id(),
			'm_field_label'			=> $this->get_label(),
			'm_field_list_items'	=> implode("\n", $this->get_options()),
			'm_field_type'			=> $this->get_type()
		);
	}
	
	
	
	/* --------------------------------------------------------------
	 * PRIVATE METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Validates the field type.
	 *
	 * @access	private
	 * @param	string	$type	The field type to validate.
	 * @return	bool
	 */
	private function _validate_type($type)
	{
		$valid_types = array(
			self::DATATYPE_SELECT,
			self::DATATYPE_TEXT,
			self::DATATYPE_TEXTAREA
		);
		
		return in_array($type, $valid_types);
	}
	
}

/* End of file		: campaigner_member_field.php */
/* File location	: third_party/campaigner/classes/campaigner_member_field.php */
