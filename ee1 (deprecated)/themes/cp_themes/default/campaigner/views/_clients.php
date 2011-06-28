<h2><?php echo $lang->line('client_title'); ?></h2>

<?php if ($all_clients): ?>

<div class="info"><?php echo $lang->line('client_info'); ?></div>

<table cellpadding="0" cellspacing="0">
	<tbody>
		<tr class="odd">
			<th width="15%" style="width : 25%;"><label for="client_id"><?php echo $lang->line('client_label') .'<span>' .$lang->line('client_hint'). '</span>'; ?></label></th>
			<td>
				<select id="client_id" name="client_id" tabindex="30">
					<?php
					foreach($all_clients AS $client)
					{
						echo '<option value="' .$client['client_id'] .'"';
						echo $client['client_id'] == $settings['client_id'] ? ' selected="selected">' : '>';
						echo $client['client_name'];
						echo '</option>';
					}
					?>
				</select>
			</td>
			<td style="width : 30%;"><span class="ajax button" id="get-lists" tabindex="40"><?php echo $lang->line('get_lists'); ?></span></td>
		</tr>
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
		$message = '<p>' .$lang->line('no_clients') .'</p>';
	}
	
	echo $message;	
	echo '</div>';

endif;
?>