<?php
class Application_Model_Beer {
	protected $_beerId;
	protected $_name;
	protected $_style;
	protected $_location;
	protected $_catalog;
	protected $_score;
	protected $_date;
	
	/**
	 * @return int
	 */
	public function getId() {
		return $this->_beerId;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * @return string
	 */
	public function getStyle() {
		return $this->_style;
	}
	
	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->_location;
	}
	
	/**
	 * @return string
	 */
	public function getCatalog() {
		return $this->_catalog;
	}
	
	/**
	 * @return int
	 */
	public function getScore() {
		return $this->_score;
	}
	
	/**
	 * @return string
	 */
	public function getDate() {
		return $this->_date;
	}
	
	public function __get($propertyName)
	{
		$getter = 'get' . $propertyName;
		if (!method_exists($this, $getter)) {
			throw new RuntimeException('Property by name ' . $propertyName . ' not found as part of this object.');
		}
		return $this->{$getter}();
	}
	
	public function setFromArray(array $data)
	{
		foreach (array('beerId', 'name', 'style', 'location',
				'catalog', 'score', 'date'
		) as $property) {
			if (isset($data[$property])) {
				$this->{'_' . $property} = $data[$property];
			}
		}
	}
}