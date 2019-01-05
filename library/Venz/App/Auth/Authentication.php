<?php
 
class Venz_App_Auth_Authentication extends Zend_Db_Table_Abstract
{
	protected $_name = 'users';    
    protected $_primary = array('login');
    protected $_auth = NULL;
	
	
	public function loginsys($login, $pass)
    {
	
        $sessionUsers = new Zend_Session_Namespace('sessionUsers');	
		$auth = Zend_Auth::getInstance();
        $authAdapter = new Zend_Auth_Adapter_DbTable($this->getAdapter(), 'ACLUsers', 'Username', 'Password', "? AND Active=1");
        $authAdapter->setIdentity($login)
                    ->setCredential($pass);
//		$authAdapter->getDbSelect()->where('EntityID = '.$sessionUsers->arrEntity['ID']);
        
        // Checks and saves the result of authentication
        $result = $auth->authenticate($authAdapter);
//       exit();
		if ($result->isValid()) {
        // Successfully
            // It's possible to save the session in additional fields
			$userInfo = $authAdapter->getResultRowObject();
			$auth->getStorage()->write($userInfo);

            $session = new Zend_Session_Namespace('Zend_Auth');
            $session->setExpirationSeconds(24*3600);
			$this->_auth = $result;			
			$sysAcl = new Venz_App_System_Acl();
			$sysAcl->updateUserLastLogin($userInfo->ID);
			
            return true;
        }

        // Abortive
        return $error_msg = $result->getMessages();
    }
	
    public function login($login, $pass)
    {
        $auth = Zend_Auth::getInstance();
        $authAdapter = new Zend_Auth_Adapter_DbTable($this->getAdapter(), 'ACLUsers', 'Username', 'Password', "MD5(?) AND Active=1");
 
        $authAdapter->setIdentity($login)
                    ->setCredential($pass);

        // Checks and saves the result of authentication
        $result = $auth->authenticate($authAdapter);
         
        if ($result->isValid()) {
        // Successfully

            // It's possible to save the session in additional fields
			$userInfo = $authAdapter->getResultRowObject();
			$auth->getStorage()->write($userInfo);

            $session = new Zend_Session_Namespace('Zend_Auth');
            $session->setExpirationSeconds(24*3600);
			$this->_auth = $result;			
			$sysAcl = new Venz_App_System_Acl();
			$sysAcl->updateUserLastLogin($userInfo->ID);
			
            return true;
        }

        // Abortive
        return $error_msg = $result->getMessages();
    }
	
	public function getAuth()
	{
		return $this->_auth;
	}

    
}




?>