<?php

class Inventory_BrandController extends Venz_Zend_Controller_Action
{

    public function init()
    {
	
		$actionName = $this->getRequest()->getActionName();
		switch ($actionName){
		case "index" : parent::init("inventory_brands");break;
		case "item" : parent::init("inventory_item");break;
		case "itemseries" : parent::init("inventory_itemseries");break;
		default: parent::init(NULL);
		}
			
    }
	
	public function ajaxgetretailAction()
	{
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$ItemSeriesID = $Request->getParam('ID');
		$arrItemSeries = $db->fetchRow("SELECT * FROM ItemSeries where ID=".$ItemSeriesID);
		$dispFormat = new Venz_App_Display_Format();
		print $dispFormat->format_currency($arrItemSeries['UnitRetail']);
		exit();	
	}
	
	private function updateRetail($ItemSeriesID)
	{
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$arrItemSeries = $db->fetchRow("SELECT * FROM ItemSeries where ID=".$ItemSeriesID);
		$systemSetting = new Zend_Session_Namespace('systemSetting');	
		$retailPrice = "";
		
		if ($arrItemSeries['MarkupPercent']){
			if ($systemSetting->markup_type == "MARKUP") { 
				$retailPrice = (($arrItemSeries['MarkupPercent'] / 100) * $arrItemSeries['UnitLandedCost']) + $arrItemSeries['UnitLandedCost'];
			} else if ($systemSetting->markup_type == "GROSS_MARGIN") { 
				$retailPrice = $arrItemSeries['UnitLandedCost'] / ((100-$arrItemSeries['MarkupPercent']) / 100);
			} else
			{
				$retailPrice = $arrItemSeries['UnitLandedCost'] ? $arrItemSeries['UnitLandedCost'] : $arrItemSeries['UnitPriceRM'];
			}
		}else
			$retailPrice = $arrItemSeries['UnitLandedCost'] ? $arrItemSeries['UnitLandedCost'] : $arrItemSeries['UnitPriceRM'];
			
		$arrUpdate = array("UnitRetail"=>$retailPrice);
		$db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);		
	}
	
	public function ajaxmarkupAction()
    {	
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$val = $Request->getParam('value');	
		$valNum = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
		$ItemSeriesID = $Request->getParam('id');
		$arrUpdate = array("MarkupPercent"=>$valNum);
		$db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);		
		$this->updateRetail($ItemSeriesID);
		echo number_format($valNum, 2)."%";
		exit();
	}
	
	public function ajaxlandedcostAction()
    {	
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$val = $Request->getParam('value');	
		$valNum = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
		$ItemSeriesID = $Request->getParam('id');
		$arrUpdate = array("UnitLandedCost"=>$valNum);
		$db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);		
		$this->updateRetail($ItemSeriesID);
		$dispFormat = new Venz_App_Display_Format();
		echo $dispFormat->format_currency($valNum);
		
		exit();
	}
	
	public function ajaxunitpriceAction()
    {	
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$val = $Request->getParam('value');	
		$valNum = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
		$ItemSeriesID = $Request->getParam('id');
		$arrUpdate = array("UnitPriceRM"=>$valNum);
		$db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);		
		$this->updateRetail($ItemSeriesID);
		$dispFormat = new Venz_App_Display_Format();
		echo $dispFormat->format_currency($valNum);
		exit();
	}


	public function ajaxUpdateStatusMultipleAction()
    {
        $layout = $this->_helper->layout();
        $layout->setLayout("ajax");

        $Request = $this->getRequest();
        $db = Zend_Db_Table::getDefaultAdapter();
        $libDb = new Venz_App_Db_Table();
        $dispFormat = new Venz_App_Display_Format();
        $libInv = new Venz_App_Inventory_Helper();

        $this->view->optionBranchesTransit = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->TransitTo);
        $this->view->optionPersonInCharge = $libDb->getTableOptions("ACLUsers", "Name", "ID");
        $this->view->optionStatus = $libDb->getSystemOptions("arrStockStatus");

        $UpdateUpdateStatus = $Request->getParam('UpdateUpdateStatus');
        if ($UpdateUpdateStatus)
        {

            $strItemSeriesID = $Request->getParam('ItemSeriesID');
            $arrItemSeriesID = explode(",", $strItemSeriesID);

            $strItemID = $Request->getParam('UpdateItemID');
            $arrItemIDTemp = explode(",", $strItemID);
            $arrItemID = array();
            foreach ($arrItemIDTemp as $ItemID){
                $arrItemID[$ItemID] = 1;
            }



            $ReferenceNo = $Request->getParam('UpdateReferenceNo') ? $Request->getParam('UpdateReferenceNo') : new Zend_Db_Expr("NULL");
            $StatusDate = $Request->getParam('UpdateStatusDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('UpdateStatusDate')) : new Zend_Db_Expr("NULL");
            $Status = $Request->getParam('UpdateItemSeriesStatus') ? $Request->getParam('UpdateItemSeriesStatus') : new Zend_Db_Expr("NULL");
            $TransitTo = $Request->getParam('TransitTo') ? $Request->getParam('TransitTo') : new Zend_Db_Expr("NULL");
            $UserIDResp = $Request->getParam('UpdateUserIDResp') ? $Request->getParam('UpdateUserIDResp') : new Zend_Db_Expr("NULL");
            $Notes = $Request->getParam('UpdateNotes') ? $Request->getParam('UpdateNotes') : new Zend_Db_Expr("NULL");

            foreach ($arrItemSeriesID as $ItemSeriesID)
            {
                if ($ItemSeriesID)
                {
                    $arrItemSeriesStatus = $db->fetchRow("SELECT * FROM ItemSeriesStatus WHERE ItemSeriesID=".$ItemSeriesID." order by StatusDate desc, ID desc limit 1");

                    $arrInsert = array("ItemSeriesID"=>$ItemSeriesID,"StatusDate"=>$StatusDate,"Status"=>$Status,"UserIDEntry"=>$this->userInfo->ID,"EntryDateTime"=>new Zend_Db_Expr("now()"),
                        "UserIDResp"=>$UserIDResp,"Notes"=>$Notes,"ReferenceNo"=>$ReferenceNo, "TransitTo"=>$TransitTo
                    );
                    $db->insert("ItemSeriesStatus", $arrInsert);


                    $arrUpdate = array("Status"=>$Status);
                     if ($arrItemSeriesStatus['Status'] == 'intransit' && $Status == 'in'){
                         $arrUpdate['BranchID'] = $arrItemSeriesStatus['TransitTo'];
                     }

                    $db->Update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);

                }

            }

            foreach ($arrItemID as $ItemID => $value){
                if ($ItemID){

                    $arrItem = $libInv->getItemDetail($ItemID);
                    $NumStock = $arrItem['NumStock'];
                    $MinStock = $arrItem['MinStock'];
                    $BrandID = $arrItem['BrandID'];
                    if (($NumStock <= $MinStock) && $BrandID && $MinStock)
                    {
                        $arrBrand = $db->fetchRow("SELECT * FROM Brand WHERE ID=".$BrandID);
                        $Content =<<<END
					The item below had reached the minimal stock level. <BR>
					<table>
					<TR><TD>Brand:</TD><TD>$arrBrand[FullName]</TD></TR>
					<TR><TD>Item Name:</TD><TD>$arrItem[ItemName]</TD></TR>
					<TR><TD>Model Name:</TD><TD>$arrItem[ModelNumber]</TD></TR>
					<TR><TD>Part Number:</TD><TD>$arrItem[PartNumber]</TD></TR>
					<TR><TD>Current Stock:</TD><TD>$NumStock</TD></TR>
					<TR><TD>Minimum Stock Alert:</TD><TD>$MinStock</TD></TR>
					</table>
	
END;

                        $this->appMessage->setNotice(1, "Stock alert trigger has been sent through email.");
                        $sysNotification->setNotificationEmail("Inventory: Minimum Stock Alert", $Content, "ACLRole='Sales' OR ACLRole='AdminSystem' OR ACLRole='Admin'");

                    }
                }

            }


            print "1"; exit();
        }

    }

    public function indexAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ID';
				
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
			$search_brand = $Request->getParam('search_brand');	
			$this->view->searchBrand = false;
			$strHiddenSearch = "";
			if ($search_brand)
			{
				$this->view->searchUsers = true;
				$FullName = $Request->getParam('FullName');	
				$ShortName = $Request->getParam('ShortName');	
				$CompanyName = $Request->getParam('CompanyName');	

				$sqlSearch .= $FullName ? " and Brand.FullName LIKE '%".$FullName."%'" : "";
				$sqlSearch .= $ShortName ? " and Brand.ShortName LIKE '%".$ShortName."%'" : "";
				$sqlSearch .= $CompanyName ? " and Brand.CompanyName LIKE '%".$CompanyName."%'" : "";
				
				//print $sqlSearch; exit();
				$this->view->FullName = $FullName ? $FullName : "";				
				$this->view->ShortName = $ShortName ? $ShortName : "";				
				$this->view->CompanyName = $CompanyName ? $CompanyName : "";				
				
				$strHiddenSearch = "<input type=hidden name='search_brand' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='FullName' value='".$FullName."'>";
				$strHiddenSearch .= "<input type=hidden name='ShortName' value='".$ShortName."'>";
				$strHiddenSearch .= "<input type=hidden name='CompanyName' value='".$CompanyName."'>";

			}

			
			$add_brand = $Request->getParam('add_brand');	
			if ($add_brand)
			{
			
				$FullName = $Request->getParam('FullName') ? trim($Request->getParam('FullName')) : new Zend_Db_Expr("NULL");
				$ShortName = $Request->getParam('ShortName') ? trim($Request->getParam('ShortName')) : new Zend_Db_Expr("NULL");
				$CompanyName = $Request->getParam('CompanyName') ? trim($Request->getParam('CompanyName')) : new Zend_Db_Expr("NULL");
				$errorFile = false;
				if (!$_FILES['BrandLogo']['error'])
				{

					if ($_FILES['BrandLogo']['type'] != "image/jpeg")
					{
						$this->appMessage->setMsg(0, "Please select a jpeg format file to be uploaded.");
						$errorFile = true;
						
					}
					
					if ($_FILES['BrandLogo']['size'] > (3 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, "Please select a image that is less than 1MB in size.");
						$errorFile = true;
						
					}
					
				}
		
				if (!$errorFile){
					$arrInsert = array("FullName"=>$FullName,"ShortName"=>$ShortName, "CompanyName"=>$CompanyName);

					$db->insert("Brand", $arrInsert);
					$brandID = $db->lastInsertId();
					$filename = $brandID.".jpg";
					$relativePath = "/uploads/BrandLogo/".$filename;
					$relativePathSmall = "/uploads/BrandLogo/small/".$filename;
					if (!$_FILES['BrandLogo']['error'])
					{
						move_uploaded_file($_FILES['BrandLogo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePath);
						$imgResize = new Venz_App_Image_Resize($_SERVER['DOCUMENT_ROOT'].$relativePath);
						// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
						$imgResize -> resizeImage(80, 80, 'auto');
						// *** 3) Save image
						$imgResize -> saveImage($_SERVER['DOCUMENT_ROOT'].$relativePathSmall, 100);
						$arrUpdate = array("BrandLogoPath"=>$relativePath, "BrandLogoPathSmall"=>$relativePathSmall);
						$db->update("Brand", $arrUpdate, "ID=".$brandID);
					
					}
					
					$this->appMessage->setNotice(1, "New brand \"<B>".$FullName."</B>\" has been created.");
					$this->_redirect('/inventory/brand'); 
				}
				
			}
			
			$this->view->edit_brand = '';
			$edit_brand = $Request->getParam('edit_brand');	
			if ($edit_brand)
			{
				$this->view->edit_brand = $edit_brand;
				$arrBrandDetail = $libInv->getBrandDetail($edit_brand);
				
				$this->view->FullName = $arrBrandDetail['FullName'];	
				$this->view->ShortName = $arrBrandDetail['ShortName'];	
				$this->view->CompanyName = $arrBrandDetail['CompanyName'];	
				$this->view->BrandLogoPath = $arrBrandDetail['BrandLogoPath'];	
			}					
		
		
		
		
			$save_brand = $Request->getParam('save_brand');	
			if ($save_brand)
			{
				$ID = $Request->getParam('save_brand_id') ? $Request->getParam('save_brand_id') : new Zend_Db_Expr("NULL");
				
				$FullName = $Request->getParam('FullName') ? $Request->getParam('FullName') : new Zend_Db_Expr("NULL");
				$ShortName = $Request->getParam('ShortName') ? $Request->getParam('ShortName') : new Zend_Db_Expr("NULL");
				$CompanyName = $Request->getParam('CompanyName') ? $Request->getParam('CompanyName') : new Zend_Db_Expr("NULL");

				$this->view->FullName = $FullName;	
				$this->view->ShortName = $ShortName;	
				$this->view->CompanyName = $CompanyName;				
				
				$errorFile = false;
				if (!$_FILES['BrandLogo']['error'])
				{

					if ($_FILES['BrandLogo']['type'] != "image/jpeg")
					{
						$this->appMessage->setMsg(0, "Please select a jpeg format file to be uploaded.");
						$errorFile = true;
						
					}
					
					if ($_FILES['BrandLogo']['size'] > (1 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, "Please select a image that is less than 1MB in size.");
						$errorFile = true;
						
					}
					
				}
		
				if (!$errorFile){

					$arrUpdate = array("FullName"=>$FullName,"ShortName"=>$ShortName,"CompanyName"=>$CompanyName);
		
					$db->update("Brand", $arrUpdate, "ID=".$ID);
					
					$filename = $ID.".jpg";
					$relativePath = "/uploads/BrandLogo/".$filename;
					$relativePathSmall = "/uploads/BrandLogo/small/".$filename;
					if (!$_FILES['BrandLogo']['error'])
					{
						move_uploaded_file($_FILES['BrandLogo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePath);
						$imgResize = new Venz_App_Image_Resize($_SERVER['DOCUMENT_ROOT'].$relativePath);
						// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
						$imgResize -> resizeImage(80, 80, 'auto');
						// *** 3) Save image
						$imgResize -> saveImage($_SERVER['DOCUMENT_ROOT'].$relativePathSmall, 100);
						
						
						
						$arrUpdate = array("BrandLogoPath"=>$relativePath, "BrandLogoPathSmall"=>$relativePathSmall);
						$db->update("Brand", $arrUpdate, "ID=".$ID);
					}

					$this->appMessage->setNotice(1, "Details for <B>".$FullName."</B> has been updated.");
					$this->_redirect('/inventory/brand'); 
				}
			}


			$remove_brand = $Request->getParam('remove_brand');	
			if ($remove_brand)
			{
				$arrBrandDetail = $libInv->getBrandDetail($remove_brand);
			
				$db->delete("Brand", "ID=".$remove_brand);
				$this->appMessage->setNotice(1, "The brand has been removed.");
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrBrandDetail['BrandLogoPath']);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrBrandDetail['BrandLogoPathSmall']);
				$this->_redirect('/inventory/brand/');   				
			}			
		
			$remove_logo = $Request->getParam('remove_logo');	
			if ($remove_logo)
			{
				$ID = $Request->getParam('save_brand_id') ? $Request->getParam('save_brand_id') : new Zend_Db_Expr("NULL");
				$arrBrandDetail = $libInv->getBrandDetail($ID);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrBrandDetail['BrandLogoPath']);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrBrandDetail['BrandLogoPathSmall']);
				$arrUpdate = array("BrandLogoPath"=>new Zend_Db_Expr("NULL"), "BrandLogoPathSmall"=>new Zend_Db_Expr("NULL"));
				$db->update("Brand", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, "The brand logo has been removed.");
				$this->_redirect('/inventory/brand/index/edit_brand/'.$ID);   				
			}			
			
		

			$libInv->setFetchMode(Zend_Db::FETCH_NUM);

			$arrBrand = $libInv->getBrand($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataBrand = $arrBrand[1];
			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return "<a href='/inventory/brand/index/edit_brand/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a> | <a href='javascript:void(0);' onclick='OnDeleteBrand(".$rowdata[0].")'><img border=0 src='/images/icons/IconDelete.gif'></a>";
			}		
			
			$sessionBrandCounter = new Zend_Session_Namespace('sessionBrandCounter');
			$sessionBrandCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionBrandCounter = new Zend_Session_Namespace('sessionBrandCounter');
				$sessionBrandCounter->numCounter++;
				return $sessionBrandCounter->numCounter;
			}		

			function format_logo($colnum, $rowdata)
			{
				if ($rowdata[5])
					return "<img src='".$rowdata[5]."'>";
				else
					return "";
			}		
			
			function format_item($colnum, $rowdata)
			{
				return "<a href='/inventory/brand/item/search_item/1/BrandID/".$rowdata[0]."'>". ($rowdata[6] ? $rowdata[6]  : "0")."</a>";
			}	
			
			$arrHeader = array ('#', 'ID', '', $this->translate->_('Full Name'), $this->translate->_('Short Name'),$this->translate->_('Company Name'),$this->translate->_('Items'),$this->translate->_('Edit | Delete'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataBrand,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','{format_logo}','%1%', '%2%', '%3%', '{format_item}', '{format_action}'),					 
					 'sort_column' 	=> array('','ID','','FullName','ShortName', 'CompanyName','', ''),
					 'alllen' 		=> $arrBrand[0],
					 'title'		=> $this->translate->_('Brands'),					 
					 'aligndata' 	=> 'LCCLLLCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "850px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_brand = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }

	
	public function getitemAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			$libInv = new Venz_App_Inventory_Helper();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}	
			$arrLastItem = $db->fetchRow("SELECT ID From Item order by ID desc");
			
			$optionItems = "<option value=''>-</option>";
			$optionItems = $libInv->getItemOptions($arrLastItem['ID']);
			//$optionItems .= $libDb->getTableOptions("Branches", "Name", "ID"); 
			$optionItems .= "<option value='add-new'><<< ".$this->translate->_('Add New')." >>></option>";
	
			echo $optionItems;
			exit();
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
	
	public function getitemexAction()   
	{

		try {
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
		
		
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libDb = new Venz_App_Db_Table();
			
			$libInv = new Venz_App_Inventory_Helper();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}	
			$arrLastItem = $db->fetchRow("SELECT ID From Item order by ID desc");
			$optionItems = "<option value=''>-</option>";
			$optionItems .= $libInv->getItemOptionsEx($arrLastItem['ID']);
			//$optionItems .= $libDb->getTableOptions("Branches", "Name", "ID"); 
			$optionItems .= "<option value='add-new'><<< ".$this->translate->_('Add New')." >>></option>";
	
			echo $optionItems;
			exit();
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
    public function additemAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}			
			
						
			$add_item = $Request->getParam('add_item');	
			if ($add_item)
			{
				//	print "THIS IS NEW ADD ITEM";	
	
				$BrandID = $Request->getParam('BrandID') ? $Request->getParam('BrandID') : new Zend_Db_Expr("NULL");
				$ItemName = $Request->getParam('ItemName') ? $Request->getParam('ItemName') : new Zend_Db_Expr("NULL");
				$ModelNumber = $Request->getParam('ModelNumber') ? $Request->getParam('ModelNumber') : new Zend_Db_Expr("NULL");
				$PartNumber = $Request->getParam('PartNumber') ? $Request->getParam('PartNumber') : new Zend_Db_Expr("NULL");
				$RetailPrice = $Request->getParam('RetailPrice') ? $Request->getParam('RetailPrice') : new Zend_Db_Expr("NULL");
				$arrInsert = array("BrandID"=>$BrandID,"ItemName"=>$ItemName,"ModelNumber"=>$ModelNumber, "PartNumber"=>$PartNumber, "RetailPrice"=>$RetailPrice);

				$db->insert("Item", $arrInsert);

				echo $ItemName;
				exit();

			}
			
			$this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID); 
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }	
	

    public function itemAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$sysNotification = new Venz_App_System_Notification();
			$libDb = new Venz_App_Db_Table();
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
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
			
			$recordsPerPage = 30 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$search_item = $Request->getParam('search_item');
			$clear_search = $Request->getParam('clear_search');	

			$add_item = $Request->getParam('add_item');
			$edit_item = $Request->getParam('edit_item');
			$save_item = $Request->getParam('save_item');
			$remove_item = $Request->getParam('remove_item');
			$remove_image = $Request->getParam('remove_image');				
						
			$this->view->searchItem = false;
			$strHiddenSearch = "";
			if ($search_item)
			{
				$this->view->searchItem = true;
				$BrandID = $Request->getParam('BrandID');	
				$ItemName = $Request->getParam('ItemName');	
				$ModelNumber = $Request->getParam('ModelNumber');	
				$PartNumber = $Request->getParam('PartNumber');	
				$RetailPrice = $Request->getParam('RetailPrice');	
		
				setcookie('ItemBrandID', $BrandID, time() + (3600*30),"/"); 
				setcookie('ItemItemName', $ItemName, time() + (3600*30),"/"); 
				setcookie('ItemModelNumber', $ModelNumber, time() + (3600*30),"/"); 
				setcookie('ItemPartNumber', $PartNumber, time() + (3600*30),"/"); 
				setcookie('ItemRetailPrice', $RetailPrice, time() + (3600*30),"/"); 
			}else if (!$add_item && !$edit_item && !$save_item && !$remove_item || !$remove_image)
			{
				if ($clear_search)
				{
					setcookie('ItemBrandID',"", time()-3600, "/"); unset($_COOKIE['ItemBrandID']);
					setcookie('ItemItemName', "", time()-3600, "/");unset($_COOKIE['ItemItemName']); 
					setcookie('ItemModelNumber', "", time()-3600, "/"); unset($_COOKIE['ItemModelNumber']);
					setcookie('ItemPartNumber', "", time()-3600, "/"); unset($_COOKIE['ItemPartNumber']);
					setcookie('ItemRetailPrice', "", time()-3600, "/"); 	unset($_COOKIE['ItemRetailPrice']);	
				
				}
				else
				{
					$BrandID = $_COOKIE['ItemBrandID'];	
					$ItemName = $_COOKIE['ItemItemName'];	
					$ModelNumber = $_COOKIE['ItemModelNumber'];	
					$PartNumber = $_COOKIE['ItemPartNumber'];
					$RetailPrice = $_COOKIE['ItemRetailPrice'];
				}
			}
			
			
			$sqlSearch .= $BrandID ? " and Brand.ID = ".$BrandID : "";
			$sqlSearch .= $ItemName ? " and Item.ItemName LIKE '%".$ItemName."%'" : "";
			$sqlSearch .= $ModelNumber ? " and Item.ModelNumber LIKE '%".$ModelNumber."%'" : "";
			$sqlSearch .= $PartNumber ? " and Item.PartNumber LIKE '%".$PartNumber."%'" : "";
			$sqlSearch .= $RetailPrice ? " and Item.RetalPrice = '".$RetailPrice."'" : "";
			
			//print $sqlSearch; exit();
			$this->view->BrandID = $BrandID ? $BrandID : "";				
			$this->view->ItemName = $ItemName ? $ItemName : "";				
			$this->view->ModelNumber = $ModelNumber ? $ModelNumber : "";				
			$this->view->PartNumber = $PartNumber ? $PartNumber : "";				
			$this->view->RetailPrice = $RetailPrice ? $RetailPrice : "";				
			
			$strHiddenSearch = "<input type=hidden name='search_brand' value='true'>";
			$strHiddenSearch .= "<input type=hidden name='BrandID' value='".$BrandID."'>";
			$strHiddenSearch .= "<input type=hidden name='ItemName' value='".$ItemName."'>";
			$strHiddenSearch .= "<input type=hidden name='ModelNumber' value='".$ModelNumber."'>";
			$strHiddenSearch .= "<input type=hidden name='PartNumber' value='".$PartNumber."'>";
			$strHiddenSearch .= "<input type=hidden name='RetailPrice' value='".$RetailPrice."'>";

			

			
			if ($add_item)
			{
			
				$BrandID = $Request->getParam('BrandID') ? $Request->getParam('BrandID') : new Zend_Db_Expr("NULL");
				$ItemName = $Request->getParam('ItemName') ? trim($Request->getParam('ItemName')) : new Zend_Db_Expr("NULL");
				$ModelNumber = $Request->getParam('ModelNumber') ? trim($Request->getParam('ModelNumber')) : new Zend_Db_Expr("NULL");
				$PartNumber = $Request->getParam('PartNumber') ? trim($Request->getParam('PartNumber')) : new Zend_Db_Expr("NULL");
				$RetailPrice = $Request->getParam('RetailPrice') ? $Request->getParam('RetailPrice') : new Zend_Db_Expr("NULL");
				$MinStock = $Request->getParam('MinStock') ? $Request->getParam('MinStock') : new Zend_Db_Expr("NULL");
                $MonthDepreciation = $Request->getParam('MonthDepreciation') ? $Request->getParam('MonthDepreciation') : new Zend_Db_Expr("NULL");
				$errorFile = false;
				if (!$_FILES['ItemImage']['error'])
				{

					if ($_FILES['ItemImage']['type'] != "image/jpeg")
					{
						$this->appMessage->setMsg(0, $this->translate->_('Please select a jpeg format file to be uploaded'));
						$errorFile = true;
						
					}
					
					if ($_FILES['ItemImage']['size'] > (3 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, $this->translate->_('Please select a image that is less than 1MB in size'));
						$errorFile = true;
						
					}
					
				}
		
				if (!$errorFile){
					$arrInsert = array("BrandID"=>$BrandID,"ItemName"=>$ItemName,"ModelNumber"=>$ModelNumber, "PartNumber"=>$PartNumber, "RetailPrice"=>$RetailPrice,
                        "MinStock"=>$MinStock, "MonthDepreciation"=>$MonthDepreciation);

					$db->insert("Item", $arrInsert);
					$itemID = $db->lastInsertId();
					$filename = $itemID.".jpg";
					$relativePath = "/uploads/ItemImage/".$filename;
					$relativePathSmall = "/uploads/ItemImage/small/".$filename;
					if (!$_FILES['ItemImage']['error'])
					{
						move_uploaded_file($_FILES['ItemImage']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePath);
						$imgResize = new Venz_App_Image_Resize($_SERVER['DOCUMENT_ROOT'].$relativePath);
						// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
						$imgResize -> resizeImage(80, 80, 'auto');
						// *** 3) Save image
						$imgResize -> saveImage($_SERVER['DOCUMENT_ROOT'].$relativePathSmall, 100);
						$arrUpdate = array("ItemImagePath"=>$relativePath, "ItemImagePathSmall"=>$relativePathSmall);
						$db->update("Item", $arrUpdate, "ID=".$itemID);
					
					}
					
					$this->appMessage->setNotice(1, "New item \"<B>".$ItemName."</B>\" has been created.");
					$this->_redirect('/inventory/brand/item'); 
				}
				
			}
			
			$this->view->edit_item = '';	
			if ($edit_item)
			{
				$this->view->edit_item = $edit_item;
				$arrItemDetail = $libInv->getItemDetail($edit_item);
				
				$this->view->BrandID = $arrItemDetail['BrandID'];	
				$this->view->ItemName = $arrItemDetail['ItemName'];	
				$this->view->ModelNumber = $arrItemDetail['ModelNumber'];	
				$this->view->PartNumber = $arrItemDetail['PartNumber'];	
				$this->view->ItemImagePath = $arrItemDetail['ItemImagePath'];	
				$this->view->RetailPrice = $arrItemDetail['RetailPrice'];	
				$this->view->MinStock = $arrItemDetail['MinStock'];
                $this->view->MonthDepreciation = $arrItemDetail['MonthDepreciation'];
                $this->view->NumStock = $arrItemDetail['NumStock'];
			}					
		
		
		
		
			if ($save_item)
			{
				$ID = $Request->getParam('save_item_id') ? $Request->getParam('save_item_id') : new Zend_Db_Expr("NULL");
				
				$BrandID = $Request->getParam('BrandID') ? $Request->getParam('BrandID') : new Zend_Db_Expr("NULL");
				$ItemName = $Request->getParam('ItemName') ? $Request->getParam('ItemName') : new Zend_Db_Expr("NULL");
				$ModelNumber = $Request->getParam('ModelNumber') ? $Request->getParam('ModelNumber') : new Zend_Db_Expr("NULL");
				$PartNumber = $Request->getParam('PartNumber') ? $Request->getParam('PartNumber') : new Zend_Db_Expr("NULL");
				$RetailPrice = $Request->getParam('RetailPrice') ? $Request->getParam('RetailPrice') : new Zend_Db_Expr("NULL");
				$MinStock = $Request->getParam('MinStock') ? $Request->getParam('MinStock') : new Zend_Db_Expr("NULL");
                $MonthDepreciation = $Request->getParam('MonthDepreciation') ? $Request->getParam('MonthDepreciation') : new Zend_Db_Expr("NULL");

				$NumStock = $Request->getParam('NumStock');
				$trigger_alert = $Request->getParam('trigger_alert');
				if ($trigger_alert && $BrandID)
				{
					$arrBrand = $db->fetchRow("SELECT * FROM Brand WHERE ID=".$BrandID);
					$Content =<<<END
					The item below had reached the minimal stock level. <BR>
					<table>
					<TR><TD>Brand:</TD><TD>$arrBrand[FullName]</TD></TR>
					<TR><TD>Item Name:</TD><TD>$ItemName</TD></TR>
					<TR><TD>Model Name:</TD><TD>$ModelNumber</TD></TR>
					<TR><TD>Part Number:</TD><TD>$PartNumber</TD></TR>
					<TR><TD>Current Stock:</TD><TD>$NumStock</TD></TR>
					<TR><TD>Minimum Stock Alert:</TD><TD>$MinStock</TD></TR>
					</table>
	
END;
					$this->appMessage->setNotice(1, "Stock alert trigger has been sent through email.");
					$sysNotification->setNotificationEmail("Inventory: Minimum Stock Alert", $Content, "ACLRole='Sales' OR ACLRole='AdminSystem' OR ACLRole='Admin'");
					
				}
				
				
				$this->view->BrandID = $BrandID;	
				$this->view->ItemName = $ItemName;	
				$this->view->ModelNumber = $ModelNumber;	
				$this->view->PartNumber = $PartNumber;				
				$this->view->RetailPrice = $RetailPrice;				
				$this->view->MinStock = $MinStock;
                $this->view->MonthDepreciation = $MonthDepreciation;

                $errorFile = false;
				if (!$_FILES['ItemImage']['error'])
				{

					if ($_FILES['ItemImage']['type'] != "image/jpeg")
					{
						$this->appMessage->setMsg(0,$this->translate->_('Please select a jpeg format file to be uploaded'));
						$errorFile = true;
						
					}
					
					if ($_FILES['ItemImage']['size'] > (1 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, $this->translate->_('Please select a image that is less than 1MB in size'));
						$errorFile = true;
						
					}
					
				}
		
				if (!$errorFile){

					$arrUpdate = array("BrandID"=>$BrandID,"ItemName"=>$ItemName,"ModelNumber"=>$ModelNumber,"PartNumber"=>$PartNumber,"RetailPrice"=>$RetailPrice,
                        "MinStock"=>$MinStock, "MonthDepreciation"=>$MonthDepreciation);
					$db->update("Item", $arrUpdate, "ID=".$ID);
					
					$filename = $ID.".jpg";
					$relativePath = "/uploads/ItemImage/".$filename;
					$relativePathSmall = "/uploads/ItemImage/small/".$filename;
					if (!$_FILES['ItemImage']['error'])
					{
						move_uploaded_file($_FILES['ItemImage']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePath);
						$imgResize = new Venz_App_Image_Resize($_SERVER['DOCUMENT_ROOT'].$relativePath);
						// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
						$imgResize -> resizeImage(80, 80, 'auto');
						// *** 3) Save image
						$imgResize -> saveImage($_SERVER['DOCUMENT_ROOT'].$relativePathSmall, 100);
						
						
						
						$arrUpdate = array("ItemImagePath"=>$relativePath, "ItemImagePathSmall"=>$relativePathSmall);
						$db->update("Item", $arrUpdate, "ID=".$ID);
					}

					$this->appMessage->setNotice(1, $this->translate->_('Details for')." <B>".$FullName."</B> ".$this->translate->_('has been updated').".");
					$this->_redirect('/inventory/brand/item'); 
				}
			}


			
			if ($remove_item)
			{
				$arrItemDetail = $libInv->getItemDetail($remove_item);
			
				$db->delete("Item", "ID=".$remove_item);
				$this->appMessage->setNotice(1, $this->translate->_('The item has been removed'));
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrItemDetail['ItemImagePath']);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrItemDetail['ItemImagePathSmall']);
				$this->_redirect('/inventory/brand/item');   				
			}			
		
			
			if ($remove_image)
			{
				$ID = $Request->getParam('save_item_id') ? $Request->getParam('save_item_id') : new Zend_Db_Expr("NULL");
				$arrItemDetail = $libInv->getItemDetail($ID);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrItemDetail['ItemImagePath']);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrItemDetail['ItemImagePathSmall']);
				$arrUpdate = array("ItemImagePath"=>new Zend_Db_Expr("NULL"), "ItemImagePathSmall"=>new Zend_Db_Expr("NULL"));
				$db->update("Item", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, $this->translate->_('The item image has been removed'));
				$this->_redirect('/inventory/brand/item/edit_item/'.$ID);   				
			}			
			
			$this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID); 

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
				if ($rowdata[8] > 0)
					return "<a href='/inventory/brand/itemseries/search_series/1/ItemID/".$rowdata[0]."'>".$rowdata[8]."</a>";
				else
					return $rowdata[8];
			}

            function format_lifespan($colnum, $rowdata)
            {

                return $rowdata[10] ? $rowdata[10]. " months" : "";
            }




            $arrHeader = array ('#', 'ID', '', $this->translate->_('Brand'), $this->translate->_('Item Name'), $this->translate->_('Model Name'),$this->translate->_('Part Number'),
                $this->translate->_('Retail Price'),$this->translate->_('In Stock'),'Min Stock<BR>Trigger', 'Asset<BR>Lifespan', $this->translate->_('Edit | Delete'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%0%','{format_image}','%1%', '%2%', '%3%', '%4%', '{format_retail}','{format_stocknum}','%9%','{format_lifespan}', '{format_action}'),
					 'sort_column' 	=> array('','ID','','BrandName','ItemName','ModelNumber', 'PartNumber', 'RetailPrice','NumStock', 'MinStock','MonthDepreciation', ''),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Items'),					 
					 'aligndata' 	=> 'CCCLLLLRCCCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "1400px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_item = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }
		

    public function itemseriesAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ItemSeries.ID';
				
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
			$search_series = $Request->getParam('search_series');
			$clear_search = $Request->getParam('clear_search');
			
			$this->view->searchSeries = false;
			$strHiddenSearch = "";
			if ($search_series)
			{
				$this->view->searchSeries = true;
				$OrderID = $Request->getParam('OrderID');	
				$ItemID = $Request->getParam('ItemID');	
				$SeriesNumber = $Request->getParam('SeriesNumber');	
				$BranchID = $Request->getParam('BranchID');	
				
				setcookie('ItemSeriesOrderID', $OrderID, time() + (3600*30),"/"); 
				setcookie('ItemSeriesItemID', $ItemID, time() + (3600*30),"/"); 
				setcookie('ItemSeriesSeriesNumber', $SeriesNumber, time() + (3600*30),"/"); 
				setcookie('ItemSeriesBranchID', $BranchID, time() + (3600*30),"/"); 
			}else
			{
				if ($clear_search)
				{
					setcookie('ItemSeriesOrderID',"", time()-3600, "/"); unset($_COOKIE['ItemSeriesOrderID']);
					setcookie('ItemSeriesItemID', "", time()-3600, "/");unset($_COOKIE['ItemSeriesItemID']); 
					setcookie('ItemSeriesSeriesNumber', "", time()-3600, "/"); unset($_COOKIE['ItemSeriesSeriesNumber']);
					setcookie('ItemSeriesBranchID', "", time()-3600, "/"); unset($_COOKIE['ItemSeriesBranchID']);
					
				}
				else
				{
					$OrderID = $_COOKIE['ItemSeriesOrderID'];	
					$ItemID = $_COOKIE['ItemSeriesItemID'];	
					$SeriesNumber = $_COOKIE['ItemSeriesSeriesNumber'];	
					$BranchID = $_COOKIE['ItemSeriesBranchID'];
				}
			}
		
			$sqlSearch .= $OrderID ? " and PurchaseOrders.ID = ".$OrderID : "";
			$sqlSearch .= $ItemID ? " and ItemSeries.ItemID = ".$ItemID : "";
			$sqlSearch .= $BranchID ? " and ItemSeries.BranchID = ".$BranchID : "";
			$sqlSearch .= $SeriesNumber ? " and ItemSeries.SeriesNumber LIKE '%".$SeriesNumber ."%'" : "";
			
			//print $sqlSearch; exit();
			$this->view->OrderID = $OrderID ? $OrderID : "";				
			$this->view->ItemID = $ItemID ? $ItemID : "";				
			$this->view->SeriesNumber = $SeriesNumber ? $SeriesNumber : "";	
			$this->view->BranchID = $BranchID ? $BranchID : "";					
			
			$strHiddenSearch = "<input type=hidden name='search_series' value='true'>";
			$strHiddenSearch .= "<input type=hidden name='OrderID' value='".$OrderID."'>";
			$strHiddenSearch .= "<input type=hidden name='ItemID' value='".$ItemID."'>";
			$strHiddenSearch .= "<input type=hidden name='SeriesNumber' value='".$SeriesNumber."'>";
			$strHiddenSearch .= "<input type=hidden name='BranchID' value='".$BranchID."'>";

			

		
			//$this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID); 
			$this->view->optionItems = $libInv->getItemOptions($this->view->ItemID);
			$this->view->optionPO = $libDb->getTableOptions("PurchaseOrders", "OrderNumber", "ID", $this->view->OrderID, "PurchaseDate"); 
			
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 

			$libInv->setFetchMode(Zend_Db::FETCH_NUM);
			$arrItem = $libInv->getItemsSeries($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataItem = $arrItem[1];

			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			

			$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
			$sessionItemCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
				$sessionItemCounter->numCounter++;
				return $sessionItemCounter->numCounter;
			}		

			function format_unitprice($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[2]);
			}		
			function format_unitdeliverycost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[3]);
			}
			function format_unittaxcost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[4]);
			}				
			function format_unitlandedcost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[5]);
			}
			function format_retailprice($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[20]);
			}

			function format_pono($colnum, $rowdata)
			{
				return "<a target='_new' href='/inventory/po/index/edit_po/".$rowdata[13]."'>".$rowdata[7]."</a>";
			}
			
			function format_seriesnumber($colnum, $rowdata)
			{
//				return "<a target='_new' href='/inventory/po/itemseries/POID/".$rowdata[13]."/POItemsID/".$rowdata[14]."/ItemID/".$rowdata[1]."/fromlisting/1'>".$rowdata[6]."</a>";
				return $rowdata[6];
			}	
			
			function format_purchasedate($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_date_db_to_simple($rowdata[15]);
			}
			function format_branch($colnum, $rowdata)
			{
				return $rowdata[16];
			}						
			function format_itemname($colnum, $rowdata)
			{
//				return  "<a target='_new' href='/inventory/brand/item/edit_item/".$rowdata[1] ."'>".$rowdata[8] ." (".$rowdata[9] . ")";
				return  $rowdata[8] ." (".$rowdata[9];
			}
			
			function format_action($colnum, $rowdata)
			{
				return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/series'><img border=0 src='/images/icons/IconEdit.gif'></a>";
			}		
			
			function format_markup($colnum, $rowdata)
			{

				return $rowdata[18]."%";
			}				
			
			
			
			
			$arrHeader = array ('#', $this->translate->_('PO #'), $this->translate->_('PO Date'), $this->translate->_('Item Name'),$this->translate->_('Branch'),$this->translate->_('Serial Number'), $this->translate->_('Unit Price'), $this->translate->_('Delivery'),$this->translate->_('Tax'),$this->translate->_('Landed Cost'),$this->translate->_('Markup %'),$this->translate->_('Unit Retail'),$this->translate->_('Edit'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','{format_pono}','{format_purchasedate}','{format_itemname}','{format_branch}','{format_seriesnumber}','{format_unitprice}', '{format_unitdeliverycost}', '{format_unittaxcost}', '{format_unitlandedcost}',  '{format_markup}','{format_retailprice}', '{format_action}'),					 
					 'sort_column' 	=> array('','PurchaseOrder.ID','PurchaseOrder.PurchaseDate','ItemFullName','BranchName','ItemSeries.SeriesNumber','ItemSeries.UnitPrice','ItemSeries.UnitDeliveryCost','ItemSeries.UnitTaxCost','ItemSeries.UnitLandedCost','ItemSeries.MarkupPercent', 'Item.RetailPrice', ''),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Items Series'),					 
					 'aligndata' 	=> 'CCCLLCRRRRRRC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "1400px",
			         'sortby' => $sortby,
					 'colparam'      => array("","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap"),
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch
				)
			);
			$this->view->content_item = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }
	
	public function itemseriesdetailAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			$displayFormat = new Venz_App_Display_Format();
			$sysNotification = new Venz_App_System_Notification();

            $systemSetting = new Zend_Session_Namespace('systemSetting');
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];
			
			$dispFormat = new Venz_App_Display_Format();
            $invRental = new Venz_App_Inventory_Rental();

            /////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$ItemSeriesID = $Request->getParam('id');
			$accessFrom = $Request->getParam('f');
			$this->view->accessFrom = $accessFrom;
			$add_series = $Request->getParam('add_series');
			$this->view->add_series = $add_series;
	
			$add_itemseries = $Request->getParam('add_itemseries');	
			if ($add_itemseries)
			{
				$ItemID = $Request->getParam('ItemID') ? $Request->getParam('ItemID') : new Zend_Db_Expr("NULL");
				//$PurchaseDate = $Request->getParam('PurchaseDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('PurchaseDate')) : new Zend_Db_Expr("NULL");
				$SeriesNumber = $Request->getParam('SeriesNumber') ? $Request->getParam('SeriesNumber') : new Zend_Db_Expr("NULL");
				$SalesOrderNumber = $Request->getParam('SalesOrderNumber') ? $Request->getParam('SalesOrderNumber') : new Zend_Db_Expr("NULL");
				$BranchID = $Request->getParam('BranchID') ? $Request->getParam('BranchID') : new Zend_Db_Expr("NULL");
				$UnitPriceRM = $Request->getParam('UnitPriceRM') ? $Request->getParam('UnitPriceRM') : new Zend_Db_Expr("NULL");
				$UnitDeliveryCost = $Request->getParam('UnitDeliveryCost') ? $Request->getParam('UnitDeliveryCost') : new Zend_Db_Expr("NULL");
				$UnitTaxCost = $Request->getParam('UnitTaxCost') ? $Request->getParam('UnitTaxCost') : new Zend_Db_Expr("NULL");
				$UnitLandedCost = $Request->getParam('UnitLandedCost') ? $Request->getParam('UnitLandedCost') : new Zend_Db_Expr("NULL");
				$StatusItem = $Request->getParam('StatusItem') ? $Request->getParam('StatusItem') : new Zend_Db_Expr("NULL");
				$UnitRetail = $Request->getParam('UnitRetail') ? $Request->getParam('UnitRetail') : new Zend_Db_Expr("NULL");
				
				$arrInsert = array("ItemID"=>$ItemID,"SeriesNumber"=>$SeriesNumber,"BranchID"=>$BranchID,"UnitPriceRM"=>$UnitPriceRM,"UnitDeliveryCost"=>$UnitDeliveryCost,"UnitDeliveryCost"=>$UnitDeliveryCost,
					"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"Status"=>$StatusItem,"MarkupPercent"=>$MarkupPercent,"SalesOrderNumber"=>$SalesOrderNumber,"UnitRetail"=>$UnitRetail
				);
				$db->insert("ItemSeries", $arrInsert);
				$ItemSeriesID = $db->lastInsertId();
				
				$arrInsert = array("ItemSeriesID"=>$ItemSeriesID,"StatusDate"=>new Zend_Db_Expr("now()"),"Status"=>$StatusItem,"UserIDEntry"=>$this->userInfo->ID,"EntryDateTime"=>new Zend_Db_Expr("now()"),
					"UserIDResp"=>$this->userInfo->ID,"Notes"=>new Zend_Db_Expr("NULL"),"ReferenceNo"=>new Zend_Db_Expr("NULL"), "TransitTo"=>new Zend_Db_Expr("NULL")
				);
				$db->insert("ItemSeriesStatus", $arrInsert);

				if ($StatusItem == 'rental_asset') {
                    $invRental->insertAsRental($ItemSeriesID, NULL, NULL);
                }

				
				$this->appMessage->setNotice(1, $this->translate->_('Item series has been created').".");
				$this->_redirect('/inventory/brand/itemseriesdetail/id/'.$ItemSeriesID.'/f/'.$this->view->accessFrom); 				
			}
	
			
			$update_item = $Request->getParam('update_item');	
			if ($update_item)
			{

			
				$ItemID = $Request->getParam('ItemID') ? $Request->getParam('ItemID') : new Zend_Db_Expr("NULL");
				$SeriesNumber = $Request->getParam('SeriesNumber') ? $Request->getParam('SeriesNumber') : new Zend_Db_Expr("NULL");
//				$SalesOrderNumber = $Request->getParam('SalesOrderNumber') ? $Request->getParam('SalesOrderNumber') : new Zend_Db_Expr("NULL");
				//$BranchID = $Request->getParam('BranchID') ? $Request->getParam('BranchID') : new Zend_Db_Expr("NULL");
//				$UnitPriceRM = $Request->getParam('UnitPriceRM') ? $Request->getParam('UnitPriceRM') : new Zend_Db_Expr("NULL");
//				$UnitDeliveryCost = $Request->getParam('UnitDeliveryCost') ? $Request->getParam('UnitDeliveryCost') : new Zend_Db_Expr("NULL");
//				$UnitTaxCost = $Request->getParam('UnitTaxCost') ? $Request->getParam('UnitTaxCost') : new Zend_Db_Expr("NULL");
//				$UnitLandedCost = $Request->getParam('UnitLandedCost') ? $Request->getParam('UnitLandedCost') : new Zend_Db_Expr("NULL");
//				//$StatusItem = $Request->getParam('StatusItem') ? $Request->getParam('StatusItem') : new Zend_Db_Expr("NULL");
//				$MarkupPercent = $Request->getParam('MarkupPercent') ? $Request->getParam('MarkupPercent') : new Zend_Db_Expr("NULL");
//				$UnitRetail = $Request->getParam('UnitRetail') ? $Request->getParam('UnitRetail') : new Zend_Db_Expr("NULL");
				
//				$arrUpdate = array("ItemID"=>$ItemID,"SeriesNumber"=>$SeriesNumber,"BranchID"=>$BranchID,"UnitPriceRM"=>$UnitPriceRM,"UnitDeliveryCost"=>$UnitDeliveryCost,"UnitDeliveryCost"=>$UnitDeliveryCost,
//					"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"Status"=>$StatusItem,"MarkupPercent"=>$MarkupPercent,"SalesOrderNumber"=>$SalesOrderNumber,"UnitRetail"=>$UnitRetail
//				);

                if ($this->userInfo->ACLRole == "AdminSystem"){
                    $BranchID = $Request->getParam('BranchID') ? $Request->getParam('BranchID') : new Zend_Db_Expr("NULL");
//                    $arrUpdate = array("ItemID"=>$ItemID,"SeriesNumber"=>$SeriesNumber,"BranchID"=>$BranchID,"UnitPriceRM"=>$UnitPriceRM,"UnitDeliveryCost"=>$UnitDeliveryCost,"UnitDeliveryCost"=>$UnitDeliveryCost,
//                        "UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"SalesOrderNumber"=>$SalesOrderNumber,"UnitRetail"=>$UnitRetail
//                    );

                    $arrUpdate = array("ItemID"=>$ItemID,"SeriesNumber"=>$SeriesNumber,"BranchID"=>$BranchID);

                }else{
//                    $arrUpdate = array("ItemID"=>$ItemID,"SeriesNumber"=>$SeriesNumber,"UnitPriceRM"=>$UnitPriceRM,"UnitDeliveryCost"=>$UnitDeliveryCost,"UnitDeliveryCost"=>$UnitDeliveryCost,
//                        "UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"SalesOrderNumber"=>$SalesOrderNumber,"UnitRetail"=>$UnitRetail
//                    );

                    $arrUpdate = array("ItemID"=>$ItemID,"SeriesNumber"=>$SeriesNumber);


                }
                $db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);


				$arrItem = $libInv->getItemDetail($ItemID);
				$NumStock = $arrItem['NumStock'];	
				$MinStock = $arrItem['MinStock'];	
				$BrandID = $arrItem['BrandID'];
				if (($NumStock <= $MinStock) && $BrandID && $MinStock)
				{
					$arrBrand = $db->fetchRow("SELECT * FROM Brand WHERE ID=".$BrandID);
					$Content =<<<END
					The item below had reached the minimal stock level. <BR>
					<table>
					<TR><TD>Brand:</TD><TD>$arrBrand[FullName]</TD></TR>
					<TR><TD>Item Name:</TD><TD>$arrItem[ItemName]</TD></TR>
					<TR><TD>Model Name:</TD><TD>$arrItem[ModelNumber]</TD></TR>
					<TR><TD>Part Number:</TD><TD>$arrItem[PartNumber]</TD></TR>
					<TR><TD>Current Stock:</TD><TD>$NumStock</TD></TR>
					<TR><TD>Minimum Stock Alert:</TD><TD>$MinStock</TD></TR>
					</table>
	
END;
					$this->appMessage->setNotice(1, "Stock alert trigger has been sent through email.");
					$sysNotification->setNotificationEmail("Inventory: Minimum Stock Alert", $Content, "ACLRole='Sales' OR ACLRole='AdminSystem' OR ACLRole='Admin'");
			//		$sysNotification->setNotificationEmail("Inventory: Minimum Stock Alert", $Content, "ID=3");
					

									
				}
				
				
									
				$this->appMessage->setNotice(1, $this->translate->_('Item series has been created'));
				$this->_redirect('/inventory/brand/itemseriesdetail/id/'.$ItemSeriesID.'/f/'.$this->view->accessFrom); 
				
						
			}

			$remove_status = $Request->getParam('remove_status');	
			if ($remove_status)
			{
			    $arrStatusRemove = $db->fetchRow("SELECT * FROM ItemSeriesStatus WHERE ID=".$remove_status);
			    if ($arrStatusRemove['Status'] == 'rental_asset'){
                    $arrRentalAsset = $db->fetchRow("SELECT * FROM RentalAsset WHERE ItemSeriesID=".$ItemSeriesID);
                    $db->delete("RentalAssetStatus", "RentalAssetID=".$arrRentalAsset['ID']);
                    $db->delete("RentalAsset", "ItemSeriesID=".$ItemSeriesID);
                }

				$db->delete("ItemSeriesStatus", "ID=".$remove_status);
                $arrLatestStatus = $db->fetchRow("SELECT * FROM ItemSeriesStatus WHERE ItemSeriesID=".$ItemSeriesID." ORDER BY StatusDate Desc, EntryDateTime Desc");
                $LatestStatus = $arrLatestStatus['Status'] ? $arrLatestStatus['Status'] : "in";
                $db->update("ItemSeries", array("Status"=>$LatestStatus), "ID=".$ItemSeriesID);

                $this->appMessage->setNotice(1, "The entry has been removed.");
				$this->_redirect('/inventory/brand/itemseriesdetail/id/'.$ItemSeriesID.'/f/'.$this->view->accessFrom); 
			
	/*			$arrItemDetail = $libInv->getItemDetail($remove_item);
			
				$db->delete("Item", "ID=".$remove_item);
				$appMessage->setNotice(1, "The item has been removed.");
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrItemDetail['ItemImagePath']);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrItemDetail['ItemImagePathSmall']);
				$this->_redirect('/inventory/brand/item');   				
	*/		}					
			
			
			$add_status = $Request->getParam('add_status');	
			if ($add_status)
			{

				$ItemID = $Request->getParam('ItemID');
				$ReferenceNo = $Request->getParam('ReferenceNo') ? $Request->getParam('ReferenceNo') : new Zend_Db_Expr("NULL");
				$StatusDate = $Request->getParam('StatusDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('StatusDate')) : new Zend_Db_Expr("NULL");
				$Status = $Request->getParam('Status') ? $Request->getParam('Status') : new Zend_Db_Expr("NULL");
				$TransitTo = $Request->getParam('TransitTo') ? $Request->getParam('TransitTo') : new Zend_Db_Expr("NULL");
                $MonthDepreciation = $Request->getParam('MonthDepreciation') ? $Request->getParam('MonthDepreciation') : new Zend_Db_Expr("NULL");
                $MonthRemaining = $Request->getParam('MonthRemaining') ? $Request->getParam('MonthRemaining') : new Zend_Db_Expr("NULL");
                $UserIDResp = $Request->getParam('UserIDResp') ? $Request->getParam('UserIDResp') : new Zend_Db_Expr("NULL");
				$Notes = $Request->getParam('Notes') ? $Request->getParam('Notes') : new Zend_Db_Expr("NULL");

                $arrItemSeriesStatus = $db->fetchRow("SELECT * FROM ItemSeriesStatus WHERE ItemSeriesID=".$ItemSeriesID." order by StatusDate desc, ID desc limit 1");

                $arrInsert = array("ItemSeriesID"=>$ItemSeriesID,"StatusDate"=>$StatusDate,"Status"=>$Status,"UserIDEntry"=>$this->userInfo->ID,"EntryDateTime"=>new Zend_Db_Expr("now()"),
					"UserIDResp"=>$UserIDResp,"Notes"=>$Notes,"ReferenceNo"=>$ReferenceNo, "TransitTo"=>$TransitTo, "MonthDepreciation"=>$MonthDepreciation, "MonthRemaining"=>$MonthRemaining
				);
				$db->insert("ItemSeriesStatus", $arrInsert);

                $arrUpdate = array("Status"=>$Status);
                if ($arrItemSeriesStatus['Status'] == 'intransit' && $Status == 'in'){
                    $arrUpdate['BranchID'] = $arrItemSeriesStatus['TransitTo'];
                }


                $db->Update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);

                if ($Status == 'rental_asset') {
                    $arrItemSeries = $db->fetchRow("SELECT * FROM ItemSeries WHERE ID=".$ItemSeriesID);
                    $invRental->insertAsRental($ItemSeriesID,$arrItemSeries['POItemsID'],$arrItemSeries['UnitLandedCost'],$MonthDepreciation,$MonthRemaining);
                }

				$arrItem = $libInv->getItemDetail($ItemID);
				$NumStock = $arrItem['NumStock'];	
				$MinStock = $arrItem['MinStock'];	
				$BrandID = $arrItem['BrandID'];
				if (($NumStock <= $MinStock) && $BrandID && $MinStock)
				{
					$arrBrand = $db->fetchRow("SELECT * FROM Brand WHERE ID=".$BrandID);
					$Content =<<<END
					The item below had reached the minimal stock level. <BR>
					<table>
					<TR><TD>Brand:</TD><TD>$arrBrand[FullName]</TD></TR>
					<TR><TD>Item Name:</TD><TD>$arrItem[ItemName]</TD></TR>
					<TR><TD>Model Name:</TD><TD>$arrItem[ModelNumber]</TD></TR>
					<TR><TD>Part Number:</TD><TD>$arrItem[PartNumber]</TD></TR>
					<TR><TD>Current Stock:</TD><TD>$NumStock</TD></TR>
					<TR><TD>Minimum Stock Alert:</TD><TD>$MinStock</TD></TR>
					</table>
	
END;
					$this->appMessage->setNotice(1, "Stock alert trigger has been sent through email.");
					$sysNotification->setNotificationEmail("Inventory: Minimum Stock Alert", $Content, "ACLRole='Sales' OR ACLRole='AdminSystem' OR ACLRole='Admin'");

				}
				
				
									
				$this->appMessage->setNotice(1, $this->translate->_('Item series status')." \"<B>".$SeriesNumber."</B>\" ".$this->translate->_('has been updated').".");
				$this->_redirect('/inventory/brand/itemseriesdetail/id/'.$ItemSeriesID.'/f/'.$this->view->accessFrom); 
				
						
			}

			if ($ItemSeriesID)
			{
				$arrItemDetail = $libInv->getItemsSeriesDetail($ItemSeriesID);

				$this->view->ItemSeriesID = $ItemSeriesID;
				
				//print_r($arrItemDetail);
				
				$this->view->ItemID = $arrItemDetail['ItemID'];	
				$this->view->OrderNumber = $arrItemDetail['OrderNumber'];	
				$this->view->POLocked = $arrItemDetail['POLocked'];	
				$this->view->ItemFullName = $arrItemDetail['ItemFullName'];	
				$this->view->PurchaseDate = $displayFormat->format_date($arrItemDetail['PurchaseDate']);	
				$this->view->SeriesNumber = $arrItemDetail['SeriesNumber'];	
				$this->view->BranchID = $arrItemDetail['BranchID'];
				$this->view->UnitPriceRM = $arrItemDetail['UnitPriceRM'];	
				$this->view->UnitDeliveryCost = $arrItemDetail['UnitDeliveryCost'];	
				$this->view->UnitTaxCost = $arrItemDetail['UnitTaxCost'];	
				$this->view->UnitLandedCost = $arrItemDetail['UnitLandedCost'];	
				$this->view->Status = $arrItemDetail['Status'];	
				$this->view->strStatus = $systemSetting->arrStockStatus[$this->view->Status];
				
				$this->view->RetailPrice = $arrItemDetail['UnitRetail'];	
				$this->view->MarkupPercent = $arrItemDetail['MarkupPercent'];	
				$this->view->SalesOrderNumber = $arrItemDetail['SalesOrderNumber'];	
				
				$this->view->ItemImagePath = $arrItemDetail['ItemImagePath'];	
				$arrItem = $libInv->getItemDetail($this->view->ItemID);
				
				$this->view->NumStock = $arrItem['NumStock'];	
				$this->view->MinStock = $arrItem['MinStock'];	
				
				
				$this->view->SOLink = "";
				if ($arrItemDetail['SOItemsID'])
				{
					$arrSODetail = $db->fetchRow("SELECT SalesOrders.ID from SOItems, SalesOrders where SalesOrders.ID=SOItems.OrderID AND SOItems.ID=".$arrItemDetail['SOItemsID']);
					$this->view->SOLink = "<BR><a href='/inventory/so/index/edit_so/".$arrSODetail['ID']."'><B>view</B></a>";
					
				}
				
				$arrItemStatusAll = $libInv->getItemsSeriesStatus($ItemSeriesID);
                $counter=0;
                foreach ($arrItemStatusAll as $arrItemStatus){
                    $counter++;
					$strStatus = $systemSetting->arrStockStatus[$arrItemStatus[Status]];
					$strTransitLocation = "";
					if ($arrItemStatus['TransitLocation'])
						$strTransitLocation = " To ".$arrItemStatus['TransitLocation'];
					
					$strEntryDateTime = $displayFormat->format_datetime($arrItemStatus['EntryDateTime']);
					$strDeleteStatus = "";
					if ($this->view->userInfo->ACLRole != "User" && $this->userInfo->ACLRole != "Sales"  && $this->userInfo->ACLRole != "Account" && $counter == 1)
						$strDeleteStatus = "<input type=button name='delete_status' id='delete_status' value='".$this->translate->_('Delete Entry')."' onclick='OnDeleteStatus($arrItemStatus[ID])'>";
					
					
					$this->view->status .= <<<END
					  <tr>
		<td class="report_even" style="text-align:center">$arrItemStatus[ReferenceNo]</td>
		<td class="report_even" style="text-align:center">$arrItemStatus[StatusDate]</td>
		<td class="report_even" style="text-align:center">$strStatus $strTransitLocation</td>
		<td class="report_even" style="text-align:center">$arrItemStatus[UserInchargeName]</td>
		<td class="report_even" style="text-align:center">$arrItemStatus[Notes]</td>
		<td class="report_even" style="text-align:center">$arrItemStatus[UserEntryName]</td>
		<td class="report_even" style="text-align:center">$strEntryDateTime</td>
		<td class="report_even" style="text-align:center">$strDeleteStatus</td>
	</tr>
					
END;
				}
			}					
				
			

				
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			$this->view->optionBranchesTransit = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->TransitTo); 
			$this->view->optionPersonInCharge = $libDb->getTableOptions("ACLUsers", "Name", "ID"); 
			$this->view->optionStatus = $libDb->getSystemOptions("arrStockStatus", NULL,
                $arrItemDetail['Status'] == 'rental_asset' ? array('rental_asset', 'intransit') : array());
			$this->view->optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $this->view->Status); 
			$this->view->optionItems = $libInv->getItemOptions($this->view->ItemID);
					
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }



    public function flowAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$sessionUserInfo = new Zend_Session_Namespace('sessionUserInfo');
			$sessionUserInfo->userInfo = $this->userInfo;
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ItemSeries.ID';
				
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
			$search_series = $Request->getParam('search_series');
			$clear_search = $Request->getParam('clear_search');			
			$this->view->searchSeries = false;
			$strHiddenSearch = "";
		
			
		
			if ($search_series)
			{
				$this->view->searchSeries = true;
				$SearchString = $Request->getParam('SearchString');	
				
				$OrderID = $Request->getParam('OrderID');	
				$ItemID = $Request->getParam('ItemID');	
				
				$BrandID = $Request->getParam('BrandID');	
				$ItemName = $Request->getParam('ItemName');	
				$ModelNumber = $Request->getParam('ModelNumber');	
				$PartNumber = $Request->getParam('PartNumber');	
				
				
				$SeriesNumber = $Request->getParam('SeriesNumber');	
				$BranchID = $Request->getParam('BranchID');
				$Status = $Request->getParam('Status');

			
				setcookie('FlowSearchString', $SearchString, time() + (3600*30),"/"); 
				setcookie('FlowOrderID', $OrderID, time() + (3600*30),"/"); 
				setcookie('FlowItemID', $ItemID, time() + (3600*30),"/"); 
				setcookie('FlowBrandID', $BrandID, time() + (3600*30),"/"); 
				setcookie('FlowItemName', $ItemName, time() + (3600*30),"/"); 
				setcookie('FlowModelNumber', $ModelNumber, time() + (3600*30),"/"); 
				setcookie('FlowPartNumber', $PartNumber, time() + (3600*30),"/"); 
				setcookie('FlowSeriesNumber', $SeriesNumber, time() + (3600*30),"/"); 
				setcookie('FlowBranchID', $BranchID, time() + (3600*30),"/"); 
				setcookie('FlowStatus', $Status, time() + (3600*30),"/"); 
				
				
			}else
			{
				if ($clear_search)
				{
					setcookie('FlowSearchString',"", time()-3600, "/"); unset($_COOKIE['FlowSearchString']);
					setcookie('FlowOrderID',"", time()-3600, "/"); unset($_COOKIE['FlowOrderID']);
					setcookie('FlowItemID', "", time()-3600, "/");unset($_COOKIE['FlowItemID']); 
					setcookie('FlowBrandID', "", time()-3600, "/");unset($_COOKIE['FlowBrandID']); 
					setcookie('FlowItemName', "", time()-3600, "/");unset($_COOKIE['FlowItemName']); 
					setcookie('FlowModelNumber', "", time()-3600, "/");unset($_COOKIE['FlowModelNumber']); 
					setcookie('FlowPartNumber', "", time()-3600, "/");unset($_COOKIE['FlowPartNumber']); 
					setcookie('FlowSeriesNumber', "", time()-3600, "/"); unset($_COOKIE['FlowSeriesNumber']);
					setcookie('FlowBranchID', "", time()-3600, "/"); unset($_COOKIE['FlowBranchID']);
					setcookie('FlowStatus', "", time()-3600, "/"); 	unset($_COOKIE['FlowStatus']);	
				
				}
				else
				{
					$SearchString = $_COOKIE['FlowSearchString'];	
					$OrderID = $_COOKIE['FlowOrderID'];	
					$ItemID = $_COOKIE['FlowItemID'];	
					$BrandID = $_COOKIE['FlowBrandID'];	
					$ItemName = $_COOKIE['FlowItemName'];	
					$ModelNumber = $_COOKIE['FlowModelNumber'];	
					$PartNumber = $_COOKIE['FlowPartNumber'];						
					$SeriesNumber = $_COOKIE['FlowSeriesNumber'];	
					$BranchID = $_COOKIE['FlowBranchID'];
					$Status = $_COOKIE['FlowStatus'];
				}
			}			

			$sqlSearch .= $SearchString ? " and (Item.ItemName LIKE ".$db->quote('%' . trim($SearchString) .'%'). " OR Item.ModelNumber LIKE ".$db->quote('%' .trim($SearchString) . '%') . " OR Item.PartNumber LIKE ".$db->quote('%' . trim($SearchString) . '%').")" : "";
			$sqlSearch .= $OrderID ? " and PurchaseOrders.ID = ".$OrderID : "";
			$sqlSearch .= $ItemID ? " and ItemSeries.ItemID = ".$ItemID : "";
			$sqlSearch .= $BrandID ? " and Item.BrandID = ".$BrandID : "";

			//			$sqlSearch .= $ItemName ? " and Item.ItemName LIKE ".$db->quote('%' . trim($ItemName) .'%') : "";
