/**
 * Campaigner control panel JavaScript.
 *
 * @author			: Stephen Lewis <addons@experienceinternet.co.uk>
 * @copyright		: Experience Internet
 * @package			: Campaigner
 */

(function($) {
	
	var loading 	= false;
	var initialLoad	= true;
	
	/**
	 * Checks if an API key is already set. If so, an AJAX call is
	 * automatically triggered, to load the clients.
	 *
	 * @return 	void
	 */
	function autoLoadClients() {
		if ($('#api_key').val()) {
			getClients();
		}
	}
	
	
	/**
	 * Checks if a client ID is already set. If so, an AJAX call is
	 * automatically triggered, to load the mailing lists.
	 *
	 * @return 	void
	 */
	function autoLoadLists() {
		if ($('#api_key').val() && $('#client_id').val()) {
			getLists();
		} else {
			stopLoading();
		}
	}
	
	
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
		$('#campaigner_lists').empty();
		
		iniGetListsLink();
		
		if (initialLoad) {
			initialLoad = false;
			loading = false;			// Fudge. Calling stopLoading causes an unpleasant two-step loading "flash".
			autoLoadLists();
		} else {
			$('#client_id').val('');	// Reset the selected client.
			stopLoading();
		}
	}
	
	
	/**
	 * Handles the getLists AJAX response.
	 *
	 * @param 	string		response		The AJAX response in JSON format.
	 * @return	void
	 */
	function handleGetListsResponse(response) {
		$('#campaigner_lists').html(eval(response));
		iniTriggerFields();
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
	 * Extracts any JSON objects for later use.
	 *
	 * @return	void
	 */
	function iniJson() {
		EE.campaigner.memberFields = eval('(' + EE.campaigner.memberFields + ')');
	}
	
	
	/**
	 * Initialises the 'loading' message.
	 *
	 * @return	void
	 */
	function iniLoadingMessage() {
		$('body').append('<div id="campaigner_loading"><p></p></div>');
	}
	
	
	/**
	 * Adds a handler to any 'trigger field' drop-downs.
	 *
	 * @return 	void
	 */
	function iniTriggerFields() {
		
		/**
		 * No point doing any of this if we haven't got the member fields object.
		 */

		if ( ! EE.campaigner.memberFields instanceof Object) {
			return;
		}
		
		$('select[id*=trigger_field]').bind('change', function(e) {
		
			/**
			 * General Note:
			 * jQuery chokes on the field ID, presumably because it contains square brackets.
			 * We go old school to retrieve the element, using document.getElementById as required.
			 */
		
			var triggerFieldId 		= this.value;
			var triggerValueHtml 	= '';
			var triggerValueFieldId = this.id.replace('[trigger_field]', '[trigger_value]');
			var triggerValueField	= document.getElementById(triggerValueFieldId);
		
			/**
			 * If the Member field is of type "select", construct a drop-down of the
			 * available options. Otherwise stick with a text input field.
			 */
			
			if (EE.campaigner.memberFields[triggerFieldId] instanceof Object &&
				EE.campaigner.memberFields[triggerFieldId].type == 'select') {
				
				var options 			= EE.campaigner.memberFields[triggerFieldId].options;
				var currentFieldValue	= $(triggerValueField).val();
			
				triggerValueHtml += '<select name="' + triggerValueFieldId + '" '
					+'id="' + triggerValueFieldId + '" '
					+'style="display:none;" '
					+'tabindex="' + triggerValueField.tabIndex + '">';

				for (var opt in options) {
					triggerValueHtml += '<option value="' + options[opt] + '"';
					
					if (options[opt] == currentFieldValue) {
						triggerValueHtml += ' selected="selected"';
					}
					
					triggerValueHtml += '>' + options[opt] + '</option>';
				}

				triggerValueHtml += '</select>';

			} else if (document.getElementById(triggerValueFieldId).type != 'text') {
			
				triggerValueHtml += '<input type="text"' 
					+'name="' + triggerValueFieldId + '" '
					+'id="' + triggerValueFieldId + '" '
					+'class="field" '
					+'style="display:none;" '
					+'tabindex="' + triggerValueField.tabIndex + '">';
			}
		
			if (triggerValueHtml) {
				$(triggerValueField).fadeOut('normal').replaceWith(triggerValueHtml);
				$(document.getElementById(triggerValueFieldId)).fadeIn('normal');
			}
		});
		
		
		// Run the change handler on all the trigger value fields.
		$('select[id*=trigger_field]').change();
	};
	
	
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
		iniJson();
		
		iniGetClientsLink();
		iniGetListsLink();
		iniLoadingMessage();
		iniTriggerFields();
		
		autoLoadClients();
	});

})(window.jQuery);


/* End of file		: cp.js */
/* File location	: themes/third_party/campaigner/js/cp.js */