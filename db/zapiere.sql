-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 05, 2026 at 04:52 PM
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `checkout_produk` (IN `p_id_pembeli` INT, IN `p_data_keranjang` JSON)   main_block: BEGIN
    DECLARE v_idx INT DEFAULT 0;
    DECLARE v_jml_jenis_barang_dipesan INT;
    DECLARE v_jumlah_beli INT;
    
    DECLARE v_id_produk INT;
    DECLARE v_stok INT;
    DECLARE v_harga INT;
    DECLARE v_id_penjual INT;
    DECLARE v_nama_produk VARCHAR(100);

    DECLARE v_total_tagihan INT DEFAULT 0;
    DECLARE v_saldo_pembeli INT;
    DECLARE v_id_pesanan INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        SELECT 'Gagal' AS status, 'Terjadi kesalahan sistem (Database Error).' AS pesan;
    END;

    START TRANSACTION;

    SELECT saldo INTO v_saldo_pembeli 
    FROM users 
    WHERE id_user = p_id_pembeli 
    FOR UPDATE;

    INSERT INTO pesanan (id_user, tanggal) VALUES (p_id_pembeli, NOW());
    SET v_id_pesanan = LAST_INSERT_ID();

    SET v_jml_jenis_barang_dipesan = JSON_LENGTH(p_data_keranjang);

    WHILE v_idx < v_jml_jenis_barang_dipesan DO
        
        SET v_id_produk = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_data_keranjang, CONCAT('$[', v_idx, '].id_produk'))) AS UNSIGNED);
        SET v_jumlah_beli = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_data_keranjang, CONCAT('$[', v_idx, '].jumlah'))) AS UNSIGNED);

        SET v_stok = NULL;
        SELECT stok, harga, id_user, nama 
        INTO v_stok, v_harga, v_id_penjual, v_nama_produk 
        FROM produk 
        WHERE id_produk = v_id_produk 
        FOR UPDATE;

        IF v_stok IS NULL THEN
            ROLLBACK;
            SELECT 'Gagal' AS status, CONCAT('Produk ID ', v_id_produk, ' tidak ditemukan!') AS pesan;
            LEAVE main_block;
            
        ELSEIF v_stok < v_jumlah_beli THEN
            ROLLBACK;
            SELECT 'Gagal' AS status, CONCAT('Stok ', v_nama_produk, ' tidak cukup! Sisa: ', v_stok) AS pesan;
            LEAVE main_block;
        END IF;

        SET v_total_tagihan = v_total_tagihan + (v_harga * v_jumlah_beli);

        UPDATE produk SET stok = stok - v_jumlah_beli WHERE id_produk = v_id_produk;

        UPDATE users SET saldo = saldo + (v_harga * v_jumlah_beli) WHERE id_user = v_id_penjual;

        INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah) 
        VALUES (v_id_pesanan, v_id_produk, v_jumlah_beli);

        SET v_idx = v_idx + 1;
    END WHILE;

    IF v_saldo_pembeli < v_total_tagihan THEN
        ROLLBACK;
        SELECT 'Gagal' AS status, CONCAT('Saldo tidak cukup! Total tagihan seluruh keranjang Rp ', FORMAT(v_total_tagihan, 0)) AS pesan;
        LEAVE main_block;
    END IF;

    UPDATE users SET saldo = saldo - v_total_tagihan WHERE id_user = p_id_pembeli;

    COMMIT;
    SELECT 'Berhasil' AS status, CONCAT('Berhasil checkout! Total belanja Rp ', FORMAT(v_total_tagihan, 0)) AS pesan;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `edit_produk` (IN `in_id_produk` INT, IN `in_nama` VARCHAR(100), IN `in_harga` INT, IN `in_stok` INT, IN `in_id_kategori` INT, IN `in_foto_barang` VARCHAR(255), IN `in_deskripsi` TEXT)   BEGIN
	UPDATE `produk`
	SET `nama`=in_nama, `harga`=in_harga, `stok`=in_stok, `id_kategori`=in_id_kategori, `foto_barang`=in_foto_barang, `deskripsi`=in_deskripsi
	WHERE `id_produk`=in_id_produk;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `hapus_produk` (IN `id_input` INT)   BEGIN
	DELETE FROM produk WHERE id_produk = id_input;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `posting_produk` (IN `in_nama` VARCHAR(100), IN `in_harga` INT, IN `in_stok` INT, IN `in_id_user` INT, IN `in_id_kategori` INT, IN `in_foto_barang` VARCHAR(255), IN `in_deskripsi` TEXT)   BEGIN
	DECLARE v_saldo INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION 
    BEGIN
        ROLLBACK;
        SELECT 'Gagal' AS status, 'Terjadi kesalahan sistem (Database Error).' AS pesan;
    END;

	START TRANSACTION;
    
	SELECT saldo INTO v_saldo FROM users WHERE id_user = in_id_user
	FOR UPDATE;
    
    IF v_saldo < (in_harga * 0.1) THEN
    	ROLLBACK;
        SELECT 'Gagal' AS status, 'Saldo tidak cukup untuk membayar pajak, minimal 10% dari harga barang!' AS pesan;
    ELSE
    	CALL tambah_produk(in_nama, in_harga, in_stok, in_id_user, in_id_kategori, in_foto_barang, in_deskripsi);
        UPDATE users SET saldo = saldo - (in_harga * 0.1) WHERE id_user = in_id_user;
        
       	COMMIT;
        SELECT 'Berhasil' AS status, 'Produk berhasil ditambahkan' AS pesan;
    END IF;
