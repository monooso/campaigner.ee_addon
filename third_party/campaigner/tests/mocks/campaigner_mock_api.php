<?php

/**
 * Mock CMBase (API interface) class.
 *
 * @see			http://www.simpletest.org/en/mock_objects_documentation.html
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

class Campaigner_mock_api {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS.
	 * ------------------------------------------------------------ */
	public function clientGetLists() {}		// clientGetListsDropdown?
	public function listGetCustomFields() {}
	public function subscriberAddWithCustomFields() {}
	public function subscriberUnsubscriber() {}
	public function userGetClients() {}
	
}


/* End of file		: Campaigner_mock_api.php */
/* File location	: third_party/campaigner/tests/mocks/campaigner_mock_api.php */