<?php

/**
 * Campaigner API connector. Wraps the various CreateSend API classes.
 *
 * @author      : Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright   : Experience Internet
 * @package     : Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_connector.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_client.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_custom_field.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_exception.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_mailing_list.php';
require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber.php';

class Campaigner_cm_api_connector extends Campaigner_api_connector {

  private $_factory;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   string    $api_key    The API key.
   * @param   object    $factory    Factory used to create the Campaign Monitor 
   *                                API classes, as required.
   * @return  void
   */
  public function __construct($api_key, $factory)
  {
    $this->_factory = $factory;
    parent::__construct($api_key);
  }


  /**
   * Adds a subscriber to the specified mailing list.
   *
   * @access  public
   * @param   string                  $list_id        The list ID.
   * @param   Campaigner_subscriber   $subscriber     The subscriber.
   * @param   bool                    $resubscribe    Automatically resubscribe?
   * @return  void
   */
  public function add_list_subscriber($list_id,
    Campaigner_subscriber $subscriber, $resubscribe = FALSE
  )
  {
    // Get out early.
    if ( ! $connector = $this->_factory->get_api_class_subscribers($list_id))
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_no_api_connector'));
    }

    $subscriber_data = array(
      'EmailAddress'  => $subscriber->get_email(),
      'Name'          => $subscriber->get_name(),
      'Resubscribe'   => $resubscribe
    );

    if ($custom_data = $subscriber->get_custom_data())
    {
      $subscriber_data['CustomFields'] = array();

      foreach ($custom_data AS $c)
      {
        $subscriber_data['CustomFields'][] = array(
          'Key'   => $c->get_key(),
          'Value' => $c->get_value()
        );
      }
    }

    $result = $this->_make_connector_request(
      $connector, 'add', array($subscriber_data));

    if ( ! $result->was_successful())
    {
      throw new Campaigner_api_exception($result->response->Message,
        $result->response->Code);
    }
  }


  /**
   * Custom error handler. Used when making a connector request. Must be
   * public.
   *
   * @access  public
   * @param   int       $error_level      The error level.
   * @param   string    $error_message    The error message.
   * @param   string    $error_file       The file in which the error occurred.
   *                                      Optional.
   * @param   int       $error_line       The line of which the error occurred.
   *                                      Optional.
   * @param   Array     $error_context    An array of variables from the scope
   *                                      in which the error occurred. Optional.
   * @return  void
   */
  public function error_handler($error_level, $error_message,
    $error_file = NULL, $error_line = NULL, Array $error_context = array()
  )
  {
    throw new Campaigner_exception($error_message);
  }


  /**
   * Retrieves the clients associated with the account.
   *
   * @access  public
   * @return  array
   */
  public function get_clients()
  {
    // Get out early.
    if ( ! $connector = $this->_factory->get_api_class_general())
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_no_api_connector'));
    }

    $result = $this->_make_connector_request($connector, 'get_clients');

    if ( ! $result->was_successful())
    {
      throw new Campaigner_api_exception(
        $result->response->Message, $result->response->Code);
    }

    $clients = array();

    foreach ($result->response AS $cm_client)
    {
      $clients[] = new Campaigner_client(array(
        'client_id'   => $cm_client->ClientID,
        'client_name' => $cm_client->Name
      ));
    }

    return $clients;
  }


  /**
   * Retrieves the mailing lists associated with the specified client.
   *
   * @access  public
   * @param   string  $client_id        The client ID.
   * @param   bool    $include_fields   Retrieve custom fields for each list?
   * @return  array
   */
  public function get_client_lists($client_id, $include_fields = FALSE)
  {
    // Get out early.
    if ( ! $connector = $this->_factory->get_api_class_clients($client_id))
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_no_api_connector'));
    }

    $result = $this->_make_connector_request($connector, 'get_lists');

    if ( ! $result->was_successful())
    {
      throw new Campaigner_api_exception(
        $result->response->Message, $result->response->Code);
    }

    $lists = array();

    foreach ($result->response AS $cm_list)
    {
      $lists[] = new Campaigner_mailing_list(array(
        'custom_fields' => $include_fields
                            ? $this->get_list_fields($cm_list->ListID)
                            : array(),
        'list_id'       => $cm_list->ListID,
        'list_name'     => $cm_list->Name
      ));
    }

    return $lists;
  }


  /**
   * Returns whether the specified email address is subscribed to the specified 
   * mailing list.
   *
   * @access  public
   * @param   string    $list_id    The list ID.
   * @param   string    $email      The email address.
   * @return  bool
   */
  public function get_is_subscribed($list_id, $email)
  {
    // Get out early.
    if ( ! $list_id OR ! is_string($list_id) OR ! $email OR ! is_string($email))
    {
      return FALSE;
    }

    if ( ! $connector = $this->_factory->get_api_class_subscribers($list_id))
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_no_api_connector'));
    }

    $result = $this->_make_connector_request($connector, 'get', array($email));

    if ( ! $result->was_successful())
    {
      return FALSE;
    }

    return (strtolower($result->response->State) == 'active');
  }


  /**
   * Retrieves the custom fields for the specified list.
   *
   * @access  public
   * @param   string    $list_id    The list ID.
   * @return  array
   */
  public function get_list_fields($list_id)
  {
    // Get out early.
    if ( ! $list_id OR ! is_string($list_id))
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_missing_or_invalid_list_id'));
    }

    if ( ! $connector = $this->_factory->get_api_class_lists($list_id))
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_no_api_connector'));
    }

    $result = $this->_make_connector_request($connector, 'get_custom_fields');

    if ( ! $result->was_successful())
    {
      throw new Campaigner_api_exception(
        $result->response->Message, $result->response->Code);
    }

    $fields = array();

    foreach ($result->response AS $cm_field)
    {
      $fields[] = new Campaigner_custom_field(array(
        'cm_key'  => $cm_field->Key,
        'label'   => $cm_field->FieldName
      ));
    }

    return $fields;
  }


  /**
   * Removes the specified subscriber from the specified mailing list.
   *
   * @access  public
   * @param   string    $list_id    The list ID.
   * @param   string    $email      The subscriber's email address.
   * @return  void
   */
  public function remove_list_subscriber($list_id, $email)
  {
    // Get out early.
    if ( ! $list_id OR ! is_string($list_id) OR ! $email OR ! is_string($email))
    {
      return FALSE;
    }

    if ( ! $connector = $this->_factory->get_api_class_subscribers($list_id))
    {
      throw new Campaigner_exception(
        $this->EE->lang->line('error_no_api_connector'));
    }

    $result = $this->_make_connector_request(
      $connector, 'unsubscribe', array($email));

    // Success?
    if ( ! $result->was_successful())
    {
      throw new Campaigner_api_exception(
        $result->response->Message, $result->response->Code);
    }
  }



  /* --------------------------------------------------------------
   * PRIVATE METHODS
   * ------------------------------------------------------------ */

  /**
   * Makes a 'connector' request. Sets a custom error handler prior to calling
   * the connector method, and removes it afterwards, in order to handle
   * 'trigger_error' statements in the CM library.
   *
   * @access  private
   * @param   mixed     $connector    The connector object.
   * @param   string    $method       The method to call.
   * @param   Array     $arguments    An array of method arguments. Optional.
   * @return  mixed
   */
  private function _make_connector_request($connector, $method,
    Array $arguments = array()
  )
  {
    set_error_handler(array($this, 'error_handler'));

    try
    {
      $result = call_user_func_array(array($connector, $method), $arguments);
    }
    catch (Campaigner_exception $e)
    {
      /**
       * The exception is thrown by self::_error_handler. We catch it here
       * in order to restore the previous error handler.
       */

      restore_error_handler();
      throw $e;
    }

    restore_error_handler();
    return $result;
  }


}


/* End of file    : campaigner_cm_api_connector.php */
/* File location  : third_party/campaigner/classes/campaigner_cm_api_connector.php */
