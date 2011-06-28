<div id="sjl">
	<div id="masthead" class="clearfix">
		<h1><?php echo $lang->line('extension_name') ." <em>v{$version}</em>"; ?></h1>
		<ul>
			<li class="active"><a href="#settings" title="<?php echo $lang->line('settings_link_title'); ?>"><?php echo $lang->line('settings_link'); ?></a></li>
			<li><a href="#error-log" title="<?php echo $lang->line('error_log_link_title'); ?>"><?php echo $lang->line('error_log_link'); ?></a></li>
			<li><a href="<?php echo $docs_url; ?>" title="<?php echo $lang->line('docs_link_title'); ?>" target="_blank"><?php echo $lang->line('docs_link'); ?></a></li>
			<li><a href="<?php echo $support_url; ?>" title="<?php echo $lang->line('support_link_title'); ?>" target="_blank"><?php echo $lang->line('support_link'); ?></a></li>
		</ul>
	</div><!-- #masthead -->

	<!-- Settings -->
	<div class="content-block" id="settings">
		<?php echo $form_open; ?>
		
			<!-- API Key -->
			<fieldset>
				<h2><?php echo $lang->line('api_key_title'); ?></h2>
				<table cellpadding="0" cellspacing="0">
					<tbody>
						<tr class="odd">
							<th style="width : 25%;">
								<label for="api_key"><?php echo $lang->line('api_key_label') .'<span>' .$lang->line('api_key_hint'). '</span>'; ?></label>
							</th>
							<td><input class="text" id="api_key" name="api_key" tabindex="10" type="text" value="<?php echo $settings['api_key']; ?>" /></td>
							<td style="width : 30%;"><span class="ajax button" id="get-clients" tabindex="20"><?php echo $lang->line('get_clients'); ?></span></td>
						</tr>
					</tbody>
				</table>
			</fieldset>
			
			<!-- Clients -->
			<fieldset id="clients">
				<?php if ($all_clients) include($themes_path .'views/_clients.php'); ?>
			</fieldset>
			
			<!-- Lists -->
			<fieldset id="lists">
				<?php if ($all_mailing_lists) include($themes_path .'views/_lists.php'); ?>
			</fieldset>

			<!-- Save -->
			<fieldset class="submit">
				<input type="submit" value="<?php echo $lang->line('save_settings'); ?>" />
			</fieldset>
		</form>
	</div><!-- /#settings -->
	
	<?php include($themes_path .'views/_error_log.php'); ?>
	
	<div id="loading"><p><img src="<?php echo $themes_url .'img/loading.gif' ;?>" /></p></div>
	<?php include($js_include); ?>
</div><!-- /#sjl -->