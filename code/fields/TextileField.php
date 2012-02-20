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
}