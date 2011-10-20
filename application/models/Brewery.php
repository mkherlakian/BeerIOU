<?php
class Application_Model_Brewery {

	/**
	 * @var string
	 */
	protected $_name;
	
	/**
	 * @var string
	 */
	protected $_description;
	
	/**
	 * @var string
	 */
	protected $_established;
	
	/**
	 * @var string
	 */
	protected $_website;
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * @return string
	 */
	public function getEstablished() {
		return $this->_established;
	}

	/**
	 * @return string
	 */
	public function getWebsite() {
		return $this->_website;
	}
	
	public function toArray() {
		return array(
			'name' => $this->getName(), 
			'description' => $this->getDescription(), 
			'established' => $this->getEstablished(), 
			'website' => $this->getWebsite()
		);
	}
	
	/**
	 * @param array $data
	 */
	public function setFromArray(array $data)
	{
		foreach (array('breweryDb', 'name', 'description', 'website', 'established') as $property) {
			if (isset($data[$property])) {
				$this->{'_' . $property} = $data[$property];
			}
		}
	}
}