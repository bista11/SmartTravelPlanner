CREATE DATABASE IF NOT EXISTS `smart_travel_planner`;
USE `smart_travel_planner`;

CREATE TABLE IF NOT EXISTS `trips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `destination` varchar(150) NOT NULL,
  `country` varchar(100) NOT NULL,
  `travel_date` date DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_destination` (`destination`)
);

INSERT INTO `trips` (`id`, `destination`, `country`, `travel_date`, `budget`, `notes`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
	(1, 'Paris', 'France', '2026-05-15', 850.00, 'Quick city break, food and sights', 48.8566000, 2.3522000, '2026-03-29 20:08:16', '2026-03-29 20:09:05'),
	(2, 'Rome', 'Italy', '2026-06-10', 1200.00, 'History, food and lots of walking', 41.9028000, 12.4964000, '2026-03-29 20:08:16', '2026-03-29 20:09:12'),
	(3, 'New York City', 'USA', '2026-09-05', 2200.00, 'Busy trip, shopping and exploring', 40.7128000, -74.0060000, '2026-03-29 20:08:16', '2026-03-29 20:09:18'),
	(4, 'Tokyo', 'Japan', '2026-10-18', 3000.00, 'Culture, food and unique vibes', 35.6762000, 139.6503000, '2026-03-29 20:08:16', '2026-03-29 20:09:25'),
	(5, 'Barcelona', 'Spain', '2026-07-22', 1100.00, 'Beach, chill and nightlife', 41.3851000, 2.1734000, '2026-03-29 20:08:16', '2026-03-29 20:09:35'),
	(6, 'Dubai', 'UAE', '2026-12-01', 2500.00, 'Luxury feel with some adventure', 25.2048000, 55.2708000, '2026-03-29 20:08:16', '2026-03-29 20:09:37');