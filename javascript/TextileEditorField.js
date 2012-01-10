(function($) {
	$(function() {
		$('textarea.TextileEditorField').livequery(function() {
			$(this).each(function() {
				convertInputToTextileEditorField(jQuery(this));
			})
		});
	});
})(jQuery);

function convertInputToTextileEditorField( field ) {
	var $ = jQuery;
	var $field = $(field);

	// Don't attempt to enable fields which have already been enabled
	if( $field.hasClass('TextileEditorFieldEnabled') ) {
		return;
	} else {
		$field.addClass('TextileEditorFieldEnabled')
	}

	var mySettings = {
		previewParserPath:	'', // path to your Textile parser
		onShiftEnter:		{keepDefault:false, replaceWith:'\\n\\n'},
		markupSet: [
			{className:"markitup-button-heading-2", name:'Heading 2', key:'2', openWith:'h2(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
			{className:"markitup-button-heading-3", name:'Heading 3', key:'3', openWith:'h3(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
			{className:"markitup-button-heading-4", name:'Heading 4', key:'4', openWith:'h4(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
			{className:"markitup-button-heading-5", name:'Heading 5', key:'5', openWith:'h5(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
			{className:"markitup-button-heading-6", name:'Heading 6', key:'6', openWith:'h6(!(([![Class]!]))!). ', placeHolder:'Your title here...' },
			{separator:'---------------' },
			{className:"markitup-button-bold", name:'Bold', key:'B', closeWith:'*', openWith:'*'},
			{className:"markitup-button-italic", name:'Italic', key:'I', closeWith:'_', openWith:'_'},
			{separator:'---------------' },
			{className:"markitup-button-list-bullet", name:'Bulleted list', openWith:'(!(* |!|*)!)'},
			{className:"markitup-button-list-numeric", name:'Numeric list', openWith:'(!(# |!|#)!)'}, 
			{separator:'---------------' },
			{className:"markitup-button-link", name:'Link', openWith:'"', closeWith:'([![Title]!])":[![Link:!:http://]!]', placeHolder:'Your text to link here...' },
			{className:"markitup-button-quotes", name:'Quotes', openWith:'bq(!(([![Class]!]))!). '}
		]
	};

	$field.markItUp(mySettings);
}