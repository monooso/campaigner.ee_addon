<?php

/**
 * Campaigner Trigger Field tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package 	Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_trigger_field.php';

class Test_campaigner_trigger_field extends Testee_unit_test_case {
	
	private $_props;
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Runs before each test.
	 *
	 * @access	public
	 * @return	void
	 */
	public function setUp()
	{
		parent::setUp();
		
		$this->_props = array(
			'id'		=> 'm_field_id_10',
			'label'		=> 'Favourite Colour',
            'options'   => array(
                new Campaigner_trigger_field_option(array('id' => 'GBP', 'label' => 'Pound Sterling')),
                new Campaigner_trigger_field_option(array('id' => 'EUR', 'label' => 'Euro'))
            ),
			'type'		=> Campaigner_trigger_field::DATATYPE_SELECT
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$field = new Campaigner_trigger_field($this->_props);
		
		foreach ($this->_props AS $key => $val)
		{
			$method = 'get_' .$key;
			$this->assertIdentical($val, $field->$method());
		}
	}
	
	
	public function test_constructor__invalid_property()
	{
		$this->_props['INVALID'] = 'INVALID';
		
		// If no error is thrown, we're golden.
		new Campaigner_trigger_field($this->_props);
	}
	
	
	public function test_add_option__success()
	{
		$field = new Campaigner_trigger_field($this->_props);
        $option = new Campaigner_trigger_field_option(array(
            'id'    => 'USD',
            'label' => 'U.S. Dollar'
        ));

		$this->_props['options'][] = $option;
		$this->assertIdentical($this->_props['options'], $field->add_option($option));
	}
	
	
	public function test_set_label__invalid_values()
	{
		$field = new Campaigner_trigger_field($this->_props);
		
		$this->assertIdentical($this->_props['label'], $field->set_label(NULL));
		$this->assertIdentical($this->_props['label'], $field->set_label(FALSE));
		$this->assertIdentical($this->_props['label'], $field->set_label(100));
		$this->assertIdentical($this->_props['label'], $field->set_label(new StdClass()));
	}
	
	
	public function test_set_type__invalid_values()
	{
		$field = new Campaigner_trigger_field($this->_props);
		
		$this->assertIdentical($this->_props['type'], $field->set_type(NULL));
		$this->assertIdentical($this->_props['type'], $field->set_type(FALSE));
		$this->assertIdentical($this->_props['type'], $field->set_type(100));
		$this->assertIdentical($this->_props['type'], $field->set_type('checkbox'));
		$this->assertIdentical($this->_props['type'], $field->set_type(new StdClass()));
	}
	
	
	public function test_to_array__success()
	{
		$field = new Campaigner_trigger_field($this->_props);
		$field_array = $field->to_array();

        $expected_result = $this->_props;
        $expected_result['options'] = array();

        foreach ($this->_props['options'] AS $option)
        {
            $expected_result['options'][] = $option->to_array();
        }
		
		ksort($expected_result);
		ksort($field_array);
		
		$this->assertIdentical($expected_result, $field_array);
	}
	
	
}


/* End of file		: test.campaigner_trigger_field.php */
/* File location	: third_party/campaigner/tests/test.campaigner_trigger_field.php */
