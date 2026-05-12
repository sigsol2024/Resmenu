-- Registration / transactional deliverability: hard-bounce suppression for OTP sends.
-- Run once on production DB. Application checks this table before sending registration OTP.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `email_delivery_suppressions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_sha256` char(64) NOT NULL,
  `reason` varchar(64) NOT NULL DEFAULT 'hard_bounce',
  `source` varchar(64) NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email_sha256` (`email_sha256`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
