<div id="campaigner">

<?php

echo form_open($action_url, '', $hidden_fields);

$this->table->set_template($cp_pad_table_template);
$this->table->set_heading(array('colspan' => '3', 'data' => lang('hd_api_title')));

$this->table->add_row(array(
	array(
		'data' 	=> lang('lbl_api_key', 'api_key'),
		'style'	=> 'width : 25%'
	),
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
			'content'	=> lang('lbl_get_clients'),
			'name'		=> 'get_clients'
		)),
		'style' => 'width : 20%;'
	)
));

echo $this->table->generate();
$this->table->clear();

?>

<!-- Clients -->
<div id="campaigner_clients"></div>

<!-- Mailing Lists -->
<div id="campaigner_lists"></div>

<?php

echo '<div class="submit_wrapper">';
echo form_submit(array('name' => 'submit', 'value' => lang('lbl_save_settings'), 'class' => 'submit'));
echo '</div>';
echo form_close();

?>

</div><!-- /#campaigner -->