END$$

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

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_produk` (IN `in_nama` VARCHAR(100), IN `in_harga` INT, IN `in_stok` INT, IN `in_id_user` INT, IN `in_id_kategori` INT, IN `in_foto_barang` VARCHAR(255), IN `in_deskripsi` TEXT)   BEGIN
 	INSERT INTO produk (nama, harga, stok, id_user, id_kategori, foto_barang, deskripsi)
	VALUES (in_nama, in_harga, in_stok, in_id_user, in_id_kategori, in_foto_barang, in_deskripsi);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tampil_produk` ()   BEGIN
    SELECT p.*,
           u.nama AS penjual,
           k.nama AS kategori
    FROM produk p
    INNER JOIN users u ON p.id_user = u.id_user
    INNER JOIN kategori k ON p.id_kategori = k.id_kategori;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `topup_saldo` (IN `p_id_user` INT, IN `p_nominal` INT)   BEGIN
    DECLARE v_nama VARCHAR(100);

    START TRANSACTION;

    SELECT nama INTO v_nama FROM users WHERE id_user = p_id_user FOR UPDATE;

    IF v_nama IS NULL THEN
        ROLLBACK;
        SELECT 'Gagal' AS status, 'User tidak ditemukan!' AS pesan;
    ELSE

        UPDATE users SET saldo = saldo + p_nominal WHERE id_user = p_id_user;
        
        INSERT INTO log_aktifitas (id_user, keterangan, tgl_aktifitas) 
        VALUES (p_id_user, CONCAT('Top-up saldo Rp ', p_nominal), NOW());

        COMMIT;
        SELECT 'Berhasil' AS status, 'Top-up sukses!' AS pesan;
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
(3, 2, 4, 1),
(4, 3, 5, 1),
(5, 4, 9, 1),
(6, 4, 4, 4),
(16, 8, 5, 8),
(17, 8, 27, 1),
(19, 12, 6, 3),
(22, 16, 5, 3),
(23, 16, 7, 1),
(24, 17, 5, 1),
(25, 17, 15, 1),
(26, 17, 14, 1),
(27, 17, 17, 1),
(28, 17, 24, 1);

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
(7, 6, 'Membeli Produk: Monitor LG 24 Inch IPS (1 pcs)', '2026-06-05 01:30:32'),
(10, 2, 'Top-up saldo Rp 50000', '2026-06-05 17:39:19'),
(11, 7, 'Top-up saldo Rp 10000', '2026-06-05 17:47:28'),
(14, 4, 'Top-up saldo Rp 100000', '2026-06-05 19:37:39'),
(15, 4, 'Top-up saldo Rp 100000000', '2026-06-05 19:42:53'),
(18, 8, 'Top-up saldo Rp 500000', '2026-06-05 20:14:30'),
(32, 2, 'Menghapus Produk: Mouse Wireless Logitech G304', '2026-06-05 21:27:49'),
(33, 2, 'Memperbarui Informasi Produk: Lenovo Legion 5 Pro', '2026-06-05 21:28:17'),
(34, 2, 'Menambahkan Produk Baru: Monitor', '2026-06-05 21:29:33'),
(35, 4, 'Melakukan Pembelian Produk. ID Pesanan: 16', '2026-06-05 21:30:45'),
(36, 2, 'Memperbarui Informasi Produk: Monitor LG 24 Inch', '2026-06-05 21:30:45'),
(37, 3, 'Memperbarui Informasi Produk: Macbook Air M2 256GB Space Gray', '2026-06-05 21:30:45'),
(38, 2, 'Menambahkan Produk Baru: Monitor', '2026-06-05 22:56:37'),
(39, 4, 'Melakukan Pembelian Produk. ID Pesanan: 17', '2026-06-05 23:05:32'),
(40, 2, 'Memperbarui Informasi Produk: Monitor LG 24 Inch', '2026-06-05 23:05:32'),
(41, 3, 'Memperbarui Informasi Produk: Mouse Razer DeathAdder V3 Pro', '2026-06-05 23:05:32'),
(42, 2, 'Memperbarui Informasi Produk: Keyboard Mechanical Keychron K2', '2026-06-05 23:05:32'),
(43, 3, 'Memperbarui Informasi Produk: Powerbank Anker PowerCore 20000mAh', '2026-06-05 23:05:32'),
(44, 2, 'Memperbarui Informasi Produk: Kulkas Sharp 2 Pintu Inverter', '2026-06-05 23:05:32'),
(45, 2, 'Memperbarui Informasi Produk: Monitor LG 12 inch', '2026-06-05 23:06:19');

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
(2, 5, '2026-06-02 14:15:00'),
(3, 6, '2026-06-05 01:30:32'),
(4, 4, '2026-06-05 15:16:49'),
(8, 4, '2026-06-05 15:26:55'),
(12, 4, '2026-06-05 19:47:51'),
(16, 4, '2026-06-05 21:30:45'),
(17, 4, '2026-06-05 23:05:32');

