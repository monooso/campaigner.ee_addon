<?php

$tabindex = 50;

$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(
	array('data' => '', 'style' => 'width : 4%'),
	array('data' => lang('tbl_hd_list_name'), 'style' => 'width : 21%'),
	lang('tbl_hd_trigger_field'),
	array('data' => lang('tbl_hd_trigger_value'), 'style' => 'width : 20%'),
	array('data' => lang('tbl_hd_custom_fields'), 'style' => 'width : 35%;')
);

if ($mailing_lists)
{
	// Prepare the member fields data for use in a dropdown.
	$member_fields_dd_data = array();
	
	foreach ($member_fields AS $member_field)
	{
		$member_fields_dd_data[$member_field->get_id()] = $member_field->get_label();
	}
	
	// Shortcut to the saved mailing lists.
	$saved_mailing_lists = $settings->get_mailing_lists();
	
	// Loop through all the available mailing lists.
	foreach ($mailing_lists AS $mailing_list)
	{
		// Extract the list information.
		$list_id	= $mailing_list->get_id();
		$list_name	= $mailing_list->get_name();
		
		// Determine whether settings have been saved for this mailing list. Hugely inefficient.
		$saved_mailing_list = new Campaigner_mailing_list();
		
		foreach ($saved_mailing_lists AS $saved_list)
		{
			if ($saved_list->get_list_id() == $list_id)
			{
				$saved_mailing_list = $saved_list;
				break;
			}
		}
		
		$checkbox = form_checkbox(array(
			'checked'	=> (bool) $saved_mailing_list->get_list_id(),
			'id' 		=> "mailing_lists[{$list_id}][checked]",
			'name' 		=> "mailing_lists[{$list_id}][checked]",
			'tabindex'	=> $tabindex += 10,
			'value' 	=> $list_id
		));
		
		$label = form_label($list_name, "mailing_lists[{$list_id}][checked]");
		
		// Trigger field. Do it the old-fashioned way.
		$trigger_field = form_dropdown(
			"mailing_lists[{$list_id}][trigger_field]",
			array_merge(array('' => lang('lbl_no_trigger_field')), $member_fields_dd_data),
			$saved_mailing_list->get_trigger_field(),
			"id='mailing_lists[{$list_id}][trigger_field]' tabindex='" .($tabindex += 10) ."'"
		);
		
		// Trigger value.
		$trigger_value = form_input(array(
			'id'		=> "mailing_lists[{$list_id}][trigger_value]",
			'class'		=> 'field',
			'name'		=> "mailing_lists[{$list_id}][trigger_value]",
			'tabindex'	=> $tabindex += 10,
			'value' 	=> $saved_mailing_list->get_trigger_value()
		));
		
		// Custom fields.
		if (($list_fields = $mailing_list->get_custom_fields())):
		
			$cell = '';
			
			foreach ($list_fields AS $list_field):
			
				$cell .= '<label><span>' .$list_field->get_name() .'</span>';
				$cell .= form_dropdown(
					"mailing_lists[{$list_id}][custom_fields][{$list_field->get_sanitized_key()}]",
					array_merge(array('' => lang('lbl_no_custom_field')), $member_fields_dd_data),
					'',
					"id='mailing_lists[{$list_id}][custom_fields][{$list_field->get_sanitized_key()}]' tabindex='" .($tabindex += 10) ."'"
				);
				$cell .= '</label>';
			
			endforeach;
				
		else:
			
			$cell = '<p>' .lang('msg_no_custom_fields') .'</p>';
	
		endif;
		
		$custom_fields = array('class' => 'stacked', 'data' => $cell);
		
		// Build the row.
		$row = array($checkbox, $label, $trigger_field, $trigger_value, $custom_fields);
		
		$this->table->add_row($row);
	
	} // End of mailing lists loop.
	
}
else
{
	$this->table->add_row(array('colspan' => '5', 'data' => lang('msg_no_mailing_lists')));
}

echo $this->table->generate();
$this->table->clear();

/* End of file		: _mailing_lists.php */
/* File location	: third_party/campaigner/views/_mailing_lists.php */