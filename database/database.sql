CREATE DATABASE  `Inventory` ;



CREATE TABLE IF NOT EXISTS `ACLUsers` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ACLRole` varchar(255) NOT NULL,
  `Name` varchar(255) default NULL,
  `Username` varchar(64) NOT NULL,
  `Password` varchar(100) default NULL,
  `Email` varchar(255) NOT NULL,
  `Active` tinyint(4) NOT NULL default '1',
  `LastLogin` datetime NOT NULL,
  `UserCreated` varchar(64) NOT NULL,
  `DateCreated` datetime default NULL,
  `UserModified` varchar(64) NOT NULL,
  `DateModified` datetime default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;




CREATE TABLE IF NOT EXISTS `ACLRole` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(30) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `ParentName` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `ACLPriviledges` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `Description` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `ACLResources` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `Description` varchar(255) default NULL, 
  `Category` varchar(255) default NULL,
  `ParentName` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `ACLMap` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Role` varchar(255) default NULL,
  `Resources` varchar(255) default NULL, 
  `Priviledges` varchar(255) default NULL,
  `Allow` int(1) default 1,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY (`Role`,`Resources`,`Priviledges`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- February 12, 2014 --
CREATE TABLE IF NOT EXISTS `Brand` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `FullName` varchar(255) default NULL,
  `ShortName` varchar(35) default NULL, 
  `CompanyName` varchar(255) default NULL,
  `BrandLogoPath` varchar(255) default NULL,
  `BrandLogoPathSmall` varchar(255) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `Item` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `BrandID` int(10) default NULL,
  `ItemName` varchar(255) default NULL,
  `ModelNumber` varchar(255) default NULL,
  `PartNumber` varchar(255) default NULL,
  `ItemImagePath` varchar(255) default NULL,
  `ItemImagePathSmall` varchar(255) default NULL,
  `RetailPrice` float(10,2) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- February 16, 2014 --

CREATE TABLE IF NOT EXISTS `PurchaseOrders` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `VendorID` int(10) default NULL,
  `OrderNumber` varchar(255) default NULL,
  `PurchaseDate` date default NULL,
  `POFilePath` varchar(255) default NULL, /* upload of PO document */
  `ProductCost` float(10,2) default NULL,
  `Currency` varchar(8) default NULL,
  `ProductCostRM` float(10,2) default NULL,
  `Multiplier` float(6,2) default NULL,
  `PODeliveryCost` float(10,2) default NULL,
  `POTaxCost` float(10,2) default NULL,
  `POMiscCost` float(10,2) default NULL,
  `POMiscNote` varchar(255) default NULL,
  `TotalCost` float(10,2) default NULL,
  
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `POItems` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `OrderID` int(10) default NULL,
  `ItemID` int(10) default NULL,
  `Quantity` int(5) default NULL,
  `UnitPrice` float(10,2) default NULL,
  `UnitPriceRM` float(10,2) default NULL,
  `DeliveryCost` float(10,2) default NULL,
  `TaxCost` float(10,2) default NULL,
  `LandedCost` float(10,2) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `ItemSeries` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `POItemsID` int(10) default NULL,
  `ItemID` int(10) default NULL,
  `UnitPrice` float(10,2) default NULL,
  `UnitPriceRM` float(10,2) default NULL,
  `UnitDeliveryCost` float(10,2) default NULL,
  `UnitTaxCost` float(10,2) default NULL,
  `UnitLandedCost` float(10,2) default NULL,
  `SeriesNumber` varchar(255) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `Vendors` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `Address` varchar(1024) NULL,
  `Phone` varchar(32) NULL,
  `Email` varchar(255) NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `Branches` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `Location` varchar(255) default NULL,
  `Address` varchar(1024) NULL,
  `Phone` varchar(32) NULL,
  `Email` varchar(255) NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

ALTER TABLE `PurchaseOrders` ADD `BranchID` int(10) default NULL;
ALTER TABLE `ItemSeries` ADD `BranchID` int(10) default NULL;

ALTER TABLE `PurchaseOrders` ADD `Locked` int(2) default 0;
ALTER TABLE `PurchaseOrders` ADD `LockedBy` int(10) default NULL;
ALTER TABLE `PurchaseOrders` ADD `LockedDate` datetime default NULL;


-- March 07, 2014 --
ALTER TABLE `ItemSeries` ADD `Status` enum("in", "loan", "demo", "sold", "reserved") default "in";

CREATE TABLE IF NOT EXISTS `ItemSeriesStatus` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ItemSeriesID` int(10) default NULL,
  `StatusDate` date default NULL,
  `Status` enum("in", "loan", "demo", "sold", "reserved") default NULL,
  `UserIDEntry` int(11) default NULL,
  `EntryDateTime` datetime default NULL,
  `UserIDResp` int(11) default NULL,
  `Notes` varchar(1024) default NULL,
 PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- March 17, 2014 --

ALTER TABLE `ItemSeries` ADD `MarkupPercent` float(5,2) default 0;
ALTER TABLE `ItemSeries` ADD `SalesOrderNumber` varchar(128) default NULL;



-- March 28, 2014 --

CREATE TABLE IF NOT EXISTS `Settings` (
  `SettingLanguage` varchar(2) default 'en',
  `SettingCurrency` varchar(3) default 'USD',
  `SettingMarkup` varchar(3) default '30'
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `Categories` (
  `ID` int(4) unsigned NOT NULL auto_increment,
  `Name` varchar(128) NOT NULL default 'Default',
  `ParentID` int(4) DEFAULT NULL,
 PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- April 1, 2014 --

ALTER TABLE `ItemSeries` ADD `UnitRetail` float(10,2) default 0;

-- April 2, 2014 --
ALTER TABLE `Item` ADD `CategoryID` int(5) default 1;

-- April 3, 2014 --
ALTER TABLE `ItemSeriesStatus` ADD `ReferenceNo` varchar(36) default NULL;


-- April 6, 2014 --

CREATE TABLE IF NOT EXISTS `Customers` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(255) default NULL,
  `Address` varchar(1024) NULL,
  `Phone` varchar(32) NULL,
  `Email` varchar(255) NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;




CREATE TABLE IF NOT EXISTS `SalesOrders` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `CustomerID` int(10) default NULL,
  `BranchID` int(10) default NULL,
  `OrderNumber` varchar(255) default NULL,
  `SalesDate` date default NULL,
  `SOFilePath` varchar(255) default NULL, /* upload of PO document */
  `SubtotalCurrency` float(10,2) default NULL,
  `Subtotal` float(10,2) default NULL,
  `Currency` varchar(8) default NULL,
  `Multiplier` float(6,2) default NULL,
  `SODeliveryCharge` float(10,2) default NULL,
  `SOTaxCharge` float(10,2) default NULL,
  `SOMiscCharge` float(10,2) default NULL,
  `SOMiscNote` varchar(255) default NULL,
  `Total` float(10,2) default NULL,
  `Locked` int(2) default 0,
  `LockedBy` int(10) default NULL,
  `LockedDate` datetime default NULL, 
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `SOItems` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `OrderID` int(10) default NULL,
  `ItemID` int(10) default NULL,
  `Quantity` int(5) default NULL,
  `Currency` varchar(8) default NULL,
  `UnitPrice` float(10,2) default NULL,
  `UnitTotal` float(10,2) default NULL,
  `UnitTotalCurrency` float(10,2) default NULL,
  `UnitDiscount` float(10,2) default NULL,
  `UnitDiscountType` varchar(2) default NULL,
  `SubTotal` float(10,2) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- April 9, 2014 --
ALTER TABLE `ItemSeries` ADD `SOItemsID` int(10) default NULL;


-- April 16, 2014 --
ALTER TABLE `POItems` ADD `UnitDiscount` float(10,2) default NULL;
ALTER TABLE `POItems` ADD `UnitDiscountType` varchar(2) default NULL;
ALTER TABLE `PurchaseOrders` ADD `PODiscount` float(10,2) default NULL;
ALTER TABLE `SalesOrders` ADD `SODiscount` float(10,2) default NULL;


-- April 18, 2014 --
ALTER TABLE `ItemSeriesStatus` ADD `TransitTo` int(10) default NULL;

ALTER TABLE `ItemSeriesStatus` CHANGE  `Status`  `Status` VARCHAR( 64 ) DEFAULT NULL;

ALTER TABLE `ItemSeries` CHANGE  `Status`  `Status` VARCHAR( 64 ) DEFAULT "in";




CREATE TABLE IF NOT EXISTS `SYSLog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) DEFAULT NULL,
  `role` varchar(32) DEFAULT NULL,
  `logtime` DATETIME DEFAULT NULL,
  `zendmodule` varchar(32) DEFAULT NULL,
  `zendcontroller` varchar(32) DEFAULT NULL,
  `zendaction` varchar(32) DEFAULT NULL,
  `postdata` BLOB DEFAULT NULL,
  `getdata` varchar(64) DEFAULT NULL,
  `IP` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- April 30, 2014 --

