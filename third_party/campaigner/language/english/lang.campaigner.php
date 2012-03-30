<?php

/**
 * Campaigner language strings.
 * 
 * @author          : Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright       : Experience Internet
 * @package         : Campaigner
 */

$lang = array(

/* --------------------------------------------------------------
 * EXTENSION : REQUIRED
 * ------------------------------------------------------------ */
'campaigner_extension_name'         => 'Campaigner',
'campaigner_extension_description'  => 'Effortlessly subscribe members of your site to one or more Campaign Monitor mailing lists.',

/* --------------------------------------------------------------
 * EXTENSION : NAVIGATION
 * ------------------------------------------------------------ */
'nav_errors'                => 'Error Log',
'nav_settings'              => 'Settings',
'nav_support'               => 'Support',

/* --------------------------------------------------------------
 * EXTENSION : SETTINGS
 * ------------------------------------------------------------ */
'hd_api_title'              => 'API Settings',
'hd_clients'                => 'Clients',
'hd_no_clients'             => 'No Clients',
'hd_no_mailing_lists'       => 'No Mailing Lists',

'lbl_api_key'               => 'API Key',
'lbl_get_clients'           => 'Get Clients',

'lbl_client'                => 'Client',
'lbl_get_lists'             => 'Get Mailing Lists',
'lbl_select_client'         => '&mdash; Select a client &mdash;',
'lbl_no_custom_field'       => '&mdash; No custom field &mdash;',
'lbl_no_trigger_field'      => '&mdash; No trigger field &mdash;',

'tbl_hd_list_name'          => 'List Name',
'tbl_hd_trigger_field'      => 'Trigger Field',
'tbl_hd_trigger_value'      => 'Trigger Value',
'tbl_hd_custom_fields'      => 'Custom Fields',

'msg_loading_custom_fields' => 'Loading custom fields&hellip;',
'msg_no_clients'            => 'There are no clients associated with this account.',
'msg_no_custom_fields'      => 'There are no custom fields for this mailing list.',
'msg_no_mailing_lists'      => 'There are no mailing lists associated with this client.',
'msg_settings_saved'        => 'Settings saved.',
'msg_settings_not_saved'    => 'Unable to save settings.',

'lbl_save_settings'         => 'Save Settings',

// 'Member fields' dropdown option group headings.
'lbl_custom_member'   => 'Custom Member Fields',
'lbl_default_member'  => 'Standard Member Fields',
'lbl_zoo_visitor'     => 'Zoo Visitor Member Fields',

/* --------------------------------------------------------------
 * JAVASCRIPT MESSAGES
 * ------------------------------------------------------------ */
'msg_missing_api_key'       => 'Please enter your Campaign Monitor API key.',
'msg_missing_client_id'     => 'Please select a client from the list.',

/* --------------------------------------------------------------
 * ERRORS
 * ------------------------------------------------------------ */
'hd_api_error'                      => 'API Error',
'api_error_missing_root_preamble'   => 'The Campaign Monitor API response is missing the root node: ',
'api_error_preamble'                => 'The Campaign Monitor API reported the following error: ',
'api_error_unable_to_unsubscribe_member' => 'Unable to unsubscribe member from mailing list.',
'api_error_unknown'                 => 'An unknown problem occurred when contacting the Campaign Monitor API.',

'error_missing_or_invalid_list_id'  => 'Missing or invalid list ID.',
'error_missing_or_invalid_member_id' => 'Missing or invalid member ID.',
'error_no_api_connector'            => 'Unable to create API connector.',
'error_unknown'                     => 'An unknown error occurred.',
'error_unknown_member'              => 'Unknown member.',
'error_unknown_ajax_request'        => 'Unknown AJAX request.',

'error_unable_to_load_custom_fields' => 'Unable to load custom fields.',

/* --------------------------------------------------------------
 * MEMBER FIELDS
 * ------------------------------------------------------------ */
'mbr_email'         => 'Email',
'mbr_group_id'      => 'Group ID',
'mbr_location'      => 'Location',
'mbr_occupation'    => 'Occupation',
'mbr_screen_name'   => 'Screen Name',
'mbr_url'           => 'URL',
'mbr_username'      => 'Username',

// All done.
'' => ''

);

/* End of file      : lang.campaigner.php */
/* File location    : third_party/campaigner/language/english/lang.campaigner.php */
