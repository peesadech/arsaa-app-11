-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: couseio_db
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `year` int NOT NULL COMMENT 'ปีการศึกษา',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: Active, 2: Inactive',
  `is_current_year` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ปีการศึกษาปัจจุบัน',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `academic_years_year_unique` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,2026,1,0,'2026-03-26 06:00:38','2026-03-26 06:00:38'),(2,2025,1,0,'2026-03-26 06:00:44','2026-03-26 06:00:44');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

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
  PRIMARY KEY (`key`)
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
  PRIMARY KEY (`key`)
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
-- Table structure for table `classrooms`
--

DROP TABLE IF EXISTS `classrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classrooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อห้องเรียน',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classrooms_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classrooms`
--

LOCK TABLES `classrooms` WRITE;
/*!40000 ALTER TABLE `classrooms` DISABLE KEYS */;
INSERT INTO `classrooms` VALUES (1,'1',1,'2026-03-26 08:49:56','2026-03-26 08:49:56'),(2,'2',1,'2026-03-26 08:50:00','2026-03-26 08:50:00'),(3,'3',1,'2026-03-26 08:50:04','2026-03-26 08:50:04');
/*!40000 ALTER TABLE `classrooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อวิชา',
  `grade_id` bigint unsigned NOT NULL COMMENT 'รหัสระดับชั้น',
  `semester_id` bigint unsigned NOT NULL COMMENT 'รหัสภาคเรียน',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_name_unique` (`name`),
  KEY `courses_grade_id_foreign` (`grade_id`),
  KEY `courses_semester_id_foreign` (`semester_id`),
  CONSTRAINT `courses_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `courses_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,'Thai',1,1,1,'2026-03-26 07:56:28','2026-03-27 01:22:10'),(2,'Enlish 201',2,1,1,'2026-03-27 01:21:57','2026-03-27 01:21:57'),(3,'Thai 102',1,2,1,'2026-03-27 02:16:30','2026-03-27 02:16:30'),(4,'Match 101',1,1,1,'2026-03-27 02:17:24','2026-03-27 02:17:24'),(5,'Match 202',2,2,1,'2026-03-27 02:25:36','2026-03-27 02:25:36');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `current_academic_settings`
--

DROP TABLE IF EXISTS `current_academic_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `current_academic_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned NOT NULL,
  `semester_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `current_academic_settings_academic_year_id_foreign` (`academic_year_id`),
  KEY `current_academic_settings_semester_id_foreign` (`semester_id`),
  CONSTRAINT `current_academic_settings_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `current_academic_settings_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `current_academic_settings`
--

LOCK TABLES `current_academic_settings` WRITE;
/*!40000 ALTER TABLE `current_academic_settings` DISABLE KEYS */;
INSERT INTO `current_academic_settings` VALUES (4,1,1,'2026-03-27 02:29:41','2026-03-27 02:29:41');
/*!40000 ALTER TABLE `current_academic_settings` ENABLE KEYS */;
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
-- Table structure for table `global_schedules`
--

DROP TABLE IF EXISTS `global_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teaching_days` json DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `period_duration` smallint unsigned NOT NULL DEFAULT '50',
  `day_configs` json DEFAULT NULL,
  `periods_per_day` smallint unsigned NOT NULL DEFAULT '8',
  `breaks` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_schedules`
--

LOCK TABLES `global_schedules` WRITE;
/*!40000 ALTER TABLE `global_schedules` DISABLE KEYS */;
INSERT INTO `global_schedules` VALUES (1,'[\"1\", \"2\", \"3\", \"4\", \"5\", \"6\", \"7\"]','17:00:00',50,'{\"1\": {\"breaks\": {\"2\": 15}, \"periods\": 4, \"start_time\": null}, \"2\": {\"breaks\": {\"2\": 15}, \"periods\": 5, \"start_time\": null}, \"3\": {\"breaks\": {\"2\": 15}, \"periods\": 4, \"start_time\": null}, \"4\": {\"breaks\": {\"2\": 15}, \"periods\": 4, \"start_time\": null}, \"5\": {\"breaks\": {\"2\": 15}, \"periods\": 3, \"start_time\": null}, \"6\": {\"breaks\": {\"2\": 15, \"4\": 60}, \"periods\": 6, \"start_time\": \"08:30\"}, \"7\": {\"breaks\": [], \"periods\": 0, \"start_time\": null}}',4,'[{\"duration\": 15, \"after_period\": 2}]','2026-03-27 02:57:55','2026-03-27 03:38:47');
/*!40000 ALTER TABLE `global_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grades`
--

DROP TABLE IF EXISTS `grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_th` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grades`
--

