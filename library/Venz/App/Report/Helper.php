<?php
 
class Venz_App_Report_Helper extends Zend_Db_Table_Abstract
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
	
	public function getStockLevelDetailEx($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
	{
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$systemSetting = new Zend_Session_Namespace('systemSetting');	
		foreach ($systemSetting->arrStockStatus as $status => $status_name)
		{
			$sqlJoin .= " LEFT JOIN (SELECT COUNT(*) as TotalNum_".$status.", ItemID FROM (SELECT ItemSeriesStatus.Status, ItemSeriesStatus.ItemSeriesID, ItemSeriesStatus.ItemID, ItemSeriesStatus.ItemName, EntryDateTime FROM ".
				" (SELECT ItemSeriesStatus.*, Item.ID as ItemID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName FROM ItemSeriesStatus, ItemSeries, Item WHERE ItemSeriesStatus.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID ". 
				" order by StatusDate Desc, EntryDateTime Desc) as ItemSeriesStatus, ItemSeries WHERE ".
				" ItemSeries.ID=ItemSeriesStatus.ItemSeriesID GROUP BY ItemSeriesStatus.ItemSeriesID) as StatusList WHERE Status = '".$status."'";
			if ($searchString)
				$sqlJoin .= $searchString;
			$sqlJoin .= " group by ItemID order by TotalNum_".$status.") as Count_".$status." ON (Count_".$status.".ItemID=Item.ID)";
		}
	
		$sqlAll = "SELECT Item.ID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName";
		foreach ($systemSetting->arrStockStatus as $status => $status_name)
		{
			$sqlAll .= ", TotalNum_".$status;
		}
		$sqlAll .= " FROM Item ".$sqlJoin . " order by $sql_orderby";
		
	//	print $sqlAll;
		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sqlAll), $sqlAll);	

	}
	
	public function getStockLevelDetail($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
	{
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		
		$sqlAll = "SELECT COUNT(*) as TotalNum, ItemName, Status, EntryDateTime, ItemID FROM (SELECT ItemSeriesStatus.Status, ItemSeriesStatus.ItemSeriesID, ItemSeriesStatus.ItemID, ItemSeriesStatus.ItemName, EntryDateTime FROM ".
				" (SELECT ItemSeriesStatus.*, Item.ID as ItemID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName  FROM ItemSeriesStatus, ItemSeries, Item WHERE ItemSeriesStatus.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID ". 
				" order by StatusDate Desc, EntryDateTime Desc) as ItemSeriesStatus, ItemSeries WHERE ".
				" ItemSeries.ID=ItemSeriesStatus.ItemSeriesID GROUP BY ItemSeriesStatus.ItemSeriesID) as StatusList WHERE 1=1 ".
				" ";
	
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." group by ItemID order by $sql_orderby $sql_limit";
		
	//	print $sql;
		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql), $sql);	

	}
	
		
	
}




?>