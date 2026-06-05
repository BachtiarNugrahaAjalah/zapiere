-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 05, 2026 at 05:11 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zapiere`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `p_register_user` (IN `in_username` VARCHAR(50), IN `in_nama` VARCHAR(100), IN `in_password` VARCHAR(255), IN `in_role` ENUM('admin','penjual','pembeli'))   BEGIN
    DECLARE user_exists INT;
    
    -- Cek apakah username sudah dipakai
    SELECT COUNT(*) INTO user_exists FROM users WHERE username = in_username;
    
    IF user_exists > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Username sudah terdaftar!';
    ELSE
        INSERT INTO users (username, nama, password, role, saldo)
        VALUES (in_username, in_nama, in_password, in_role, 0);
    END IF;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `f_cek_stok_tersedia` (`p_id_produk` INT) RETURNS TINYINT(1) READS SQL DATA BEGIN
    DECLARE v_stok INT DEFAULT 0;
    SELECT stok INTO v_stok FROM produk WHERE id_produk = p_id_produk;
    RETURN IF(v_stok > 0, 1, 0);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_daftar_produk_pesanan` (`p_id_pesanan` INT) RETURNS TEXT CHARSET utf8mb4 READS SQL DATA BEGIN
    DECLARE v_list TEXT;
    SELECT GROUP_CONCAT(CONCAT(p.nama, ' x', dp.jumlah) SEPARATOR ', ') INTO v_list
    FROM detail_pesanan dp JOIN produk p ON dp.id_produk = p.id_produk
    WHERE dp.id_pesanan = p_id_pesanan;
    RETURN v_list;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_format_rupiah` (`nominal` DECIMAL(15,2)) RETURNS VARCHAR(50) CHARSET utf8mb4 DETERMINISTIC BEGIN
    RETURN CONCAT('Rp ', REPLACE(FORMAT(nominal, 0), ',', '.'));
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_jumlah_produk_terjual` (`p_id_produk` INT) RETURNS INT READS SQL DATA BEGIN
    DECLARE v_terjual INT DEFAULT 0;
    SELECT COALESCE(SUM(jumlah), 0) INTO v_terjual
    FROM detail_pesanan WHERE id_produk = p_id_produk;
    RETURN v_terjual;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_jumlah_produk_toko` (`p_id_user` INT) RETURNS INT READS SQL DATA BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(id_produk) INTO v_count FROM produk WHERE id_user = p_id_user;
    RETURN v_count;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_total_bayar_pesanan` (`p_id_pesanan` INT) RETURNS INT READS SQL DATA BEGIN
    DECLARE v_total INT DEFAULT 0;
    SELECT COALESCE(SUM(dp.jumlah * p.harga), 0) INTO v_total
    FROM detail_pesanan dp JOIN produk p ON dp.id_produk = p.id_produk
    WHERE dp.id_pesanan = p_id_pesanan;
    RETURN v_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_total_belanja` (`p_id_user` INT) RETURNS INT READS SQL DATA BEGIN
    DECLARE v_total INT DEFAULT 0;
    SELECT COALESCE(SUM(dp.jumlah * p.harga), 0) INTO v_total
    FROM pesanan ps
    JOIN detail_pesanan dp ON ps.id_pesanan = dp.id_pesanan
    JOIN produk p ON dp.id_produk = p.id_produk
    WHERE ps.id_user = p_id_user;
    RETURN v_total;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_total_item_pesanan` (`p_id_pesanan` INT) RETURNS INT READS SQL DATA BEGIN
    DECLARE v_item INT DEFAULT 0;
    SELECT COUNT(id_detail) INTO v_item FROM detail_pesanan WHERE id_pesanan = p_id_pesanan;
    RETURN v_item;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `f_total_omzet_penjual` (`p_id_user` INT) RETURNS INT READS SQL DATA BEGIN
    DECLARE v_total INT DEFAULT 0;
    SELECT COALESCE(SUM(dp.jumlah * p.harga), 0) INTO v_total
    FROM detail_pesanan dp
    JOIN produk p ON dp.id_produk = p.id_produk
    JOIN pesanan ps ON dp.id_pesanan = ps.id_pesanan
    WHERE p.id_user = p_id_user;
    RETURN v_total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int NOT NULL,
  `id_pesanan` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `jumlah` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 2),