//			$sqlSearch .= $ModelNumber ? " and Item.ModelNumber LIKE ".$db->quote('%' .trim($ModelNumber) . '%') : "";
//			$sqlSearch .= $PartNumber ? " and Item.PartNumber LIKE ".$db->quote('%' . trim($PartNumber) . '%'): "";
            $sqlSearch .= $ItemName ? " and Item.ItemName = ".$db->quote(trim($ItemName)) : "";
            $sqlSearch .= $ModelNumber ? " and Item.ModelNumber = ".$db->quote(trim($ModelNumber)) : "";
            $sqlSearch .= $PartNumber ? " and Item.PartNumber = ".$db->quote(trim($PartNumber)): "";

			$sqlSearch .= $BranchID ? " and ItemSeries.BranchID = ".$BranchID : "";
			$sqlSearch .= $SeriesNumber ? " and ItemSeries.SeriesNumber LIKE '%".trim($SeriesNumber) ."%'" : "";
			$sqlSearch .= $Status ? " and ItemSeries.Status = '".$Status."'" : "";

			$this->view->SearchString = $SearchString ? $SearchString : "";				
			$this->view->OrderID = $OrderID ? $OrderID : "";				
			$this->view->ItemID = $ItemID ? $ItemID : "";				
			$this->view->BrandID = $BrandID ? $BrandID : "";				
			$this->view->ItemName = $ItemName ? $ItemName : "";				
			$this->view->ModelNumber = $ModelNumber ? $ModelNumber : "";				
			$this->view->PartNumber = $PartNumber ? $PartNumber : "";				
			$this->view->SeriesNumber = $SeriesNumber ? $SeriesNumber : "";	
			$this->view->BranchID = $BranchID ? $BranchID : "";					
			$this->view->Status = $Status ? $Status : "";					
			
			$strHiddenSearch = "<input type=hidden name='search_series' value='true'>";
			$strHiddenSearch .= "<input type=hidden name='SearchString' value='".$SearchString."'>";
			$strHiddenSearch .= "<input type=hidden name='OrderID' value='".$OrderID."'>";
			$strHiddenSearch .= "<input type=hidden name='ItemID' value='".$ItemID."'>";
			$strHiddenSearch .= "<input type=hidden name='BrandID' value='".$BrandID."'>";
			$strHiddenSearch .= "<input type=hidden name='ItemName' value=\"".$ItemName."\">";
			$strHiddenSearch .= "<input type=hidden name='ModelNumber' value=\"".$ModelNumber."\">";
			$strHiddenSearch .= "<input type=hidden name='PartNumber' value=\"".$PartNumber."\">";
			$strHiddenSearch .= "<input type=hidden name='SeriesNumber' value='".$SeriesNumber."'>";
			$strHiddenSearch .= "<input type=hidden name='BranchID' value='".$BranchID."'>";
			$strHiddenSearch .= "<input type=hidden name='Status' value='".$Status."'>";

			//$this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID); 
			$this->view->optionItems = $libInv->getItemOptions($this->view->ItemID);
			$this->view->optionPO = $libDb->getTableOptions("PurchaseOrders", "OrderNumber", "ID", $this->view->OrderID, "PurchaseDate"); 
			
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			$this->view->optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $this->view->Status); 			
			
			$this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID); 
			
