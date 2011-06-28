<h2><?php echo $lang->line('list_title'); ?></h2>

<?php

$tabindex = 50;
if ($all_mailing_lists): ?>

<div class="info"><?php echo $lang->line('list_info'); ?></div>
<table cellpadding="0" cellspacing="0">
	<thead>
		<th style="width : 5%;">&nbsp;</th>
		<th style="width : 20%;"><?php echo $lang->line('list_name_label'); ?></th>
		<th><?php echo $lang->line('list_trigger_field_label'); ?></th>
		<th><?php echo $lang->line('list_trigger_value_label'); ?></th>
		<th style="width : 30%;"><?php echo $lang->line('list_custom_fields_label'); ?></th>
	</thead>
	
	<tbody>
		<?php
		
		$count = 0;
		foreach($all_mailing_lists AS $mailing_list):
			$count++;
			$class = $count % 2 ? 'odd' : 'even';
		
			// Start with some defaults.
			$member_field_type 		= 'text';
			$member_field_options	= array();
			$trigger_field			= '';
			$trigger_value			= '';
			
			// Is this mailing list 'active'.
			$active_list = array_key_exists($mailing_list['list_id'], $settings['mailing_lists'])
				? $settings['mailing_lists'][$mailing_list['list_id']]
				: array();
		
			if ($active_list)
			{
				$trigger_field 		= $active_list['trigger_field'];
				$trigger_value 		= $active_list['trigger_value'];
				$member_field_type	= array_key_exists($trigger_field, $member_fields)
					? $member_fields[$trigger_field]['type']
					: '';
				
				if ($member_field_type == 'select')
				{
					$member_field_options = $member_fields[$trigger_field]['options'];
				}
			}
		?>
		<tr class="<?php echo $class; ?>" valign="top">
			<td>
				<input id="mailing_lists[<?php echo $mailing_list['list_id']; ?>][checked]" 
					name="mailing_lists[<?php echo $mailing_list['list_id']; ?>][checked]"
					tabindex="<?php echo $tabindex += 10; ?>"
					type="checkbox"
					<?php echo ($active_list ? 'checked="checked"' : ''); ?>
					value="<?php echo $mailing_list['list_id']; ?>" />
			</td>
			<td><label for="mailing_lists[<?php echo $mailing_list['list_id']; ?>][checked]"><?php echo $mailing_list['list_name']; ?></label></td>
			<td>
				<select id="mailing_lists[<?php echo $mailing_list['list_id']; ?>][trigger_field]"
					name="mailing_lists[<?php echo $mailing_list['list_id']; ?>][trigger_field]"
					tabindex="<?php echo $tabindex += 10; ?>">
					<option value=""><?php echo $lang->line('list_trigger_dd_hint')?></option>
					<?php
					foreach ($member_fields AS $member_field)
					{
						echo '<option value="' .$member_field['id'] .'"';
						echo $member_field['id'] == $trigger_field ? 'selected="selected"' : '';
						echo '>' .$member_field['label'] .'</option>';
					}
					?>
				</select>
			</td>
			<td>
				<?php if ($member_field_type == 'select'): ?>
				
				<select id="mailing_lists[<?php echo $mailing_list['list_id']; ?>][trigger_value]"
					name="mailing_lists[<?php echo $mailing_list['list_id']; ?>][trigger_value]"
					tabindex="<?php echo $tabindex += 10; ?>">
					<?php
					foreach ($member_field_options AS $member_field_option)
					{
						echo '<option value="' .$member_field_option .'"';
						echo $member_field_option == $trigger_value ? 'selected="selected"' : '';
						echo '>' .$member_field_option .'</option>';
					}
					?>
				</select>
				
				<?php else: ?>
				
				<input id="mailing_lists[<?php echo $mailing_list['list_id']; ?>][trigger_value]"
					name="mailing_lists[<?php echo $mailing_list['list_id']; ?>][trigger_value]"
					tabindex="<?php echo $tabindex += 10; ?>"
					type="text"
					value="<?php echo $trigger_value; ?>" />
				
				<?php endif; ?>
			</td>
			<td class="nested">
				<table cellpadding="0" cellspacing="0">
					<tbody>
						<?php
						if ($mailing_list['custom_fields']):
							foreach ($mailing_list['custom_fields'] AS $custom_field):
							
								$active_custom_field = array_key_exists('custom_fields', $active_list)
									&& array_key_exists($custom_field['field_id'], $active_list['custom_fields'])
									? $active_list['custom_fields'][$custom_field['field_id']]['member_field_id']
									: FALSE;
						?>
						<tr>
							<td style="width : 50%;"><label><?php echo $custom_field['field_name']; ?></label></td>
							<td>
							<select
								id="mailing_lists[<?php echo $mailing_list['list_id']; ?>][custom_fields][<?php echo $custom_field['safe_field_id']; ?>]"
								name="mailing_lists[<?php echo $mailing_list['list_id']; ?>][custom_fields][<?php echo $custom_field['safe_field_id']; ?>]"
								tabindex="<?php echo $tabindex += 10; ?>">
								
								<option value=""><?php echo $lang->line('list_custom_field_dd_hint')?></option>
								<?php
								foreach ($member_fields AS $member_field)
								{
									echo '<option value="' .$member_field['id'] .'"';
									echo $member_field['id'] == $active_custom_field ? 'selected="selected"' : '';
									echo '>' .$member_field['label'] .'</option>';
								}
								?>
							</select>
							</td>
						</tr>
						<?php
							endforeach;
						else:
						?>
						<tr><td>&nbsp;</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php
else:

	echo '<div class="info">';
	
	if ($errors)
	{
		$message = '<p>';
		foreach($errors AS $error)
		{
			$message .= $error['error_message'] .'<br />';
		}
		$message .= '</p>';
	}
	else
	{
		$message = '<p>' .$lang->line('no_lists') .'</p>';
	}
	
	echo $message;	
	echo '</div>';

endif;
?>