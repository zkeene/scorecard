ALTER TABLE `performances`
	CHANGE COLUMN `denominator` `denominator` INT(11) UNSIGNED NULL AFTER `numerator`;

ALTER TABLE `metrics`
	ADD COLUMN `is_calculated_metric` TINYINT(1) UNSIGNED NULL AFTER `metric_def`;

update metrics set is_calculated_metric = 0 where 1=1;

ALTER TABLE `metrics`
	CHANGE COLUMN `is_calculated_metric` `is_calculated_metric` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `metric_def`;