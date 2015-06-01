jQuery(function( $ ) {
	
	$.feedback({
		postBrowserInfo: false,
		postHTML: false,
		postURL: false,
		highlightElement: false,
        ajaxURL: window.rollerblade_ajax_url,
        ajaxNonce: window.rollerblade_nonce,
        feedbackButton: '#rollerblade-button',
        tpl: {
			description:	'<div id="feedback-welcome"><p>Feedback lets you send us suggestions about our products. We welcome problem reports, feature ideas and general comments.</p><p>Start by writing a brief description:</p><textarea id="feedback-note-tmp"></textarea><p>Next we\'ll let you identify areas of the page related to your description.</p><button id="feedback-welcome-next" class="feedback-next-btn feedback-btn-gray">Next</button><div id="feedback-welcome-error">Please enter your description</div><div class="feedback-box-top"><p>····</p></div><div class="feedback-wizard-close"></div></div>',
			overview:		'<div id="feedback-overview"><div id="feedback-overview-description"><div id="feedback-overview-description-text"><div id="feedback-overview-error">Please enter your description</div><h3 class="feedback-additional">Additional info</h3><div id="feedback-additional-none"><span>None</span></div><div id="feedback-browser-info"><span>Browser Info</span></div><div id="feedback-page-info"><span>Page Info</span></div><div id="feedback-page-structure"><span>Page Structure</span></div></div></div><div id="feedback-overview-screenshot"><h3>Screenshot</h3></div><div class="feedback-buttons"><button id="feedback-overview-back" class="feedback-back-btn feedback-btn-gray">cancel</button><button id="feedback-submit" class="feedback-submit-btn feedback-btn-blue">submit</button></div><div class="feedback-box-top"><p></p></div><div class="feedback-wizard-close"></div></div>',
			submitSuccess:	'<div id="feedback-submit-success"><div id="success-rb-close-icon"></div><div id="success-rb-icon"></div><div id="rb-success-message">Your comment was<br />submitted as Ticket<div id="rb-ticket-id"></div></div><a href="#" id="rb-success-ticket-link">View on Rollerblade</a></div>',
			submitError:	'<div id="feedback-submit-error"><p>An error occured while sending your feedback. Please try again.</p><button class="feedback-close-btn feedback-btn-blue">OK</button><div class="feedback-box-top"><p></p></div><div class="feedback-wizard-close"></div></div>'
		},
		onClose: function() {
			
			//hide mouse tip as well
			$( '#mouse-tip' ).hide();
			
			$( 'body' ).removeClass( 'rollerblade-active' );
			
			$( '#rollerblade-button' ).css({ height: '50px', backgroundPosition: '0 0' });
			
		}
    });
	
	$( '#rollerblade-button' ).hover(function() {
		
		if ( ! $( 'body' ).hasClass( 'rollerblade-active' ) ) {
			
			$( this ).css({ height: '100px', backgroundPosition: '0 -50px' });
			
		}
		
	}, function() {
		
		if ( ! $( 'body' ).hasClass( 'rollerblade-active' ) ) {
			
			$( this ).css({ height: '50px', backgroundPosition: '0 0' });
			
		}
		
	});
	
	$( '#rollerblade-button' ).draggable({		//make rollerblade button draggable
		handle: '#rb-button-drag-area',
		cursor: 'pointer'
	});
	
	//make rollerblade button and comment box draggable without breaking the click event
	$( '#rb-button-drag-area' ).click(function( event ) {
		
		event.stopPropagation();
		
	});
	
	//apply dynamic css
	$( '#rb-tickets-link' ).hover(function() {
		
		$( this ).parent().css({ backgroundPosition: '0 -150px' });
		
	}, function() {
		
		$( this ).parent().css({ backgroundPosition: '0 -50px' });
		
	});
	
});
