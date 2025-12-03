-- Desain Database untuk Aplikasi Warung Kuncen
-- Nama Database: dbwarkun

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Struktur Tabel `produk`
--
CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `stok` int(11) NOT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Data Awal untuk Tabel `produk`
--
INSERT INTO `produk` (`nama_produk`, `deskripsi`, `harga`, `gambar`, `stok`) VALUES
('Soto Ayam', 'Soto ayam bening dengan suwiran ayam, soun, dan tauge.', 15000.00, 'soto1.jpg', 50),
('Ketoprak', 'Ketoprak dengan bumbu kacang, tahu, bihun, dan lontong.', 12000.00, 'ketoprak.jpg', 40),
('Pentol Pedas', 'Pentol daging sapi dengan bumbu pedas dan saus sambal.', 10000.00, 'pentol.jpg', 100),
('Donat Gula', 'Donat empuk dengan taburan gula halus.', 3000.00, 'donat.jpg', 80),
('Donat Salju', 'Donat kentang lembut dengan topping gula salju.', 3500.00, 'donatsalju.jpg', 80),
('Roti Tape', 'Roti manis dengan isian tape singkong fermentasi.', 2500.00, 'rotitape.jpg', 60);

--
-- Struktur Tabel `user`
--
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama_user` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `level` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Data Awal untuk Tabel `user` (Admin)
-- Password admin adalah 'admin' yang di-enkripsi MD5
--
INSERT INTO `user` (`nama_user`, `email`, `password`, `alamat`, `level`) VALUES
('Administrator', 'admin@warkun.com', '21232f297a57a5a743894a0e4a801fc3', 'Kantor Pusat', 'admin');

--
-- Struktur Tabel `pesanan`
--
CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `tanggal_pesan` datetime NOT NULL DEFAULT current_timestamp(),
  `total_harga` decimal(10,2) NOT NULL,
  `status_pesanan` enum('menunggu','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu',
  PRIMARY KEY (`id_pesanan`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Struktur Tabel `detail_pesanan`
--
CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_pesanan` (`id_pesanan`),
  KEY `id_produk` (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

--
-- Struktur untuk tabel `pesan_kontak`
--
CREATE TABLE `pesan_kontak` (
  `id_pesan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pengirim` varchar(255) NOT NULL,
  `email_pengirim` varchar(255) NOT NULL,
  `subjek_pesan` varchar(255) NOT NULL,
  `isi_pesan` text NOT NULL,
  `tanggal_kirim` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('baru','dibaca','dibalas') NOT NULL DEFAULT 'baru',
  `tanggal_balas` datetime DEFAULT NULL,
  `balasan` text DEFAULT NULL,
  PRIMARY KEY (`id_pesan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
