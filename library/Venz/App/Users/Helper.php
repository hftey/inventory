<?php
 
class Venz_App_Users_Helper extends Zend_Db_Table_Abstract
{
	protected $_db  = NULL;

	public function __construct($DbMode = Zend_Db::FETCH_ASSOC)
	{
		parent::__construct();
		$this->_db = $this->getAdapter();
		$this->_db->setFetchMode($DbMode);
	}
	
    public function getUserList()
    {
	
		$sqlAll = "SELECT ACLUsers.ID, ACLUsers.Name, ACLUsers.Email, ACLUsers.LastLogin, ACLUsers.DateCreated FROM ACLUsers";	
		return $this->_db->fetchAll($sqlAll);
    }	
	
    public function getUserDetail($ACLUserID)
    {
	
		$sqlAll = "SELECT ACLUsers.ID, ACLUsers.Name, ACLUsers.Email, ACLUsers.LastLogin, ACLUsers.DateCreated FROM ACLUsers WHERE ID=".$ACLUserID;	
		return $this->_db->fetchAll($sqlAll);
    }		
	
	
}	
	



?>