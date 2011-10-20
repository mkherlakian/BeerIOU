<?php
class SendController extends Zend_Controller_Action
{
	public function inviteAction() {
		$request = $this->getRequest();
		/* @var $request Zend_Controller_Request_Http */
		
		$mode = $request->getParam('mode', 'i_owe');
		if(!in_array($mode, array('i_owe', 'am_owed'))) {
			$mode = 'i_owe';
		}
		
		$inviteForm = new Application_Form_Invite(null, ($mode == 'i_owe') ? Application_Form_Invite::MODE_I_OWE : Application_Form_Invite::MODE_AM_OWED);
		$this->view->inviteForm = $inviteForm;
		
		if($request->isPost()) {
			if($inviteForm->isValid($request->getPost())) {
				$userService = new Application_Service_User();
				
				$invite = new Application_Model_Invite();
				$invite->setUserService($userService);
				
				$inviteArray = array(
							'reason' => $inviteForm->getValue('reason'), 
							'numberOfBeers' => $inviteForm->getValue('number_of_beers'), 
							'favorSize' => $inviteForm->getValue('favor'),
							'date' => date('Y-m-d h:i:s'));
				
				$email = $inviteForm->getValue('email');
				$targetUser = $userService->getUserByEmail($email);
				if($targetUser === false) {
					$targetUser = $userService->createAndStoreUserFromEmail($email);
				}
				
				if($mode == 'i_owe') {
					$inviteArray['toUserId'] = $targetUser->getUserId();
				} else {
					$inviteArray['fromUserId'] = $targetUser->getUserId();
				}
				
				$invite->setFromArray($inviteArray);
				
				$session = Zend_Registry::get('session');
				/* @var $session Zend_Session_Namespace */
				
				$inviteService = new Application_Service_Invite();
				$inviteService->queueInvite($invite, $session);
				
				$this->_helper->flashMessenger('Login/register to send your invites');
				$this->_helper->redirector('login', 'index');
			}
		}
	}
	
	
	public function thankYouAction() {
		$session = Zend_Registry::get('session');
		
		$inviteService = new Application_Service_Invite();
		$sentInvites = $inviteService->getSentInvites($session);
		
		if(!$sentInvites) {
			$this->_helper->redirector('index', 'my-account');
		}
		
		$this->view->sentInvites = $sentInvites;
		$inviteService->clearSentInvites($session);
	}
	
	public function testAction() {
		$adapter = new Zend_Auth_Adapter_DbTable( Zend_Db_Table::getDefaultAdapter(), 'user', 'email', 'password', 'MD5(?)' );
		$authenticateService = new Application_Service_Authentication(
				Zend_Auth::getInstance(),
				$adapter
		);
		
		Zend_Debug::dump($authenticateService->getUser());
		$userService = new Application_Service_User();
		
		$invite = new Application_Model_Invite();
		$invite->setUserService($userService);
		
		$invite->setFromArray(array('toUserId' => 1, 'numberOfBeers' => 2, 'reason' => 'Big favor, introduces me to...', 'favorSize'=>'Big favor', 'date'=>date('Y-m-d h:i:s')));

		$session = Zend_Registry::get('session');
		
		$inviteService = new Application_Service_Invite();
		$inviteService->queueInvite($invite, $session);
		
		$view = new Zend_View();
		$view->setScriptPath(APPLICATION_PATH.'/views/scripts/email/');
		
		$originUser = $userService->getUserById(1);
		
		Zend_Debug::dump($invite);
		
		Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_File());
		$mail = new Zend_Mail();
		$mail->setSubject('Message from the BeerIOU project');
		$mail->setFrom('BIOU project');

		$inviteService->setMailer($mail);
		
		$inviteService->processQueuedInvites($originUser, $session, $view, array('i_owe' => 'invite-i-owe.phtml', 'am_owed' => 'invite-am-owed.phtml'));
		
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
}