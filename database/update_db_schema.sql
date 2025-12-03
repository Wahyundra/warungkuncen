-- SQL script to update database schema for shop management

-- Create 'toko' table
CREATE TABLE IF NOT EXISTS `toko` (
  `id_toko` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_toko` VARCHAR(255) NOT NULL,
  `deskripsi_toko` TEXT NOT NULL,
  `lokasi_toko` VARCHAR(255) NOT NULL,
  `gambar_toko` VARCHAR(255) DEFAULT NULL,
  `status_toko` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rating_toko` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  PRIMARY KEY (`id_toko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add 'id_toko' column to 'produk' table if it doesn't exist
-- And set it as a foreign key
ALTER TABLE `produk`
ADD COLUMN `id_toko` INT(11) DEFAULT NULL,
ADD CONSTRAINT `fk_produk_toko` FOREIGN KEY (`id_toko`) REFERENCES `toko` (`id_toko`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Update existing products to be associated with a default 'Warung Kuncen' shop
-- First, create a default shop if it doesn't exist
INSERT IGNORE INTO `toko` (`id_toko`, `nama_toko`, `deskripsi_toko`, `lokasi_toko`, `status_toko`, `rating_toko`)
VALUES (1, 'Warung Kuncen', 'Menyajikan Cita Rasa Otentik Sejak 2023. Dibuat dari bahan-bahan segar dan resep turun-temurun untuk menjaga kualitas rasa asli kuliner nusantara.', 'Jl. 24 Purwasaba, Mandiraja', 'approved', 4.5);

-- Update existing products to link to the default 'Warung Kuncen' shop
UPDATE `produk` SET `id_toko` = 1 WHERE `id_toko` IS NULL;

-- Create 'shop_products' table to store products for each shop
-- This is an alternative to adding id_toko to produk table, but for now, we will stick to id_toko in produk table.
-- This is just a comment to show the thought process.

-- Update the 'user' table to include a 'toko_id' for shop owners
-- This is for future use if we want to link a user to a shop they own.
-- ALTER TABLE `user`
-- ADD COLUMN `toko_id` INT(11) DEFAULT NULL,
-- ADD CONSTRAINT `fk_user_toko` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id_toko`) ON DELETE SET NULL ON UPDATE CASCADE;
