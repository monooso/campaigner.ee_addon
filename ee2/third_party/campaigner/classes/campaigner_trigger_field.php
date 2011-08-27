<?php

/**
 * Campaigner "trigger" field.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_trigger_field_option.php';

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
	 * Resets all the properties to their default values.
	 *
	 * @access	public
	 * @return	EI_member_field
	 */
	public function reset()
	{
		$this->_id 		= '';
		$this->_label	= '';
		$this->_options = array();
		$this->_type	= '';
	}
	
	
	/**
	 * Sets the field ID.
	 *
	 * @access	public
	 * @param 	string	    $id	    The field ID.
	 * @return	mixed
	 */
	public function set_id($id)
	{
        $this->_id = $id;
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
            if ( ! $option instanceof Campaigner_trigger_field_option)
            {
                continue;
            }

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

/* End of file		: campaigner_trigger_field.php */
/* File location	: third_party/campaigner/classes/campaigner_trigger_field.php */
