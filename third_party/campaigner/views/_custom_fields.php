<?php

if ($custom_fields)
{
  $custom_fields_cell = '';
  
  foreach ($custom_fields AS $custom_field)
  {
    $custom_fields_cell .= '<label>';
    $custom_fields_cell .= '<span>' .$custom_field->get_label() .'</span>';
    
    $custom_fields_cell .= form_dropdown(
      "mailing_lists[{$list_id}][custom_fields][{$custom_field->get_sanitized_cm_key()}]",
      array_merge(
        array('' => lang('lbl_no_custom_field')),
        $member_fields_dd_data
      ),
      $custom_field->get_member_field_id(),
      "id='mailing_lists[{$list_id}][custom_fields][{$custom_field->get_sanitized_cm_key()}]'
        tabindex='0'"
    );
    
    $custom_fields_cell .= '</label>';
  }
}
else
{
  $custom_fields_cell = lang('msg_no_custom_fields');
}

echo '<div class="campaigner_custom_fields">' .$custom_fields_cell .'</div>';

?>
