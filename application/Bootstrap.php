<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
  protected function _initBeerNavigation() {
  	$this->getApplication()->bootstrap('navigation');
  	$view = $this->getApplication()->getBootstrap()->getResource('view');

  	/* @var $navigationHelper Zend_View_Helper_Navigation */
  	$navigationHelper = $view->getHelper('navigation');
  	
  	/* @var $navigation Zend_Navigation */
  	$navigation = $navigationHelper->getContainer();
  	
  	$navigation->removePage($navigation->findOneBy('label', 'Home'));

  	if(Zend_Auth::getInstance()->hasIdentity()) {
  		$navigation->addPages(array(
  				new Zend_Navigation_Page_Mvc(array(
  						'label' => 'my account',
  						'action' => 'index',
  						'controller' => 'my-account'
  				)),
  				new Zend_Navigation_Page_Mvc(array(
  						'label' => 'logout',
  						'action' => 'logout',
  						'controller' => 'my-account'
  				))
  			)
  		);
  	} else {
	  	$navigation->addPages(array(
	  		new Zend_Navigation_Page_Mvc(array(
	  				'label' => 'login',
	  				'action' => 'login',
	  				'controller' => 'index'
	  		))
	  	)
	  );
  	}
  	
  	$navigation->addPages(array(
  			new Zend_Navigation_Page_Mvc(array(
  					'label' => 'Breweries',
  					'action' => 'get-sync',
  					'controller' => 'breweries'
  			)),
  			new Zend_Navigation_Page_Mvc(array(
  					'label' => 'About',
  					'action' => 'about',
  					'controller' => 'index'
  			)),
  	)
  	);
  }
  
  protected function _initSession() {
  	Zend_Session::start();
  	
  	$ns = new Zend_Session_Namespace('BeerIOU');
  	Zend_Registry::set('session', $ns);
  }

  protected function _initRegisterNamespace() {
  	$loader = Zend_Loader_Autoloader::getInstance();
  	$loader->registerNamespace('Biou_');
  }

  protected function _initFlashMessenger()
  {
  	/** @var $flashMessenger Zend_Controller_Action_Helper_FlashMessenger */
	$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
	if ($flashMessenger->hasMessages()) {
	  $view = $this->getResource('view');
	  $view->messages = $flashMessenger->getMessages();
	 }
  }
  
  protected function _initAuthenticatedPlugin() {
  	$protected = array(
  		'default' => array('my-account')
  	);
  	$plugin = new Biou_Plugin_Authenticate($protected, array('module'=>'default', 'controller' => 'index', 'action' =>'login'), Zend_Auth::getInstance());
  	
  	$front = Zend_Controller_Front::getInstance();
  	$front->registerPlugin($plugin); 	
  }
  
  protected function _initBarlocationsPlugin() {
  	$bl = $this->getOption('barlocations');
  	
  	if($bl['enabled']) {
	  	$plugin = new Biou_Plugin_Barlocations();
	  	
	  	$front = Zend_Controller_Front::getInstance();
	  	$front->registerPlugin($plugin);
  	}
  }
  
  protected function _initViewPartial() {
  	$view = $this->getApplication()->getBootstrap()->getResource('view');
  	/* @var $view Zend_View */
  	
  	$view->getHelper('partialLoop')->setObjectKey('model');
  }
}