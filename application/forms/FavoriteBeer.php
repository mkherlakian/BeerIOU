<?php
class Application_Form_FavoriteBeer extends Zend_Form {
	protected $_beers;
	
	/**
	 * @param array $beers an array of Application_Model_beer
	 */
	public function __construct($options, $beers) {
		$this->_beers = $beers;
		parent::__construct($options);
	}
	
	public function init() {
		$this->addElement('select', 'beer_id', array(
				'label' => 'My favorite beer',
				'required' => true,
				'filters' => array('int'),
				'validators' => array('int')
		));
		
		$element = $this->getElement('beer_id');

		/* @var $element Zend_Form_Element_Select */
		$element->addMultiOption('', '-- Choose your favorite beer --');
		
		foreach($this->_beers as $beer) {
			/* @var $beer Application_Model_Beer */
			$element->addMultiOption($beer->getId(), utf8_encode($beer->getName()));
		}
		
		$this->addDisplayGroup(array('beer_id'), 'Favorite beer', array('legend' => 'My favorite!'));
		$this->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'dl', 'class' => 'fieldgroup register1')), 'Form'));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit');
		$this->addElement($submit);
	}
}