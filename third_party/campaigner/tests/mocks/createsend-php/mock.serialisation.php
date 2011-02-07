<?php

/**
 * Mock CreateSend Serialisation classes.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

class Mock_CS_REST_SerialiserFactory {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_available_serialiser($log) {}
	public function check_encoding($data) {}
}



class Mock_CS_REST_NativeJsonSerialiser {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_format() {}
	public function get_type() {}
	public function is_available() {}
	public function serialise($data) {}
	public function deserialise($text) {}
}



class Mock_CS_REST_ServicesJsonSerialiser {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function get_content_type() {}
	public function get_format() {}
	public function get_type() {}
	public function serialise($data) {}
	public function deserialise($text) {}
}



/* End of file			: mock.serialisation.php */
/* File location		: third_party/campaigner/tests/mocks/createsend-php/mock.serialisation.php */