<?php

class Inventory_SoController extends Venz_Zend_Controller_Action
{

    public function init()
    {
		$actionName = $this->getRequest()->getActionName();
		switch ($actionName){
		case "index" : parent::init("po_create");break;
		case "listing" : parent::init("po_listing");break;
		default: parent::init(NULL);
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
			$dispFormat = new Venz_App_Display_Format();


			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
			$this->view->allowEdit = $sessionUsers->Acl->isAllowed($this->userInfo->ACLRole, "so_edit", "view");
			$this->view->allowEdit = true;
			
			
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];			
			$this->view->currencyTypeID = $systemSetting->currency;			
		
			
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
			
			$recordsPerPage = 10 ;
			////////////////////////////////////////////////////////////////////////////////////////


			$save_so = $Request->getParam('save_so');	
			$create_so = $Request->getParam('create_so');	
			if (($create_so || $save_so) && $this->view->allowEdit)
			{
				if ($save_so)
					$ID = $Request->getParam('save_so_id') ? $Request->getParam('save_so_id') : new Zend_Db_Expr("NULL");
				
				$CustomerID = $Request->getParam('CustomerID') ? $Request->getParam('CustomerID') : new Zend_Db_Expr("NULL");
				$BranchID = $Request->getParam('BranchID') ? $Request->getParam('BranchID') : new Zend_Db_Expr("NULL");
				$OrderNumber = $Request->getParam('OrderNumber') ? $Request->getParam('OrderNumber') : new Zend_Db_Expr("NULL");
				$SalesDate = $Request->getParam('SalesDate') ? $Request->getParam('SalesDate') : new Zend_Db_Expr("NULL");
				$Subtotal = $Request->getParam('Subtotal') ? $Request->getParam('Subtotal') : new Zend_Db_Expr("NULL");
				$Currency = $Request->getParam('Currency') ? $Request->getParam('Currency') : $systemSetting->currency;
				$SubtotalCurrency = $Request->getParam('SubtotalCurrency') ? $Request->getParam('SubtotalCurrency') : new Zend_Db_Expr("NULL");
				$Multiplier = $Request->getParam('Multiplier') ? $Request->getParam('Multiplier') : new Zend_Db_Expr("NULL");
				
				$SODeliveryCharge = $Request->getParam('SODeliveryCharge') ? $Request->getParam('SODeliveryCharge') : new Zend_Db_Expr("NULL");
				$SOTaxCharge = $Request->getParam('SOTaxCharge') ? $Request->getParam('SOTaxCharge') : new Zend_Db_Expr("NULL");
				//print_r($_SERVER); exit();
				
//				$MiscCost = $Request->getParam('MiscCost') ? $Request->getParam('MiscCost') : new Zend_Db_Expr("NULL");
//				$MiscNote = $Request->getParam('MiscNote') ? $Request->getParam('MiscNote') : new Zend_Db_Expr("NULL");
				$Total = $Request->getParam('Total') ? $Request->getParam('Total') : new Zend_Db_Expr("NULL");
				
				$this->view->CustomerID = $CustomerID;	
				$this->view->BranchID = $BranchID;	
				$this->view->OrderNumber = $OrderNumber;	
				$this->view->SalesDate = $SalesDate;	
				$this->view->Subtotal = $Subtotal;	
				$this->view->Currency = $Currency;	
				$this->view->SubtotalCurrency = $SubtotalCurrency;	
				$this->view->Multiplier = $Multiplier;	

				$this->view->SODeliveryCharge = $SODeliveryCharge;	
				$this->view->SOTaxCharge = $SOTaxCharge;	

			
				
			//	$this->view->MiscCost = $MiscCost;				
			//	$this->view->MiscNote = $MiscNote;				
				$this->view->Total = $Total;			
				
				
				
				$errorFile = false;
				
				
				
				if (!$_FILES['SOFile']['error'])
				{
					if ($_FILES['SOFile']['size'] > (5 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, "Please select a file that is less than 5MB in size.");
						$errorFile = true;
						
					}
				}

				if (!$errorFile){
					
					$arrData = array("CustomerID"=>$CustomerID,"BranchID"=>$BranchID,"OrderNumber"=>$OrderNumber,"SalesDate"=>$dispFormat->format_date_simple_to_db($SalesDate),"Subtotal"=>$Subtotal,
					"SODeliveryCharge"=>$SODeliveryCharge, "Currency"=>$Currency,"SubtotalCurrency"=>$SubtotalCurrency,"Multiplier"=>$Multiplier,"SOTaxCharge"=>$SOTaxCharge, 
					"Total"=>$Total);

					if ($save_so){	
						$db->update("SalesOrders", $arrData, "ID=".$ID);					
					}else{
						$db->insert("SalesOrders", $arrData);
						$ID = $db->lastInsertId();
					}
					
					if ($_FILES){
						$arrTemp = explode(".", $_FILES['SOFile']['name']);
						$ext = $arrTemp[sizeof($arrTemp) -1];
						$filename = $ID.".". $ext;
						$relativePath = "/uploads/SOFile/".$filename;
						if (!$_FILES['SOFile']['error'])
						{
							move_uploaded_file($_FILES['SOFile']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePath);
							$arrUpdate = array("SOFilePath"=>$relativePath);
							$db->update("SalesOrders", $arrUpdate, "ID=".$ID);
						}
					}
					
					if ($save_so){	
						$this->appMessage->setNotice(1,$this->translate->_('Details for') . " <B>".$OrderNumber."</B> ".$this->translate->_('has been updated').".");
					}else{
						$this->appMessage->setNotice(1, $this->translate->_('New PO'). " \"<B>".$OrderNumber."</B>\" ".$this->translate->_('has been created').".");
					}
					
					//////  Save all the PO Item details ///////
					$arrItemID = $Request->getParam('ItemID');
					$arrQuantity = $Request->getParam('Quantity');
					$arrUnitPrice = $Request->getParam('UnitPrice');
					$arrUnitTotal = $Request->getParam('UnitTotal');
					$arrUnitTotalCurrency = $Request->getParam('UnitTotalCurrency');
					$arrUnitDiscount = $Request->getParam('UnitDiscount');
					$arrUnitDiscountType = $Request->getParam('UnitDiscountType');
					$arrSubTotal = $Request->getParam('SubTotal');
					
					$arrSOItemsID = $Request->getParam('SOItemsID');
				
					if ($arrSOItemsID){
					
						foreach ($arrSOItemsID as $index => $SOItemsID)
						{
							$arrData = array("ItemID"=>$arrItemID[$index],"UnitPrice"=>$arrUnitPrice[$index],"Quantity"=>$arrQuantity[$index],
							"UnitTotal"=>$arrUnitTotal[$index],"UnitTotalCurrency"=>$arrUnitTotalCurrency[$index],"SubTotal"=>$arrSubTotal[$index],
							"UnitDiscount"=>$arrUnitDiscount[$index],"UnitDiscountType"=>$arrUnitDiscountType[$index],
							"OrderID"=>$ID);
							
							$db->update("SOItems", $arrData, "ID=".$SOItemsID);	
							
						}
					}					
					$this->_redirect('/inventory/so/index/edit_so/'.$ID); 
				}
				
			}
			$this->view->Locked = 0;
			$this->view->edit_so = '';
			$edit_so = $Request->getParam('edit_so');	
			if ($edit_so)
			{
				$this->view->edit_so = $edit_so;
			
				$arrSODetail = $libInv->getSODetail($edit_so);
				
				$this->view->CustomerID = $arrSODetail['CustomerID'];	
				$this->view->BranchID = $arrSODetail['BranchID'];	
				$this->view->SOFilePath = $arrSODetail['SOFilePath'];	
				
				$this->view->Locked = $arrSODetail['Locked'];
				$this->view->LockedBy = $arrSODetail['LockByName'];	
				$this->view->LockedDate = $arrSODetail['LockedDate'];	
				$this->view->disabled = "";
				if ($this->view->Locked)
					$this->view->disabled = "disabled";
				
				if (!$this->view->allowEdit)
					$this->view->disabled = "disabled";
				
				$this->view->OrderNumber = $arrSODetail['OrderNumber'];	
				$this->view->SalesDate = $dispFormat->format_date_db_to_simple($arrSODetail['SalesDate']);	
				$this->view->Subtotal = $arrSODetail['Subtotal'];
				$this->view->SubtotalCurrency = $arrSODetail['SubtotalCurrency'];

				$this->view->Currency = $arrSODetail['Currency'];	
				$this->view->Total = $arrSODetail['Total'];	
				$this->view->Multiplier = $arrSODetail['Multiplier'];	
				$this->view->SOTaxCharge = $arrSODetail['SOTaxCharge'];	
				
				$this->view->SODeliveryCharge = $arrSODetail['SODeliveryCharge'];	
				$this->view->Total = $arrSODetail['Total'];	
				$libInv->setFetchMode();
				$arrItems = $libInv->getSOItems("SalesOrders.ID", "asc", 1000, 1, " AND OrderID=".$edit_so);
				
				$currencyLabel = $this->view->Currency;
				$this->view->currencyTypeID = $this->view->Currency;
				
				$dataItems = $arrItems[1];
				foreach ($dataItems as $arrData)
				{
					$optionItems = $libInv->getItemOptions($arrData['ItemID']);
					$SOItemsID=$arrData['ID'];
					$ItemID=$arrData['ItemID'];
					$Quantity = $arrData['Quantity'];
					$UnitPrice = $arrData['UnitPrice'];
					
					$UnitDiscount = $arrData['UnitDiscount'];
					$UnitDiscountType = $arrData['UnitDiscountType'];
					
					$checkedAmount = ($UnitDiscountType == "$") ? "selected" : "";
					$checkedPercent = ($UnitDiscountType == "%") ? "selected" : "";
					
					
					$SOItemsID = $arrData['ID'];
					$IconDelete = "";
					$disabled = "";
					if ($this->view->Locked)
						$disabled = "disabled";
						
					if (!$this->view->allowEdit)	
						$disabled = "disabled";
					
					if (!$this->view->Locked && $this->view->allowEdit)
						$IconDelete = "<img border=0 src='/images/icons/IconDelete.gif' id='RemoveRowConfirm' name='RemoveRowConfirm'>";
					$systemSetting = new Zend_Session_Namespace('systemSetting');
					$imgEdit = "IconEdit.gif";
					if ($systemSetting->userInfo->ACLRole == "User")	
						$imgEdit = "IconView.png";
					
					$sql = "SELECT count(*) as Total FROM ItemSeries where SOItemsID=".$SOItemsID." and Status='reserved'";
					$arrSeriesSelected = $db->fetchRow($sql);

					$seriesSelected = ">>"; //.$arrSeriesSelected['Total'];
					$ItemsTotalSelected = $arrSeriesSelected['Total'];

//					if ($arrSeriesSelected['Total'] > 0)
//						$seriesSelected = ">>".$arrSeriesSelected['Total'];
					
					$strAddNew = $this->translate->_('Add New');
						$this->view->listItems .= <<<END
			<TR><TD  nowrap class='report_odd' style='text-align:center'><div id="itemcounter"></div></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><SELECT $disabled  Name="ItemID[]" ID="ItemID"><option value=''>-</option>$optionItems<option value='add-new'><<< $strAddNew >>></option></SELECT></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly $disabled  style='text-align:center' size="2" type=text name="Quantity[]" id="Quantity" value="$Quantity">&nbsp;<input style='padding-left: 2px; padding-right: 2px' type='button' size=2 name='SelectSeries[]' id='SelectSeries' value='$seriesSelected'></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input   $disabled style='text-align:right' type='text' name='UnitPrice[]' id='UnitPrice'  size="6"  value="$UnitPrice"></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  readonly $disabled style='text-align:right' type='text' name='UnitTotal[]' id='UnitTotal'  size="6"  value="$UnitTotal"></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='UnitTotalCurrency[]' id='UnitTotalCurrency'  size="6"  value="$UnitTotalCurrency" ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='UnitDiscount[]' id='UnitDiscount'  size="6" value='$UnitDiscount' >
	<SELECT name='UnitDiscountType[]' id='UnitDiscountType'><option value="">-</option><option value="%" $checkedPercent>%</option><option value="$" $checkedAmount>$</option></SELECT></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='SubTotal[]' id='SubTotal'  size="6" value='$SubTotal' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center' width=40px>$IconDelete
	<input type=hidden name="SOItemsID[]" id="SOItemsID" value="$SOItemsID">
	<input type=hidden name="ItemsTotalSelected[]" id="ItemsTotalSelected" value="$ItemsTotalSelected">
	<input type=hidden name="OriginalItemID[]" id="OriginalItemID" value="$ItemID">
	</TD></TR>
		
END;
				}
			}					

			$lock_po = $Request->getParam('lock_po');	
			if ($lock_po && $this->view->allowEdit)
			{
				$ID = $Request->getParam('save_so_id') ? $Request->getParam('save_so_id') : new Zend_Db_Expr("NULL");
				$arrUpdate = array("Locked"=>1,"LockedBy"=>$this->userInfo->ID,"LockedDate"=>new Zend_Db_Expr("now()"));
				$db->update("SalesOrders", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, "The Sales Order has been locked.");
				$this->_redirect('/inventory/so/index/edit_so/'.$ID);   				
			}				
			
			$remove_so = $Request->getParam('remove_so');	
			if ($remove_so && $this->view->allowEdit)
			{
				$arrSODetail = $libInv->getSODetail($remove_so);
			
				$db->delete("SalesOrders", "ID=".$remove_so);
				$this->appMessage->setNotice(1, "The Sales Order has been removed.");
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrSODetail['POFilePath']);
				$this->_redirect('/inventory/so');   				
			}			
		
