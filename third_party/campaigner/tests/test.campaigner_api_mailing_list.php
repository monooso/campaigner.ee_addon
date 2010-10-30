<?php

/**
 * Campaigner API Mailing List tests.
 *
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 * @package		Campaigner
 */

require_once PATH_THIRD .'campaigner/classes/campaigner_api_mailing_list' .EXT;

class Test_campaigner_api_mailing extends Testee_unit_test_case {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */
	
	/**
	 * Custom fields.
	 *
	 * @access	private
	 * @var		array
	 */
	private $_custom_fields;
	
	/**
	 * Properties.
	 *
	 * @access	private
	 * @var		array
	 */
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
		
		$custom_field_a_data = array(
			'key'		=> '[FullName]',
			'name'		=> 'Full Name',
			'options'	=> array(),
			'type'		=> Campaigner_api_custom_field::DATATYPE_TEXT
		);
		
		$custom_field_b_data = array(
			'key'		=> '[EmailAddress]',
			'name'		=> 'Email Address',
			'options'	=> array(),
			'type'		=> Campaigner_api_custom_field::DATATYPE_TEXT
		);
		
		$this->_custom_fields = array(
			new Campaigner_api_custom_field($custom_field_a_data),
			new Campaigner_api_custom_field($custom_field_b_data)
		);
		
		$this->_props = array(
			'custom_fields'	=> array($custom_field_a_data, $custom_field_b_data),
			'id'			=> 'LIST_ID',
			'name'			=> 'LIST_NAME'
		);
	}
	
	
	/* --------------------------------------------------------------
	 * TEST METHODS
	 * ------------------------------------------------------------ */
	
	public function test_constructor__success()
	{
		$list = new Campaigner_api_mailing_list($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['id'], $list->get_id());
		$this->assertIdentical($this->_props['name'], $list->get_name());
		$this->assertIdentical($this->_custom_fields, $list->get_custom_fields());
	}
	
	
	public function test_constructor__invalid_property()
	{
		// Dummy data.
		$this->_props['INVALID'] = 'INVALID';
		
		// Tests. If this doesn't throw an error, we're good.
		new Campaigner_api_mailing_list($this->_props);
	}
	
	
	public function test_add_custom_field__success()
	{
		$list 			= new Campaigner_api_mailing_list($this->_props);
		$custom_field 	= new Campaigner_api_custom_field(array(
			'key'		=> '[Age]',
			'name'		=> 'Age',
			'options'	=> array(),
			'type'		=> Campaigner_api_custom_field::DATATYPE_NUMBER
		));
		
		$this->_custom_fields[] = $custom_field;
		$this->assertIdentical($this->_custom_fields, $list->add_custom_field($custom_field));
	}
	
	
	public function test_set_custom_fields__objects()
	{
		$this->_props['custom_fields'] = $this->_custom_fields;
		$list = new Campaigner_api_mailing_list($this->_props);
		
		$this->assertIdentical($this->_custom_fields, $list->get_custom_fields());
	}
	
	
	public function test_set_custom_fields__invalid_values()
	{
		$this->_props['custom_fields'] = array('A', 'B', 'C');
		$list = new Campaigner_api_mailing_list($this->_props);
		
		$this->assertIdentical(array(), $list->get_custom_fields());
	}
	
	
	public function test_set_id__invalid_values()
	{
		$list = new Campaigner_api_mailing_list($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['id'], $list->set_id(FALSE));
		$this->assertIdentical($this->_props['id'], $list->set_id(NULL));
		$this->assertIdentical($this->_props['id'], $list->set_id(new stdClass()));
	}
	
	
	public function test_set_name__invalid_values()
	{
		$list = new Campaigner_api_mailing_list($this->_props);
		
		// Tests.
		$this->assertIdentical($this->_props['name'], $list->set_name(FALSE));
		$this->assertIdentical($this->_props['name'], $list->set_name(NULL));
		$this->assertIdentical($this->_props['name'], $list->set_name(100));
		$this->assertIdentical($this->_props['name'], $list->set_name(new stdClass()));
	}
	
	
	public function test_to_array()
	{
		$list = new Campaigner_api_mailing_list($this->_props);
		$list_array = $list->to_array();
		
		ksort($this->_props);
		ksort($list_array);
		
		// Tests.
		$this->assertIdentical($this->_props, $list_array);
	}
	
}


/* End of file		: test.campaigner_api_mailing_list.php */
/* File location	: third_party/campaigner/tests/test.campaigner_api_mailing_list.php */