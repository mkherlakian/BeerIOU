<?php
class TestController extends Zend_Controller_Action {
	public function testGetAllBeersAction() {
		$bootstrap = $this->getInvokeArg('bootstrap');
		$cache = $bootstrap->getResource('cachemanager');
		
		$beerService = new Application_Service_Beer();
 		//$beerService->setCache($cache->getCache('zs'));
		
		$beers = $beerService->getAllBeers();
		
		$userService = new Application_Service_User();
		$user = $userService->getUserById(1);
		$user->setBeerService(new Application_Service_Beer());
		
		$favoriteBeerForm = new Application_Form_FavoriteBeer(array(), $beers);
		$favoriteBeerForm->setDefaults(array('beer_id'=>$user->getFavoriteBeer()->getId()));
		
		$this->view->favoriteBeerForm = $favoriteBeerForm;
	}
	
	public function testGetBreweriesAction() {
		$bootstrap = $this->getInvokeArg('bootstrap');
		$brewery = $bootstrap->getOption('brewerydb');
		
		$service = new Application_Service_Brewery(
					$brewery['url'], $brewery['apikey']
				);
		$breweries = $service->getAllSync();
		Zend_Debug::dump($breweries);
		exit;
	}
}