			$remove_file = $Request->getParam('remove_file');	
			if ($remove_file && $this->view->allowEdit)
			{
				$ID = $Request->getParam('save_so_id') ? $Request->getParam('save_so_id') : new Zend_Db_Expr("NULL");
				$arrSODetail = $libInv->getSODetail($ID);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrSODetail['SOFilePath']);
				$arrUpdate = array("SOFilePath"=>new Zend_Db_Expr("NULL"));
				$db->update("SalesOrders", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, "The Sales Order file has been removed.");
				$this->_redirect('/inventory/so/index/edit_so/'.$ID);   				
			}			
			
			
			$this->view->optionCustomers = $libDb->getTableOptions("Customers", "Name", "ID", $this->view->CustomerID); 
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			$this->view->optionCurrency = $libDb->getSystemOptions("arrCurrency", $this->view->currencyTypeID); 
			
		
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }
		
	public function ajaxremovesoitemsAction()
	{
		$Request = $this->getRequest();	
		$SOItemsID = $Request->getParam('soitemsid');
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$db->delete("SOItems", "ID=".$SOItemsID);

		$arrUpdateItemSeries = array("SOItemsID"=>new Zend_Db_Expr("NULL"), "Status"=>'in');
		$db->Update("ItemSeries", $arrUpdateItemSeries, "SOItemsID=".$SOItemsID);
		exit();
	}
	
	public function ajaxsavesoitemAction()
	{
		$Request = $this->getRequest();	
		$OriginalItemID = $Request->getParam('OriginalItemID');
		$SOItemsID = $Request->getParam('SOItemsID');
		$ItemID = $Request->getParam('ItemID');
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$arrUpdate = array("ItemID"=>$ItemID);
		$db->Update("SOItems", $arrUpdate, "ID=".$SOItemsID);
		
		$arrUpdateItemSeries = array("SOItemsID"=>new Zend_Db_Expr("NULL"), "Status"=>'in');
		//print_r($arrUpdateItemSeries);
		$db->Update("ItemSeries", $arrUpdateItemSeries, "SOItemsID=".$SOItemsID);
	
		$arrUpdateSOItems = array("UnitPrice"=>new Zend_Db_Expr("NULL"), "UnitTotal"=>new Zend_Db_Expr("NULL"), "UnitTotalCurrency"=>new Zend_Db_Expr("NULL"), 
		"Quantity"=>new Zend_Db_Expr("NULL"), "UnitDiscount"=>new Zend_Db_Expr("NULL"), "UnitDiscountType"=>new Zend_Db_Expr("NULL"), "SubTotal"=>new Zend_Db_Expr("NULL"));
		$db->Update("SOItems", $arrUpdateSOItems, "ID=".$SOItemsID);
	
		exit();
		
	}
	

	public function ajaxsavesoitemqAction()
	{
		$Request = $this->getRequest();	
		$SOItemsID = $Request->getParam('SOItemsID');
		$Quantity = $Request->getParam('Quantity');
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$arrUpdate = array("Quantity"=>$Quantity);
		$db->Update("SOItems", $arrUpdate, "ID=".$SOItemsID);
		exit();
		
	}
	
	public function ajaxadditemAction()
	{
		$Request = $this->getRequest();	
		$OrderID = $Request->getParam('orderid');
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libInv = new Venz_App_Inventory_Helper();
		$optionItems = $libInv->getItemOptions();
		$systemSetting = new Zend_Session_Namespace('systemSetting');		
		$markup = $systemSetting->markup;
			
		$arrInsert = array("OrderID"=>$OrderID);
		$db->insert("SOItems", $arrInsert);	
		$SOItemsID = $db->lastInsertId();
		$strAddNew = $this->translate->_('Add New');
		$content = <<<END
			<TR><TD  nowrap class='report_odd' style='text-align:center'><div id="itemcounter"></div></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><SELECT Name="ItemID[]" ID="ItemID"><option value=''>-</option>$optionItems<OPTION value='add-new'><<< $strAddNew >>></OPTION></SELECT></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:center' size="2" type=text name="Quantity[]" id="Quantity">&nbsp;<input style='padding-left: 2px; padding-right: 2px' type='button' size=2 name='SelectSeries' id='SelectSeries' value='>>'></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='UnitPrice[]' id='UnitPrice'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='UnitTotal[]' id='UnitTotal'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='UnitTotalCurrency[]' id='UnitTotalCurrency'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='UnitDiscount[]' id='UnitDiscount'  size="6" value=''>
	<SELECT name='UnitDiscountType[]' id='UnitDiscountType'><option value="">-</option><option value="%">%</option><option value="$">$</option></SELECT>
	</TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='SubTotal[]' id='SubTotal'  size="6" value='' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center' width=40px><img border=0 src='/images/icons/IconDelete.gif' id='RemoveRow' name='RemoveRow'>
	<input type=hidden name="SOItemsID[]" id="SOItemsID" value="$SOItemsID">
	</TD></TR>
		
END;
		
		echo $content;
		exit();
	}
	
	public function listingAction() 
	{
	
	
		try {
			$Request = $this->getRequest();			
			$db = Zend_Db_Table::getDefaultAdapter(); 
			$sysHelper = new Venz_App_System_Helper();
			$libInv = new Venz_App_Inventory_Helper();
			$dispFormat = new Venz_App_Display_Format();
			$libDb = new Venz_App_Db_Table();
			
			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			$systemSetting = new Zend_Session_Namespace('systemSetting');		
			$this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];			
			
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
			$search_so = $Request->getParam('search_so');	
			$this->view->searchSO = false;
			$strHiddenSearch = "";
			if ($search_so)
			{
				$this->view->searchSO = true;
				$CustomerID = $Request->getParam('CustomerID');	
				$BranchID = $Request->getParam('BranchID');	
				$OrderNumber = $Request->getParam('OrderNumber');	
				$SalesDate = $Request->getParam('SalesDate');	
				
				
				$SubtotalRMFrom = $Request->getParam('SubtotalRMFrom');	
				$SubtotalRMTo = $Request->getParam('SubtotalRMTo');					
				$SODeliveryChargeFrom = $Request->getParam('SODeliveryChargeFrom');	
				$SODeliveryChargeTo = $Request->getParam('SODeliveryChargeTo');	
				$SOTaxChargeFrom = $Request->getParam('SOTaxChargeFrom');	
				$SOTaxChargeTo = $Request->getParam('SOTaxChargeTo');	
				$TotalFrom = $Request->getParam('TotalFrom');	
				$TotalTo = $Request->getParam('TotalTo');	
				
				
				$sqlSearch .= $OrderNumber ? " and SalesOrders.OrderNumber LIKE '%".$OrderNumber."%'" : "";
				$sqlSearch .= $CustomerID ? " and SalesOrders.CustomerID =".$CustomerID : "";
				$sqlSearch .= $BranchID ? " and SalesOrders.BranchID =".$BranchID : "";
				$sqlSearch .= $SalesDate ? " and SalesOrders.SalesDate = '".$dispFormat->format_date_simple_to_db($SalesDate)."'" : "";
				
				$sqlSearch .= $SubtotalRMFrom ? " and SalesOrders.SubtotalRM >= ".$SubtotalRMFrom : "";
				$sqlSearch .= $SubtotalRMTo ? " and SalesOrders.SubtotalRM <= ".$SubtotalRMTo : "";
				$sqlSearch .= $SODeliveryChargeFrom ? " and SalesOrders.SODeliveryCharge >= ".$SODeliveryChargeFrom : "";
				$sqlSearch .= $SODeliveryChargeTo ? " and SalesOrders.SODeliveryCharge <= ".$SODeliveryChargeTo : "";
				$sqlSearch .= $SOTaxChargeFrom ? " and SalesOrders.SOTaxCharge >= ".$SOTaxChargeFrom : "";
				$sqlSearch .= $SOTaxChargeTo ? " and SalesOrders.SOTaxCharge <= ".$SOTaxChargeTo : "";
				$sqlSearch .= $TotalFrom ? " and SalesOrders.Total >= ".$TotalFrom : "";
				$sqlSearch .= $TotalTo ? " and SalesOrders.Total <= ".$TotalTo : "";


				
				//print $sqlSearch; exit();
				$this->view->OrderNumber = $OrderNumber ? $OrderNumber : "";				
				$this->view->SalesDate = $SalesDate ? $SalesDate : "";				
				$this->view->CustomerID = $CustomerID ? $CustomerID : "";				
				$this->view->BranchID = $BranchID ? $BranchID : "";				
				
				$this->view->SubtotalRMFrom = $SubtotalRMFrom ? $SubtotalRMFrom : "";				
				$this->view->SubtotalRMTo = $SubtotalRMTo ? $SubtotalRMTo : "";				
				$this->view->SODeliveryChargeFrom = $SODeliveryChargeFrom ? $SODeliveryChargeFrom : "";				
				$this->view->SODeliveryChargeTo = $SODeliveryChargeTo ? $SODeliveryChargeTo : "";				
				$this->view->SOTaxChargeFrom = $SOTaxChargeFrom ? $SOTaxChargeFrom : "";				
				$this->view->SOTaxChargeTo = $SOTaxChargeTo ? $SOTaxChargeTo : "";				
				$this->view->TotalFrom = $TotalFrom ? $TotalFrom : "";				
				$this->view->TotalTo = $TotalTo ? $TotalTo : "";				
				
				$strHiddenSearch = "<input type=hidden name='search_so' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='OrderNumber' value='".$OrderNumber."'>";
				$strHiddenSearch .= "<input type=hidden name='SalesDate' value='".$SalesDate."'>";
				$strHiddenSearch .= "<input type=hidden name='CustomerID' value='".$CustomerID."'>";
				$strHiddenSearch .= "<input type=hidden name='BranchID' value='".$BranchID."'>";

				$strHiddenSearch .= "<input type=hidden name='SubtotalRMFrom' value='".$SubtotalRMFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='SubtotalRMTo' value='".$SubtotalRMTo."'>";
				$strHiddenSearch .= "<input type=hidden name='SODeliveryChargeFrom' value='".$SODeliveryChargeFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='SODeliveryChargeTo' value='".$SODeliveryChargeTo."'>";
				$strHiddenSearch .= "<input type=hidden name='SOTaxChargeFrom' value='".$SOTaxChargeFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='SOTaxChargeTo' value='".$SOTaxChargeTo."'>";
				$strHiddenSearch .= "<input type=hidden name='TotalFrom' value='".$TotalFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='TotalTo' value='".$TotalTo."'>";
			}

			$this->view->optionCustomers = $libDb->getTableOptions("Customers", "Name", "ID", $this->view->CustomerID); 
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			
			$libInv->setFetchMode(Zend_Db::FETCH_NUM);

			$arrItem = $libInv->getSO($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataItem = $arrItem[1];
			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				if ($systemSetting->userInfo->ACLRole == "User")
				return "<a href='/inventory/so/index/edit_so/".$rowdata[0]."'><img border=0 src='/images/icons/IconView.png'></a>";
				else
				return "<a href='/inventory/so/index/edit_so/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a>";
			}		
			
			$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
			$sessionItemCounter->numCounter = $recordsPerPage * ($showPage-1);			
			function format_counter($colnum, $rowdata)
			{
				$sessionItemCounter = new Zend_Session_Namespace('sessionItemCounter');
				$sessionItemCounter->numCounter++;
				return $sessionItemCounter->numCounter;
			}		

			function format_date($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_date_db_to_simple($rowdata[2]);
			}			

			function format_productcost($colnum, $rowdata)
			{
				return format_amount($rowdata[3]);
			}		

			function format_delivery($colnum, $rowdata)
			{
				return format_amount($rowdata[4]);
			}				
			
			function format_tax($colnum, $rowdata)
			{
				return format_amount($rowdata[5]);
			}				
			
			function format_total($colnum, $rowdata)
			{
				return format_amount($rowdata[6]);
			}	
			
			function format_itemcount($colnum, $rowdata)
			{
				return $rowdata[8];
			}
			
			function format_branch($colnum, $rowdata)
			{
				return $rowdata[9];
			}				
			function format_amount($amount)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($amount);
			}
			
			function format_locked($colnum, $rowdata)
			{
				return $rowdata[10] ? "<img src='/images/icons/IconApproved.gif'>" : "<img src='/images/icons/IconExclamation.gif'>" ;
			}		

			
			
			$arrHeader = array ('#', $this->translate->_('Order Number'),$this->translate->_('Branch'), $this->translate->_('Purchase Date'), $this->translate->_('# Items'), $this->translate->_('Product Cost'), $this->translate->_('Delivery Cost'), $this->translate->_('Tax Cost'), $this->translate->_('Final Costing'), $this->translate->_('Locked'), $this->translate->_('View'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','%1%','{format_branch}', '{format_date}','{format_itemcount}', '{format_productcost}', '{format_delivery}', '{format_tax}','{format_total}', '{format_locked}', '{format_action}'),					 
					 'sort_column' 	=> array('','OrderNumber','BranchName','PurchaseDate','TotalItem','ProductCost','PODeliveryCost','POTaxCost', 'TotalCost', 'Locked', ''),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Sales Orders'),					 
					 'aligndata' 	=> 'CCCCCRRRRCC',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "850px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_so = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
	
	}
	
	
				
	function ajaxlocksoAction()
	{
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysAcl = new Venz_App_System_Acl();
		$libDb = new Venz_App_Db_Table();
		$ID = $Request->getParam('ID');			
		$Password = $Request->getParam('Password');	
		$SOID = $Request->getParam('SOID');	
		$arrRow = $db->fetchRow("SELECT * FROM ACLUsers where ID=".$ID." AND Password=MD5('".$Password."')");

		if ($arrRow){
			$arrUpdate = array("Locked"=>1,"LockedBy"=>$arrRow['ID'],"LockedDate"=>new Zend_Db_Expr("now()"));
			$db->update("SalesOrders", $arrUpdate, "ID=".$SOID);
			$arrSOItemsAll = $db->fetchAll("SELECT SOItems.*, SalesOrders.OrderNumber FROM SOItems, SalesOrders WHERE SalesOrders.ID=SOItems.OrderID AND SOItems.OrderID=".$SOID);
			foreach ($arrSOItemsAll as $arrSOItems)
			{
				$db->query("Update ItemSeries SET ItemSeries.Status='sold', SalesOrderNumber='".$arrSOItems['OrderNumber']."' where ItemSeries.SOItemsID=".$arrSOItems['ID']);
				$arrItemSeriesAll = $db->fetchAll("SELECT ItemSeries.* FROM ItemSeries WHERE ItemSeries.SOItemsID=".$arrSOItems['ID']);
				foreach ($arrItemSeriesAll as $arrItemSeries)
				{
					$ReferenceNo = $arrSOItems['OrderNumber'];
					$StatusDate = new Zend_Db_Expr("now()");
					$Status = 'sold';
					$UserIDResp = $this->userInfo->ID;
					$Notes = "Item Sold";
					
					$arrInsert = array("ItemSeriesID"=>$arrItemSeries['ID'],"StatusDate"=>$StatusDate,"Status"=>$Status,"UserIDEntry"=>$this->userInfo->ID,"EntryDateTime"=>new Zend_Db_Expr("now()"),
						"UserIDResp"=>$UserIDResp,"Notes"=>$Notes,"ReferenceNo"=>$ReferenceNo
					);
					$db->insert("ItemSeriesStatus", $arrInsert);
				}
			}
			
			
			
			echo 1;
		}else
			echo 0;
		exit();
			
		
	}


	function ajaxunlocksoAction()
	{
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysAcl = new Venz_App_System_Acl();
		$libDb = new Venz_App_Db_Table();
		$ID = $Request->getParam('ID');			
		$Password = $Request->getParam('Password');	
		$SOID = $Request->getParam('SOID');	
		$arrRow = $db->fetchRow("SELECT * FROM ACLUsers where ID=".$ID." AND Password=MD5('".$Password."')");

		if ($arrRow){
			$arrUpdate = array("Locked"=>0,"LockedBy"=>$arrRow['ID'],"LockedDate"=>new Zend_Db_Expr("now()"));
			$db->update("SalesOrders", $arrUpdate, "ID=".$SOID);
			$arrSOItemsAll = $db->fetchAll("SELECT SOItems.*, SalesOrders.OrderNumber FROM SOItems, SalesOrders WHERE SalesOrders.ID=SOItems.OrderID AND SOItems.OrderID=".$SOID);
			foreach ($arrSOItemsAll as $arrSOItems)
			{
				$db->query("Update ItemSeries SET ItemSeries.Status='reserved', SalesOrderNumber='".$arrSOItems['OrderNumber']."' where ItemSeries.SOItemsID=".$arrSOItems['ID']);
				$arrItemSeriesAll = $db->fetchAll("SELECT ItemSeries.* FROM ItemSeries WHERE ItemSeries.SOItemsID=".$arrSOItems['ID']);
				foreach ($arrItemSeriesAll as $arrItemSeries)
				{
					$ReferenceNo = $arrSOItems['OrderNumber'];
					$StatusDate = new Zend_Db_Expr("now()");
					$Status = 'reserved';
					$UserIDResp = $this->userInfo->ID;
					$Notes = "Sales Order Unlocked";
					
					$arrInsert = array("ItemSeriesID"=>$arrItemSeries['ID'],"StatusDate"=>$StatusDate,"Status"=>$Status,"UserIDEntry"=>$this->userInfo->ID,"EntryDateTime"=>new Zend_Db_Expr("now()"),
						"UserIDResp"=>$UserIDResp,"Notes"=>$Notes,"ReferenceNo"=>$ReferenceNo
					);
					$db->insert("ItemSeriesStatus", $arrInsert);
				}
			}
			echo 1;
		}else
			echo 0;
		exit();
			
		
	}	
	
}