/*			if ($this->view->BrandID)
				$this->view->optionItem = $libDb->getTableOptions("Item", "ItemName", "ItemName", $this->view->ItemName, NULL, " AND Item.BrandID=".$this->view->BrandID);
			else
				$this->view->optionItem = $libDb->getTableOptions("Item", "ItemName", "ItemName", $this->view->ItemName); 
			
			if ($this->view->BrandID)
				$this->view->optionModelNumber = $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", $this->view->ModelNumber, NULL, " AND Item.BrandID=".$this->view->BrandID); 
			else
				$this->view->optionModelNumber = $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", $this->view->ModelNumber); 
			
			if ($this->view->BrandID)
				$this->view->optionPartNumber = $libDb->getTableOptions("Item", "PartNumber", "PartNumber", $this->view->PartNumber, NULL, " AND Item.BrandID=".$this->view->BrandID);
			else
				$this->view->optionPartNumber = $libDb->getTableOptions("Item", "PartNumber", "PartNumber", $this->view->PartNumber); 
*/
			
			$sqlFilterBrand = "";$sqlFilterItem = "";$sqlFilterModel = "";
			if ($this->view->BrandID)
				$sqlFilterBrand .= " AND Item.BrandID=".$this->view->BrandID;
			if ($this->view->ItemName)
				$sqlFilterItem .= " AND Item.ItemName=".$db->quote(trim($this->view->ItemName));
			if ($this->view->ModelNumber)
				$sqlFilterModel .= " AND Item.ModelNumber=".$db->quote(trim($this->view->ModelNumber));
			
			$this->view->optionItem = $libDb->getTableOptions("Item", "ItemName", "ItemName", $this->view->ItemName, NULL, $sqlFilterBrand);
			$this->view->optionModelNumber = $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", $this->view->ModelNumber, NULL, $sqlFilterBrand.$sqlFilterItem); 
			$this->view->optionPartNumber = $libDb->getTableOptions("Item", "PartNumber", "PartNumber", $this->view->PartNumber, NULL, $sqlFilterBrand.$sqlFilterItem.$sqlFilterModel);
			
	
			//else
			//	$this->view->optionModelNumber = $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", $this->view->ModelNumber); 
				
				
			$libInv->setFetchMode(Zend_Db::FETCH_NUM);
			$arrItem = $libInv->getItemsSeries($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			
			$dataItem = $arrItem[1];
			$exportSql = $arrItem[2];


			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			

			$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
			$sessionItemCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
				$sessionItemCounter->numCounter++;
				return $sessionItemCounter->numCounter;
			}	
			
			$sessionInlineUnitPrice = new Zend_Session_Namespace('sessionInlineUnitPrice');
			$sessionInlineUnitPrice->jsInline = "";
			function format_unitprice($colnum, $rowdata, $export)
			{
				$sessionInlineUnitPrice = new Zend_Session_Namespace('sessionInlineUnitPrice');
				$sessionUserInfo = new Zend_Session_Namespace('sessionUserInfo');
				$dispFormat = new Venz_App_Display_Format();

				if ($export){
                    if ($rowdata[2])
                        return $dispFormat->format_currency($rowdata[2]);
                    else
                        return "";
                }

				if ($sessionUserInfo->userInfo->ACLRole == "AdminSystem" || $sessionUserInfo->userInfo->ACLRole == "Admin") 
				{
					if ($rowdata[2] && !$rowdata[0])
					{
						$sessionInlineUnitPrice->jsInline .= "$('#unit_price_".$rowdata[11]."').editable({success: function (res, val){ updateUnitRetail(".$rowdata[11].", 'unit_price', res);}});";
						$edit = '<a href="#" id="unit_price_'.$rowdata[11].'" data-type="text" data-pk="1" data-url="/inventory/brand/ajaxUnitPrice/id/'.$rowdata[11].'" data-title="Enter unit price"><img width="15px" src="/images/icons/IconEdit2.png"> '.$dispFormat->format_currency($rowdata[2]).'</a>';
						return $edit;
					}else if ($rowdata[2])
					{
						return $dispFormat->format_currency($rowdata[2]);
					}
					else if (!$rowdata[0])
					{
						$sessionInlineUnitPrice->jsInline .= "$('#unit_price_".$rowdata[11]."').editable({success: function (res, val){ updateUnitRetail(".$rowdata[11].", 'unit_price', res);}});";
						return '<a href="#" id="unit_price_'.$rowdata[11].'" data-type="text" data-pk="1" data-url="/inventory/brand/ajaxUnitPrice/id/'.$rowdata[11].'" data-title="Enter unit price"><img src="/images/icons/IconEdit2.png"></a>';

					}else
						return "";
				}else
				{
					if ($rowdata[2])
						return $dispFormat->format_currency($rowdata[2]);
					else
						return "";
				}
			}		
			function format_unitdeliverycost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[3]);
			}
			function format_unittaxcost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[4]);
			}
			
			$sessionInlineLandedCost = new Zend_Session_Namespace('sessionInlineLandedCost');
			$sessionInlineLandedCost->jsInline = "";
			function format_unitlandedcost($colnum, $rowdata, $export)
			{
				$sessionInlineLandedCost = new Zend_Session_Namespace('sessionInlineLandedCost');
				$sessionUserInfo = new Zend_Session_Namespace('sessionUserInfo');
				$dispFormat = new Venz_App_Display_Format();

                if ($export){
                    if ($rowdata[5])
                        return $dispFormat->format_currency($rowdata[5]);
                    else
                        return "";
                }


                if ($sessionUserInfo->userInfo->ACLRole == "AdminSystem" || $sessionUserInfo->userInfo->ACLRole == "Admin")
				{
					if ($rowdata[5] && !$rowdata[0])
					{
						$sessionInlineLandedCost->jsInline .= "$('#landed_cost_".$rowdata[11]."').editable({success: function (res, val){ updateUnitRetail(".$rowdata[11].", 'landed_cost', res);}});";
						$edit = '<a href="#" id="landed_cost_'.$rowdata[11].'" data-type="text" data-pk="1" data-url="/inventory/brand/ajaxLandedCost/id/'.$rowdata[11].'" data-title="Enter landed cost"><img width="15px" src="/images/icons/IconEdit2.png"> '.$dispFormat->format_currency($rowdata[5]).'</a>';
						return $edit;
					}else if ($rowdata[5])
					{
						return $dispFormat->format_currency($rowdata[5]);
					}
					else if (!$rowdata[0])
					{
						$sessionInlineLandedCost->jsInline .= "$('#landed_cost_".$rowdata[11]."').editable({success: function (res, val){ updateUnitRetail(".$rowdata[11].", 'landed_cost', res);}});";
						return '<a href="#" id="landed_cost_'.$rowdata[11].'" data-type="text" data-pk="1" data-url="/inventory/brand/ajaxLandedCost/id/'.$rowdata[11].'" data-title="Enter landed cost"><img src="/images/icons/IconEdit2.png"></a>';

					}else
						return "";
				}else
				{
					if ($rowdata[5])
						return $dispFormat->format_currency($rowdata[5]);
					else
						return "";
				}
			}
			function format_retailprice($colnum, $rowdata, $export)
			{
				$dispFormat = new Venz_App_Display_Format();

				if ($export){
				    if ($rowdata[20])
				        return $dispFormat->format_currency($rowdata[20]);
				    else
				        return "";
                }

				if ($rowdata[20])
					return "<div style='width: 100%; text-align: right;' id='idRetail_".$rowdata[11]."'>". $dispFormat->format_currency($rowdata[20])."</div>";
				else
					return "<div style='width: 100%; text-align: right;' id='idRetail_".$rowdata[11]."'></div>";
			}

			function format_pono($colnum, $rowdata)
			{
				return "<a target='_new' href='/inventory/po/index/edit_po/".$rowdata[13]."'>".$rowdata[7]."</a>";
			}

            function format_partnumber($colnum, $rowdata, $export)
            {
                return $rowdata[26];
            }

            function format_seriesnumber($colnum, $rowdata, $export)
			{
				if ($export)
					return $rowdata[6];
				
				return $rowdata[6];
//				return "<a target='_new' href='/inventory/po/itemseries/POID/".$rowdata[13]."/POItemsID/".$rowdata[14]."/ItemID/".$rowdata[1]."/fromlisting/1'>".$rowdata[6]."</a>";
			}	
			
			function format_purchasedate($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_date_db_to_simple($rowdata[15]);
			}
			function format_branch($colnum, $rowdata)
			{
				return $rowdata[16];
			}						
			function format_itemname($colnum, $rowdata, $export)
			{
				if ($export)
					return $rowdata[8] ." (".$rowdata[9] . ")";
				
				return $rowdata[8] ." (".$rowdata[9] . ")";
//				return  "<a target='_new' href='/inventory/brand/item/edit_item/".$rowdata[1] ."'>".$rowdata[8] ." (".$rowdata[9] . ")</a>";
			}
			function format_action($colnum, $rowdata, $export)
			{
				if ($export)
					return "";
			
				$systemSetting = new Zend_Session_Namespace('systemSetting');		
				if ($systemSetting->userInfo->ACLRole == "User")
					return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/flow'><img border=0 src='/images/icons/IconView.png'></a>";
				else
					return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/flow'><img border=0 src='/images/icons/IconEdit.gif'></a>";
			}		
			function format_status($colnum, $rowdata, $export)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				$dispFormat = new Venz_App_Display_Format();
				if ($rowdata[17]){
					if ($export)
						return  $systemSetting->arrStockStatus[$rowdata[17]].($rowdata[17] == "indent" ? "<BR>Expected Date: ".$dispFormat->format_date_db_to_simple($rowdata[22]) : "") ;
					else
						return  $systemSetting->arrStockStatus[$rowdata[17]].($rowdata[17] == "indent" ? "<BR><img src='/images/icons/delivery.png'>".$dispFormat->format_date_db_to_simple($rowdata[22]) : "") ;
				}
			}
			function format_image($colnum, $rowdata)
			{
				if ($rowdata[21])
					return "<img src='".$rowdata[21]."' style='height:auto; width:auto; max-height:75px; padding-bottom:0px;'>";
				else
					return "";
			}

			function format_notes($colnum, $rowdata)
			{
				$db = Zend_Db_Table::getDefaultAdapter(); 
				if ($rowdata[11])
				{
					$arrNotes = $db->fetchRow("SELECT Notes FROM ItemSeriesStatus WHERE ItemSeriesID=".$rowdata[11]." AND ItemSeriesStatus.Notes IS NOT NULL ORDER BY StatusDate Desc, EntryDateTime Desc");
					return $arrNotes[0];
				}else
					return "";
			}
			
			
			$sessionInlineMarkup = new Zend_Session_Namespace('sessionInlineMarkup');
			$sessionInlineMarkup->jsInline = "";
			function format_markup($colnum, $rowdata, $export)
			{
				$sessionInlineMarkup = new Zend_Session_Namespace('sessionInlineMarkup');
				$dispFormat = new Venz_App_Display_Format();


				if ($export){
                    if ($rowdata[18])
                        return $rowdata[18]."%";
                    else
                        return "";
                }

				if ($rowdata[18] && !$rowdata[0])
				{
					$sessionInlineMarkup->jsInline .= "$('#markup_".$rowdata[11]."').editable({success: function (res, val){ updateUnitRetail(".$rowdata[11].", 'markup', res);}});";
					$edit = '<a href="#" id="markup_'.$rowdata[11].'" data-type="text" data-pk="1" data-url="/inventory/brand/ajaxMarkup/id/'.$rowdata[11].'" data-title="Enter markup"><img width="15px" src="/images/icons/IconEdit2.png"> '.$rowdata[18]."%".'</a>';
					return $edit;
				}else if ($rowdata[18])
				{
					return $rowdata[18]."%";
				}
				else if (!$rowdata[0])
				{
					$sessionInlineMarkup->jsInline .= "$('#markup_".$rowdata[11]."').editable({success: function (res, val){ updateUnitRetail(".$rowdata[11].", 'markup', res);}});";
					return '<a href="#" id="markup_'.$rowdata[11].'" data-type="text" data-pk="1" data-url="/inventory/brand/ajaxMarkup/id/'.$rowdata[11].'" data-title="Enter markup"><img src="/images/icons/IconEdit2.png"></a>';

				}else
					return "";
			
			}

            function format_checkbox($colnum, $rowdata)
            {
                return "<input type='checkbox' ItemID='".$rowdata[25]."' name='ItemSeriesID[]' id='SelectItemSeries' class='SelectItemSeries' value='".$rowdata[11]."'>";

            }
			
			$exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));	
			
			if ($this->userInfo->ACLRole == "User"){
				$arrHeader = array ('#', 'PO', $this->translate->_('Item Name (Model Name)'),$this->translate->_('Part Number'), $this->translate->_('Serial Number'), $this->translate->_('Branch'),$this->translate->_('Status'),$this->translate->_('Notes'),$this->translate->_('View'));
				$arrFormat = array('{format_counter}','%7%', '{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_branch}','{format_status}','{format_notes}', '{format_action}');
				$tablewidth = "1050px";
				$aligndata = "CRLLCCLC";
				$export = "";
			
			}else
			{
				$arrHeader = array ('<input type=checkbox name="SelectAllItemSeries" id="SelectAllItemSeries">','#', 'PO',$this->translate->_('Item Name (Model Name)'),$this->translate->_('Part Number'),$this->translate->_('Serial Number'), $this->translate->_('Branch'),$this->translate->_('Unit Price'),$this->translate->_('Landed Cost'),$this->translate->_('Markup %'),$this->translate->_('Unit Retail'),$this->translate->_('Status'),$this->translate->_('Notes'),$this->translate->_('Edit'));
				$arrFormat = array('{format_checkbox}','{format_counter}','%7%','{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_branch}','{format_unitprice}', '{format_unitlandedcost}', '{format_markup}','{format_retailprice}','{format_status}','{format_notes}', '{format_action}');
				$tablewidth = "1550px";
				$aligndata = "CCRLLCRRRRCLC";
				$export = $exportReport->display_icon();
				
			}
		$arrHeaderEx = array ('#','PO', $this->translate->_('Item Name (Model Name)'),$this->translate->_('Part Number'),$this->translate->_('Serial Number'), $this->translate->_('Branch'),$this->translate->_('Unit Price'),$this->translate->_('Landed Cost'),$this->translate->_('Markup %'),$this->translate->_('Unit Retail'),$this->translate->_('Notes'),$this->translate->_('Status'));
			$arrFormatEx = array('{format_counter}','%7%','{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_branch}','{format_unitprice}', '{format_unitlandedcost}', '{format_markup}','{format_retailprice}','{format_notes}','{format_status}');
			
			$this->view->totalItems = $arrItem[0];

			$strUpdateButton = "<BR><input type=button name='UpdateStatus' id='UpdateStatus' value='Update Status' disabled>";
			
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> $arrFormat,					 
					 'sort_column' 	=> array('','','PurchaseOrders.OrderNumber','ItemFullName','Item.PartNumber','ItemSeries.SeriesNumber','BranchName','ItemSeries.UnitPriceRM','ItemSeries.UnitLandedCost','ItemSeries.MarkupPercent', 'Item.RetailPrice',  'ItemSeries.Status', 'LatestNotes.Notes', ''),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Items Series').": ". $arrItem[0]." ".$this->translate->_('items').$strUpdateButton,
					 'aligndata' 	=> $aligndata,
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => $tablewidth,
					 'export_excel' => $export,
			         'sortby' => $sortby,
					 'colparam'      => array("","","nowrap","width='300px;'","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap width='125px'","","nowrap"),
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_item = $displayTable->render();
			$sessionItemCounter->numCounter = 0;
			
			$export_excel_x = $Request->getParam('export_excel_x');						
			if ($export_excel_x)
			{

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$exportsql = $Request->getParam('exportsql');	
				$exportReport = new Venz_App_Report_Excel(array('exportsql'=> base64_decode($exportsql), 'db'=>$db, 'headings'=>$arrHeaderEx, 'format'=>$arrFormatEx));	
				$exportReport->render();
				
			}
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }	
	
	public function getitemserieslistAction()
	{

		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			$dispFormat = new Venz_App_Display_Format();
			$layout = $this->_helper->layout();
			$layout->setLayout("ajax");	
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				exit();
			}			
			
			$ItemID = $Request->getParam('itemID');	
			$Quantity = $Request->getParam('quantity');	
			$SOItemID = $Request->getParam('SOItemID');	
			
			$this->view->ItemID = $ItemID;
			$this->view->Quantity = $Quantity;
			$this->view->SOItemID = $SOItemID;
			
			
			$arrItemDetail = $libInv->getItemDetail($ItemID);
			$this->view->ItemName = $arrItemDetail['ItemName'];
			$this->view->ModelNumber = $arrItemDetail['ModelNumber'];
			
			$qtyCount = new Zend_Session_Namespace('qtyCount');		
			$qtyCount->num = $Quantity;
			
			//print $ItemID . "--".		$quantity;
			
		
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'ItemSeries.ID';
				
			$ascdesc = $Request->getParam('ascdesc');			
			if (strlen($ascdesc) == 0) $ascdesc = 'desc'; 
			
			$showPage = $Request->getParam('Pagerpagenum');			
			if (!$showPage) $showPage = 1; 
				
			$pagerNext = $Request->getParam('Pager_next_page');			
			if (strlen($pagerNext) > 0) $showPage++; 	

			$pagerPrev = $Request->getParam('Pager_prev_page');			
			if (strlen($pagerPrev) > 0) $showPage--; 	
			
			$recordsPerPage = 1000 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$search_series = $Request->getParam('search_series');
			$clear_search = $Request->getParam('clear_search');			
			$this->view->searchSeries = false;

			$sqlSearchSelected = "";
			$sqlSearchSelected .= " and ItemSeries.ItemID = ".$ItemID;
			$sqlSearchSelected .= " and (ItemSeries.Status = 'reserved' || ItemSeries.Status = 'sold')";
			$sqlSearchSelected .= " and ItemSeries.SOItemsID = ".$SOItemID;

			$sqlSearch .= " and ItemSeries.ItemID = ".$ItemID;
			$sqlSearch .= " and ItemSeries.Status = 'in'";

			$strHiddenSearch = "";
			if ($search_series)
			{
				$this->view->searchSeries = true;
				$SeriesNumber = $Request->getParam('SeriesNumber');	
				$SaerchBranchID = $Request->getParam('SaerchBranchID');	
				
				$sqlSearch .= $SeriesNumber ? " and ItemSeries.SeriesNumber LIKE '%".$SeriesNumber ."%'" : "";
				$sqlSearch .= $SaerchBranchID ? " and ItemSeries.BranchID = ".$SaerchBranchID : "";
			
			}
			
			
			$select_series = $Request->getParam('select_series');
			$checkitemseries = $Request->getParam('checkitemseries');
			if ($select_series)
			{
				foreach ($checkitemseries as $ItemSeriesID => $val)
				{
					$sql = "UPDATE ItemSeries Set SOItemsID=".$this->view->SOItemID.", Status='reserved' where ID=".$ItemSeriesID;
					$db->query($sql);
				}
				
			}
			
			$remove_series = $Request->getParam('remove_series');
			$checkitemseries_selected = $Request->getParam('checkitemseries_selected');
			if ($remove_series)
			{
				foreach ($checkitemseries_selected as $ItemSeriesID => $val)
				{
					$sql = "UPDATE ItemSeries Set SOItemsID=NULL, Status='in' where ID=".$ItemSeriesID;
					$db->query($sql);
				}
	
			}	

			if ($select_series || $remove_series)
			{
				$arrTotal = $db->fetchRow("SELECT count(*) as Total From ItemSeries where SOItemsID=".$this->view->SOItemID." and Status='reserved'");
				$arrUpdate = array("Quantity"=>$arrTotal['Total'],"UnitPrice"=>new Zend_Db_Expr("NULL"), "UnitTotal"=>new Zend_Db_Expr("NULL"), "UnitTotalCurrency"=>new Zend_Db_Expr("NULL"), 
				"UnitDiscount"=>new Zend_Db_Expr("NULL"), "UnitDiscountType"=>new Zend_Db_Expr("NULL"), "SubTotal"=>new Zend_Db_Expr("NULL"));
				$db->Update("SOItems", $arrUpdate, "ID=".$this->view->SOItemID);

			}
			
			
			
			//print $sqlSearch; exit();
			$this->view->SeriesNumber = $SeriesNumber ? $SeriesNumber : "";	
			$this->view->BranchID = $SaerchBranchID ? $SaerchBranchID : "";					
			
			$strHiddenSearch = "<input type=hidden name='search_series' value='true'>";
			$strHiddenSearch .= "<input type=hidden name='SeriesNumber' value='".$SeriesNumber."'>";
			$strHiddenSearch .= "<input type=hidden name='BranchID' value='".$SaerchBranchID."'>";
			$strSelectButton = "<input style='margin-bottom: 5px; padding-top: 1px; padding-bottom: 1px; font-size:10px;' type=button name='SelectSeries1' id='SelectSeries1' value='".$systemSetting->translate->_("Select")."'>";
			$strSelectButton2 = "<input style='margin-top: 5px; padding-top: 1px; padding-bottom: 1px; font-size:10px;'  type=button name='SelectSeries2' id='SelectSeries2' value='".$systemSetting->translate->_("Select")."'><BR><BR>";
			
			
			$strSelectButtonRemove = "<input style='margin-bottom: 5px;padding-top: 1px; padding-bottom: 1px; font-size:10px;'  type=button name='RemoveSeries' id='RemoveSeries' value='".$systemSetting->translate->_("Remove")."'>";
			
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 


			$libInv->setFetchMode(Zend_Db::FETCH_NUM);
			$arrItem = $libInv->getItemsSeries($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataItem = $arrItem[1];
			
			$arrItemSelected = $libInv->getItemsSeries($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearchSelected);
			$dataItemSelected = $arrItemSelected[1];
			$this->view->QuantitySelected = $arrItemSelected[0];
			/// already selected all the quantity
			$sessionItemAllSelected = new Zend_Session_Namespace('sessionItemAllSelected');
			$this->view->itemLeft = $this->view->Quantity;
			if ($arrItemSelected[0] >= $this->view->Quantity)
				$sessionItemAllSelected->selected = true;
			else{
				$sessionItemAllSelected->selected = false;
				$this->view->itemLeft = ($this->view->Quantity - $arrItemSelected[0]);
			}
			
			if ($arrItem[0] == 0)
			{
				$strSelectButton = "";
			}

			
			if ($arrItemSelected[0] == 0)
			{
				$strSelectButtonRemove = "";
			}
			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			

			$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
			$sessionItemCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
				$sessionItemCounter->numCounter++;
				return $sessionItemCounter->numCounter;
			}		

			function format_unitprice($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[2]);
			}		
			function format_unitdeliverycost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[3]);
			}
			function format_unittaxcost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[4]);
			}				
			function format_unitlandedcost($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[5]);
			}
			function format_retailprice($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[20]);
			}
			$selectedTotalRetail = new Zend_Session_Namespace('selectedTotalRetail');
			$selectedTotalRetail->totalRetail = 0;
			function format_retailprice_selected($colnum, $rowdata)
			{
				$selectedTotalRetail = new Zend_Session_Namespace('selectedTotalRetail');
				$selectedTotalRetail->totalRetail += $rowdata[20];
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[20]);
			}
			function format_pono($colnum, $rowdata)
			{
				return "<a target='_new' href='/inventory/po/index/edit_po/".$rowdata[13]."'>".$rowdata[7]."</a>";
			}
			
			function format_seriesnumber($colnum, $rowdata)
			{
				return "<a target='_new' href='/inventory/po/itemseries/POID/".$rowdata[13]."/POItemsID/".$rowdata[14]."/ItemID/".$rowdata[1]."/fromlisting/1'>".$rowdata[6]."</a>";
			}	
			
			function format_purchasedate($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_date_db_to_simple($rowdata[15]);
			}
			
			function format_branch($colnum, $rowdata)
			{
				return $rowdata[16];
			}
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');		
				if ($systemSetting->userInfo->ACLRole == "User")
					return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/flow'><img border=0 src='/images/icons/IconView.png'></a>";
				else
					return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/flow'><img border=0 src='/images/icons/IconEdit.gif'></a>";
			}
			
			function format_status($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				return  $systemSetting->arrStockStatus[$rowdata[17]];
			}

			function format_markup($colnum, $rowdata)
			{
				return $rowdata[18]."%";
			}	
			
			if ($arrItemSelected[0])
				$qtyCount->num -= $arrItemSelected[0];
				
			function format_checkbox($colnum, $rowdata)
			{
//				$sessionItemAllSelected = new Zend_Session_Namespace('sessionItemAllSelected');
			
//				$qtyCount = new Zend_Session_Namespace('qtyCount');		
//				$checked = "";
//				if ($qtyCount->num > 0)
//					$checked = "checked";
//				$disabled = "";	
//				if ($sessionItemAllSelected->selected)	
//					$disabled = "disabled";	
//					
//				$qtyCount->num--;
				$disabled = "";
				return '<input '.$disabled.' '.$checked.' type=checkbox name="checkitemseries['.$rowdata[11].']" id="checkitemseries" value="1">';
			}			
			function format_checkboxselected($colnum, $rowdata)
			{
				return '<input  '.$checked.' type=checkbox name="checkitemseries_selected['.$rowdata[11].']" id="checkitemseries_selected" value="1">';
			}		
			$disabled = "";	
