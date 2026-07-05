-- ============================================================
-- DATABASE SCHEMA — Sistem Aplikasi Bank Sampah EcoBank
-- SMKN 2 Indramayu | Laravel 11 + Spatie Permission
-- ============================================================
-- Generated: 2026-07-03
-- Engine: MySQL 8.0+ / MariaDB 10.6+
-- Charset: utf8mb4 (for emoji support)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: users
-- Stores all users across all roles.
-- Roles: siswa, operator, walikelas, manajer
-- ============================================================
CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`              VARCHAR(255)    NOT NULL,
  `email`             VARCHAR(255)    NOT NULL,
  `nisn`              VARCHAR(20)     DEFAULT NULL COMMENT 'Only for siswa (student ID)',
  `role`              ENUM('siswa','operator','walikelas','manajer') NOT NULL DEFAULT 'siswa',
  `class`             VARCHAR(50)     DEFAULT NULL COMMENT 'e.g. XII RPL 1 (siswa only)',
  `phone`             VARCHAR(20)     DEFAULT NULL,
  `balance`           INT             NOT NULL DEFAULT 0 COMMENT 'Current balance in Rupiah',
  `points`            INT             NOT NULL DEFAULT 0 COMMENT 'Gamification points accumulated',
  `avatar`            VARCHAR(255)    DEFAULT NULL COMMENT 'Relative path to uploaded avatar',
  `status`            ENUM('approved','pending','rejected') NOT NULL DEFAULT 'pending',
  `email_verified_at` TIMESTAMP       DEFAULT NULL,
  `password`          VARCHAR(255)    NOT NULL,
  `remember_token`    VARCHAR(100)    DEFAULT NULL,
  `created_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nisn_unique` (`nisn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: waste_categories
-- Master data: categories of waste with pricing & points.
-- Managed by Manajer/Admin.
-- ============================================================
CREATE TABLE `waste_categories` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(100)    NOT NULL COMMENT 'e.g. Plastik, Kertas, Logam',
  `key`           VARCHAR(50)     NOT NULL COMMENT 'Slug identifier (e.g. plastik)',
  `price_per_kg`  INT             NOT NULL DEFAULT 0 COMMENT 'Price in Rupiah per kg',
  `points_per_kg` INT             NOT NULL DEFAULT 0 COMMENT 'Gamification points per kg',
  `icon`          VARCHAR(50)     NOT NULL DEFAULT 'leaf' COMMENT 'FontAwesome/Heroicon icon name',
  `created_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `waste_categories_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: transactions
