<?php
class Application_Service_Beer {
	
	protected static $_dbTable = null;
	
	/**
	 * @var Zend_Cache_Core
	 */
	protected $_cache = null;

	public function getAllBeers() {
		if(!is_null($this->_cache)) {
			if(($results = $this->_cache->load('allbeers')) != false) {
				return $results;
			}
		}
		
		$beers = self::getDbTable()->fetchAll(self::getDbTable()->select()->order('beer ASC'));
		
		$results = array();
		
		foreach($beers as $beerRow) {
			$results[] = $this->createBeerFromRow($beerRow);
		}
		
		if(!is_null($this->_cache)) {
			$this->_cache->save($results, 'allbeers', array(), 3600);
		}
		
		return $results;
	}
	
	public function getBeerById($beerId) {
		$beerRow = self::getDbTable()->fetchRow(self::getDbTable()->select()->where('id = ?', $beerId));
		
		$beer = false;
		if($beerRow) {
			$beer = $this->createBeerFromRow($beerRow);
		}
		return $beer; 
	}
	
	/**
	 * @param Zend_Cache_Core $cache
	 */
	public function setCache(Zend_Cache_Core $cache) {
		$this->_cache = $cache;
	}
	
	
	/**
	 * 
	 * @param Zend_Db_Table_Row $row
	 * @param Application_Model_Beer $beer
	 */
	public function createBeerFromRow(Zend_Db_Table_Row $row, Application_Model_Beer $beer = null) {
		if(is_null($beer)) {
			//factory??
			$beer = new Application_Model_Beer();
		}
		
		$rowData = $row->toArray();
//		'beerId', 'name', 'style', 'location',
//				'catalog', 'score', 'date'
		
		$beer->setFromArray(array(
				'beerId' => $rowData['id'],
				'name' => $rowData['beer'],
				'style' => $rowData['style'],
				'location' => $rowData['location'],
				'catalog' => $rowData['catalog'],
				'score' => $rowData['score'],
				'date' => $rowData['date'],
		));
		
		return $beer;
	}
	
	/**
	 * @return Application_Model_DbTable_Beer
	 */
	public static function getDbTable()
	{
		if (self::$_dbTable == null) {
			self::$_dbTable = new Application_Model_DbTable_Beer();
		}
	
		return self::$_dbTable;
	}
	
}