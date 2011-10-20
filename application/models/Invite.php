<?php

/**
 *
* The user class for the whole application
*
*/
class Application_Model_Invite
{
	protected $_inviteId = null;
	protected $_fromUserId = null;
	protected $_toUserId = null;
	protected $_originUserId = null;
	protected $_reason = null;
	protected $_numberOfBeers = null;
	protected $_favorSize = null;
	protected $_date = null;
	
	/**
	 * Lazy-loaded user object for toUser
	 * @var Application_Model_User
	 */
	protected $_toUser = null;

	/**
	* Lazy loaded user objcect for fromUser
	* @var Application_Model_User
	*/
	protected $_fromUser = null;
	
	/**
	 * Lazy loaded user objcect for fromUser
	 * @var Application_Model_User
	 */
	protected $_originUser = null;
	
	
	/**
	 * User service - inject the user service to retrieve users
	 * Services shouldn't be injected in entitites, but since we're not implementing a full-blown ORM here...
	 * 
	 * @var Application_Service_User
	 */
	protected $_userService = null;
	
	/**
	 * A valid user service is required here
	 * It is used mainly for the get*User calls.
	 * 
	 * @param Application_Service_User $service
	 */
	public function setUserService(Application_Service_User $service) {
		$this->_userService = $service;
	}
	
	public function getInviteId() {
		return $this->_inviteId;
	}

	public function getFromUserId() {
		return $this->_fromUserId;
	}
	
	public function getToUserId() {
		return $this->_toUserId;
	}
	
	public function getOriginUserId() {
		return $this->_originUserId;
	}

	public function getReason() {
		return $this->_reason;
	}
	
	public function getNumberOfBeers() {
		return $this->_numberOfBeers;
	}
	
	public function getFavorSize() {
		return $this->_favorSize;
	}
	
	public function getDate() {
		return $this->_date;
	}

	public function getToUser() {
		if(is_null($this->_userService)) {
			throw new Zend_Exception ('Need the user service. Set one by using the setUserService');
		}
		
		if(isset($this->_toUser)) {
			return $this->_toUser;
		}
		$userId = $this->getToUserId();
		
		if(null == $userId) {
			throw new Zend_Exception('To user id is not set.');
		}
		
		//lazy load
		$user = $this->_userService->getUserById($this->getToUserId());
		$this->_toUser = $user;
		return $this->_toUser;
	}
	
	public function getFromUser() {
		if(is_null($this->_userService)) {
			throw new Zend_Exception ('Need the user service. Set one by using the setUserService');
		}
		
		if(isset($this->_fromUser)) {
			return $this->_fromUser;
		}
		$userId = $this->getFromUserId();
		
		if(null == $userId) {
			throw new Zend_Exception('From user id is not set.');
		}
		
		//lazy load
		$user = $this->_userService->getUserById($this->getFromUserId());
		$this->_fromUser = $user;
		return $this->_fromUser;
	}
	
	public function getOriginUser() {
		if(is_null($this->_userService)) {
			throw new Zend_Exception ('Need the user service. Set one by using the setUserService');
		}
		
		if(isset($this->_originUser)) {
			return $this->_originUser;
		}
		$userId = $this->getOriginUserId();
		
		if(null == $userId) {
			throw new Zend_Exception('Origin user id is not set.');
		}
		
		//lazy load
		$user = $this->_userService->getUserById($this->getOriginUserId());
		$this->_originUser = $user;
		return $this->_originUser;
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
		foreach (array('inviteId', 'fromUserId', 'toUserId', 'originUserId', 'reason',
				'numberOfBeers', 'favorSize', 'date'
		) as $property) {
			if (isset($data[$property])) {
				$this->{'_' . $property} = $data[$property];
			}
		}
	}
}