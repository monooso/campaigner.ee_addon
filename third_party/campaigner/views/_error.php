<?php

$this->table->set_heading(lang('hd_api_error'));
$this->table->set_template($cp_pad_table_template);
$this->table->add_row($error_message .' (' .$error_code .')');

echo $this->table->generate();
$this->table->clear();

/* End of file		: _error.php */
/* File location	: third_party/campaigner/views/_error.php */
