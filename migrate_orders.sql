-- Fix the typo in orders table and add new columns
ALTER TABLE orders 
  CHANGE `otal_amount` `total_amount` DECIMAL(10,2) DEFAULT 0.00,
  ADD COLUMN `transaction_code` VARCHAR(20) AFTER `id`,
  ADD COLUMN `notes` TEXT NULL AFTER `status`;

-- Create order_items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `unit` VARCHAR(20) DEFAULT 'piece',
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
