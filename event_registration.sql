-- Event Registration Module Database Tables
-- Drupal 10 Custom Module
-- Created: 2026-01-28

-- Table structure for table `event_configuration`

CREATE TABLE IF NOT EXISTS `event_configuration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Event ID',
  `event_name` varchar(255) NOT NULL COMMENT 'Name of the event',
  `event_category` varchar(100) NOT NULL COMMENT 'Category of the event',
  `event_date` varchar(20) NOT NULL COMMENT 'Date of the event',
  `registration_start_date` varchar(20) NOT NULL COMMENT 'Registration start date',
  `registration_end_date` varchar(20) NOT NULL COMMENT 'Registration end date',
  `created` int(11) NOT NULL DEFAULT 0 COMMENT 'Timestamp when the event was created',
  PRIMARY KEY (`id`),
  KEY `event_category` (`event_category`),
  KEY `event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores event configuration data';

-- Table structure for table `event_registration`

CREATE TABLE IF NOT EXISTS `event_registration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Registration ID',
  `full_name` varchar(255) NOT NULL COMMENT 'Full name of the registrant',
  `email` varchar(255) NOT NULL COMMENT 'Email address of the registrant',
  `college_name` varchar(255) NOT NULL COMMENT 'College name',
  `department` varchar(255) NOT NULL COMMENT 'Department',
  `event_category` varchar(100) NOT NULL COMMENT 'Category of the event',
  `event_date` varchar(20) NOT NULL COMMENT 'Date of the event',
  `event_id` int(11) NOT NULL COMMENT 'Foreign key to event_configuration table',
  `created` int(11) NOT NULL DEFAULT 0 COMMENT 'Timestamp when the registration was created',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_event_date` (`email`, `event_date`),
  KEY `email` (`email`),
  KEY `event_id` (`event_id`),
  KEY `event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores event registration data';

-- Add foreign key constraint
-- Note: Uncomment the following if your database supports foreign keys
-- ALTER TABLE `event_registration`
--   ADD CONSTRAINT `fk_event_configuration` 
--   FOREIGN KEY (`event_id`) 
--   REFERENCES `event_configuration` (`id`) 
--   ON DELETE CASCADE 
--   ON UPDATE CASCADE;

-- Sample data for testing (optional)
-- INSERT INTO `event_configuration` (`event_name`, `event_category`, `event_date`, `registration_start_date`, `registration_end_date`, `created`) VALUES
-- ('Web Development Workshop', 'Online Workshop', '2026-02-15', '2026-01-20', '2026-02-10', UNIX_TIMESTAMP()),
-- ('AI Hackathon 2026', 'Hackathon', '2026-03-01', '2026-02-01', '2026-02-25', UNIX_TIMESTAMP()),
-- ('Tech Conference 2026', 'Conference', '2026-03-20', '2026-02-15', '2026-03-15', UNIX_TIMESTAMP());
