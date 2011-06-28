<?php

$tabindex = 50;

$this->table->set_template($cp_pad_table_template);

if ($mailing_lists)
{
	$this->table->set_heading(
		array('data' => '', 'style' => 'width : 4%'),
		array('data' => lang('tbl_hd_list_name'), 'style' => 'width : 21%'),
		lang('tbl_hd_trigger_field'),
		array('data' => lang('tbl_hd_trigger_value'), 'style' => 'width : 20%'),
		array('data' => lang('tbl_hd_custom_fields'), 'style' => 'width : 35%;')
	);
	
	// Loop through all the available mailing lists.
	foreach ($mailing_lists AS $mailing_list)
	{
		// Shortcuts.
		$list_id	= $mailing_list->get_list_id();
		$list_name	= $mailing_list->get_list_name();
		
		$checkbox = form_checkbox(array(
			'checked'	=> $mailing_list->get_active(),
			'id' 		=> "mailing_lists[{$list_id}][checked]",
			'name' 		=> "mailing_lists[{$list_id}][checked]",
			'tabindex'	=> $tabindex += 10,
			'value' 	=> $list_id
		));
		
		$label = form_label($list_name, "mailing_lists[{$list_id}][checked]");
		
		// Trigger field.
		$trigger_field = form_dropdown(
			"mailing_lists[{$list_id}][trigger_field]",
			array_merge(array('' => lang('lbl_no_trigger_field')), $member_fields_dd_data),
			$mailing_list->get_trigger_field(),
			"id='mailing_lists[{$list_id}][trigger_field]' tabindex='" .($tabindex += 10) ."'"
		);
		
		// Trigger value.
		$trigger_value = form_input(array(
			'id'		=> "mailing_lists[{$list_id}][trigger_value]",
			'class'		=> 'field',
			'name'		=> "mailing_lists[{$list_id}][trigger_value]",
			'tabindex'	=> $tabindex += 10,
			'value' 	=> $mailing_list->get_trigger_value()
		));
		
		// Custom fields.
		if (($custom_fields = $mailing_list->get_custom_fields()))
		{
			$custom_fields_cell = '';
			
			foreach ($custom_fields AS $custom_field)
			{
				$custom_fields_cell .= '<label><span>' .$custom_field->get_label() .'</span>';
				
				$custom_fields_cell .= form_dropdown(
					"mailing_lists[{$list_id}][custom_fields][{$custom_field->get_sanitized_cm_key()}]",
					array_merge(array('' => lang('lbl_no_custom_field')), $member_fields_dd_data),
					$custom_field->get_member_field_id(),
					"id='mailing_lists[{$list_id}][custom_fields][{$custom_field->get_sanitized_cm_key()}]' tabindex='" .($tabindex += 10) ."'"
				);
				
				$custom_fields_cell .= '</label>';
			}
		}
		else
		{
			$custom_fields_cell = '<p>' .lang('msg_no_custom_fields') .'</p>';
		}
		
		$mailing_list_custom_fields = array('class' => 'stacked', 'data' => $custom_fields_cell);
		
		// Add the row to the table.
		$this->table->add_row(array(
			$checkbox,
			$label,
			$trigger_field,
			$trigger_value,
			$mailing_list_custom_fields
		));
	
	} // End of mailing lists loop.
	
}
else
{
	$this->table->set_heading(lang('hd_no_mailing_lists'));
	$this->table->add_row(lang('msg_no_mailing_lists'));
}

echo $this->table->generate();
$this->table->clear();

/* End of file		: _mailing_lists.php */
/* File location	: third_party/campaigner/views/_mailing_lists.php */