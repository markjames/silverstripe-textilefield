;Behaviour.register({
	'#Form_EditForm' : {
		initialize : function() {
			this.observeMethod('PageLoaded', this.adminPageHandler);
			this.adminPageHandler();
		},
		adminPageHandler : function() {
			// Place your custom code here.
			(function($){
				jQuery('textarea.TextileEditorField').each(function() {
					convertInputToTextileEditorField(jQuery(this));
				})
			 })(jQuery);
		}
	}
});
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