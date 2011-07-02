<?php

/**
 * Campaigner API connector. Wraps the various CreateSend API classes.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_connector.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_client.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_exception.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber.php';

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
	 * @return	void
	 */
	public function add_list_subscriber($list_id, Campaigner_subscriber $subscriber, $resubscribe = FALSE)
	{
		// Get out early.
		if ( ! $connector = $this->_factory->get_api_class_subscribers($list_id))
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_no_api_connector'));
		}

		$subscriber_data = array(
			'EmailAddress'	=> $subscriber->get_email(),
			'Name'			=> $subscriber->get_name(),
			'Resubscribe'	=> $resubscribe
		);

		if ($custom_data = $subscriber->get_custom_data())
		{
			$subscriber_data['CustomFields'] = array();

			foreach ($custom_data AS $c)
			{
				$subscriber_data['CustomFields'][] = array(
					'Key'	=> $c->get_key(),
					'Value'	=> $c->get_value()
				);
			}
		}

		$result = $connector->add($subscriber_data);

		if ( ! $result->was_successful())
		{
			throw new Campaigner_api_exception($result->response->Message, $result->response->Code);
		}
	}
	
	
	/**
	 * Retrieves the clients associated with the account.
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_clients()
	{
		// Get out early.
		if ( ! $connector = $this->_factory->get_api_class_general())
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_no_api_connector'));
		}

		$result = $connector->get_clients();

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
		// Get out early.
		if ( ! $connector = $this->_factory->get_api_class_clients($client_id))
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_no_api_connector'));
		}

		$result = $connector->get_lists();
		
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
		// Get out early.
		if ( ! $list_id OR ! is_string($list_id)
			OR ! $email OR ! is_string($email))
		{
			return FALSE;
		}

		if ( ! $connector = $this->_factory->get_api_class_subscribers($list_id))
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_no_api_connector'));
		}

		$result = $connector->get($email);

		if ( ! $result->was_successful())
		{
			return FALSE;
		}

		return (strtolower($result->response->State) == 'active');
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
		// Get out early.
		if ( ! $list_id OR ! is_string($list_id))
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_missing_or_invalid_list_id'));
		}

		if ( ! $connector = $this->_factory->get_api_class_lists($list_id))
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_no_api_connector'));
		}

		$result = $connector->get_custom_fields();
		
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
	 * @return	void
	 */
	public function remove_list_subscriber($list_id, $email)
	{
		// Get out early.
		if ( ! $list_id OR ! is_string($list_id)
			OR ! $email OR ! is_string($email))
		{
			return FALSE;
		}

		if ( ! $connector = $this->_factory->get_api_class_subscribers($list_id))
		{
			throw new Campaigner_exception($this->_ee->lang->line('error_no_api_connector'));
		}

		// Unsubscribe the member.
		$result = $connector->unsubscribe($email);
		
		// Success?
		if ( ! $result->was_successful())
		{
			throw new Campaigner_api_exception($result->response->Message, $result->response->Code);
		}
	}

}

/* End of file		: campaigner_cm_api_connector.php */
/* File location	: third_party/campaigner/classes/campaigner_cm_api_connector.php */
