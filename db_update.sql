ALTER TABLE `service_lines`
	ADD COLUMN `is_period_based` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `service_line`;
ALTER TABLE `performances`
	ADD COLUMN `period_performance` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `import_error`;