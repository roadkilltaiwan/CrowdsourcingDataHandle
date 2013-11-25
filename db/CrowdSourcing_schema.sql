-- MySQL dump 10.13  Distrib 5.1.69, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: CrowdSourcing
-- ------------------------------------------------------
-- Server version	5.1.69

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Comments`
--

DROP TABLE IF EXISTS `Comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Comments` (
  `from` varchar(255) CHARACTER SET ascii NOT NULL,
  `to` varchar(255) CHARACTER SET ascii NOT NULL,
  `message` text CHARACTER SET utf8 NOT NULL,
  `created_time` datetime NOT NULL,
  `comment_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `num_likes` int(11) NOT NULL,
  `match` text CHARACTER SET utf8 NOT NULL,
  `guess` text CHARACTER SET utf8 NOT NULL,
  `confirmed_cn` text CHARACTER SET utf8 NOT NULL COMMENT '測試用',
  `confirmed_sn` text CHARACTER SET utf8 NOT NULL COMMENT '測試用',
  `extracted_date` varchar(20) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `to` (`to`),
  KEY `from` (`from`),
  KEY `created_time` (`created_time`),
  FULLTEXT KEY `message` (`message`),
  FULLTEXT KEY `species` (`match`),
  FULLTEXT KEY `guess` (`guess`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Decide`
--

DROP TABLE IF EXISTS `Decide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Decide` (
  `object_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `common_name` varchar(50) NOT NULL,
  `score` float unsigned NOT NULL DEFAULT '0',
  `canonical_name` varchar(100) NOT NULL,
  `shot_date` date NOT NULL,
  `tagged` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否經人標記? 0:否 1:是',
  `inWhiteList` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否由白名單所標記? 0:否, 1:是',
  PRIMARY KEY (`object_id`,`canonical_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `GoogleLoc`
--

DROP TABLE IF EXISTS `GoogleLoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GoogleLoc` (
  `photo_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `x` decimal(10,6) DEFAULT NULL,
  `y` decimal(10,6) DEFAULT NULL,
  `p1` varchar(255) CHARACTER SET utf8 NOT NULL,
  `p2` varchar(255) CHARACTER SET utf8 NOT NULL,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`photo_id`),
  KEY `p1` (`p1`),
  KEY `p2` (`p2`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Likes`
--

DROP TABLE IF EXISTS `Likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Likes` (
  `from` varchar(255) CHARACTER SET ascii NOT NULL,
  `to` varchar(255) CHARACTER SET ascii NOT NULL,
  `to_type` varchar(255) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`from`,`to`),
  KEY `to_type` (`to_type`),
  KEY `from` (`from`),
  KEY `to` (`to`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Location`
--

DROP TABLE IF EXISTS `Location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Location` (
  `photo_id` varchar(50) NOT NULL DEFAULT '',
  `x` decimal(10,7) DEFAULT NULL,
  `y` decimal(10,8) DEFAULT NULL,
  `road` varchar(9) DEFAULT NULL,
  `mile` varchar(5) DEFAULT NULL,
  `places` varchar(15) DEFAULT NULL,
  `placename` varchar(200) NOT NULL,
  `town` varchar(40) NOT NULL,
  `county` varchar(40) NOT NULL,
  `type` varchar(50) NOT NULL,
  `placename_id` varchar(50) NOT NULL,
  PRIMARY KEY (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Person`
--

DROP TABLE IF EXISTS `Person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Person` (
  `realname` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '真實姓名',
  `person_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `username` varchar(255) CHARACTER SET ascii NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT 'FB暱稱',
  `authState` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '該使用者對上傳至FB照片的授權意願',
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `byWhom` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '引用時的姓名標示方式',
  `inWhiteList` tinyint(1) NOT NULL DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `username` (`username`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Photo`
--

DROP TABLE IF EXISTS `Photo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Photo` (
  `photo_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `name` text NOT NULL,
  `picture` text NOT NULL,
  `created_time` datetime NOT NULL,
  `updated_time` datetime NOT NULL,
  `x` int(5) NOT NULL,
  `y` int(5) NOT NULL,
  `link` text CHARACTER SET ascii NOT NULL,
  `embedded_in` varchar(255) CHARACTER SET ascii NOT NULL,
  `downloaded` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_by` varchar(255) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`photo_id`),
  KEY `created_time` (`created_time`),
  KEY `updated_time` (`updated_time`),
  KEY `downloaded` (`downloaded`,`uploaded_by`),
  KEY `embedded_in` (`embedded_in`),
  KEY `uploaded_by` (`uploaded_by`),
  FULLTEXT KEY `message` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Post`
--

DROP TABLE IF EXISTS `Post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Post` (
  `post_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `message` text NOT NULL,
  `picture` text NOT NULL,
  `created_time` datetime NOT NULL,
  `updated_time` datetime NOT NULL,
  `post_to` varchar(255) CHARACTER SET ascii NOT NULL,
  `post_from` varchar(255) CHARACTER SET ascii NOT NULL,
  `num_likes` int(11) NOT NULL,
  `match` text NOT NULL,
  `guess` text NOT NULL,
  `image_fit` text NOT NULL,
  `confirmed_sn` text NOT NULL COMMENT '測試用',
  `confirmed_cn` text NOT NULL COMMENT '測試用',
  `object_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `extracted_date` varchar(20) CHARACTER SET ascii NOT NULL,
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `post_id` (`post_id`),
  KEY `created_time` (`created_time`),
  KEY `updated_time` (`updated_time`),
  KEY `post_to` (`post_to`),
  KEY `post_from` (`post_from`),
  KEY `object_id` (`object_id`),
  KEY `post_from_2` (`post_from`),
  KEY `post_to_2` (`post_to`),
  FULLTEXT KEY `message` (`message`),
  FULLTEXT KEY `species` (`match`),
  FULLTEXT KEY `guess` (`guess`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ProcessLog`
--

DROP TABLE IF EXISTS `ProcessLog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ProcessLog` (
  `auto_id` int(11) NOT NULL AUTO_INCREMENT,
  `system_id` int(11) NOT NULL,
  `system_user` varchar(255) CHARACTER SET ascii NOT NULL,
  `ui_user` varchar(255) CHARACTER SET utf8 NOT NULL,
  `hostname` text CHARACTER SET ascii NOT NULL,
  `program` varchar(255) CHARACTER SET ascii NOT NULL,
  `action` text CHARACTER SET utf8 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `final_state` tinyint(1) NOT NULL DEFAULT '0',
  `system_details` blob NOT NULL,
  PRIMARY KEY (`auto_id`),
  KEY `system_id` (`system_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abbrv`
--

DROP TABLE IF EXISTS `abbrv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abbrv` (
  `full` varchar(150) NOT NULL,
  `abbrv` varchar(30) NOT NULL,
  PRIMARY KEY (`full`,`abbrv`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bigTable`
--

DROP TABLE IF EXISTS `bigTable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bigTable` (
  `photo_id` varchar(50) CHARACTER SET ascii NOT NULL,
  `link` text CHARACTER SET utf8 NOT NULL,
  `picture` text CHARACTER SET utf8 NOT NULL,
  `post_id` varchar(50) CHARACTER SET ascii NOT NULL,
  `person_id` varchar(255) CHARACTER SET ascii NOT NULL COMMENT '與post_from互補',
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created_time` datetime NOT NULL,
  `shot_date` date NOT NULL,
  `common_name` varchar(50) CHARACTER SET utf8 NOT NULL,
  `canonical_name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '純淨天然的學名',
  `tagged` tinyint(1) NOT NULL DEFAULT '0' COMMENT '資料已由使用者以#或*標記',
  `inWhiteList` tinyint(1) NOT NULL DEFAULT '0' COMMENT '資料已由白名單使用者以#或*標記',
  `rk` tinyint(1) NOT NULL COMMENT '是否為路死',
  `needMore` tinyint(1) NOT NULL DEFAULT '1',
  `authState` text CHARACTER SET utf8 NOT NULL COMMENT '授權方式',
  `byWhom` varchar(255) CHARACTER SET utf8 NOT NULL,
  `custom_id` varchar(255) CHARACTER SET ascii NOT NULL COMMENT '額外的ID, 類別(值)',
  `x` decimal(10,6) DEFAULT NULL COMMENT 'wgs84十進位經度',
  `y` decimal(10,6) DEFAULT NULL COMMENT 'wgs84十進位緯度',
  `altitude` decimal(10,1) DEFAULT NULL COMMENT '海拔高, 存到小數點後1位',
  `p1` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '縣市',
  `p2` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '鄉鎮',
  `p3` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '地名其他',
  `remark` text CHARACTER SET utf8 NOT NULL COMMENT '備註',
  `placename_id` varchar(255) CHARACTER SET utf8 NOT NULL,
  `hu` tinyint(1) NOT NULL DEFAULT '0' COMMENT '已由人工整理更新過',
  `modifiedDT` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activity` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`photo_id`),
  KEY `placename_id` (`placename_id`),
  KEY `activity` (`activity`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval`
--

DROP TABLE IF EXISTS `eval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eval` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `found` tinyint(1) NOT NULL,
  `run` int(10) unsigned NOT NULL,
  `matched` text NOT NULL,
  `total_matched` int(3) unsigned NOT NULL DEFAULT '0',
  `showed` text NOT NULL,
  `total_showed` int(3) unsigned NOT NULL DEFAULT '0',
  `intersect` text NOT NULL,
  `total_intersect` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extra_ids`
--

DROP TABLE IF EXISTS `extra_ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extra_ids` (
  `object_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `custom_id` varchar(100) CHARACTER SET utf8 NOT NULL,
  `id_type` varchar(100) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`object_id`,`custom_id`,`id_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `general_info`
--

DROP TABLE IF EXISTS `general_info`;
/*!50001 DROP VIEW IF EXISTS `general_info`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `general_info` (
 `photo_id` tinyint NOT NULL,
  `link` tinyint NOT NULL,
  `picture` tinyint NOT NULL,
  `post_id` tinyint NOT NULL,
  `person_id` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `created_time` tinyint NOT NULL,
  `shot_date` tinyint NOT NULL,
  `common_name` tinyint NOT NULL,
  `canonical_name` tinyint NOT NULL,
  `tagged` tinyint NOT NULL,
  `inWhiteList` tinyint NOT NULL,
  `post_from` tinyint NOT NULL,
  `authState` tinyint NOT NULL,
  `specimen_id` tinyint NOT NULL,
  `x` tinyint NOT NULL,
  `y` tinyint NOT NULL,
  `p1` tinyint NOT NULL,
  `p2` tinyint NOT NULL,
  `placename_id` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `placename_tw3`
--

DROP TABLE IF EXISTS `placename_tw3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `placename_tw3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Kind` varchar(255) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Phone_no` varchar(255) NOT NULL,
  `County` varchar(255) NOT NULL,
  `Townname` varchar(255) NOT NULL,
  `Xcoord` decimal(10,6) NOT NULL,
  `Ycoord` decimal(10,6) NOT NULL,
  `en_tongyong` varchar(255) NOT NULL,
  `en_hanyu` varchar(255) NOT NULL,
  `en_suggestion` varchar(255) NOT NULL,
  `geonameid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=159623 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `general_info`
--

/*!50001 DROP TABLE IF EXISTS `general_info`*/;
/*!50001 DROP VIEW IF EXISTS `general_info`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `general_info` AS select distinct `Photo`.`photo_id` AS `photo_id`,`Photo`.`link` AS `link`,`Photo`.`picture` AS `picture`,`Photo`.`embedded_in` AS `post_id`,`Person`.`person_id` AS `person_id`,`Person`.`name` AS `name`,`Photo`.`created_time` AS `created_time`,`Decide`.`shot_date` AS `shot_date`,`Decide`.`common_name` AS `common_name`,`Decide`.`canonical_name` AS `canonical_name`,`Decide`.`tagged` AS `tagged`,`Decide`.`inWhiteList` AS `inWhiteList`,`Post`.`post_from` AS `post_from`,`Person`.`authState` AS `authState`,`extra_ids`.`custom_id` AS `specimen_id`,`GoogleLoc`.`x` AS `x`,`GoogleLoc`.`y` AS `y`,`GoogleLoc`.`p1` AS `p1`,`GoogleLoc`.`p2` AS `p2`,`Location`.`placename_id` AS `placename_id` from ((((((`Photo` left join `Post` on((`Photo`.`photo_id` = `Post`.`object_id`))) left join `Decide` on((`Photo`.`photo_id` = `Decide`.`object_id`))) left join `Person` on((`Photo`.`uploaded_by` = `Person`.`person_id`))) left join `extra_ids` on(((convert(`Photo`.`photo_id` using utf8) = `extra_ids`.`object_id`) and (`extra_ids`.`id_type` = 'specimenID')))) left join `GoogleLoc` on((`Photo`.`photo_id` = `GoogleLoc`.`photo_id`))) left join `Location` on((convert(`Photo`.`photo_id` using utf8) = `Location`.`photo_id`))) group by `Photo`.`photo_id`,`Decide`.`common_name` order by `Photo`.`photo_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-11-20 23:21:47
