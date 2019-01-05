<?php
 
class Venz_App_System_Acl extends Zend_Db_Table_Abstract
{
	protected $_db  = NULL;

	public function __construct($DbMode = Zend_Db::FETCH_ASSOC)
	{
		parent::__construct();
		$this->_db = $this->getAdapter();
		$this->_db->setFetchMode($DbMode);
	}
	
	public function setFetchMode($DbMode = Zend_Db::FETCH_ASSOC)
	{
		$this->_db->setFetchMode($DbMode);
	}
	
	public function updateUserLastLogin($userID)
	{
		$this->_db->update("ACLUsers", array("LastLogin"=>new Zend_Db_Expr('now()')), "ID=".$userID);
	}

    public function getUsers($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name, Username, ACLRole, Email, Active, LastLogin, DateCreated,  DateModified FROM ACLUsers  WHERE 1=1";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getUsersDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ACLUsers.* FROM ACLUsers  WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 	
	
	

    public function getResources($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name, Description, Category, ParentName FROM ACLResources WHERE 1=1";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getResourcesDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Name, Description, Category, ParentName FROM ACLResources WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 


    public function getPriviledges($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name, Description FROM ACLPriviledges WHERE 1=1";	
	
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getPriviledgesDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Name, Description FROM ACLPriviledges WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	
	
	
   public function getRoles($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name, Description, ParentName FROM ACLRole WHERE 1=1";	
	
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getRolesDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Name, Description, ParentName FROM ACLRole  WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	
	

	
	public function isRolesaccess($role, $resources, $priviledges)
	{
		$sql = "SELECT Count(*) Num FROM ACLMap where Role='".$role."' AND  Resources='".$resources."' AND  Priviledges='".$priviledges."'";
		$dataRow = $this->_db->fetchRow($sql);
		return $dataRow['Num'];
	}   
	
	
   public function getRolesaccess($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Role, Resources, Priviledges, Allow FROM ACLMap WHERE 1=1";	
	
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getRolesaccessDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Role, Resources, Priviledges, Allow FROM ACLMap WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	
		
	
	
}




?>