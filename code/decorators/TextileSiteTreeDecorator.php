<?php

/**
 * This decorates SiteTree, so that all HTMLEditorFields are replaced by TextileFormFields.
 */
class TextileSiteTreeDecorator extends DataObjectDecorator {

	public function extraStatics() {
		return array(
			'db' => array(
				'Content' => 'TextileField'
			)
		);
	}

	public function updateCMSFields(&$fields) {
		$toreplace = array();
		foreach($fields->dataFields() as $f) {
			if ($f instanceof HtmlEditorField) {
				$toreplace[] = $f;
			}
		}
		foreach($toreplace as $f) {
			$fields->replaceField($f->name,
				new TextileEditorField($f->name, $f->title, $f->rows, $f->cols, $f->value, $f->form)
				);
		}
	}

}