//			if ($sessionItemAllSelected->selected)	
//				$disabled = "disabled";				
			$arrHeader = array ('#', '<input '.$disabled.' type=checkbox name="checkall" id="checkall">', $this->translate->_('Serial Number'), $this->translate->_('Branch'),$this->translate->_('Unit Price'),$this->translate->_('Landed Cost'),$this->translate->_('Markup %'),$this->translate->_('Unit Retail'),$this->translate->_('Status'));
			$arrHeaderSelected = array ('#', '<input type=checkbox name="checkall_selected" id="checkall_selected">', $this->translate->_('Serial Number'), $this->translate->_('Branch'),$this->translate->_('Unit Price'),$this->translate->_('Landed Cost'),$this->translate->_('Markup %'),$this->translate->_('Unit Retail'),$this->translate->_('Status'));
			
			$arrFormat = array('{format_counter}','{format_checkbox}','{format_seriesnumber}','{format_branch}','{format_unitprice}', '{format_unitlandedcost}', '{format_markup}','{format_retailprice}','{format_status}');
			$arrFormatSelected = array('{format_counter}','{format_checkboxselected}','{format_seriesnumber}','{format_branch}','{format_unitprice}', '{format_unitlandedcost}', '{format_markup}','{format_retailprice_selected}','{format_status}');
			
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch.$strSelectButton,
					 'headings' => $arrHeader,
					 'formname' => 'seriesform',
					 'format' 		=> $arrFormat,					 
					 'sort_column' 	=> array('','','ItemSeries.SeriesNumber','BranchName','ItemSeries.UnitPrice','ItemSeries.UnitLandedCost','ItemSeries.Markup', 'Item.RetailPrice',  'ItemSeries.Status'),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_(''),					 
					 'aligndata' 	=> 'CCLCRRRRCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "800px",
			         'sortby' => $sortby,
					 'colparam'      => array("","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap"),
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch
				)
			);
			$this->view->content_item = $displayTable->render();
			$sessionItemCounter->numCounter = 0;
			
			$displayTableSelected = new Venz_App_Display_Table(
				array (
			         'data' => $dataItemSelected,
					 'hiddenparamtop'=> $strSearch.$strSelectButtonRemove,
					 'headings' => $arrHeaderSelected,
					 'formname' => 'seriesformselected',
					 'format' 		=> $arrFormatSelected,					 
					 'sort_column' 	=> array('','','ItemSeries.SeriesNumber','BranchName','ItemSeries.UnitPrice','ItemSeries.UnitLandedCost','ItemSeries.Markup', 'Item.RetailPrice',  'ItemSeries.Status'),
					 'alllen' 		=> $arrItemSelected[0],
					 'title'		=> $this->translate->_('Selected Item: ').$this->view->QuantitySelected,					 
					 'aligndata' 	=> 'CCLCRRRRCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeaderSelected),
			         'tablewidth' => "800px",
			         'sortby' => $sortby,
					 'colparam'      => array("","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap"),
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch
				)
			);
			$this->view->content_itemselected = $displayTableSelected->render();
			$totalRetail = $dispFormat->format_currency($selectedTotalRetail->totalRetail);
			$unitRetail = $dispFormat->format_currency($selectedTotalRetail->totalRetail / $arrItemSelected[0]);
			
			$this->view->content_itemselected .= $this->translate->_('Total Retail').": ".$totalRetail."<BR>";
			$this->view->content_itemselected .= $this->translate->_('Ave. Unit Retail').": ".$unitRetail."<BR>";
			
						//$selectedTotalRetail = new Zend_Session_Namespace('selectedTotalRetail');

			
			if ($search_series)
			{

				echo $this->view->content_itemselected;
				echo $this->view->content_item;
				exit();
			}
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	}
	
	
	public function stockcountAction()
    {
	
	
		try {
			$Request = $this->getRequest();			
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$libDb = new Venz_App_Db_Table();
			$dispFormat = new Venz_App_Display_Format();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$sessionUserInfo = new Zend_Session_Namespace('sessionUserInfo');
			$sessionUserInfo->userInfo = $this->userInfo;
			
			$sortby = $Request->getParam('sortby');			
			if (strlen($sortby) == 0) $sortby = 'POItems.ID';
				
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
			$search_series = $Request->getParam('search_series');
			$clear_search = $Request->getParam('clear_search');			
			$this->view->searchSeries = false;
			$strHiddenSearch = "";
		
			
		
			if ($search_series)
			{
				$this->view->searchSeries = true;
				$SearchString = $Request->getParam('SearchString');	
				$BranchID = $Request->getParam('BranchID');
				$Status = $Request->getParam('Status');
				$StatusCurrent = $Request->getParam('StatusCurrent');
				$AsOffDate = $Request->getParam('AsOffDate');

			
				setcookie('FlowSearchString', $SearchString, time() + (3600*30),"/"); 
				setcookie('FlowBranchID', $BranchID, time() + (3600*30),"/"); 
				setcookie('FlowStatus', $Status, time() + (3600*30),"/"); 
				setcookie('FlowStatusCurrent', $StatusCurrent, time() + (3600*30),"/"); 
				setcookie('FlowAsOffDate', $AsOffDate, time() + (3600*30),"/"); 
				
				
			}else
			{
				if ($clear_search)
				{
					setcookie('FlowSearchString',"", time()-3600, "/"); unset($_COOKIE['FlowSearchString']);
					setcookie('FlowBranchID', "", time()-3600, "/"); unset($_COOKIE['FlowBranchID']);
					setcookie('FlowStatus', "", time()-3600, "/"); 	unset($_COOKIE['FlowStatus']);	
					setcookie('FlowStatusCurrent', "", time()-3600, "/"); 	unset($_COOKIE['FlowStatusCurrent']);	
					setcookie('FlowAsOffDate', "", time()-3600, "/"); 	unset($_COOKIE['FlowAsOffDate']);	
				
				}
				else
				{
					$SearchString = $_COOKIE['FlowSearchString'];	
					$BranchID = $_COOKIE['FlowBranchID'];
					$Status = $_COOKIE['FlowStatus'];
					$StatusCurrent = $_COOKIE['FlowStatusCurrent'];
					$AsOffDate = $_COOKIE['FlowAsOffDate'];
				}
			}			

			$sqlSearch .= $SearchString ? " and (Item.ItemName LIKE '".addslashes('%' . trim($SearchString) .'%'). "' OR Item.ModelNumber LIKE '".addslashes('%' .trim($SearchString) . '%') . "' OR Item.PartNumber LIKE '".addslashes('%' . trim($SearchString) . '%')."')" : "";
			$sqlSearch .= $BranchID ? " and ItemSeries.BranchID = ".$BranchID : "";
			$sqlSearch .= $Status ? " and LatestStatus.Status = '".$Status."'" : "";
			$sqlSearch .= $StatusCurrent ? " and ItemSeries.Status = '".$StatusCurrent."'" : "";
			$sqlAsOffDate .= $AsOffDate ? $dispFormat->format_date_simple_to_db($AsOffDate)." 23:59:59" : strftime("Y-m-d", time());
			
			$this->view->SearchString = $SearchString ? $SearchString : "";				
			$this->view->BranchID = $BranchID ? $BranchID : "";					
			$this->view->Status = $Status ? $Status : "";	
			$this->view->StatusCurrent = $StatusCurrent ? $StatusCurrent : "";	
			$this->view->AsOffDate = $AsOffDate ? $AsOffDate : "";	

			
			
			$strHiddenSearch = "<input type=hidden name='search_series' value='true'>";
			$strHiddenSearch .= "<input type=hidden name='SearchString' value='".$SearchString."'>";
			$strHiddenSearch .= "<input type=hidden name='BranchID' value='".$BranchID."'>";
			$strHiddenSearch .= "<input type=hidden name='Status' value='".$Status."'>";
			$strHiddenSearch .= "<input type=hidden name='StatusCurrent' value='".$StatusCurrent."'>";
			$strHiddenSearch .= "<input type=hidden name='AsOffDate' value='".$AsOffDate."'>";

			
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			$this->view->optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $this->view->Status); 			
			$this->view->optionStatusItemCurrent = $libDb->getSystemOptions("arrStockStatus", $this->view->StatusCurrent); 			
				
			$libInv->setFetchMode(Zend_Db::FETCH_NUM);
			$arrItem = $libInv->getItemsSeriesStockCount($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch, $sqlAsOffDate);
			
			$dataItem = $arrItem[1];
			$exportSql = $arrItem[2];


			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			

			$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
			$sessionItemCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
				$sessionItemCounter->numCounter++;
				return $sessionItemCounter->numCounter;
			}	
			
			function format_pono($colnum, $rowdata)
			{
				return "<a target='_new' href='/inventory/po/index/edit_po/".$rowdata[13]."'>".$rowdata[7]."</a>";
			}

            function format_partnumber($colnum, $rowdata, $export)
            {
                return $rowdata[28];
            }

            function format_seriesnumber($colnum, $rowdata, $export)
			{
				return $rowdata[6];
			}
			
			function format_purchasedate($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_date_db_to_simple($rowdata[15]);
			}
			function format_branch($colnum, $rowdata)
			{
				return $rowdata[16];
			}						
			function format_itemname($colnum, $rowdata, $export)
			{
//				if ($export)
//					return "'".$rowdata[8] ." (".$rowdata[9] . ")"."'";
				
				return $rowdata[8] ." (".$rowdata[9] . ")";
//				return  "<a target='_new' href='/inventory/brand/item/edit_item/".$rowdata[1] ."'>".$rowdata[8] ." (".$rowdata[9] . ")</a>";
			}
			function format_action($colnum, $rowdata, $export)
			{
				if ($export)
					return "";
			
				$systemSetting = new Zend_Session_Namespace('systemSetting');		
				if ($systemSetting->userInfo->ACLRole == "User")
					return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/flow'><img border=0 src='/images/icons/IconView.png'></a>";
				else
					return "<a href='/inventory/brand/itemseriesdetail/id/".$rowdata[11]."/f/flow'><img border=0 src='/images/icons/IconEdit.gif'></a>";
			}		
			function format_status($colnum, $rowdata, $export)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				$dispFormat = new Venz_App_Display_Format();
				if ($rowdata[17]){
					if ($export)
						return  $systemSetting->arrStockStatus[$rowdata[17]].($rowdata[17] == "indent" ? "<BR>Expected Date: ".$dispFormat->format_date_db_to_simple($rowdata[22]) : "") ;
					else
						return  $systemSetting->arrStockStatus[$rowdata[17]].($rowdata[17] == "indent" ? "<BR><img src='/images/icons/delivery.png'>".$dispFormat->format_date_db_to_simple($rowdata[22]) : "") ;
				}
			}

            function format_status_asoff($colnum, $rowdata, $export)
            {
                $systemSetting = new Zend_Session_Namespace('systemSetting');
                $dispFormat = new Venz_App_Display_Format();
                if ($rowdata[25]){
                    if ($export)
                        return  $systemSetting->arrStockStatus[$rowdata[25]];
                    else
                        return  $systemSetting->arrStockStatus[$rowdata[25]];
                }
            }

			function format_landedcost($colnum, $rowdata, $export)
			{
			    if ($export)
			        return $rowdata[5];

				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[5]);
			}


            function format_status_date($colnum, $rowdata, $export)
            {

                $dispFormat = new Venz_App_Display_Format();
                return $dispFormat->format_date($rowdata[26], " ");
            }

            function format_status_days($colnum, $rowdata, $export)
            {
                return $rowdata[27]." days";
            }

            $exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));

			if ($Status && $AsOffDate){
                $arrHeader = array ('#', 'PO', $this->translate->_('Item Name'),$this->translate->_('Part Number'), $this->translate->_('Serial Number'), $this->translate->_('Landed Cost'), $this->translate->_('Branch'),$this->translate->_('Current<BR>Item Status'), $this->translate->_('Item Status as Off<BR>').$AsOffDate, $this->translate->_('Date of Stock Entry'),$this->translate->_('Days with status'),$this->translate->_('Notes'),$this->translate->_('View'));
                $arrFormat = array('{format_counter}','%7%', '{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_landedcost}','{format_branch}','{format_status}','{format_status_asoff}','{format_status_date}','{format_status_days}','%23%', '{format_action}');
                $arrSort = array('','PurchaseOrders.OrderNumber','ItemFullName','Item.PartNumber','ItemSeries.SeriesNumber','ItemSeries.UnitLandedCost','BranchName','ItemSeries.Status','LatestStatus.Status', 'LatestStatus.StatusDate', 'StatusDays', 'LatestStatus.Notes', '');
                $tablewidth = "1450px";
                $aligndata = "CRLLRCCCCCLC";
                $arrHeaderEx = array ('#','PO', $this->translate->_('Item Name'),$this->translate->_('Part Number'), $this->translate->_('Serial Number'), $this->translate->_('Landed Cost'), $this->translate->_('Branch'),$this->translate->_('Notes'),$this->translate->_('Current Item Status'), $this->translate->_('Item Status as Off').$AsOffDate, $this->translate->_('Date of Stock Entry'), $this->translate->_('Days with status'));
                $arrFormatEx = array('{format_counter}','%7%','{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_landedcost}','{format_branch}','%23%','{format_status}','{format_status_asoff}','{format_status_date}','{format_status_days}');

            }else{
                $arrHeader = array ('#', 'PO', $this->translate->_('Item Name'),$this->translate->_('Part Number'), $this->translate->_('Serial Number'), $this->translate->_('Landed Cost'), $this->translate->_('Branch'),$this->translate->_('Current<BR>Item Status'),$this->translate->_('Notes'),$this->translate->_('View'));
                $arrFormat = array('{format_counter}','%7%', '{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_landedcost}','{format_branch}','{format_status}','%23%', '{format_action}');
                $arrSort = array('','PurchaseOrders.OrderNumber','ItemFullName','Item.PartNumber','ItemSeries.SeriesNumber','ItemSeries.UnitLandedCost','BranchName','ItemSeries.Status','LatestStatus.Notes', '');
                $tablewidth = "1250px";
                $aligndata = "CRLLRCCLC";
                $arrHeaderEx = array ('#','PO', $this->translate->_('Item Name'),$this->translate->_('Part Number'), $this->translate->_('Serial Number'), $this->translate->_('Landed Cost'), $this->translate->_('Branch'),$this->translate->_('Notes'),$this->translate->_('Current Item Status'));
                $arrFormatEx = array('{format_counter}','%7%','{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_landedcost}','{format_branch}','%23%','{format_status}');

            }

			/*
			$exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));	
			$arrHeader = array ('#', 'PO', $this->translate->_('Item Name'),$this->translate->_('Serial Number'), $this->translate->_('Landed Cost'), $this->translate->_('Branch'),$this->translate->_('Current<BR>Item Status'),$this->translate->_('Notes'),$this->translate->_('View'));
			$arrFormat = array('{format_counter}','%7%', '{format_itemname}','{format_seriesnumber}','{format_landedcost}','{format_branch}','{format_status}','%23%', '{format_action}');
			$tablewidth = "1050px";
			$aligndata = "CRLLRCCLC";
            */

			$export = $exportReport->display_icon();


			$this->view->totalItems = $arrItem[0];
			
			
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> $arrFormat,					 
					 'sort_column' 	=> $arrSort,
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Items Series').": ". $arrItem[0]." ".$this->translate->_('items'),					 
					 'aligndata' 	=> $aligndata,
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => $tablewidth,
					 'export_excel' => $export,
			         'sortby' => $sortby,
					 'colparam'      => array("","","nowrap","width='300px;'","nowrap","nowrap","nowrap","nowrap width='125px'","","nowrap"),
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_item = $displayTable->render();
			$sessionItemCounter->numCounter = 0;
			
			$export_excel_x = $Request->getParam('export_excel_x');						
			if ($export_excel_x)
			{

				$db = Zend_Db_Table::getDefaultAdapter(); 
				$exportsql = $Request->getParam('exportsql');	
				$exportReport = new Venz_App_Report_Excel(array('exportsql'=> base64_decode($exportsql), 'db'=>$db, 'headings'=>$arrHeaderEx, 'format'=>$arrFormatEx));	
				$exportReport->render();
				
			}
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }


    public function getReportAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $arrItemSeriesAll = $db->fetchAll("SELECT * FROM ItemSeries order by ID");

        $couter = 0;
        print "<table border=1><TR><TD>#</TD><TD>Item Series ID</TD><TD>Item Series Current Status</TD><TD>Latest Historical Status</TD>";
        foreach ($arrItemSeriesAll as $arrItemSeries)
        {
            $arrStatus = $db->fetchRow("SELECT * FROM ItemSeriesStatus WHERE ItemSeriesID=".$arrItemSeries['ID']." ORDER BY StatusDate DESC, EntryDateTime DESC LIMIT 1");
            if ($arrItemSeries['Status'] != $arrStatus['Status']){
                $couter++;
                print "<TR><TD>".$couter."</TD><TD>".$arrItemSeries['ID'] . "</TD><TD>" .$arrItemSeries['Status'] . "</TD><TD>" .$arrStatus['Status']."</TD></TR>";
            }

        }
        print "</table>";
        exit();

    }

}

