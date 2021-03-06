<?php
 
class Venz_App_Vacancy_Helper extends Zend_Db_Table_Abstract
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
	
	public function getApplicationStatus($AppID)
	{
		$sql = "SELECT VAApplicationStatusHistory.*, VAApplicationStatus.Name as StatusName FROM VAApplicationStatusHistory, VAApplicationStatus where ".
		"VAApplicationStatusHistory.VAApplicationStatusID=VAApplicationStatus.ID AND VAApplicationID=".$AppID." order by DateChange";
		return $this->_db->fetchAll($sql);
	}
	
	public function updateApplicationStatus($AppID, $status, $datedb)
	{
		$arrUpdate = array("Status"=>$status);
		$this->_db->Update("VAApplication", $arrUpdate, "ID=".$AppID);
		
		$arrInsert = array("VAApplicationID"=>$AppID, "VAApplicationStatusID"=>$status, "DateInformation"=>$datedb, 
			"DateChange"=> new Zend_Db_Expr('now()'));
		//print_r($arrInsert);exit();
		$this->_db->Insert("VAApplicationStatusHistory", $arrInsert);
	}
	
	public function getApplicationNumber($VAPostID)
	{
		$sql = "SELECT * FROM VAApplication WHERE VAApplication.VAPostID=".$VAPostID;	

		return count($this->_db->fetchAll($sql));		
	}
	
	
	public function getApplicationDetail($ACLUsersID, $VAPostID)
	{
		$sql = "SELECT * FROM VAApplication WHERE VAApplication.ACLUsersID=".$ACLUsersID." AND VAApplication.VAPostID=".$VAPostID;	

		return $this->_db->fetchRow($sql);		
	}
	
    public function getApplicationList($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $sqlString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "DateApplied" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";	
		$sqlAll = "SELECT VAPost.ID as VAPostID, ACLUsers.ID as ACLUsersID, VAApplication.ID as VAApplicationID, VAPost.Title,  VAApplication.DateApplied, ".
/*5*/		"VAApplicationStatus.Name as ApplicationStatus, VAApplication.DateStatusChange, SYSSalarygrade.Description as SalaryGrade, SYSCities.Name as Location, SYSClassification.Name as Classifications, SYSEducation.Name as EducationLevel, ".
/*11*/		" ACLUsers.Name UserName, ACLUsers.Email, VAApplicationStatus.ID ".
			" FROM ACLUsers, VAApplicationStatus, VAApplication LEFT JOIN VAApplicationRemarks on (VAApplicationRemarks.VAApplicationID=VAApplication.ID), VAPost LEFT JOIN SYSCities ON (VAPost.SYSCitiesID=SYSCities.ID) ".
				"LEFT JOIN SYSClassification ON (VAPost.SYSClassificationID=SYSClassification.ID) ".		
				"LEFT JOIN SYSSalarygrade ON (VAPost.SYSSalarygradeID=SYSSalarygrade.ID) ".		
				"LEFT JOIN SYSEducation ON (VAPost.SYSEducationID=SYSEducation.ID) WHERE 1=1 AND ".
				"VAApplication.VAPostID=VAPost.ID AND ACLUsers.ID=VAApplication.ACLUsersID AND VAApplicationStatus.ID=VAApplication.Status  ";
					
		if ($sqlString)
			$sqlAll .= $sqlString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";

		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));		
		
	}
	
	public function applyVacancy($ACLUserID, $VAPostID)
	{
		$sql = "SELECT * From VAApplication where ACLUsersID=".$ACLUserID." and VAPostID=".$VAPostID;
		if (!$this->_db->fetchRow($sql))
		{

			$arrDataInsert = array("ACLUsersID"=>$ACLUserID, "VAPostID"=>$VAPostID, "DateApplied"=>new Zend_Db_Expr("now()"));
			$this->_db->Insert("VAApplication", $arrDataInsert);
		}
		else
		{
			$appMessage = new Venz_App_Msg();	
			$appMessage->setMsg(0, "exist");
		}
	}
	
	public function getVacancyRequirement($VAPostID)
	{
		$sql = "SELECT VAPostExamRequirements.ID, SYSExamCat.Name as ExamCategory, SYSExamSubject.Name as ExamSubject, VAPostExamRequirements.ReqExamGrade FROM ".
		"VAPostExamRequirements, SYSExamSubject, SYSExamCat where ".
		"VAPostExamRequirements.ReqExamSubjectID=SYSExamSubject.ID AND SYSExamCat.ID=SYSExamSubject.SYSExamCatID AND VAPostID=".$VAPostID;
		return $this->_db->fetchAll($sql);
		
	}
	
	public function getVacancyListEx($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "SYSOfficeWaranQuota.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT SYSOfficeWaranQuota.ID, SYSOffice.Name, SYSDesignationLevel.Name as DesignationLevel, SYSOfficeWaranQuota.Quota, SYSOffice.ID as OfficeID, SYSDesignationLevel.ID as SYSDesignationLevelID ".
			" FROM SYSOfficeWaranQuota LEFT JOIN SYSOffice ON (SYSOffice.ID=SYSOfficeWaranQuota.SYSOfficeID) LEFT JOIN SYSDesignationLevel ON (SYSOfficeWaranQuota.SYSDesignationLevelID=SYSDesignationLevel.ID) WHERE 1=1 ";	
					
		if ($searchString)
			$sqlAll .= $searchString;
		$sqlAll .= " order by $sql_orderby ";
		$sql .= $sqlAll." $sql_limit";
		//print $sql; exit();
		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql), $sqlAll);
    }
	
    public function getVacancyDetailEx($ID = NULL, $searchString = null)
    {
		$sql = "SELECT SYSOfficeWaranQuota.ID, SYSOffice.Name, SYSDesignationLevel.Name as DesignationLevel, SYSOfficeWaranQuota.Quota, SYSOffice.ID as OfficeID, SYSDesignationLevel.ID as SYSDesignationLevelID ".
			" FROM SYSOfficeWaranQuota LEFT JOIN SYSOffice ON (SYSOffice.ID=SYSOfficeWaranQuota.SYSOfficeID) LEFT JOIN SYSDesignationLevel ON (SYSOfficeWaranQuota.SYSDesignationLevelID=SYSDesignationLevel.ID) WHERE 1=1 ";	
				
		
		if ($ID)
			$sql .= " and SYSOfficeWaranQuota.ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	
  		
    public function getVacancyList($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "PostedDate" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT VAPost.ID, VAPost.VacancyDate, SYSOffice.Name, VAPost.VAComments, SYSDesignationLevel.Name as DesignationLevel, ".
			"VAPost.VAType, VAPost.VANumber, VAPost.VacancyDate, VAPost.PostedDate, VAPost.PostedBy, VAPost.SYSOfficeID, VAPost.SYSDesignationLevelID ".
			" FROM VAPost LEFT JOIN SYSOffice ON (SYSOffice.ID=VAPost.SYSOfficeID) LEFT JOIN SYSDesignationLevel ON (VAPost.SYSDesignationLevelID=SYSDesignationLevel.ID) WHERE 1=1 ";	
					
		if ($searchString)
			$sqlAll .= $searchString;
		$sqlAll .= " order by $sql_orderby ";
		$sql .= $sqlAll." $sql_limit";
		//print $sql; exit();
		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql), $sqlAll);
    }

	
	
	
    public function getVacancyDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT VAPost.ID, VAPost.VacancyDate, SYSOffice.Name, VAPost.VAComments, SYSDesignationLevel.Name as DesignationLevel, ".
			"VAPost.VAType, VAPost.VANumber, VAPost.VacancyDate, VAPost.PostedDate, VAPost.PostedBy, VAPost.SYSOfficeID, VAPost.SYSDesignationLevelID ".
			" FROM VAPost LEFT JOIN SYSOffice ON (SYSOffice.ID=VAPost.SYSOfficeID) LEFT JOIN SYSDesignationLevel ON (VAPost.SYSDesignationLevelID=SYSDesignationLevel.ID) WHERE 1=1 ";	
		
		
		if ($ID)
			$sql .= " and VAPost.ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
			
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	
  
    	
  
}




?>