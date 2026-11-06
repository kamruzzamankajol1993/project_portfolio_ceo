-- EntrepreneurWiki About Us / Contact Us dynamic pages patch
-- Safe to run after importing the supplied database dump. Existing edited pages are preserved.

INSERT IGNORE INTO `extra_pages`
(`page_key`, `title`, `slug`, `content`, `meta_title`, `meta_description`, `meta_keywords`, `meta_robots`, `canonical_url`, `is_published`, `created_at`, `updated_at`)
VALUES
('about', 'About Us', 'about-us', '<p>EntrepreneurWiki is an independent platform documenting entrepreneurs, founders, business leaders and the organisations they build.</p><p>Our goal is to make reliable, useful and clearly presented information easier to discover. We bring together professional stories, company information and sourced achievements in one accessible place.</p><p>We value accuracy, transparency and responsible contributions. Public profile information is reviewed before publication, and readers can contact our team with corrections or suggestions.</p>', 'About Us | EntrepreneurWiki', 'Learn about EntrepreneurWiki, our purpose and our approach to entrepreneur and business information.', NULL, 'index,follow', NULL, 1, NOW(), NOW()),
('contact', 'Contact Us', 'contact-us', '<p>We welcome questions, profile suggestions, corrections and partnership enquiries.</p><p>Use the contact details shown on this page to reach the EntrepreneurWiki team. For profile submissions, please create an account and follow the review process.</p>', 'Contact Us | EntrepreneurWiki', 'Contact the EntrepreneurWiki team for questions, corrections, profile suggestions and partnerships.', NULL, 'index,follow', NULL, 1, NOW(), NOW());

-- Give the Super Admin role all existing web permissions so its modules are visible.
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT `permissions`.`id`, `roles`.`id`
FROM `permissions`
CROSS JOIN `roles`
WHERE `permissions`.`guard_name` = 'web'
  AND `roles`.`guard_name` = 'web'
  AND `roles`.`name` = 'Super Admin';

-- Contact details are intentionally not fabricated. Enter them in Admin > Website > Site Settings.

-- Contact Us form inbox (run this patch when importing SQL directly instead of running migrations).
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
