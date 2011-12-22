<?php

class TextileEditorField extends TextareaField
{
	/**
	 * Includes the JavaScript neccesary for this field to work using the {@link Requirements} system.
	 */
	public static function include_js() {
		
		Requirements::clear('textilefield/javascript/TextileEditorField.js');
		Requirements::clear('textilefield/javascript/TextileEditorField.js');
		Requirements::javascript('textilefield/javascript/TextileEditorField.js');
		
		Requirements::css('textilefield/thirdparty/markitup/sets/textile/style.css');
		Requirements::css('textilefield/css/TextileEditorField.css');

		Requirements::javascript('textilefield/thirdparty/markitup/jquery.markitup.js');
		self::include_js_config();
		
		Requirements::javascript('textilefield/javascript/TextileEditorField.js');

	}

	public static function include_js_config() {

		Requirements::customScript( <<<TEXTILESETTINGS

		mySettings = {
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
		}
TEXTILESETTINGS

		);

	}
	
	/**
	 * @see TextareaField::__construct()
	 */
	public function __construct($name, $title = null, $rows = 30, $cols = 20, $value = '', $form = null) {
		parent::__construct($name, $title, $rows, $cols, $value, $form);
		
		$this->addExtraClass('TextileEditorField');
		
		self::include_js();
	}

	/**
	 * @return string
	 */
	function Field() {
		// mark up broken links
		$value  = new SS_HTMLValue($this->value);
		
		if($links = $value->getElementsByTagName('a')) foreach($links as $link) {
			$matches = array();
			
			if(preg_match('/\[sitetree_link id=([0-9]+)\]/i', $link->getAttribute('href'), $matches)) {
				if(!DataObject::get_by_id('SiteTree', $matches[1])) {
					$class = $link->getAttribute('class');
					$link->setAttribute('class', ($class ? "$class ss-broken" : 'ss-broken'));
				}
			}
		}
		
		return $this->createTag (
			'textarea',
			array (
				'class'   => $this->extraClass(),
				'rows'    => $this->rows,
				'cols'    => $this->cols,
				'style'   => 'width: 100%; box-sizing: border-box; height: ' . ($this->rows * 11) . 'px', // prevents horizontal scrollbars
				'tinymce' => 'true',
				'id'      => $this->id(),
				'name'    => $this->name
			),
			htmlentities($value->getContent(), ENT_COMPAT, 'UTF-8')
		);
	}
}

/**
 * Readonly version of an {@link TextileEditorField}.
 * @package forms
 * @subpackage fields-formattedinput
 */
class TextileEditorField_Readonly extends ReadonlyField {
	function Field() {
		$valforInput = $this->value ? Convert::raw2att($this->value) : "";
		return "<span class=\"readonly typography\" id=\"" . $this->id() . "\">" . ( $this->value && $this->value != '<p></p>' ? $this->value : '<i>(not set)</i>' ) . "</span><input type=\"hidden\" name=\"".$this->name."\" value=\"".$valforInput."\" />";
	}
	function Type() {
		return 'TextileEditorfield readonly';
	}
}

/**
 * External toolbar for the TextileEditorField.
 * This is used by the CMS
 * @package forms
 * @subpackage fields-formattedinput
 */
class TextileEditorField_Toolbar extends RequestHandler {
	protected $controller, $name;
	
	function __construct($controller, $name) {
		parent::__construct();
		Requirements::javascript(SAPPHIRE_DIR . "/thirdparty/behaviour/behaviour.js");
		Requirements::javascript(SAPPHIRE_DIR . "/javascript/tiny_mce_improvements.js");
		
		Requirements::javascript(SAPPHIRE_DIR ."/thirdparty/jquery-form/jquery.form.js");
		Requirements::javascript(SAPPHIRE_DIR ."/javascript/TextileEditorField.js");
		
		$this->controller = $controller;
		$this->name = $name;
	}

	/**
	 * Searches the SiteTree for display in the dropdown
	 *  
	 * @return callback
	 */	
	function siteTreeSearchCallback($sourceObject, $labelField, $search) {
		return DataObject::get($sourceObject, "\"MenuTitle\" LIKE '%$search%' OR \"Title\" LIKE '%$search%'");
	}
	
	/**
	 * Return a {@link Form} instance allowing a user to
	 * add links in the TinyMCE content editor.
	 *  
	 * @return Form
	 */
	function LinkForm() {
		$siteTree = new TreeDropdownField('internal', _t('TextileEditorField.PAGE', "Page"), 'SiteTree', 'ID', 'MenuTitle', true);
		// mimic the SiteTree::getMenuTitle(), which is bypassed when the search is performed
		$siteTree->setSearchFunction(array($this, 'siteTreeSearchCallback'));
		
		$form = new Form(
			$this->controller,
			"{$this->name}/LinkForm", 
			new FieldSet(
				new LiteralField('Heading', '<h2><img src="cms/images/closeicon.gif" alt="' . _t('TextileEditorField.CLOSE', 'close').'" title="' . _t('TextileEditorField.CLOSE', 'close') . '" />' . _t('TextileEditorField.LINK', 'Link') . '</h2>'),
				new OptionsetField(
					'LinkType',
					_t('TextileEditorField.LINKTO', 'Link to'), 
					array(
						'internal' => _t('TextileEditorField.LINKINTERNAL', 'Page on the site'),
						'external' => _t('TextileEditorField.LINKEXTERNAL', 'Another website'),
						'anchor' => _t('TextileEditorField.LINKANCHOR', 'Anchor on this page'),
						'email' => _t('TextileEditorField.LINKEMAIL', 'Email address'),
						'file' => _t('TextileEditorField.LINKFILE', 'Download a file'),			
					)
				),
				$siteTree,
				new TextField('external', _t('TextileEditorField.URL', 'URL'), 'http://'),
				new EmailField('email', _t('TextileEditorField.EMAIL', 'Email address')),
				new TreeDropdownField('file', _t('TextileEditorField.FILE', 'File'), 'File', 'Filename', 'Title', true),
				new TextField('Anchor', _t('TextileEditorField.ANCHORVALUE', 'Anchor')),
				new TextField('LinkText', _t('TextileEditorField.LINKTEXT', 'Link text')),
				new TextField('Description', _t('TextileEditorField.LINKDESCR', 'Link description')),
				new CheckboxField('TargetBlank', _t('TextileEditorField.LINKOPENNEWWIN', 'Open link in a new window?')),
				new HiddenField('Locale', null, $this->controller->Locale)
			),
			new FieldSet(
				new FormAction('insert', _t('TextileEditorField.BUTTONINSERTLINK', 'Insert link')),
				new FormAction('remove', _t('TextileEditorField.BUTTONREMOVELINK', 'Remove link'))
			)
		);
		
		$form->loadDataFrom($this);
		
		$this->extend('updateLinkForm', $form);
		
		return $form;
	}

}