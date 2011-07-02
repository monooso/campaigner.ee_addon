<?php

/**
 * Extension settings.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list.php';

class Campaigner_settings {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES.
	 * ------------------------------------------------------------ */
	
	/**
	 * API key.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_api_key = '';
	
	/**
	 * Client ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_client_id = '';
	
	/**
	 * Mailing lists.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_mailing_lists = array();
	
	
	
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
	 * Adds a new mailing list to the mailing lists array.
	 *
	 * @access	public
	 * @param	Campaigner_mailing_list		$list	The mailing list.
	 * @return	array
	 */
	public function add_mailing_list(Campaigner_mailing_list $list)
	{
		$this->_mailing_lists[] = $list;
		return $this->get_mailing_lists();
	}
	
	
	/**
	 * Returns API key.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_api_key()
	{
		return $this->_api_key;
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
	 * Returns the mailing lists.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_mailing_lists()
	{
		return $this->_mailing_lists;
	}
	
	
	/**
	 * Returns the specified mailing list.
	 *
	 * @access	public
	 * @param	string		$list_id	The mailing list ID.
	 * @return	Campaigner_mailing_list|FALSE
	 */
	public function get_mailing_list_by_id($list_id)
	{
		if ( ! $list_id OR ! is_string($list_id))
		{
			return FALSE;
		}
		
		foreach ($this->_mailing_lists AS $list)
		{
			if ($list->get_list_id() == $list_id)
			{
				return $list;
			}
		}
		
		return FALSE;
	}
	
	
	/**
	 * Sets API key.
	 *
	 * @access	public
	 * @param 	string		$api_key		The API key.
	 * @return	string
	 */
	public function set_api_key($api_key = '')
	{
		$this->_api_key = $api_key;
		return $this->get_api_key();
	}
	
	
	/**
	 * Sets client ID.
	 *
	 * @access	public
	 * @param 	string		$client_id		The client ID.
	 * @return	string
	 */
	public function set_client_id($client_id = '')
	{
		$this->_client_id = $client_id;
		return $this->get_client_id();
	}
	
	
	/**
	 * Sets the mailing lists array.
	 *
	 * @access	public
	 * @param	array		$mailing_lists		The mailing lists.
	 * @return	array
	 */
	public function set_mailing_lists(Array $mailing_lists = array())
	{
		$this->_mailing_lists = array();
		
		foreach ($mailing_lists AS $list)
		{
			$this->add_mailing_list($list);
		}
		
		return $this->get_mailing_lists();
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
			'api_key'		=> $this->get_api_key(),
			'client_id'		=> $this->get_client_id(),
			'mailing_lists'	=> array()
		);
		
		$mailing_lists = $this->get_mailing_lists();
		foreach ($mailing_lists AS $mailing_list)
		{
			$return_data['mailing_lists'][] = $mailing_list->to_array();
		}
		
		return $return_data;
	}
	
}

/* End of file		: campaigner_settings.php */
/* File location	: third_party/campaigner/classes/campaigner_settings.php */