-- Core transactional table.
-- type=setor: Waste deposit by siswa, processed by operator.
-- type=tarik: Balance withdrawal request by siswa.
-- ============================================================
CREATE TABLE `transactions` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          BIGINT UNSIGNED NOT NULL COMMENT 'FK → users.id (Siswa who deposits/withdraws)',
  `operator_id`      BIGINT UNSIGNED NOT NULL COMMENT 'FK → users.id (Operator who processes)',
  `type`             ENUM('setor','tarik') NOT NULL COMMENT 'setor=deposit, tarik=withdraw',
  `waste_category_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'FK → waste_categories.id (NULL for tarik)',
  `weight`           DECIMAL(8,2)    DEFAULT NULL COMMENT 'Weight in kg (only for setor)',
  `amount`           INT             NOT NULL DEFAULT 0 COMMENT 'Transaction value in Rupiah',
  `points`           INT             NOT NULL DEFAULT 0 COMMENT 'Points earned in this transaction',
  `status`           ENUM('Berhasil','Menunggu','Batal') NOT NULL DEFAULT 'Menunggu',
  `note`             VARCHAR(255)    DEFAULT NULL COMMENT 'Operator notes or withdrawal method',
  `created_at`       TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_index` (`user_id`),
  KEY `transactions_operator_id_index` (`operator_id`),
  KEY `transactions_waste_category_id_index` (`waste_category_id`),
  CONSTRAINT `transactions_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_operator_id_foreign`
    FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `transactions_waste_category_id_foreign`
    FOREIGN KEY (`waste_category_id`) REFERENCES `waste_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: sessions
-- Laravel session management (database driver).
-- ============================================================
CREATE TABLE `sessions` (
  `id`            VARCHAR(255)    NOT NULL,
  `user_id`       BIGINT UNSIGNED DEFAULT NULL,
  `ip_address`    VARCHAR(45)     DEFAULT NULL,
  `user_agent`    TEXT            DEFAULT NULL,
  `payload`       LONGTEXT        NOT NULL,
  `last_activity` INT             NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: password_reset_tokens
-- Laravel password reset tokens.
-- ============================================================
CREATE TABLE `password_reset_tokens` (
  `email`      VARCHAR(255) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP    DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: permissions  [Spatie RBAC]
-- ============================================================
CREATE TABLE `permissions` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(125)    NOT NULL,
  `guard_name` VARCHAR(125)    NOT NULL DEFAULT 'web',
  `created_at` TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: roles  [Spatie RBAC]
-- ============================================================
CREATE TABLE `roles` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(125)    NOT NULL,
  `guard_name` VARCHAR(125)    NOT NULL DEFAULT 'web',
  `created_at` TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: model_has_roles  [Spatie Pivot]
-- Links users → roles (polymorphic many-to-many)
-- ============================================================
CREATE TABLE `model_has_roles` (
  `role_id`    BIGINT UNSIGNED NOT NULL,
  `model_type` VARCHAR(255)    NOT NULL COMMENT 'e.g. App\Models\User',
  `model_id`   BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: model_has_permissions  [Spatie Pivot]
-- ============================================================
CREATE TABLE `model_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `model_type`    VARCHAR(255)    NOT NULL,
  `model_id`      BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: role_has_permissions  [Spatie Pivot]
-- ============================================================
CREATE TABLE `role_has_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id`       BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign`
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================
-- PLANNED TABLES (Sprint 3 & 4) — NOT YET IMPLEMENTED
-- ==============================================================

-- ============================================================
-- TABLE: distributions  [Sprint 3]
-- Records waste distribution events to external agents/units.
-- Created by Manajer. One distribution may link to many
-- transactions via a pivot table (distribution_transactions).
-- ============================================================
CREATE TABLE `distributions` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_date`   DATE            NOT NULL COMMENT 'Date of the distribution batch',
  `route`        ENUM('agent','unit') NOT NULL DEFAULT 'agent',
  `total_weight` DECIMAL(8,2)    NOT NULL DEFAULT 0.00 COMMENT 'Total kg distributed',
  `total_value`  INT             NOT NULL DEFAULT 0 COMMENT 'Total Rupiah received from agent',
  `agent_name`   VARCHAR(150)    DEFAULT NULL COMMENT 'Agent or unit name',
  `notes`        TEXT            DEFAULT NULL,
  `created_by`   BIGINT UNSIGNED NOT NULL COMMENT 'FK → users.id (Manajer)',
  `created_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `distributions_created_by_index` (`created_by`),
  CONSTRAINT `distributions_created_by_foreign`
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: audit_logs  [Sprint 4]
-- Immutable security log for sensitive actions.
-- (price changes, role changes, manual edits)
-- ============================================================
CREATE TABLE `audit_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL COMMENT 'FK → users.id (actor)',
  `action`      VARCHAR(100)    NOT NULL COMMENT 'e.g. UPDATE_PRICE, APPROVE_SISWA, DELETE_TX',
  `description` TEXT            NOT NULL COMMENT 'Detailed description of the change',
  `ip_address`  VARCHAR(45)     DEFAULT NULL,
  `user_agent`  VARCHAR(255)    DEFAULT NULL,
  `created_at`  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_action_index` (`action`),
  CONSTRAINT `audit_logs_user_id_foreign`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- ============================================================
