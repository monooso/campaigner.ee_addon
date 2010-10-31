<?php

$this->table->set_template($cp_pad_table_template);
$this->table->add_row($api_error->get_message() .'(' .$api_error->get_code() .')');

echo $this->table->generate();
$this->table->clear();

/* End of file		: _clients_api_error.php */
/* File location	: third_party/campaigner/views/_clients_api_error.php */