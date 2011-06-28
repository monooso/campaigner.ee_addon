<?php

$this->table->set_template($cp_pad_table_template);

if ($error_log)
{
	$this->table->set_heading(array(
		lang('tbl_hd_error_date'),
		lang('tbl_hd_error_code'),
		lang('tbl_hd_error_message')
	));
	
	foreach ($error_log AS $error)
	{
		$this->table->add_row(array(
			date('Y-m-d H:i:s', $error->get_error_date()),
			$error->get_error_code(),
			$error->get_error_message()
		));
	}
	
}
else
{
	$this->table->set_heading(lang('tbl_hd_error_log_empty'));
	$this->table->add_row(lang('msg_error_log_empty'));
}

echo $this->table->generate();
$this->table->clear();

/* End of file		: error_log.php */
/* File location	: third_party/campaigner/views/error_log.php */