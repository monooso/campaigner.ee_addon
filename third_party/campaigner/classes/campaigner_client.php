<?php

/**
 * Campaigner Client.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Campaigner_client {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * Client ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_client_id = '';
	
	/**
	 * Client name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_client_name = '';
	
	
	
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
	public function get_client_id()
	{
		return $this->_client_id;
	}
	
	
	/**
	 * Returns client name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_client_name()
	{
		return $this->_client_name;
	}
	
	
	/**
	 * Sets client ID.
	 *
	 * @access	public
	 * @param 	string		$client_id			The client ID.
	 * @return	string
	 */
	public function set_client_id($client_id)
	{
		if (is_string($client_id) OR is_int($client_id))
		{
			$this->_client_id = $client_id;
		}
		
		return $this->get_client_id();
	}
	
	
	/**
	 * Sets client name.
	 *
	 * @access	public
	 * @param 	string		$client_name		The client name.
	 * @return	string
	 */
	public function set_client_name($client_name)
	{
		if (is_string($client_name))
		{
			$this->_client_name = $client_name;
		}
		
		return $this->get_client_name();
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
			'client_id'		=> $this->get_client_id(),
			'client_name'	=> $this->get_client_name()
		);
	}
	
}

/* End of file		: campaigner_client.php */
/* File location	: third_party/campaigner/classes/campaigner_client.php */