ALTER TABLE `PurchaseOrders` ADD `AOFilePath` varchar(255) default NULL;

ALTER TABLE `PurchaseOrders` ADD `ExpectedDate` date default NULL;
ALTER TABLE `PurchaseOrders` ADD `OADate` date default NULL;
ALTER TABLE `PurchaseOrders` ADD `ReceivedDate` date default NULL;
ALTER TABLE `PurchaseOrders` ADD `FreightForwarder` varchar(255) default NULL;
ALTER TABLE `PurchaseOrders` ADD `POStatus` varchar(255) default NULL;


------ June 01, 2014 ----------------
ALTER TABLE `Settings` ADD `SettingMarkupType` enum("GROSS_MARGIN","MARKUP") default "GROSS_MARGIN";


Update ItemSeries SET UnitRetail = (UnitLandedCost / ((100 - MarkupPercent) / 100));

------ July 14, 2014 ----------------
ALTER TABLE `POItems` ADD `Status` varchar(64) default NULL;


------ Oct 06, 2014 ----------------
ALTER TABLE `POItems` ADD `StatusDate` date default NULL;




SELECT Item.ID, Item.EntityID,  CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName, TotalNum_sold, TotalNum_sold, TotalNum_sold, TotalNum_sold FROM Item 
LEFT JOIN (SELECT COUNT(*) as TotalNum_in, ItemID FROM (SELECT ItemSeriesStatus.Status, ItemSeriesStatus.ItemSeriesID, ItemSeriesStatus.ItemID, ItemSeriesStatus.ItemName, EntryDateTime, ItemSeriesStatus.EntityID FROM (SELECT ItemSeriesStatus.*, Item.ID as ItemID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName FROM ItemSeriesStatus, ItemSeries, Item WHERE Item.EntityID=1 AND ItemSeriesStatus.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID order by StatusDate Desc, EntryDateTime Desc) as ItemSeriesStatus, ItemSeries WHERE ItemSeries.ID=ItemSeriesStatus.ItemSeriesID AND ItemSeries.EntityID=1 GROUP BY ItemSeriesStatus.ItemSeriesID) as StatusList WHERE Status = 'in' AND StatusList.EntityID=1 group by ItemID order by TotalNum_in) as Count_in ON (Count_in.ItemID=Item.ID) 
LEFT JOIN (SELECT COUNT(*) as TotalNum_indent, ItemID FROM (SELECT ItemSeriesStatus.Status, ItemSeriesStatus.ItemSeriesID, ItemSeriesStatus.ItemID, ItemSeriesStatus.ItemName, EntryDateTime, ItemSeriesStatus.EntityID FROM (SELECT ItemSeriesStatus.*, Item.ID as ItemID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName FROM ItemSeriesStatus, ItemSeries, Item WHERE Item.EntityID=1 AND ItemSeriesStatus.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID order by StatusDate Desc, EntryDateTime Desc) as ItemSeriesStatus, ItemSeries WHERE ItemSeries.ID=ItemSeriesStatus.ItemSeriesID AND ItemSeries.EntityID=1 GROUP BY ItemSeriesStatus.ItemSeriesID) as StatusList WHERE Status = 'indent' AND StatusList.EntityID=1 group by ItemID order by TotalNum_indent) as Count_indent ON (Count_indent.ItemID=Item.ID) 
LEFT JOIN (SELECT COUNT(*) as TotalNum_reserved, ItemID FROM (SELECT ItemSeriesStatus.Status, ItemSeriesStatus.ItemSeriesID, ItemSeriesStatus.ItemID, ItemSeriesStatus.ItemName, EntryDateTime, ItemSeriesStatus.EntityID FROM (SELECT ItemSeriesStatus.*, Item.ID as ItemID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName FROM ItemSeriesStatus, ItemSeries, Item WHERE Item.EntityID=1 AND ItemSeriesStatus.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID order by StatusDate Desc, EntryDateTime Desc) as ItemSeriesStatus, ItemSeries WHERE ItemSeries.ID=ItemSeriesStatus.ItemSeriesID AND ItemSeries.EntityID=1 GROUP BY ItemSeriesStatus.ItemSeriesID) as StatusList WHERE Status = 'reserved' AND StatusList.EntityID=1 group by ItemID order by TotalNum_reserved) as Count_reserved ON (Count_reserved.ItemID=Item.ID) 
LEFT JOIN (SELECT COUNT(*) as TotalNum_sold, ItemID FROM (SELECT ItemSeriesStatus.Status, ItemSeriesStatus.ItemSeriesID, ItemSeriesStatus.ItemID, ItemSeriesStatus.ItemName, EntryDateTime, ItemSeriesStatus.EntityID FROM (SELECT ItemSeriesStatus.*, Item.ID as ItemID, CONCAT(Item.ItemName, ' (',Item.ModelNumber, ')') as ItemName FROM ItemSeriesStatus, ItemSeries, Item WHERE Item.EntityID=1 AND ItemSeriesStatus.ItemSeriesID=ItemSeries.ID AND ItemSeries.ItemID=Item.ID order by StatusDate Desc, EntryDateTime Desc) as ItemSeriesStatus, ItemSeries WHERE ItemSeries.ID=ItemSeriesStatus.ItemSeriesID AND ItemSeries.EntityID=1 GROUP BY ItemSeriesStatus.ItemSeriesID) as StatusList WHERE Status = 'sold' AND StatusList.EntityID=1 group by ItemID order by TotalNum_sold) as Count_sold ON (Count_sold.ItemID=Item.ID) 

