<?php

class Admin_ReportController extends Venz_Zend_Controller_Action
{

    public function init()
    {
        $actionName = $this->getRequest()->getActionName();
		switch ($actionName){
		case "index" : parent::init("menu_report");break;
		default: parent::init(NULL);
		}		
		
    }
	
	public function checkstatusAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysHelper = new Venz_App_System_Helper();
		$reportHelper = new Venz_App_Report_Helper();
		$libDb = new Venz_App_Db_Table();
		
		$sql = "SELECT CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemName, ItemSeries.SeriesNumber, ItemSeries.ID, ItemSeries.Status, 
		ItemSeriesStatus.Status as SeriesStatus, ItemSeriesStatus.StatusDate FROM ItemSeries, 
		(SELECT * FROM (SELECT * FROM ItemSeriesStatus order by StatusDate DESC, EntryDateTime Desc) AS AA GROUP BY ItemSeriesID) as ItemSeriesStatus, Item
		WHERE Item.ID=ItemSeries.ItemID AND ItemSeriesStatus.ItemSeriesID = ItemSeries.ID and NOT(ItemSeries.Status <=> ItemSeriesStatus.Status) ORDER BY StatusDate";
		
		$db->setFetchMode(Zend_Db::FETCH_NUM);
		$arrItemSeriesNullAll = $db->fetchAll($sql);
		
		$recordsPerPage = 250 ;
		////////////////////////////////////////////////////////////////////////////////////////
		
		
		$reportHelper->setFetchMode(Zend_Db::FETCH_NUM);

		$sessionStock = new Zend_Session_Namespace('sessionStock');
		$sessionStock->numCounter = 0;
		function format_counter($colnum, $rowdata)
		{
			$sessionStock = new Zend_Session_Namespace('sessionStock');
			$sessionStock->numCounter++;
			return $sessionStock->numCounter;
		}

		function format_serial($colnum, $rowdata)
		{
			return "<a target='_new' href='/inventory/brand/itemseriesdetail/id/".$rowdata[2]."'>".$rowdata[1]."</a>";
		}
		function format_entrydate($colnum, $rowdata)
		{
			$displayFormat = new Venz_App_Display_Format();
			return $displayFormat->format_datetime_simple($rowdata[5]);
		}
		
		
		$arrHeader = array ('', $this->translate->_('Name'), $this->translate->_('Serial Number'), $this->translate->_('Series ID'), $this->translate->_('Product Status'), $this->translate->_('Latest Status Record'), $this->translate->_('Entry Date'));
		$displayTable = new Venz_App_Display_Table(
			array (
				 'data' => $arrItemSeriesNullAll,
				 'hiddenparamtop'=> $strSearch,
				 'headings' => $arrHeader,
				 'format' 		=> array('{format_counter}','%0%','{format_serial}', '%2%', '%3%','%4%','{format_entrydate}'),	
				 'alllen' 		=> sizeof($arrItemSeriesNullAll),
				 'title'		=> $this->translate->_('Status Discrepancy Report'),					 
				 'aligndata' 	=> 'CLLCCCC',
				 'pagelen' 		=> $recordsPerPage,
				 'numcols' 		=> sizeof($arrHeader),
				 'tablewidth' => "1100px",
				 'sortby' => $sortby,
				 'ascdesc' => $ascdesc,
				 'hiddenparam' => $strHiddenSearch,
			)
		);
		$this->view->content_report = $displayTable->render();
	
	}



	public function initdata2Action()
    {
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysHelper = new Venz_App_System_Helper();
		$reportHelper = new Venz_App_Report_Helper();
		$libDb = new Venz_App_Db_Table();
		
		$sql = "SELECT ItemSeries.ID, ItemSeries.Status, ItemSeriesStatus.ID as ItemSeriesStatusID, PurchaseOrders.ReceivedDate, ItemSeriesStatus.StatusDate, ItemSeriesStatus.EntryDateTime FROM ItemSeries LEFT JOIN 
		ItemSeriesStatus ON (ItemSeriesStatus.ItemSeriesID=ItemSeries.ID) 
		LEFT JOIN POItems ON (ItemSeries.POItemsID=POItems.ID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID)
		WHERE ItemSeriesStatus.StatusDate IS NULL";
		$arrItemSeriesNullAll = $db->fetchAll($sql);
		
		print "<PRE>";
		print_r($arrItemSeriesNullAll);
		print "</PRE>";
		
		foreach ($arrItemSeriesNullAll as $arrItemSeriesNull)
		{
			if ($arrItemSeriesNull['EntryDateTime'])
				$arrUpdate = array("StatusDate"=>$arrItemSeriesNull['EntryDateTime']);
			else
				$arrUpdate = array("StatusDate"=>'2014-01-01');
			
			$db->update("ItemSeriesStatus", $arrUpdate, "ID=".$arrItemSeriesNull['ItemSeriesStatusID']);	
			
		}
		exit();
	}
	

	public function initdataAction()
    {
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysHelper = new Venz_App_System_Helper();
		$reportHelper = new Venz_App_Report_Helper();
		$libDb = new Venz_App_Db_Table();
		
		$sql = "SELECT ItemSeries.ID, ItemSeries.Status, ItemSeriesStatus.ID as ItemSeriesStatusID, PurchaseOrders.ReceivedDate FROM ItemSeries LEFT JOIN ItemSeriesStatus ON (ItemSeriesStatus.ItemSeriesID=ItemSeries.ID) 
		LEFT JOIN POItems ON (ItemSeries.POItemsID=POItems.ID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID)
		WHERE ItemSeriesStatus.ID IS NULL";
		$arrItemSeriesNullAll = $db->fetchAll($sql);
		
		print "<PRE>";
		print_r($arrItemSeriesNullAll);
		print "</PRE>";
		
		foreach ($arrItemSeriesNullAll as $arrItemSeriesNull)
		{
			if ($arrItemSeriesNull['Status'] == 'in' && $arrItemSeriesNull['ReceivedDate'])
			{
				$arrInsert = array("ItemSeriesID"=>$arrItemSeriesNull['ID'] ,"StatusDate"=>$arrItemSeriesNull['ReceivedDate'],"Status"=>$arrItemSeriesNull['Status'],"UserIDEntry"=>3, "UserIDResp"=>3, "Notes"=>"-" );
				$db->insert("ItemSeriesStatus", $arrInsert);	
			}else
			{
				$arrInsert = array("ItemSeriesID"=>$arrItemSeriesNull['ID'] ,"StatusDate"=>new Zend_Db_Expr('NULL'),"Status"=>$arrItemSeriesNull['Status'],"UserIDEntry"=>3, "UserIDResp"=>3, "Notes"=>"-" );
				$db->insert("ItemSeriesStatus", $arrInsert);	
			
			}
			
		}
		exit();
	}

   public function stockcountAction()
   {
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$reportHelper = new Venz_App_Report_Helper();
			$libDb = new Venz_App_Db_Table();
			$displayFormat = new Venz_App_Display_Format();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'TotalNum_in';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 250 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$generate_report = $Request->getParam('generate_report');	
			if ($generate_report)
			{
				//$Status = $Request->getParam('Status');	
				$EntryDateFrom = $Request->getParam('EntryDateFrom');				
				$EntryDateTo = $Request->getParam('EntryDateTo');				

				//$sqlSearch .= $Status ? " and Status = '".$Status."'" : "";
				$sqlEntryDateFrom = $EntryDateFrom ? $displayFormat->format_date_simple_to_db($EntryDateFrom) : "2014-08-01";
				$sqlSearch .= $EntryDateTo ? " and ((EntryDateTime > '".$sqlEntryDateFrom."' and EntryDateTime < '".$displayFormat->format_date_simple_to_db($EntryDateTo)."') or EntryDateTime IS NULL)" : "and (EntryDateTime > '".$sqlEntryDateFrom."' or EntryDateTime IS NULL)";
				
				//print $sqlSearch; exit();
				//$this->view->Status = $Status ? $Status : "";				
				$this->view->EntryDateFrom = $EntryDateFrom ? $EntryDateFrom : "";
				$this->view->EntryDateTo = $EntryDateTo ? $EntryDateTo : "";

				$strHiddenSearch = "<input type=hidden name='generate_report' value='true'>";
				
				//$strHiddenSearch .= "<input type=hidden name='Status' value='".$Status."'>";
				$strHiddenSearch .= "<input type=hidden name='EntryDateTo' value='".$EntryDateTo."'>";
				$strHiddenSearch .= "<input type=hidden name='EntryDateFrom' value='".$EntryDateFrom."'>";

			}
			$this->view->optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $this->view->Status); 			
			
			
			$reportHelper->setFetchMode(Zend_Db::FETCH_NUM);

			//$arrStock = $reportHelper->getStockLevelDetail($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$arrStock = $reportHelper->getStockLevelDetailEx($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);					

			$dataStock = $arrStock[1];
			$exportSql = $arrStock[2];
			
			$exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));	
			$export = $exportReport->display_icon();
				

			$sessionStock = new Zend_Session_Namespace('sessionStock');
			$sessionStock->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionStock = new Zend_Session_Namespace('sessionStock');
				$sessionStock->numCounter++;
				return $sessionStock->numCounter;
			}

			
			$sessionStock->arrStock = array();
			function format_count($colnum, $rowdata)
			{
				$sessionStock = new Zend_Session_Namespace('sessionStock');
				$sessionStock->arrStock[$colnum-3] += $rowdata[$colnum-1];
				return "<a target='_blank' href='/inventory/brand/flow/search_series/1/Status/".$sessionStock->arrStatus[$colnum-3]."/ItemID/".$rowdata[0]."'>".$rowdata[$colnum-1]."</a>";
			}	

					
			
			
			$arrHeader = array ('', $this->translate->_('Name'));
			$arrFormat = array('{format_counter}','%1%');
			$sort_column =array('','Name');
			$aligndata = "CL";
			$systemSetting = new Zend_Session_Namespace('systemSetting');$counter=2;	
			$sessionStock->arrStatus = array();

			foreach ($systemSetting->arrStockStatus as $status => $status_name)
			{
				array_push($sessionStock->arrStatus, $status);
				array_push($arrHeader, $status_name);
				array_push($arrFormat, "{format_count}");
				array_push($sort_column, "TotalNum_".$status);
				$counter++;
				$aligndata .= "C";
			}
			
			
			
			$displayTable = new Venz_App_Display_Table(
				array (
					 'data' => $dataStock,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> $arrFormat,					 
					 'sort_column' 	=> $sort_column,
					 'alllen' 		=> $arrStock[0],
					 'title'		=> $this->translate->_('Stock Status Count'),					 
					 'aligndata' 	=> $aligndata,
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
					 'export_excel' => $export,
					 'tablewidth' => "1500px",
					 'sortby' => $sortby,
					 'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_report = $displayTable->render();
			$this->view->content_total = $sessionStock->total;
			
			//print_r($sessionStock->arrStock);
			$this->view->stockTotal = ""; $exportHidden = "<TR><TD></TD><TD></TD>";
			foreach ($sessionStock->arrStock as $stockTotal)
			{
				$this->view->stockTotal .= "<TD class='report_header' style='text-align: center'>".$stockTotal."</TD>";
				$exportHidden .= "<TD class='report_header' style='text-align: center'>".$stockTotal."</TD>";
			}
			$exportHidden .= "<TR>";
			
			
			$sessionItemCounter->numCounter = 0;
			$sessionStock->total = 0;
			
			$export_excel_x = $Request->getParam('export_excel_x');						
			if ($export_excel_x)
			{

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$exportsql = $Request->getParam('exportsql');	
				$exportReport = new Venz_App_Report_Excel(array('exportsql'=> base64_decode($exportsql), 'db'=>$db, 'headings'=>$arrHeader, 'format'=>$arrFormat, 'hiddenparam'=>$exportHidden, 'aligndata'=>$aligndata));	
				$exportReport->render();
				
			}
						
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}	
   }
	
    public function indexAction()
    {
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$reportHelper = new Venz_App_Report_Helper();
			$libDb = new Venz_App_Db_Table();
			$displayFormat = new Venz_App_Display_Format();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'TotalNum';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 250 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$generate_report = $Request->getParam('generate_report');	
			if ($generate_report)
			{
				$Status = $Request->getParam('Status');	
				$EntryDateFrom = $Request->getParam('EntryDateFrom');				
				$EntryDateTo = $Request->getParam('EntryDateTo');				

				$sqlSearch .= $Status ? " and Status = '".$Status."'" : "";
				$sqlEntryDateFrom = $EntryDateFrom ? $displayFormat->format_date_simple_to_db($EntryDateFrom) : "2014-08-01";
				$sqlSearch .= $EntryDateTo ? " and ((EntryDateTime > '".$sqlEntryDateFrom."' and EntryDateTime < '".$displayFormat->format_date_simple_to_db($EntryDateTo)."') or EntryDateTime IS NULL)" : "and (EntryDateTime > '".$sqlEntryDateFrom."' or EntryDateTime IS NULL)";
				
				//print $sqlSearch; exit();
				$this->view->Status = $Status ? $Status : "";				
				$this->view->EntryDateFrom = $EntryDateFrom ? $EntryDateFrom : "";
				$this->view->EntryDateTo = $EntryDateTo ? $EntryDateTo : "";

				$strHiddenSearch = "<input type=hidden name='generate_report' value='true'>";
				
				$strHiddenSearch .= "<input type=hidden name='Status' value='".$Status."'>";
				$strHiddenSearch .= "<input type=hidden name='EntryDateTo' value='".$EntryDateTo."'>";
				$strHiddenSearch .= "<input type=hidden name='EntryDateFrom' value='".$EntryDateFrom."'>";

			}else
			{
				$sqlSearch .= " and Status='in'";
				$this->view->Status = "in";
			}
			
			$this->view->optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $this->view->Status); 			

			$reportHelper->setFetchMode(Zend_Db::FETCH_NUM);

			$arrStock = $reportHelper->getStockLevelDetail($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataStock = $arrStock[1];
			$exportSql = $arrStock[2];
			
			$exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));	
			$export = $exportReport->display_icon();
				

			$sessionStock = new Zend_Session_Namespace('sessionStock');
			$sessionStock->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionStock = new Zend_Session_Namespace('sessionStock');
				$sessionStock->numCounter++;
				return $sessionStock->numCounter;
			}

			$sessionStock->total = 0;
			function format_total($colnum, $rowdata, $bexport)
			{
				$sessionStock = new Zend_Session_Namespace('sessionStock');
				$sessionStock->total += $rowdata[0];
				if ($bexport)
					return $rowdata[0];
				else
					return "<a target='_blank' href='/inventory/brand/flow/search_series/1/Status/".$rowdata[2]."/ItemID/".$rowdata[4]."'>".$rowdata[0]."</a>";
			}
			
			function format_status($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');	
				return $systemSetting->arrStockStatus[$rowdata[2]];
			}				
					
			
			
			$arrHeader = array ('', $this->translate->_('Name'), $this->translate->_('Status'),$this->translate->_('Total'));
			$arrFormat = array('{format_counter}','%1%', '{format_status}', '{format_total}');
			
			$displayTable = new Venz_App_Display_Table(
				array (
					 'data' => $dataStock,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> $arrFormat,					 
					 'sort_column' 	=> array('','Name', 'ItemName', 'Status', 'TotalNum'),
					 'alllen' 		=> $arrStock[0],
					 'title'		=> $this->translate->_('Stock Status Count'),					 
					 'aligndata' 	=> 'CLCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
					 'export_excel' => $export,
					 'tablewidth' => "1100px",
					 'sortby' => $sortby,
					 'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_report = $displayTable->render();
			$this->view->content_total = $sessionStock->total;
			
			$sessionItemCounter->numCounter = 0;
			$sessionStock->total = 0;
			
			$export_excel_x = $Request->getParam('export_excel_x');						
			if ($export_excel_x)
			{

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$exportsql = $Request->getParam('exportsql');	
				$exportReport = new Venz_App_Report_Excel(array('exportsql'=> base64_decode($exportsql), 'db'=>$db, 'headings'=>$arrHeader, 'format'=>$arrFormat));	
				$exportReport->render();
				
			}
						
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}	
		
    }
	
	////  no longer in use ////////////
	public function ajaxgetbrandbyitemAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$ItemName = $Request->getParam('ItemName');
		if ($ItemName){
			$sql = "SELECT Brand.* FROM Brand, Item where Item.BrandID=Brand.ID AND Item.ItemName='".trim($ItemName)."' GROUP BY Brand.ID";
			//print $sql;
			$arrBrandAll = $db->fetchAll($sql);
		}
		else{
			$arrBrandAll = $db->fetchAll("SELECT Brand.* FROM Brand");
		}
		echo "<option value=''>-";
		foreach ($arrBrandAll as $arrBrand)
		{
			echo "<option value='".$arrBrand['ID']."'>".$arrBrand['FullName'];
		}
