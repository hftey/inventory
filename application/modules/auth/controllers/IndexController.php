<?php

class Auth_IndexController extends Venz_Zend_Controller_Action
{

	private $_userInfo;
    public function init()
    {
        parent::init(NULL, NULL, false);
		if(Zend_Auth::getInstance()->hasIdentity())   
		{   
			$this->_userInfo = Zend_Auth::getInstance()->getStorage()->read(); 
		}
		$this->view->userInfo = $this->_userInfo;
		
    }

    public function indexAction()
    {
		if ($this->userInfo) {
			$this->_redirect('/default');
		}
    }
	
	
	public function authloginsysAction()   
	{
	
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$front = Zend_Controller_Front::getInstance();
		$front->throwExceptions(true);
		try {
			
			$Request = $this->getRequest();
			$Username = $Request->getParam('userID');
			$Password = $Request->getParam('userPassword');
			$appMessage = new Venz_App_Msg();
			$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
			
			$rowAccount = $db->fetchRow("SELECT * FROM ACLUsers WHERE Username='".$Username."' AND Active=1");
			if (!$rowAccount)
			{
				$appMessage->setMsg(0, "The username and password entered do not match or<BR>your account has been deactivated.");
				$this->_redirect('/auth');
				exit();
			}
			
		   if($Username && $Password) {

				$refURL = $Request->getParam('referer_url');	
				// Connect to the database and receive the adapter
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$auth = new Venz_App_Auth_Authentication($db);	

		
				if (true === $message = $auth->loginsys($Username, $Password)) {
					$appMessage->setNotice(1, "Login Successful.");
					$userInfo = Zend_Auth::getInstance()->getStorage()->read(); 
					$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
					$sessionUsers->Acl = new Venz_Zend_Acl($userInfo->ACLRole);
				} else {
					$appMessage->setMsg(0, "The username and password entered do not match or<BR>your account has been deactivated.");
					$this->_redirect('/auth');
				}

			}
			else{
				$appMessage->setMsg(0, "Username and password must be entered.");
				$this->_redirect('/auth');

			}
			
			$userInfo = Zend_Auth::getInstance()->getStorage()->read();	
			if ($userInfo->ACLRole == "accounts") 
				$this->_redirect('/admin/system/invoices');
			else if ($userInfo->ACLRole == "transporter" || $userInfo->ACLRole == "loading_warehouse" ) 
				$this->_redirect('/#tabs2');
			else
				$this->_redirect('/#tabs1');
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}

		
		exit();

	}	
	
	
	public function authloginAction()   
	{
	
	
		$front = Zend_Controller_Front::getInstance();
		$front->throwExceptions(true);
		try {
			
			$Request = $this->getRequest();
			$Username = $Request->getParam('userID');
			$Password = $Request->getParam('userPassword');
			$appMessage = new Venz_App_Msg();
		   if($Username && $Password) {

				$refURL = $Request->getParam('referer_url');	
				// Connect to the database and receive the adapter
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$auth = new Venz_App_Auth_Authentication($db);	

				if (true === $message = $auth->login($Username, $Password)) {
					$appMessage->setNotice(1, "Login Successful.");
					$userInfo = Zend_Auth::getInstance()->getStorage()->read(); 
					$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
					$sessionUsers->Acl = new Venz_Zend_Acl($userInfo->ACLRole);
					
				} else {
					$appMessage->setMsg(0, "Username and password do not match.");
				}
				
			}
			else{
				$appMessage->setMsg(0, "Username and password must be entered.");

			}
			$this->_redirect('/auth');
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}

		
		exit();

	}	


	public function authlogoutAction()   
	{
		Zend_Auth::getInstance()->clearIdentity();
		$systemSetting = new Zend_Session_Namespace('systemSetting');		
		$this->_redirect($systemSetting->arrEnvironments['main_url']);	
		
	}	

}

