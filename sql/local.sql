-- MySQL dump 10.13  Distrib 8.0.35, for Win64 (x86_64)
--
-- Host: ::1    Database: local
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inspections`
--

DROP TABLE IF EXISTS `inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inspections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_id` bigint unsigned NOT NULL,
  `surveyor_id` bigint unsigned NOT NULL,
  `shift` enum('A','B','C') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_match` tinyint(1) NOT NULL DEFAULT '1',
  `actual_length` decimal(8,3) DEFAULT NULL,
  `actual_gb` decimal(8,2) DEFAULT NULL,
  `actual_pb` decimal(8,2) DEFAULT NULL,
  `actual_diameter` decimal(8,2) DEFAULT NULL,
  `actual_vol_cbm` decimal(10,6) DEFAULT NULL,
  `actual_l_ref` decimal(10,6) DEFAULT NULL,
  `actual_d_ref` decimal(10,6) DEFAULT NULL,
  `buyer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surveyor_remarks` text COLLATE utf8mb4_unicode_ci,
  `images` json DEFAULT NULL,
  `verified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspections_log_id_foreign` (`log_id`),
  KEY `inspections_surveyor_id_foreign` (`surveyor_id`),
  CONSTRAINT `inspections_log_id_foreign` FOREIGN KEY (`log_id`) REFERENCES `logs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inspections_surveyor_id_foreign` FOREIGN KEY (`surveyor_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inspections`
--

LOCK TABLES `inspections` WRITE;
/*!40000 ALTER TABLE `inspections` DISABLE KEYS */;
INSERT INTO `inspections` VALUES (1,1,2,'B',1,13.900,80.00,75.00,77.00,NULL,NULL,NULL,'MASHFI EXIM',NULL,'{\"top\": \"inspections/WF1YNQeo23tTxqYfivOmLULZxSvu6JUzqo0bkqnv.png\", \"butt\": \"inspections/v6iIx9jh9mYmPkvn3mOYDlRvcFZ7SkGWsLXNEvtS.png\"}','2026-02-01 12:21:31','2026-02-01 12:21:31','2026-02-01 12:21:31');
/*!40000 ALTER TABLE `inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vessel_id` bigint unsigned NOT NULL,
  `serial_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `species` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `length` decimal(8,3) DEFAULT NULL,
  `girth_butt` decimal(8,2) DEFAULT NULL,
  `girth_top` decimal(8,2) DEFAULT NULL,
  `diameter` decimal(8,2) DEFAULT NULL,
  `vol_cbm` decimal(10,6) DEFAULT NULL,
  `l_ref` decimal(10,6) DEFAULT NULL,
  `d_ref` decimal(10,6) DEFAULT NULL,
  `buyer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logs_vessel_id_foreign` (`vessel_id`),
  KEY `logs_tag_no_index` (`tag_no`),
  CONSTRAINT `logs_vessel_id_foreign` FOREIGN KEY (`vessel_id`) REFERENCES `vessels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,1,'SF_1','AD773GC1','45305-05-1','AZOBE','CAMEROON',13.900,80.00,75.00,77.00,6.472725,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (2,1,'SF_2','AD004LDE','45305-05-2','AZOBE','CAMEROON',8.600,75.00,73.00,74.00,3.698731,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (3,1,'SF_3','AD009GD8','45305-08-2','OKAN','CAMEROON',13.100,69.00,59.00,64.00,4.214268,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (4,1,'SF_4','AD010GDR','45305-16-1','AZOBE','CAMEROON',12.200,87.00,75.00,81.00,6.286671,0.800000,NULL,'MASHFI EXIM','MALADI','2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (5,1,'SF_5','AD018GD9','45305-18-1','NIOVE','CAMEROON',14.300,81.00,67.00,74.00,6.150216,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (6,1,'SF_6','AD019GD7','45305-19-1','AZOBE','CAMEROON',10.400,91.00,86.00,88.00,6.325423,0.300000,NULL,'MASHFI EXIM','BEND','2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (7,1,'SF_7','AD024GDG','45305-21-1','AZOBE','CAMEROON',10.800,92.00,84.00,88.00,6.568709,NULL,NULL,'SEA LAND TRADING',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (8,1,'SF_8','AD030GDP','45306-07-1','AZOBE','LIBERIA',13.700,76.00,67.00,71.00,5.424106,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (9,1,'SF_9','AD034GDF','45306-08-2','OKAN','LIBERIA',8.800,89.00,82.00,85.00,4.993573,NULL,NULL,'MASHFI EXIM','HOLE','2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (10,1,'SF_10','AD035GDD','45306-09-1','OKAN','LIBERIA',10.400,89.00,80.00,84.00,5.763454,NULL,NULL,'SEA LAND TRADING',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (11,1,'SF_11','AD037GD9','45306-13-2','AZOBE','LIBERIA',6.700,96.00,83.00,89.00,4.168173,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (12,1,'SF_12','AD048GD6','45306-25-2','AZOBE','LIBERIA',12.600,70.00,57.00,63.00,3.927738,0.500000,NULL,'MASHFI EXIM','CRACK','2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (13,1,'SF_13','AD054GDD','45306-25-3','NIOVE','LIBERIA',13.600,70.00,59.00,64.00,4.375118,NULL,NULL,'SEA LAND TRADING',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (14,1,'SF_14','AD056GD9','45306-26-1','AZOBE','LIBERIA',11.700,88.00,72.00,80.00,5.881075,NULL,NULL,'MASHFI EXIM',NULL,'2026-02-01 12:21:02','2026-02-01 12:21:02');
INSERT INTO `logs` VALUES (15,1,'SF_15','AD059GD3','45306-30-2','DABEMA','LIBERIA',6.100,84.00,81.00,82.00,3.221428,0.300000,NULL,'MASHFI EXIM','CRACK','2026-02-01 12:21:02','2026-02-01 12:21:02');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` VALUES (4,'2026_01_28_021500_create_vessels_table',1);
INSERT INTO `migrations` VALUES (5,'2026_01_28_021501_create_logs_table',1);
INSERT INTO `migrations` VALUES (6,'2026_01_28_021502_create_inspections_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('dvYMrfgQ5h3KZnLdjpK3hZXitGQmg9KMF1EjJnIX',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiT05acWw1VldaUkx0Vk1DaUxONGhVa1RBS2tNcDRuQ1R1enZadFdZdSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozNDoiaHR0cHM6Ly9sb2dzLmxvY2FsL2FkbWluL3Zlc3NlbHMvMSI7czo1OiJyb3V0ZSI7czoxMjoidmVzc2Vscy5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1775640916);
INSERT INTO `sessions` VALUES ('yula2bS0BCkD9M7zv4vh8isLiy48PnqI0WBZkRmT',1,'127.0.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUlp2WXBhTFFlaFNTeGRqMldQa1pKM2tmUWNjbFBpd21UdDg3SHE3UCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozMjoiaHR0cHM6Ly9sb2dzLmxvY2FsL2FkbWluL3Zlc3NlbHMiO3M6NToicm91dGUiO3M6MTM6InZlc3NlbHMuaW5kZXgiO319',1775328731);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','surveyor','buyer') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@example.com','2026-02-01 12:20:28','$2y$12$eVByfcaRQfFd83Jw5Cmwm.JJb7EdvCy7zEErmvgxkQ3AXpix5pxxC','admin','0PVdUp56kYFfMoS5w39KDHFRyDa7hYwZhkmIXjWM9nx8UTiLUFcMxxmaJOzU','2026-02-01 12:20:28','2026-02-01 12:20:28');
INSERT INTO `users` VALUES (2,'Surveyor One','surveyor@example.com','2026-02-01 12:20:28','$2y$12$mPytT2A1oiiYo.jUD7WCtucqoHxw4oW8Vk1i2Ymf4lYXwKHoJ7/9a','surveyor','yoo3HYVDlI','2026-02-01 12:20:28','2026-02-01 12:20:28');
INSERT INTO `users` VALUES (3,'Buyer One','buyer@example.com','2026-02-01 12:20:28','$2y$12$5jMiLMpoZ6buKBUXvnYzueUifTGhTrf9a/zU5PrMzJhUjqCBU9x7u','buyer','4hevlOeYdv','2026-02-01 12:20:28','2026-02-01 12:20:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vessels`
--

DROP TABLE IF EXISTS `vessels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vessels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vessel_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vessel_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arrival_date` date DEFAULT NULL,
  `status` enum('open','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `surveyor_access` tinyint(1) NOT NULL DEFAULT '0',
  `buyer_access` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vessels_vessel_code_unique` (`vessel_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vessels`
--

LOCK TABLES `vessels` WRITE;
/*!40000 ALTER TABLE `vessels` DISABLE KEYS */;
INSERT INTO `vessels` VALUES (1,'SF_2504','Mashfi-2026-02','2026-02-18','open',1,1,'2026-02-01 12:21:02','2026-04-04 12:44:30');
/*!40000 ALTER TABLE `vessels` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-09  0:43:59
