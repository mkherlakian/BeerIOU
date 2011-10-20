<?php
/**
 *
* The user class for the whole application
*
*/
class Application_Model_User
{
	protected $_userId = null;
	protected $_firstName = null;
	protected $_lastName = null;
	protected $_password = null;
	protected $_email = null;
	protected $_registered = null;
	protected $_favoriteBeerId = null;
	
	/**
	 * @var Application_Service_Invite
	 */
	protected $_inviteService = null;
	
	/**
	 * @var Application_Service_Beer
	 */
	protected $_beerService = null;
	
	public function __construct() {
	}
	
	/**
	 * @param Application_Service_Invite $inviteService
	 */
	public function setInviteService(Application_Service_Invite $inviteService) {
		$this->_inviteService = $inviteService;
	}
	
	/**
	 * @param Application_Service_Beer $beerService
	 */
	public function setBeerService(Application_Service_Beer $beerService) {
		$this->_beerService = $beerService;
	}
	
	public function getUserId()
	{
		return $this->_userId;
	}

	public function getName()
	{
		return $this->_firstName . " " . $this->_lastName;
	}
	
	public function getFirstName()
	{
		return $this->_firstName;
	}
	
	public function getLastName()
	{
		return $this->_lastName;
	}
	
	public function getPassword()
	{
		return $this->_password;
	}

	public function getEmail()
	{
		return $this->_email;
	}
	
	public function getOwedInvites(Application_Model_User $fromUser = null) {
		if(is_null($this->_inviteService)) {
			throw new Zend_Exception('Requires the invite service. Set the invite service to the user class using setInviteService');
		}
		
		$owingInvites = $this->_inviteService->getUserReceivedInvites($this, $fromUser);
		return $owingInvites;
	}
	
	public function getOwingInvites(Application_Model_User $toUser = null) {
		if(is_null($this->_inviteService)) {
			throw new Zend_Exception('Requires the invite service. Set the invite service to the user class using setInviteService');
		}
		
		$owingInvites = $this->_inviteService->getUserSentInvites($this, $toUser);
		return $owingInvites;
	}
	
	/**
	 * @return Application_Model_Beer
	 */
	public function getFavoriteBeer() {
		if(is_null($this->_beerService)) {
			throw new Zend_Exception('Beer service is uninitialized. Set using setBeerService');
		}
		
		if(is_null($this->_favoriteBeerId)) {
			return new Application_Model_Beer();
		}
		
		return $this->_beerService->getBeerById($this->_favoriteBeerId);
	}
	
	
	/**
	 * @return integer
	 */
	public function getFavoriteBeerId() {
		return $this->_favoriteBeerId;
	}
	
	/**
	 * @return bool
	 */
	public function getRegistered() {
		return $this->_registered;
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
		foreach (array('userId', 'firstName', 'lastName', 'email',
				'password', 'registered', 'favoriteBeerId'
		) as $property) {
			if (isset($data[$property])) {
				$this->{'_' . $property} = $data[$property];
			}
		}
	}
}