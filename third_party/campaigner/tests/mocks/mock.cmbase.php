<?php

/**
 * Mock CMBase (API interface) class.
 *
 * @see			http://www.simpletest.org/en/mock_objects_documentation.html
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

class Mock_CMBase {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS.
	 * ------------------------------------------------------------ */
	public function clientGetLists() {}
	public function listGetCustomFields() {}
	public function subscriberAddWithCustomFields() {}
	public function subscriberUnsubscribe() {}
	public function userGetClients() {}
	
}


/* End of file		: mock.cmbase.php */
/* File location	: third_party/campaigner/tests/mocks/mock.cmbase.php */