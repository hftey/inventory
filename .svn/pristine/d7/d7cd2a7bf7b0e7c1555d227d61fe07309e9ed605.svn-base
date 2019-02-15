-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 09, 2014 at 10:21 AM
-- Server version: 5.5.24-log
-- PHP Version: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `aclmap`
--

CREATE TABLE IF NOT EXISTS `ACLMap` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Role` varchar(255) DEFAULT NULL,
  `Resources` varchar(255) DEFAULT NULL,
  `Priviledges` varchar(255) DEFAULT NULL,
  `Allow` int(1) DEFAULT '1',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Role` (`Role`,`Resources`,`Priviledges`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=327 ;

--
-- Dumping data for table `aclmap`
--

INSERT INTO `ACLMap` (`ID`, `Role`, `Resources`, `Priviledges`, `Allow`) VALUES
(284, 'User', 'po_edit', 'view', 0),
(309, 'Admin', 'po_listing', 'view', 1),
(281, 'User', 'menu_manage', 'view', 0),
(282, 'User', 'menu_po_create', 'view', 0),
(283, 'User', 'menu_user_management', 'view', 0),
(325, 'AdminSystem', 'po_listing', 'view', 1),
(323, 'AdminSystem', 'po_create', 'view', 1),
(324, 'AdminSystem', 'po_edit', 'view', 1),
(311, 'Admin', 'admin_users', 'view', 0),
(310, 'Admin', 'public', 'view', 1),
(280, 'User', 'menu_admin', 'view', 0),
(279, 'User', 'inventory_itemseries', 'view', 0),
(308, 'Admin', 'po_edit', 'view', 1),
(307, 'Admin', 'po_create', 'view', 1),
(278, 'User', 'inventory_item', 'view', 0),
(322, 'AdminSystem', 'menu_user_management', 'view', 1),
(321, 'AdminSystem', 'menu_po_create', 'view', 1),
(306, 'Admin', 'menu_po_create', 'view', 1),
(305, 'Admin', 'menu_manage', 'view', 1),
(277, 'User', 'inventory_brands', 'view', 0),
(276, 'User', 'admin_vendors', 'view', 0),
(319, 'AdminSystem', 'menu_admin', 'view', 1),
(320, 'AdminSystem', 'menu_manage', 'view', 1),
(318, 'AdminSystem', 'inventory_itemseries', 'view', 1),
(304, 'Admin', 'menu_admin', 'view', 1),
(303, 'Admin', 'inventory_itemseries', 'view', 1),
(302, 'Admin', 'inventory_item', 'view', 1),
(301, 'Admin', 'inventory_brands', 'view', 1),
(326, 'AdminSystem', 'public', 'view', 1),
(316, 'AdminSystem', 'inventory_brands', 'view', 1),
(275, 'User', 'admin_users', 'view', 0),
(274, 'User', 'admin_branches', 'view', 0),
(273, 'User', 'public', 'view', 1),
(317, 'AdminSystem', 'inventory_item', 'view', 1),
(315, 'AdminSystem', 'admin_vendors', 'view', 1),
(300, 'Admin', 'admin_vendors', 'view', 1),
(299, 'Admin', 'admin_branches', 'view', 1),
(272, 'User', 'po_listing', 'view', 1),
(271, 'User', 'po_create', 'view', 1),
(314, 'AdminSystem', 'admin_users', 'view', 1),
(313, 'AdminSystem', 'admin_branches', 'view', 1),
(312, 'Admin', 'menu_user_management', 'view', 0);

-- --------------------------------------------------------

--
-- Table structure for table `aclpriviledges`
--

CREATE TABLE IF NOT EXISTS `ACLPriviledges` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `aclpriviledges`
--

INSERT INTO `ACLPriviledges` (`ID`, `Name`, `Description`) VALUES
(1, 'view', 'view'),
(2, 'edit', 'edit'),
(3, 'add', 'add'),
(4, 'delete', 'delete');

-- --------------------------------------------------------

--
-- Table structure for table `aclresources`
--

CREATE TABLE IF NOT EXISTS `ACLResources` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `ParentName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

--
-- Dumping data for table `aclresources`
--

INSERT INTO `ACLResources` (`ID`, `Name`, `Description`, `Category`, `ParentName`) VALUES
(1, 'public', 'Public Sections', 'Public', 'NULL'),
(13, 'admin_branches', 'Admin - Branches', 'Admin', ''),
(4, 'inventory_brands', 'Management - Brands', 'Management', ''),
(5, 'inventory_item', 'Management - Items', 'Management', ''),
(6, 'inventory_itemseries', 'Management - Item Series', 'Management', ''),
(7, 'menu_manage', 'Menu - Inventory Manage', 'Menu', ''),
(8, 'po_create', 'PO - Create', 'PO', ''),
(9, 'po_listing', 'PO - Listing', 'PO', ''),
(10, 'menu_po_create', 'Menu - Create PO', 'Menu', ''),
(11, 'menu_admin', 'Menu - Admin Section', 'Menu', ''),
(12, 'menu_user_management', 'Menu - User Creation', 'Menu', ''),
(14, 'admin_vendors', 'Admin - Vendors', 'Admin', ''),
(15, 'admin_users', 'Admin - User Management', 'Admin', ''),
(16, 'po_edit', 'PO - Edit', 'PO', '');

-- --------------------------------------------------------

--
-- Table structure for table `aclrole`
--

CREATE TABLE IF NOT EXISTS `ACLRole` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) NOT NULL,
  `Description` varchar(100) NOT NULL,
  `ParentName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `aclrole`
--

INSERT INTO `ACLRole` (`ID`, `Name`, `Description`, `ParentName`) VALUES
(1, 'User', 'Normal User', 'NULL'),
(4, 'AdminSystem', 'System Administrator', 'User'),
(3, 'Admin', 'Administrator', 'User');

-- --------------------------------------------------------

--
-- Table structure for table `aclusers`
--

CREATE TABLE IF NOT EXISTS `ACLUsers` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ACLRole` varchar(255) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Username` varchar(64) NOT NULL,
  `Password` varchar(100) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `Active` tinyint(4) NOT NULL DEFAULT '1',
  `LastLogin` datetime NOT NULL,
  `UserCreated` varchar(64) NOT NULL,
  `DateCreated` datetime DEFAULT NULL,
  `UserModified` varchar(64) NOT NULL,
  `DateModified` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `aclusers`
--

INSERT INTO `ACLUsers` (`ID`, `ACLRole`, `Name`, `Username`, `Password`, `Email`, `Active`, `LastLogin`, `UserCreated`, `DateCreated`, `UserModified`, `DateModified`) VALUES
(1, 'Admin', 'admin', 'admin', '29083f8732d13605187dc5be5d8f53d2', 'admin@email.com', 1, '2014-03-09 00:46:11', '', NULL, '', NULL),
(2, 'User', 'Normal User', 'user', '67164a34f6a9ec4d82b87184d33e8ca6', 'normaluser@email.com', 1, '2014-03-09 00:09:32', 'guest', '2014-02-12 14:35:13', '', NULL),
(3, 'AdminSystem', 'System Admin', 'system_admin', '29083f8732d13605187dc5be5d8f53d2', 'system_admin@email.com', 1, '2014-03-08 23:58:11', 'admin', '2014-03-08 23:45:19', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE IF NOT EXISTS `Branches` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `Address` varchar(1024) DEFAULT NULL,
  `Phone` varchar(32) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `branches`
--

INSERT INTO `Branches` (`ID`, `Name`, `Location`, `Address`, `Phone`, `Email`) VALUES
(2, 'HQ', 'Puchong', '29-B, Jalan Kenari 17E\r\nBandar Puchong Jaya\r\n47100 Puchong\r\nSelangor Darul Ehsan\r\nMalaysia', '+60(3)-8076 5531', 'info@exactanalytical.com.my'),
(3, 'Bintulu Office ', 'Bintulu Sarawak', 'No. 31 Kemena Commercial Centre\r\n1st Floor\r\nJalan Tanjung Batu\r\n97000 Bintulu\r\nSarawak Malaysia', '+60(86)-314 668', NULL),
(4, 'Kerteh Office', 'Kerteh Terengganu', 'Lot 16694, Tingkat Atas\r\nJalan Besar Paka\r\n23100 Paka, Dungun\r\nTerengganu\r\nMalaysia', '+60(9)-827 6897', NULL),
(24, 'Shah Alam', NULL, NULL, NULL, NULL),
(25, 'Seremban', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brand`
--

CREATE TABLE IF NOT EXISTS `Brand` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FullName` varchar(255) DEFAULT NULL,
  `ShortName` varchar(35) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `BrandLogoPath` varchar(255) DEFAULT NULL,
  `BrandLogoPathSmall` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `brand`
--

INSERT INTO `Brand` (`ID`, `FullName`, `ShortName`, `CompanyName`, `BrandLogoPath`, `BrandLogoPathSmall`) VALUES
(9, 'Applied Analytics', 'AAI', 'Applied Analytics', '/uploads/BrandLogo/9.jpg', '/uploads/BrandLogo/small/9.jpg'),
(5, 'Bacharach', 'BCR', 'Bacharach', '/uploads/BrandLogo/5.jpg', '/uploads/BrandLogo/small/5.jpg'),
(8, 'Procal', 'PC', 'Procal', '/uploads/BrandLogo/8.jpg', '/uploads/BrandLogo/small/8.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE IF NOT EXISTS `Item` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `BrandID` int(10) DEFAULT NULL,
  `ItemName` varchar(255) DEFAULT NULL,
  `ModelNumber` varchar(255) DEFAULT NULL,
  `PartNumber` varchar(255) DEFAULT NULL,
  `ItemImagePath` varchar(255) DEFAULT NULL,
  `ItemImagePathSmall` varchar(255) DEFAULT NULL,
  `RetailPrice` float(10,2) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `item`
--

INSERT INTO `Item` (`ID`, `BrandID`, `ItemName`, `ModelNumber`, `PartNumber`, `ItemImagePath`, `ItemImagePathSmall`, `RetailPrice`) VALUES
(3, 5, 'Combustion Analyzers', 'PCA 3', NULL, '/uploads/ItemImage/3.jpg', '/uploads/ItemImage/small/3.jpg', 800.00),
(4, 5, 'Combustion Analyzers', 'ECA 450', NULL, '/uploads/ItemImage/4.jpg', '/uploads/ItemImage/small/4.jpg', NULL),
(5, 5, 'Combustion Analyzers', 'Fyrite Insight', NULL, '/uploads/ItemImage/5.jpg', '/uploads/ItemImage/small/5.jpg', NULL),
(6, 9, 'OMA 300 UV-VIS Process Analyzer', 'OMA 300', NULL, '/uploads/ItemImage/6.jpg', '/uploads/ItemImage/small/6.jpg', 990.00),
(7, 9, 'TLG 837 Tail Gas Analyzer', 'TLG 837', NULL, '/uploads/ItemImage/7.jpg', '/uploads/ItemImage/small/7.jpg', 500.50),
(18, 9, 'AAAAAAA', 'AAAAAAAAA', 'AAAAAA', NULL, NULL, 11.00),
(13, 5, 'SOme Item', 'M123123', NULL, NULL, NULL, 11.45),
(14, 8, 'ZZZZZZ', 'Z23123', NULL, NULL, NULL, 89.90),
(15, 5, 'BBBBBB', 'B123123', NULL, NULL, NULL, 123.00),
(16, 8, 'CCCCCC', 'C312312', NULL, NULL, NULL, 34.50);

-- --------------------------------------------------------

--
-- Table structure for table `itemseries`
--

CREATE TABLE IF NOT EXISTS `ItemSeries` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `POItemsID` int(10) DEFAULT NULL,
  `ItemID` int(10) DEFAULT NULL,
  `UnitPrice` float(10,2) DEFAULT NULL,
  `UnitPriceRM` float(10,2) DEFAULT NULL,
  `UnitDeliveryCost` float(10,2) DEFAULT NULL,
  `UnitTaxCost` float(10,2) DEFAULT NULL,
  `UnitLandedCost` float(10,2) DEFAULT NULL,
  `SeriesNumber` varchar(255) DEFAULT NULL,
  `BranchID` int(10) DEFAULT NULL,
  `Status` enum('in','loan','demo','sold') DEFAULT 'in',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=90 ;

--
-- Dumping data for table `itemseries`
--

INSERT INTO `ItemSeries` (`ID`, `POItemsID`, `ItemID`, `UnitPrice`, `UnitPriceRM`, `UnitDeliveryCost`, `UnitTaxCost`, `UnitLandedCost`, `SeriesNumber`, `BranchID`, `Status`) VALUES
(71, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(69, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(70, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(67, 15, 7, 9.00, 115.20, 9.04, 0.00, 124.24, 'BC 44567', 4, 'in'),
(68, 16, 3, 329.10, 842.50, 66.11, 0.00, 908.61, 'R12312 12', 4, 'in'),
(47, 10, 4, 31.25, 401.25, 18.75, 2.25, 422.25, '1', NULL, 'in'),
(48, 10, 4, 31.25, 401.25, 18.75, 2.25, 422.25, '2', NULL, 'in'),
(49, 10, 4, 31.25, 401.25, 18.75, 2.25, 422.25, '3', NULL, 'in'),
(50, 10, 4, 31.25, 401.25, 18.75, 2.25, 422.25, '4', NULL, 'loan'),
(58, 12, 5, 50.00, 321.00, 15.00, 16.50, 352.50, NULL, NULL, 'in'),
(59, 12, 5, 50.00, 321.00, 15.00, 16.50, 352.50, NULL, NULL, 'in'),
(60, 13, 6, 100.00, 321.00, 15.00, 16.00, 352.00, NULL, NULL, 'in'),
(61, 14, 6, 172.62, 883.79, 69.35, 0.00, 953.14, 'A 123123', 3, 'in'),
(62, 14, 6, 172.62, 883.79, 69.35, 0.00, 953.14, 'VBC 1231452', 3, 'in'),
(63, 15, 7, 9.00, 115.20, 9.04, 0.00, 124.24, 'BC 44568', 3, 'in'),
(64, 15, 7, 9.00, 115.20, 9.04, 0.00, 124.24, 'BC 44569', 3, 'in'),
(65, 15, 7, 9.00, 115.20, 9.04, 0.00, 124.24, 'BC 44570', 3, 'in'),
(66, 15, 7, 9.00, 115.20, 9.04, 0.00, 124.24, 'BC 44571', 3, 'in'),
(72, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(73, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(74, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(75, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(76, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(77, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(78, 17, 3, 9.93, 99.30, 12.40, 5.50, 117.20, NULL, NULL, 'in'),
(79, 20, 15, 100.00, 1600.00, 50.00, 2.40, 1652.40, '234234234', 2, 'in'),
(80, 20, 15, 100.00, 1600.00, 50.00, 2.40, 1652.40, '123123123', 2, 'in'),
(81, 20, 15, 100.00, 1600.00, 50.00, 2.40, 1652.40, '435353453', 2, 'sold'),
(82, 20, 15, 100.00, 1600.00, 50.00, 2.40, 1652.40, '224234234', 2, 'demo'),
(83, 20, 15, 100.00, 1600.00, 50.00, 2.40, 1652.40, '6456464534', 2, 'in'),
(84, 18, 6, 250.00, 1600.00, 50.00, 5.50, 1655.50, '123123123', 2, 'demo'),
(85, 18, 6, 250.00, 1600.00, 50.00, 5.50, 1655.50, '123123123', 2, 'loan'),
(86, 19, 5, 62.50, 800.00, 25.00, 2.50, 827.50, NULL, 2, 'in'),
(87, 19, 5, 62.50, 800.00, 25.00, 2.50, 827.50, NULL, 2, 'in'),
(88, 19, 5, 62.50, 800.00, 25.00, 2.50, 827.50, NULL, 2, 'in'),
(89, 19, 5, 62.50, 800.00, 25.00, 2.50, 827.50, NULL, 2, 'sold');

-- --------------------------------------------------------

--
-- Table structure for table `itemseriesstatus`
--

CREATE TABLE IF NOT EXISTS `ItemSeriesStatus` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ItemSeriesID` int(10) DEFAULT NULL,
  `StatusDate` date DEFAULT NULL,
  `Status` enum('in','loan','demo','sold') DEFAULT NULL,
  `UserIDEntry` int(11) DEFAULT NULL,
  `EntryDateTime` datetime DEFAULT NULL,
  `UserIDResp` int(11) DEFAULT NULL,
  `Notes` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `itemseriesstatus`
--

INSERT INTO `ItemSeriesStatus` (`ID`, `ItemSeriesID`, `StatusDate`, `Status`, `UserIDEntry`, `EntryDateTime`, `UserIDResp`, `Notes`) VALUES
(6, 84, '2014-03-07', 'demo', 1, '2014-03-07 15:52:46', 2, 'THIs demo is for XXX company'),
(2, 89, NULL, NULL, 1, '2014-03-07 15:01:57', NULL, NULL),
(7, 85, '2014-03-19', 'loan', 1, '2014-03-07 16:30:59', 2, '12312'),
(8, 82, '2014-03-07', 'demo', 1, '2014-03-07 16:55:05', 2, 'demo for xxc vendor'),
(9, 81, '2014-03-07', 'sold', 1, '2014-03-07 16:55:36', 2, '123123123'),
(10, 50, '2014-03-07', 'loan', 1, '2014-03-07 17:39:12', 2, 'awdqwd');

-- --------------------------------------------------------

--
-- Table structure for table `poitems`
--

CREATE TABLE IF NOT EXISTS `POItems` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `OrderID` int(10) DEFAULT NULL,
  `ItemID` int(10) DEFAULT NULL,
  `Quantity` int(5) DEFAULT NULL,
  `UnitPrice` float(10,2) DEFAULT NULL,
  `UnitPriceRM` float(10,2) DEFAULT NULL,
  `DeliveryCost` float(10,2) DEFAULT NULL,
  `TaxCost` float(10,2) DEFAULT NULL,
  `LandedCost` float(10,2) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `poitems`
--

INSERT INTO `POItems` (`ID`, `OrderID`, `ItemID`, `Quantity`, `UnitPrice`, `UnitPriceRM`, `DeliveryCost`, `TaxCost`, `LandedCost`) VALUES
(12, 1, 5, 2, 100.00, 642.00, 30.00, 33.00, 705.00),
(13, 1, 6, 1, 100.00, 321.00, 15.00, 16.00, 352.00),
(10, 1, 4, 4, 125.00, 1605.00, 75.00, 9.00, 1689.00),
(14, 2, 6, 2, 345.23, 1767.58, 138.70, 0.00, 1906.28),
(15, 2, 7, 5, 45.00, 576.00, 45.20, 0.00, 621.20),
(16, 2, 3, 1, 329.10, 842.50, 66.11, 0.00, 908.61),
(17, 3, 3, 10, 99.30, 993.00, 124.00, 55.00, 1172.00),
(18, 4, 6, 2, 500.00, 3200.00, 100.00, 11.00, 3311.00),
(19, 4, 5, 4, 250.00, 3200.00, 100.00, 10.00, 3310.00),
(20, 4, 15, 5, 500.00, 8000.00, 250.00, 12.00, 8262.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchaseorders`
--

CREATE TABLE IF NOT EXISTS `PurchaseOrders` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `VendorID` int(10) DEFAULT NULL,
  `OrderNumber` varchar(255) DEFAULT NULL,
  `PurchaseDate` date DEFAULT NULL,
  `POFilePath` varchar(255) DEFAULT NULL,
  `ProductCost` float(10,2) DEFAULT NULL,
  `Currency` varchar(8) DEFAULT NULL,
  `ProductCostRM` float(10,2) DEFAULT NULL,
  `Multiplier` float(6,2) DEFAULT NULL,
  `PODeliveryCost` float(10,2) DEFAULT NULL,
  `POTaxCost` float(10,2) DEFAULT NULL,
  `POMiscCost` float(10,2) DEFAULT NULL,
  `POMiscNote` varchar(255) DEFAULT NULL,
  `TotalCost` float(10,2) DEFAULT NULL,
  `BranchID` int(10) DEFAULT NULL,
  `Locked` int(2) DEFAULT '0',
  `LockedBy` int(10) DEFAULT NULL,
  `LockedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `purchaseorders`
--

INSERT INTO `PurchaseOrders` (`ID`, `VendorID`, `OrderNumber`, `PurchaseDate`, `POFilePath`, `ProductCost`, `Currency`, `ProductCostRM`, `Multiplier`, `PODeliveryCost`, `POTaxCost`, `POMiscCost`, `POMiscNote`, `TotalCost`, `BranchID`, `Locked`, `LockedBy`, `LockedDate`) VALUES
(1, 1, 'BCC 998776', '2014-02-19', NULL, 800.00, 'USD', 2568.00, 3.21, 120.00, 58.00, NULL, NULL, 2746.00, 2, 0, NULL, NULL),
(2, 1, 'UPP 00-1231-213', '2014-02-23', NULL, 1244.56, 'USD', 3186.07, 2.56, 250.00, 0.00, NULL, NULL, 3436.07, 3, 1, 1, '2014-02-23 18:34:10'),
(3, 1, 'HH 2342-234', '2014-02-21', NULL, 993.00, 'USD', 993.00, 1.00, 124.00, 55.00, NULL, NULL, 1172.00, 2, 1, 1, '2014-03-05 21:30:53'),
(4, 4, '809823012312', '2014-03-06', '/uploads/POFile/4.jpg', 4500.00, 'USD', 14400.00, 3.20, 450.00, 33.00, NULL, NULL, 14883.00, 2, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE IF NOT EXISTS `Vendors` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `Address` varchar(1024) DEFAULT NULL,
  `Phone` varchar(32) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `vendors`
--

INSERT INTO `Vendors` (`ID`, `Name`, `Address`, `Phone`, `Email`) VALUES
(1, 'Venzon Solution Services', 'Shah Alam', '01231231', 'raymond.tey@gmail.com'),
(2, 'some other company', 'PUchong', '92312931', '01923@231.co'),
(3, 'Exact Analytics Sdn Bhd', NULL, NULL, NULL),
(4, 'Nestle Sdn Bhd', 'This is some address in PJ', '112312312', 'raymond.tey@gmail.com');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;



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



