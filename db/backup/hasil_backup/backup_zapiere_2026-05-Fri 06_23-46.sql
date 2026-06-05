-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: zapiere
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `detail_pesanan`
--

DROP TABLE IF EXISTS `detail_pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_pesanan` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_pesanan` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `fk_detail_pesanan` (`id_pesanan`),
  KEY `fk_detail_produk` (`id_produk`),
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_pesanan`
--

LOCK TABLES `detail_pesanan` WRITE;
/*!40000 ALTER TABLE `detail_pesanan` DISABLE KEYS */;
INSERT INTO `detail_pesanan` VALUES (3,2,4,1),(4,3,5,1),(5,4,9,1),(6,4,4,4),(16,8,5,8),(17,8,27,1),(19,12,6,3),(22,16,5,3),(23,16,7,1),(24,17,5,1),(25,17,15,1),(26,17,14,1),(27,17,17,1),(28,17,24,1);
/*!40000 ALTER TABLE `detail_pesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (1,'Komputer & Laptop'),(2,'Handphone & Tablet'),(3,'Aksesoris & Periferal'),(4,'Kamera & Fotografi'),(5,'Peralatan Rumah Tangga');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_aktifitas`
--

DROP TABLE IF EXISTS `log_aktifitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_aktifitas` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `keterangan` varchar(255) NOT NULL,
  `tgl_aktifitas` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `fk_log_user` (`id_user`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_aktifitas`
--

LOCK TABLES `log_aktifitas` WRITE;
/*!40000 ALTER TABLE `log_aktifitas` DISABLE KEYS */;
INSERT INTO `log_aktifitas` VALUES (1,2,'Menambahkan Produk Baru: Laptop Asus ROG Strix','2026-05-30 09:00:00'),(2,2,'Menambahkan Produk Baru: Mouse Wireless Logitech G304','2026-05-30 09:10:00'),(3,3,'Menambahkan Produk Baru: iPhone 15 Pro Max','2026-05-31 10:00:00'),(4,4,'Membeli Produk: Laptop Asus ROG Strix (1 pcs)','2026-06-01 10:30:00'),(5,4,'Membeli Produk: Mouse Wireless Logitech G304 (2 pcs)','2026-06-01 10:30:00'),(6,5,'Membeli Produk: TWS Soundcore R50i (1 pcs)','2026-06-02 14:15:00'),(7,6,'Membeli Produk: Monitor LG 24 Inch IPS (1 pcs)','2026-06-05 01:30:32'),(10,2,'Top-up saldo Rp 50000','2026-06-05 17:39:19'),(11,7,'Top-up saldo Rp 10000','2026-06-05 17:47:28'),(14,4,'Top-up saldo Rp 100000','2026-06-05 19:37:39'),(15,4,'Top-up saldo Rp 100000000','2026-06-05 19:42:53'),(18,8,'Top-up saldo Rp 500000','2026-06-05 20:14:30'),(32,2,'Menghapus Produk: Mouse Wireless Logitech G304','2026-06-05 21:27:49'),(33,2,'Memperbarui Informasi Produk: Lenovo Legion 5 Pro','2026-06-05 21:28:17'),(34,2,'Menambahkan Produk Baru: Monitor','2026-06-05 21:29:33'),(35,4,'Melakukan Pembelian Produk. ID Pesanan: 16','2026-06-05 21:30:45'),(36,2,'Memperbarui Informasi Produk: Monitor LG 24 Inch','2026-06-05 21:30:45'),(37,3,'Memperbarui Informasi Produk: Macbook Air M2 256GB Space Gray','2026-06-05 21:30:45'),(38,2,'Menambahkan Produk Baru: Monitor','2026-06-05 22:56:37'),(39,4,'Melakukan Pembelian Produk. ID Pesanan: 17','2026-06-05 23:05:32'),(40,2,'Memperbarui Informasi Produk: Monitor LG 24 Inch','2026-06-05 23:05:32'),(41,3,'Memperbarui Informasi Produk: Mouse Razer DeathAdder V3 Pro','2026-06-05 23:05:32'),(42,2,'Memperbarui Informasi Produk: Keyboard Mechanical Keychron K2','2026-06-05 23:05:32'),(43,3,'Memperbarui Informasi Produk: Powerbank Anker PowerCore 20000mAh','2026-06-05 23:05:32'),(44,2,'Memperbarui Informasi Produk: Kulkas Sharp 2 Pintu Inverter','2026-06-05 23:05:32'),(45,2,'Memperbarui Informasi Produk: Monitor LG 12 inch','2026-06-05 23:06:19');
/*!40000 ALTER TABLE `log_aktifitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan`
--

DROP TABLE IF EXISTS `pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  PRIMARY KEY (`id_pesanan`),
  KEY `fk_pesanan_user` (`id_user`),
  CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan`
--

LOCK TABLES `pesanan` WRITE;
/*!40000 ALTER TABLE `pesanan` DISABLE KEYS */;
INSERT INTO `pesanan` VALUES (2,5,'2026-06-02 14:15:00'),(3,6,'2026-06-05 01:30:32'),(4,4,'2026-06-05 15:16:49'),(8,4,'2026-06-05 15:26:55'),(12,4,'2026-06-05 19:47:51'),(16,4,'2026-06-05 21:30:45'),(17,4,'2026-06-05 23:05:32');
/*!40000 ALTER TABLE `pesanan` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_pesanan` AFTER INSERT ON `pesanan` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (NEW.id_user,
        CONCAT('Melakukan Pembelian Produk. ID Pesanan: ', NEW.id_pesanan),
        NOW()
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk` (
  `id_produk` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `foto_barang` varchar(255) DEFAULT 'default.png',
  `deskripsi` text,
  PRIMARY KEY (`id_produk`),
  KEY `fk_produk_kategori` (`id_kategori`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
INSERT INTO `produk` VALUES (3,3,'iPhone 15 Pro Max',20000000,5,2,'default.png','iPhone 15 Pro Max kapasitas 256GB warna Natural Titanium. Ex garansi iBox, Battery Health 92%. Body mulus 98% selalu pakai case. True tone & Face ID on lancar jaya.'),(4,3,'TWS Soundcore R50i',200000,96,3,'default.png','Earphone bluetooth TWS dari Anker Soundcore. Bass mantap, daya tahan baterai hingga 30 jam dengan casing. Cocok untuk olahraga atau commute harian. Segel!'),(5,2,'Monitor LG 12 inch',15000,12,1,'1780642805_b8515e4b80934702a7a7b01b00f8f6da.webp','Monitor LG 24 inch panel. Layar jernih, warna akurat cocok untuk desain maupun main game ringan. Minus pemakaian wajar, tidak ada dead pixel.'),(6,2,'Lenovo Legion 5 Pro',21500000,5,1,'default.png','Laptop gaming andalan dengan RTX 4060 dan layar WQHD+ 165Hz. Cocok untuk hardcore gamer dan content creator.'),(7,3,'Macbook Air M2 256GB Space Gray',18500000,14,1,'default.png','Laptop super tipis dan ringan dari Apple dengan chip M2. Baterai tahan seharian penuh untuk produktivitas maksimal.'),(8,2,'PC Rakitan Core i5 12400F',8500000,2,1,'default.png','PC Rakitan siap pakai untuk gaming mid-range. Sudah terinstall Windows 11, aplikasi standar, dan garansi part 1 tahun.'),(9,3,'SSD Samsung 980 PRO 1TB NVMe',1800000,29,1,'default.png','SSD PCIe 4.0 dengan kecepatan baca hingga 7000MB/s. Loading game jadi super cepat dan copy data hitungan detik.'),(10,2,'Samsung Galaxy S24 Ultra 512GB',21000000,12,2,'default.png','Smartphone flagship dengan fitur Galaxy AI, kamera utama 200MP, frame titanium, dan S Pen bawaan.'),(11,3,'iPad Pro M4 11-inch Wi-Fi 256GB',19000000,7,2,'default.png','Tablet paling mutakhir dari Apple dengan layar Ultra Retina XDR OLED dan chip M4 yang sangat bertenaga untuk render video.'),(12,2,'Xiaomi 14 12/256GB',12000000,3,2,'default.png','Flagship berukuran compact dengan lensa Leica otentik. Performa ngebut dengan chipset Snapdragon 8 Gen 3 terbaru.'),(13,3,'Poco X6 Pro 5G',4500000,45,2,'default.png','Ponsel mid-range killer. Menggunakan prosesor Dimensity 8300 Ultra, cocok banget buat gaming kompetitif tanpa frame drop.'),(14,2,'Keyboard Mechanical Keychron K2',1350000,24,3,'default.png','Keyboard mechanical wireless layout 75%. Menggunakan switch Gateron Brown yang tactile namun tidak berisik.'),(15,3,'Mouse Razer DeathAdder V3 Pro',2200000,9,3,'default.png','Mouse gaming wireless ultra ringan favorit atlet esports dunia. Menggunakan sensor optik presisi tinggi dan minim latensi.'),(16,2,'Headset Gaming HyperX Cloud II',1200000,18,3,'default.png','Headset gaming legendaris dengan 7.1 surround sound. Earpad memory foam yang sangat nyaman dipakai berjam-jam.'),(17,3,'Powerbank Anker PowerCore 20000mAh',650000,59,3,'default.png','Powerbank kapasitas besar dengan teknologi fast charging IQ. Port Type-C output tinggi, bisa untuk ngecas laptop darurat.'),(18,2,'Kamera Mirrorless Canon EOS R50',12500000,4,4,'default.png','Kamera mirrorless ringkas dan ringan, sangat cocok untuk pemula dan vlogger. Sudah termasuk lensa kit 18-45mm.'),(19,3,'Lensa Sony FE 50mm f/1.8',3500000,8,4,'default.png','Lensa fix wajib untuk pengguna sistem kamera Sony Full-Frame. Menghasilkan foto dengan efek bokeh yang mulus dan tajam.'),(21,3,'Tripod Takara Rover 66',450000,35,4,'default.png','Tripod kokoh dengan bahan aluminium. Ballhead stabil dan salah satu kaki bisa dilepas untuk dijadikan monopod.'),(22,2,'Smart TV Samsung 43 Inch 4K UHD',4200000,12,5,'default.png','TV pintar dengan resolusi 4K yang memanjakan mata. Mendukung Netflix, YouTube, dan integrasi ekosistem SmartThings.'),(23,3,'AC Daikin Standard 1/2 PK',3400000,10,5,'default.png','AC hemat listrik, awet, dan cepat dingin. Sangat cocok untuk ukuran kamar tidur standar. Pemasangan mudah.'),(24,2,'Kulkas Sharp 2 Pintu Inverter',3800000,7,5,'default.png','Kulkas ukuran sedang dengan teknologi Plasmacluster untuk membunuh bakteri, dan kompresor inverter yang sangat hemat energi.'),(25,3,'Mesin Cuci LG Front Load 8kg',5100000,5,5,'default.png','Mesin cuci bukaan depan dengan teknologi AI DD. Pintar mendeteksi jenis kain agar mencuci lebih bersih namun tetap merawat pakaian.'),(26,2,'Laptop Advan Work Pro',100000,50,1,'1780641477_telur.png','Laptop baut lepas, engsel mangap'),(27,2,'Laptop Advan Work Pro',100000,49,1,'default.png','Laptop baut lepas, engsel mangap'),(28,8,'Laptop Advan WorkPro',5000000,10,1,'1780665296_images.jpg','Laptop baut sering copot, engsel kendor, keyboard pada mati, speaker rusak'),(30,2,'Monitor',10000000,100,1,'1780669773_b8515e4b80934702a7a7b01b00f8f6da.webp','--'),(31,2,'Monitor',1000000,9,1,'1780674997_b8515e4b80934702a7a7b01b00f8f6da.webp','-');
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_produk` AFTER INSERT ON `produk` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (NEW.id_user,
        CONCAT('Menambahkan Produk Baru: ', NEW.nama),
        NOW()
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_produk` AFTER UPDATE ON `produk` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (NEW.id_user,
        CONCAT('Memperbarui Informasi Produk: ', NEW.nama),
        NOW()
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `delete_produk` AFTER DELETE ON `produk` FOR EACH ROW BEGIN
    INSERT INTO log_aktifitas(id_user, keterangan, tgl_aktifitas)
    VALUES (OLD.id_user,
        CONCAT('Menghapus Produk: ', OLD.nama),
        NOW()
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `produk_aksesoris`
--

DROP TABLE IF EXISTS `produk_aksesoris`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk_aksesoris` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_aksesoris`
--

LOCK TABLES `produk_aksesoris` WRITE;
/*!40000 ALTER TABLE `produk_aksesoris` DISABLE KEYS */;
INSERT INTO `produk_aksesoris` VALUES ('Gadget Murah','Aksesoris & Periferal',4,'TWS Soundcore R50i',200000,96),('Toko Komputer Jaya','Aksesoris & Periferal',14,'Keyboard Mechanical Keychron K2',1350000,25),('Gadget Murah','Aksesoris & Periferal',15,'Mouse Razer DeathAdder V3 Pro',2200000,10),('Toko Komputer Jaya','Aksesoris & Periferal',16,'Headset Gaming HyperX Cloud II',1200000,18),('Gadget Murah','Aksesoris & Periferal',17,'Powerbank Anker PowerCore 20000mAh',650000,60);
/*!40000 ALTER TABLE `produk_aksesoris` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_handphone`
--

DROP TABLE IF EXISTS `produk_handphone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk_handphone` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_handphone`
--

LOCK TABLES `produk_handphone` WRITE;
/*!40000 ALTER TABLE `produk_handphone` DISABLE KEYS */;
INSERT INTO `produk_handphone` VALUES ('Gadget Murah','Handphone & Tablet',3,'iPhone 15 Pro Max',20000000,5),('Toko Komputer Jaya','Handphone & Tablet',10,'Samsung Galaxy S24 Ultra 512GB',21000000,12),('Gadget Murah','Handphone & Tablet',11,'iPad Pro M4 11-inch Wi-Fi 256GB',19000000,7),('Toko Komputer Jaya','Handphone & Tablet',12,'Xiaomi 14 12/256GB',12000000,3),('Gadget Murah','Handphone & Tablet',13,'Poco X6 Pro 5G',4500000,45);
/*!40000 ALTER TABLE `produk_handphone` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_kamera`
--

DROP TABLE IF EXISTS `produk_kamera`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk_kamera` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_kamera`
--

LOCK TABLES `produk_kamera` WRITE;
/*!40000 ALTER TABLE `produk_kamera` DISABLE KEYS */;
INSERT INTO `produk_kamera` VALUES ('Toko Komputer Jaya','Kamera & Fotografi',18,'Kamera Mirrorless Canon EOS R50',12500000,4),('Gadget Murah','Kamera & Fotografi',19,'Lensa Sony FE 50mm f/1.8',3500000,8),('Gadget Murah','Kamera & Fotografi',21,'Tripod Takara Rover 66',450000,35);
/*!40000 ALTER TABLE `produk_kamera` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_komputer`
--

DROP TABLE IF EXISTS `produk_komputer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk_komputer` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_komputer`
--

LOCK TABLES `produk_komputer` WRITE;
/*!40000 ALTER TABLE `produk_komputer` DISABLE KEYS */;
INSERT INTO `produk_komputer` VALUES ('Toko Komputer Jaya','Komputer & Laptop',5,'Monitor LG 24 Inch',1500000,13),('Toko Komputer Jaya','Komputer & Laptop',6,'Lenovo Legion 5 Pro',21500000,5),('Gadget Murah','Komputer & Laptop',7,'Macbook Air M2 256GB Space Gray',18500000,14),('Toko Komputer Jaya','Komputer & Laptop',8,'PC Rakitan Core i5 12400F',8500000,2),('Gadget Murah','Komputer & Laptop',9,'SSD Samsung 980 PRO 1TB NVMe',1800000,29),('Toko Komputer Jaya','Komputer & Laptop',26,'Laptop Advan Work Pro',100000,50),('Toko Komputer Jaya','Komputer & Laptop',27,'Laptop Advan Work Pro',100000,49),('','Komputer & Laptop',28,'Laptop Advan WorkPro',5000000,10),('Toko Komputer Jaya','Komputer & Laptop',30,'Monitor',10000000,100);
/*!40000 ALTER TABLE `produk_komputer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_prt`
--

DROP TABLE IF EXISTS `produk_prt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk_prt` (
  `nama_toko` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `id_produk` int NOT NULL DEFAULT '0',
  `nama` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_prt`
--

LOCK TABLES `produk_prt` WRITE;
/*!40000 ALTER TABLE `produk_prt` DISABLE KEYS */;
INSERT INTO `produk_prt` VALUES ('Toko Komputer Jaya','Peralatan Rumah Tangga',22,'Smart TV Samsung 43 Inch 4K UHD',4200000,12),('Gadget Murah','Peralatan Rumah Tangga',23,'AC Daikin Standard 1/2 PK',3400000,10),('Toko Komputer Jaya','Peralatan Rumah Tangga',24,'Kulkas Sharp 2 Pintu Inverter',3800000,8),('Gadget Murah','Peralatan Rumah Tangga',25,'Mesin Cuci LG Front Load 8kg',5100000,5);
/*!40000 ALTER TABLE `produk_prt` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ringkasan_produk`
--

DROP TABLE IF EXISTS `ringkasan_produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ringkasan_produk` (
  `id_produk` int NOT NULL DEFAULT '0',
  `nama_produk` varchar(100) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `harga` int NOT NULL,
  `stok` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ringkasan_produk`
--

LOCK TABLES `ringkasan_produk` WRITE;
/*!40000 ALTER TABLE `ringkasan_produk` DISABLE KEYS */;
INSERT INTO `ringkasan_produk` VALUES (5,'Monitor LG 24 Inch','Komputer & Laptop',1500000,13),(6,'Lenovo Legion 5 Pro','Komputer & Laptop',21500000,5),(7,'Macbook Air M2 256GB Space Gray','Komputer & Laptop',18500000,14),(8,'PC Rakitan Core i5 12400F','Komputer & Laptop',8500000,2),(9,'SSD Samsung 980 PRO 1TB NVMe','Komputer & Laptop',1800000,29),(26,'Laptop Advan Work Pro','Komputer & Laptop',100000,50),(27,'Laptop Advan Work Pro','Komputer & Laptop',100000,49),(28,'Laptop Advan WorkPro','Komputer & Laptop',5000000,10),(30,'Monitor','Komputer & Laptop',10000000,100),(3,'iPhone 15 Pro Max','Handphone & Tablet',20000000,5),(10,'Samsung Galaxy S24 Ultra 512GB','Handphone & Tablet',21000000,12),(11,'iPad Pro M4 11-inch Wi-Fi 256GB','Handphone & Tablet',19000000,7),(12,'Xiaomi 14 12/256GB','Handphone & Tablet',12000000,3),(13,'Poco X6 Pro 5G','Handphone & Tablet',4500000,45),(4,'TWS Soundcore R50i','Aksesoris & Periferal',200000,96),(14,'Keyboard Mechanical Keychron K2','Aksesoris & Periferal',1350000,25),(15,'Mouse Razer DeathAdder V3 Pro','Aksesoris & Periferal',2200000,10),(16,'Headset Gaming HyperX Cloud II','Aksesoris & Periferal',1200000,18),(17,'Powerbank Anker PowerCore 20000mAh','Aksesoris & Periferal',650000,60),(18,'Kamera Mirrorless Canon EOS R50','Kamera & Fotografi',12500000,4),(19,'Lensa Sony FE 50mm f/1.8','Kamera & Fotografi',3500000,8),(21,'Tripod Takara Rover 66','Kamera & Fotografi',450000,35),(22,'Smart TV Samsung 43 Inch 4K UHD','Peralatan Rumah Tangga',4200000,12),(23,'AC Daikin Standard 1/2 PK','Peralatan Rumah Tangga',3400000,10),(24,'Kulkas Sharp 2 Pintu Inverter','Peralatan Rumah Tangga',3800000,8),(25,'Mesin Cuci LG Front Load 8kg','Peralatan Rumah Tangga',5100000,5);
/*!40000 ALTER TABLE `ringkasan_produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('penjual','pembeli','admin') NOT NULL,
  `saldo` int DEFAULT '0',
  `nama_toko` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin_zapiere','Admin Zapiere','admin123','admin',0,''),(2,'andi_komputer','Andi Komputer','pass123','penjual',93200000,'Toko Komputer Jaya'),(3,'ahmad_sobri','Ahmad Sobri','pass123','penjual',25950000,'Gadget Murah'),(4,'abdul_buyer','Abdul','pass123','pembeli',1900000,''),(5,'budi_buyer','Budi Santoso','pass123','pembeli',500000,''),(6,'bach','Bachtiar Nugraha','bachtiarX24','pembeli',0,''),(7,'rara','rara ya','111111','pembeli',10000,''),(8,'kere','Penjual Kere','123456','penjual',0,'');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `v_log_aktifitas`
--

DROP TABLE IF EXISTS `v_log_aktifitas`;
/*!50001 DROP VIEW IF EXISTS `v_log_aktifitas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_log_aktifitas` AS SELECT 
 1 AS `id_log`,
 1 AS `tgl_aktifitas`,
 1 AS `nama_pelaku`,
 1 AS `role`,
 1 AS `keterangan`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_penjualan_detail`
--

DROP TABLE IF EXISTS `v_penjualan_detail`;
/*!50001 DROP VIEW IF EXISTS `v_penjualan_detail`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_penjualan_detail` AS SELECT 
 1 AS `id_pesanan`,
 1 AS `tanggal`,
 1 AS `pembeli`,
 1 AS `produk`,
 1 AS `id_user_penjual`,
 1 AS `jumlah`,
 1 AS `subtotal`,
 1 AS `subtotal_rp`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_pesanan_lengkap`
--

DROP TABLE IF EXISTS `v_pesanan_lengkap`;
/*!50001 DROP VIEW IF EXISTS `v_pesanan_lengkap`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_pesanan_lengkap` AS SELECT 
 1 AS `id_pesanan`,
 1 AS `id_user`,
 1 AS `tanggal`,
 1 AS `pembeli`,
 1 AS `total_item`,
 1 AS `total_bayar`,
 1 AS `total_bayar_rp`,
 1 AS `produk`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `v_produk_lengkap`
--

DROP TABLE IF EXISTS `v_produk_lengkap`;
/*!50001 DROP VIEW IF EXISTS `v_produk_lengkap`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `v_produk_lengkap` AS SELECT 
 1 AS `id_produk`,
 1 AS `id_user`,
 1 AS `nama`,
 1 AS `harga`,
 1 AS `harga_rp`,
 1 AS `stok`,
 1 AS `is_tersedia`,
 1 AS `id_kategori`,
 1 AS `foto_barang`,
 1 AS `kategori`,
 1 AS `penjual`,
 1 AS `total_terjual`,
 1 AS `omzet`,
 1 AS `omzet_rp`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_log_aktifitas`
--

/*!50001 DROP VIEW IF EXISTS `v_log_aktifitas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_log_aktifitas` AS select `l`.`id_log` AS `id_log`,`l`.`tgl_aktifitas` AS `tgl_aktifitas`,coalesce(`u`.`nama`,'Sistem / Akun Dihapus') AS `nama_pelaku`,`u`.`role` AS `role`,`l`.`keterangan` AS `keterangan` from (`log_aktifitas` `l` left join `users` `u` on((`l`.`id_user` = `u`.`id_user`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_penjualan_detail`
--

/*!50001 DROP VIEW IF EXISTS `v_penjualan_detail`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_penjualan_detail` AS select `ps`.`id_pesanan` AS `id_pesanan`,`ps`.`tanggal` AS `tanggal`,`buyer`.`nama` AS `pembeli`,`p`.`nama` AS `produk`,`p`.`id_user` AS `id_user_penjual`,`dp`.`jumlah` AS `jumlah`,(`dp`.`jumlah` * `p`.`harga`) AS `subtotal`,`f_format_rupiah`((`dp`.`jumlah` * `p`.`harga`)) AS `subtotal_rp` from (((`detail_pesanan` `dp` join `produk` `p` on((`p`.`id_produk` = `dp`.`id_produk`))) join `pesanan` `ps` on((`ps`.`id_pesanan` = `dp`.`id_pesanan`))) join `users` `buyer` on((`buyer`.`id_user` = `ps`.`id_user`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_pesanan_lengkap`
--

/*!50001 DROP VIEW IF EXISTS `v_pesanan_lengkap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_pesanan_lengkap` AS select `ps`.`id_pesanan` AS `id_pesanan`,`ps`.`id_user` AS `id_user`,`ps`.`tanggal` AS `tanggal`,`u`.`nama` AS `pembeli`,`f_total_item_pesanan`(`ps`.`id_pesanan`) AS `total_item`,`f_total_bayar_pesanan`(`ps`.`id_pesanan`) AS `total_bayar`,`f_format_rupiah`(`f_total_bayar_pesanan`(`ps`.`id_pesanan`)) AS `total_bayar_rp`,`f_daftar_produk_pesanan`(`ps`.`id_pesanan`) AS `produk` from (`pesanan` `ps` left join `users` `u` on((`u`.`id_user` = `ps`.`id_user`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_produk_lengkap`
--

/*!50001 DROP VIEW IF EXISTS `v_produk_lengkap`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_produk_lengkap` AS select `p`.`id_produk` AS `id_produk`,`p`.`id_user` AS `id_user`,`p`.`nama` AS `nama`,`p`.`harga` AS `harga`,`f_format_rupiah`(`p`.`harga`) AS `harga_rp`,`p`.`stok` AS `stok`,`f_cek_stok_tersedia`(`p`.`id_produk`) AS `is_tersedia`,`p`.`id_kategori` AS `id_kategori`,`p`.`foto_barang` AS `foto_barang`,`k`.`nama` AS `kategori`,`u`.`nama` AS `penjual`,`f_jumlah_produk_terjual`(`p`.`id_produk`) AS `total_terjual`,(`f_jumlah_produk_terjual`(`p`.`id_produk`) * `p`.`harga`) AS `omzet`,`f_format_rupiah`((`f_jumlah_produk_terjual`(`p`.`id_produk`) * `p`.`harga`)) AS `omzet_rp` from ((`produk` `p` left join `kategori` `k` on((`k`.`id_kategori` = `p`.`id_kategori`))) left join `users` `u` on((`u`.`id_user` = `p`.`id_user`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-05 23:46:23
