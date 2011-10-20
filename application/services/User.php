<?php
/**
 * 
 * @author Maurice Kherlakian
 *
 */
class Application_Service_User {
	protected static $_dbTable = null;
	protected static $_rowRepository = array();

	protected $_lastErrorMessage;
	/**
	 * @return Application_Model_DbTable_User
	 */
	public static function getDbTable()
	{
		if (self::$_dbTable == null) {
			self::$_dbTable = new Application_Model_DbTable_User();
		}

		return self::$_dbTable;
	}

	/**
	 * @return null
	 */
	public static function resetDbTable()
	{
		self::$_dbTable = null;
	}

	/**
	 * @param $userId
	 * @return Zend_Db_Table_Row
	 */
	public static function getUserRowById($userId)
	{
		if (isset(self::$_rowRepository[$userId])) {
			return self::$_rowRepository[$userId];
		}

		$row = self::getDbTable()->find($userId)->current();
		if ($row instanceof Zend_Db_Table_Row) {
			self::$_rowRepository[$userId] = $row;
			return $row;
		} else {
			return false;
		}
	}

	/**
	 * @param $userId
	 * @return Zend_Db_Table_Row || false if user not found in table
	 */
	public static function getUserRowByEmail($email)
	{
		foreach (self::$_rowRepository as $key => $value) {
			if ($value->email == $email) {
				return self::$_rowRepository[$key];
			}
		}

		$row = self::getDbTable()
			->fetchRow(self::getDbTable()
			->select()
			->where('email = ?', $email));
		if ($row instanceof Zend_Db_Table_Row) {
			self::$_rowRepository[$row->id] = $row;
			return $row;
		} else {
			return false;
		}
	}
	
	
	/**
	 * Get user rows for which the userId owes beers
	 * 
	 * @param integer $userId
	 */
	public static function getUserRowsByIOwe($userId) {
		$rows = self::getDbTable()->fetchAll(
			self::getDbTable()
			->select()
			->setIntegrityCheck(false)
			->distinct()
			->from(array('u' => 'user'))
			->join('invite', 'invite.to_user_id = u.id', array())
			->where('invite.from_user_id = ?', $userId));
		if(count($rows)) {
			return $rows;
		}
		return array();
	}

	/**
	 * Get user rows for which the userId is owed beers
	 *
	 * @param integer $userId
	 */
	public static function getUserRowsByAmOwed($userId) {
		$rows = self::getDbTable()->fetchAll(
				self::getDbTable()
				->select()
				->setIntegrityCheck(false)
				->distinct()
				->from(array('u' => 'user'))
				->join('invite', 'invite.from_user_id = u.id', array())
				->where('invite.to_user_id = ?', $userId));
		if(count($rows)) {
			return $rows;
		}
		return array();
	}	
	
	/**
	 * @param int $userId
	 * @return Application_Model_User
	 */
	public function getUserById($userId)
	{
		$userRow = $this->getUserRowById($userId);

		if (!$userRow) {
			return false;
		}

		$user = new Application_Model_User();
		$this->createUserFromRow($userRow, $user);
		return $user;
	}

	/**
	 * @param string $userName
	 * @return Application_Model_User
	 */
	public function getUserByEmail($email)
	{
		$userRow = $this->getUserRowByEmail($email);

		if (!$userRow) {
			return false;
		}

		$user = new Application_Model_User();
		$this->createUserFromRow($userRow, $user);
		return $user;
	}

	
	/**
	 * 
	 * @param Application_Model_User $user
	 * @return boolean|array:Application_Model_User
	 */
	public function getUsersByIOwe(Application_Model_User $user) {
		$id = $user->getUserId();
		if(!$id) {
			return false;
		}
		
		$result = array();
		foreach($this->getUserRowsByIOwe($id) as $row) {
			$result[] = $this->createUserFromRow($row);
		}
		return $result;
	}
	
