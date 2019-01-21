<?php

class Inventory_RentalController extends Venz_Zend_Controller_Action
{
    static $db = NULL;
    public function init()
    {
        parent::init(NULL);
        $this->db = Zend_Db_Table::getDefaultAdapter();
    }


    public function indexAction()
    {


        try {
            $Request = $this->getRequest();
            $db = Zend_Db_Table::getDefaultAdapter();
            $sysHelper = new Venz_App_System_Helper();
            $libInv = new Venz_App_Inventory_Helper();
            $libRental = new Venz_App_Inventory_Rental();
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
                $RentalStatus = $Request->getParam('RentalStatus');


                setcookie('RentalSearchString', $SearchString, time() + (3600*30),"/");
                setcookie('RentalOrderID', $OrderID, time() + (3600*30),"/");
                setcookie('RentalItemID', $ItemID, time() + (3600*30),"/");
                setcookie('RentalFlowBrandID', $BrandID, time() + (3600*30),"/");
                setcookie('RentalItemName', $ItemName, time() + (3600*30),"/");
                setcookie('RentalModelNumber', $ModelNumber, time() + (3600*30),"/");
                setcookie('RentalPartNumber', $PartNumber, time() + (3600*30),"/");
                setcookie('RentalSeriesNumber', $SeriesNumber, time() + (3600*30),"/");
                setcookie('RentalBranchID', $BranchID, time() + (3600*30),"/");
                setcookie('RentalStatus', $RentalStatus, time() + (3600*30),"/");


            }else
            {
                if ($clear_search)
                {
                    setcookie('RentalSearchString',"", time()-3600, "/"); unset($_COOKIE['RentalSearchString']);
                    setcookie('RentalOrderID',"", time()-3600, "/"); unset($_COOKIE['RentalOrderID']);
                    setcookie('RentalItemID', "", time()-3600, "/");unset($_COOKIE['RentalItemID']);
                    setcookie('RentalBrandID', "", time()-3600, "/");unset($_COOKIE['RentalBrandID']);
                    setcookie('RentalItemName', "", time()-3600, "/");unset($_COOKIE['RentalItemName']);
                    setcookie('RentalModelNumber', "", time()-3600, "/");unset($_COOKIE['RentalModelNumber']);
                    setcookie('RentalPartNumber', "", time()-3600, "/");unset($_COOKIE['RentalPartNumber']);
                    setcookie('RentalSeriesNumber', "", time()-3600, "/"); unset($_COOKIE['RentalSeriesNumber']);
                    setcookie('RentalBranchID', "", time()-3600, "/"); unset($_COOKIE['RentalBranchID']);
                    setcookie('RentalStatus', "", time()-3600, "/"); 	unset($_COOKIE['RentalStatus']);

                }
                else
                {
                    $SearchString = $_COOKIE['RentalSearchString'];
                    $OrderID = $_COOKIE['RentalOrderID'];
                    $ItemID = $_COOKIE['RentalItemID'];
                    $BrandID = $_COOKIE['RentalBrandID'];
                    $ItemName = $_COOKIE['RentalItemName'];
                    $ModelNumber = $_COOKIE['RentalModelNumber'];
                    $PartNumber = $_COOKIE['RentalPartNumber'];
                    $SeriesNumber = $_COOKIE['RentalSeriesNumber'];
                    $BranchID = $_COOKIE['RentalBranchID'];
                    $RentalStatus = $_COOKIE['RentalStatus'];
                }
            }
            $sqlSearch .= " and ItemSeries.Status = 'rental_asset'";

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
            $sqlSearch .= $RentalStatus ? " and RentalAsset.RentalStatus = '".$RentalStatus ."'" : "";

            $this->view->SearchString = $SearchString ? $SearchString : "";
            $this->view->OrderID = $OrderID ? $OrderID : "";
            $this->view->ItemID = $ItemID ? $ItemID : "";
            $this->view->BrandID = $BrandID ? $BrandID : "";
            $this->view->ItemName = $ItemName ? $ItemName : "";
            $this->view->ModelNumber = $ModelNumber ? $ModelNumber : "";
            $this->view->PartNumber = $PartNumber ? $PartNumber : "";
            $this->view->SeriesNumber = $SeriesNumber ? $SeriesNumber : "";
            $this->view->BranchID = $BranchID ? $BranchID : "";
            $this->view->RentalStatus = $RentalStatus ? $RentalStatus : "";

            $strHiddenSearch = "<input type=hidden name='search_series' value='true'>";
            $strHiddenSearch .= "<input type=hidden name='SearchString' value='".$SearchString."'>";
            $strHiddenSearch .= "<input type=hidden name='OrderID' value='".$OrderID."'>";
            $strHiddenSearch .= "<input type=hidden name='ItemID' value='".$ItemID."'>";
            $strHiddenSearch .= "<input type=hidden name='BrandID' value='".$BrandID."'>";
            $strHiddenSearch .= "<input type=hidden name='ItemName' value='".$ItemName."'>";
            $strHiddenSearch .= "<input type=hidden name='ModelNumber' value='".$ModelNumber."'>";
            $strHiddenSearch .= "<input type=hidden name='PartNumber' value='".$PartNumber."'>";
            $strHiddenSearch .= "<input type=hidden name='SeriesNumber' value='".$SeriesNumber."'>";
            $strHiddenSearch .= "<input type=hidden name='BranchID' value='".$BranchID."'>";
            $strHiddenSearch .= "<input type=hidden name='RentalStatus' value='".$RentalStatus."'>";


            $strRentalIDs = $libRental->getItemRentalID();
            $strRentalBrandIDs = $libRental->getBrandRentalID();
            $strRentalPOIDs = $libRental->getPORentalID();
            $strRentalBranchIDs = $libRental->getItemBranchID();
            //$this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID);
            $this->view->optionItems = $libInv->getItemOptions($this->view->ItemID, " AND Item.ID IN (".$strRentalIDs.")", 'rental_asset');
            $this->view->optionPO = $libDb->getTableOptions("PurchaseOrders", "OrderNumber", "ID", $this->view->OrderID, "PurchaseDate", " AND PurchaseOrders.ID IN (".$strRentalPOIDs.")");

            $this->view->optionBranches = $libDb->getTableOptions("Branches", "Name", "ID", $this->view->BranchID, NULL, " AND Branches.ID IN (".$strRentalBranchIDs.")");
            //$this->view->optionStatusItem = $libDb->getSystemOptions("arrStockStatus", $this->view->Status);
            $this->view->optionStatusItem = $libDb->getSystemOptions("arrRentalStatus", $this->view->RentalStatus);

            $this->view->optionBrand = $libDb->getTableOptions("Brand", "FullName", "ID", $this->view->BrandID, NULL, " AND Brand.ID IN (".$strRentalBrandIDs.")");


            $sqlFilterRental = " AND Item.ID IN (".$strRentalIDs.")";
            $sqlFilterBrand = "";$sqlFilterItem = "";$sqlFilterModel = "";
            if ($this->view->BrandID)
                $sqlFilterBrand .= " AND Item.BrandID=".$this->view->BrandID;
            if ($this->view->ItemName)
                $sqlFilterItem .= " AND Item.ItemName=".$db->quote(trim($this->view->ItemName));
            if ($this->view->ModelNumber)
                $sqlFilterModel .= " AND Item.ModelNumber=".$db->quote(trim($this->view->ModelNumber));

            $this->view->optionItem = $libDb->getTableOptions("Item", "ItemName", "ItemName", $this->view->ItemName, NULL, $sqlFilterBrand.$sqlFilterRental);
            $this->view->optionModelNumber = $libDb->getTableOptions("Item", "ModelNumber", "ModelNumber", $this->view->ModelNumber, NULL, $sqlFilterBrand.$sqlFilterItem.$sqlFilterRental);
            $this->view->optionPartNumber = $libDb->getTableOptions("Item", "PartNumber", "PartNumber", $this->view->PartNumber, NULL, $sqlFilterBrand.$sqlFilterItem.$sqlFilterModel.$sqlFilterRental);

            $libInv->setFetchMode(Zend_Db::FETCH_NUM);
            $arrItem = $libInv->getItemsSeriesRental($sortby, $ascdesc, $recordsPerPage, $showPage, $sqlSearch);

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

            $sessionInitialValue = new Zend_Session_Namespace('sessionInitialValue');
            $sessionInitialValue->jsInline = "";
            function format_initvalue($colnum, $rowdata, $export)
            {
                $sessionInitialValue = new Zend_Session_Namespace('sessionInitialValue');
                $sessionUserInfo = new Zend_Session_Namespace('sessionUserInfo');
                $dispFormat = new Venz_App_Display_Format();

                if ($export){
                    if ($rowdata[28])
                        return $dispFormat->format_currency($rowdata[28]);
                    else
                        return "";
                }
                if ($sessionUserInfo->userInfo->ACLRole == "AdminSystem" || $sessionUserInfo->userInfo->ACLRole == "Admin")
                {
                    if ($rowdata[28] && !$rowdata[0])
                    {
                        $sessionInitialValue->jsInline .= "$('#init_value_".$rowdata[27]."').editable({success: function (data){ updateUnitRental(".$rowdata[27].", 'init_value', data);}});";
                        $edit = '<a href="#" id="init_value_'.$rowdata[27].'" data-type="text" data-pk="1" data-url="/inventory/rental/ajaxinitvalue/id/'.$rowdata[27].'" data-title="Enter initial value"><img width="15px" src="/images/icons/IconEdit2.png"> '.$dispFormat->format_currency($rowdata[28]).'</a>';
                        return $edit;
                    }else if ($rowdata[28])
                    {
                        return $dispFormat->format_currency($rowdata[28]);
                    }
                    else if (!$rowdata[0])
                    {
                        $sessionInitialValue->jsInline .= "$('#init_value_".$rowdata[27]."').editable({success: function (data){ updateUnitRental(".$rowdata[27].", 'init_value', data);}});";
                        return '<a href="#" id="init_value_'.$rowdata[27].'" data-type="text" data-pk="1" data-url="/inventory/rental/ajaxinitvalue/id/'.$rowdata[27].'" data-title="Enter initial value"><img src="/images/icons/IconEdit2.png"></a>';

                    }else
                        return "";
                }else
                {
                    if ($rowdata[28])
                        return $dispFormat->format_currency($rowdata[28]);
                    else
                        return "";
                }
            }



            $sessionCurrentValue = new Zend_Session_Namespace('sessionCurrentValue');
            $sessionCurrentValue->jsInline = "";
            function format_currentvalue($colnum, $rowdata, $export)
            {
                $dispFormat = new Venz_App_Display_Format();
//               return $dispFormat->format_currency(($rowdata[34] / $rowdata[33]) * $rowdata[28]);
                if ($rowdata[35]){
                    if ($rowdata[35] <= 0){
                        return "<div style='color: red; text-align: right'>".$dispFormat->format_currency($rowdata[35])."</div>";
                    }else{
                        return $dispFormat->format_currency($rowdata[35]);
                    }
                }else{
                    return "";
                }

//                $sessionCurrentValue = new Zend_Session_Namespace('sessionCurrentValue');
//                $sessionUserInfo = new Zend_Session_Namespace('sessionUserInfo');
//                $dispFormat = new Venz_App_Display_Format();
//
//                if ($export){
//                    if ($rowdata[29])
//                        return $dispFormat->format_currency($rowdata[29]);
//                    else
//                        return "";
//                }
//
//
//                if ($sessionUserInfo->userInfo->ACLRole == "AdminSystem" || $sessionUserInfo->userInfo->ACLRole == "Admin")
//                {
//                    if ($rowdata[29])
//                    {
//                        $sessionCurrentValue->jsInline .= "$('#current_value_".$rowdata[27]."').editable({success: function (data){ updateUnitRental(".$rowdata[27].", 'current_value', data);}});";
//                        $edit = '<a href="#" id="current_value_'.$rowdata[27].'" data-type="text" data-pk="1" data-url="/inventory/rental/ajaxcurrentvalue/id/'.$rowdata[27].'" data-title="Enter current value"><img width="15px" src="/images/icons/IconEdit2.png"> '.$dispFormat->format_currency($rowdata[29]).'</a>';
//                        return $edit;
//                    }else
//                    {
//                        $sessionCurrentValue->jsInline .= "$('#current_value_".$rowdata[27]."').editable({success: function (data){ updateUnitRental(".$rowdata[27].", 'current_value', data);}});";
//                        return '<a href="#" id="current_value_'.$rowdata[27].'" data-type="text" data-pk="1" data-url="/inventory/rental/ajaxcurrentvalue/id/'.$rowdata[27].'" data-title="Enter current value"><img src="/images/icons/IconEdit2.png"></a>';
//
//                    }
//                }else
//                {
//                    if ($rowdata[29])
//                        return $dispFormat->format_currency($rowdata[29]);
//                    else
//                        return "";
//                }
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


            function format_branch($colnum, $rowdata)
            {
                return $rowdata[16];
            }
            function format_itemname($colnum, $rowdata, $export)
            {
                if ($export)
                    return $rowdata[8] ." (".$rowdata[9] . ")". $rowdata[26];

                return $rowdata[8] ." (".$rowdata[9] . ")<BR>". $rowdata[26];
//				return  "<a target='_new' href='/inventory/brand/item/edit_item/".$rowdata[1] ."'>".$rowdata[8] ." (".$rowdata[9] . ")</a>";
            }
            function format_action($colnum, $rowdata, $export)
            {
                if ($export)
                    return "";

                $systemSetting = new Zend_Session_Namespace('systemSetting');
                if ($systemSetting->userInfo->ACLRole == "User")
                    return "<a href='/inventory/rental/detail/id/".$rowdata[27]."'><img border=0 src='/images/icons/IconView.png'></a>";
                else
                    return "<a href='/inventory/rental/detail/id/".$rowdata[27]."'><img border=0 src='/images/icons/IconEdit.gif'></a>";
            }
            function format_status($colnum, $rowdata, $export)
            {
                $systemSetting = new Zend_Session_Namespace('systemSetting');
                return  $systemSetting->arrRentalStatus[$rowdata[30]];
            }
            function format_image($colnum, $rowdata)
            {
                if ($rowdata[21])
                    return "<img src='".$rowdata[21]."' style='height:auto; width:auto; max-height:75px; padding-bottom:0px;'>";
                else
                    return "";
            }

            function format_checkbox($colnum, $rowdata)
            {
                return "<input type='checkbox' ItemID='".$rowdata[25]."' name='ItemSeriesID[]' class='SelectItemSeries' value='".$rowdata[11]."'>";

            }

            function format_lifespan($colnum, $rowdata)
            {
                if ($rowdata[33]){
                    if ($rowdata[34] <= 0){
                        return "<div style='color: red; text-align: center'>".$rowdata[34] . " / ".$rowdata[33]." months</div>";
                    }else{
                        return $rowdata[34] . " / ".$rowdata[33]." months";
                    }
                }else{
                    return "";
                }

            }

            function format_dateasset($colnum, $rowdata)
            {
                $dispFormat = new Venz_App_Display_Format();
                return $dispFormat->format_date($rowdata[32]);
            }

            $exportReport = new Venz_App_Report_Excel(array('exportsql'=> $exportSql, 'hiddenparam'=>'<input type=hidden name="Search" value="Search">'));

//            if ($this->userInfo->ACLRole == "User"){
//                $arrHeader = array ('#', 'PO', $this->translate->_('Item Name (Model Name)'),$this->translate->_('Part Number'), $this->translate->_('Serial Number'), $this->translate->_('Branch'),$this->translate->_('Status'),$this->translate->_('Notes'),$this->translate->_('View'));
//                $arrFormat = array('{format_counter}','%7%', '{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_branch}','{format_status}','{format_notes}', '{format_action}');
//                $tablewidth = "1050px";
//                $aligndata = "CRLLCCLC";
//                $export = "";
//
//            }else
//            {

            $arrHeader = array ('<input type=checkbox name="SelectAllItemSeries" id="SelectAllItemSeries">','#', 'PO',$this->translate->_('Item Name (Model Name)<BR>Part Number'),$this->translate->_('Serial Number'), $this->translate->_('Branch'),
                $this->translate->_('Date marked<BR>for rental'), $this->translate->_('Initial<BR>Asset Value'),$this->translate->_('Asset<BR>Lifespan'), $this->translate->_('Current<BR>Value'),$this->translate->_('Status'),$this->translate->_('Edit'));
            $arrFormat = array('{format_checkbox}','{format_counter}','%7%','{format_itemname}','{format_seriesnumber}','{format_branch}',
                '{format_dateasset}','{format_initvalue}', '{format_lifespan}', '{format_currentvalue}', '{format_status}','{format_action}');
            $tablewidth = "1550px";
            $aligndata = "CCRLCCCRCRCC";
            //$export = $exportReport->display_icon();

//            }
            $arrHeaderEx = array ('#','PO', $this->translate->_('Item Name (Model Name)'),$this->translate->_('Part Number'),$this->translate->_('Serial Number'), $this->translate->_('Branch'),  $this->translate->_('Unit Price'),$this->translate->_('Landed Cost'),$this->translate->_('Markup %'),$this->translate->_('Unit Retail'),$this->translate->_('Notes'),$this->translate->_('Status'));
            $arrFormatEx = array('{format_counter}','%7%','{format_itemname}','{format_partnumber}','{format_seriesnumber}','{format_branch}','{format_dateasset}','{format_unitprice}', '{format_unitlandedcost}', '{format_markup}','{format_retailprice}','{format_notes}','{format_status}');

            $this->view->totalItems = $arrItem[0];

            $strUpdateButton = "<BR><input type=button name='UpdateStatus' id='UpdateStatus' value='Update Status' disabled>";

            $displayTable = new Venz_App_Display_Table(
                array (
                    'data' => $dataItem,
                    'hiddenparamtop'=> $strSearch,
                    'headings' => $arrHeader,
                    'format' 		=> $arrFormat,
                    'sort_column' 	=> array('','','PurchaseOrders.OrderNumber','ItemFullName','ItemSeries.SeriesNumber','BranchName','RentalAsset.DateAsAsset',
                        'RentalAsset.AssetInitialValue','Lifespan','CurrentValue','RentalAsset.RentalStatus'),
                    'alllen' 		=> $arrItem[0],
                    'title'		=> $this->translate->_('Items Series').": ". $arrItem[0]." ".$this->translate->_('items').$strUpdateButton,
                    'aligndata' 	=> $aligndata,
                    'pagelen' 		=> $recordsPerPage,
                    'numcols' 		=> sizeof($arrHeader),
                    'tablewidth' => $tablewidth,
//                    'export_excel' => $export,
                    'sortby' => $sortby,
                    'colparam'      => array("","","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap","nowrap width='100px'","nowrap width='100px'"),
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

    public function detailAction()
    {

        try {
            $Request = $this->getRequest();

            $db = Zend_Db_Table::getDefaultAdapter();
            $sysHelper = new Venz_App_System_Helper();
            $libInv = new Venz_App_Inventory_Helper();
            $libRental = new Venz_App_Inventory_Rental();
            $libDb = new Venz_App_Db_Table();
            $displayFormat = new Venz_App_Display_Format();
            $sysNotification = new Venz_App_System_Notification();
            $dbJob = new Venz_App_Db_Job();

            $systemSetting = new Zend_Session_Namespace('systemSetting');
            $this->view->currencyType = $systemSetting->arrCurrency[$systemSetting->currency][0];

            $dispFormat = new Venz_App_Display_Format();
            $invRental = new Venz_App_Inventory_Rental();

            /////////////////////////// DEALING WITH PAGINGS AND SORTING ///////////////////////////
            if (!$this->userInfo){
                $this->appMessage->setMsg(0, "Please login first before accessing this page.");
                $this->_redirect('/auth');
            }
            $RentalAssetID = $Request->getParam('id');
            $accessFrom = $Request->getParam('f');
            $this->view->accessFrom = $accessFrom;
            $add_series = $Request->getParam('add_series');
            $this->view->add_series = $add_series;



            $update_item = $Request->getParam('update_item');
            if ($update_item)
            {


                $ItemID = $Request->getParam('ItemID') ? $Request->getParam('ItemID') : new Zend_Db_Expr("NULL");
                $ItemSeriesID = $Request->getParam('ItemSeriesID');
                $SeriesNumber = $Request->getParam('SeriesNumber') ? $Request->getParam('SeriesNumber') : new Zend_Db_Expr("NULL");

                if ($ItemSeriesID){
                    $arrUpdate = array("SeriesNumber"=>$SeriesNumber);
                    $db->update("ItemSeries", $arrUpdate, "ID=".$ItemSeriesID);

                }

                $this->appMessage->setNotice(1, $this->translate->_('Item series has been updated'));
                $this->_redirect('/inventory/rental/detail/id/'.$RentalAssetID);


            }

            $remove_status = $Request->getParam('remove_status');
            if ($remove_status)
            {
                $db->delete("RentalAssetStatus", "ID=".$remove_status);
                $arrLatestStatus = $db->fetchRow("SELECT * FROM RentalAssetStatus WHERE RentalAssetID=".$RentalAssetID." ORDER BY StatusDate Desc, EntryDateTime Desc");

                $LatestStatus = $arrLatestStatus['RentalStatus'] ? $arrLatestStatus['RentalStatus'] : "available";
                $db->update("RentalAsset", array("RentalStatus"=>$LatestStatus), "ID=".$RentalAssetID);

                $this->appMessage->setNotice(1, "The entry has been removed.");
                $this->_redirect('/inventory/rental/detail/id/'.$RentalAssetID);

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
                $SeriesNumber = $Request->getParam('SeriesNumber');
                $ReferenceNo = $Request->getParam('ReferenceNo') ? $Request->getParam('ReferenceNo') : new Zend_Db_Expr("NULL");
                $StatusDate = $Request->getParam('StatusDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('StatusDate')) : new Zend_Db_Expr("NULL");
                $RentalStatus = $Request->getParam('RentalStatus') ? $Request->getParam('RentalStatus') : new Zend_Db_Expr("NULL");
                $UserIDResp = $Request->getParam('UserIDResp') ? $Request->getParam('UserIDResp') : new Zend_Db_Expr("NULL");
                $Notes = $Request->getParam('Notes') ? $Request->getParam('Notes') : new Zend_Db_Expr("NULL");

                $ClientName = $Request->getParam('ClientName') ? $Request->getParam('ClientName') : new Zend_Db_Expr("NULL");
                $EstimatedReturnDate = $Request->getParam('EstimatedReturnDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('EstimatedReturnDate')) : new Zend_Db_Expr("NULL");
                $ActualReturnDate = $Request->getParam('ActualReturnDate') ? $dispFormat->format_date_simple_to_db($Request->getParam('ActualReturnDate')) : new Zend_Db_Expr("NULL");


                $arrInsert = array("RentalAssetID"=>$RentalAssetID,"StatusDate"=>$StatusDate,"RentalStatus"=>$RentalStatus,"UserIDEntry"=>$this->userInfo->ID,"EntryDateTime"=>new Zend_Db_Expr("now()"),
                    "UserIDResp"=>$UserIDResp,"Notes"=>$Notes,"ReferenceNo"=>$ReferenceNo,"ClientName"=>$ClientName,"EstimatedReturnDate"=>$EstimatedReturnDate,"ActualReturnDate"=>$ActualReturnDate
                );
                $db->insert("RentalAssetStatus", $arrInsert);

                $arrUpdate = array("RentalStatus"=>$RentalStatus);
                $db->Update("RentalAsset", $arrUpdate, "ID=".$RentalAssetID);

                $this->appMessage->setNotice(1, $this->translate->_('Item series status')." \"<B>".$SeriesNumber."</B>\" ".$this->translate->_('has been updated').".");
                $this->_redirect('/inventory/rental/detail/id/'.$RentalAssetID);


            }

            if ($RentalAssetID)
            {
                $arrRentalDetail = $libInv->getItemsSeriesRentalDetail($RentalAssetID);

                $this->view->RentalAssetID = $RentalAssetID;

                //print_r($arrRentalDetail);

                $this->view->ItemID = $arrRentalDetail['ItemID'];
                $this->view->ItemSeriesID = $arrRentalDetail['ItemSeriesID'];
                $this->view->OrderNumber = $arrRentalDetail['OrderNumber'];
                $this->view->POLocked = $arrRentalDetail['POLocked'];
                $this->view->ItemFullName = $arrRentalDetail['ItemFullName'];
                $this->view->PurchaseDate = $displayFormat->format_date($arrRentalDetail['PurchaseDate']);
                $this->view->SeriesNumber = $arrRentalDetail['SeriesNumber'];
                $this->view->BranchID = $arrRentalDetail['BranchID'];
                $this->view->UnitPriceRM = $arrRentalDetail['UnitPriceRM'];
                $this->view->UnitDeliveryCost = $arrRentalDetail['UnitDeliveryCost'];
                $this->view->UnitTaxCost = $arrRentalDetail['UnitTaxCost'];
                $this->view->UnitLandedCost = $arrRentalDetail['UnitLandedCost'];
                $this->view->RentalStatus = $arrRentalDetail['RentalStatus'];
                $this->view->strStatus = $systemSetting->arrRentalStatus[$this->view->RentalStatus];

                $this->view->RetailPrice = $arrRentalDetail['UnitRetail'];
                $this->view->MarkupPercent = $arrRentalDetail['MarkupPercent'];
                $this->view->SalesOrderNumber = $arrRentalDetail['SalesOrderNumber'];

                $this->view->ItemImagePath = $arrRentalDetail['ItemImagePath'];

                $arrStockCountAvailable = $libRental->getRentalStockDetail($RentalAssetID);
                $this->view->NumStockTotal = $arrStockCountAvailable['NumStock'];
                $arrStockCountAvailable = $libRental->getRentalStockDetail($RentalAssetID, 'available');
                $this->view->NumStockAvailable = $arrStockCountAvailable['NumStock'];


                $this->view->SOLink = "";
                if ($arrRentalDetail['SOItemsID'])
                {
                    $arrSODetail = $db->fetchRow("SELECT SalesOrders.ID from SOItems, SalesOrders where SalesOrders.ID=SOItems.OrderID AND SOItems.ID=".$arrRentalDetail['SOItemsID']);
                    $this->view->SOLink = "<BR><a href='/inventory/so/index/edit_so/".$arrSODetail['ID']."'><B>view</B></a>";

                }

                $this->view->optionCustomers = $dbJob->getTableOptions("Customers", "Name", "Name", NULL, "Name", NULL, "GROUP BY Name");

                $arrItemStatusAll = $libRental->getItemRentalStatus($RentalAssetID);
                //print_r($arrItemStatus);
                $counter=0;
                foreach ($arrItemStatusAll as $arrItemStatus){
                    $counter++;
                    $strStatus = $systemSetting->arrRentalStatus[$arrItemStatus['RentalStatus']];
                    $strEntryDateTime = $displayFormat->format_datetime($arrItemStatus['EntryDateTime']);
                    $strDeleteStatus = "";
                    if ($this->view->userInfo->ACLRole != "User" && $this->userInfo->ACLRole != "Sales"  && $this->userInfo->ACLRole != "Account" && $counter === 1)
                        $strDeleteStatus = "<input type=button name='delete_status' id='delete_status' value='".$this->translate->_('Delete Entry')."' onclick='OnDeleteStatus($arrItemStatus[ID])'>";

                    $strDetail = "";
                    if ($arrItemStatus['RentalStatus'] == "out"){

                        $strDetail = $arrItemStatus['ClientName'] ? "<B>Customer</B>:<BR><U>".$arrItemStatus['ClientName']."</U><BR>" : "";
                        $strDetail .= $arrItemStatus['EstimatedReturnDate'] ?
                            "<B>Estimated Return Date</B>:<BR><U>".$displayFormat->format_date($arrItemStatus['EstimatedReturnDate'])."</U><BR>" : "";
                    }else if ($arrItemStatus['RentalStatus'] == "returned"){

                        $strDetail = $arrItemStatus['ActualReturnDate'] ?
                            "<B>Return Date</B>:<BR><U>".$displayFormat->format_date($arrItemStatus['ActualReturnDate'])."</U><BR>" : "";
                    }

                    $this->view->status .= <<<END
    <tr>
		<td class="report_even" style="text-align:center">$arrItemStatus[ReferenceNo]</td>
		<td class="report_even" style="text-align:center">$arrItemStatus[StatusDate]</td>
		<td class="report_even" style="text-align:center">$strStatus</td>
		<td class="report_even" style="text-align:left">$strDetail</td>
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
            $this->view->optionRentalStatus = $libDb->getSystemOptions("arrRentalStatus", NULL,
                $arrRentalDetail['RentalStatus'] == 'available' ? array('out', 'service', 'writeoff') : (
                    $arrRentalDetail['RentalStatus'] == 'out' ? array('returned') : (
                        $arrRentalDetail['RentalStatus'] == 'returned' ? array('service') : (
                            $arrRentalDetail['RentalStatus'] == 'service' ? array('available', 'writeoff') : array()))));
            $this->view->optionStatusItem = $libDb->getSystemOptions("arrRentalStatus", $this->view->RentalStatus);
            $this->view->optionItems = $libInv->getItemOptions($this->view->ItemID);




        }catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    public function ajaxgetretailAction()
    {
        $Request = $this->getRequest();
        $db = Zend_Db_Table::getDefaultAdapter();
        $RentalAssetID = $Request->getParam('ID');
        $arrItemSeries = $db->fetchRow("SELECT * FROM ItemSeries where ID=".$RentalAssetID);
        $dispFormat = new Venz_App_Display_Format();
        print $dispFormat->format_currency($arrItemSeries['UnitRetail']);
        exit();
    }

    public function ajaxinitvalueAction()
    {
        $Request = $this->getRequest();
        $db = Zend_Db_Table::getDefaultAdapter();
        $val = $Request->getParam('value');
        $valNum = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
        $RentalAssetID = $Request->getParam('id');
        $arrUpdate = array("AssetInitialValue"=>$valNum);
        $db->update("RentalAsset", $arrUpdate, "ID=".$RentalAssetID);
        $dispFormat = new Venz_App_Display_Format();
        echo $dispFormat->format_currency($valNum);
        exit();
    }

    public function ajaxcurrentvalueAction()
    {
        $Request = $this->getRequest();
        $db = Zend_Db_Table::getDefaultAdapter();
        $val = $Request->getParam('value');
        $valNum = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
        $RentalAssetID = $Request->getParam('id');
        $arrUpdate = array("AssetCurrentValue"=>$valNum);
        $db->update("RentalAsset", $arrUpdate, "ID=".$RentalAssetID);
        $dispFormat = new Venz_App_Display_Format();
        echo $dispFormat->format_currency($valNum);
        exit();
    }


}

