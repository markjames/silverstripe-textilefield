function convertInputToTextileEditorField( field ) {
	var $ = jQuery;
	var $field = $(field);

	// Don't attempt to enable fields which have already been enabled
	if( $field.hasClass('TextileEditorFieldEnabled') ) {
		return;
	} else {
		$field.addClass('TextileEditorFieldEnabled')
	}
	
	$field.markItUp(mySettings);
}

jQuery(document).ready(function() {
	
	// Run when page loads
	jQuery('textarea.TextileEditorField').each(function() {
		convertInputToTextileEditorField(jQuery(this));
	})

	// Run when AJAX load completes (i.e. a form is reloaded)
	jQuery('body').ajaxComplete(function() {
		jQuery('textarea.TextileEditorField').each(function() {
			convertInputToTextileEditorField(jQuery(this));
		})
	});

});