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
require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list' .EXT;

class Campaigner_cm_api_connector extends Campaigner_api_connector {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Factory.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_factory;

	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Constructor.
	 *
	 * @access	public
	 * @param	string		$api_key		The API key.
	 * @param	object		$factory		Factory used to create the Campaign Monitor API classes, as required.
	 * @return	void
	 */
	public function __construct($api_key, $factory)
	{
		$this->_factory = $factory;
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
		$connector	= $this->_factory->get_api_class_general();
		$result		= $connector->get_clients();

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
		$connector	= $this->_factory->get_api_class_clients($client_id);
		$result		= $connector->get_lists();
		
		if ( ! $result->was_successful())
		{
			throw new Campaigner_api_exception($result->response->Message, $result->response->Code);
		}

		$lists = array();

		foreach ($result->response AS $cm_list)
		{
			$lists[] = new Campaigner_mailing_list(array(
				'custom_fields'	=> $include_fields ? $this->get_list_fields($cm_list->ListID) : array(),
				'list_id'		=> $cm_list->ListID,
				'list_name'		=> $cm_list->Name
			));
		}

		return $lists;
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
		$connector	= $this->_factory->get_api_class_lists($list_id);
		$result		= $connector->get_custom_fields();
		
		if ( ! $result->was_successful())
		{
			throw new Campaigner_api_exception($result->response->Message, $result->response->Code);
		}

		$fields = array();

		foreach ($result->response AS $cm_field)
		{
			$fields[] = new Campaigner_custom_field(array(
				'cm_key'	=> $cm_field->Key,
				'label'		=> $cm_field->FieldName
			));
		}

		return $fields;
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
