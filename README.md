# 🛒 ZAPIERE (Proyek UAP)

ZAPIERE merupakan platform **e-commerce marketplace** yang dibangun menggunakan **PHP** dan **MySQL**. Sistem ini dirancang untuk menghubungkan penjual dan pembeli dalam satu ekosistem digital yang terintegrasi dengan memanfaatkan fitur-fitur lanjutan DBMS seperti **Stored Procedure**, **Function**, **View**, **Trigger**, **Transaction**, serta **Backup & Monitoring Infrastruktur**.

Selain menyediakan fitur marketplace seperti pengelolaan produk, keranjang belanja, dan transaksi pembelian, sistem ini juga menerapkan mekanisme otomatisasi database untuk menjaga konsistensi data, keamanan transaksi, serta pencatatan aktivitas pengguna melalui audit trail.

<img src="assets/images/dashboard.png">

<h1>📌 Detail Konsep</h1>
Beberapa implementasi **Stored Procedure**, **Function**, **View**, dan **Trigger** yang digunakan pada sistem ZAPIERE adalah sebagai berikut:

## 🛍️ checkout_produk - Stored Procedure
<img src="assets/images/tabelProsedur.png">
Stored Procedure ini digunakan untuk menangani proses checkout produk dari keranjang belanja dalam satu transaksi database.

Procedure ini akan:

* Memeriksa ketersediaan stok produk.
* Memvalidasi saldo pengguna.
* Membuat data pesanan.
* Mengurangi stok produk.
* Memindahkan saldo pembeli ke penjual.
* Menjalankan `COMMIT` jika seluruh proses berhasil.
* Menjalankan `ROLLBACK` jika terjadi kegagalan.

**Contoh Pemanggilan Procedure**

```php
$stmt = mysqli_prepare($conn, "CALL checkout_produk(?)");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
```

---

## 🧮 f_total_bayar_pesanan

Function ini digunakan untuk menghitung total pembayaran pesanan berdasarkan data produk dan jumlah pembelian yang tersimpan pada database.

Function ini dimanfaatkan untuk:

* Mengurangi perhitungan manual pada PHP.
* Menjaga konsistensi nilai total pembayaran.
* Mendukung pembuatan laporan dan view.

**Contoh Pemanggilan Function**

```sql
SELECT f_total_bayar_pesanan(id_pesanan);
```

---

## 👁️ View : v_log_aktifitas
<img src="assets/images/view_log.png">

Untuk memfasilitasi halaman pemantauan admin, dibangun sebuah View (v_log_aktifitas) yang mengenkapsulasi kueri kompleks dengan LEFT JOIN dan fungsi COALESCE. View ini memastikan integritas data riwayat sistem, di mana aktivitas dari akun yang telah dihapus atau aksi otomatis dari sistem tidak akan hilang atau bernilai null, melainkan secara otomatis dilabeli sebagai aktivitas sistem.

**Contoh Penggunaan**
SELECT * FROM v_log_aktifitas 
WHERE tgl_aktifitas >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
ORDER BY tgl_aktifitas DESC

Data pada tabel view dibatasi menjadi interval 30 hari terakhir agar sistem tidak terbebani dengan data aktivitas ynag terlalu banyak.
- NOW(): Mengambil waktu tepat saat ini (detik ini juga).
- INTERVAL 30 DAY: Menentukan durasi waktu, yaitu 30 hari.
- DATE_SUB(..., ...): Fungsi untuk melakukan pengurangan tanggal. Jadi, DATE_SUB(NOW(), INTERVAL 30 DAY) artinya: "Hitung tanggal tepat 30 hari yang lalu dari hari ini."
---

## 🔄 Trigger

### insert_pesanan

<img src="assets/images/tabelTrigger.png">
Sistem audit trail otomatis dibangun menggunakan trigger database (AFTER INSERT, AFTER UPDATE, dan AFTER DELETE). Sistem ini berfungsi untuk mencatat setiap perubahan penting pada data produk dan pesanan ke dalam tabel log aktivitas secara real-time. Dengan pencatatan yang dilakukan langsung di tingkat database, sistem dapat mengurangi risiko kehilangan data log akibat kesalahan atau kegagalan pada aplikasi PHP serta meningkatkan keamanan dan keandalan proses pencatatan aktivitas.
Contoh penerapan query untuk trigger insert pemesanan ke tabel log aktivitas adalah:
```sql
CREATE TRIGGER insert_pesanan
AFTER INSERT ON pesanan
FOR EACH ROW
BEGIN
    INSERT INTO log_aktifitas(keterangan, tgl_aktifitas)
    VALUES(
        CONCAT('Melakukan Pembelian Produk. ID Pesanan: ', NEW.id_pesanan),
        NOW()
    );
END;
```

Selain itu ada pula trigger delete_produk, insert_produk dan update_produk yang akan otomatis menambahkan keterangan aktivitas di tabel log_aktifitas. Trigger ini dijalankan ketika penjual melakukan perubahan, menambah atau menghapus data produk. Sehingga setiap perubahan dapat otomatis dicatat ke tabel `log_aktifitas` yang mempermudah administrator untuk melakukan pelacakan aktivitas pengelolaan produk.


# 💾 Backup & Monitoring Infrastruktur

Untuk menjaga ketersediaan dan keamanan data, ZAPIERE menyediakan dashboard monitoring yang hanya dapat diakses oleh pengguna dengan role **admin**.

<img src="img/backup-dashboard.png">
