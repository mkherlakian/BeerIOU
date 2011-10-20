<?php
class Application_Form_Invite extends Zend_Form
{
	const MODE_I_OWE = 1;
	const MODE_AM_OWED = 2;
	
	protected $_mode = 1;
	
	public function __construct($options, $mode) {
		$this->_mode = $mode;
		if(!($mode == self::MODE_I_OWE || $mode == self::MODE_AM_OWED)) {
			throw new Zend_Exception('Invalid mode');
		}
		parent::__construct($options);
	}
	
	public function init() {
		
		$this->addElement('text', 'email', array(
			'label' => $this->_mode == self::MODE_I_OWE ? 'Email of the person you owe (a) beer(s)':'Email of the person that owes you (a) beer(s)',
			'size'  => 30,
			'maxlength' => 30,
			'required' => true,
			'filters' => array('StringTrim'),
			'validators' => array('EmailAddress')
		));
		
		$this->addElement('textarea', 'reason', array(
			'label' => $this->_mode == self::MODE_I_OWE ? 'Why do you owe?':'Why are you owed?',
			'rows' => 5,
			'cols' => 34,
			'required' => true,
			'filters' => array(),
			'validators' => array(
						array('StringLength', true, array(10,512))
			)
		));
		
		$this->addElement('text', 'number_of_beers', array(
			'label' => 'How many?',
			'size'  => 3,
			'required' => true,
			'value' => 1,
			'filters' => array('Int'),
			'validators' => array('Int')
		));
		
		$this->addElement('select', 'favor', array(
			'label' => 'How big a favor was it?',
			'required' => false,
		));
		$this->getElement('favor')->addMultiOptions(array('Small' => 'Small','Big' => 'Big', 'Humongous' => 'Humungous', 'Life-saving stuff' => 'Life-saving stuff'));
		
		$this->addDisplayGroup(array('email', 'reason', 'number_of_beers', 'favor'), 'Send', array('legend' => 'Beer time!'));
		$this->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'dl', 'class' => 'fieldgroup register1')), 'Form'));

		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit');
		$this->addElement($submit);
	}
	
}