--
-- Triggers `pesanan`
--
DELIMITER $$
CREATE TRIGGER `insert_pesanan` AFTER INSERT ON `pesanan` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (NEW.id_user,
        CONCAT('Melakukan Pembelian Produk. ID Pesanan: ', NEW.id_pesanan),
        NOW()
    );
END
$$
DELIMITER ;

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
(3, 3, 'iPhone 15 Pro Max', 20000000, 5, 2, 'default.png', 'iPhone 15 Pro Max kapasitas 256GB warna Natural Titanium. Ex garansi iBox, Battery Health 92%. Body mulus 98% selalu pakai case. True tone & Face ID on lancar jaya.'),
(4, 3, 'TWS Soundcore R50i', 200000, 96, 3, 'default.png', 'Earphone bluetooth TWS dari Anker Soundcore. Bass mantap, daya tahan baterai hingga 30 jam dengan casing. Cocok untuk olahraga atau commute harian. Segel!'),
(5, 2, 'Monitor LG 12 inch', 15000, 12, 1, '1780642805_b8515e4b80934702a7a7b01b00f8f6da.webp', 'Monitor LG 24 inch panel. Layar jernih, warna akurat cocok untuk desain maupun main game ringan. Minus pemakaian wajar, tidak ada dead pixel.'),
(6, 2, 'Lenovo Legion 5 Pro', 21500000, 5, 1, 'default.png', 'Laptop gaming andalan dengan RTX 4060 dan layar WQHD+ 165Hz. Cocok untuk hardcore gamer dan content creator.'),
(7, 3, 'Macbook Air M2 256GB Space Gray', 18500000, 14, 1, 'default.png', 'Laptop super tipis dan ringan dari Apple dengan chip M2. Baterai tahan seharian penuh untuk produktivitas maksimal.'),
(8, 2, 'PC Rakitan Core i5 12400F', 8500000, 2, 1, 'default.png', 'PC Rakitan siap pakai untuk gaming mid-range. Sudah terinstall Windows 11, aplikasi standar, dan garansi part 1 tahun.'),
(9, 3, 'SSD Samsung 980 PRO 1TB NVMe', 1800000, 29, 1, 'default.png', 'SSD PCIe 4.0 dengan kecepatan baca hingga 7000MB/s. Loading game jadi super cepat dan copy data hitungan detik.'),
(10, 2, 'Samsung Galaxy S24 Ultra 512GB', 21000000, 12, 2, 'default.png', 'Smartphone flagship dengan fitur Galaxy AI, kamera utama 200MP, frame titanium, dan S Pen bawaan.'),
(11, 3, 'iPad Pro M4 11-inch Wi-Fi 256GB', 19000000, 7, 2, 'default.png', 'Tablet paling mutakhir dari Apple dengan layar Ultra Retina XDR OLED dan chip M4 yang sangat bertenaga untuk render video.'),
(12, 2, 'Xiaomi 14 12/256GB', 12000000, 3, 2, 'default.png', 'Flagship berukuran compact dengan lensa Leica otentik. Performa ngebut dengan chipset Snapdragon 8 Gen 3 terbaru.'),
(13, 3, 'Poco X6 Pro 5G', 4500000, 45, 2, 'default.png', 'Ponsel mid-range killer. Menggunakan prosesor Dimensity 8300 Ultra, cocok banget buat gaming kompetitif tanpa frame drop.'),
(14, 2, 'Keyboard Mechanical Keychron K2', 1350000, 24, 3, 'default.png', 'Keyboard mechanical wireless layout 75%. Menggunakan switch Gateron Brown yang tactile namun tidak berisik.'),
(15, 3, 'Mouse Razer DeathAdder V3 Pro', 2200000, 9, 3, 'default.png', 'Mouse gaming wireless ultra ringan favorit atlet esports dunia. Menggunakan sensor optik presisi tinggi dan minim latensi.'),
(16, 2, 'Headset Gaming HyperX Cloud II', 1200000, 18, 3, 'default.png', 'Headset gaming legendaris dengan 7.1 surround sound. Earpad memory foam yang sangat nyaman dipakai berjam-jam.'),
(17, 3, 'Powerbank Anker PowerCore 20000mAh', 650000, 59, 3, 'default.png', 'Powerbank kapasitas besar dengan teknologi fast charging IQ. Port Type-C output tinggi, bisa untuk ngecas laptop darurat.'),
(18, 2, 'Kamera Mirrorless Canon EOS R50', 12500000, 4, 4, 'default.png', 'Kamera mirrorless ringkas dan ringan, sangat cocok untuk pemula dan vlogger. Sudah termasuk lensa kit 18-45mm.'),
(19, 3, 'Lensa Sony FE 50mm f/1.8', 3500000, 8, 4, 'default.png', 'Lensa fix wajib untuk pengguna sistem kamera Sony Full-Frame. Menghasilkan foto dengan efek bokeh yang mulus dan tajam.'),
(21, 3, 'Tripod Takara Rover 66', 450000, 35, 4, 'default.png', 'Tripod kokoh dengan bahan aluminium. Ballhead stabil dan salah satu kaki bisa dilepas untuk dijadikan monopod.'),
(22, 2, 'Smart TV Samsung 43 Inch 4K UHD', 4200000, 12, 5, 'default.png', 'TV pintar dengan resolusi 4K yang memanjakan mata. Mendukung Netflix, YouTube, dan integrasi ekosistem SmartThings.'),
(23, 3, 'AC Daikin Standard 1/2 PK', 3400000, 10, 5, 'default.png', 'AC hemat listrik, awet, dan cepat dingin. Sangat cocok untuk ukuran kamar tidur standar. Pemasangan mudah.'),
(24, 2, 'Kulkas Sharp 2 Pintu Inverter', 3800000, 7, 5, 'default.png', 'Kulkas ukuran sedang dengan teknologi Plasmacluster untuk membunuh bakteri, dan kompresor inverter yang sangat hemat energi.'),
(25, 3, 'Mesin Cuci LG Front Load 8kg', 5100000, 5, 5, 'default.png', 'Mesin cuci bukaan depan dengan teknologi AI DD. Pintar mendeteksi jenis kain agar mencuci lebih bersih namun tetap merawat pakaian.'),
(26, 2, 'Laptop Advan Work Pro', 100000, 50, 1, '1780641477_telur.png', 'Laptop baut lepas, engsel mangap'),
(27, 2, 'Laptop Advan Work Pro', 100000, 49, 1, 'default.png', 'Laptop baut lepas, engsel mangap'),
(28, 8, 'Laptop Advan WorkPro', 5000000, 10, 1, '1780665296_images.jpg', 'Laptop baut sering copot, engsel kendor, keyboard pada mati, speaker rusak'),
(30, 2, 'Monitor', 10000000, 100, 1, '1780669773_b8515e4b80934702a7a7b01b00f8f6da.webp', '--'),
(31, 2, 'Monitor', 1000000, 9, 1, '1780674997_b8515e4b80934702a7a7b01b00f8f6da.webp', '-');

