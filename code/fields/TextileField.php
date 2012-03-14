<?php

class TextileField extends DBField implements CompositeDBField {

	public $Source;
	public $Cache;

	/**
	 * @var boolean Is this record changed or not?
	 */
	protected $isChanged = false;

	/**
	 * Similiar to {@link DataObject::$db},
	 * holds an array of composite field names.
	 * Don't include the fields "main name",
	 * it will be prefixed in {@link requireField()}.
	 * 
	 * @var array $composite_db
	 */
	static $composite_db = array(
		'Source' => 'Text',
		'' => 'Text'
	);

	/**
	 * Returns the value of this field.
	 * @return mixed
	 */
	function getValue() {
		return $this->Source;
	}

	/**
	 * Set the value of this field in various formats.
	 * Used by {@link DataObject->getField()}, {@link DataObject->setCastedField()}
	 * {@link DataObject->dbObject()} and {@link DataObject->write()}.
	 * 
	 * As this method is used both for initializing the field after construction,
	 * and actually changing its values, it needs a {@link $markChanged}
	 * parameter. 
	 * 
	 * @param DBField|array $value
	 * @param array $record Map of values loaded from the database
	 * @param boolean $markChanged Indicate wether this field should be marked changed. 
	 *  Set to FALSE if you are initializing this field after construction, rather
	 *  than setting a new value.
	 */
	function setValue($value, $record = null, $markChanged = true){

		if ($value instanceof TextileField) {

			$this->Source = $value->Source;
			$this->Cache = $value->Cache;

		} elseif ( $record && isset($record[$this->name . 'Source'] ) ) {

			$this->Source = $record[$this->name . 'Source'];

			if( isset($record[$this->name . '']) ) {
				$this->Cache = $record[$this->name . ''];
			} else {
				$this->Cache = '';
			}

		} else if (is_array($value)) {
			$this->Source = $value['Source'];
			$this->Cache = $value[''];
		} else if (is_string($value)) {
			$this->Source = $value;
			$this->Cache = '';
		} else {
			//user_error('Invalid value in '.get_class().'->setValue()', E_USER_ERROR);
		}
		
		if( $markChanged ) {
			$this->isChanged = true;
		}

	}

	/**
	 * Used in constructing the database schema.
	 * Add any custom properties defined in {@link $composite_db}.
	 * Should make one or more calls to {@link DB::requireField()}.
	 */
	function requireField(){
		$fields = $this->compositeDatabaseFields();
		if($fields) foreach($fields as $name => $type){
			DB::requireField($this->tableName, $this->name.$name, $type);
		}
	}
	
	/**
	 * Add the custom internal values to an INSERT or UPDATE
	 * request passed through the ORM with {@link DataObject->write()}.
	 * Fields are added in $manipulation['fields']. Please ensure
	 * these fields are escaped for database insertion, as no
	 * further processing happens before running the query.
	 * Use {@link DBField->prepValueForDB()}.
	 * Ensure to write NULL or empty values as well to allow 
	 * unsetting a previously set field. Use {@link DBField->nullValue()}
	 * for the appropriate type.
	 * 
	 * @param array $manipulation
	 */
	function writeToManipulation(&$manipulation){

		$source = (string)$this->Source;
		$manipulation['fields'][$this->name.'Source'] = $this->prepValueForDB($source);
		$manipulation['fields'][$this->name.''] = $this->prepValueForDB($this->getCache());

	}
	
	/**
	 * Add all columns which are defined through {@link requireField()}
	 * and {@link $composite_db}, or any additional SQL that is required
	 * to get to these columns. Will mostly just write to the {@link SQLQuery->select}
	 * array.
	 * 
	 * @param SQLQuery $query
	 */
	function addToQuery(&$query) {
		parent::addToQuery($query);
	}
	
	/**
	 * Return array in the format of {@link $composite_db}.
	 * Used by {@link DataObject->hasOwnDatabaseField()}.
	 * @return array
	 */
	function compositeDatabaseFields(){
		return self::$composite_db;
	}
	
	/**
	 * Determines if the field has been changed since its initialization.
	 * Most likely relies on an internal flag thats changed when calling
	 * {@link setValue()} or any other custom setters on the object.
	 * 
	 * @return boolean
	 */
	function isChanged(){
		return $this->isChanged;
	}

	function saveInto($dataObject) {
		$fieldName = $this->name;
		if($fieldName) {
			$dataObject->{$fieldName.'Source'} = $this->Source;
			
			// Recalculate cached output
			$this->Cache = '';
			$dataObject->{$fieldName.''} = $this->getCache();
		}
	}

