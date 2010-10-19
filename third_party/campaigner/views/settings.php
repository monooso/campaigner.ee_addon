<?php

echo form_open($action_url, '', $hidden_fields);
$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(array('colspan' => '3', 'data' => lang('api_title')));

/*
$this->table->set_heading(array(
	array('data' => lang('setting_heading'), 'style' => 'width : 25%'),
	lang('value_heading')
));
*/

$this->table->add_row(array(
	lang('api_key', 'api_key'),
	form_input(array(
		'id'	=> 'api_key',
		'class'	=> 'full_field',
		'name'	=> 'api_key',
		'value' => $settings->get_api_key()
	)),
	array(
		'data' => form_button(array(
			'id'		=> 'get_clients',
			'class'		=> 'submit',
			'content'	=> lang('get_clients'),
			'name'		=> 'get_clients'
		)),
		'style' => 'width : 25%;'
	)
));

echo $this->table->generate();

echo '<div class="submit_wrapper">';
echo form_submit(array('name' => 'submit', 'value' => lang('save_settings'), 'class' => 'submit'));
echo '</div>';
echo form_close();


/* End of file		: settings.php */
/* File location	: third_party/campaigner/views/settings.php */