LOCK TABLES `grades` WRITE;
/*!40000 ALTER TABLE `grades` DISABLE KEYS */;
INSERT INTO `grades` VALUES (1,'ม 1','M 1',NULL,1,'2026-03-26 06:25:43','2026-03-26 06:25:43'),(2,'ม2','M2',NULL,1,'2026-03-26 06:25:52','2026-03-26 06:25:52'),(3,'ม3','M3',NULL,1,'2026-03-27 00:01:30','2026-03-27 00:02:19');
/*!40000 ALTER TABLE `grades` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_01_14_102149_add_role_to_users_table',1),(5,'2026_01_14_104718_create_permission_types_table',1),(6,'2026_01_14_122741_add_image_path_to_permission_types_table',1),(7,'2026_01_15_065114_create_settings_table',1),(8,'2026_01_15_072104_add_image_path_to_users_table',1),(9,'2026_01_15_084643_add_theme_to_settings_table',1),(10,'2026_01_15_105718_add_status_to_users_table',1),(11,'2026_01_16_071000_add_registration_enabled_to_settings_table',1),(12,'2026_01_16_071747_add_google_id_to_users_table',1),(13,'2026_01_16_073841_add_google_configs_to_settings_table',1),(14,'2026_01_16_081827_add_google_login_enabled_to_settings_table',1),(15,'2026_01_16_084036_add_facebook_configs_to_settings_table',1),(16,'2026_01_16_084036_add_facebook_id_to_users_table',1),(17,'2026_03_11_090824_create_grades_table',1),(18,'2026_03_11_092900_create_semesters_table',1),(19,'2026_03_11_093456_create_academic_years_table_v2',1),(20,'2026_03_11_094001_create_classrooms_table',1),(21,'2026_03_11_100000_create_courses_table',1),(22,'2026_03_13_063432_create_permission_tables',1),(23,'2026_03_13_120000_add_is_current_year_to_academic_years_table',1),(24,'2026_03_13_130000_create_current_academic_settings_table',1),(25,'2026_03_27_062017_create_opened_grades_table',2),(26,'2026_03_27_070503_create_opened_classrooms_table',3),(27,'2026_03_27_140000_create_opened_courses_table',4),(28,'2026_03_27_180000_create_teaching_schedules_table',5),(29,'2026_03_27_180000_create_global_schedules_table',6),(30,'2026_03_27_102016_add_day_configs_to_global_schedules_table',7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (3,'App\\Models\\User',1),(3,'AppModelsUser',1),(1,'App\\Models\\User',2);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opened_classrooms`
--

DROP TABLE IF EXISTS `opened_classrooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opened_classrooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned NOT NULL,
  `semester_id` bigint unsigned NOT NULL,
  `grade_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oc_year_sem_grade_class_unique` (`academic_year_id`,`semester_id`,`grade_id`,`classroom_id`),
  KEY `opened_classrooms_semester_id_foreign` (`semester_id`),
  KEY `opened_classrooms_grade_id_foreign` (`grade_id`),
  KEY `opened_classrooms_classroom_id_foreign` (`classroom_id`),
  CONSTRAINT `opened_classrooms_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_classrooms_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_classrooms_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_classrooms_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opened_classrooms`
--

LOCK TABLES `opened_classrooms` WRITE;
/*!40000 ALTER TABLE `opened_classrooms` DISABLE KEYS */;
INSERT INTO `opened_classrooms` VALUES (28,1,1,1,1,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(29,1,1,1,2,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(30,1,1,1,3,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(37,1,1,2,1,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(38,1,1,2,2,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(39,1,1,2,3,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(40,1,2,1,1,'2026-03-27 02:19:28','2026-03-27 02:19:28'),(41,1,2,1,2,'2026-03-27 02:19:28','2026-03-27 02:19:28'),(42,1,2,1,3,'2026-03-27 02:19:28','2026-03-27 02:19:28'),(43,1,2,2,1,'2026-03-27 02:24:55','2026-03-27 02:24:55'),(44,1,2,2,2,'2026-03-27 02:24:55','2026-03-27 02:24:55'),(45,1,2,2,3,'2026-03-27 02:24:55','2026-03-27 02:24:55');
/*!40000 ALTER TABLE `opened_classrooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opened_courses`
--

DROP TABLE IF EXISTS `opened_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opened_courses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned NOT NULL,
  `semester_id` bigint unsigned NOT NULL,
  `grade_id` bigint unsigned NOT NULL,
  `classroom_id` bigint unsigned NOT NULL,
  `course_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `oc_year_sem_grade_room_course_unique` (`academic_year_id`,`semester_id`,`grade_id`,`classroom_id`,`course_id`),
  KEY `opened_courses_semester_id_foreign` (`semester_id`),
  KEY `opened_courses_grade_id_foreign` (`grade_id`),
  KEY `opened_courses_classroom_id_foreign` (`classroom_id`),
  KEY `opened_courses_course_id_foreign` (`course_id`),
  CONSTRAINT `opened_courses_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_courses_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_courses_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_courses_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opened_courses`
--

LOCK TABLES `opened_courses` WRITE;
/*!40000 ALTER TABLE `opened_courses` DISABLE KEYS */;
INSERT INTO `opened_courses` VALUES (4,1,1,1,1,1,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(5,1,1,1,2,1,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(6,1,1,1,3,1,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(10,1,1,2,1,2,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(11,1,1,2,2,2,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(12,1,1,2,3,2,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(13,1,1,1,1,4,'2026-03-27 02:17:45','2026-03-27 02:17:45'),(14,1,2,1,1,3,'2026-03-27 02:19:28','2026-03-27 02:19:28'),(16,1,2,1,3,3,'2026-03-27 02:19:28','2026-03-27 02:19:28'),(17,1,2,1,2,3,'2026-03-27 02:24:55','2026-03-27 02:24:55'),(18,1,2,2,1,5,'2026-03-27 02:26:00','2026-03-27 02:26:00'),(19,1,2,2,2,5,'2026-03-27 02:26:09','2026-03-27 02:26:09'),(20,1,2,2,3,5,'2026-03-27 02:26:16','2026-03-27 02:26:16');
/*!40000 ALTER TABLE `opened_courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opened_grades`
--

DROP TABLE IF EXISTS `opened_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opened_grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `academic_year_id` bigint unsigned NOT NULL,
  `semester_id` bigint unsigned NOT NULL,
  `grade_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opened_grades_academic_year_id_semester_id_grade_id_unique` (`academic_year_id`,`semester_id`,`grade_id`),
  KEY `opened_grades_semester_id_foreign` (`semester_id`),
  KEY `opened_grades_grade_id_foreign` (`grade_id`),
  CONSTRAINT `opened_grades_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_grades_grade_id_foreign` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE,
  CONSTRAINT `opened_grades_semester_id_foreign` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opened_grades`
--

LOCK TABLES `opened_grades` WRITE;
/*!40000 ALTER TABLE `opened_grades` DISABLE KEYS */;
INSERT INTO `opened_grades` VALUES (15,1,1,1,'2026-03-27 01:33:01','2026-03-27 01:33:01'),(18,1,1,2,'2026-03-27 01:43:25','2026-03-27 01:43:25'),(19,1,2,1,'2026-03-27 02:19:27','2026-03-27 02:19:27'),(20,1,2,2,'2026-03-27 02:24:55','2026-03-27 02:24:55');
/*!40000 ALTER TABLE `opened_grades` ENABLE KEYS */;
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
-- Table structure for table `permission_types`
--

DROP TABLE IF EXISTS `permission_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_types` (
  `permissionType_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `permissionType_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `permissionType_image_path` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`permissionType_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_types`
--

LOCK TABLES `permission_types` WRITE;
/*!40000 ALTER TABLE `permission_types` DISABLE KEYS */;
INSERT INTO `permission_types` VALUES (1,'view','2026-03-26 06:29:46','2026-03-26 06:29:46',NULL),(2,'create','2026-03-26 06:29:51','2026-03-26 06:29:51',NULL),(3,'delete','2026-03-26 06:29:58','2026-03-26 06:29:58',NULL);
/*!40000 ALTER TABLE `permission_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'user','web','2026-03-26 06:30:16','2026-03-26 08:49:32'),(2,'permissions','web','2026-03-26 07:53:38','2026-03-26 08:49:40');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(1,3),(2,3),(1,4),(2,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'SuperAdmin','web','2026-03-26 05:57:00','2026-03-26 05:57:00'),(3,'Admin','web','2026-03-26 08:30:09','2026-03-26 08:46:40'),(4,'user','web','2026-03-26 08:39:32','2026-03-26 08:39:32');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `semesters`
--

DROP TABLE IF EXISTS `semesters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semesters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `semester_number` int NOT NULL COMMENT 'ภาคเรียนที่',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: Active, 2: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `semesters_semester_number_unique` (`semester_number`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `semesters`
--

LOCK TABLES `semesters` WRITE;
/*!40000 ALTER TABLE `semesters` DISABLE KEYS */;
INSERT INTO `semesters` VALUES (1,1,1,'2026-03-26 06:01:02','2026-03-26 06:01:02'),(2,2,1,'2026-03-26 06:01:09','2026-03-26 06:01:09');
/*!40000 ALTER TABLE `semesters` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('RB1NBgxq16hmeZzwuk4YQsboWgmHpfZaCfM6Hwc3',2,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','YTo3OntzOjY6Il90b2tlbiI7czo0MDoidHhaM2RqZFBOWVY0WkgwWUhRNHhsemg3bTRWZzNaUTY1NXNCeXI2OCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly9hcnNhYS1hcHAudGVzdC9hZG1pbi9kYXNoYm9hcmQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc3NDYwMzc2OTt9czoyNDoiY3VycmVudF9hY2FkZW1pY195ZWFyX2lkIjtzOjE6IjEiO3M6MTk6ImN1cnJlbnRfc2VtZXN0ZXJfaWQiO3M6MToiMSI7fQ==',1774607942);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `app_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'My App',
  `app_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `google_login_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `facebook_login_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `google_client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_client_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_client_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'Couseio',NULL,0,0,0,'dark','2026-03-26 05:59:44','2026-03-26 22:57:20',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
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
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1=active, 2=not active',
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@gmail.com',1,NULL,'admin','2026-03-26 05:46:44','$2y$12$zvPShftravF7zdpvwThvPuWwsFA74apqpgQrG1Qg2b5H7.jC31GjW',NULL,NULL,'vUgy3rrnHnRGCrqGGBfTcisvBAonJm849GFWT4E8Q3ckUq5eTctB0ZAJMFdA','2026-03-26 05:46:44','2026-03-26 19:57:51'),(2,'SuperAdmin','admin@gmail.com',1,NULL,'SuperAdmin',NULL,'$2y$12$Q0zes/OXFx6RlFjaey8.4uh6Z7Oxseqd5MQDqoX8hXX1t/AiQEypG',NULL,NULL,'1cSLIh2sQj8qdgSOfh4AnJoJI7Ao5Hhy181Xh5lsZIKaNUnEFO8E0xgtMYUp','2026-03-26 05:46:44','2026-03-26 05:46:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'couseio_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-27 17:43:18
