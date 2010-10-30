<?php

/**
 * Mock CMBase (API interface) class.
 *
 * @see			http://www.simpletest.org/en/mock_objects_documentation.html
 * @package 	Campaigner
 * @author 		Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright	Experience Internet
 */

class Mock_CMBase {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS.
	 * ------------------------------------------------------------ */
	public function clientGetLists() {}
	public function listGetCustomFields() {}
	public function subscriberAddWithCustomFields() {}
	public function subscriberUnsubscribe() {}
	public function userGetClients() {}
	
}

class Mock_CampaignMonitor extends Mock_CMBase {
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */
	
	public function subscribersGetActive($date = 0, $list_id = NULL, $action = 'Subscribers.GetActive') {}
	public function subscribersGetUnsubscribed($date = 0, $list_id = NULL) {}
	public function subscribersGetBounced($date = 0, $list_id = NULL) {}
	public function subscriberAdd($email, $name, $list_id = NULL, $resubscribe = FALSE) {}
	public function subscriberAddRedundant($email, $name, $list_id = NULL) {}
	public function subscriberAddWithCustomFields($email, $name, $fields, $list_id = NULL, $resubscribe = FALSE) {}
	public function subscriberAddWithCustomFieldsRedundant($email, $name, $fields, $list_id = NULL) {}
	public function subscriberUnsubscribe($email, $list_id = NULL, $check_subscribed = FALSE) {}
	public function subscribersGetIsSubscribed($email, $list_id = NULL) {}
	public function checkSubscriptions($email, $lists, $no_assoc = TRUE) {}
	public function subscriberAddAndResubscribe($email, $name, $list_id = NULL) {}
	public function subscriberAddAndResubscribeWithCustomFields($email, $name, $fields, $list_id = NULL) {}
	public function subscriberGetSingleSubscriber($list_id = NULL, $email) {}
	public function  clientGeneric( $method, $client_id = NULL) {}
	public function clientGetLists($client_id = NULL) {}
	public function clientGetListsDropdown($client_id = NULL) {}
	public function clientGetSegmentsDropdown($client_id = NULL) {}
	public function clientGetCampaigns($client_id = NULL) {}
	public function clientGetSegments($client_id = NULL) {}
	public function clientGetSuppressionList($client_id = NULL) {}
	public function clientGetTemplates($client_id = NULL) {}
	public function clientGetDetail($client_id = NULL) {}
	public function clientCreate($companyName, $contactName, $emailAddress, $country, $timezone) {}
	public function clientUpdateBasics($client_id, $companyName, $contactName, $emailAddress, $country, $timezone) {}
	public function clientUpdateAccessAndBilling($client_id, $accessLevel, $username, $password, $billingType, $currency, $deliveryFee, $costPerRecipient, $designAndSpamTestFee) {}
	public function userGetClients() {}
	public function userGetSystemDate() {}
	public function userGetTimezones() {}
	public function userGetCountries() {}
	public function userGetApiKey($site_url, $username, $password) {}
	public function campaignGeneric($method, $campaign_id = NULL) {}
	public function campaignGetSummary($campaign_id = NULL) {}
	public function campaignGetOpens($campaign_id = NULL) {}
	public function campaignGetBounces($campaign_id = NULL) {}
	public function campaignGetSubscriberClicks($campaign_id = NULL) {}
	public function campaignGetUnsubscribes($campaign_id = NULL) {}
	public function campaignGetLists($campaign_id = NULL) {}
	public function campaignCreate($client_id, $name, $subject, $fromName, $fromEmail, $replyTo, $htmlUrl, $textUrl, $subscriberListIds, $listSegments) {}
	public function campaignSend($campaign_id, $confirmEmail, $sendDate) {}
	public function campaignDelete($campaign_id) {}
	public function listCreate($client_id, $title, $unsubscribePage, $confirmOptIn, $confirmationSuccessPage) {}
	public function listUpdate($list_id, $title, $unsubscribePage, $confirmOptIn, $confirmationSuccessPage) {}
	public function listDelete($list_id) {}
	public function listGetDetail($list_id) {}
	public function listGetStats($list_id) {}
	public function listCreateCustomField($list_id, $fieldName, $dataType, $options) {}
	public function listGetCustomFields($list_id) {}
	public function listDeleteCustomField($list_id, $key) {}
	public function templateCreate($client_id, $template_name, $html_url, $zip_url, $screenshot_url) {}
	public function templateGetDetail($template_id) {}
	public function templateUpdate($template_id, $template_name, $html_url, $zip_url, $screenshot_url) {}
	public function templateDelete($template_id) {}
	
}


/* End of file		: mock.cmbase.php */
/* File location	: third_party/campaigner/tests/mocks/mock.cmbase.php */