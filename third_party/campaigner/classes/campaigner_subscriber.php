<?php

/**
 * Subscriber datatype.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber_custom_data.php';

class Campaigner_subscriber {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Custom data.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_custom_data;

	/**
	 * Email address.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_email;
	
	/**
	 * Name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_name;



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
	 * Adds a custom data field.
	 *
	 * @access	public
	 * @param	Campaigner_subscriber_custom_data	$custom_data	The custom data field.
	 * @return	array
	 */
	public function add_custom_data(Campaigner_subscriber_custom_data $custom_data)
	{
		$this->_custom_data[] = $custom_data;
	}


	/**
	 * Returns the custom data array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_custom_data()
	{
		return $this->_custom_data;
	}


	/**
	 * Returns the email address.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_email()
	{
		return $this->_email;
	}


	/**
	 * Returns the name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_name()
	{
		return $this->_name;
	}


	/**
	 * Resets the instance properties.
	 *
	 * @access	public
	 * @return	Campaigner_subscriber
	 */
	public function reset()
	{
		$this->_email		= '';
		$this->_name		= '';
		$this->_custom_data	= array();
		
		return $this;
	}


	/**
	 * Resets the custom data array.
	 *
	 * @access	public
	 * @return	array 
	 */
	public function reset_custom_data()
	{
		$this->_custom_data = array();
		return $this->get_custom_data();
	}


	/**
	 * Sets the custom data array .
	 *
	 * @access	public
	 * @param 	array 		$custom_data		The custom data array .
	 * @return	array
	 */
	public function set_custom_data(Array $custom_data = array())
	{
		$this->reset_custom_data();

		foreach ($custom_data AS $field)
		{
			if ( ! $field instanceof Campaigner_subscriber_custom_data)
			{
				continue;
			}

			$this->add_custom_data($field);
		}
		
		return $this->get_custom_data();
	}
	
	
	/**
	 * Sets the email address.
	 *
	 * @access	public
	 * @param 	string		$email		The email address.
	 * @return	string
	 */
	public function set_email($email)
	{
		if (is_string($email))
		{
			$this->_email = $email;
		}

		return $this->get_email();
	}


	/**
	 * Sets the name.
	 *
	 * @access	public
	 * @param 	string		$name		The name.
	 * @return	string
	 */
	public function set_name($name)
	{
		if (is_string($name))
		{
			$this->_name = $name;
		}

		return $this->get_name();
	}


	/**
	 * Converts the instance to an array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function to_array()
	{
		$return_data = array(
			'custom_data'	=> array(),
			'email'			=> $this->get_email(),
			'name'			=> $this->get_name()
		);

		foreach ($this->_custom_data AS $field)
		{
			$return_data['custom_data'][] = $field->to_array();
		}

		return $return_data;
	}

}

/* End of file		: campaigner_subscriber.php */
/* File location	: third_party/campaigner/classes/campaigner_subscriber.php */
