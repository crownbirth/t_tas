-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 14, 2014 at 04:30 PM
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
-- Table structure for table `olevel_veri_data`
--

CREATE TABLE IF NOT EXISTS `olevel_veri_data` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `stdid` varchar(11) NOT NULL,
  `exam_type` varchar(25) NOT NULL,
  `exam_year` int(4) NOT NULL,
  `exam_no` varchar(10) NOT NULL,
  `card_no` varchar(15) NOT NULL,
  `card_pin` varchar(15) NOT NULL,
  `approve` enum('Yes','No') NOT NULL DEFAULT 'No',
  `treated` enum('Yes','No') DEFAULT 'No',
  `date` varchar(10) DEFAULT NULL,
  `return_msg` text,
  `ordid` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
