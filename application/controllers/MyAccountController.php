<?php
class MyAccountController extends Zend_Controller_Action {

	/**
	 * @var Application_Service_Authentication
	 */
	protected $_authenticateService = null;
	
	public function init() {
		$adapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter(), 'user', 'email', 'password', 'MD5(?)' );
		$this->_authenticateService = new Application_Service_Authentication(
				Zend_Auth::getInstance(),
				$adapter
		);
	}
	
	public function indexAction() {
		$request = $this->getRequest();
		/* @var $request Zend_Controller_Request_Http */
		
		$user = $this->_authenticateService->getUser();
		/* @var $user Application_Model_User */

		$bootstrap = $this->getInvokeArg('bootstrap');
		$cache = $bootstrap->getResource('cachemanager');
		
		$beerService = new Application_Service_Beer();
// 		$beerService->setCache($cache->getCache('zs'));
		
		
		$userService = new Application_Service_User();
		$user->setBeerService(new Application_Service_Beer());

//START COMMENT TO DEMO WITHOUT GETTING ALL BEERS
		$beers = $beerService->getAllBeers();
		
		$favoriteBeerForm = new Application_Form_FavoriteBeer(array(), $beers);		
		$favoriteBeerForm->setDefaults(array('beer_id' => $user->getFavoriteBeer()->getId()));
		if($request->isPost()) {
			$valid = $favoriteBeerForm->isValid($request->getPost());
			if($valid) {
				$user->setFromArray(array('favoriteBeerId' => $favoriteBeerForm->getValue('beer_id')));
				$userService->storeUser($user);
				
				$this->_helper->flashMessenger('Favorite beer updated successfully');
				$this->_helper->redirector('index', 'my-account');
			}
		}
		
		$this->view->favoriteBeerForm = $favoriteBeerForm;
//END COMMENT TO DEMO WITHOUT GETTING ALL BEERS

		$this->view->authenticatedUser = $user;
		$this->view->usersIOwe = $userService->getUsersByIOwe($user);
		$this->view->usersAmOwed = $userService->getUsersByAmOwed($user);		
	}
	
	public function logoutAction() {
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		//DI container to inject dependencies		
		$this->_authenticateService->logout();

    	//$errorMessage = current($authenticateService->getLastAuthenticateErrors());
    	$errorMessage = 'Logout successful';
    	$flashMessenger->addMessage($errorMessage);
    	$this->_helper->redirector('index', 'index');
	}
}