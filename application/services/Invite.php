<?php
class Application_Service_Invite
{
	protected static $_dbTable = null;
	
	/**
	 * @var Zend_Mail
	 */
	protected $_mail = null;
	
	/**
	 * @return Application_Model_DbTable_User
	 */
	public static function getDbTable()
	{
		if (self::$_dbTable == null) {
			self::$_dbTable = new Application_Model_DbTable_Invite();
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
	
	public function setMailer(Zend_Mail $mail) {
		$this->_mail = $mail;
	}
	
	/**
	 * We need to queue invites in a session variable 
	 * in case registration is required.
	 * 
	 * @param Application_Model_Invite $invite
	 * @param Zend_Session_Namespace $queue
	 */
	public function queueInvite(Application_Model_Invite $invite, Zend_Session_Namespace $session) {
		$session->queue[] = $invite;
	}
	
	/**
	 * Process the pending invites, after user registration, login, or other.
	 * 
	 * @param Application_Model_User $originUser This is the currently logged in user
	 * @param Zend_Session_Namespace $session The session object we are using. The call to processQueue 
	 * 										  should be preceded by a call to queueInvite, and the $session should be the same
	 * @param Zend_View $email A Zend_View objet to use to construct the email message
	 * @param array $emailTemplateNames An array of 2 elements array('i_owe' => 'i_owe.phtml', 'am_owed' => am_owed.phtml');
	 * 									Both ojects need to be present in the array.
	 * @return int number of messages processed, or false on failure
	 */
	public function processQueuedInvites(Application_Model_User $originUser, Zend_Session_Namespace $session, Zend_View $email, $emailTemplateNames) {
		//Make sure that mailer was set.
		if(null == $this->_mail) {
			throw new Zend_Exception ('Mailer is not set. Set mailer on the invite service by calling Application_Service_Invite::setMailer(Zend_Mail)');
		}
		
		if(!is_array($emailTemplateNames) || !array_key_exists('am_owed', $emailTemplateNames) || !array_key_exists('i_owe', $emailTemplateNames)) {
			throw new Zend_Exception('$emailTemplateNames must be an array, and must have two elements with keys \'i_owe\' and \'am_owed\'');
		}
		
		foreach($session->queue as $invite) {
			$inviteCount = 0;
			
			/* @var $invite Application_Model_Invite */
			if($invite->getOriginUserId() == null) {
				$invite->setFromArray(array('originUserId' => $originUser->getUserId()));
			}
			
			/*
			 * One of the two has to be set, and the other has to be the same as the originUserId
			 */
			if($invite->getFromUserId() == null) {
				$invite->setFromArray(array('fromUserId' => $originUser->getUserId()));
			} elseif($invite->getToUserId() == null) {
				$invite->setFromArray(array('toUserId' => $originUser->getUserId()));
			}

			$email->senderName = $originUser->getName();
			$email->description = $invite->getReason();
			$email->date = $invite->getDate();
			$email->numberOfBeers = $invite->getNumberOfBeers();
						
			/*
			 * @TODO: Should be abstracted further.
			 */
			if($invite->getFromUserId() == $invite->getOriginUserId()) {
				$emailTemplate = $emailTemplateNames['i_owe'];
			} elseif($invite->getToUserId() == $invite->getOriginUserId()) {
				//Am owed
				$emailTemplate = $emailTemplateNames['am_owed'];
			}
			
			//Render the message content from template
			try{ 
				$messageContent = $email->render($emailTemplate);
			} catch(Zend_View_Exception $e) {
				throw new Zend_Exception('Unable to render view. Is the view script path set on Zend_View? The message returned by Zend View was: '.$e->getMessage());	
			}
			
			//Send the email
			$toUser = $invite->getToUser();
			$this->_mail->addTo($toUser->getEmail(), $toUser->getName());
			$this->_mail->setBodyText($messageContent);
			$this->_mail->send();
			
			$this->storeInvite($invite);
			
			$session->sentInvites[] = $invite;
			
			//Increment invite counter.
			$inviteCount++;
		}
			
		//reset queue
		$session->queue = array();
		
		//return
		return $inviteCount;
	}
	
	public function getSentInvites(Zend_Session_Namespace $session) {
		return $session->sentInvites;
	}
	
	public function clearSentInvites(Zend_Session_Namespace $session) {
		unset($session->sentInvites);
	}
	
	public function getUserSentInvites(Application_Model_User $user, Application_Model_User $toUser = null) {
		$inviteTable = self::getDbTable();
		$select = self::getDbTable()
				->select()
				->from('invite')
				->where('from_user_id = ?', $user->getUserId());
		if(!is_null($toUser)) {
			$select->where('to_user_id = ?', $toUser->getUserId());
		}
		
		$rows = $inviteTable->fetchAll($select);
		
		$result = array();
		foreach($rows as $row) {
			$result[] = $this->createInviteFromRow($row);
		}
		return $result;
	}
	
	public function getUserReceivedInvites(Application_Model_User $user, Application_Model_User $fromUser = null) {
		$inviteTable = self::getDbTable();
		
		$select = self::getDbTable()
				->select()
				->from('invite')
				->where('to_user_id = ?', $user->getUserId());
		if(!is_null($fromUser)) {
			$select->where('from_user_id = ?', $fromUser->getUserId());
		}
		
		$rows = $inviteTable->fetchAll($select);
		
		$result = array();
		foreach($rows as $row) {
			$result[] = $this->createInviteFromRow($row);
		}
		return $result;
	}
	
	public function storeInvite(Application_Model_Invite $invite) {
		$inviteTable = self::getDbTable();
		
		$inviteRow = null;
		$inviteData = array();
		
		if ($invite->getInviteId() != null) {
		// for a new invite
			$inviteRow = $inviteTable->find($invite->getInviteId());
		}
		
		if ($inviteRow == null) {
		// this is a new user
			$inviteRow = $inviteTable->createRow();
		} else {
			$inviteRow = $inviteRow[0];
		}
	
		$inviteData = $inviteData + array(
			'id'   => $invite->getInviteId(),
			'from_user_id'  => $invite->getFromUserId(),
			'to_user_id'  => $invite->getToUserId(),
			'origin_user_id' => $invite->getOriginUserId(),
			'number_of_beers'  => $invite->getNumberOfBeers(),
			'reason' => $invite->getReason(),
			'favor_size' => $invite->getFavorSize(),
			'date' => $invite->getDate(),
		);
	
		$inviteRow->setFromArray($inviteData);
		$inviteRow->save();
	}
	
	public function createInviteFromRow(Zend_Db_Table_Row $row, Application_Model_Invite $invite = null) {
		if(null == $invite) {
			$invite = new Application_Model_Invite();
		}
		//'userId', 'fromUserId', 'toUserId', 'reason',
		//		'numberOfBeers', 'favorSize'
		$rowData = $row->toArray();

		$invite->setFromArray(array(
			'inviteId' => $rowData['id'],
			'fromUserId' => $rowData['from_user_id'],
			'toUserId' => $rowData['to_user_id'],
			'originUserId' => $rowData['origin_user_id'],
			'reason' => $rowData['reason'],
			'numberOfBeers' => $rowData['number_of_beers'],
			'favorSize' => $rowData['favor_size'],
			'date' => $rowData['date']
		));

		return $invite;
	}
}