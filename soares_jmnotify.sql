-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 23, 2011 at 05:34 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `soares_jmnotify`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE IF NOT EXISTS `apps` (
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `job_status` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `app_status` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `allow_update` int(1) NOT NULL DEFAULT '1' COMMENT 'Continue updating if set to 1.',
  PRIMARY KEY (`user_id`,`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(2) NOT NULL,
  `day` int(2) NOT NULL,
  `hour` int(2) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12772 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `password` blob NOT NULL,
  `email` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `optout` int(1) NOT NULL DEFAULT '0' COMMENT 'Opt out of news e-mails.',
  `app_check` varchar(255) COLLATE latin1_general_ci NOT NULL COMMENT 'MD5 hash of application page.',
  `phone` varchar(10) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Phone number.',
  `interview_only` int(1) NOT NULL DEFAULT '0' COMMENT 'Only wants interview notifs.',
  `sms_count` int(5) NOT NULL DEFAULT '0' COMMENT 'SMS counter.',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT 'Are we actively checking?',
  `override` int(1) NOT NULL DEFAULT '0' COMMENT 'Are we overriding the ''Employed'' keyword?',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=127 ;
