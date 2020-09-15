<?php

class Venz_App_Inventory_Rental extends Zend_Db_Table_Abstract
{
    protected $_db  = NULL;

    public function __construct($DbMode = Zend_Db::FETCH_ASSOC)
    {
        parent::__construct();
        $this->_db = $this->getAdapter();
        $this->_db->setFetchMode($DbMode);
    }

    public function insertAsRental($ItemSeriesID,$POItemsID,$AssetInitialValue,$MonthDepreciation = NULL,$MonthRemaining = NULL){
        $POItemsID = $POItemsID ? $POItemsID : new Zend_Db_Expr("NULL");
        $AssetInitialValue = $AssetInitialValue ? $AssetInitialValue : new Zend_Db_Expr("NULL");
        $MonthDepreciation = $MonthDepreciation ? $MonthDepreciation : new Zend_Db_Expr("NULL");
        $MonthRemaining = $MonthRemaining ? $MonthRemaining : new Zend_Db_Expr("NULL");

        $arrRecord = $this->_db->fetchRow("SELECT * FROM RentalAsset WHERE ItemSeriesID=".$ItemSeriesID);
        if (!$arrRecord){
            $arrInsert = array("ItemSeriesID"=>$ItemSeriesID, "POItemsID"=>$POItemsID, "AssetInitialValue"=>$AssetInitialValue, "AssetCurrentValue"=>$AssetInitialValue,
                "MonthDepreciation"=>$MonthDepreciation,"MonthRemaining"=>$MonthRemaining,"DateAsAsset"=>new Zend_Db_Expr("now()"));
            $this->_db->insert("RentalAsset", $arrInsert);

        }

    }


    public function updateAssetByPO($POItemsID, $AssetInitialValue){

        $arrRentalAssets = $this->_db->fetchAll("SELECT * FROM RentalAsset WHERE POItemsID=".$POItemsID);

        if ($arrRentalAssets){
            $arrUpdate = array("AssetInitialValue"=>$AssetInitialValue);
            $this->_db->update("RentalAsset", $arrUpdate, "POItemsID=".$POItemsID);
        }else{
            $arrItemSeriesAll = $this->_db->fetchAll("SELECT * FROM ItemSeries WHERE POItemsID=".$POItemsID);
            foreach ($arrItemSeriesAll as $arrItemSeries){
                $arrInsert = array("ItemSeriesID"=>$arrItemSeries['ID'], "POItemsID"=>$POItemsID, "AssetInitialValue"=>$arrItemSeries['UnitLandedCost'], "AssetCurrentValue"=>$arrItemSeries['UnitLandedCost'], "DateAsAsset"=>new Zend_Db_Expr("now()"));
                $this->_db->insert("RentalAsset", $arrInsert);
            }
        }

    }

    public function getRentalStockDetail($RentalAssetID, $RentalStatus = NULL){

        $sql = "SELECT count(*) as NumStock FROM ItemSeries, 
            (SELECT Item.ID FROM RentalAsset, ItemSeries, Item WHERE RentalAsset.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID ".
                "AND RentalAsset.ID=".$RentalAssetID.") as Item, RentalAsset WHERE ItemSeries.ItemID=Item.ID AND ItemSeries.Status='rental_asset' ".
                "AND RentalAsset.ItemSeriesID=ItemSeries.ID ";

        if ($RentalStatus)
        {
            $sql = $sql. "AND RentalAsset.RentalStatus='".$RentalStatus."'";
        }

        return $this->_db->fetchRow($sql);

    }

    public function getItemRentalStatus($RentalAssetID){
        $sql = "SELECT RentalAssetStatus.*, UserIncharge.Name as UserInchargeName, UserEntry.Name as UserEntryName FROM RentalAssetStatus ".
            " LEFT JOIN ACLUsers as UserIncharge ON (UserIncharge.ID=RentalAssetStatus.UserIDResp)".
            " LEFT JOIN ACLUsers as UserEntry ON (UserEntry.ID=RentalAssetStatus.UserIDEntry)".
            " WHERE RentalAssetID=".$RentalAssetID." order by StatusDate desc, EntryDateTime Desc";
        return $this->_db->fetchAll($sql);
    }


    public function clearAssetByPO($POItemsID){
        $this->_db->delete("RentalAsset", "POItemsID=".$POItemsID);

    }

    public function getItemRentalID(){
        $sqlRentalItem = "SELECT group_concat(DISTINCT Item.ID) as ItemID FROM Item, ItemSeries, RentalAsset WHERE 
            RentalAsset.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID";
        $arrRentalItems = $this->_db->fetchRow($sqlRentalItem);
        return $arrRentalItems['ItemID'];
    }

    public function getBrandRentalID(){
        $sqlRentalItem = "SELECT group_concat(DISTINCT Item.BrandID) as BrandID FROM Item, ItemSeries, RentalAsset WHERE 
            RentalAsset.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID";
        $arrRentalItems = $this->_db->fetchRow($sqlRentalItem);
        return $arrRentalItems['BrandID'];
    }

    public function getPORentalID(){
        $sqlRentalItem = "SELECT group_concat(DISTINCT POItems.OrderID) as OrderID FROM POItems, ItemSeries, RentalAsset WHERE 
            RentalAsset.ItemSeriesID=ItemSeries.ID AND POItems.ID=ItemSeries.POItemsID";
        $arrRentalItems = $this->_db->fetchRow($sqlRentalItem);
        return $arrRentalItems['OrderID'];
    }


    public function getItemBranchID(){
        $sqlRentalItem = "SELECT group_concat(DISTINCT ItemSeries.BranchID) as BranchID FROM ItemSeries, RentalAsset WHERE 
            RentalAsset.ItemSeriesID=ItemSeries.ID";
        $arrRentalItems = $this->_db->fetchRow($sqlRentalItem);
        return $arrRentalItems['BranchID'];
    }


}