	/**
	 * 
	 * @param Application_Model_User $user
	 * @return boolean|array:Application_Model_User
	 */
	public function getUsersByAmOwed(Application_Model_User $user) {
		$id = $user->getUserId();
		if(!$id) {
			return false;
		}
		
		$result = array();
		foreach($this->getUserRowsByAmOwed($id) as $row) {
			$result[] = $this->createUserFromRow($row);
		}
		return $result;		
	}
	
	
	/**
	 * Creates a new user and persists him in the databse.
	 * @param string $email
	 * @return bool
	 * 
	 */
	public function createAndStoreUserFromEmail($email) {
		$user = new Application_Model_User();
		$user->setFromArray(array('email' => $email, 'first_name' => '', 'last_name' => '', 'password' => '', 'registered' => 0));
		
		$this->storeUser($user);
		
		return $user;
	}
	
	/**
	 * @param int $id
	 * @return Application_Model_CurrentUser
	 */
	public function getCurrentUserById($userId)
	{
		$userRow = $this->getUserRowById($userId);

		$user = new Application_Model_CurrentUser();
		$this->createUserFromRow($userRow, $user);
		
		return $user;
	}


	/**
	 * Internally use this to populate a user object with row information
	 *
	 * @param Zend_Db_Table_Row $userRow
	 * @param Application_Model_User $user
	 * @return Application_Model_User
	 */
	public function createUserFromRow(Zend_Db_Table_Row $userRow, $user = null)
	{
		if ($user == null) {
			$user = new Application_Model_User();
		} elseif (!$user instanceof Application_Model_User) {
			throw new Exception('This method expected $user to be of type Application_Model_User');
		}

		$rowData = $userRow->toArray();
		$user->setFromArray(array(
			'userId' => $rowData['id'],
			'email' => $rowData['email'],
			'firstName' => $rowData['first_name'],
			'lastName' => $rowData['last_name'],
			'registered' => $rowData['registered'],
			'favoriteBeerId' => $rowData['favorite_beer_id']
		));
		return $user;
	}
	
	public function registerUser(Application_Model_User $user, $encryptPassword = false) {
		$this->_lastErrorMessage = null;
		
		$userTable = self::getDbTable();
		
		//find user that matches by email address
		$userByEmailRow = self::getUserRowByEmail($user->getEmail());
		if($userByEmailRow) {
			$userByEmail = $this->createUserFromRow($userByEmailRow);
			if($userByEmail->getRegistered() == 1) {
				$this->_lastErrorMessage = 'Email address already registered';
				return false;
			}
			
			$user->setFromArray(array('userId' => $userByEmail->getUserId()));
		}
		
		$this->storeUser($user, $encryptPassword);
		return true;
	}
	
	public function getLastErrorMessage() {
		return $this->_lastErrorMessage;
	}
	
  /**
	 * @param Application_Model_User $user
	 * @param boolean $encryptPassword default true
	 * @return null
	 */
	public function storeUser(Application_Model_User $user, $encryptPassword = false)
	{
		$userTable = self::getDbTable();
		$userRow = null;
		$userData = array();

		if ($user->getUserId() != null) {
			// for a new user being registered, userId had better be null
			$userRow = $userTable->find($user->getUserId());
		}
		
		if ($userRow == null) {
			// this is a new user
			$userRow = $userTable->createRow();
		} else {
			$userRow = $userRow[0];
		}
		
		$password = $encryptPassword
		? md5($user->getPassword()) // SQLite has no built-in MD5()
		: $user->getPassword() ;

		$userData = $userData + array(
			'user_id'   => $user->getUserId(),
			'first_name'  => $user->getFirstName(),
			'last_name'  => $user->getLastName(),
//			'password'  => $password,
			'email' => $user->getEmail(),
			'registered' => $user->getRegistered(),
			'favorite_beer_id' => $user->getFavoriteBeerId()
		);
		
		if($password) {
			$userData['password'] = $password;
		}

		$userRow->setFromArray($userData);
		$userRow->save();
		
		$user->setFromArray(array('userId' => $userRow['id']));
	}
}