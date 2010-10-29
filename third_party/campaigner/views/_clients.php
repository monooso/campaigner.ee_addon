<?php

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(array('colspan' => '3', 'data' => lang('hd_clients')));

$this->table->add_row(array(
	array(
		'data'	=> lang('lbl_client', 'client_id'),
		'style'	=> 'width : 15%'
	),
	form_input(array(
		'id'	=> 'client_id',
		'class'	=> 'full_field',
		'name'	=> 'client_id',
		'value' => $settings->get_client_id()
	)),
	array(
		'data' => form_button(array(
			'id'		=> 'get_lists',
			'class'		=> 'submit',
			'content'	=> lang('lbl_get_lists'),
			'name'		=> 'get_lists'
		)),
		'style' => 'width : 20%;'
	)
));

echo $this->table->generate();
$this->table->clear();

/* End of file		: _clients.php */
/* File location	: third_party/campaigner/views/_clients.php */