<?php

/**
 * Service class to wrap Zend_Auth
 * 
 * @copyright Copyright (c) 2011
 * @author mkherlakian
 * @version $Id$
 */
class Application_Service_Authentication {
	
	/**
	 * @var Zend_Auth
	 */
	protected $_auth;
	
	/**
	 * @var array
	 */
	protected $_lastAuthenticateErrors = null;
	
	/**
	 * @var Zend_Auth_Adapter_DbTable
	 */
	protected $_adapter;	
	
	/**
	 * Expects an instance of Zend_Auth
	 * @param Zend_Auth $auth
	 */
	public function __construct(Zend_Auth $auth, Zend_Auth_Adapter_DbTable $adapter) 
	{
		$this->_auth = $auth;
		$this->_adapter = $adapter;
	}
	
	public function getLastAuthenticateErrors() 
	{
		return $this->_lastAuthenticateErrors;
	}

	public function hasUser()
	{
		return $this->_auth->hasIdentity();
	}

	public function getUser()
	{
		return $this->_auth->getIdentity();
	}

	/**
	 * The authenticate method that authenticates a user
	 * 
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function authenticate($username, $password)
	{
		$this->_lastAuthenticateErrors = null;
		
		$this->_adapter->setIdentity($username)->setCredential($password);
		$authResult = $this->_auth->authenticate($this->_adapter);
		
		if(!$authResult->isValid()) {
			$this->_lastAuthenticateErrors = $authResult->getMessages();
			return false;
		}
		
		$userId = $this->_adapter->getResultRowObject('id')->id;
		
		$userService = new Application_Service_User();
		$currentUser = $userService->getCurrentUserById($userId);
		
		//store details in session
		$this->_auth->getStorage()->write($currentUser);
		return true;
	}

	public function logout() 
	{
		$this->_auth->clearIdentity();
	}
}