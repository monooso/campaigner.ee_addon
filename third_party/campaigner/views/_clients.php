<?php

$this->table->set_template($cp_pad_table_template);

if ($clients):
	
	$this->table->set_heading(array('colspan' => '3', 'data' => lang('hd_clients')));
	
	$client_options = array('' => lang('lbl_select_client'));
	
	foreach ($clients AS $client):
		$client_options[$client->get_client_id()] = $client->get_client_name();
	endforeach;
	
	$this->table->add_row(array(
		array(
			'data'	=> lang('lbl_client', 'client_id'),
			'style'	=> 'width : 25%'
		),
		form_dropdown(
			'client_id',
			$client_options,
			$settings->get_client_id(),
			'id="client_id"'
		),
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
	
else:
	
	$this->table->set_heading(lang('hd_no_clients'));
	$this->table->add_row(lang('msg_no_clients'));

endif;

echo $this->table->generate();
$this->table->clear();

/* End of file		: _clients.php */
/* File location	: third_party/campaigner/views/_clients.php */
