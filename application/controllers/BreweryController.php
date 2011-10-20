<?php
class BreweryController extends Zend_Controller_Action {
	public function getSyncAction() {
		$bootstrap = $this->getInvokeArg('bootstrap');
		$brewery = $bootstrap->getOption('brewerydb');
		
		$service = new Application_Service_Brewery(
				$brewery['url'], $brewery['apikey']
		);
		$breweries = $service->getAllSync();
		
		$this->view->breweries = $breweries;
	}
	
	public function getAsyncAction() {
		$bootstrap = $this->getInvokeArg('bootstrap');
		$urlJobBaseUrl = $bootstrap->getOption('jobbaseurl');
		
		$key = uniqid('breweries_');
		
		$jq = new ZendJobQueue();
		$jq->createHttpJob($urlJobBaseUrl.'/brewery/get-breweries-job', array('key' => $key));
		
		$this->view->key = $key;
	}
	
	
	public function pollBreweriesAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNeverRender(true);
		
		$request = $this->getRequest();
		/* @var $request Zend_Controller_Request_Http */
		
		$key = $request->get('key');
		if(!$key) {
			echo json_encode(array('status'=>'failed'));
			return;
		}
		
		$path = '/tmp/'.$key;
		if(!file_exists($path)) {
			echo json_encode(array('status'=>'pending'));
			return;
		}
		
		$data = unserialize(file_get_contents($path));
		
		$this->view->breweries = $data;
		$rendered = $this->view->render('brewery/get-sync.phtml');
		
		echo json_encode(array('status' => 'success', 'data' => $rendered));
	}
	
	public function getBreweriesJobAction() {
		zend_monitor_event_reporting(~ZEND_MONITOR_ETBM_REQ_SLOW_EXEC);
		
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout()->disableLayout();
		
		$jobParams = ZendJobQueue::getCurrentJobParams();
		
		$bootstrap = $this->getInvokeArg('bootstrap');
		$brewery = $bootstrap->getOption('brewerydb');
		
		$service = new Application_Service_Brewery(
				$brewery['url'], $brewery['apikey']
		);
		
		$path = '/tmp/'.$jobParams['key'];
		$breweries = $service->getAllAsync($path);
		
		ZendJobQueue::setCurrentJobStatus(ZendJobQueue::OK);
	}
	
}