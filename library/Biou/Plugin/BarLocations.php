<?php
class Biou_Plugin_Barlocations 
	extends Zend_Controller_Plugin_Abstract {

	public function routeShutdown($request) {
		$service = new Application_Service_Barlocations();
		$locations = $service->getLocations();
		
		$view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
		/* @var $layout Zend_Layout */
		
		$view->barLocations = $locations;
	}
}