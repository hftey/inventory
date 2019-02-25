<?php
 
class Venz_Zend_Acl extends Zend_Acl
{
	private $_role = '';
	private $_arrResource = array();
	public function __construct($role)
	{

		try {
		$this->_role = $role;
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$arrDataResources = $db->fetchAll("SELECT Name FROM ACLResources");
		foreach ($arrDataResources as $arrResources)
		{
			$this->add(new Zend_Acl_Resource($arrResources['Name']));			
			$this->_arrResource[$arrResources['Name']] = true;
		}

		
		$arrRoles = $this->_getRoles($role);
		$parentRole = '';
		foreach ($arrRoles as $roles)
		{
			if (strlen($parentRole) == 0)
				$this->addRole($roles);
			else
				$this->addRole($roles, $parentRole);
			
			$parentRole=$roles;			
			$this->_setAccess($roles);
		}
		}catch (Exception $e)
		{
			$appMessage = new Venz_App_Msg();
			$appMessage->setMsg(0, $e->getMessage());
		}
	}
    
	private function _getRoles($role)
	{
		return explode("|", $this->_getRolesAll($role)); 
	}
	
	
	private function _getRolesAll($role)
	{
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$arrData = $db->fetchRow("SELECT ParentName FROM ACLRole WHERE Name='".$role."'");
		if (!empty($arrData['ParentName']))
			return $this->_getRolesAll($arrData['ParentName'])."|".$role;
		else
			return $role;
			
	}
	
	private function _setAccess($role)
	{
		$db = Zend_Db_Table::getDefaultAdapter(); 
		
		$arrData = $db->fetchAll("SELECT * FROM ACLMap WHERE Role='".$role."'");
		foreach ($arrData as $arrRecord)
		{
			if ($arrRecord['Allow'])
				$this->allow($role, $arrRecord['Resources'], $arrRecord['Priviledges']);
			else{
				$this->deny($role, $arrRecord['Resources'], $arrRecord['Priviledges']);
			}	
		}

	}
	
	public function isAllowed($role = NULL, $resource = NULL, $privillege = NULL)
	{

		if ($this->_arrResource[$resource])
			return parent::isAllowed($role, $resource, $privillege);
		else
			return false;
		
	}	
}




?>