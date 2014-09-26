jQuery( document ).ready( function( $ ) {

	/*
	Set css on not validated fields
	 */
	if( SLEIPNER.validation.length ) {

		$.each( SLEIPNER.validation, function( i, value ) {
			var element = $('input[name="sleipner[' + value + ']"]');
			element.css({ 'border-width':'1px', 'border-color':'red', 'background-color':'pink' });
		});

	}

});