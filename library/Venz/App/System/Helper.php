<?php
 
class Venz_App_System_Helper extends Zend_Db_Table_Abstract
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

	
    public function getCustomers($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name, Address, Phone, Email FROM Customers WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getCustomersDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Name, Address, Phone, Email FROM Customers WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 
	
  
	
	
    public function getVendors($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name, Address, Phone, Email FROM Vendors WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getVendorsDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Name, Address, Phone, Email FROM Vendors WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 
	
  
  
 
    public function getBranches($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, Name,Location, Address, Phone, Email FROM Branches WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getBranchesDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT ID, Name,Location, Address, Phone, Email FROM Branches WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	


	
    public function getLog($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ACLUsers.Name, SYSLog.username, SYSLog.role, SYSLog.logtime, SYSLog.zendmodule, SYSLog.zendcontroller, SYSLog.zendaction, SYSLog.postdata, SYSLog.getdata, SYSLog.ID, SYSLog.IP
			FROM SYSLog LEFT JOIN ACLUsers on (ACLUsers.Username=SYSLog.username) WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		

		$sql .= $sqlAll." order by ".$sql_orderby." ". $sql_limit;
		$sqlCount = "SELECT  COUNT(*) as Num FROM SYSLog LEFT JOIN ACLUsers on (ACLUsers.Username=SYSLog.username) WHERE 1=1 ";		
		$arrCount = $this->_db->fetchRow($sqlCount);
		
		
		return array($arrCount[0], $this->_db->fetchAll($sql));
    }	
	 
  
}




?>