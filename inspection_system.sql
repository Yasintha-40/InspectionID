-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: inspection_system_fixed
-- ------------------------------------------------------
-- Server version	10.4.27-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `inspection_system_fixed`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `inspection_system_fixed` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `inspection_system_fixed`;

--
-- Table structure for table `bulk_print_job_items`
--

DROP TABLE IF EXISTS `bulk_print_job_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bulk_print_job_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `print_job_id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `sequence_no` tinyint(3) unsigned NOT NULL,
  `printed_at` datetime(6) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_print_job_officer` (`print_job_id`,`officer_id`),
  UNIQUE KEY `uq_print_job_sequence` (`print_job_id`,`sequence_no`),
  KEY `ix_print_items_officer` (`officer_id`),
  CONSTRAINT `fk_print_items_job` FOREIGN KEY (`print_job_id`) REFERENCES `bulk_print_jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_print_items_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bulk_print_job_items`
--

LOCK TABLES `bulk_print_job_items` WRITE;
/*!40000 ALTER TABLE `bulk_print_job_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `bulk_print_job_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bulk_print_jobs`
--

DROP TABLE IF EXISTS `bulk_print_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bulk_print_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `public_id` char(36) NOT NULL,
  `officer_ids` text NOT NULL,
  `record_count` tinyint(3) unsigned NOT NULL,
  `status` enum('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `completed_at` datetime(6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bulk_print_jobs_public_id` (`public_id`),
  KEY `ix_bulk_print_jobs_status_created` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bulk_print_jobs`
--

LOCK TABLES `bulk_print_jobs` WRITE;
/*!40000 ALTER TABLE `bulk_print_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `bulk_print_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_jobs`
--

DROP TABLE IF EXISTS `import_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `public_id` char(36) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `source_sha256` char(64) DEFAULT NULL,
  `duplicate_policy` enum('skip','update') NOT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'completed',
  `total_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `inserted_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `skipped_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `failed_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `started_at` datetime(6) DEFAULT NULL,
  `completed_at` datetime(6) DEFAULT NULL,
  `error_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_import_jobs_public_id` (`public_id`),
  KEY `ix_import_jobs_status_created` (`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_jobs`
--

LOCK TABLES `import_jobs` WRITE;
/*!40000 ALTER TABLE `import_jobs` DISABLE KEYS */;
INSERT INTO `import_jobs` VALUES (1,'bab338f3-9d3b-11f1-a8a6-005056c00008','New_formatted.xlsx',NULL,'skip','completed',14,0,0,13,1,'2026-08-21 12:39:53.000000','2026-08-21 12:39:53.000000',NULL,'2026-08-21 07:09:53','2026-08-21 14:08:48.613362');
/*!40000 ALTER TABLE `import_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officer_audit_log`
--

DROP TABLE IF EXISTS `officer_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officer_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `officer_id` int(11) DEFAULT NULL,
  `action` enum('insert','update','delete') NOT NULL,
  `actor` varchar(150) NOT NULL DEFAULT 'system',
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `occurred_at` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`id`),
  KEY `ix_audit_officer_time` (`officer_id`,`occurred_at`),
  KEY `ix_audit_time` (`occurred_at`),
  CONSTRAINT `fk_audit_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_audit_old_json` CHECK (`old_values` is null or json_valid(`old_values`)),
  CONSTRAINT `chk_audit_new_json` CHECK (`new_values` is null or json_valid(`new_values`))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officer_audit_log`
--

LOCK TABLES `officer_audit_log` WRITE;
/*!40000 ALTER TABLE `officer_audit_log` DISABLE KEYS */;
INSERT INTO `officer_audit_log` VALUES (1,1,'update','root@localhost','{\"full_name\": \"Ms. A.L.A.P. Umashani\", \"nic\": \"199881900934\", \"email\": \"probodika.umashani@ucr.ac.lk\", \"status\": \"\", \"issue_date\": \"2026-08-21\", \"expiry_date\": \"2029-08-21\"}','{\"full_name\": \"Ms. A.L.A.P. Umashani\", \"nic\": \"199881900934\", \"email\": \"probodika.umashani@ucr.ac.lk\", \"status\": \"Active\", \"issue_date\": \"2026-08-21\", \"expiry_date\": \"2029-08-21\"}','2026-08-21 14:09:47.870182');
/*!40000 ALTER TABLE `officer_audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officer_card_issuances`
--

DROP TABLE IF EXISTS `officer_card_issuances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officer_card_issuances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `officer_id` int(11) NOT NULL,
  `print_job_id` int(11) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `card_status` enum('issued','expired','revoked','replaced') NOT NULL DEFAULT 'issued',
  `serial_number` varchar(50) DEFAULT NULL,
  `issued_by` varchar(150) DEFAULT NULL,
  `revoked_at` datetime(6) DEFAULT NULL,
  `revoke_reason` varchar(500) DEFAULT NULL,
  `created_at` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  `updated_at` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_card_serial_number` (`serial_number`),
  KEY `ix_card_officer_status` (`officer_id`,`card_status`),
  KEY `ix_card_expiry` (`expiry_date`),
  KEY `ix_card_print_job` (`print_job_id`),
  CONSTRAINT `fk_card_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`),
  CONSTRAINT `fk_card_print_job` FOREIGN KEY (`print_job_id`) REFERENCES `bulk_print_jobs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_card_date_range` CHECK (`expiry_date` > `issue_date`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officer_card_issuances`
