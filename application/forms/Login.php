<?php
class Application_Form_Login
	extends Zend_Form
{
	public function init() {
		$this->addElement('text', 'email', array(
				'label' => 'Email adress',
				'size'  => 30,
				'maxlength' => 30,
				'required' => true,
				'filters' => array('StringTrim'),
				'validators' => array('EmailAddress')
			)
		);
		
		$this->addElement('password', 'password', array(
				'label' => 'Password',
				'size'  => 30,
				'maxlength' => 30,
				'required' => true,
// 				'validators' => array(
// 				array('StringLength' , true, array(4,30))
// 				)
			)
		);
		
		$this->addDisplayGroup(array('email', 'password'), 'login', array('legend' => 'Login'));
		
		$this->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'dl', 'class' => 'fieldgroup register1')), 'Form'));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit');
		$this->addElement($submit);
	}
}