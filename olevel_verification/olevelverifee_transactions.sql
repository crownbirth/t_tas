-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 14, 2014 at 04:31 PM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tams4`
--

-- --------------------------------------------------------

--
-- Table structure for table `olevelverifee_transactions`
--

CREATE TABLE IF NOT EXISTS `olevelverifee_transactions` (
  `matric_no` varchar(20) DEFAULT NULL,
  `can_no` int(7) DEFAULT NULL,
  `can_name` varchar(100) NOT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `amt` text,
  `resp_code` varchar(4) DEFAULT NULL,
  `resp_desc` text NOT NULL,
  `ordid` varchar(20) NOT NULL,
  `auth_code` varchar(20) NOT NULL,
  `year` int(4) NOT NULL,
  `pan` varchar(20) NOT NULL,
  `status` text NOT NULL,
  `name` text NOT NULL,
  `date_time` varchar(40) NOT NULL,
  `sessionid` varchar(500) DEFAULT NULL,
  `gatewayurl` varchar(500) DEFAULT NULL,
  `xml` text NOT NULL,
  `admission_type` varchar(20) NOT NULL,
  `percentPaid` int(3) NOT NULL,
  `balance` int(7) DEFAULT NULL,
  `pay_used` enum('Yes','No') DEFAULT 'No',
  `card_submit` enum('Yes','No') NOT NULL DEFAULT 'No',
  UNIQUE KEY `reference` (`reference`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
