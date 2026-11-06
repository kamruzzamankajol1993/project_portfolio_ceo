-- Run this patch only when importing the SQL dump directly instead of running Laravel migrations.
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'unread',
  `read_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_email_index` (`email`),
  KEY `contact_messages_status_index` (`status`),
  KEY `contact_messages_status_created_at_index` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`, `group`)
SELECT 'contact-messages.manage', 'web', NOW(), NOW(), 'Website Messages'
WHERE NOT EXISTS (
  SELECT 1 FROM `permissions` WHERE `name` = 'contact-messages.manage' AND `guard_name` = 'web'
);

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.`id`, r.`id`
FROM `permissions` p
JOIN `roles` r ON r.`guard_name` = 'web' AND r.`name` IN ('Admin', 'Super Admin')
WHERE p.`name` = 'contact-messages.manage' AND p.`guard_name` = 'web';
