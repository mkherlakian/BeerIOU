<?php
class Application_Model_Barlocation {
	protected $_name;
	protected $_type;
	protected $_address;
	protected $_city;
	protected $_state;
	protected $_zip;
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * @return string
	 */
	public function getType() {
		return $this->_type;
	}
	
	/**
	 * @return string
	 */
	public function getAddress() {
		return $this->_address;
	}
	
	/**
	 * @return string
	 */
	public function getCity() {
		return $this->_city;
	}
	
	/**
	 * @return string
	 */
	public function getState() {
		return $this->_state;
	}
	
	/**
	 * @return string
	 */
	public function getZip() {
		return $this->_zip;
	}
	
	public function setFromArray($data) {
		$this->_name = $data['name'];
		$this->_type = $data['type'];
		$this->_address = $data['address'];
		$this->_city = $data['city'];
		$this->_state = $data['state'];
		$this->_zip = $data['zip'];
 	}
}