--
-- Triggers `produk`
--
DELIMITER $$
CREATE TRIGGER `delete_produk` AFTER DELETE ON `produk` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (OLD.id_user,
        CONCAT('Menghapus Produk: ', OLD.nama),
        NOW()
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_produk` AFTER INSERT ON `produk` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (NEW.id_user,
        CONCAT('Menambahkan Produk Baru: ', NEW.nama),
        NOW()
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_produk` AFTER UPDATE ON `produk` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (NEW.id_user,
        CONCAT('Memperbarui Informasi Produk: ', NEW.nama),
        NOW()
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `produk_aksesoris`
--

CREATE TABLE `produk_aksesoris` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_aksesoris`
--

INSERT INTO `produk_aksesoris` (`nama_toko`, `kategori`, `id_produk`, `nama`, `harga`, `stok`) VALUES
('Gadget Murah', 'Aksesoris & Periferal', 4, 'TWS Soundcore R50i', 200000, 96),
('Toko Komputer Jaya', 'Aksesoris & Periferal', 14, 'Keyboard Mechanical Keychron K2', 1350000, 25),
('Gadget Murah', 'Aksesoris & Periferal', 15, 'Mouse Razer DeathAdder V3 Pro', 2200000, 10),
('Toko Komputer Jaya', 'Aksesoris & Periferal', 16, 'Headset Gaming HyperX Cloud II', 1200000, 18),
('Gadget Murah', 'Aksesoris & Periferal', 17, 'Powerbank Anker PowerCore 20000mAh', 650000, 60);

-- --------------------------------------------------------

--
-- Table structure for table `produk_handphone`
--

CREATE TABLE `produk_handphone` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_handphone`
--

INSERT INTO `produk_handphone` (`nama_toko`, `kategori`, `id_produk`, `nama`, `harga`, `stok`) VALUES
('Gadget Murah', 'Handphone & Tablet', 3, 'iPhone 15 Pro Max', 20000000, 5),
('Toko Komputer Jaya', 'Handphone & Tablet', 10, 'Samsung Galaxy S24 Ultra 512GB', 21000000, 12),
('Gadget Murah', 'Handphone & Tablet', 11, 'iPad Pro M4 11-inch Wi-Fi 256GB', 19000000, 7),
('Toko Komputer Jaya', 'Handphone & Tablet', 12, 'Xiaomi 14 12/256GB', 12000000, 3),
('Gadget Murah', 'Handphone & Tablet', 13, 'Poco X6 Pro 5G', 4500000, 45);

-- --------------------------------------------------------

--
-- Table structure for table `produk_kamera`
--

CREATE TABLE `produk_kamera` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_kamera`
--

INSERT INTO `produk_kamera` (`nama_toko`, `kategori`, `id_produk`, `nama`, `harga`, `stok`) VALUES
('Toko Komputer Jaya', 'Kamera & Fotografi', 18, 'Kamera Mirrorless Canon EOS R50', 12500000, 4),
('Gadget Murah', 'Kamera & Fotografi', 19, 'Lensa Sony FE 50mm f/1.8', 3500000, 8),
('Gadget Murah', 'Kamera & Fotografi', 21, 'Tripod Takara Rover 66', 450000, 35);

-- --------------------------------------------------------

--
-- Table structure for table `produk_komputer`
--

CREATE TABLE `produk_komputer` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_komputer`
--

INSERT INTO `produk_komputer` (`nama_toko`, `kategori`, `id_produk`, `nama`, `harga`, `stok`) VALUES
('Toko Komputer Jaya', 'Komputer & Laptop', 5, 'Monitor LG 24 Inch', 1500000, 13),
('Toko Komputer Jaya', 'Komputer & Laptop', 6, 'Lenovo Legion 5 Pro', 21500000, 5),
('Gadget Murah', 'Komputer & Laptop', 7, 'Macbook Air M2 256GB Space Gray', 18500000, 14),
('Toko Komputer Jaya', 'Komputer & Laptop', 8, 'PC Rakitan Core i5 12400F', 8500000, 2),
('Gadget Murah', 'Komputer & Laptop', 9, 'SSD Samsung 980 PRO 1TB NVMe', 1800000, 29),
('Toko Komputer Jaya', 'Komputer & Laptop', 26, 'Laptop Advan Work Pro', 100000, 50),
('Toko Komputer Jaya', 'Komputer & Laptop', 27, 'Laptop Advan Work Pro', 100000, 49),
('', 'Komputer & Laptop', 28, 'Laptop Advan WorkPro', 5000000, 10),
('Toko Komputer Jaya', 'Komputer & Laptop', 30, 'Monitor', 10000000, 100);

-- --------------------------------------------------------

--
-- Table structure for table `produk_prt`
--

CREATE TABLE `produk_prt` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_prt`
--

INSERT INTO `produk_prt` (`nama_toko`, `kategori`, `id_produk`, `nama`, `harga`, `stok`) VALUES
('Toko Komputer Jaya', 'Peralatan Rumah Tangga', 22, 'Smart TV Samsung 43 Inch 4K UHD', 4200000, 12),
('Gadget Murah', 'Peralatan Rumah Tangga', 23, 'AC Daikin Standard 1/2 PK', 3400000, 10),
('Toko Komputer Jaya', 'Peralatan Rumah Tangga', 24, 'Kulkas Sharp 2 Pintu Inverter', 3800000, 8),
('Gadget Murah', 'Peralatan Rumah Tangga', 25, 'Mesin Cuci LG Front Load 8kg', 5100000, 5);

-- --------------------------------------------------------

--
-- Table structure for table `ringkasan_produk`
--

CREATE TABLE `ringkasan_produk` (
  `id_produk` int NOT NULL DEFAULT '0',
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ringkasan_produk`
--

INSERT INTO `ringkasan_produk` (`id_produk`, `nama_produk`, `kategori`, `harga`, `stok`) VALUES
(5, 'Monitor LG 24 Inch', 'Komputer & Laptop', 1500000, 13),
(6, 'Lenovo Legion 5 Pro', 'Komputer & Laptop', 21500000, 5),
(7, 'Macbook Air M2 256GB Space Gray', 'Komputer & Laptop', 18500000, 14),
(8, 'PC Rakitan Core i5 12400F', 'Komputer & Laptop', 8500000, 2),
(9, 'SSD Samsung 980 PRO 1TB NVMe', 'Komputer & Laptop', 1800000, 29),
(26, 'Laptop Advan Work Pro', 'Komputer & Laptop', 100000, 50),
(27, 'Laptop Advan Work Pro', 'Komputer & Laptop', 100000, 49),
(28, 'Laptop Advan WorkPro', 'Komputer & Laptop', 5000000, 10),
(30, 'Monitor', 'Komputer & Laptop', 10000000, 100),
(3, 'iPhone 15 Pro Max', 'Handphone & Tablet', 20000000, 5),
(10, 'Samsung Galaxy S24 Ultra 512GB', 'Handphone & Tablet', 21000000, 12),
(11, 'iPad Pro M4 11-inch Wi-Fi 256GB', 'Handphone & Tablet', 19000000, 7),
(12, 'Xiaomi 14 12/256GB', 'Handphone & Tablet', 12000000, 3),
(13, 'Poco X6 Pro 5G', 'Handphone & Tablet', 4500000, 45),
(4, 'TWS Soundcore R50i', 'Aksesoris & Periferal', 200000, 96),
(14, 'Keyboard Mechanical Keychron K2', 'Aksesoris & Periferal', 1350000, 25),
(15, 'Mouse Razer DeathAdder V3 Pro', 'Aksesoris & Periferal', 2200000, 10),
(16, 'Headset Gaming HyperX Cloud II', 'Aksesoris & Periferal', 1200000, 18),
(17, 'Powerbank Anker PowerCore 20000mAh', 'Aksesoris & Periferal', 650000, 60),
(18, 'Kamera Mirrorless Canon EOS R50', 'Kamera & Fotografi', 12500000, 4),
(19, 'Lensa Sony FE 50mm f/1.8', 'Kamera & Fotografi', 3500000, 8),
(21, 'Tripod Takara Rover 66', 'Kamera & Fotografi', 450000, 35),
(22, 'Smart TV Samsung 43 Inch 4K UHD', 'Peralatan Rumah Tangga', 4200000, 12),
(23, 'AC Daikin Standard 1/2 PK', 'Peralatan Rumah Tangga', 3400000, 10),
(24, 'Kulkas Sharp 2 Pintu Inverter', 'Peralatan Rumah Tangga', 3800000, 8),
(25, 'Mesin Cuci LG Front Load 8kg', 'Peralatan Rumah Tangga', 5100000, 5);

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
(2, 'andi_komputer', 'Andi Komputer', 'pass123', 'penjual', 93200000, 'Toko Komputer Jaya'),
(3, 'ahmad_sobri', 'Ahmad Sobri', 'pass123', 'penjual', 25950000, 'Gadget Murah'),
(4, 'abdul_buyer', 'Abdul', 'pass123', 'pembeli', 1900000, ''),
(5, 'budi_buyer', 'Budi Santoso', 'pass123', 'pembeli', 500000, ''),
(6, 'bach', 'Bachtiar Nugraha', 'bachtiarX24', 'pembeli', 0, ''),
(7, 'rara', 'rara ya', '111111', 'pembeli', 10000, ''),
(8, 'kere', 'Penjual Kere', '123456', 'penjual', 0, '');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_log_aktifitas`
-- (See below for the actual view)
--
CREATE TABLE `v_log_aktifitas` (
`id_log` int
,`tgl_aktifitas` datetime
,`nama_pelaku` varchar(100)
,`role` enum('penjual','pembeli','admin')
,`keterangan` varchar(255)
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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_log_aktifitas`  AS SELECT `l`.`id_log` AS `id_log`, `l`.`tgl_aktifitas` AS `tgl_aktifitas`, coalesce(`u`.`nama`,'Sistem / Akun Dihapus') AS `nama_pelaku`, `u`.`role` AS `role`, `l`.`keterangan` AS `keterangan` FROM (`log_aktifitas` `l` left join `users` `u` on((`l`.`id_user` = `u`.`id_user`)))  ;

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
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `log_aktifitas`
--
ALTER TABLE `log_aktifitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
