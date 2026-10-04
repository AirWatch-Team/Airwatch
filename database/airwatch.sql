-- ====================================================================
-- AIRWATCH DATABASE SCHEMA & DEMO DATA
-- Web-Based Air Quality Data Collection, Monitoring and Analysis System
-- Compatible with MySQL 5.7+ / MySQL 8.0+ / MariaDB / XAMPP / WAMP
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `airwatch` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `airwatch`;

-- --------------------------------------------------------------------
-- Table structure for `air_quality_records`
-- --------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `air_quality_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `location` VARCHAR(100) NOT NULL,
  `record_date` DATE NOT NULL,
  `record_time` TIME NOT NULL DEFAULT '12:00:00',
  `aqi` INT UNSIGNED NOT NULL,
  `pm25` DECIMAL(6,2) NOT NULL COMMENT 'PM2.5 in µg/m³',
  `pm10` DECIMAL(6,2) NOT NULL COMMENT 'PM10 in µg/m³',
  `temperature` DECIMAL(4,1) NULL DEFAULT NULL COMMENT 'Temperature in °C',
  `humidity` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Relative Humidity in %',
  `pollution_source` VARCHAR(50) NOT NULL DEFAULT 'Other',
  `notes` TEXT NULL DEFAULT NULL,
  `aqi_status` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_location` (`location`),
  INDEX `idx_record_date` (`record_date`),
  INDEX `idx_aqi` (`aqi`),
  INDEX `idx_pollution_source` (`pollution_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Initial Sample / Demo Data (25 Synthetic Measurement Records)
-- Clearly labeled synthetic dataset for demonstration purposes.
-- --------------------------------------------------------------------

INSERT INTO `air_quality_records` 
(`location`, `record_date`, `record_time`, `aqi`, `pm25`, `pm10`, `temperature`, `humidity`, `pollution_source`, `notes`, `aqi_status`) 
VALUES
('Main Road', '2026-10-01', '08:30:00', 165, 84.50, 142.10, 28.5, 62.00, 'Vehicle Traffic', 'Heavy morning rush hour traffic and diesel exhaust.', 'Unhealthy'),
('College Campus', '2026-10-01', '09:15:00', 42, 11.20, 24.80, 27.0, 58.00, 'Other', 'Clean air near tree grove.', 'Good'),
('Bus Stand', '2026-10-01', '10:00:00', 148, 72.30, 128.90, 29.0, 60.00, 'Vehicle Traffic', 'Idling buses and high pedestrian activity.', 'Unhealthy for Sensitive Groups'),
('Market Yard', '2026-10-01', '11:45:00', 112, 52.80, 98.40, 31.2, 52.00, 'Dust', 'Loading goods, unpaved dust suspension.', 'Unhealthy for Sensitive Groups'),
('Residential Area', '2026-10-01', '18:00:00', 58, 16.40, 35.10, 26.5, 65.00, 'Waste Burning', 'Minor localized smoke from leaves.', 'Moderate'),

('Main Road', '2026-10-02', '08:45:00', 178, 92.10, 158.40, 29.1, 59.00, 'Vehicle Traffic', 'Congested traffic flow.', 'Unhealthy'),
('College Campus', '2026-10-02', '12:30:00', 38, 9.80, 21.30, 30.5, 50.00, 'Other', 'Clear sky and low traffic.', 'Good'),
('Industrial Zone', '2026-10-02', '14:00:00', 215, 125.60, 210.50, 33.0, 45.00, 'Industrial Smoke', 'Factory emission plume observed.', 'Very Unhealthy'),
('Market Yard', '2026-10-02', '16:15:00', 125, 59.40, 105.00, 32.0, 48.00, 'Dust', 'High wind sweeping street dust.', 'Unhealthy for Sensitive Groups'),
('Residential Area', '2026-10-02', '20:00:00', 64, 19.50, 39.80, 25.0, 70.00, 'Other', 'Normal ambient evening level.', 'Moderate'),

('Main Road', '2026-10-03', '09:00:00', 152, 78.40, 136.20, 28.0, 64.00, 'Vehicle Traffic', 'Traffic emission peak.', 'Unhealthy'),
('College Campus', '2026-10-03', '11:00:00', 45, 12.50, 26.00, 29.0, 55.00, 'Other', 'Moderate green zone reading.', 'Good'),
('Bus Stand', '2026-10-03', '13:30:00', 135, 66.00, 118.50, 31.8, 51.00, 'Vehicle Traffic', 'Interstate bus movement.', 'Unhealthy for Sensitive Groups'),
('Construction Site B', '2026-10-03', '15:00:00', 185, 98.20, 172.00, 32.5, 46.00, 'Construction', 'Excavation and cement mixing without water spraying.', 'Unhealthy'),
('Residential Area', '2026-10-03', '19:30:00', 52, 14.80, 31.00, 26.0, 68.00, 'Other', 'Favorable evening dispersion.', 'Moderate'),

('Main Road', '2026-10-04', '08:15:00', 160, 81.00, 139.00, 27.5, 66.00, 'Vehicle Traffic', 'Morning rush hours.', 'Unhealthy'),
('College Campus', '2026-10-04', '10:30:00', 35, 8.50, 19.20, 28.2, 57.00, 'Other', 'Excellent ambient quality.', 'Good'),
('Bus Stand', '2026-10-04', '12:00:00', 140, 68.50, 122.00, 30.0, 54.00, 'Vehicle Traffic', 'High bus frequency.', 'Unhealthy for Sensitive Groups'),
('Industrial Zone', '2026-10-04', '14:45:00', 230, 140.00, 235.00, 34.0, 42.00, 'Industrial Smoke', 'High stack plume activity.', 'Very Unhealthy'),
('Construction Site B', '2026-10-04', '16:30:00', 170, 89.00, 155.00, 31.5, 49.00, 'Construction', 'Demolition work airborne dust.', 'Unhealthy'),

('Market Yard', '2026-10-04', '17:45:00', 105, 48.00, 92.00, 30.0, 53.00, 'Waste Burning', 'Open waste combustion nearby.', 'Unhealthy for Sensitive Groups'),
('Residential Area', '2026-10-04', '20:15:00', 48, 13.00, 28.00, 24.5, 72.00, 'Other', 'Low human activity.', 'Good'),
('Main Road', '2026-10-04', '21:00:00', 130, 62.00, 110.00, 26.0, 69.00, 'Vehicle Traffic', 'Late evening freight trucks.', 'Unhealthy for Sensitive Groups'),
('College Campus', '2026-10-04', '22:00:00', 30, 7.00, 16.00, 24.0, 75.00, 'Other', 'Night calm conditions.', 'Good'),
('Industrial Zone', '2026-10-04', '23:00:00', 195, 108.00, 185.00, 25.5, 71.00, 'Industrial Smoke', 'Night thermal inversion trapping smoke.', 'Unhealthy');
