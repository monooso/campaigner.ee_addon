<?php

/**
 * Abstract API connector. All API connector implementations should extend this 
 * class.
 *
 * @author      : Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright   : Experience Internet
 * @package     : Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_subscriber.php';

abstract class Campaigner_api_connector {

  protected $EE;
  protected $_api_key;


  /* --------------------------------------------------------------
   * PUBLIC METHODS
   * ------------------------------------------------------------ */

  /**
   * Constructor.
   *
   * @access  public
   * @param   string    $api_key    The API key.
   * @return  void
   */
  public function __construct($api_key)
  {
    $this->EE       =& get_instance();
    $this->_api_key = $api_key;
  }



  /* --------------------------------------------------------------
   * ABSTRACT METHODS
   * ------------------------------------------------------------ */

  /**
   * Adds a subscriber to the specified mailing list.
   *
   * @abstract
   * @access  public
   * @param   string                  $list_id      The list ID.
   * @param   Campaigner_subscriber   $subscriber   The subscriber.
   * @param   bool                    $resubscribe  Automatically resubscribe?
   * @return  bool
   */
  abstract public function add_list_subscriber($list_id,
    Campaigner_subscriber $subscriber, $resubscribe = FALSE);


  /**
   * Retrieves the clients associated with the account.
   *
   * @abstract
   * @access  public
   * @return  array
   */
  abstract public function get_clients();


  /**
   * Retrieves the mailing lists associated with the specified client.
   *
   * @abstract
   * @access  public
   * @param   string    $client_id      The client ID.
   * @param   bool    $include_fields   Retrieve custom fields for each list?
   * @return  array
   */
  abstract public function get_client_lists($client_id,
    $include_fields = FALSE);


  /**
   * Returns whether the specified email address is subscribed to the specified 
   * mailing list.
   *
   * @abstract
   * @access  public
   * @param   string    $list_id    The list ID.
   * @param   string    $email      The email address.
   * @return  bool
   */
  abstract public function get_is_subscribed($list_id, $email);


  /**
   * Retrieves the custom fields for the specified list.
   *
   * @abstract
   * @access  public
   * @param   string    $list_id    The list ID.
   * @return  array
   */
  abstract public function get_list_fields($list_id);


  /**
   * Removes the specified subscriber from the specified mailing list.
   *
   * @abstract
   * @access  public
   * @param   string    $list_id    The list ID.
   * @param   string    $email      The subscriber's email address.
   * @return  bool
   */
  abstract public function remove_list_subscriber($list_id, $email);


}


/* End of file    : campaigner_api_connector.php */
/* File location  : third_party/campaigner/classes/campaigner_api_connector.php */
