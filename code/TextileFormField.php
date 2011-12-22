<?php

class TextileFormField extends TextareaField
{
	public function __construct($name, $title = null, $rows = 5, $cols = 20, $value = '', $form = null) {

		Requirements::javascript('stageblocks/thirdparty/jquery_ui/js/jquery-ui-1.8.15.custom.min.js');
		
		Requirements::javascript('stageblocks/thirdparty/markitup/jquery.markitup.js');
		Requirements::javascript('stageblocks/thirdparty/markitup/sets/backstage-textile/set.js');
		Requirements::javascript('stageblocks/thirdparty/markitup/plugins/image-uploader/jquery.markitup.imageuploader.js');
		
		Requirements::clear('stageblocks/javascript/textile.js');
		Requirements::javascript('stageblocks/javascript/textile.js');
		
		Requirements::css('sapphire/thirdparty/jquery-ui-themes/base/jquery.ui.all.css', 'screen,projection');
		Requirements::css('sapphire/thirdparty/jquery-ui-themes/base/jquery.ui.dialog.css', 'screen,projection');
		
		Requirements::css('stageblocks/thirdparty/markitup/skins/markitup/style.css', 'screen,projection');
		Requirements::css('stageblocks/thirdparty/markitup/sets/backstage-textile/style.css', 'screen,projection');
		
		parent::__construct($name, $title, $rows, $cols, $value, $form);
	}
		
}