--

LOCK TABLES `officer_card_issuances` WRITE;
/*!40000 ALTER TABLE `officer_card_issuances` DISABLE KEYS */;
INSERT INTO `officer_card_issuances` VALUES (1,1,NULL,'2026-08-21','2029-08-21','issued',NULL,NULL,NULL,NULL,'2026-08-21 14:08:48.762346','2026-08-21 14:08:48.762346'),(2,10,NULL,'2026-08-21','2029-08-21','issued',NULL,NULL,NULL,NULL,'2026-08-21 14:08:48.762346','2026-08-21 14:08:48.762346');
/*!40000 ALTER TABLE `officer_card_issuances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officers`
--

DROP TABLE IF EXISTS `officers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `officer_id` varchar(20) NOT NULL,
  `guide_category` varchar(60) NOT NULL DEFAULT 'National Guide',
  `full_name` varchar(255) NOT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `languages` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `nic` varchar(30) DEFAULT NULL,
  `nic_normalized` varchar(30) GENERATED ALWAYS AS (nullif(ucase(replace(replace(trim(`nic`),' ',''),'-','')),'')) STORED,
  `email` varchar(150) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `designation` varchar(100) DEFAULT 'Inspection Officer',
  `department` varchar(150) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('Active','Inactive','Expired','Suspended') NOT NULL DEFAULT 'Active',
  `row_version` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `deleted_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `officer_id` (`officer_id`),
  UNIQUE KEY `uq_officers_nic_normalized` (`nic_normalized`),
  KEY `ix_officers_status_expiry` (`status`,`expiry_date`),
  KEY `ix_officers_name` (`full_name`),
  KEY `ix_officers_email` (`email`),
  KEY `ix_officers_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officers`
--

LOCK TABLES `officers` WRITE;
/*!40000 ALTER TABLE `officers` DISABLE KEYS */;
INSERT INTO `officers` VALUES (1,'INS-0001','National Guide','Ms. A.L.A.P. Umashani',NULL,NULL,'B/19/A, Railway Quarters, Ratmalana.','199881900934','199881900934','probodika.umashani@ucr.ac.lk','D:\\PHOTOS\\A L A P Umashani - Probodika Umashani.jpg','D:\\PHOTOS\\Ms. A.L.A.P. Umashani QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,'2026-08-21','2029-08-21','Active',1,'2026-08-21 03:57:54','2026-08-21 14:09:47.870182',NULL),(2,'INS-0002','National Guide','Mr. C.H. De Saram',NULL,NULL,'72, De Saram Place, Yakkala Road, Gampaha','560091907V','560091907V','chrisdesaram@gmail.com','D:\\PHOTOS\\Chris de Saram.jpg','D:\\PHOTOS\\Mr. C.H. De Saram QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(3,'INS-0003','National Guide','Mr. M.S. Gamunu Srilal',NULL,NULL,'11/1B,1 st Ln,Raphael Thennakoon Mw,Parakandeniya,Imbulgoda',NULL,NULL,'gamunusrilal@gmail.com','D:\\PHOTOS\\Gamunu Portrait AI - Gamunu Srilal.jpg','D:\\PHOTOS\\Mr. M.S. Gamunu Srilal QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(4,'INS-0004','National Guide','Ms. Priyanthika Wijenaika',NULL,NULL,'No. 134, W. A. silva Mawatha, Wellawatte, Colombo 06.','556971943v','556971943V','wijenaikepriyanthika15@gmail.com','D:\\PHOTOS\\PP Wijenaike.jpg','D:\\PHOTOS\\Ms. Priyanthika Wijenaika QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(5,'INS-0005','National Guide','Mr. Nuwan Chamara',NULL,NULL,'No 144/A, Maddumage Watta, Gangodawila, Nugegoda.','802953976V','802953976V','chamichamara1980@gmail.com','D:\\PHOTOS\\Nuwan Chamara Senanayake.jpg',NULL,'Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(6,'INS-0006','National Guide','Ms. Namashiwayam Gishila',NULL,NULL,'Peace Haven, Adisham Road, Haputale','846854533V','846854533V','gishilashivam1984@gmail.com','D:\\PHOTOS\\N GISHILA.jpg','D:\\PHOTOS\\Ms. Namashiwayam Gishila QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(7,'INS-0007','National Guide','Mr. Gihan Wijesuriya',NULL,NULL,'No 11,\"Pinibindu Uyana\",Nilwakka,Kegalle','902261230V','902261230V','gihanwijesuriya90@gmail.com','D:\\PHOTOS\\W.A.G.Wijesuriya - Gihan.jpg','D:\\PHOTOS\\Mr. Gihan Wijesuriya QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(8,'INS-0008','National Guide','Mr. Rohana Bandara',NULL,NULL,'Sri Lanka Institute Of Tourism and Hotel Management , Golf Link Road , Bandarawela','770100801v','770100801V','bandaraw@slithm.edu.lk',NULL,NULL,'Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(9,'INS-0009','National Guide','Mr. Upul Atapattu',NULL,NULL,'264/28, Namal Uyana, Thambiligasmulla Road, Kiribathgoda.','570033948v','570033948V','uattapattu@gmail.com','D:\\PHOTOS\\UPUL ATHAPATHTHU .jpg','D:\\PHOTOS\\Mr. Upul Atapattu QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(10,'INS-0010','National Guide','Mr. Ravindra Senavirathne',NULL,NULL,'No 330 Siyambalagoda Danture','812730517V','812730517V','ravindras@slithm.edu.lk',NULL,NULL,'Inspection Officer',NULL,'Western',NULL,NULL,'2026-08-21','2029-08-21','Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(11,'INS-0011','National Guide','Mr. Roshan Fernando',NULL,NULL,'6/3 , St Rita’s road , Mt Lavinia','561162433v','561162433V','kfrfdo@gmail.com','D:\\PHOTOS\\Roshan Fernando.jpg','D:\\PHOTOS\\Mr. Roshan Fernando QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(12,'INS-0012','National Guide','Ms. W. A . I. Madupani Gunasekara',NULL,NULL,'3 C, 107, NATIONAL HOUSING SCHEME, MATTEGODA','867830103v','867830103V','indrachapa.tourism@gmail.com','D:\\PHOTOS\\W.A.I.M.Gunasekara - Indrachapa Gunasekara.jpg','D:\\PHOTOS\\Ms. W. A . I. Madupani Gunasekara QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(13,'INS-0013','National Guide','Mr. Sujith De Silva',NULL,NULL,'No.5/1, De Mel Road, Katubedda','672331250V','672331250V','sujithdesilva29@gmail.com','D:\\PHOTOS\\SUJITH MERVIN.jpg','D:\\PHOTOS\\Mr. Sujith De Silva QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL),(14,'INS-0014','National Guide','Ms. Gangani Marasinghe',NULL,NULL,'No. 197/5, Dudly Senanayaka Mawatha,Negambo road, Nittambuwa','937761716V','937761716V','gangani325@gmail.com','D:\\PHOTOS\\M.M.G.K Marasinghe - gangani marasinghe.jpg','D:\\PHOTOS\\Ms. Gangani Marasinghe QR.jpg','Inspection Officer',NULL,NULL,NULL,NULL,NULL,NULL,'Active',1,'2026-08-21 03:57:54','2026-08-21 14:08:48.498350',NULL);
/*!40000 ALTER TABLE `officers` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_officers_after_insert
AFTER INSERT ON officers FOR EACH ROW
BEGIN
    INSERT INTO officer_audit_log (officer_id, action, actor, new_values)
    VALUES (NEW.id, 'insert', CURRENT_USER(), JSON_OBJECT(
        'officer_id', NEW.officer_id, 'full_name', NEW.full_name,
        'nic', NEW.nic, 'email', NEW.email, 'status', NEW.status
    ));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_officers_after_update
AFTER UPDATE ON officers FOR EACH ROW
BEGIN
    INSERT INTO officer_audit_log (officer_id, action, actor, old_values, new_values)
    VALUES (NEW.id, 'update', CURRENT_USER(),
        JSON_OBJECT('full_name', OLD.full_name, 'nic', OLD.nic, 'email', OLD.email,
                    'status', OLD.status, 'issue_date', OLD.issue_date, 'expiry_date', OLD.expiry_date),
        JSON_OBJECT('full_name', NEW.full_name, 'nic', NEW.nic, 'email', NEW.email,
                    'status', NEW.status, 'issue_date', NEW.issue_date, 'expiry_date', NEW.expiry_date)
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_officers_after_delete
AFTER DELETE ON officers FOR EACH ROW
BEGIN
    INSERT INTO officer_audit_log (officer_id, action, actor, old_values)
    VALUES (NULL, 'delete', CURRENT_USER(), JSON_OBJECT(
        'id', OLD.id, 'officer_id', OLD.officer_id, 'full_name', OLD.full_name,
        'nic', OLD.nic, 'email', OLD.email, 'status', OLD.status
    ));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `provinces`
--

DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `provinces` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_order` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_provinces_name` (`name`),
  KEY `ix_provinces_active_order` (`is_active`,`display_order`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provinces`
--

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;
INSERT INTO `provinces` VALUES
(1,'Western',1,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(2,'Central',2,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(3,'Southern',3,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(4,'Northern',4,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(5,'Eastern',5,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(6,'North Western',6,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(7,'North Central',7,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(8,'Uva',8,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946'),
(9,'Sabaragamuwa',9,1,'2026-08-21 14:35:15','2026-08-21 14:35:15.521946');
/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schema_migrations`
--

DROP TABLE IF EXISTS `schema_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_migrations` (
  `version` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `applied_at` datetime(6) NOT NULL DEFAULT current_timestamp(6),
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schema_migrations`
--

LOCK TABLES `schema_migrations` WRITE;
/*!40000 ALTER TABLE `schema_migrations` DISABLE KEYS */;
INSERT INTO `schema_migrations` VALUES
('2026-08-21-001','Production schema, normalized print items, card history, audit trail and indexes','2026-08-21 14:08:48.798503'),
('2026-08-21-002','Add database-managed Sri Lankan provinces','2026-08-21 14:35:15.525684');
/*!40000 ALTER TABLE `schema_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'inspection_system_fixed'
--

--
-- Dumping routines for database 'inspection_system_fixed'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-21 14:11:31
