-- MySQL dump 10.13  Distrib 5.1.41, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: 1chan
-- ------------------------------------------------------
-- Server version	5.1.41-3ubuntu12.10

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
-- Table structure for table `1chan_category`
--

DROP TABLE IF EXISTS `1chan_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `1chan_category` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `pos` int(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `posts` int(32) NOT NULL,
  `code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `code` (`code`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `1chan_comment`
--

DROP TABLE IF EXISTS `1chan_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `1chan_comment` (
  `id` int(32) NOT NULL,
  `post_id` int(32) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `author` varchar(32) DEFAULT 'anonymous',
  `created_at` int(32) NOT NULL,
  `text` text NOT NULL,
  `special_comment` text NOT NULL,
  PRIMARY KEY (`id`,`post_id`),
  KEY `ip_index` (`ip`(16))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `1chan_post`
--

DROP TABLE IF EXISTS `1chan_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `1chan_post` (
  `id` int(32) NOT NULL,
  `ip` varchar(255) DEFAULT NULL,
  `author` varchar(32) DEFAULT 'anonymous',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `pinned` tinyint(1) NOT NULL DEFAULT '0',
  `rated` tinyint(1) DEFAULT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `rateable` tinyint(1) NOT NULL DEFAULT '1',
  `bumpable` tinyint(1) NOT NULL DEFAULT '1',
  `category` int(32) DEFAULT NULL,
  `created_at` int(32) NOT NULL,
  `updated_at` int(32) NOT NULL,
  `link` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `text_full` text NOT NULL,
  `rate` int(32) NOT NULL DEFAULT '0',
  `comments` int(32) NOT NULL DEFAULT '0',
  `special_comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hidden` (`hidden`,`rated`,`category`,`created_at`,`updated_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-07-10 17:32:45
