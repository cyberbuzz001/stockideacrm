-- Shreesvarn CRM - Production Database Schema (MariaDB/MySQL)
-- Compatibility: Ubuntu 22.04 / CyberPanel

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

-- --------------------------------------------------------
-- Users Table
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Employee',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commission_rate` decimal(5,2) DEFAULT NULL,
  `lead_weight` int(11) DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Leads Table (Current Year Shard)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leads_2026` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `mobile` varchar(25) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Fresh',
  `lead_score` int(11) DEFAULT 0,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `follow_up_date` datetime DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `status_changed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leads_mobile_unique` (`mobile`),
  KEY `leads_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `leads_2026_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Payments Table 
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `payment_date` date NOT NULL,
  `commission_amount` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- System Settings (RBAC & Branding)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Initial Data (Optional)
-- --------------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES 
('Admin', 'admin@shreesvarn.com', '$2y$12$bYGBKeHjJJWpmfdp/ssCAeNIQA1Gg7o7U7O5iQCWVUcw7a1ZNSvYa', 'Admin');

COMMIT;
