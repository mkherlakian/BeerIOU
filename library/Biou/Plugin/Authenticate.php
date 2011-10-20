<?php
class Biou_Plugin_Authenticate extends Zend_Controller_Plugin_Abstract {
	protected $_parts;
	
	/**
	 * @param unknown_type $protected
	 * @param unknown_type $login
	 * @param Zend_Auth $auth
	 */
	public function __construct($protected, $login, Zend_Auth $auth) {
		$this->_parts = $protected;
		$this->_login = $login;
		$this->_auth = $auth;
	}
	
	public function routeShutdown($request) {
		/* @var $request Zend_Controller_Request_Http */
		$needToAuthenticate = false;
		
		//go down the protected parts array and check if module/controller
		foreach($this->_parts as $module=>$controllers) {
			if(is_array($controllers) && count($controllers) == 0) {
				if($module == $request->getModuleName()) {
					$needToAuthenticate = true;
					break;
				} elseif (is_string($controllers)) {
					if($module == $request->getModuleName() && $controllers == $request->getControllerName()) {
						$needToAuthenticate = true;
						break;
					}
				}
			} elseif(is_array($controllers)) {
				foreach($controllers as $controller) {
					if($module == $request->getModuleName() && $controller == $request->getControllerName()) {
						$needToAuthenticate = true;
						break;
					}
				}
			}
			
			if($needToAuthenticate == true) {
				if(!$this->_auth->hasIdentity()) {
					$request->setModuleName($this->_login['module']);
					$request->setControllerName($this->_login['controller']);
					$request->setActionName($this->_login['action']);
				}
			}
		}
	}
}