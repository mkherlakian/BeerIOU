<?php

class IndexController extends Zend_Controller_Action
{
	private $_flashMessenger;

	
	public function preDispatch() {

	}
	
    public function init()
    {
        /* Initialize action controller here */
    	$this->_flashMessenger = $this->getHelper('FlashMessenger');
    }

    public function indexAction()
    {
		$this->view->render('register/_register_banner.phtml');		
    	
		// action body
        $adapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter(), 'user', 'email', 'password', 'MD5(?)' );
    	
    	$auth = new Application_Service_Authentication(
    					Zend_Auth::getInstance(), 
    					$adapter
    				);
    	
    	$userService = new Application_Service_User();
    	$user = $userService->getUserByEmail('maurice.k@zend.com');
    }
    
    public function signupAction() {
    	$request = $this->getRequest();
    	/* @var $request Zend_Controller_Request_Http */
    	
    	$registerForm = new Application_Form_Register();
    	    	
    	if($request->isPost()) {
    		if($request->getPost('banner') == 1) {
    			/**
    			 * When getting values from the index page registration form
    			 * make sure to clean them up.
    			 */
    			$values = $registerForm->getValidValues($request->getPost());
    		} else {
	    		$valid = $registerForm->isValid($request->getPost());
	    		if($valid) {
	    			$userService = new Application_Service_User();
	    			$user = new Application_Model_User();
	    			$user->setFromArray(array(
	    				'email' => $registerForm->getValue('email'),
	    				'firstName' => $registerForm->getValue('firstName'),
	    				'lastName' => $registerForm->getValue('lastName'),
	    				'password' => $registerForm->getValue('password'),
	    				'registered' => 1
	    			));
	    			
	    			if($userService->registerUser($user, true)) {
	    				$adapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter(), 'user', 'email', 'password', 'MD5(?)' );
	    				$authenticateService = new Application_Service_Authentication(
	    						Zend_Auth::getInstance(),
	    						$adapter
	    				);
	    				
	    				$authenticateService->authenticate($registerForm->getValue('email'), $registerForm->getValue('password'));
	    				$this->_loginRedirect(array('action' => 'signup-success', 'controller' => 'index'));
	    			} else {
	    				$this->view->errorMessage = $userService->getLastErrorMessage();
	    			}
	    		}
    		}
    	}
    	$this->view->registerForm = $registerForm;
    }
    
    public function loginAction() {
    	//Attempt authentication
    	//DI container to inject dependencies
    	$adapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter(), 'user', 'email', 'password', 'MD5(?)' );
    	$authenticateService = new Application_Service_Authentication(
    		Zend_Auth::getInstance(),
    		$adapter
    	);
    	
    	if($authenticateService->hasUser()) {
    		$this->_loginRedirect();
    	}
    	
    	$request = $this->getRequest();
    	/* @var $request Zend_Controller_Request_Http */
    	
    	$loginForm = new Application_Form_Login();
    	if($request->isPost()) {
    		$valid = $loginForm->isValid($request->getPost());
    		if($valid) {
    			$authResult = $authenticateService->authenticate($loginForm->getValue('email'), $loginForm->getValue('password'));
    			if(!$authResult) {
    				//$errorMessage = current($authenticateService->getLastAuthenticateErrors());
    				$errorMessage = 'Invalid username/password combination';
    				$this->_flashMessenger->addMessage($errorMessage);
    				$this->_helper->redirector('login');
       			} else {
       				$this->_loginRedirect(array('action' => 'index', 'controller' => 'my-account'));
       			}
    		}
    	}
    	
    	$this->view->loginForm = $loginForm;
    }
    
    public function signupSuccessAction() {
    	
    }
    
    private function _loginRedirect($destination) {
		$adapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter(), 'user', 'email', 'password', 'MD5(?)' );
		$authenticateService = new Application_Service_Authentication(
				Zend_Auth::getInstance(),
				$adapter
		);
		    	
		$session = Zend_Registry::get('session');
    	$inviteService = new Application_Service_Invite();
    	
    	$view = new Zend_View();
    	$view->setScriptPath(APPLICATION_PATH.'/views/scripts/email/');
    	
    	$originUser = $authenticateService->getUser();
    	
    	Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_File());
    	$mail = new Zend_Mail();
    	$mail->setSubject('Message from the BeerIOU project');
    	$mail->setFrom('BIOU project');
    	
    	$inviteService->setMailer($mail);
    	
    	$inviteService->processQueuedInvites($originUser, $session, $view, array('i_owe' => 'invite-i-owe.phtml', 'am_owed' => 'invite-am-owed.phtml'));

    	if($inviteService->getSentInvites($session)) {
    		$this->_helper->redirector('thank-you', 'send');
    	} else {
    		$this->_helper->redirector($destination['action'], $destination['controller']);
    	}
    }
}