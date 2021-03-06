<?php
 
class Venz_App_Inventory_Helper extends Zend_Db_Table_Abstract
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


    public function getBrand($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT ID, FullName, ShortName, CompanyName, BrandLogoPath, BrandLogoPathSmall, TotalItem FROM Brand LEFT JOIN (SELECT COUNT(*) as TotalItem, BrandID from Item Group by Item.BrandID) as ItemCount ON (Brand.ID=ItemCount.BrandID) WHERE 1=1";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getBrandDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT Brand.* FROM Brand  WHERE 1=1";
		if ($ID)
			$sql .= " and ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 	
	

    public function getItem($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT Item.ID, Brand.FullName as BrandName, ItemName, ModelNumber, PartNumber, ".
            /*5*/"ItemImagePath, ItemImagePathSmall, Item.RetailPrice, IF (ItemCount.NumStock, ItemCount.NumStock, 0) as NumStock, Item.MinStock, ".
            /*10*/"Item.MonthDepreciation ".
		" FROM Item LEFT JOIN (SELECT count(*) as NumStock, ItemID from ItemSeries where Status='in' group by ItemID) as ItemCount ON (ItemCount.ItemID=Item.ID), Brand  WHERE Item.BrandID=Brand.ID ";		
		if ($searchString)
			$sqlAll .= $searchString;
		//$sqlAll .= " GROUP BY Item.ID ";	
			
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }

    public function getItemDetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT Item.*, Brand.ID as BrandID, Brand.FullName as BrandName, IF (ItemCount.NumStock, ItemCount.NumStock, 0) as NumStock FROM  Item LEFT JOIN (SELECT count(*) as NumStock, ItemID from ItemSeries where Status='in' group by ItemID) as ItemCount ON (ItemCount.ItemID=Item.ID), 
			Brand  WHERE  Item.BrandID=Brand.ID  ";
		if ($ID)
			$sql .= " and Item.ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}

    public function getSODetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT SalesOrders.*, Customers.Name as CustomerName, Branches.Name as BranchName, ACLUsers.Name as LockByName FROM  SalesOrders LEFT JOIN Customers ON (Customers.ID=SalesOrders.CustomerID) ".
		" LEFT JOIN Branches ON (Branches.ID=SalesOrders.BranchID) LEFT JOIN ACLUsers ON (ACLUsers.ID=SalesOrders.LockedBy) WHERE 1=1  ";
		if ($ID)
			$sql .= " and SalesOrders.ID=".$ID;	

		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 	
				
    public function getSO($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT SalesOrders.ID, OrderNumber, SalesDate, SubtotalCurrency, SODeliveryCharge, ".
		/*5*/"SOTaxCharge, Total, SOFilePath, TotalItem, Branches.Name as BranchName, Locked ".
		"FROM SalesOrders LEFT JOIN (SELECT SUM(Quantity) as TotalItem, OrderID FROM SOItems GROUP BY OrderID) as ItemCount ON (ItemCount.OrderID=SalesOrders.ID) ".
		"LEFT JOIN Branches ON (Branches.ID=SalesOrders.BranchID) WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }
	
    public function getSOItems($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT OrderID, SOItems.ItemID, Quantity, SOItems.UnitPrice, SalesOrders.OrderNumber, Item.ItemName, Item.ModelNumber, Item.RetailPrice, SOItems.ID, ItemSeries.MarkupPercent, ItemSeries.UnitRetail, ".
		"SOItems.UnitDiscount, SOItems.UnitDiscountType, SOItems.SubTotal FROM SOItems LEFT JOIN SalesOrders ON (SalesOrders.ID=SOItems.OrderID) LEFT JOIN Item ON (Item.ID=SOItems.ItemID) LEFT JOIN ItemSeries ON (SOItems.ID=ItemSeries.POItemsID) WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." GROUP BY SOItems.ID order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }	
	
	
    public function getPODetail($ID = NULL, $searchString = null)
    {
		$sql = "SELECT PurchaseOrders.*, Vendors.Name as VendorName, Branches.Name as BranchName, ACLUsers.Name as LockByName FROM  PurchaseOrders LEFT JOIN Vendors ON (Vendors.ID=PurchaseOrders.VendorID) ".
		" LEFT JOIN Branches ON (Branches.ID=PurchaseOrders.BranchID) LEFT JOIN ACLUsers ON (ACLUsers.ID=PurchaseOrders.LockedBy) WHERE 1=1  ";
		if ($ID)
			$sql .= " and PurchaseOrders.ID=".$ID;	
		
	//	print $sql;
		if ($searchString)
			$sql .= " ".$searchString;	
		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
	}	 	
				
    public function getPO($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT PurchaseOrders.ID, OrderNumber, PurchaseDate, ProductCostRM, PODeliveryCost, ".
		/*5*/"POTaxCost, TotalCost, POFilePath, TotalItem, Branches.Name as BranchName, Locked, AOFilePath ".
		"FROM PurchaseOrders LEFT JOIN (SELECT SUM(Quantity) as TotalItem, OrderID FROM POItems GROUP BY OrderID) as ItemCount ON (ItemCount.OrderID=PurchaseOrders.ID) ".
		"LEFT JOIN Branches ON (Branches.ID=PurchaseOrders.BranchID) WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }
	
    public function getPOItems($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT OrderID, POItems.ItemID, Quantity, POItems.UnitPrice, DeliveryCost, TaxCost, LandedCost, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, Item.RetailPrice, POItems.ID, ItemSeries.MarkupPercent, ItemSeries.UnitRetail, ".
		"POItems.UnitDiscount, POItems.UnitDiscountType, POItems.Status, POItems.StatusDate ".
		"FROM POItems LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID) LEFT JOIN Item ON (Item.ID=POItems.ItemID) LEFT JOIN ItemSeries ON (POItems.ID=ItemSeries.POItemsID) WHERE 1=1 ";		
		if ($searchString)
			$sqlAll .= $searchString;
		$sql .= $sqlAll." GROUP BY POItems.ID order by $sql_orderby $sql_limit";


		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql));
    }	
	
	public function getItemsSeriesStockCount($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null, $dateasoff = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";

		if (is_null($dateasoff))
		{
			$dateasoff = strftime("Y-m-d", time());
		}
		
//		$sqlLatestStatusOrder = "SELECT ItemSeriesStatus.* FROM ItemSeriesStatus WHERE ItemSeriesStatus.StatusDate <= '".$dateasoff."' AND ItemSeriesStatus.StatusDate IS NOT NULL order by StatusDate Desc, EntryDateTime Desc LIMIT 18446744073709551615";
        $sqlLatestStatusOrder = "SELECT ItemSeriesStatus.* FROM ItemSeriesStatus WHERE ItemSeriesStatus.StatusDate <= '".$dateasoff."' AND ItemSeriesStatus.StatusDate IS NOT NULL order by StatusDate Desc, EntryDateTime Desc LIMIT 100000";
//        $sqlLatestStatusOrder = "SELECT ItemSeriesStatus.* FROM ItemSeriesStatus WHERE (ItemSeriesStatus.StatusDate <= '".$dateasoff."' OR ItemSeriesStatus.StatusDate IS NULL)  order by StatusDate Desc, EntryDateTime Desc";

		$sqlLatestStatusGroup = "SELECT MAX(ItemSeriesStatus.StatusDate), ItemSeriesStatus.* FROM (".$sqlLatestStatusOrder.") as ItemSeriesStatus GROUP BY ItemSeriesStatus.ItemSeriesID";
//        $sqlLatestStatusGroup = "SELECT MAX(IF (ItemSeriesStatus.StatusDate IS NULL, ItemSeriesStatus.EntryDateTime, ItemSeriesStatus.StatusDate)), ItemSeriesStatus.* FROM ItemSeriesStatus GROUP BY ItemSeriesStatus.ItemSeriesID";

		$sqlAll = "SELECT POItems.OrderID, POItems.ItemID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
		/*5*/"IF (ItemSeries.UnitLandedCost IS NULL, (POItems.LandedCost / POItems.Quantity), ItemSeries.UnitLandedCost) as LandedCost , ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
		/*10*/"Item.RetailPrice, ItemSeries.ID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
		/*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, ItemSeries.Status, ItemSeries.MarkupPercent, ItemSeries.SalesOrderNumber, ".
		/*20*/"ItemSeries.UnitRetail, Item.ItemImagePath, PurchaseOrders.ExpectedDate, LatestStatus.Notes, PurchaseOrders.Locked, LatestStatus.Status as StatusAsOff,".
            /*26*/"LatestStatus.StatusDate, DATEDIFF('$dateasoff', IF(LatestStatus.StatusDate IS NULL, LatestStatus.EntryDateTime, LatestStatus.StatusDate)) as StatusDays, Item.PartNumber ".
		"FROM ItemSeries LEFT JOIN ".
		"(".$sqlLatestStatusGroup.") as LatestStatus ON (LatestStatus.ItemSeriesID=ItemSeries.ID) ".
		"LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
		"LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item WHERE Item.ID=ItemSeries.ItemID ";

//
//        $sqlAll = "SELECT POItems.OrderID, POItems.ItemID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
//            /*5*/"ItemSeries.UnitLandedCost as LandedCost , ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
//            /*10*/"Item.RetailPrice, ItemSeries.ID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
//            /*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, ItemSeries.Status, ItemSeries.MarkupPercent, ItemSeries.SalesOrderNumber, ".
//            /*20*/"ItemSeries.UnitRetail, Item.ItemImagePath, PurchaseOrders.ExpectedDate, LatestStatus.Notes, PurchaseOrders.Locked, LatestStatus.Status as StatusAsOff,".
//            /*26*/"LatestStatus.StatusDate, DATEDIFF('$dateasoff', LatestStatus.StatusDate) as StatusDays ".
//            "FROM ItemSeries LEFT JOIN ".
//            "(".$sqlLatestStatusGroup.") as LatestStatus ON (LatestStatus.ItemSeriesID=ItemSeries.ID) ".
//            "LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
//            "LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item WHERE Item.ID=ItemSeries.ItemID ";


		if ($searchString)
			$sqlAll .= $searchString;

		//print $sqlAll;
		$sqlAll = $sqlAll." order by $sql_orderby ";
		$sql .= $sqlAll. $sql_limit;

		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql), $sqlAll);
    }



    public function getItemsSeriesRental($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
        if ($showPage	< 0 || $showPage == "") $showPage = 1;

        $sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
        $sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
        $count = $showPage -1;
        $sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";

        $sqlAll = "SELECT POItems.OrderID, POItems.ItemID as POItemsID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
            /*5*/"ItemSeries.UnitLandedCost, ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
            /*10*/"Item.RetailPrice, ItemSeries.ID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
            /*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, ItemSeries.Status, ItemSeries.MarkupPercent, ItemSeries.SalesOrderNumber, ".
            /*20*/"ItemSeries.UnitRetail, Item.ItemImagePath, PurchaseOrders.ExpectedDate, NULL, PurchaseOrders.Locked, Item.ID as ItemID, Item.PartNumber, ".
            /*27*/"RentalAsset.ID as RentalAssetID, RentalAsset.AssetInitialValue, RentalAsset.AssetCurrentValue, RentalAsset.RentalStatus, ".
            /*31*/"ItemSeries.ID as ItemSeriesID, RentalAsset.DateAsAsset, Item.MonthDepreciation, IF (Item.MonthDepreciation, Item.MonthDepreciation - TIMESTAMPDIFF(MONTH, RentalAsset.DateAsAsset, NOW()), 0) as Lifespan, ".
            /*35*/"IF (Item.MonthDepreciation, ((Item.MonthDepreciation - TIMESTAMPDIFF(MONTH, RentalAsset.DateAsAsset, NOW())) / Item.MonthDepreciation) * RentalAsset.AssetInitialValue, NULL) as CurrentValue, RentalAsset.MonthDepreciation, RentalAsset.MonthRemaining ".
            "FROM ItemSeries ".
            "LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
            "LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item, RentalAsset WHERE Item.ID=ItemSeries.ItemID AND RentalAsset.ItemSeriesID=ItemSeries.ID ";



        $sqlAll = "SELECT POItems.OrderID, POItems.ItemID as POItemsID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
            /*5*/"ItemSeries.UnitLandedCost, ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
            /*10*/"Item.RetailPrice, ItemSeries.ID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
            /*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, ItemSeries.Status, ItemSeries.MarkupPercent, ItemSeries.SalesOrderNumber, ".
            /*20*/"ItemSeries.UnitRetail, Item.ItemImagePath, PurchaseOrders.ExpectedDate, NULL, PurchaseOrders.Locked, Item.ID as ItemID, Item.PartNumber, ".
            /*27*/"RentalAsset.ID as RentalAssetID, RentalAsset.AssetInitialValue, RentalAsset.AssetCurrentValue, RentalAsset.RentalStatus, ".
            /*31*/"ItemSeries.ID as ItemSeriesID, RentalAsset.DateAsAsset, RentalAsset.MonthDepreciation, IF (RentalAsset.MonthRemaining, RentalAsset.MonthRemaining - TIMESTAMPDIFF(MONTH, RentalAsset.DateAsAsset, NOW()), 0) as Lifespan, ".
            /*35*/"IF (RentalAsset.MonthRemaining, ((RentalAsset.MonthRemaining - TIMESTAMPDIFF(MONTH, RentalAsset.DateAsAsset, NOW())) / RentalAsset.MonthDepreciation) * IF(POItems.OrderID IS NULL, RentalAsset.AssetInitialValue,ItemSeries.UnitLandedCost) , NULL) as CurrentValue, RentalAsset.MonthDepreciation, RentalAsset.MonthRemaining, ".
            /*38*/"RentalAssetStatus.ClientName, RentalAssetStatus.StatusDate, RentalAssetStatus.EstimatedReturnDate, DATEDIFF(RentalAssetStatus.EstimatedReturnDate, now()) as DaysRemaining ".
            "FROM ItemSeries ".
            "LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
            "LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item, RentalAsset LEFT JOIN ".
            " RentalAssetStatus ON (RentalAssetStatus.RentalAssetID=RentalAsset.ID AND RentalAssetStatus.ID=(SELECT MAX(ID) FROM RentalAssetStatus WHERE RentalAssetStatus.RentalAssetID=RentalAsset.ID))  ".
            "WHERE Item.ID=ItemSeries.ItemID AND RentalAsset.ItemSeriesID=ItemSeries.ID ";


        if ($searchString)
            $sqlAll .= $searchString;

        $sql .= $sqlAll." order by $sql_orderby $sql_limit";
        return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql), $sqlAll);
    }

    public function getItemsSeriesRentalDetail($ID = null, $searchString = null)
    {
        if ($showPage	< 0 || $showPage == "") $showPage = 1;

        $sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
        $sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
        $count = $showPage -1;
        $sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
        $sqlAll = "SELECT POItems.OrderID, ItemSeries.ItemID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
            /*5*/"ItemSeries.UnitLandedCost, ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
            /*10*/"Item.RetailPrice, ItemSeries.ID as ItemSeriesID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
            /*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, Branches.ID as BranchID, ItemSeries.Status, ItemSeries.MarkupPercent,  ".
            /*20*/"ItemSeries.SalesOrderNumber, ItemSeries.UnitRetail, ItemSeries.SOItemsID, Item.ItemImagePath, PurchaseOrders.Locked as POLocked, Item.ID as ItemID, Item.PartNumber, ".
            /*27*/"RentalAsset.ID as RentalAssetID, RentalAsset.AssetInitialValue, RentalAsset.AssetCurrentValue, RentalAsset.RentalStatus, ".
            /*31*/"RentalAsset.MonthDepreciation, RentalAsset.MonthRemaining, RentalAsset.DateAsAsset ".
            "FROM ItemSeries LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
            "LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item, RentalAsset  WHERE Item.ID=ItemSeries.ItemID AND RentalAsset.ItemSeriesID=ItemSeries.ID  ";

        if ($ID)
            $sqlAll .= " and RentalAsset.ID=".$ID;
        if ($searchString)
            $sqlAll .= $searchString;

        $sql .= $sqlAll." order by $sql_orderby $sql_limit";

        if ($ID)
            return $this->_db->fetchRow($sql);
        else
            return $this->_db->fetchAll($sql);
    }


    public function getItemsSeries($orderby = null, $ascdesc = null, $recordsPerPage = null, $showPage = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT POItems.OrderID, POItems.ItemID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
		/*5*/"ItemSeries.UnitLandedCost, ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
		/*10*/"Item.RetailPrice, ItemSeries.ID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
		/*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, ItemSeries.Status, ItemSeries.MarkupPercent, ItemSeries.SalesOrderNumber, ".
		/*20*/"ItemSeries.UnitRetail, Item.ItemImagePath, PurchaseOrders.ExpectedDate, LatestNotes.Notes, PurchaseOrders.Locked, Item.PartNumber ".
		"FROM ItemSeries LEFT JOIN ".
		"(SELECT Notes, ItemSeries.ID as ItemSeriesID FROM (SELECT ItemSeriesStatus.ID, ItemSeriesStatus.Notes, ItemSeriesStatus.ItemSeriesID FROM ItemSeriesStatus WHERE ItemSeriesStatus.Notes IS NOT NULL order by StatusDate Desc) as ItemSeriesStatus,  ItemSeries WHERE ItemSeries.ID=ItemSeriesStatus.ItemSeriesID GROUP BY ItemSeriesStatus.ItemSeriesID) as LatestNotes ON (LatestNotes.ItemSeriesID=ItemSeries.ID) ".
		"LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
		"LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item WHERE Item.ID=ItemSeries.ItemID ";		
		
		
		$sqlAll = "SELECT POItems.OrderID, POItems.ItemID as POItemsID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
		/*5*/"ItemSeries.UnitLandedCost, ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
		/*10*/"Item.RetailPrice, ItemSeries.ID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
		/*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, ItemSeries.Status, ItemSeries.MarkupPercent, ItemSeries.SalesOrderNumber, ".
		/*20*/"ItemSeries.UnitRetail, Item.ItemImagePath, PurchaseOrders.ExpectedDate, NULL, PurchaseOrders.Locked, Item.ID as ItemID, Item.PartNumber ".
		"FROM ItemSeries ".
		"LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
		"LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item WHERE Item.ID=ItemSeries.ItemID ";	
		
		if ($searchString)
			$sqlAll .= $searchString;

		$sql .= $sqlAll." order by $sql_orderby $sql_limit";
	
		return array(sizeof($this->_db->fetchAll($sqlAll)), $this->_db->fetchAll($sql), $sqlAll);
    }	

	public function getItemsSeriesDetail($ID = null, $searchString = null)
    {
		if ($showPage	< 0 || $showPage == "") $showPage = 1;
				
		$sql_orderby =  is_null($orderby) ? "Item.ID" : $orderby;
		$sql_orderby .= strlen($sql_orderby) == 0 ? "" : " " . $ascdesc ;
		$count = $showPage -1;
		$sql_limit = isset($recordsPerPage) ? " limit " . ($count * $recordsPerPage) . ", " . $recordsPerPage : "";
		$sqlAll = "SELECT POItems.OrderID, ItemSeries.ItemID, ItemSeries.UnitPriceRM, ItemSeries.UnitDeliveryCost, ItemSeries.UnitTaxCost, ".
		/*5*/"ItemSeries.UnitLandedCost, ItemSeries.SeriesNumber, PurchaseOrders.OrderNumber, Item.ItemName, Item.ModelNumber, ".
		/*10*/"Item.RetailPrice, ItemSeries.ID as ItemSeriesID, CONCAT(Item.ItemName, ' (', Item.ModelNumber, ')') as ItemFullName, PurchaseOrders.ID as POID, POItems.ID as POItemsID, ".
		/*15*/"PurchaseOrders.PurchaseDate, Branches.Name as BranchName, Branches.ID as BranchID, ItemSeries.Status, ItemSeries.MarkupPercent,  ".
		/*20*/"ItemSeries.SalesOrderNumber, ItemSeries.UnitRetail, ItemSeries.SOItemsID, Item.ItemImagePath, PurchaseOrders.Locked as POLocked  ".
		"FROM ItemSeries LEFT JOIN POItems ON (POItems.ID=ItemSeries.POItemsID) LEFT JOIN PurchaseOrders ON (PurchaseOrders.ID=POItems.OrderID ) ".
		"LEFT JOIN Branches ON (ItemSeries.BranchID=Branches.ID), Item WHERE Item.ID=ItemSeries.ItemID  ";		
		if ($ID)
			$sqlAll .= " and ItemSeries.ID=".$ID;		
		if ($searchString)
			$sqlAll .= $searchString;
	
		$sql .= $sqlAll." order by $sql_orderby $sql_limit";

		if ($ID)
			return $this->_db->fetchRow($sql);
		else
			return $this->_db->fetchAll($sql);
    }	

	public function getItemsSeriesStatus($ItemSeriesID = null, $searchString = null)
    {
		$sqlAll = "SELECT ItemSeriesStatus.*, UserIncharge.Name as UserInchargeName, UserEntry.Name as UserEntryName, TransitBranch.Name as TransitLocation from ItemSeriesStatus LEFT JOIN Branches as TransitBranch ON (TransitBranch.ID=ItemSeriesStatus.TransitTo), ACLUsers as UserIncharge, ACLUsers as UserEntry where ".
		"UserIncharge.ID=ItemSeriesStatus.UserIDResp and UserEntry.ID=ItemSeriesStatus.UserIDEntry and ItemSeriesStatus.ItemSeriesID=".$ItemSeriesID;		
		if ($searchString)
			$sqlAll .= $searchString;
		
		$sql .= $sqlAll." order by ItemSeriesStatus.StatusDate desc, ItemSeriesStatus.ID desc";

		return $this->_db->fetchAll($sql);
    }			

	public function getItemOptions($defaultValue = NULL, $searchString = null, $defaultStatus = 'in')
   {

		$systemSetting = new Zend_Session_Namespace('systemSetting');
	   	$sqlAll = "select Item.*, Brand.FullName as BrandName, IF (ItemCount.TotalItem IS NULL, '0', ItemCount.TotalItem ) as TotalItem from Item LEFT JOIN (SELECT count(*)TotalItem, Item.ID as ItemID FROM ItemSeries, Item WHERE Item.ID=ItemSeries.ItemID AND ItemSeries.Status='".$defaultStatus."' group by Item.ID) as ItemCount ON (ItemCount.ItemID=Item.ID),".
		"Brand where Item.BrandID=Brand.ID ";

       if ($searchString)
           $sqlAll .= $searchString;

        $sql .= $sqlAll." order by ItemName ";

		$record = $this->_db->fetchAll($sql);

		$option_string = "";
		foreach ($record as $index => $TypeData)
		{	
			if (!is_null($defaultValue))
			{
				if ($defaultValue == $TypeData['ID'])
					$option_string .= "<option value='".$TypeData['ID']."' selected>".$systemSetting->translate->_($TypeData['ItemName']) . " - ".$systemSetting->translate->_($TypeData['ModelNumber'])." (".$TypeData['TotalItem'].")</option>";
				else
					$option_string .= "<option value='".$TypeData['ID']."'>".$systemSetting->translate->_($TypeData['ItemName']) . " - ".$systemSetting->translate->_($TypeData['ModelNumber'])." (".$TypeData['TotalItem'].")</option>";
			}else
				$option_string .= "<option value='".$TypeData['ID']."'>".$systemSetting->translate->_($TypeData['ItemName']) . " - ".$systemSetting->translate->_($TypeData['ModelNumber'])." (".$TypeData['TotalItem'].")</option>";
		}
		return $option_string;
   }
   
   
   public function getItemOptionsEx($defaultValue = NULL)
   {

		$systemSetting = new Zend_Session_Namespace('systemSetting');
		
		
		
	   	$sql = "select Item.*, Brand.FullName as BrandName, IF (ItemCount.TotalItem IS NULL, '0', ItemCount.TotalItem ) as TotalItem from Item LEFT JOIN (SELECT count(*)TotalItem, Item.ID as ItemID FROM ItemSeries, Item WHERE Item.ID=ItemSeries.ItemID AND ItemSeries.Status='in' group by Item.ID) as ItemCount ON (ItemCount.ItemID=Item.ID),".
		"Brand where Item.BrandID=Brand.ID order by BrandName, ItemName ";

		$record = $this->_db->fetchAll($sql);

		$option_string = "";
		
	
		
		foreach ($record as $index => $TypeData)
		{	
			$strImage = "";
			if ( $TypeData['ItemImagePath'])
			{
				$strImage = "data-imagesrc='".$TypeData['ItemImagePath']."'";
			}
		
			if (!is_null($defaultValue))
			{
				if ($defaultValue == $TypeData['ID'])
					$option_string .= "<option data-description=\"".$TypeData['ModelNumber']."<BR>".$TypeData['PartNumber']."\" ".$strImage." value='".$TypeData['ID']."' selected>".$TypeData['BrandName'] ." - ". $TypeData['ItemName'] . " (".$TypeData['TotalItem'].")</option>";
				else
					$option_string .= "<option data-description=\"".$TypeData['ModelNumber']."<BR>".$TypeData['PartNumber']."\" ".$strImage." value='".$TypeData['ID']."'>".$TypeData['BrandName'] ." - ". $TypeData['ItemName'] . " (".$TypeData['TotalItem'].")</option>";
			}else
				$option_string .= "<option data-description=\"".$TypeData['ModelNumber']."<BR>".$TypeData['PartNumber']."\" ".$strImage." value='".$TypeData['ID']."'>".$TypeData['BrandName'] ." - ". $TypeData['ItemName'] . " (".$TypeData['TotalItem'].")</option>";
		}
		return $option_string;
   }


   
}



?>