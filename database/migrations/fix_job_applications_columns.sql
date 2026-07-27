-- Run this SQL on the server database to add missing columns to job_applications.
-- The Laravel migration 2026_07_24_102939 has not been run yet.

ALTER TABLE `job_applications`
  ADD COLUMN `motivation` TEXT NULL AFTER `overall_status`,
  ADD COLUMN `work_motivation` TEXT NULL AFTER `motivation`,
  ADD COLUMN `current_salary` DECIMAL(15,2) NULL AFTER `work_motivation`,
  ADD COLUMN `expected_salary` DECIMAL(15,2) NULL AFTER `current_salary`,
  ADD COLUMN `expected_facilities` TEXT NULL AFTER `expected_salary`,
  ADD COLUMN `available_start_date` DATE NULL AFTER `expected_facilities`;
