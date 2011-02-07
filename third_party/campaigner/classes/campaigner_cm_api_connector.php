<?php

/**
 * Campaigner API connector. Wraps the various CreateSend API classes.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_connector' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_client' .EXT;
require_once PATH_THIRD .'campaigner/classes/campaigner_exception' .EXT;
require_once PATH_THIRD .'campaigner/libraries/createsend-php/csrest_general' .EXT;

class Campaigner_cm_api_connector extends Campaigner_api_connector {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Campaign Monitor API connector classes, from the CreateSend-PHP library.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_cm_api_clients;
	private $_cm_api_general;
	private $_cm_api_lists;
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Constructor.
	 *
	 * @access	public
	 * @param	string		$api_key		The API key.
	 * @return	void
	 */
	public function __construct($api_key)
	{
		parent::__construct($api_key);
	}

	
	/**
	 * Adds a subscriber to the specified mailing list.
	 *
	 * @access	public
	 * @param	string						$list_id			The list ID.
	 * @param	Campaigner_subscriber		$subscriber			The subscriber.
	 * @param 	bool						$resubscribe		Automatically resubscribe if necessary.
	 * @return	bool
	 */
	public function add_list_subscriber($list_id, Campaigner_subscriber $subscriber, $resubscribe = FALSE)
	{
		
	}
	
	
	/**
	 * Retrieves the clients associated with the account.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_clients()
	{
		if ( ! $this->_cm_api_general)
		{
			$this->_cm_api_general = new CS_REST_General($this->_api_key);
		}

		$result = $this->_cm_api_general->get_clients();

		if ( ! $result->was_successful())
		{
			throw new Campaigner_api_exception($result->response->Message, $result->response->Code);
		}

		$clients = array();

		foreach ($result->response AS $cm_client)
		{
			$clients[] = new Campaigner_client(array(
				'client_id'		=> $cm_client->ClientID,
				'client_name'	=> $cm_client->Name
			));
		}
		
		return $clients;
	}


	/**
	 * Retrieves the mailing lists associated with the specified client.
	 *
	 * @access	public
	 * @param	string		$client_id				The client ID.
	 * @param	bool		$include_fields			Retrieve the custom fields for each list?
	 * @return	array
	 */
	public function get_client_lists($client_id, $include_fields = FALSE)
	{
		
	}
	
	
	/**
	 * Returns whether the specified email address is subscribed to the specified mailing list.
	 *
	 * @access	public
	 * @param	string		$list_id		The list ID.
	 * @param	string		$email			The email address.
	 * @return	bool
	 */
	public function get_is_subscribed($list_id, $email)
	{
		
	}
	
	
	/**
	 * Retrieves the custom fields for the specified list.
	 *
	 * @access	public
	 * @param	string		$list_id		The list ID.
	 * @return	array
	 */
	public function get_list_fields($list_id)
	{
		
	}
	
	
	/**
	 * Removes the specified subscriber from the specified mailing list.
	 *
	 * @access	public
	 * @param	string		$list_id		The list ID.
	 * @param	string		$email			The subscriber's email address.
	 * @return	bool
	 */
	public function remove_list_subscriber($list_id, $email)
	{
		
	}


	/**
	 * Sets mock API connector classes. Used for testing.
	 * 
	 * @access	public
	 * @param	array		$connectors		The mock API connector classes.
	 * @return	void
	 */
	public function set_mock_api_connectors(Array $connectors = array())
	{
		foreach ($connectors AS $key => $val)
		{
			$prop_name = '_' .$key;
			$this->$prop_name = $val;		// No data validation, as this is just for testing.
		}
	}

	/**
	 * Updates the specified list subscriber.
	 *
	 * @param	string						$list_id		The list ID.
	 * @param	Campaigner_subscriber		$subscriber		The subscriber.
	 * @return	bool
	 */
	public function update_list_subscriber($list_id, Campaigner_subscriber $subscriber)
	{
		
	}
	
}

/* End of file		: campaigner_cm_api_connector.php */
/* File location	: third_party/campaigner/classes/campaigner_cm_api_connector.php */
