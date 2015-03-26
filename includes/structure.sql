-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 26, 2015 at 02:11 PM
-- Server version: 5.5.40
-- PHP Version: 5.3.10-1ubuntu3.15

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
-- Table structure for table `barcodes`
--

DROP TABLE IF EXISTS `barcodes`;
CREATE TABLE IF NOT EXISTS `barcodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serials` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=362 ;

-- --------------------------------------------------------

--
-- Table structure for table `Inout`
--

DROP TABLE IF EXISTS `Inout`;
CREATE TABLE IF NOT EXISTS `Inout` (
  `ID` int(255) NOT NULL,
  `StudentID` varchar(255) NOT NULL,
  `Use` varchar(255) NOT NULL,
  `DateIn` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateOut` datetime NOT NULL,
  `UserOut` varchar(255) NOT NULL,
  `UserIn` varchar(255) NOT NULL DEFAULT 'N/A',
  `Issues` text NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Inventory`
--

DROP TABLE IF EXISTS `Inventory`;
CREATE TABLE IF NOT EXISTS `Inventory` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `SerialNumber` varchar(255) NOT NULL,
  `DeviceSerial` varchar(255) NOT NULL,
  `Type` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `Issues` text NOT NULL,
  `PhotoName` varchar(255) NOT NULL,
  `State` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=174 ;

-- --------------------------------------------------------

--
-- Table structure for table `Sessions`
--

DROP TABLE IF EXISTS `Sessions`;
CREATE TABLE IF NOT EXISTS `Sessions` (
  `ID` int(255) NOT NULL AUTO_INCREMENT,
  `SessionID` varchar(255) NOT NULL,
  `UserName` varchar(255) NOT NULL,
  `IP` varchar(255) NOT NULL,
  `Token` varchar(255) NOT NULL,
  `Date` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=512 ;

-- --------------------------------------------------------

--
-- Table structure for table `Wiped`
--

DROP TABLE IF EXISTS `Wiped`;
CREATE TABLE IF NOT EXISTS `Wiped` (
  `ID` int(255) NOT NULL,
  `DeviceID` varchar(255) NOT NULL,
  `UserName` varchar(255) NOT NULL,
  `Date` datetime NOT NULL,
  KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

