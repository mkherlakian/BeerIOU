<?php

/**
 * Comparison of two fields
 * 
 * @link http://framework.zend.com/manual/en/zend.form.elements.html
 * @author mkherlakian
 *
 */
class Biou_Validate_FieldCompare extends Zend_Validate_Abstract
{
	const NOT_MATCH = 'notMatch';
	const NO_FIELD = 'noField';
	const INVALID_FIELD = 'invalidField';
	
	protected $_field = null;
	protected $_fieldLabel = null;
	
	protected $_messageTemplates = array(
				self::NOT_MATCH => 'Field %field% does not match',
				self::NO_FIELD => 'No field to compare against supplied',
				self::INVALID_FIELD => 'Invalid field to compare against supplied'
			);
	
	protected $_messageVariables = array(
			'field' => '_field',
	);	
	
	public function __construct($options = array()) {
		if(is_array($options)){
			if(array_key_exists('field', $options)) {
				$this->_field = $options['field'];
			}
			if(array_key_exists('field_label', $options)) {
				$this->_fieldLabel = $options['field_label'];
				$this->_messageVariables['field'] = '_fieldLabel';
			}
		} elseif (is_string($options)) {
			$this->_field = $options;
		}
	}
	
	public function isValid($value, $context=null) {
		$this->_setValue($value);
		
		if(is_null($this->field)) {
			$this->_error(self::NO_FIELD);
			return false;
		}
		if(!is_array($context) || !array_key_exists($this->_field, $context)) {
			$this->_error(self::INVALID_FIELD);
			return false;
		}
		if(strcmp($context[$this->_field], $value) !== 0) {
			$this->_error(self::NOT_MATCH);
			return false;
		}
		
		return true;
	}
}