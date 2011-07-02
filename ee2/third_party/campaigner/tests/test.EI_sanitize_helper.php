<?php

/**
 * EI Sanitize Helper tests.
 *
 * @package		EI
 * @author		Stephen Lewis <stephen@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/helpers/EI_sanitize_helper.php';

class Test_EI_sanitize_helper extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_sanitize__pass()
	{
		$source_string	= '!@£$^&*(){}[]';
		$pattern 		= '#^[a-z 0-9~%\.:_\-]+$#i';		// Standard list of permitted characters.
		
		$this->assertPattern($pattern, sanitize_string($source_string));
	}
	
	
	public function test_round_trip__pass()
	{
		$source_string = '!@£$^&*(){}[]';
		$this->assertIdentical($source_string, desanitize_string(sanitize_string($source_string)));
	}
	
}

/* End of file		: test.EI_sanitize_helper.php */
/* File location	: system/tests/campaigner/test.EI_sanitize_helper.php */
