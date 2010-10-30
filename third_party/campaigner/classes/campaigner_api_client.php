<?php

/**
 * Client information, returned from the Campaign Monitor API.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Campaigner_api_client {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * Client ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_id = '';
	
	/**
	 * Client name.
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
	 * Returns client ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_id()
	{
		return $this->_id;
	}
	
	
	/**
	 * Returns client name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_name()
	{
		return $this->_name;
	}
	
	
	/**
	 * Sets client ID.
	 *
	 * @access	public
	 * @param 	string		$id			The client ID.
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
	 * Sets client name.
	 *
	 * @access	public
	 * @param 	string		$name		The client name.
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
		return array(
			'id'	=> $this->get_id(),
			'name'	=> $this->get_name()
		);
	}
	
}

/* End of file		: campaigner_api_client.php */
/* File location	: third_party/campaigner/classes/campaigner_api_client.php */