/*		if ($ItemName)
			echo $libDb->getTableOptions("Item", "ItemName", "ItemName", NULL, NULL, " AND Item.BrandID=".$BrandID); 
		else
			echo $libDb->getTableOptions("Item", "ItemName", "ItemName"); 	
*/		exit();
		
    }
	

	public function ajaxgetpartbymodelAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$ModelNumber = $Request->getParam('ModelNumber');
		$ItemName = $Request->getParam('ItemName');
		$BrandID = $Request->getParam('BrandID');
		$sqlFilter = "";
		if ($ModelNumber)
			$sqlFilter .= " AND Item.ModelNumber='".trim($ModelNumber)."'";
		if ($ItemName)
			$sqlFilter .= " AND Item.ItemName='".trim($ItemName)."'";
		if ($BrandID)
			$sqlFilter .= " AND Item.BrandID=".$BrandID;
		
		echo "<option value=''>-";
		echo $libDb->getTableOptions("Item", "PartNumber", "PartNumber", NULL, NULL, $sqlFilter); 
		exit();
		
    }	
	
	public function ajaxgetmodelbyitemAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$ItemName = $Request->getParam('ItemName');
		$BrandID = $Request->getParam('BrandID');
		$sqlFilter = "";
		if ($ItemName)
			$sqlFilter .= " AND Item.ItemName='".trim($ItemName)."'";
		if ($BrandID)
			$sqlFilter .= " AND Item.BrandID=".$BrandID;
		
		echo "<option value=''>-";
		echo $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", NULL, NULL, $sqlFilter); 
		exit();
		
    }
	

	
	public function ajaxgetpartbyitemAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$ItemName = $Request->getParam('ItemName');
		$BrandID = $Request->getParam('BrandID');
		$sqlFilter = "";
		if ($ItemName)
			$sqlFilter .= " AND Item.ItemName='".trim($ItemName)."'";
		if ($BrandID)
			$sqlFilter .= " AND Item.BrandID=".$BrandID;
		
		echo "<option value=''>-";
		echo $libDb->getTableOptions("Item", "PartNumber", "PartNumber", NULL, NULL, $sqlFilter); 
		exit();
		
    }	
	
	
	
	
	public function ajaxgetitembybrandAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$BrandID = $Request->getParam('BrandID');
		echo "<option value=''>-";
		if ($BrandID)
			echo $libDb->getTableOptions("Item", "ItemName", "ItemName", NULL, NULL, " AND Item.BrandID=".$BrandID); 
		else
			echo $libDb->getTableOptions("Item", "ItemName", "ItemName"); 	
		exit();
		
    }
	
	
	public function ajaxgetmodelbybrandAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$BrandID = $Request->getParam('BrandID');
		echo "<option value=''>-";
		if ($BrandID)
			echo $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", NULL, NULL, " AND Item.BrandID=".$BrandID); 
		else
			echo $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber"); 	
		exit();
		
    }
	

	
	public function ajaxgetpartbybrandAction()
    {
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libDb = new Venz_App_Db_Table();
		$BrandID = $Request->getParam('BrandID');
		echo "<option value=''>-";
		if ($BrandID)
			echo $libDb->getTableOptions("Item", "PartNumber", "PartNumber", NULL, NULL, " AND Item.BrandID=".$BrandID); 
		else
			echo $libDb->getTableOptions("Item", "PartNumber", "PartNumber"); 	
		exit();
		
    }
		
	
	
	public function addbranchesAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$add_branches = $Request->getParam('add_branches');	
			if ($add_branches)
			{
				
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Location = $Request->getParam('Location') ? $Request->getParam('Location') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("Name"=>$Name,"Location"=>$Location,"Address"=>$Address, "Phone"=>$Phone,"Email"=>$Email);

				$db->insert("Branches", $arrInsert);
				$this->appMessage->setNotice(1, $this->translate->_("New branch")." \"<B>".$Name."</B>\" ".$this->translate->_("has been created").".");
				print $Name; exit();		
				//$this->_redirect('/admin/system/branches/');   				
			}
	
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}

	}		
	public function getbranchesAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}	
			
			$arrLastItem = $db->fetchRow("SELECT ID From Branches order by ID desc");
			
			$optionBranches = "<option value=''>-</option>";
			$optionBranches .= $libDb->getTableOptions("Branches", "Name", "ID", $arrLastItem['ID']); 
			$optionBranches .= "<option value='add-new'><<< ".$this->translate->_('Add New')." >>></option>";
			echo $optionBranches;
			exit();
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
	public function branchesAction()   
	{
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'Branches.ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$search_branches = $Request->getParam('search_branches');	
			$this->view->searchBranches = false;
			$strHiddenSearch = "";
			if ($search_branches)
			{
				$this->view->searchBranches = true;
				$Name = $Request->getParam('Name');	
				$Location = $Request->getParam('Location');	
				$Address = $Request->getParam('Address');	
				$Phone = $Request->getParam('Phone');	
				$Email = $Request->getParam('Email');	
				

				$sqlSearch .= $Name ? " and Branches.Name LIKE '%".$Name."%'" : "";
				$sqlSearch .= $Location ? " and Branches.Location LIKE '%".$Location."%'" : "";
				$sqlSearch .= $Phone ? " and Branches.Phone LIKE '%".$Phone."%'" : "";
				$sqlSearch .= $Address ? " and Branches.Address LIKE '%".$Address."%'" : "";
				$sqlSearch .= $Email ? " and Branches.Email LIKE '%".$Email."%'" : "";
				
				//print $sqlSearch; exit();
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Location = $Location ? $Location : "";				
				$this->view->Phone = $Phone ? $Phone : "";				
				$this->view->Address = $Address ? $Address : "";				
				$this->view->Email = $Email ? $Email : "";			

				$strHiddenSearch = "<input type=hidden name='search_branches' value='true'>";
				
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='Location' value='".$Location."'>";
				$strHiddenSearch .= "<input type=hidden name='Phone' value='".$Phone."'>";
				$strHiddenSearch .= "<input type=hidden name='Email' value='".$Email."'>";
				$strHiddenSearch .= "<input type=hidden name='Address' value='".$Address."'>";

			}
			
			$add_branches = $Request->getParam('add_branches');	
			if ($add_branches)
			{
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Location = $Request->getParam('Location') ? $Request->getParam('Location') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("Name"=>$Name,"Location"=>$Location,"Address"=>$Address, "Phone"=>$Phone,"Email"=>$Email);

				$db->insert("Branches", $arrInsert);
				$this->appMessage->setNotice(1, $this->translate->_("New branch")." \"<B>".$Name."</B>\" ".$this->translate->_("has been created").".");
						
				$this->_redirect('/admin/system/branches/');   				
			}
			
			$this->view->edit_branches = '';
			$edit_branches = $Request->getParam('edit_branches');	
			if ($edit_branches)
			{
				$this->view->edit_branches = $edit_branches;
				$arrBranchesDetail = $sysHelper->getBranchesDetail($edit_branches);
				$this->view->Name = $arrBranchesDetail['Name'];			
				$this->view->Location = $arrBranchesDetail['Location'];			
				$this->view->Address = $arrBranchesDetail['Address'];		
				$this->view->Email = $arrBranchesDetail['Email'];		
				$this->view->Phone = $arrBranchesDetail['Phone'];		
			}					
		
			$save_branches = $Request->getParam('save_branches');	
			if ($save_branches)
			{
				$ID = $Request->getParam('save_branches_id') ? $Request->getParam('save_branches_id') : new Zend_Db_Expr("NULL");
				
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Location = $Request->getParam('Location') ? $Request->getParam('Location') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				
				$arrUpdate = array("Name"=>$Name,"Location"=>$Location,"Address"=>$Address,"Email"=>$Email,"Phone"=>$Phone);
				
				$db->update("Branches", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, $this->translate->_('Record for')." <B>".$Name."</B> ".$this->translate->_('has been updated').".");
				$this->_redirect('/admin/system/branches/'); 
								
			}


			$remove_branches = $Request->getParam('remove_branches');	
			if ($remove_branches)
			{
				$db->delete("Branches", "ID=".$remove_branches);
				$this->_redirect('/admin/system/branches/');   				
			}			

			
			$sysHelper->setFetchMode(Zend_Db::FETCH_NUM);

			$arrBranches = $sysHelper->getBranches($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataBranches = $arrBranches[1];

			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return "<a href='/admin/system/branches/edit_branches/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteBranches(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}		

			$sessionBranches = new Zend_Session_Namespace('sessionBranches');
			$sessionBranches->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionBranches = new Zend_Session_Namespace('sessionBranches');
				$sessionBranches->numCounter++;
				return $sessionBranches->numCounter;
			}

		
			
			$arrHeader = array ('', $this->translate->_('Name'), $this->translate->_('Location'),$this->translate->_('Address'),$this->translate->_('Phone'), $this->translate->_('Email'), $this->translate->_('Action'));
			$displayTable = new Venz_App_Display_Table(
				array (
					 'data' => $dataBranches,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%1%', '%2%', '%3%', '%4%','%5%', '{format_action}'),					 
					 'sort_column' 	=> array('','Name', 'Location', 'Address', 'Phone', 'Email', ''),
					 'alllen' 		=> $arrVendors[0],
					 'title'		=> $this->translate->_('Branches'),					 
					 'aligndata' 	=> 'CLLLLC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
					 'tablewidth' => "800px",
					 'sortby' => $sortby,
					 'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_branches = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
		

	public function addvendorsAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}
			
			$add_vendors = $Request->getParam('add_vendors');	
			if ($add_vendors)
			{
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("Name"=>$Name,"Address"=>$Address, "Phone"=>$Phone,"Email"=>$Email);
				$db->insert("Vendors", $arrInsert);
				print $Name; exit();	
						
			}
	
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}

	}		
	public function getvendorsAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}	
			
			$arrLastItem = $db->fetchRow("SELECT ID From Vendors order by ID desc");
			
			$optionBranches = "<option value=''>-</option>";
			$optionBranches .= $libDb->getTableOptions("Vendors", "Name", "ID", $arrLastItem['ID']); 
			$optionBranches .= "<option value='add-new'><<< ".$this->translate->_('Add New')." >>></option>";
			echo $optionBranches;
			exit();
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}		

	public function vendorsAction()   
	{
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'Vendors.ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
	

			$sqlSearch = "";
			$search_vendors = $Request->getParam('search_vendors');	
			$this->view->searchVendors = false;
			$strHiddenSearch = "";
			if ($search_vendors)
			{
				$this->view->searchVendors = true;
				$Name = $Request->getParam('Name');	
				$Address = $Request->getParam('Address');	
				$Phone = $Request->getParam('Phone');	
				$Email = $Request->getParam('Email');	
				

				$sqlSearch .= $Name ? " and Vendors.Name LIKE '%".$Name."%'" : "";
				$sqlSearch .= $Phone ? " and Vendors.Phone LIKE '%".$Phone."%'" : "";
				$sqlSearch .= $Address ? " and Vendors.Address LIKE '%".$Address."%'" : "";
				$sqlSearch .= $Email ? " and Vendors.Email LIKE '%".$Email."%'" : "";
				
				//print $sqlSearch; exit();
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Phone = $Phone ? $Phone : "";				
				$this->view->Address = $Address ? $Address : "";				
				$this->view->Email = $Email ? $Email : "";			

				$strHiddenSearch = "<input type=hidden name='search_vendors' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='Phone' value='".$Phone."'>";
				$strHiddenSearch .= "<input type=hidden name='Email' value='".$Email."'>";
				$strHiddenSearch .= "<input type=hidden name='Address' value='".$Address."'>";

			}
			
			$add_vendors = $Request->getParam('add_vendors');	
			if ($add_vendors)
			{
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("Name"=>$Name,"Address"=>$Address, "Phone"=>$Phone,"Email"=>$Email);

				$db->insert("Vendors", $arrInsert);
				$this->appMessage->setNotice(1, $this->translate->_('New vendor')." \"<B>".$Name."</B>\" ".$this->translate->_('has been created').".");
						
				$this->_redirect('/admin/system/vendors/');   				
			}
			
			$this->view->edit_vendors = '';
			$edit_vendors = $Request->getParam('edit_vendors');	
			if ($edit_vendors)
			{
				$this->view->edit_vendors = $edit_vendors;
				$arrVendorDetail = $sysHelper->getVendorsDetail($edit_vendors);
				$this->view->Name = $arrVendorDetail['Name'];			
				$this->view->Address = $arrVendorDetail['Address'];		
				$this->view->Email = $arrVendorDetail['Email'];		
				$this->view->Phone = $arrVendorDetail['Phone'];		
			}					
		
			$save_vendors = $Request->getParam('save_vendors');	
			if ($save_vendors)
			{
				$ID = $Request->getParam('save_vendors_id') ? $Request->getParam('save_vendors_id') : new Zend_Db_Expr("NULL");
				
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				
				$arrUpdate = array("Name"=>$Name,"Address"=>$Address,"Email"=>$Email,"Phone"=>$Phone);
				
				$db->update("Vendors", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, $this->translate->_('Record for')." <B>".$Name."</B> ".$this->translate->_('has been updated').".");
				$this->_redirect('/admin/system/vendors/'); 
								
			}


			$remove_vendors = $Request->getParam('remove_vendors');	
			if ($remove_vendors)
			{
				$db->delete("Vendors", "ID=".$remove_vendors);
				$this->_redirect('/admin/system/vendors/');   				
			}			

			
			$sysHelper->setFetchMode(Zend_Db::FETCH_NUM);

			$arrVendors = $sysHelper->getVendors($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataVendors = $arrVendors[1];
			
			function format_date($colnum, $rowdata)
			{

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_datetime_simple($rowdata[6], "<BR>");
				
			}
			
			function format_date_created($colnum, $rowdata)
			{

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_datetime_simple($rowdata[7], "<BR>");
				
			}
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return "<a href='/admin/system/vendors/edit_vendors/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteVendors(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}		

			$sessionUsers = new Zend_Session_Namespace('sessionUsers');
			$sessionUsers->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionUsers = new Zend_Session_Namespace('sessionUsers');
				$sessionUsers->numCounter++;
				return $sessionUsers->numCounter;
			}
			
			function format_active($colnum, $rowdata)
			{
				return $rowdata[5] ? "Yes" : "No";
			}
			
			$strSearch = "";
			if ($this->view->searchUsers)
				$strSearch = "<input type=hidden name=''>";
			
			$arrHeader = array ('', $this->translate->_('Name'), $this->translate->_('Address'),$this->translate->_('Phone'), $this->translate->_('Email'), $this->translate->_('Action'));
			$displayTable = new Venz_App_Display_Table(
				array (
					 'data' => $dataVendors,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%1%', '%2%', '%3%', '%4%', '{format_action}'),					 
					 'sort_column' 	=> array('','Name', 'Address', 'Phone', 'Email', ''),
					 'alllen' 		=> $arrVendors[0],
					 'title'		=> $this->translate->_('Vendors'),					 
					 'aligndata' 	=> 'CLLLLC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
					 'tablewidth' => "800px",
					 'sortby' => $sortby,
					 'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_vendors = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}		
		
		

	public function addcustomersAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}
			
			$add_customers = $Request->getParam('add_customers');	
			if ($add_customers)
			{
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("Name"=>$Name,"Address"=>$Address, "Phone"=>$Phone,"Email"=>$Email);
				$db->insert("Customers", $arrInsert);
				print $Name; exit();	
						
			}
	
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}

	}		
	public function getcustomersAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}

			$arrLastItem = $db->fetchRow("SELECT ID From Customers order by ID desc");
			
			$optionCustomers = "<option value=''>-</option>";
			$optionCustomers .= $libDb->getTableOptions("Customers", "Name", "ID", $arrLastItem['ID']); 
			$optionCustomers .= "<option value='add-new'><<< ".$this->translate->_('Add New')." >>></option>";
			echo $optionCustomers;
			exit();
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}		

	public function customersAction()   
	{
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'Customers.ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
	

			$sqlSearch = "";
			$search_customers = $Request->getParam('search_customers');	
			$this->view->searchCustomers = false;
			$strHiddenSearch = "";
			if ($search_customers)
			{
				$this->view->searchCustomers = true;
				$Name = $Request->getParam('Name');	
				$Address = $Request->getParam('Address');	
				$Phone = $Request->getParam('Phone');	
				$Email = $Request->getParam('Email');	
				

				$sqlSearch .= $Name ? " and Customers.Name LIKE '%".$Name."%'" : "";
				$sqlSearch .= $Phone ? " and Customers.Phone LIKE '%".$Phone."%'" : "";
				$sqlSearch .= $Address ? " and Customers.Address LIKE '%".$Address."%'" : "";
				$sqlSearch .= $Email ? " and Customers.Email LIKE '%".$Email."%'" : "";
				
				//print $sqlSearch; exit();
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Phone = $Phone ? $Phone : "";				
				$this->view->Address = $Address ? $Address : "";				
				$this->view->Email = $Email ? $Email : "";			

				$strHiddenSearch = "<input type=hidden name='search_customers' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='Phone' value='".$Phone."'>";
				$strHiddenSearch .= "<input type=hidden name='Email' value='".$Email."'>";
				$strHiddenSearch .= "<input type=hidden name='Address' value='".$Address."'>";

			}
			
			$add_customers = $Request->getParam('add_customers');	
			if ($add_customers)
			{
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("Name"=>$Name,"Address"=>$Address, "Phone"=>$Phone,"Email"=>$Email);

				$db->insert("Customers", $arrInsert);
				$this->appMessage->setNotice(1, $this->translate->_('New customer')." \"<B>".$Name."</B>\" ".$this->translate->_('has been created').".");
						
				$this->_redirect('/admin/system/customers/');   				
			}
			
			$this->view->edit_customers = '';
			$edit_customers = $Request->getParam('edit_customers');	
			if ($edit_customers)
			{
				$this->view->edit_customers = $edit_customers;
				$arrCustomerDetail = $sysHelper->getCustomersDetail($edit_customers);
				$this->view->Name = $arrCustomerDetail['Name'];			
				$this->view->Address = $arrCustomerDetail['Address'];		
				$this->view->Email = $arrCustomerDetail['Email'];		
				$this->view->Phone = $arrCustomerDetail['Phone'];		
			}					
		
			$save_customers = $Request->getParam('save_customers');	
			if ($save_customers)
			{
				$ID = $Request->getParam('save_customers_id') ? $Request->getParam('save_customers_id') : new Zend_Db_Expr("NULL");
				
				$Name = $Request->getParam('Name') ? $Request->getParam('Name') : new Zend_Db_Expr("NULL");
				$Address = $Request->getParam('Address') ? $Request->getParam('Address') : new Zend_Db_Expr("NULL");
				$Email = $Request->getParam('Email') ? $Request->getParam('Email') : new Zend_Db_Expr("NULL");
				$Phone = $Request->getParam('Phone') ? $Request->getParam('Phone') : new Zend_Db_Expr("NULL");
				
				$arrUpdate = array("Name"=>$Name,"Address"=>$Address,"Email"=>$Email,"Phone"=>$Phone);
				
				$db->update("Customers", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, $this->translate->_('Record for')." <B>".$Name."</B> ".$this->translate->_('has been updated').".");
				$this->_redirect('/admin/system/customers/'); 
								
			}


			$remove_customers = $Request->getParam('remove_customers');	
			if ($remove_customers)
			{
				$db->delete("Customers", "ID=".$remove_customers);
				$this->_redirect('/admin/system/customers/');   				
			}			

			
			$sysHelper->setFetchMode(Zend_Db::FETCH_NUM);

			$arrCustomers = $sysHelper->getCustomers($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataCustomers = $arrCustomers[1];
			
			function format_date($colnum, $rowdata)
			{

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_datetime_simple($rowdata[6], "<BR>");
				
			}
			
			function format_date_created($colnum, $rowdata)
			{

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_datetime_simple($rowdata[7], "<BR>");
				
			}
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return "<a href='/admin/system/customers/edit_customers/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteVendors(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}		

			$sessionCustomers = new Zend_Session_Namespace('sessionCustomers');
			$sessionCustomers->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionCustomers = new Zend_Session_Namespace('sessionCustomers');
				$sessionCustomers->numCounter++;
				return $sessionCustomers->numCounter;
			}
			
			function format_active($colnum, $rowdata)
			{
				return $rowdata[5] ? "Yes" : "No";
			}
			
			$strSearch = "";
			if ($this->view->searchUsers)
				$strSearch = "<input type=hidden name=''>";
			
			$arrHeader = array ('', $this->translate->_('Name'), $this->translate->_('Address'),$this->translate->_('Phone'), $this->translate->_('Email'), $this->translate->_('Action'));
			$displayTable = new Venz_App_Display_Table(
				array (
					 'data' => $dataCustomers,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%1%', '%2%', '%3%', '%4%', '{format_action}'),					 
					 'sort_column' 	=> array('','Name', 'Address', 'Phone', 'Email', ''),
					 'alllen' 		=> $arrVendors[0],
					 'title'		=> $this->translate->_('Customers'),					 
					 'aligndata' 	=> 'CLLLLC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
					 'tablewidth' => "800px",
					 'sortby' => $sortby,
					 'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_customers = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}			
		
		public function jsonrolesaccessexAction()
		{
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			$roles = $Request->getParam('roles');	
			
			$arrACLRoleMap = array();
			$arrACLRoleAll = $db->fetchAll("SELECT * FROM ACLMap where Role = '".$roles."'");
			foreach ($arrACLRoleAll as $arrACLRole)
			{
				$arrACLRoleMap[$arrACLRole['Resources']][$arrACLRole['Priviledges']] = 1;
			}
			
			
			$arrACLResourcesAll = $db->fetchAll("SELECT * FROM ACLResources");
			$arrACLPriviledgesAll = $db->fetchAll("SELECT * FROM ACLPriviledges");
		
			$matrix = array();
			foreach ($arrACLPriviledgesAll as $i => $arrACLPriviledges)
			{
				$matrix['priviledges'][$i] = $arrACLPriviledges['Name'];
		
			}
			
			
			
			foreach ($arrACLResourcesAll as $arrACLResources)
			{
				foreach ($arrACLPriviledgesAll as $arrACLPriviledges)
				{
					$matrix['data'][$arrACLResources['Name']][$arrACLPriviledges['Name']] = $arrACLRoleMap[$arrACLResources['Name']][$arrACLPriviledges['Name']] ? true : false;
			
				}
				
			}

			echo Zend_Json::encode($matrix);
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}			
			
			
			
			
			exit();
		}

		
        public function rolesaccessexAction()   
        {	
			try {
				$Request = $this->getRequest();			
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$sysAcl = new Venz_App_System_Acl();
				$libDb = new Venz_App_Db_Table();
				/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
				$accessMap = $Request->getParam('accessMap');
				$ACLRole = $Request->getParam('ACLRole');	
				$Update = $Request->getParam('Update');	
				$this->view->optionsRole = $libDb->getTableOptions('ACLRole', "Name", "Name", $ACLRole);
				
				if ($Update)
				{
					
					$db->query("DELETE FROM ACLMap where Role = '".$ACLRole."'");
					
					foreach ($accessMap as $indexResource => $value)
					{
						$arrIndexResource = explode("|", $indexResource);
						$arrResource = array("Role"=>$ACLRole, "Resources" => $arrIndexResource[0], "Priviledges" => $arrIndexResource[1], "Allow" => 1);
						$db->insert("ACLMap", $arrResource);
					}
				}
				
			}catch (Exception $e)
			{
				echo $e->getMessage();
			}
						
		}

		
        public function rolesaccessAction()   
        {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$add_rolesaccess = $Request->getParam('add_rolesaccess');	
			if ($add_rolesaccess)
			{
				$Role = $Request->getParam('Role');	
				$Resources = $Request->getParam('Resources');
				$Priviledges = $Request->getParam('Priviledges');
				$Access = $Request->getParam('Access');
				if (!$sysAcl->isRolesaccess($Role, $Resources, $Priviledges))
				{
					$arrInsert = array("Role"=>$Role,"Resources"=>$Resources,"Priviledges"=>$Priviledges,"Allow"=>$Access);
					$db->insert("ACLMap", $arrInsert);
				}
				$this->_redirect('/admin/acl/rolesaccess/');   				
			}
			
			$save_rolesaccess = $Request->getParam('save_rolesaccess');	
			if ($save_rolesaccess)
			{
				$Role = $Request->getParam('Role');	
				$Resources = $Request->getParam('Resources');
				$Priviledges = $Request->getParam('Priviledges');
				$Access = $Request->getParam('Access');		
				$ID = $Request->getParam('save_rolesaccess_id');	
				if (!$sysAcl->isRolesaccess($Role, $Resources, $Priviledges))
				{
					$arrUpdate = array("Role"=>$Role,"Resources"=>$Resources,"Priviledges"=>$Priviledges,"Allow"=>$Access);
					$db->update("ACLMap", $arrUpdate, "ID=".$ID);
				}
				$this->_redirect('/admin/acl/rolesaccess/');   				
			}


			$remove_rolesaccess = $Request->getParam('remove_rolesaccess');	
			if ($remove_rolesaccess)
			{
				$db->delete("ACLMap", "ID=".$remove_rolesaccess);
				$this->_redirect('/admin/acl/rolesaccess/');   				
			}			
			
			$this->view->edit_rolesaccess = '';
			$edit_rolesaccess = $Request->getParam('edit_rolesaccess');	
			if ($edit_rolesaccess)
			{
				$this->view->edit_rolesaccess = $edit_rolesaccess;
				$arrRolesaccessDetail = $sysAcl->getRolesaccessDetail($edit_rolesaccess);
				$this->view->Role = $arrRolesaccessDetail['Role'];			
				$this->view->Resources = $arrRolesaccessDetail['Resources'];		
				$this->view->Priviledges = $arrRolesaccessDetail['Priviledges'];		
				$this->view->Access = $arrRolesaccessDetail['Access'];		
			}			
			
			$sqlSearch = "";
			$search_rolesaccess = $Request->getParam('search_rolesaccess');	
			$strHiddenSearch = "";
			if ($search_rolesaccess)
			{
				$Role = $Request->getParam('Role');	
				$sqlSearch .= $Role ? " and Role LIKE '%".$Role."%'" : "";
				
				$Resources = $Request->getParam('Resources');	
				$sqlSearch .= $Resources ? " and Resources LIKE '%".$Resources."%'" : "";

				$Priviledges = $Request->getParam('Priviledges');	
				$sqlSearch .= $Priviledges ? " and Priviledges LIKE '%".$Priviledges."%'" : "";

				$Access = $Request->getParam('Access');	
				$sqlSearch .= $Access ? " and Allow = ".$Access."" : "";
				
				$this->view->Role = $Role ? $Role : "";				
				$this->view->Resources = $Resources ? $Resources : "";				
				$this->view->Priviledges = $Priviledges ? $Priviledges : "";				
				$this->view->Access = $Access ? $Access : "";				
				$strHiddenSearch = "<input type=hidden name='search_rolesaccess' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Role' value='".$Role."'>";
				$strHiddenSearch .= "<input type=hidden name='Resources' value='".$Resources."'>";
				$strHiddenSearch .= "<input type=hidden name='Priviledges' value='".$Priviledges."'>";
				$strHiddenSearch .= "<input type=hidden name='Access' value='".$Access."'>";
				
			
			}

			
			$this->view->optionsRole = $libDb->getTableOptions('ACLRole', "Name", "Name", $this->view->Role);
			$this->view->optionsResources = $libDb->getTableOptions('ACLResources', "Name", "Name", $this->view->Resources);
			$this->view->optionsPriviledges = $libDb->getTableOptions('ACLPriviledges', "Name", "Name", $this->view->Priviledges);
			
			$this->view->optionsAccess = "";
			foreach (array(1=>'Allow', 0=>'Deny') as $AccessID => $Access)
			{
				if ($this->view->Access == $AccessID && strlen($AccessID) == 0)
					$this->view->optionsAccess .= "<OPTION value='".$AccessID."' SELECTED>".$Access;
				else
					$this->view->optionsAccess .= "<OPTION value='".$AccessID."'>".$Access;
			}
			
			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
			$arrRolesaccess = $sysAcl->getRolesaccess($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataRolesaccess = $arrRolesaccess[1];
			
			function format_access($colnum, $rowdata)
			{
				return $rowdata[4] ? "Allow" : "Deny";
			}			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');			
				return "<a href='/admin/acl/rolesaccess/edit_rolesaccess/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteRolesaccess(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}
			$sessionRolesaccess = new Zend_Session_Namespace('sessionRolesaccess');
			$sessionRolesaccess->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionRolesaccess = new Zend_Session_Namespace('sessionRolesaccess');
				$sessionRolesaccess->numCounter++;
				return $sessionRolesaccess->numCounter;
			}
			
			$arrHeader = array ('', 'ID', $this->translate->_('Role'), $this->translate->_('Resources'), $this->translate->_('Priviledges'), $this->translate->_('Access'), '');
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataRolesaccess,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','%1%','%2%','%3%','{format_access}', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','Role', 'Resources', 'Priviledges', 'Allow', ''),
					 'alllen' 		=> $arrRolesaccess[0],
					 'title'		=> 'Roles',					 
					 'aligndata' 	=> 'LLLLL',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "700px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_rolesaccess = $displayTable->render();
        			
        }	
	
        public function rolesAction()   
        {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 30 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$add_roles = $Request->getParam('add_roles');	
			if ($add_roles)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');
				$ParentName = $Request->getParam('ParentName');
				$arrInsert = array("Name"=>$Name,"Description"=>$Description,"ParentName"=>$ParentName);
				$db->insert("ACLRole", $arrInsert);
				$this->_redirect('/admin/acl/roles/');   				
			}
			
			$save_roles = $Request->getParam('save_roles');	
			if ($save_roles)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');
				$ParentName = $Request->getParam('ParentName');				
				$ID = $Request->getParam('save_roles_id');	
				$arrUpdate = array("Name"=>$Name,"Description"=>$Description,"ParentName"=>$ParentName);
				$db->update("ACLRole", $arrUpdate, "ID=".$ID);
				$this->_redirect('/admin/acl/roles/');   				
			}


			$remove_roles = $Request->getParam('remove_roles');	
			if ($remove_roles)
			{
				$db->delete("ACLRole", "ID=".$remove_roles);
				$this->_redirect('/admin/acl/roles/');   				
			}			
			
			$this->view->edit_roles = '';
			$edit_roles = $Request->getParam('edit_roles');	
			if ($edit_roles)
			{
				$this->view->edit_roles = $edit_roles;
				$arrRolesDetail = $sysAcl->getRolesDetail($edit_roles);
				$this->view->Name = $arrRolesDetail['Name'];			
				$this->view->Description = $arrRolesDetail['Description'];		
				$this->view->ParentName = $arrRolesDetail['ParentName'];		
			}			
			
			$sqlSearch = "";
			$search_roles = $Request->getParam('search_roles');	
			$strHiddenSearch = "";
			if ($search_roles)
			{
				$Name = $Request->getParam('Name');	
				$sqlSearch .= $Name ? " and Name LIKE '%".$Name."%'" : "";
				
				$Description = $Request->getParam('Description');	
				$sqlSearch .= $Description ? " and Description LIKE '%".$Description."%'" : "";

				$ParentName = $Request->getParam('ParentName');	
				$sqlSearch .= $ParentName ? " and ParentName LIKE '%".$ParentName."%'" : "";

				
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Description = $Description ? $Description : "";				
				$this->view->ParentName = $ParentName ? $ParentName : "";				
				$strHiddenSearch = "<input type=hidden name='search_roles' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='ParentName' value='".$ParentName."'>";
				$strHiddenSearch .= "<input type=hidden name='Description' value='".$Description."'>";

			}


			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
			$arrRoles = $sysAcl->getRoles($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataRoles = $arrRoles[1];
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$arrMapExist = $db->fetchRow("SELECT * FROM ACLMap where Role ='".$rowdata[1]."'");
				
				if ($arrMapExist)
					return " ** ";
				else
					return "<a href='/admin/acl/roles/edit_roles/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteRoles(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}
			$sessionRoles = new Zend_Session_Namespace('sessionRoles');
			$sessionRoles->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionRoles = new Zend_Session_Namespace('sessionRoles');
				$sessionRoles->numCounter++;
				return $sessionRoles->numCounter;
			}
			
			function format_count($colnum, $rowdata)
			{
			
				$db = Zend_Db_Table::getDefaultAdapter(); 
				return count($db->fetchAll("SELECT * FROM ACLUsers where ACLRole='".$rowdata[1]."'"));
			}
			
			$arrHeader = array ('', 'ID', $this->translate->_('Name'), $this->translate->_('Description'), $this->translate->_('Parent Name'), $this->translate->_('Number of<BR>Users'), 'Action');
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataRoles,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','%1%','%2%','%3%','{format_count}', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','Name', 'Description', 'ParentName', '', ''),
					 'alllen' 		=> $arrRoles[0],
					 'title'		=> $this->translate->_('Roles'),					 
					 'aligndata' 	=> 'LLLLLCL',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "700px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_roles = $displayTable->render();
        			
        }
	
	
	
	
        public function priviledgesAction()   
        {
		
			try {
		
				$Request = $this->getRequest();			
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$sysAcl = new Venz_App_System_Acl();
				$libDb = new Venz_App_Db_Table();
				/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
				
				$sortby = $Request->getParam('sortby');			
				if (strlen($sortby) == 0) $sortby = 'ID';
					
				$ascdesc = $Request->getParam('ascdesc');			
				if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
				
				$showPage = $Request->getParam('Pagerpagenum');			
				if (!$showPage) $showPage = 1; 
					
				$pagerNext = $Request->getParam('Pager_next_page');			
				if (strlen($pagerNext) > 0) $showPage++; 	

				$pagerPrev = $Request->getParam('Pager_prev_page');			
				if (strlen($pagerPrev) > 0) $showPage--; 	
				
				$recordsPerPage = 10 ;
				////////////////////////////////////////////////////////////////////////////////////////
				$add_priviledges = $Request->getParam('add_priviledges');	
				if ($add_priviledges)
				{
					$Name = $Request->getParam('Name');	
					$Description = $Request->getParam('Description');
					$arrInsert = array("Name"=>$Name,"Description"=>$Description);
					$db->insert("ACLPriviledges", $arrInsert);
					$this->_redirect('/admin/acl/priviledges/');   				
				}
				
				$save_priviledges = $Request->getParam('save_priviledges');	
				if ($save_priviledges)
				{
					$Name = $Request->getParam('Name');	
					$Description = $Request->getParam('Description');					
					$ID = $Request->getParam('save_priviledges_id');	
					$arrUpdate = array("Name"=>$Name,"Description"=>$Description);
					$db->update("ACLPriviledges", $arrUpdate, "ID=".$ID);
					$this->_redirect('/admin/acl/priviledges/');   				
				}


				$remove_priviledges = $Request->getParam('remove_priviledges');	
				if ($remove_priviledges)
				{
					$db->delete("ACLPriviledges", "ID=".$remove_priviledges);
					$this->_redirect('/admin/acl/priviledges/');   				
				}			
				
				$this->view->edit_priviledges = '';
				$edit_priviledges = $Request->getParam('edit_priviledges');	
				if ($edit_priviledges)
				{
					$this->view->edit_priviledges = $edit_priviledges;
					$arrPriviledgesDetail = $sysAcl->getPriviledgesDetail($edit_priviledges);
					$this->view->Name = $arrPriviledgesDetail['Name'];			
					$this->view->Description = $arrPriviledgesDetail['Description'];		
				}			
				
				$sqlSearch = "";
				$search_priviledges = $Request->getParam('search_priviledges');	
				$strHiddenSearch = "";
				if ($search_priviledges)
				{
					$Name = $Request->getParam('Name');	
					$sqlSearch .= $Name ? " and Name LIKE '%".$Name."%'" : "";
					
					$Description = $Request->getParam('Description');	
					$sqlSearch .= $Description ? " and Description LIKE '%".$Description."%'" : "";
					
					$this->view->Name = $Name ? $Name : "";				
					$this->view->Description = $Description ? $Description : "";				
					$strHiddenSearch = "<input type=hidden name='search_priviledges' value='true'>";
					$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
					$strHiddenSearch .= "<input type=hidden name='Description' value='".$Description."'>";
							
				
				
				}


				$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
				$arrPriviledges = $sysAcl->getPriviledges($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
				$dataPriviledges = $arrPriviledges[1];
				
				function format_action($colnum, $rowdata)
				{
					$systemSetting = new Zend_Session_Namespace('systemSetting');

					$db = Zend_Db_Table::getDefaultAdapter(); 
					$arrMapExist = $db->fetchRow("SELECT * FROM ACLMap where Priviledges ='".$rowdata[1]."'");
					
					if ($arrMapExist)
						return " ** ";
					else
						return "<a href='/admin/acl/priviledges/edit_priviledges/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeletePriviledges(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
				}
				$sessionPriviledges = new Zend_Session_Namespace('sessionPriviledges');
				$sessionPriviledges->numCounter = $recordsPerPage * ($showPage-1);
				function format_counter($colnum, $rowdata)
				{
					$sessionPriviledges = new Zend_Session_Namespace('sessionPriviledges');
					$sessionPriviledges->numCounter++;
					return $sessionPriviledges->numCounter;
				}
				
				$arrHeader = array ('', 'ID', $this->translate->_('Name'), $this->translate->_('Description'), $this->translate->_('Action'));
				$displayTable = new Venz_App_Display_Table(
					array (
						 'data' => $dataPriviledges,
						 'headings' => $arrHeader,
						 'format' 		=> array('{format_counter}','%0%','%1%','%2%', '{format_action}'),					 
						 'sort_column' 	=> array('','ID','Name', 'Description', ''),
						 'alllen' 		=> $arrPriviledges[0],
						 'title'		=> $this->translate->_('Priviledges'),					 
						 'aligndata' 	=> 'LLLLL',
						 'pagelen' 		=> $recordsPerPage,
						 'numcols' 		=> sizeof($arrHeader),
						 'tablewidth' => "700px",
						 'sortby' => $sortby,
						 'ascdesc' => $ascdesc,
						 'hiddenparam' => $strHiddenSearch,
					)
				);
				$this->view->content_priviledges = $displayTable->render();
        	}catch (Exception $e) {
		
				echo $e->getMessage();
			}		
        }

		
        public function resourcesAction()   
        {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysAcl = new Venz_App_System_Acl();
			$libDb = new Venz_App_Db_Table();
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'asc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$add_resources = $Request->getParam('add_resources');	
			if ($add_resources)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');	
				$Category = $Request->getParam('Category');	
				$ParentName = $Request->getParam('ParentName');	
				$arrInsert = array("Name"=>$Name,"Description"=>$Description,"Category"=>$Category,"ParentName"=>$ParentName);
				$db->insert("ACLResources", $arrInsert);
				$this->_redirect('/admin/acl/resources/');   				
			}
			
			$save_resources = $Request->getParam('save_resources');	
			if ($save_resources)
			{
				$Name = $Request->getParam('Name');	
				$Description = $Request->getParam('Description');	
				$Category = $Request->getParam('Category');	
				$ParentName = $Request->getParam('ParentName');					
				$ID = $Request->getParam('save_resources_id');	
				$arrUpdate = array("Name"=>$Name,"Description"=>$Description, "Category"=>$Category, "ParentName"=>$ParentName);
				$db->update("ACLResources", $arrUpdate, "ID=".$ID);
				$this->_redirect('/admin/acl/resources/');   				
			}


			$remove_resources = $Request->getParam('remove_resources');	
			if ($remove_resources)
			{
				$db->delete("ACLResources", "ID=".$remove_resources);
				$this->_redirect('/admin/acl/resources/');   				
			}			
			
			$this->view->edit_resources = '';
			$edit_resources = $Request->getParam('edit_resources');	
			if ($edit_resources)
			{
				$this->view->edit_resources = $edit_resources;
				$arrResourcesDetail = $sysAcl->getResourcesDetail($edit_resources);
				$this->view->Name = $arrResourcesDetail['Name'];			
				$this->view->Description = $arrResourcesDetail['Description'];			
				$this->view->Category = $arrResourcesDetail['Category'];			
				$this->view->ParentName = $arrResourcesDetail['ParentName'];			
			}			
			
			$sqlSearch = "";
			$search_resources = $Request->getParam('search_resources');	
			$strHiddenSearch = "";
			if ($search_resources)
			{
				$Name = $Request->getParam('Name');	
				$sqlSearch .= $Name ? " and Name LIKE '%".$Name."%'" : "";
				
				$Description = $Request->getParam('Description');	
				$sqlSearch .= $Description ? " and Description LIKE '%".$Description."%'" : "";
				
				$Category = $Request->getParam('Category');	
				$sqlSearch .= $Category ? " and Category LIKE '%".$Category."%'" : "";
				
				$ParentName = $Request->getParam('ParentName');	
				$sqlSearch .= $ParentName ? " and ParentName LIKE '%".$ParentName."%'" : "";
				
				$this->view->Name = $Name ? $Name : "";				
				$this->view->Description = $Description ? $Description : "";				
				$this->view->Category = $Category ? $Category : "";				
				$this->view->ParentName = $ParentName ? $ParentName : "";				
				$strHiddenSearch = "<input type=hidden name='search_resources' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='Name' value='".$Name."'>";
				$strHiddenSearch .= "<input type=hidden name='Description' value='".$Description."'>";
				$strHiddenSearch .= "<input type=hidden name='Category' value='".$Category."'>";
				$strHiddenSearch .= "<input type=hidden name='ParentName' value='".$ParentName."'>";
					
			
			
			}



			$sysAcl->setFetchMode(Zend_Db::FETCH_NUM);
			$arrResources = $sysAcl->getResources($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataResources = $arrResources[1];
			
			function format_action($colnum, $rowdata)
			{
				
				$db = Zend_Db_Table::getDefaultAdapter(); 
				$arrMapExist = $db->fetchRow("SELECT * FROM ACLMap where Resources ='".$rowdata[1]."'");
				$systemSetting = new Zend_Session_Namespace('systemSetting');	
				if ($arrMapExist)
					return " ** ";
				else
					return "<a href='/admin/acl/resources/edit_resources/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteResources(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}
			$sessionResources = new Zend_Session_Namespace('sessionResources');
			$sessionResources->numCounter = $recordsPerPage * ($showPage-1);
			function format_counter($colnum, $rowdata)
			{
				$sessionResources = new Zend_Session_Namespace('sessionResources');
				$sessionResources->numCounter++;
				return $sessionResources->numCounter;
			}
			
			$arrHeader = array ('', 'ID', $this->translate->_('Name'), $this->translate->_('Description'), $this->translate->_('Category'), $this->translate->_('Parent Name'), '');
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataResources,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','%1%','%2%','%3%','%4%', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','Name', 'Description', 'Category', 'ParentName', ''),
					 'alllen' 		=> $arrResources[0],
					 'title'		=> $this->translate->_('Resources'),					 
					 'aligndata' 	=> 'LLLL',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "700px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_resources = $displayTable->render();
        			
        }
				
	public function settingsAction()   
	{
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}
			
			
			
			$save_settings = $Request->getParam('save_settings');	
			if ($save_settings)
			{
				$SettingLanguage = $Request->getParam('SettingLanguage');	
				$SettingMarkup = $Request->getParam('SettingMarkup');	
				$SettingCurrency = $Request->getParam('SettingCurrency');	
				$SettingMarkupType = $Request->getParam('SettingMarkupType');	
				$arrUpdate = array("SettingLanguage"=>$SettingLanguage,"SettingCurrency"=>$SettingCurrency,"SettingMarkupType"=>$SettingMarkupType,"SettingMarkup"=>$SettingMarkup);
				$db->update("Settings", $arrUpdate);
				$this->appMessage->setNotice(1, $this->translate->_("Your settings has been saved"));
				$this->_redirect('/admin/system/settings/');   				
			}


				
			$systemSetting = new Zend_Session_Namespace('systemSetting');	
			
			$this->view->optionLanguage = "";			
			foreach ($systemSetting->arrLanguages as $index => $language)
			{
				if ($systemSetting->language == $index)
					$this->view->optionLanguage .= "<option value='".$index."' selected>".$language[0]."</option>";
				else
					$this->view->optionLanguage .= "<option value='".$index."'>".$language[0]."</option>";
			}			
			
			$this->view->optionCurrency = $libDb->getSystemOptions("arrCurrency", $systemSetting->currency); 
			$this->view->optionMarkupType = $libDb->getSystemOptions("arrMarkupType", $systemSetting->markup_type); 
			
			$this->view->markup = $systemSetting->markup;

			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
	
	private function make_list($arr)
	{
		$return = '<ul>';
		foreach ($arr as $index => $item)
		{
			$strTotal = "";
			if ($item['TotalItems'])
				$strTotal = " (".$item['TotalItems'].")";
			$return .= '<li id="'.$item['ID'].'">'.$item['Name'] . $strTotal;
			if (is_array($item['childs'])) 
			{ 
				$return .= $this->make_list($item['childs']);
			}
			$return .= '</li>';
		}
		$return .= '</ul>';
		return $return;
	}
	
	private function buildTree($items, $childname = 'childs') {

		$childs = array();

		foreach($items as &$item) $childs[$item['ParentID'] ? $item['ParentID']  : 0][] = &$item;
		unset($item);

		foreach($items as &$item) if (isset($childs[$item['ID']]))
				$item[$childname] = $childs[$item['ID']];

		return $childs[0];
	}

	public function ajaxjsoncategoryAction()
	{
		try {
			$Request = $this->getRequest();	
			$db = Zend_Db_Table::getDefaultAdapter(); 			
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
			$arrCatAll = $db->fetchAll("SELECT Categories.ID,Categories.Name as text, Categories.ParentID, ItemCat.TotalItems FROM Categories LEFT JOIN (SELECT count(*) as TotalItems, Categories.ID as CatID FROM Item, Categories where CategoryID=Categories.ID Group by Item.CategoryID) as ItemCat ".
			" ON (ItemCat.CatID=Categories.ID)");
			
			$arrCategories = $this->buildTree($arrCatAll, 'children');
			echo json_encode($arrCategories);
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}	
		exit();
			
	}

	public function ajaxcategoryAction()
	{
		try {
			$Request = $this->getRequest();	
			$db = Zend_Db_Table::getDefaultAdapter(); 			
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
			if ($Request->getParam('newcat')){
				$parentID = $Request->getParam('parentID') ? $Request->getParam('parentID') : new Zend_Db_Expr("NULL");
				$arrInsert = array("Name"=>"New Category", "ParentID"=>$parentID);
				$db->Insert("Categories", $arrInsert);
				echo $db->lastInsertId();
			}
			
			if ($Request->getParam('editcat')){
				$newtext = $Request->getParam('newtext') ? $Request->getParam('newtext') : "New Category";
				$arrUpdate = array("Name"=>$newtext);
				$db->Update("Categories", $arrUpdate, "ID=".$Request->getParam('editcat'));
			}
			
			if ($Request->getParam('deletecat')){
				
				$db->query("DELETE FROM Categories where ID=".$Request->getParam('deletecat'));
			}			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}	
		exit();
			
	}
	
	public function ajaxcategoryitemsAction()
	{
		try {
			
			
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			$categoryID = $Request->getParam('selectedcat');
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'Item.ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 100 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$strScript = <<<END
			<script language='Javascript'>
				$('#Move').click(function () {
					//alert($('#NewCategoryID').val());
					var t = $('#NewCategoryID').combotree('tree');	// get the tree object
					var n = t.tree('getSelected');		// get selected node
					$('#NewCatID').val(n.ID);
					
					
					$('#iditemforms').submit();
						
				})
							
				$('#checkmove').click(function () {
					
				});
								
				$('#checkall').click(function () {
					if ($('#checkall').is(':checked')){
						$( "[id='checkmove']" ).each(function( index, element  ) {
							$(element).prop('checked',true);

						});
					}else
					{
						$( "[id='checkmove']" ).each(function( index, element  ) {
							$(element).prop('checked',false);

						});

					}
				});
			</script>
			
END;
			
			
			
			$sqlSearch .= $categoryID ? " and Item.CategoryID = ".$categoryID : "";

			$libInv->setFetchMode(Zend_Db::FETCH_NUM);

			$arrItem = $libInv->getItem($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataItem = $arrItem[1];
			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return "<a href='/inventory/brand/item/edit_item/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteItem(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}		
			
			$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
			$sessionItemCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
				$sessionItemCounter->numCounter++;
				return $sessionItemCounter->numCounter;
			}		

			function format_image($colnum, $rowdata)
			{
				if ($rowdata[6])
					return "<img src='".$rowdata[6]."' style='height:auto; width:auto; max-height:150px; padding-bottom:0px;'>";
				else
					return "";
			}		

			function format_retail($colnum, $rowdata)
			{
				if ($rowdata[7])
					return "RM ".$rowdata[7];
				else
					return "";
			}
			
			function format_stocknum($colnum, $rowdata)
			{
					return $rowdata[8];
			}

			
			function format_checkbox($colnum, $rowdata)
			{
				return '<input type=checkbox name="checkmove['.$rowdata[0].']" id="checkmove">';
			}
			
			$strHiddenSearch  = "<input style='display:none' type=submit name='submit_move' id='submit_move' value='Submit'>";
			$strHiddenSearch  .= "<input type=hidden name='NewCatID' id='NewCatID' value=''>";
			
			$arrHeader = array ('#', '<input type=checkbox name="checkall" id="checkall">', '', $this->translate->_('Brand'), $this->translate->_('Item Name'), $this->translate->_('Model Name'),$this->translate->_('Part Number'),$this->translate->_('Retail Price'),$this->translate->_('In Stock'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem, 
					 'formname'      => 'itemforms',
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','{format_checkbox}','{format_image}','%1%', '%2%', '%3%', '%4%', '{format_retail}','{format_stocknum}'),					 
					 'sort_column' 	=> array('','','','BrandName','ItemName','ModelNumber', 'PartNumber', 'RetailPrice','NumStock'),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Items'),					 
					 'aligndata' 	=> 'CCCLLLLRCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "950px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			
			echo $displayTable->render();			
			echo $strScript;
			
			
			
			
			
		}catch (Exception $e) {
			echo $e->getMessage();
		}	
		exit();
	}
	
	public function categoriesAction()   
	{
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$arrCatAll = $db->fetchAll("SELECT Categories.*, ItemCat.TotalItems FROM Categories LEFT JOIN (SELECT count(*) as TotalItems, Categories.ID as CatID FROM Item, Categories where CategoryID=Categories.ID Group by Item.CategoryID) as ItemCat ".
			" ON (ItemCat.CatID=Categories.ID)");
			
			$arrCategories = $this->buildTree($arrCatAll);
		
			$this->view->categoryList = $this->make_list($arrCategories);
			//print $this->view->categoryList;
			$NewCatID = $Request->getParam('NewCatID');
			$checkmove = $Request->getParam('checkmove');
			if ($NewCatID && $checkmove)
			{
				$arrUpdate = array("CategoryID" => $NewCatID);
				foreach ($checkmove as $id => $val)
				{
					$db->Update("Item", $arrUpdate, "ID=".$id);
				}
				$this->appMessage->setNotice(1, $this->translate->_("Selected items has been moved to a new category.").".");
				
				$this->_redirect('/admin/system/categories/');  
			}
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
	
	public function ajaxgetcurrencyAction()
	{
		
		//http://download.finance.yahoo.com/d/quotes.csv?s=EURMYR=X&f=sl1d1t1ba&e=.csv
		try {
			$Request = $this->getRequest();	
			$db = Zend_Db_Table::getDefaultAdapter(); 			
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
						$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];			
			$this->view->currencyTypeID = $systemSetting->currency;	
			
			$TargetCurrency = $Request->getParam('TargetCurrency');
			//print $TargetCurrency;
			
			$url = "http://download.finance.yahoo.com/d/quotes.csv?s=".$TargetCurrency.$systemSetting->currency."=X&f=sl1d1t1ba&e=.csv";
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);								
			$rawdata=curl_exec ($ch);
			curl_close ($ch);
			$arrExchange = explode(",", $rawdata);
			print $arrExchange[1];
			
			/*
			if ($Request->getParam('TargetCurrency')){
				$parentID = $Request->getParam('parentID') ? $Request->getParam('parentID') : new Zend_Db_Expr("NULL");
				$arrInsert = array("Name"=>"New Category", "ParentID"=>$parentID);
				$db->Insert("Categories", $arrInsert);
				echo $db->lastInsertId();
			}
			
			if ($Request->getParam('editcat')){
				$newtext = $Request->getParam('newtext') ? $Request->getParam('newtext') : "New Category";
				$arrUpdate = array("Name"=>$newtext);
				$db->Update("Categories", $arrUpdate, "ID=".$Request->getParam('editcat'));
			}
			
			if ($Request->getParam('deletecat')){
				
				$db->query("DELETE FROM Categories where ID=".$Request->getParam('deletecat'));
			}	
			*/			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}	
		exit();
			
		
		
		
	}
	
	
	
	public function logAction()   
	{
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysHelper = new Venz_App_System_Helper();
		$libDb = new Venz_App_Db_Table();
		

		
		/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
		
		
		$sortby = $Request->getParam('sortby');			
		if (strlen($sortby) == 0) $sortby = 'SYSLog.logtime';
			
		$ascdesc = $Request->getParam('ascdesc');			
		if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
		
		$showPage = $Request->getParam('Pagerpagenum');			
		if (!$showPage) $showPage = 1; 
			
		$pagerNext = $Request->getParam('Pager_next_page');			
		if (strlen($pagerNext) > 0) $showPage++; 	

		$pagerPrev = $Request->getParam('Pager_prev_page');			
		if (strlen($pagerPrev) > 0) $showPage--; 	
		
		$recordsPerPage = 30 ;
		////////////////////////////////////////////////////////////////////////////////////////

		$sqlSearch = "";
		$search_log = $Request->getParam('search_log');	
		$strHiddenSearch = "";
		if ($search_log)
		{
			$Name = $Request->getParam('Name');	
			$sqlSearch .= $Name ? " and ACLUsers.Name LIKE '%".$Name."%'" : "";
			
			$Username = $Request->getParam('Username');	
			$sqlSearch .= $Username ? " and SYSLog.username LIKE '%".$Username."%'" : "";
			
			$Role = $Request->getParam('Role');	
			$sqlSearch .= $Role ? " and SYSLog.role LIKE '%".$Role."%'" : "";
			
			$Module = $Request->getParam('Module');	
			$sqlSearch .= $Module ? " and SYSLog.zendmodule LIKE '%".$Module."%'" : "";

			$Controller = $Request->getParam('Controller');	
			$sqlSearch .= $Controller ? " and SYSLog.zendcontroller LIKE '%".$Controller."%'" : "";
			
			$Action = $Request->getParam('Action');	
			$sqlSearch .= $Action ? " and SYSLog.zendaction LIKE '%".$Action."%'" : "";
			
		
			$GetData = $Request->getParam('GetData');	
			$sqlSearch .= $GetData ? " and SYSLog.getdata LIKE '%".$GetData."%'" : "";

			$PostData = $Request->getParam('PostData');	
			$sqlSearch .= $PostData ? " and SYSLog.postdata LIKE '%".$PostData."%'" : "";

			$IP = $Request->getParam('IP');	
			$sqlSearch .= $IP ? " and SYSLog.IP LIKE '%".$IP."%'" : "";

			$this->view->FromDateTime = $Request->getParam('FromDateTime');
			$FromDateTime = $this->view->FromDateTime;
			if ($FromDateTime){
				$FromDateTime = substr($FromDateTime, 6, 4)."-".substr($FromDateTime, 3, 2)."-".substr($FromDateTime, 0, 2);	
				$sqlSearch .= " and SYSLog.logtime >= '".$FromDateTime."'";	
			}
			
			$this->view->ToDateTime = $Request->getParam('ToDateTime');
			$ToDateTime = $this->view->ToDateTime;
			if ($ToDateTime){
				$ToDateTime = substr($ToDateTime, 6, 4)."-".substr($ToDateTime, 3, 2)."-".substr($ToDateTime, 0, 2);	
				$sqlSearch .= " and SYSLog.logtime <= '".$ToDateTime." 23:59:59'"; 	
			}				
			$this->view->Name = $Name ? $Name : "";				
			$this->view->Username = $Username ? $Username : "";				
			$this->view->Role = $Role ? $Role : "";				
			$this->view->Module = $Module ? $Module : "";				
			$this->view->Controller = $Controller ? $Controller : "";				
			$this->view->Action = $Action ? $Action : "";				
			$this->view->GetData = $GetData ? $GetData : "";				
			$this->view->PostData = $PostData ? $PostData : "";	
			$this->view->IP = $IP ? $IP : "";	
			
			
			$strHiddenSearch = "<input type=hidden name='search_log' value='true'>";
			$strHiddenSearch .= "<input type=hidden name='Username' value='".$Username."'>";
			$strHiddenSearch .= "<input type=hidden name='Role' value='".$Role."'>";
			$strHiddenSearch .= "<input type=hidden name='Module' value='".$Module."'>";
			$strHiddenSearch .= "<input type=hidden name='Controller' value='".$Controller."'>";
			$strHiddenSearch .= "<input type=hidden name='Action' value='".$Action."'>";
			$strHiddenSearch .= "<input type=hidden name='GetData' value='".$GetData."'>";
			$strHiddenSearch .= "<input type=hidden name='PostData' value='".$PostData."'>";
			$strHiddenSearch .= "<input type=hidden name='IP' value='".$IP."'>";
			$strHiddenSearch .= "<input type=hidden name='FromDateTime' value='".$this->view->FromDateTime."'>";
			$strHiddenSearch .= "<input type=hidden name='ToDateTime' value='".$this->view->ToDateTime."'>";

			}
		
		$this->view->chkActive = "";
		$this->view->chkNotActive = "";
		if (!$this->view->Active && !is_null($this->view->Active))
		{
			$this->view->chkActive = "";
			$this->view->chkNotActive = "checked";
		}else
		{
			$this->view->chkActive = "checked";
			$this->view->chkNotActive = "";
		
		}
		$sysHelper->setFetchMode(Zend_Db::FETCH_NUM);

		$arrDesignationLevel = $sysHelper->getLog($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
		
		$dataDesignationLevel = $arrDesignationLevel[1];
		$exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));	
		
		
		$sessionDesignationLevel = new Zend_Session_Namespace('sessionDesignationLevel');
		$sessionDesignationLevel->numCounter = $recordsPerPage * ($showPage-1);
		function format_counter($colnum, $rowdata)
		{
			$sessionDesignationLevel = new Zend_Session_Namespace('sessionDesignationLevel');
			$sessionDesignationLevel->numCounter++;
			return $sessionDesignationLevel->numCounter;
		}
		function format_postdata($colnum, $rowdata)
		{
			return ($rowdata[7] ? "<a href='/admin/system/ajaxgetlog/ID/".$rowdata[9]."?width=450' class='jTip' id='".$rowdata[9]."' name='Post Data'><img src='/images/icons/IconApproved.gif'></a>" : "");
		}					
		$arrHeader = array ('', $this->translate->_('IP'), $this->translate->_('Name'), $this->translate->_('Username'), $this->translate->_('Role'), $this->translate->_('Log Time')
		, $this->translate->_('Module'), $this->translate->_('Controller'), $this->translate->_('Action'), $this->translate->_('Post Data'), $this->translate->_('Get Data'));
		$displayTable = new Venz_App_Display_Table(
			array (
				 'data' => $dataDesignationLevel,
				 'headings' => $arrHeader,
				 'format' 		=> array('{format_counter}','%10%','%0%','%1%','%2%','%3%','%4%','%5%','%6%','{format_postdata}','%8%'),					 
				 'sort_column' 	=> array('', 'SYSLog.IP','ACLUsers.Name','SYSLog.username','SYSLog.role','SYSLog.logtime','SYSLog.zendmodule', 'SYSLog.zendcontroller', 'SYSLog.zendaction', 'SYSLog.postdata', 'SYSLog.getdata', 'SYSLog.IP'),
				 'alllen' 		=> $arrDesignationLevel[0],
				 'title'		=> $this->translate->_('Activity Log'),					 
				 'aligndata' 	=> 'LLLLLLLLLCC',
				 'pagelen' 		=> $recordsPerPage,
				 'numcols' 		=> sizeof($arrHeader),
				 'tablewidth' => "1000px",
				 'sortby' => $sortby,
				 'ascdesc' => $ascdesc,
				 'hiddenparam' => $strHiddenSearch,
			)
		);
		$this->view->content_log = $displayTable->render();

	
	}


	
	public function ajaxgetlogAction()   
	{

		$db = Zend_Db_Table::getDefaultAdapter(); 
		$Request = $this->getRequest();	
		$ID = $Request->getParam('ID'); 
		$arrLog = $db->fetchRow("SELECT * FROM SYSLog WHERE ID=".$ID);
		
		print $arrLog['postdata']; exit();
	/*	
		$arrPostData = explode("|", $arrLog['postdata']);
		print "<table border=0>";
		foreach ($arrPostData as $key => $val)
		{
			$arrParamData = explode("=", $val);
			if ($arrParamData[1])
				print "<TR><TD>".$arrParamData[0] . "</TD><TD>=</TD><TD>" . $arrParamData[1]."</TD></TR>";
		}
		print "</table>";
		exit();
	*/
	}	
	
	
	

}

