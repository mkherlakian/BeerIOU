<?php
class Application_Service_Brewery {
	/**
	 * @var string
	 */
	protected $_url = null;
	
	/**
	 * @var string
	 */
	protected $_apiKey = null;
	
	/**
	 * @var Zend_Rest_Client
	 */
	protected $_restClientClass = null;

	/**
	 * @var 
	 */
	protected $_tmpFolder = '/tmp';
	
	/**
	 * 
	 * @param string $url
	 * @param string $apiKey
	 */
	public function __construct($url, $apiKey) {
		$this->_url = $url;
		$this->_apiKey = $apiKey;
		$this->_restClientClass = 'Zend_Rest_Client';
	}
	
	/**
	 * @param string $client
	 */
	public function setRestClientClass($client) {
		$this->_restClientClass = $client;
	}
	
	public function getAllSync() {
		$rest = new $this->_restClientClass($this->_url.'breweries/');
		$rest->apikey($this->_apiKey);
		$results = $rest->get();

		$out = array();
		foreach($results->brewery as $res) {
			$brewery = new Application_Model_Brewery();
			$brewery->setFromArray(array(
				'breweryId' => (string)$res->id, 
				'name' => (string)$res->name,
				'description' => (string)$res->description,
				'established' => (string)$res->established,
				'website' => (string)$res->website
			));
			$out[] = $brewery;
		}
		return $out;
	}
	
	public function getAllAsync($path) {
		$out = $this->getAllSync();
		file_put_contents($path, serialize($out));
	}
}