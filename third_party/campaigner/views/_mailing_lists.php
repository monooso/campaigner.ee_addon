<?php

$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(
	array('data' => '', 'style' => 'width : 4%'),
	array('data' => lang('tbl_hd_list_name'), 'style' => 'width : 21%'),
	lang('tbl_hd_trigger_field'),
	array('data' => lang('tbl_hd_trigger_value'), 'style' => 'width : 15%'),
	array('data' => lang('tbl_hd_custom_fields'), 'style' => 'width : 30%;')
);

if (FALSE):
	
else:

	$this->table->add_row(array('colspan' => '5', 'data' => lang('msg_no_mailing_lists')));

endif;

echo $this->table->generate();
$this->table->clear();

/* End of file		: _mailing_lists.php */
/* File location	: third_party/campaigner/views/_mailing_lists.php */