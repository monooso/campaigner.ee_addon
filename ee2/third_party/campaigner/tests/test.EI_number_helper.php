<?php

/**
 * EI Number Helper tests.
 *
 * @package		EI
 * @author		Stephen Lewis <stephen@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

require_once PATH_THIRD .'campaigner/helpers/EI_number_helper.php';

class Test_EI_number_helper extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_valid_float__pass()
	{
		$float = 1234.56;
		
		$this->assertIdentical(valid_float($float), TRUE);
	}
	
	
	public function test_valid_float__negative_float()
	{
		$float = -1234.56;
		
		$this->assertIdentical(valid_float($float), TRUE);
	}
	
	
	public function test_valid_float__int_pass()
	{
		$float = 10;
		
		$this->assertIdentical(valid_float($float), TRUE);
	}
	
	
	public function test_valid_float__string_pass()
	{
		$float = '.56';
		
		$this->assertIdentical(valid_float($float), TRUE);
	}
	
	
	public function test_valid_float__string_fail()
	{
		$float = '1234.56sad';
		
		$this->assertIdentical(valid_float($float), FALSE);
	}
	
	
	public function test_valid_float__empty_string()
	{
		$float = '';
		
		$this->assertIdentical(valid_float($float), FALSE);
	}
	
	
	public function test_valid_float__min_pass()
	{
		$float = 100.11;
		$min = 100;
		
		$this->assertIdentical(valid_float($float, $min), TRUE);
	}
	
	
	public function test_valid_float__min_fail()
	{
		$float = 100.11;
		$min = 200;
		
		$this->assertIdentical(valid_float($float, $min), FALSE);
	}
	
	
	public function test_valid_float__max_pass()
	{
		$float = 100;
		$max = 200;
		
		$this->assertIdentical(valid_float($float, NULL, $max), TRUE);
	}
	
	
	public function test_valid_float__max_fail()
	{
		$float = 100;
		$max = 50;
		
		$this->assertIdentical(valid_float($float, NULL, $max), FALSE);
	}
	
	
	public function test_valid_float__min_max_pass()
	{
		$float = 100.11;
		$min = 100.10;
		$max = 100.12;
		
		$this->assertIdentical(valid_float($float, $min, $max), TRUE);
	}
	
	
	public function test_valid_float__min_max_junk()
	{
		$float = 100.11;
		$min = 'minimum';
		$max = 'maximum';
		
		$this->assertIdentical(valid_float($float, $min, $max), TRUE);
	}
	
	
	public function test_valid_float__min_max_swapped_pass()
	{
		$float = 100.11;
		$min = 100.12;
		$max = 100.10;
		
		$this->assertIdentical(valid_float($float, $min, $max), TRUE);
	}
	
	
	public function test_valid_float__min_max_swapped_fail()
	{
		$float = 100.1;
		$min = 120.1;
		$max = 110.1;
		
		$this->assertIdentical(valid_float($float, $min, $max), FALSE);
	}
	
	
	
	public function test_valid_int__pass()
	{
		$int = 100;
		$this->assertIdentical(valid_int($int), TRUE);
	}
	
	
	public function test_valid_int__negative_pass()
	{
		$int = -100;
		$this->assertIdentical(valid_int($int), TRUE);
	}
	
	
	public function test_valid_int__string_pass()
	{
		$int = '100';
		$this->assertIdentical(valid_int($int), TRUE);
	}
	
	
	public function test_valid_int__string_fail()
	{
		$int = '100fail';
		$this->assertIdentical(valid_int($int), FALSE);
	}
	
	
	public function test_valid_int__empty_string()
	{
		$int = '';
		$this->assertIdentical(valid_int($int), FALSE);
	}
	
	
	public function test_valid_int__min_pass()
	{
		$int = 10;
		$min = 5;
		
		$this->assertIdentical(valid_int($int, $min), TRUE);
	}
	
	
	public function test_valid_int__min_fail()
	{
		$int = 10;
		$min = 15;
		
		$this->assertIdentical(valid_int($int, $min), FALSE);
	}
	
	
	public function test_valid_int__max_pass()
	{
		$int = 10;
		$max = 15;
		
		$this->assertIdentical(valid_int($int, NULL, $max), TRUE);
	}
	
	
	public function test_valid_int__max_fail()
	{
		$int = 10;
		$max = 5;
		
		$this->assertIdentical(valid_int($int, NULL, $max), FALSE);
	}
	
	
	public function test_valid_int__min_max_pass()
	{
		$int = 10;
		$min = 5;
		$max = 15;
		
		$this->assertIdentical(valid_int($int, $min, $max), TRUE);
	}
	
	
	public function test_valid_int__min_max_junk()
	{
		$int = 10;
		$min = 'small';
		$max = 'big';
		
		$this->assertIdentical(valid_int($int, $min, $max), TRUE);
	}
	
	
	public function test_valid_int__min_max_swapped_pass()
	{
		$int = 10;
		$min = 11;
		$max = 9;
		
		$this->assertIdentical(valid_int($int, $min, $max), TRUE);
	}
	
	
	public function test_valid_int__min_max_swapped_fail()
	{
		$int = 10;
		$min = 9;
		$max = 7;
		
		$this->assertIdentical(valid_int($int, $min, $max), FALSE);
	}
	
}

/* End of file		: test.EI_number_helper.php */
/* File location	: system/tests/campaigner/test.EI_number_helper.php */