(3, 2, 4, 1),
(4, 3, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama`) VALUES
(1, 'Komputer & Laptop'),
(2, 'Handphone & Tablet'),
(3, 'Aksesoris & Periferal'),
(4, 'Kamera & Fotografi'),
(5, 'Peralatan Rumah Tangga');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktifitas`
--

CREATE TABLE `log_aktifitas` (
  `id_log` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `keterangan` varchar(255) NOT NULL,
  `tgl_aktifitas` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `log_aktifitas`
--

INSERT INTO `log_aktifitas` (`id_log`, `id_user`, `keterangan`, `tgl_aktifitas`) VALUES
(1, 2, 'Menambahkan Produk Baru: Laptop Asus ROG Strix', '2026-05-30 09:00:00'),
(2, 2, 'Menambahkan Produk Baru: Mouse Wireless Logitech G304', '2026-05-30 09:10:00'),
(3, 3, 'Menambahkan Produk Baru: iPhone 15 Pro Max', '2026-05-31 10:00:00'),
(4, 4, 'Membeli Produk: Laptop Asus ROG Strix (1 pcs)', '2026-06-01 10:30:00'),
(5, 4, 'Membeli Produk: Mouse Wireless Logitech G304 (2 pcs)', '2026-06-01 10:30:00'),
(6, 5, 'Membeli Produk: TWS Soundcore R50i (1 pcs)', '2026-06-02 14:15:00'),
(7, 6, 'Membeli Produk: Monitor LG 24 Inch IPS (1 pcs)', '2026-06-05 01:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_user`, `tanggal`) VALUES
(1, 4, '2026-06-01 10:30:00'),
(2, 5, '2026-06-02 14:15:00'),
(3, 6, '2026-06-05 01:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `foto_barang` varchar(255) DEFAULT 'default.png',
  `deskripsi` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_user`, `nama`, `harga`, `stok`, `id_kategori`, `foto_barang`, `deskripsi`) VALUES
(1, 2, 'Laptop Asus ROG Strix', 15000000, 10, 1, 'image.png', 'Laptop gaming Asus ROG Strix G15. Pemakaian baru 3 bulan, mulus no minus. Spesifikasi gahar siap rata kanan untuk semua game e-sports. Kelengkapan fullset (Box, Charger ori, Tas ROG).'),
(2, 2, 'Mouse Wireless Logitech G304', 450000, 50, 3, 'image.png', 'Mouse gaming wireless dengan sensor HERO. Kecepatan respons 1ms. Daya tahan baterai sangat lama hingga 250 jam. Barang 100% baru dan original, garansi resmi Logitech Indonesia.'),
(3, 3, 'iPhone 15 Pro Max', 20000000, 5, 2, 'image.png', 'iPhone 15 Pro Max kapasitas 256GB warna Natural Titanium. Ex garansi iBox, Battery Health 92%. Body mulus 98% selalu pakai case. True tone & Face ID on lancar jaya.'),
(4, 3, 'TWS Soundcore R50i', 200000, 100, 3, 'image.png', 'Earphone bluetooth TWS dari Anker Soundcore. Bass mantap, daya tahan baterai hingga 30 jam dengan casing. Cocok untuk olahraga atau commute harian. Segel!'),
(5, 2, 'Monitor LG 24 Inch IPS', 1500000, 14, 1, 'image.png', 'Monitor LG 24 inch panel IPS. Layar jernih, warna akurat cocok untuk desain maupun main game ringan. Minus pemakaian wajar, tidak ada dead pixel.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('penjual','pembeli','admin') NOT NULL,
  `saldo` int DEFAULT '0',
  `nama_toko` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `nama`, `password`, `role`, `saldo`, `nama_toko`) VALUES
(1, 'admin_zapiere', 'Admin Zapiere', 'admin123', 'admin', 0, ''),
(2, 'andi_komputer', 'Andi Komputer', 'pass123', 'penjual', 5000000, 'Toko Komputer Jaya'),
(3, 'ahmad_sobri', 'Ahmad Sobri', 'pass123', 'penjual', 2000000, 'Gadget Murah'),
(4, 'abdul_buyer', 'Abdul', 'pass123', 'pembeli', 15000000, ''),
(5, 'budi_buyer', 'Budi Santoso', 'pass123', 'pembeli', 500000, ''),
(6, 'bach', 'Bachtiar Nugraha', 'bachtiarX24', 'pembeli', 0, '');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_log_aktifitas`
-- (See below for the actual view)
--
CREATE TABLE `v_log_aktifitas` (
`id_log` int
,`id_user` int
,`keterangan` varchar(255)
,`tgl_aktifitas` datetime
,`nama` varchar(100)
,`role` enum('penjual','pembeli','admin')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_penjualan_detail`
-- (See below for the actual view)
--
CREATE TABLE `v_penjualan_detail` (
`id_pesanan` int
,`tanggal` datetime
,`pembeli` varchar(100)
,`produk` varchar(100)
,`id_user_penjual` int
,`jumlah` int
,`subtotal` bigint
,`subtotal_rp` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_pesanan_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `v_pesanan_lengkap` (
`id_pesanan` int
,`id_user` int
,`tanggal` datetime
,`pembeli` varchar(100)
,`total_item` int
,`total_bayar` int
,`total_bayar_rp` varchar(50)
,`produk` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_produk_lengkap`
-- (See below for the actual view)
--
CREATE TABLE `v_produk_lengkap` (
`id_produk` int
,`id_user` int
,`nama` varchar(100)
,`harga` int
,`harga_rp` varchar(50)
,`stok` int
,`is_tersedia` tinyint(1)
,`id_kategori` int
,`foto_barang` varchar(255)
,`kategori` varchar(100)
,`penjual` varchar(100)
,`total_terjual` int
,`omzet` bigint
,`omzet_rp` varchar(50)
);

-- --------------------------------------------------------

--
-- Structure for view `v_log_aktifitas`
--
DROP TABLE IF EXISTS `v_log_aktifitas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_log_aktifitas`  AS SELECT `la`.`id_log` AS `id_log`, `la`.`id_user` AS `id_user`, `la`.`keterangan` AS `keterangan`, `la`.`tgl_aktifitas` AS `tgl_aktifitas`, `u`.`nama` AS `nama`, `u`.`role` AS `role` FROM (`log_aktifitas` `la` left join `users` `u` on((`u`.`id_user` = `la`.`id_user`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `v_penjualan_detail`
--
DROP TABLE IF EXISTS `v_penjualan_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_penjualan_detail`  AS SELECT `ps`.`id_pesanan` AS `id_pesanan`, `ps`.`tanggal` AS `tanggal`, `buyer`.`nama` AS `pembeli`, `p`.`nama` AS `produk`, `p`.`id_user` AS `id_user_penjual`, `dp`.`jumlah` AS `jumlah`, (`dp`.`jumlah` * `p`.`harga`) AS `subtotal`, `f_format_rupiah`((`dp`.`jumlah` * `p`.`harga`)) AS `subtotal_rp` FROM (((`detail_pesanan` `dp` join `produk` `p` on((`p`.`id_produk` = `dp`.`id_produk`))) join `pesanan` `ps` on((`ps`.`id_pesanan` = `dp`.`id_pesanan`))) join `users` `buyer` on((`buyer`.`id_user` = `ps`.`id_user`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `v_pesanan_lengkap`
--
DROP TABLE IF EXISTS `v_pesanan_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pesanan_lengkap`  AS SELECT `ps`.`id_pesanan` AS `id_pesanan`, `ps`.`id_user` AS `id_user`, `ps`.`tanggal` AS `tanggal`, `u`.`nama` AS `pembeli`, `f_total_item_pesanan`(`ps`.`id_pesanan`) AS `total_item`, `f_total_bayar_pesanan`(`ps`.`id_pesanan`) AS `total_bayar`, `f_format_rupiah`(`f_total_bayar_pesanan`(`ps`.`id_pesanan`)) AS `total_bayar_rp`, `f_daftar_produk_pesanan`(`ps`.`id_pesanan`) AS `produk` FROM (`pesanan` `ps` left join `users` `u` on((`u`.`id_user` = `ps`.`id_user`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `v_produk_lengkap`
--
DROP TABLE IF EXISTS `v_produk_lengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_produk_lengkap`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`id_user` AS `id_user`, `p`.`nama` AS `nama`, `p`.`harga` AS `harga`, `f_format_rupiah`(`p`.`harga`) AS `harga_rp`, `p`.`stok` AS `stok`, `f_cek_stok_tersedia`(`p`.`id_produk`) AS `is_tersedia`, `p`.`id_kategori` AS `id_kategori`, `p`.`foto_barang` AS `foto_barang`, `k`.`nama` AS `kategori`, `u`.`nama` AS `penjual`, `f_jumlah_produk_terjual`(`p`.`id_produk`) AS `total_terjual`, (`f_jumlah_produk_terjual`(`p`.`id_produk`) * `p`.`harga`) AS `omzet`, `f_format_rupiah`((`f_jumlah_produk_terjual`(`p`.`id_produk`) * `p`.`harga`)) AS `omzet_rp` FROM ((`produk` `p` left join `kategori` `k` on((`k`.`id_kategori` = `p`.`id_kategori`))) left join `users` `u` on((`u`.`id_user` = `p`.`id_user`)))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_detail_pesanan` (`id_pesanan`),
  ADD KEY `fk_detail_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `log_aktifitas`
--
ALTER TABLE `log_aktifitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `fk_log_user` (`id_user`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `fk_pesanan_user` (`id_user`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `fk_produk_kategori` (`id_kategori`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `log_aktifitas`
--
ALTER TABLE `log_aktifitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `log_aktifitas`
--
ALTER TABLE `log_aktifitas`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
