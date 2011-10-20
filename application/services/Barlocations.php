<?php
class Application_Service_Barlocations {
	
	public function getLocations () {
		$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
		$config = $bootstrap->getOptions();
		
		if(!array_key_exists('barlocations', $config)) {
			throw new Zend_Exception('Define barlocations in application.ini.');
		}
		
		if(!array_key_exists('url', $config['barlocations'])) {
			throw new Zend_Exception('Define barlocations.url in application.ini.');
		}
		
		$url = $config['barlocations']['url'];
		$client = new Zend_Http_Client($url);
		$response = $client->request()->getBody();
		
		$locations = json_decode($response, true);
		
		$results = array();
		
		foreach($locations as $location) {
			$bl = new Application_Model_Barlocation();
			$bl->setFromArray($location);
			$results[] = $bl;
		}
		return $results;
	}
}