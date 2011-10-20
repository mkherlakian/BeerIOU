<?php
class Application_Form_Register
	extends Zend_Form
{
	public function init() {
		$this->addElementPrefixPath('Biou_Validate', 'Biou/Validate', 'validate');
		
		$this->addElement('text', 'email', array(
				'label' => 'Email (this will also be your login)',
				'size'  => 30,
				'maxlength' => 30,
				'required' => true,
				'filters' => array('StringTrim'),
				'validators' => array('EmailAddress')
			)
		);

		$this->addElement('text', 'firstName', array(
				'label' => 'First Name',
				'size'  => 30,
				'maxlength' => 30,
				'required' => true,
				'filters' => array('StringTrim'),
				'validators' => array(
							array('StringLength' , true, array(4,30))
				)
			)
		);
		
		$this->addElement('text', 'lastName', array(
				'label' => 'Last Name',
				'size'  => 30,
				'maxlength' => 30,
				'required' => true,
				'filters' => array('StringTrim'),
				'validators' => array(
							array('StringLength' , true, array(4,30))
				)
			)
		);

		$this->addDisplayGroup(array('email', 'firstName', 'lastName'), 'your_profile', array('label' => 'Your profile'));
 		$this->getDisplayGroup('your_profile')->setLegend('Your profile');
		
 		$this->addElement('password', 'password', array(
 				'label' => 'Password',
 				'size'  => 30,
 				'maxlength' => 30,
 				'required' => true,
				'validators' => array(
							array('StringLength' , true, array(4,30)),
							array('FieldCompare', true, array(array('field' => 'confirm_password', 'field_label' => 'confirm password')))							
				)
 			)
 		);
 		
 		$this->addElement('password', 'confirm_password', array(
 				'label' => 'Confirm password',
 				'size'  => 30,
 				'maxlength' => 30,
 				'required' => true,
				'validators' => array(
							array('StringLength' , true, array(4,30))
				)
 			)
 		);
 		
 		$this->addDisplayGroup(array('password', 'confirm_password'), 'passwd', array('label' => 'Your password'));
 		$this->getDisplayGroup('passwd')->setLegend('Your password');
		
		$this->setDecorators(array('FormElements', array('HtmlTag', array('tag' => 'dl', 'class' => 'fieldgroup register1')), 'Form'));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit');
		$this->addElement($submit);
	}
}