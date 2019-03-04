<?php

class Inventory_PoController extends Venz_Zend_Controller_Action
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
            $invRental = new Venz_App_Inventory_Rental();


			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
			$this->view->allowEdit = $sessionUsers->Acl->isAllowed($this->userInfo->ACLRole, "po_edit", "view");			
			$this->view->allowUpdateMarkup = $sessionUsers->Acl->isAllowed($this->userInfo->ACLRole, "po_markup_update", "view");
			
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
			$update_markup = $Request->getParam('update_markup');	
			if ($update_markup)
			{
				$ID = $Request->getParam('save_po_id') ? $Request->getParam('save_po_id') : new Zend_Db_Expr("NULL");
				
				$arrPOItemsID = $Request->getParam('POItemsID');
				$arrQuantity = $Request->getParam('Quantity');
				$arrUnitMarkup = $Request->getParam('UnitMarkup');
				$arrUnitRetail = $Request->getParam('UnitRetail');
				
				foreach ($arrPOItemsID as $index => $POItemsID)
				{	
					$Quantity = $arrQuantity[$index];
					$MarkupPercent = $arrUnitMarkup[$index];
					$UnitRetail = $arrUnitRetail[$index]; 	
					$arrUpdate = array("MarkupPercent"=>$MarkupPercent,"UnitRetail"=>$UnitRetail);
					$db->update("ItemSeries", $arrUpdate, "POItemsID=".$POItemsID);	
					
				}
				$this->_redirect('/inventory/po/index/edit_po/'.$ID."#POItemsList"); 				
			}
			

			$save_po = $Request->getParam('save_po');	
			$create_po = $Request->getParam('create_po');	
			if (($create_po || $save_po) && $this->view->allowEdit)
			{
				if ($save_po)
					$ID = $Request->getParam('save_po_id') ? $Request->getParam('save_po_id') : new Zend_Db_Expr("NULL");
				
				$VendorID = $Request->getParam('VendorID') ? $Request->getParam('VendorID') : new Zend_Db_Expr("NULL");
				$BranchID = $Request->getParam('BranchID') ? $Request->getParam('BranchID') : new Zend_Db_Expr("NULL");
				$OrderNumber = $Request->getParam('OrderNumber') ? $Request->getParam('OrderNumber') : new Zend_Db_Expr("NULL");
				$PurchaseDate = $Request->getParam('PurchaseDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('PurchaseDate')) : new Zend_Db_Expr("NULL");
				$OADate = $Request->getParam('OADate') ? $dispFormat->format_date_simple_to_db($Request->getParam('OADate')) : new Zend_Db_Expr("NULL");
				$ExpectedDate = $Request->getParam('ExpectedDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('ExpectedDate')) : new Zend_Db_Expr("NULL");
				$FreightForwarder = $Request->getParam('FreightForwarder') ? $Request->getParam('FreightForwarder') : new Zend_Db_Expr("NULL");
				$POStatus = $Request->getParam('POStatus') ? $Request->getParam('POStatus') : new Zend_Db_Expr("NULL");
				$ReceivedDate = $Request->getParam('ReceivedDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('ReceivedDate')) : new Zend_Db_Expr("NULL");
				
				
				$ProductCost = $Request->getParam('ProductCost') ? $Request->getParam('ProductCost') : new Zend_Db_Expr("NULL");
				$Currency = $Request->getParam('Currency') ? $Request->getParam('Currency') : new Zend_Db_Expr("NULL");
				$ProductCostRM = $Request->getParam('ProductCostRM') ? $Request->getParam('ProductCostRM') : new Zend_Db_Expr("NULL");
				$Multiplier = $Request->getParam('Multiplier') ? $Request->getParam('Multiplier') : new Zend_Db_Expr("NULL");
				$POTaxCost = $Request->getParam('POTaxCost') ? $Request->getParam('POTaxCost') : new Zend_Db_Expr("NULL");
				$PODiscount = $Request->getParam('PODiscount') ? $Request->getParam('PODiscount') : new Zend_Db_Expr("NULL");

				$PODeliveryCost = $Request->getParam('PODeliveryCost') ? $Request->getParam('PODeliveryCost') : new Zend_Db_Expr("NULL");
				$MiscCost = $Request->getParam('MiscCost') ? $Request->getParam('MiscCost') : new Zend_Db_Expr("NULL");
				$MiscNote = $Request->getParam('MiscNote') ? $Request->getParam('MiscNote') : new Zend_Db_Expr("NULL");
				$TotalCost = $Request->getParam('TotalCost') ? $Request->getParam('TotalCost') : new Zend_Db_Expr("NULL");

				$this->view->VendorID = $VendorID;	
				$this->view->BranchID = $BranchID;	
				$this->view->OrderNumber = $OrderNumber;	
				$this->view->PurchaseDate = $PurchaseDate;	
				
				$this->view->OADate = $OADate;	
				$this->view->ExpectedDate = $ExpectedDate;	
				$this->view->FreightForwarder = $FreightForwarder;	
				$this->view->POStatus = $POStatus;	
				$this->view->ReceivedDate = $ReceivedDate;	

				$this->view->ProductCost = $ProductCost;	
				$this->view->Currency = $Currency;	
				$this->view->ProductCostRM = $ProductCostRM;	
				$this->view->Multiplier = $Multiplier;	
				$this->view->POTaxCost = $POTaxCost;	

				$this->view->PODeliveryCost = $PODeliveryCost;	

			
				
				$this->view->MiscCost = $MiscCost;				
				$this->view->MiscNote = $MiscNote;				
				$this->view->TotalCost = $TotalCost;			

				
				$errorFile = false;
				if (!$_FILES['POFile']['error'])
				{
					
					if ($_FILES['POFile']['size'] > (1 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, "Please select a file that is less than 1MB in size.");
						$errorFile = true;
						
					}
				}
				
				$errorFile = false;
				if (!$_FILES['AOFile']['error'])
				{
					if ($_FILES['AOFile']['size'] > (1 * 1024 * 1024))
					{
						$this->appMessage->setMsg(0, "Please select a file that is less than 1MB in size.");
						$errorFile = true;
						
					}
				}
		
		
				if (!$errorFile){

					$arrData = array("VendorID"=>$VendorID,"BranchID"=>$BranchID,"OrderNumber"=>$OrderNumber,"PurchaseDate"=>$PurchaseDate,"ProductCost"=>$ProductCost,
					"PODeliveryCost"=>$PODeliveryCost, "Currency"=>$Currency,"ProductCostRM"=>$ProductCostRM,"Multiplier"=>$Multiplier,"POTaxCost"=>$POTaxCost,
					"TotalCost"=>$TotalCost, "PODiscount"=>$PODiscount, "OADate"=>$OADate,"ExpectedDate"=>$ExpectedDate,
					"ReceivedDate"=>$ReceivedDate,"POStatus"=>$POStatus, "FreightForwarder"=>$FreightForwarder);
					
					//print_r($arrData); exit();
					
					if ($save_po){	
						$db->update("PurchaseOrders", $arrData, "ID=".$ID);					
					}else{
						$db->insert("PurchaseOrders", $arrData);
						$ID = $db->lastInsertId();
					}
					
				
					
					$arrTemp = explode(".", $_FILES['POFile']['name']);
					$ext = $arrTemp[sizeof($arrTemp) -1];
					$filename = $ID.".". $ext;
					$relativePath = "/uploads/POFile/".$filename;
					if (!$_FILES['POFile']['error'])
					{
						move_uploaded_file($_FILES['POFile']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePath);
						$arrUpdate = array("POFilePath"=>$relativePath);
						$db->update("PurchaseOrders", $arrUpdate, "ID=".$ID);
					}
					
					
					$arrTemp = explode(".", $_FILES['AOFile']['name']);
					$ext = $arrTemp[sizeof($arrTemp) -1];
					$filenameAO = $ID.".". $ext;
					$relativePathAO = "/uploads/AOFile/".$filenameAO;
					if (!$_FILES['AOFile']['error'])
					{

						move_uploaded_file($_FILES['AOFile']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$relativePathAO);
						$arrUpdateAO = array("AOFilePath"=>$relativePathAO);

						$db->update("PurchaseOrders", $arrUpdateAO, "ID=".$ID);
					}
					
					
					
					if ($save_po){	
						$this->appMessage->setNotice(1,$this->translate->_('Details for') . " <B>".$OrderNumber."</B> ".$this->translate->_('has been updated').".");
					}else{
						$this->appMessage->setNotice(1, $this->translate->_('New PO'). " \"<B>".$OrderNumber."</B>\" ".$this->translate->_('has been created').".");
					}
					
					//////  Save all the PO Item details ///////
					$arrItemID = $Request->getParam('ItemID');
					$arrStatus = $Request->getParam('Status');
					$arrStatusDate = $Request->getParam('StatusDate');
					$arrStatusCheck = $Request->getParam('StatusCheck');
					$arrQuantity = $Request->getParam('Quantity');
					$arrUnitPrice = $Request->getParam('UnitPrice');
					$arrTotalItemPrice = $Request->getParam('TotalItemPrice');
					
					$arrUnitMarkup = $Request->getParam('UnitMarkup');
					$arrUnitRetail = $Request->getParam('UnitRetail');
					
					$arrUnitDiscount = $Request->getParam('UnitDiscount');
					$arrUnitDiscountType = $Request->getParam('UnitDiscountType');					
					
					$arrDeliveryCost = $Request->getParam('DeliveryCost');
					$arrTaxCost = $Request->getParam('TaxCost');
					$arrLandedCost = $Request->getParam('LandedCost');
					$arrPOItemsID = $Request->getParam('POItemsID');
					$itemStatus = 'indent';
					if ($POStatus == "received")
						$itemStatus = 'in';
				
					
				
					if ($arrPOItemsID){
						foreach ($arrPOItemsID as $index => $POItemsID)
						{
							$POItemStatusDate = $arrStatusDate[$index] ? $dispFormat->format_date_simple_to_db($arrStatusDate[$index]) : new Zend_Db_Expr("NULL");
							$arrData = array("ItemID"=>$arrItemID[$index],"Status"=>$arrStatus[$index], "StatusDate"=>$POItemStatusDate,
                                "UnitPrice"=>($arrUnitPrice[$index] ? $arrUnitPrice[$index] : new Zend_Db_Expr("NULL")),
                                "UnitPriceRM"=>($arrTotalItemPrice[$index] ? $arrTotalItemPrice[$index] : new Zend_Db_Expr("NULL")),
                                "DeliveryCost"=>($arrDeliveryCost[$index] ? $arrDeliveryCost[$index] : new Zend_Db_Expr("NULL")),
                                "Quantity"=>($arrQuantity[$index] ? $arrQuantity[$index] : new Zend_Db_Expr("NULL")),
                                "TaxCost"=>($arrTaxCost[$index] ? $arrTaxCost[$index] : new Zend_Db_Expr("NULL")),
                                "LandedCost"=>($arrLandedCost[$index] ? $arrLandedCost[$index] : new Zend_Db_Expr("NULL")),
                                "UnitDiscount"=>($arrUnitDiscount[$index] ? $arrUnitDiscount[$index] : new Zend_Db_Expr("NULL")),
                                "UnitDiscountType"=>($arrUnitDiscountType[$index] ? $arrUnitDiscountType[$index] : new Zend_Db_Expr("NULL")),
                                "OrderID"=>$ID);
							$db->update("POItems", $arrData, "ID=".$POItemsID);	

				
			
							/// insert into ItemsSeries table
							// check if ItemSeries for the item and po exist
							$arrItemRetail = $db->fetchRow("SELECT Item.RetailPrice FROM Item where ID=".$arrItemID[$index]);
			
							$Quantity = $arrQuantity[$index];
							$UnitPrice = $arrUnitPrice[$index] / $Quantity;
							$UnitPriceRM = $arrTotalItemPrice[$index] / $Quantity;
							$UnitDeliveryCost = $arrDeliveryCost[$index] / $Quantity;
							$UnitTaxCost = $arrTaxCost[$index] / $Quantity;
							$UnitLandedCost = $arrLandedCost[$index] / $Quantity;
							$MarkupPercent = $arrUnitMarkup[$index] ? $arrUnitMarkup[$index] : new Zend_Db_Expr("NULL");
							$UnitRetail = $arrUnitRetail[$index]; 
							
							
							
							
			/*				if ($arrItemRetail['RetailPrice'] >= $UnitLandedCost)
								$MarkupPercent = (($arrItemRetail['RetailPrice'] - $UnitLandedCost) / $UnitLandedCost) * 100;
							else
								$MarkupPercent = -(($UnitLandedCost - $arrItemRetail['RetailPrice']) / $arrItemRetail['RetailPrice']) * 100;
			*/					
								
							
						//	print $UnitPriceRM."--".$arrItemRetail['RetailPrice']. "--".$MarkupPercent . "--"; exit();
								
							$arrItemSeriesExist = $db->fetchAll("SELECT ItemSeries.ID FROM ItemSeries where POItemsID=".$POItemsID." order by ID desc");
							if (!$arrItemSeriesExist)
							{

							    /*
							     *  If item series not exist, just insert
							     */
								for ($i = 0; $i < $Quantity; $i++)
								{
									$arrInsert = array("POItemsID"=>$POItemsID, "BranchID"=>$BranchID, "ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
									"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"UnitRetail"=>$UnitRetail);
									
									if ($arrStatusCheck[$index])
									{
										$arrInsert["Status"] = $arrStatus[$index];
									}

									$db->insert("ItemSeries", $arrInsert);	
									$itemSeriesID = $db->lastInsertId();	
									if ($arrStatusCheck[$index]){									
										$arrInsertStatus = array("ItemSeriesID"=>$itemSeriesID, "StatusDate"=>$POItemStatusDate, "Status"=>$arrStatus[$index], "UserIDEntry"=>$this->userInfo->ID,
											"UserIDResp"=>$this->userInfo->ID, "EntryDateTime"=>new Zend_Db_Expr("now()"));
										$db->insert("ItemSeriesStatus", $arrInsertStatus);	
									}
									if ($arrStatusCheck[$index]){
                                        if ($arrStatus[$index] == 'rental_asset'){
                                            $invRental->insertAsRental($itemSeriesID, $POItemsID, $UnitLandedCost);
                                        }
                                    }
								}
							}
							else
							{
								if (sizeof($arrItemSeriesExist) == $Quantity){
                                    /*
                                     *  If item series already exist and as same amount of item as the PO, just update each of the item detail
                                     */
									$arrUpdate = array("POItemsID"=>$POItemsID, "ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
									"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"UnitRetail"=>$UnitRetail);
									
									if ($arrStatusCheck[$index])
									{
										$arrUpdate["Status"] = $arrStatus[$index];
									}
									$db->update("ItemSeries", $arrUpdate, "POItemsID=".$POItemsID);	
									if ($arrStatusCheck[$index]){
										$arrItemSeriesAll = $db->fetchAll("SELECT * FROM ItemSeries WHERE POItemsID=".$POItemsID);
										foreach ($arrItemSeriesAll as $arrItemSeries)
										{
											$arrInsertStatus = array("ItemSeriesID"=>$arrItemSeries['ID'], "StatusDate"=>$POItemStatusDate, "Status"=>$arrStatus[$index], "UserIDEntry"=>$this->userInfo->ID,
												"UserIDResp"=>$this->userInfo->ID, "EntryDateTime"=>new Zend_Db_Expr("now()"));
											$db->insert("ItemSeriesStatus", $arrInsertStatus);	
												
										}
									}

                                    if ($arrStatusCheck[$index]){
                                        if ($arrStatus[$index] == 'rental_asset'){
                                            $invRental->updateAssetByPO($POItemsID, $UnitLandedCost);
                                        }else{
                                            $invRental->clearAssetByPO($POItemsID);
                                        }
									}



								}else if (sizeof($arrItemSeriesExist) < $Quantity){
                                    /*
                                     *  If item series already exist and less amount of item as the PO, update each of the item detail for existing then
                                     *  add new item series with same detail
                                     */

									$arrUpdate = array("POItemsID"=>$POItemsID, "ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
									"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"UnitRetail"=>$UnitRetail);
								
									if ($arrStatusCheck[$index])
									{
										$arrUpdate["Status"] = $arrStatus[$index];
									}
									
									$db->update("ItemSeries", $arrUpdate, "POItemsID=".$POItemsID);		
									if ($arrStatusCheck[$index]){
                                        $arrItemSeriesAll = $db->fetchAll("SELECT * FROM ItemSeries WHERE POItemsID=".$POItemsID);
                                        foreach ($arrItemSeriesAll as $arrItemSeries)
                                        {
                                            $arrInsertStatus = array("ItemSeriesID"=>$arrItemSeries['ID'], "StatusDate"=>$POItemStatusDate, "Status"=>$arrStatus[$index], "UserIDEntry"=>$this->userInfo->ID,
                                                "UserIDResp"=>$this->userInfo->ID, "EntryDateTime"=>new Zend_Db_Expr("now()"));
                                            $db->insert("ItemSeriesStatus", $arrInsertStatus);

                                        }

                                        if ($arrStatusCheck[$index]){
                                            if ($arrStatus[$index] == 'rental_asset'){
                                                $invRental->updateAssetByPO($POItemsID, $UnitLandedCost);
                                            }else{
                                                $invRental->clearAssetByPO($POItemsID);
                                            }
                                        }
									}
									
									for ($i = 0; $i < ($Quantity - sizeof($arrItemSeriesExist)); $i++)
									{
										$arrInsert = array("POItemsID"=>$POItemsID,"BranchID"=>$BranchID,"ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
										"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"UnitRetail"=>$UnitRetail);
										
										if ($arrStatusCheck[$index])
										{
											$arrInsert["Status"] = $arrStatus[$index];
										}
										$db->insert("ItemSeries", $arrInsert);							
										$itemSeriesID = $db->lastInsertId();	
										if ($arrStatusCheck[$index]){									
											$arrInsertStatus = array("ItemSeriesID"=>$itemSeriesID, "StatusDate"=>$POItemStatusDate, "Status"=>$arrStatus[$index], "UserIDEntry"=>$this->userInfo->ID,
												"UserIDResp"=>$this->userInfo->ID, "EntryDateTime"=>new Zend_Db_Expr("now()"));
											$db->insert("ItemSeriesStatus", $arrInsertStatus);	
										}

                                        if ($arrStatusCheck[$index]){
                                            if ($arrStatus[$index] == 'rental_asset'){
                                                $invRental->insertAsRental($itemSeriesID, $POItemsID, $UnitLandedCost);
                                            }else{
                                                $invRental->clearAssetByPO($POItemsID);
                                            }
										}
									}										
								
								}
								else if (sizeof($arrItemSeriesExist) > $Quantity)
								{
                                    /*
                                     *  Technically should not happen
                                     */
									////// dangerous...  existing record with series number might be removed.
									$arrUpdate = array("POItemsID"=>$POItemsID, "ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
									"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost,"MarkupPercent"=>$MarkupPercent,"UnitRetail"=>$UnitRetail);
									
									if ($arrStatusCheck[$index])
									{
										$arrUpdate["Status"] = $arrStatus[$index];
									}
									
									$db->update("ItemSeries", $arrUpdate, "POItemsID=".$POItemsID);		
									if ($arrStatusCheck[$index]){
											$arrItemSeriesAll = $db->fetchAll("SELECT * FROM ItemSeries WHERE POItemsID=".$POItemsID);
											foreach ($arrItemSeriesAll as $arrItemSeries)
											{
												$arrInsertStatus = array("ItemSeriesID"=>$arrItemSeries['ID'], "StatusDate"=>$POItemStatusDate, "Status"=>$arrStatus[$index], "UserIDEntry"=>$this->userInfo->ID,
													"UserIDResp"=>$this->userInfo->ID, "EntryDateTime"=>new Zend_Db_Expr("now()"));
												$db->insert("ItemSeriesStatus", $arrInsertStatus);	
													
											}
									}
									// this will remove the bottom series
									$arrItemSeriesExistRemove = array_slice($arrItemSeriesExist, 0, (sizeof($arrItemSeriesExist) - $Quantity));
									//print "<PRE>";
									//print_r($arrItemSeriesExistRemove);
									//print "</PRE>";
									//exit();
									foreach ($arrItemSeriesExistRemove as $arrItemSeriesID)
									{
										$db->delete("ItemSeries", "ID=".$arrItemSeriesID['ID']);
										$db->delete("ItemSeriesStatus", "ItemSeriesID=".$arrItemSeriesID['ID']);
									
									}
									
								}
							}
							
		
						/*	
							for ($i = 0; $i < $Quantity; $i++)
							{
								if (!$arrItemSeriesExist)
								{
									$arrInsert = array("POItemsID"=>$POItemsID, "ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
									"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost);
									$db->insert("ItemSeries", $arrInsert);							
								}else
								{
									$arrUpdate = array("POItemsID"=>$POItemsID, "ItemID"=>$arrItemID[$index], "UnitPrice"=>$UnitPrice, "UnitPriceRM"=>$UnitPriceRM, "UnitDeliveryCost"=>$UnitDeliveryCost,
									"UnitTaxCost"=>$UnitTaxCost,"UnitLandedCost"=>$UnitLandedCost);
									//print_r($arrItemSeriesExist); exit();
									$db->update("ItemSeries", $arrUpdate, "ID=".$arrItemSeriesExist['ID']);							
						
								}

							}								
*/

							
							
						}
						
					//						exit();
						
					//	$this->appMessage->setNotice(1, "PO Items for <B>".$OrderNumber."</B> has been updated.");
					}					
/*					
					if ($arrItemID){
						$db->delete("POItems", "OrderID=".$ID);
						foreach ($arrItemID as $index => $ItemID)
						{
							$arrInsert = array("ItemID"=>$ItemID,"UnitPrice"=>$arrUnitPrice[$index],"DeliveryCost"=>$arrDeliveryCost[$index],"Quantity"=>$arrQuantity[$index],
							"TaxCost"=>$arrTaxCost[$index],"LandedCost"=>$arrLandedCost[$index],"OrderID"=>$ID);
							$db->insert("POItems", $arrInsert);	
							
							
							
							
						}
						
						$this->appMessage->setNotice(1, "PO Items for <B>".$OrderNumber."</B> has been updated.");
					}					
*/					
					
					$this->_redirect('/inventory/po/index/edit_po/'.$ID); 
				}else
					$this->_redirect('/inventory/po/index/edit_po/'.$ID); 
				
			}
			
			$this->view->edit_po = '';
			$edit_po = $Request->getParam('edit_po');	
			if ($edit_po)
			{
				$this->view->edit_po = $edit_po;
			
				$arrPODetail = $libInv->getPODetail($edit_po);
				//print_r($arrPODetail); exit();
				$this->view->VendorID = $arrPODetail['VendorID'];	
				$this->view->BranchID = $arrPODetail['BranchID'];	
				$this->view->POFilePath = $arrPODetail['POFilePath'];	
				$this->view->AOFilePath = $arrPODetail['AOFilePath'];	
				
				
				
				$this->view->Locked = $arrPODetail['Locked'];
				$this->view->LockedBy = $arrPODetail['LockByName'];	
				$this->view->LockedDate = $arrPODetail['LockedDate'];	
				$this->view->disabled = "";
				if ($this->view->Locked)
					$this->view->disabled = "disabled";
				
				if (!$this->view->allowEdit)
					$this->view->disabled = "disabled";
				
				$this->view->OrderNumber = $arrPODetail['OrderNumber'];	
				$this->view->PurchaseDate = $dispFormat->format_date_db_to_simple($arrPODetail['PurchaseDate']);	
				$this->view->ProductCost = $arrPODetail['ProductCost'];	
				$this->view->Currency = $arrPODetail['Currency'];	
				$this->view->ProductCostRM = $arrPODetail['ProductCostRM'];	
				$this->view->Multiplier = $arrPODetail['Multiplier'];	
				$this->view->POTaxCost = $arrPODetail['POTaxCost'] == "0.00" ? "" : $arrPODetail['POTaxCost'];	
				$this->view->PODiscount = $arrPODetail['PODiscount'] == "0.00" ? "" : $arrPODetail['PODiscount'];		
				
				$this->view->OADate = $dispFormat->format_date_db_to_simple($arrPODetail['OADate']);	
				$this->view->ExpectedDate = $dispFormat->format_date_db_to_simple($arrPODetail['ExpectedDate']);	
				$this->view->ReceivedDate = $dispFormat->format_date_db_to_simple($arrPODetail['ReceivedDate']);	
				$this->view->FreightForwarder = $arrPODetail['FreightForwarder'];	
				$this->view->POStatus = $arrPODetail['POStatus'];	
				
				
				
				$this->view->PODeliveryCost = $arrPODetail['PODeliveryCost'];	
				$this->view->MiscCost = $arrPODetail['MiscCost'];	
				$this->view->MiscNote = $arrPODetail['MiscNote'];	
				$this->view->TotalCost = $arrPODetail['TotalCost'];	
				$libInv->setFetchMode();
				$arrItems = $libInv->getPOItems("PurchaseOrders.ID", "asc", 1000, 1, " AND OrderID=".$edit_po);
				
				$currencyLabel = $this->view->Currency;
				$this->view->currencyTypeID = $this->view->Currency;
				
				$this->view->ddSelectjs = "";
				$dataItems = $arrItems[1];
				$itemIndex = 0;
				foreach ($dataItems as $arrData)
				{
					$optionItems = $libInv->getItemOptionsEx($arrData['ItemID']);
					$optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $arrData['Status'],array(),array('rental_asset'));


                $StatusDate = $dispFormat->format_date_db_to_simple($arrData['StatusDate']);
					$POItemsID=$arrData['ID'];
					$ItemID=$arrData['ItemID'];
					$Quantity = $arrData['Quantity'];
					$UnitPrice = $arrData['UnitPrice'];
					$UnitDiscount = $arrData['UnitDiscount'] == "0.00" ? "" : $arrData['UnitDiscount'];
					$UnitDiscountType = $arrData['UnitDiscountType'];
					$checkedAmount = ($UnitDiscountType == "$") ? "selected" : "";
					$checkedPercent = ($UnitDiscountType == "%") ? "selected" : "";
					
					
					$DeliveryCost = $arrData['DeliveryCost'] == "0.00" ? "" : $arrData['DeliveryCost'];
					$TaxCost = $arrData['TaxCost'] == "0.00" ? "" : $arrData['TaxCost'];
					$LandedCost = $arrData['LandedCost'];
					$MarkupPercent = $arrData['MarkupPercent'] == "0.00" ? "" : $arrData['MarkupPercent'];
					$UnitRetail = $arrData['UnitRetail'];
					$POItemsID = $arrData['ID'];
					$IconDelete = "";
					$disabled = "";
					if ($this->view->Locked)
						$disabled = "disabled";
						
					if (!$this->view->allowEdit)	
						$disabled = "disabled";
					
					if (!$this->view->Locked && $this->view->allowEdit)
						$IconDelete = " | <img border=0 src='/images/icons/IconDelete.gif' id='RemoveRowConfirm' name='RemoveRowConfirm'>";
					$systemSetting = new Zend_Session_Namespace('systemSetting');
					$imgEdit = "IconEdit.gif";
					if ($systemSetting->userInfo->ACLRole == "User")	
						$imgEdit = "IconView.png";
					
					$disableMarkup = $disabled;
					if ($systemSetting->userInfo->ACLRole == "Sales" || $systemSetting->userInfo->ACLRole == "Account" )	
						$disableMarkup = "";
					
					$this->view->ddSelectjs .= "$('.Item_".$POItemsID."').ddslick({ 'width':'450px', onSelected: function(selectedData){ if (!loading) {OnSelectItem(selectedData, $itemIndex, $POItemsID)}} });\n";

					
					$strAddNew = $this->translate->_('Add New');
					
						$this->view->listItems .= <<<END
			<TR><TD  nowrap class='report_odd' style='text-align:center'><div id="itemcounter"></div></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input type=hidden Name="ItemID[$itemIndex]" ID="ItemID" value="$arrData[ItemID]"><div id='divSelect_$itemIndex'><SELECT  style='width:450px' $disabled  Name="ItemID[]" class="Item_$POItemsID"><option value=''>-</option>$optionItems<option value='add-new'><<< $strAddNew >>></option></SELECT></div></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><SELECT $disabled style='width:100px; font-size:11px' name='Status[$itemIndex]' id='Status'><option value=''>-</option>$optionStatusItem </SELECT>&nbsp;<input style="width: 18px; height: 18px;" type=checkbox  ID="StatusCheck" Name="StatusCheck[$itemIndex]" value='1'>
	<BR><input $disabled type=text name='StatusDate[$itemIndex]' id='StatusDate_$itemIndex' idx='StatusDate' size=8 value="$StatusDate"><BR>
	</TD>
	
	
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled  style='text-align:center' size="2" type=text name="Quantity[]" id="Quantity" value="$Quantity"></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='UnitPrice[]' id='UnitPrice'  size="6"  value="$UnitPrice"></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='UnitDiscount[]' id='UnitDiscount'  size="6" value='$UnitDiscount' >
	<SELECT name='UnitDiscountType[]' id='UnitDiscountType'><option value="">-</option><option value="%" $checkedPercent>%</option><option value="$" $checkedAmount>$</option></SELECT></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='TotalPrice[]' id='TotalPrice'  size="6"  value="$TotalPrice"></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='TotalItemPrice[]' id='TotalItemPrice'  size="6"  value="$TotalItemPrice" ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='DeliveryCost[]' id='DeliveryCost'  size="6" value='$DeliveryCost' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input  $disabled style='text-align:right' type='text' name='TaxCost[]' id='TaxCost'  size="6" value='$TaxCost' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='LandedCost[]' id='LandedCost'  size="6" value='$LandedCost' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='UnitLandedCost[]' id='UnitLandedCost'  size="6" value='$UnitLandedCost' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input $disableMarkup style='text-align:right' type='text' name='UnitMarkup[]' id='UnitMarkup'  size="3" value='$MarkupPercent' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='UnitRetail[]' id='UnitRetail'  size="6" value='$UnitRetail' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center' width=40px><a name='UpdateSerialNumber' href='/inventory/po/itemseries/POID/$edit_po/POItemsID/$POItemsID/ItemID/$ItemID'>
	<img border=0 src='/images/icons/$imgEdit' id='EditPOItem' name='EditPOItem'></a> $IconDelete
	<input type=hidden name="POItemsID[]" id="POItemsID" value="$POItemsID">
	
	</TD></TR>
		
END;

					$itemIndex++;
				}
			}					

			$lock_po = $Request->getParam('lock_po');	
			if ($lock_po && $this->view->allowEdit)
			{
				$ID = $Request->getParam('save_po_id') ? $Request->getParam('save_po_id') : new Zend_Db_Expr("NULL");
				$arrUpdate = array("Locked"=>1,"LockedBy"=>$this->userInfo->ID,"LockedDate"=>new Zend_Db_Expr("now()"));
				$db->update("PurchaseOrders", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, "The PO has been locked.");
				$this->_redirect('/inventory/po/index/edit_po/'.$ID);   				
			}				
			
			$remove_po = $Request->getParam('remove_po');	
			if ($remove_po && $this->view->allowEdit)
			{
				$arrPODetail = $libInv->getPODetail($remove_po);
			
				$db->delete("PurchaseOrders", "ID=".$remove_po);
				$this->appMessage->setNotice(1, "The PO has been removed.");
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrPODetail['POFilePath']);
				$this->_redirect('/inventory/po');   				
			}			
		
			$remove_file_po = $Request->getParam('remove_file_po');	
			if ($remove_file_po && $this->view->allowEdit)
			{
				$ID = $Request->getParam('save_po_id') ? $Request->getParam('save_po_id') : new Zend_Db_Expr("NULL");
				$arrPODetail = $libInv->getPODetail($ID);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrPODetail['POFilePath']);
				$arrUpdate = array("POFilePath"=>new Zend_Db_Expr("NULL"));
				$db->update("PurchaseOrders", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, "The PO flie has been removed.");
				$this->_redirect('/inventory/po/index/edit_po/'.$ID);   				
			}			
	
			$remove_file_ao = $Request->getParam('remove_file_ao');	
			if ($remove_file_ao && $this->view->allowEdit)
			{
				$ID = $Request->getParam('save_po_id') ? $Request->getParam('save_po_id') : new Zend_Db_Expr("NULL");
				$arrPODetail = $libInv->getPODetail($ID);
				unlink( $_SERVER['DOCUMENT_ROOT'].$arrPODetail['POFilePath']);
				$arrUpdate = array("AOFilePath"=>new Zend_Db_Expr("NULL"));
				$db->update("PurchaseOrders", $arrUpdate, "ID=".$ID);
				$this->appMessage->setNotice(1, "The Order Acknowledgement flie has been removed.");
				$this->_redirect('/inventory/po/index/edit_po/'.$ID);   				
			}			
			
			$this->view->optionVendors = $libDb->getTableOptions("Vendors", "Name", "ID", $this->view->VendorID); 
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			$this->view->optionCurrency = $libDb->getSystemOptions("arrCurrency", $this->view->currencyTypeID); 
			$this->view->optionPOStatus = $libDb->getSystemOptions("arrPOStatus", $this->view->POStatus); 			
			$this->view->optionStatusItemOverall = $libDb->getSystemOptions("arrStockStatus",NULL,array(),array('rental_asset'));
					
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }
		
	public function ajaxremovepoitemsAction()
	{
		$Request = $this->getRequest();	
		$POItemsID = $Request->getParam('poitemsid');
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$db->delete("POItems", "ID=".$POItemsID);
		$db->delete("ItemSeries", "POItemsID=".$POItemsID);

		exit();
	}
		
	public function ajaxadditemAction()
	{
		$Request = $this->getRequest();	
		$OrderID = $Request->getParam('orderid');
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$libInv = new Venz_App_Inventory_Helper();
		$libDb = new Venz_App_Db_Table();
		$optionItems = $libInv->getItemOptionsEx();
		$optionStatusItem = $libDb->getSystemOptions("arrStockStatus", "indent"); 
					$systemSetting = new Zend_Session_Namespace('systemSetting');		
		$markup = $systemSetting->markup;
		
		$arrPOItems = $db->fetchRow("SELECT count(*) as TotalPOItems FROM POItems where OrderID=".$OrderID);
		
	//	$arrInsert = array("OrderID"=>$OrderID, "EntityID"=>$this->userInfo->EntityID);
		$arrInsert = array("OrderID"=>$OrderID);
		$db->insert("POItems", $arrInsert);	
		$POItemsID = $db->lastInsertId();
		
		
	//	print "SELECT count(*) as TotalPOItems FROM POItems where OrderID=".$OrderID;
		$itemIndex = $arrPOItems['TotalPOItems'];
		$strAddNew = $this->translate->_('Add New');
		$content = <<<END
			<TR><TD  nowrap class='report_odd' style='text-align:center'><div id="itemcounter"></div></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input type=hidden Name="ItemID[$itemIndex]" ID="ItemID" value=""><div id='divSelect_$itemIndex'><SELECT  style='width:450px' Name="ItemID[]"  class="Item_$POItemsID"><option value=''>-</option>$optionItems<option value='add-new'><<< $strAddNew >>></option></SELECT></div></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><SELECT style='width:100px; font-size:11px' Name='Status[$itemIndex]' id='Status'><option value=''>-</option>$optionStatusItem </SELECT>&nbsp;<input type=checkbox ID="StatusCheck" Name="StatusCheck[$itemIndex]" value='1' checked>
	<BR><input type=text name='StatusDate[$itemIndex]' id='StatusDate_$itemIndex' idx='StatusDate' size=8 value=''><BR>
	
	
	</TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:center' size="2" type=text name="Quantity[]" id="Quantity"></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='UnitPrice[]' id='UnitPrice'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='UnitDiscount[]' id='UnitDiscount'  size="6" value=''>
	<SELECT name='UnitDiscountType[]' id='UnitDiscountType'><option value="">-</option><option value="%">%</option><option value="$">$</option></SELECT>
	</TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='TotalPrice[]' id='TotalPrice'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='TotalItemPrice[]' id='TotalItemPrice'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='DeliveryCost[]' id='DeliveryCost'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='TaxCost[]' id='TaxCost'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='LandedCost[]' id='LandedCost'  size="6" value=''></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='UnitLandedCost[]' id='UnitLandedCost'  size="6" value='$UnitLandedCost' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input style='text-align:right' type='text' name='UnitMarkup[]' id='UnitMarkup'  size="3" value='$markup' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center'><input readonly style='text-align:right' type='text' name='UnitRetail[]' id='UnitRetail'  size="6" value='' ></TD>
	<TD  nowrap class='report_odd' style='text-align:center' width=40px><img border=0 src='/images/icons/IconDelete.gif' id='RemoveRow' name='RemoveRow'>
	<input type=hidden name="POItemsID[]" id="POItemsID" value="$POItemsID">
	</TD></TR>
	<script>
	$('.Item_'+$POItemsID).ddslick({ 
			'width':'450px', 
			onSelected: function(selectedData)
			{
				OnSelectItem(selectedData, $itemIndex, $POItemsID) 
			}
		});	
	</script>
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
			
			
			$delete_po = $Request->getParam('delete_po');			
			if ($delete_po)
			{
				$arrPO = $db->fetchRow("SELECT * FROM PurchaseOrders where ID=".$delete_po);
				if ($arrPO){
					$POFilePath = $_SERVER['DOCUMENT_ROOT'].$arrPO['POFilePath'];
					if (is_file($POFilePath))
						unlink($POFilePath);
				
					$db->delete("PurchaseOrders", "ID=".$delete_po);
					$arrPOItemsAll = $db->fetchAll("SELECT * FROM POItems where OrderID=".$delete_po);
					foreach ($arrPOItemsAll as $arrPOItems)
					{
						$db->delete("ItemSeries", "ItemID=".$arrPOItems['ItemID']);
					}
					$db->delete("POItems", "OrderID=".$delete_po);
					
					$this->_redirect('/inventory/po/listing/'); 	
					exit();
				}
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
			
			$recordsPerPage = 20 ;
			////////////////////////////////////////////////////////////////////////////////////////
			
			$sqlSearch = "";
			$search_po = $Request->getParam('search_po');	
			$this->view->searchPO = false;
			$strHiddenSearch = "";
			if ($search_po)
			{
				$this->view->searchPO = true;
				$VendorID = $Request->getParam('VendorID');	
				$BranchID = $Request->getParam('BranchID');	
				$OrderNumber = $Request->getParam('OrderNumber');	
				$PurchaseDateFrom = $Request->getParam('PurchaseDateFrom');	
				$PurchaseDateTo = $Request->getParam('PurchaseDateTo');	
				
/*				
				$ProductCostRMFrom = $Request->getParam('ProductCostRMFrom');	
				$ProductCostRMTo = $Request->getParam('ProductCostRMTo');					
				$PODeliveryCostFrom = $Request->getParam('PODeliveryCostFrom');	
				$PODeliveryCostTo = $Request->getParam('PODeliveryCostTo');	
				$POTaxCostFrom = $Request->getParam('POTaxCostFrom');	
				$POTaxCostTo = $Request->getParam('POTaxCostTo');	
				$FinalCostFrom = $Request->getParam('FinalCostFrom');	
				$FinalCostTo = $Request->getParam('FinalCostTo');	
*/				
				
				$sqlSearch .= $OrderNumber ? " and OrderNumber LIKE '%".$OrderNumber."%'" : "";
				$sqlSearch .= $VendorID ? " and PurchaseOrders.VendorID =".$VendorID : "";
				$sqlSearch .= $BranchID ? " and PurchaseOrders.BranchID =".$BranchID : "";
				$sqlSearch .= $PurchaseDateFrom ? " and PurchaseOrders.PurchaseDate >='".$dispFormat->format_date_simple_to_db($PurchaseDateFrom)."'" : "";
				$sqlSearch .= $PurchaseDateTo ? " and PurchaseOrders.PurchaseDate <= '".$dispFormat->format_date_simple_to_db($PurchaseDateTo)."'" : "";
/*				
				$sqlSearch .= $ProductCostRMFrom ? " and PurchaseOrders.ProductCostRM >= ".$ProductCostRMFrom : "";
				$sqlSearch .= $ProductCostRMTo ? " and PurchaseOrders.ProductCostRM <= ".$ProductCostRMTo : "";
				$sqlSearch .= $PODeliveryCostFrom ? " and PurchaseOrders.PODeliveryCost >= ".$PODeliveryCostFrom : "";
				$sqlSearch .= $PODeliveryCostTo ? " and PurchaseOrders.PODeliveryCost <= ".$PODeliveryCostTo : "";
				$sqlSearch .= $POTaxCostFrom ? " and PurchaseOrders.POTaxCost >= ".$POTaxCostFrom : "";
				$sqlSearch .= $POTaxCostTo ? " and PurchaseOrders.POTaxCost <= ".$POTaxCostTo : "";
				$sqlSearch .= $FinalCostFrom ? " and PurchaseOrders.FinalCost >= ".$FinalCostFrom : "";
				$sqlSearch .= $FinalCostTo ? " and PurchaseOrders.FinalCost <= ".$FinalCostTo : "";
*/

				
				//print $sqlSearch; exit();
				$this->view->OrderNumber = $OrderNumber ? $OrderNumber : "";				
				$this->view->PurchaseDateFrom = $PurchaseDateFrom ? $PurchaseDateFrom : "";				
				$this->view->PurchaseDateTo = $PurchaseDateTo ? $PurchaseDateTo : "";				
				$this->view->VendorID = $VendorID ? $VendorID : "";				
				$this->view->BranchID = $BranchID ? $BranchID : "";				
				
/*				$this->view->ProductCostRMFrom = $ProductCostRMFrom ? $ProductCostRMFrom : "";				
				$this->view->ProductCostRMTo = $ProductCostRMTo ? $ProductCostRMTo : "";				
				$this->view->PODeliveryCostFrom = $PODeliveryCostFrom ? $PODeliveryCostFrom : "";				
				$this->view->PODeliveryCostTo = $PODeliveryCostTo ? $PODeliveryCostTo : "";				
				$this->view->POTaxCostFrom = $POTaxCostFrom ? $POTaxCostFrom : "";				
				$this->view->POTaxCostTo = $POTaxCostTo ? $POTaxCostTo : "";				
				$this->view->FinalCostFrom = $FinalCostFrom ? $FinalCostFrom : "";				
				$this->view->FinalCostTo = $FinalCostTo ? $FinalCostTo : "";				
*/				
				$strHiddenSearch = "<input type=hidden name='search_po' value='true'>";
				$strHiddenSearch .= "<input type=hidden name='OrderNumber' value='".$OrderNumber."'>";
				$strHiddenSearch .= "<input type=hidden name='PurchaseDateFrom' value='".$PurchaseDateFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='PurchaseDateTo' value='".$PurchaseDateTo."'>";
				$strHiddenSearch .= "<input type=hidden name='VendorID' value='".$VendorID."'>";
				$strHiddenSearch .= "<input type=hidden name='BranchID' value='".$BranchID."'>";
/*
				$strHiddenSearch .= "<input type=hidden name='ProductCostRMFrom' value='".$ProductCostRMFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='ProductCostRMTo' value='".$ProductCostRMTo."'>";
				$strHiddenSearch .= "<input type=hidden name='PODeliveryCostFrom' value='".$PODeliveryCostFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='PODeliveryCostTo' value='".$PODeliveryCostTo."'>";
				$strHiddenSearch .= "<input type=hidden name='POTaxCostFrom' value='".$POTaxCostFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='POTaxCostTo' value='".$POTaxCostTo."'>";
				$strHiddenSearch .= "<input type=hidden name='FinalCostFrom' value='".$FinalCostFrom."'>";
				$strHiddenSearch .= "<input type=hidden name='FinalCostTo' value='".$FinalCostTo."'>";
*/			}

			$this->view->optionVendors = $libDb->getTableOptions("Vendors", "Name", "ID", $this->view->VendorID); 
			$this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID); 
			
			$libInv->setFetchMode(Zend_Db::FETCH_NUM);

			$arrItem = $libInv->getPO($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);
			$dataItem = $arrItem[1];
			
			$strSearch = "";
			if ($this->view->searchBrand)
				$strSearch = "<input type=hidden name=''>";
			
			function format_action($colnum, $rowdata)
			{
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				if ($systemSetting->userInfo->ACLRole == "User" || $systemSetting->userInfo->ACLRole == "Sales" || $systemSetting->userInfo->ACLRole == "Account")
					return "<a href='/inventory/po/index/edit_po/".$rowdata[0]."'><img border=0 src='/images/icons/IconView.png'></a>";
				else{
					$strAction = "<a href='/inventory/po/index/edit_po/".$rowdata[0]."'><img border=0 src='/images/icons/IconEdit.gif'></a>";
					if (!$rowdata[10] && $systemSetting->userInfo->ACLRole == "AdminSystem")
					{
						$strAction .= "|<a id='IDDeletePO' href='/inventory/po/listing/delete_po/".$rowdata[0]."'><img border=0 src='/images/icons/IconDelete.gif'></a>";
					}
					
					return $strAction;
				}
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
					 'title'		=> $this->translate->_('Purchase Orders'),					 
					 'aligndata' 	=> 'CCCCCRRRRCC',
					 'colparam'      => array('','','','','','','','','','','nowrap'),
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "870px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch,
				)
			);
			$this->view->content_po = $displayTable->render();
			
			
			
			
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
			$dispFormat = new Venz_App_Display_Format();

			/////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
			if (!$this->userInfo){
				$this->appMessage->setMsg(0, "Please login first before accessing this page.");
				$this->_redirect('/auth');
			}			
			
			$sessionUsers = new Zend_Session_Namespace('sessionUsers');	
			$this->view->allowEdit = $sessionUsers->Acl->isAllowed($this->userInfo->ACLRole, "po_edit", "view");
						
			
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
			
			$recordsPerPage = 20 ;
			////////////////////////////////////////////////////////////////////////////////////////
			$POID = $Request->getParam('POID');	
			$POItemsID = $Request->getParam('POItemsID');	
			$ItemID = $Request->getParam('ItemID');	
			
			$SaveSeries = $Request->getParam('SaveSeries');	
			$arrSeriesNumber = $Request->getParam('SeriesNumber');
					
			if ($SaveSeries && $arrSeriesNumber)
			{
				foreach ($arrSeriesNumber as $ItemSeriesID => $SeriesNumber)
				{

					$arrUpdate = array("SeriesNumber"=>($SeriesNumber ? $SeriesNumber : new Zend_Db_Expr("NULL")));
					$db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);	
				}
				
				$this->appMessage->setNotice(1, $this->translate->_('Serial Numbers has been updated').".");
				$this->_redirect('/inventory/po/itemseries/POID/'.$POID.'/POItemsID/'.$POItemsID.'/ItemID/'.$ItemID); 					
			}
			
			

			if ($POID)
			{

				$arrPODetail = $libInv->getPODetail($POID);
				
				$this->view->VendorID = $arrPODetail['VendorID'];	
				$this->view->VendorName = $arrPODetail['VendorName'];
				$this->view->BranchName = $arrPODetail['BranchName'];
				$this->view->OrderNumber = $arrPODetail['OrderNumber'];	
				$this->view->PurchaseDate = $dispFormat->format_date_db_to_simple($arrPODetail['PurchaseDate']);	
				$this->view->Currency = $arrPODetail['Currency'];	
				$this->view->ProductCostRM = $dispFormat->format_currency($arrPODetail['ProductCostRM']);	
				$this->view->POTaxCost = $dispFormat->format_currency($arrPODetail['POTaxCost']);	
				
				$this->view->PODeliveryCost = $dispFormat->format_currency($arrPODetail['PODeliveryCost']);	
				$this->view->TotalCost = $dispFormat->format_currency($arrPODetail['TotalCost']);	

			}					

			
			$ItemName = "";
			
			if ($POItemsID && $ItemID)
			{
				
				$sqlSearch .= $POItemsID ? " and ItemSeries.POItemsID=".$POItemsID : "";
				$sqlSearch .= $ItemID ? " and ItemSeries.ItemID=".$ItemID : "";	
				
				$strHiddenSearch .= "<input type=hidden name='POItemsID' value='".$POItemsID."'>";
				$strHiddenSearch .= "<input type=hidden name='ItemID' value='".$ItemID."'>";
			
				$arrItemDetails = $libInv->getItemDetail($ItemID);
				$ItemName = $arrItemDetails['ItemName'] . " (".$arrItemDetails['ModelNumber'].")";;
			}
			
			

			
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
			function format_seriesnumber($colnum, $rowdata)
			{
				$sessionUsers = new Zend_Session_Namespace('sessionUsers');
				$systemSetting = new Zend_Session_Namespace('systemSetting');
				$allowEdit = $sessionUsers->Acl->isAllowed($systemSetting->userInfo->ACLRole, "po_edit", "view");
				$strDisable = $allowEdit ? "" : "disabled";
				return "<input autocomplete='off' size=25 type=text name='SeriesNumber[".$rowdata[11]."]' value='".$rowdata[6]."' ".$strDisable.">";
			}	
			function format_retail($colnum, $rowdata)
			{
				$dispFormat = new Venz_App_Display_Format();
				return $dispFormat->format_currency($rowdata[20]);
			}	
			function format_markup($colnum, $rowdata)
			{
				
				return $rowdata[18]."%";
			}	
			
			$strSave = "";
			if ($this->view->allowEdit)
				$strSave = "<input type=submit name='SaveSeries' id='SaveSeries' value='Save Serial Numbers'>";
			
			
			$arrHeader = array ('#', $this->translate->_('Serial Number'), $this->translate->_('Unit Price'), $this->translate->_('Unit Delivery Cost'),$this->translate->_('Unit Tax Cost'),$this->translate->_('Unit Landed Cost'),$this->translate->_('Retail Price'),$this->translate->_('Markup %'));
			$displayTable = new Venz_App_Display_Table(
				array (
			         'data' => $dataItem,
					 'hiddenparamtop'=> $strSearch,
					 'headings' => $arrHeader,
					 'format' 		=> array('{format_counter}','{format_seriesnumber}','{format_unitprice}', '{format_unitdeliverycost}', '{format_unittaxcost}', '{format_unitlandedcost}','{format_retail}', '{format_markup}'),					 
					 'sort_column' 	=> array('','','','','','', '', '', '',  '', ''),
					 'alllen' 		=> $arrItem[0],
					 'title'		=> $this->translate->_('Items Series for ') . $ItemName ,					 
					 'aligndata' 	=> 'CCRRRRRR',
					 'pagelen' 		=> $recordsPerPage,
					 'numcols' 		=> sizeof($arrHeader),
			         'tablewidth' => "550px",
			         'sortby' => $sortby,
			         'ascdesc' => $ascdesc,
					 'hiddenparam' => $strHiddenSearch.$strSave.
					 "<input type=button name='Back' id='Back' value='".$this->translate->_('Back To PO Details')."' onclick='document.location=\"/inventory/po/index/edit_po/$POID\"'>",
				)
			);
			$this->view->content_item = $displayTable->render();
			
			
			
			
		}catch (Exception $e) {
		
			echo $e->getMessage();
		}		
    }
	
				
	function ajaxlockpoAction()
	{
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysAcl = new Venz_App_System_Acl();
		$libDb = new Venz_App_Db_Table();
		$ID = $Request->getParam('ID');			
		$Password = $Request->getParam('Password');	
		$POID = $Request->getParam('POID');	
		$arrRow = $db->fetchRow("SELECT * FROM ACLUsers where ID=".$ID." AND Password=MD5('".$Password."')");

		if ($arrRow){
			$arrUpdate = array("Locked"=>1,"LockedBy"=>$arrRow['ID'],"LockedDate"=>new Zend_Db_Expr("now()"));
			$db->update("PurchaseOrders", $arrUpdate, "ID=".$POID);
			echo 1;
		}else
			echo 0;
		exit();
			
		
	}


	function ajaxunlockpoAction()
	{
		$Request = $this->getRequest();			
		$db = Zend_Db_Table::getDefaultAdapter(); 
		$sysAcl = new Venz_App_System_Acl();
		$libDb = new Venz_App_Db_Table();
		$ID = $Request->getParam('ID');			
		$Password = $Request->getParam('Password');	
		$POID = $Request->getParam('POID');	
		$arrRow = $db->fetchRow("SELECT * FROM ACLUsers where ID=".$ID." AND Password=MD5('".$Password."')");

		if ($arrRow){
			$arrUpdate = array("Locked"=>0,"LockedBy"=>$arrRow['ID'],"LockedDate"=>new Zend_Db_Expr("now()"));
			$db->update("PurchaseOrders", $arrUpdate, "ID=".$POID);
			echo 1;
		}else
			echo 0;
		exit();
			
		
	}	
	
}

