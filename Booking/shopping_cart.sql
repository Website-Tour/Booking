CREATE DATABASE IF NOT EXISTS shopping_cart;
USE shopping_cart;

-- Create tbl_tour table
CREATE TABLE IF NOT EXISTS `tour` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `tourid` varchar(250) NOT NULL,
  `image` text NOT NULL,
  `numbers` int(11) NOT NULL DEFAULT 0,
  `date` date NOT NULL,
  `price` double(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data into tbl_tour table
INSERT INTO `tour` (`name`, `tourid`, `image`, `numbers`, `date`, `price`) VALUES
('Tour A', 'TOUR001', 'tour-images/halongbay.jpg', 50, '2024-04-01', 200),
('Tour B', 'TOUR002', 'tour-images/halongbay4.jpg', 40, '2024-04-05', 120),
('Tour C', 'TOUR003', 'tour-images/halongbay2.jpg', 30, '2024-04-10', 150),
('Tour D', 'TOUR004', 'tour-images/halongbay3.jpg', 20, '2024-04-15', 200),
('Tour E', 'TOUR005', 'tour-images/halongbay5.jpg', 10, '2024-04-20', 250);

-- Create tbl_cart table
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`tour_id`) REFERENCES `tour`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