WHERE Item.EntityID=1 order by TotalNum_in desc


/* June 25, 2016  */
ALTER TABLE `Item` ADD `MinStock` int(6) default NULL;

/* Aug 5, 2016  */

UPDATE PromoterOutlet SET DateEntry='2016-08-05' WHERE DateEntry='0000-00-00';
UPDATE PromoterOutlet SET DateEnd='2020-08-31' WHERE DateEnd='0000-00-00';


/* Mar 11, 2018  */
ALTER TABLE `Branches` Add `Code` varchar(16) DEFAULT NULL;

/* ************************************************************** */
/* Jan 07, 2019 */
CREATE TABLE IF NOT EXISTS `RentalAsset` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ItemSeriesID` int(12) NOT NULL,
  `POItemsID` int(12) DEFAULT NULL,
  `DateAsAsset` datetime NOT NULL,
  `AssetInitialValue` float(10,2) DEFAULT NULL,
  `AssetCurrentValue` float(10,2) DEFAULT NULL,
  `RentalStatus` varchar(64) DEFAULT 'available',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `RentalAssetStatus` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `RentalAssetID` int(12) NOT NULL,
  `StatusDate` date DEFAULT NULL,
  `RentalStatus` varchar(64) DEFAULT 'available',
  `UserIDEntry` int(11) default NULL,
  `EntryDateTime` datetime default NULL,
  `UserIDResp` int(11) default NULL,
  `ClientID` int(11) default NULL,
  `ClientName` varchar(128) default NULL,
  `Notes` varchar(1024) default NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


ALTER TABLE `RentalAssetStatus` ADD `ReferenceNo` varchar(36) default NULL;
ALTER TABLE `RentalAssetStatus` ADD `EstimatedReturnDate` date default NULL;
ALTER TABLE `RentalAssetStatus` ADD `ActualReturnDate` date default NULL;


ALTER TABLE `Item` ADD `MonthDepreciation` int(2) default NULL;


/* Jan 25, 2019 */

CREATE TABLE IF NOT EXISTS `RentalStatusDocuments` (
  `ID` int(11) NOT NULL auto_increment,
  `RentalAssetStatusID` int(12) DEFAULT NULL,
  `DocType` varchar(32) DEFAULT NULL,
  `Name` varchar(128) NOT NULL,
  `Description` varchar(5000) DEFAULT NULL,
  `DateSubmitted` DateTime DEFAULT NULL,
  `SubmittedBy` int(12) DEFAULT NULL,
  `FilePath` varchar(255) DEFAULT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

ALTER TABLE `RentalAsset` ADD MonthDepreciation int(3) DEFAULT NULL;
ALTER TABLE `RentalAsset` ADD MonthRemaining int(3) DEFAULT NULL;

ALTER TABLE `ItemSeriesStatus` ADD MonthDepreciation int(3) DEFAULT NULL;
ALTER TABLE `ItemSeriesStatus` ADD MonthRemaining int(3) DEFAULT NULL;

/* Jan 31, 2019 */

ALTER TABLE `ACLUsers` ADD ManageRental int(1) DEFAULT 0;

/* June 25, 2020 */

CREATE TABLE IF NOT EXISTS `Documents` (
  `ID` int(11) NOT NULL auto_increment,
  `POID` int(12) DEFAULT NULL,
  `DocType` varchar(32) DEFAULT NULL,
  `Name` varchar(128) NOT NULL,
  `Description` varchar(5000) NOT NULL,
  `DateSubmitted` DateTime DEFAULT NULL,
  `SubmittedBy` int(12) DEFAULT NULL,
  `FilePath` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


-- 2021-03-03
ALTER TABLE `Vendors` ADD `PaymentTerms` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `SalesContact` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `OrderProcessingName` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `OrderProcessingEmail` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `TechnicalSupportName` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `TechnicalSupportEmail` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `FinanceName` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `FinanceEmail` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `AreaManagerName` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `AreaManagerEmail` varchar(255) DEFAULT NULL;
ALTER TABLE `Vendors` ADD `ExactSalesPersonID` int(12) DEFAULT NULL;
