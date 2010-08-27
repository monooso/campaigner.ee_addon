<?php

echo form_open($action_url, '', $hidden_fields);
$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(array(
	array('data' => lang('setting_heading'), 'style' => 'width : 25%'),
	lang('value_heading')
));

$this->table->add_row(array(
	lang('setting_a', 'setting_a'),
	form_input(array(
		'id'	=> 'setting_a',
		'class'	=> 'full_field',
		'name'	=> 'setting_a',
		'value' => $settings->get_setting_a()
	))
));

$this->table->add_row(array(
	lang('setting_b', 'setting_b'),
	form_input(array(
		'id'	=> 'setting_b',
		'class'	=> 'full_field',
		'name'	=> 'setting_b',
		'value' => $settings->get_setting_b()
	))
));

echo $this->table->generate();

echo '<div class="submit_wrapper">';
echo form_submit(array('name' => 'submit', 'value' => lang('save_settings'), 'class' => 'submit'));
echo '</div>';
echo form_close();


/* End of file		: settings.php */
/* File location	: third_party/example_addon/views/settings.php */