	/**
	 * Determines if any of the properties in this field have a value,
	 * meaning at least one of them is not NULL.
	 * 
	 * @return boolean
	 */
	function hasValue(){
		return !!trim($this->Source);
	}

	/**
	 * Returns a CompositeField instance used as a default
	 * for form scaffolding.
	 *
	 * Used by {@link SearchContext}, {@link ModelAdmin}, {@link DataObject::scaffoldFormFields()}
	 * 
	 * @param string $title Optional. Localized title of the generated instance
	 * @return FormField
	 */
	public function scaffoldFormField($title = null) {
		$field = new TextareaField($this->name);
		return $field;
	}
	
	public function __toString() {
		return $this->Source;
	}

	public function getCache() {

		if( !$this->Cache && $this->Source ) {
			$textile = new Textile();
			$this->Cache = $textile->TextileThis($this->Source);
		}
		return $this->Cache;
	}

	/**
	 * @return string
	 */
	public function forTemplate() {
		return $this->getCache();
	}
	
	public function Summary($maxWords = 50){
		// get first sentence?
		// this needs to be more robust
		$data = Convert::xml2raw( $this->Source /*, true*/ );
		
		if( !$data )
			return "";
		
		// grab the first paragraph, or, failing that, the whole content
		if( strpos( $data, "\n\n" ) )
			$data = substr( $data, 0, strpos( $data, "\n\n" ) );
			
		$sentences = explode( '.', $data );	
		
		$count = count( explode( ' ', $sentences[0] ) );
		
		// if the first sentence is too long, show only the first $maxWords words
		if( $count > $maxWords ) {
			return implode( ' ', array_slice( explode( ' ', $sentences[0] ), 0, $maxWords ) ).'...';
		}
		// add each sentence while there are enough words to do so
		$result = '';
		do {
			$result .= trim(array_shift( $sentences )).'.';
			if(count($sentences) > 0) {
				$count += count( explode( ' ', $sentences[0] ) );
			}
			
			// Ensure that we don't trim half way through a tag or a link
			$brokenLink = (substr_count($result,'<') != substr_count($result,'>')) ||
				(substr_count($result,'<a') != substr_count($result,'</a'));
			
		} while( ($count < $maxWords || $brokenLink) && $sentences && trim( $sentences[0] ) );
		
		if( preg_match( '/<a[^>]*>/', $result ) && !preg_match( '/<\/a>/', $result ) )
			$result .= '</a>';
		
		$result = Convert::raw2xml( $result );
		return $result;
	}
	
	
	/**
	 * Perform context searching to give some context to searches, optionally
	 * highlighting the search term.
	 * 
	 * @param int $characters Number of characters in the summary
	 * @param boolean $string Supplied string ("keywords")
	 * @param boolean $striphtml Strip HTML?
	 * @param boolean $highlight Add a highlight <span> element around search query?
	 * @param String prefix text
	 * @param String suffix 
	 * 
	 * @return string
	 */
	function ContextSummary($characters = 500, $string = false, $striphtml = true, $highlight = true, $prefix = "... ", $suffix = "...") {

		if(!$string) $string = $_REQUEST['Search'];	// Use the default "Search" request variable (from SearchForm)

		// Remove HTML tags so we don't have to deal with matching tags
		$text = $striphtml ? strip_tags($this->Source) : $this->Source;
		
		// Find the search string
		$position = (int) stripos($text, $string);
		
		// We want to search string to be in the middle of our block to give it some context
		$position = max(0, $position - ($characters / 2));

		if($position > 0) {
			// We don't want to start mid-word
			$position = max((int) strrpos(substr($text, 0, $position), ' '), (int) strrpos(substr($text, 0, $position), "\n"));
		}

		$summary = substr($text, $position, $characters);
		$stringPieces = explode(' ', $string);
		
		if($highlight) {
			// Add a span around all key words from the search term as well
			if($stringPieces) {
			
				foreach($stringPieces as $stringPiece) {
					if(strlen($stringPiece) > 2) {
						$summary = str_ireplace($stringPiece, "<span class=\"highlight\">$stringPiece</span>", $summary);
					}
				}
			}
		}
		$summary = trim($summary);
		
		if($position > 0) $summary = $prefix . $summary;
		if(strlen($this->value) > ($characters + $position)) $summary = $summary . $suffix;
		
		return $summary;
	}
}