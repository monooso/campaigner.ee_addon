/**
 * Campaigner control panel JavaScript.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

(function($) {
	
	var loading = false;
	
	/**
	 * Retrieves the 'clients' HTML via AJAX.
	 *
	 * @return 	void
	 */
	function getClients() {
		if (loading) return;
		
		apiKey = $('#api_key').val();
		
		if ( ! apiKey) {
			alert(EE.campaigner.lang.missingApiKey);
			$('#api_key').focus();
			return;
		}
		
		// Load the mailing lists HTML.
		startLoading();
		
		$.get(
			EE.campaigner.ajaxUrl,
			{request : 'get_clients', api_key : apiKey},
			handleGetClientsResponse,
			'html'
		);
	}
	
	
	/**
	 * Retrieves the 'mailing lists' HTML via AJAX.
	 *
	 * @return	void
	 */
	function getLists() {
		
		if (loading) return;
		
		apiKey = $('#api_key').val();
		clientId = $('#client_id').val();
		
		if ( ! apiKey) {
			alert(EE.campaigner.lang.missingApiKey);
			$('#api_key').focus();
			return;
		}
			
		if ( ! clientId) {
			alert(EE.campaigner.lang.missingClientId);
			$('#client_id').focus();
			return;
		}
		
		// Load the mailing lists HTML.
		startLoading();
		
		$.get(
			EE.campaigner.ajaxUrl,
			{request : 'get_mailing_lists', api_key : apiKey, client_id : clientId},
			handleGetListsResponse,
			'html'
		);
	};
	
	
	/**
	 * Handles the getClients AJAX response.
	 *
	 * @param 	string 		response		The AJAX response in JSON format.
	 * @return 	void
	 */
	function handleGetClientsResponse(response) {
		$('#campaigner_clients').html(eval(response));
		iniGetListsLink();
		stopLoading();
	}
	
	
	/**
	 * Handles the getLists AJAX response.
	 *
	 * @param 	string		response		The AJAX response in JSON format.
	 * @return	void
	 */
	function handleGetListsResponse(response) {
		$('#campaigner_lists').html(eval(response));
		//iniTriggerFields();
		stopLoading();
	};
	
	
	/**
	 * Hijacks the 'Get Clients' link.
	 *
	 * @return	void
	 */
	function iniGetClientsLink() {
		$('#get_clients')
			.bind('click', function(e) {getClients();})
			.bind('keydown', function(e) {
				if (e.keyCode == '13' || e.keyCode == '32') {
					$(e.target).click();
				}
			});
	}
	
	
	/**
	 * Hijacks the 'Get Mailing Lists' link.
	 *
	 * @return 	void
	 */
	function iniGetListsLink() {
		$('#get_lists')
			.bind('click', function(e) {getLists();})
			.bind('keydown', function(e) {
				if (e.keyCode == '13' || e.keyCode == '32') {
					$(e.target).click();
				}
			});
	};
	
	
	/**
	 * Initialises the 'loading' message.
	 *
	 * @return	void
	 */
	function iniLoadingMessage() {
		$('body').append('<div id="campaigner_loading"><p></p></div>');
	}
	
	
	/**
	 * Starts the loading animation.
	 *
	 * @return 	void
	 */
	function startLoading() {
		loading = true;
		
		$('#campaigner_loading').css({
			'top'		: $(window).scrollTop(),
			'left'		: $(window).scrollLeft(),
			'width'		: $(window).width(),
			'height'	: $(window).height()
		});

		$(window).bind('scroll', function() {
			$('#campaigner_loading').css({'top' : $(window).scrollTop(), 'left' : $(window).scrollLeft()});
		}).bind('resize', function() {
			$('#campaigner_loading').css({'width' : $(window).width(), 'height' : $(window).height()});
		});
		
		$('#campaigner_loading').fadeIn('fast');
	};
	
	
	/**
	 * Stops the loading animation.
	 *
	 * @return 	void
	 */
	function stopLoading() {
		loading = false;
		
		$(window).unbind('scroll').unbind('resize');
		$('#campaigner_loading').fadeOut('fast');
	};
	
	
	
	// Run when the page has loaded.
	$('document').ready(function() {
		iniGetClientsLink();
		iniGetListsLink();
		iniLoadingMessage();
	});

})(window.jQuery);


/* End of file		: cp.js */
/* File location	: themes/third_party/campaigner/js/cp.js */