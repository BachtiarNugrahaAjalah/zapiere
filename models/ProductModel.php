<?php
require_once __DIR__ . '/../config/config.php';

function products_with_meta(?int $sellerId = null, ?int $limit = null): array
{
    $where = '';
    if ($sellerId !== null) {
        $where = "WHERE id_user = {$sellerId}";
    }

    $limitSql = $limit ? 'LIMIT ' . (int) $limit : '';

    return db_all("
        SELECT *
        FROM v_produk_lengkap
        {$where}
        ORDER BY id_produk DESC
        {$limitSql}
    ");
}

function sales_rows(int $sellerId): array
{
    return db_all("
        SELECT *
        FROM v_penjualan_detail
        WHERE id_user_penjual = {$sellerId}
        ORDER BY tanggal DESC
    ");
}

function product_image_url(string $foto_barang): string
{
    $file = trim((string) $foto_barang);
    if ($file === '') {
        $file = 'default.png';
    }
    return asset_url('assets/images/' . $file);
}

function check_stock(int $productId): bool
{
    return (bool) db_value("SELECT f_cek_stok_tersedia({$productId})");
}

function get_all_data_produk(?int $sellerId = null)
{
    if ($sellerId !== null) {
        return db_all("
            SELECT p.*, u.nama as penjual, k.nama as kategori FROM produk p 
            INNER JOIN users u ON p.id_user = u.id_user
            INNER JOIN kategori k ON p.id_kategori = k.id_kategori
            WHERE p.id_user = {$sellerId}
        ");
    }
    
    return db_all("CALL tampil_produk");
}

function checkout(int $idPembeli,string $jsonCartData)
{
    /** @var mysqli $conn */
    global $conn;
    $stmt = $conn->prepare("CALL checkout_produk(?, ?)");
    $stmt->bind_param("is", $idPembeli, $jsonCartData);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal mengeksekusi prosedur checkout.");
    }

    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        if ($row && $row['status'] === 'Berhasil') {
            return ['success' => true, 'message' => $row['pesan']];
        } else {
            $msg = $row['pesan'] ?? 'Terjadi kesalahan saat checkout.';
            return ['success' => false, 'message' => $msg];
        }
    } else {
        throw new Exception("Tidak ada respon dari server.");
    }
}

function add_product($nama_barang, $harga, $stok, $id_penjual, $id_kategori, $foto_barang, $deskripsi)
{
    /** @var mysqli $conn */
    global $conn;
    $stmt = $conn->prepare("CALL posting_produk(?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    $stmt->bind_param('siiiiss', $nama_barang, $harga, $stok, $id_penjual, $id_kategori, $foto_barang, $deskripsi);

    if (!mysqli_stmt_execute($stmt)) {
        return ['success' => false, 'message' => 'Gagal mengeksekusi prosedur tambah produk.'];
    }

    $res = mysqli_stmt_get_result($stmt);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        if ($row && $row['status'] === 'Berhasil') {
            return ['success' => true, 'message' => $row['pesan']];
        } else {
            $msg = $row['pesan'] ?? 'Terjadi kesalahan saat menambah produk.';
            return ['success' => false, 'message' => $msg];
        }
    } else {
        return ['success' => false, 'message' => 'Tidak ada respon dari server.'];
    }
}

function edit_product($id_produk, $nama_barang, $harga, $stok, $id_kategori, $foto_barang, $deskripsi)
{
    /** @var mysqli $conn */
    global $conn;
    $stmt = $conn->prepare("CALL edit_produk(?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isiiiss', $id_produk, $nama_barang, $harga, $stok, $id_kategori, $foto_barang, $deskripsi);
    return $stmt->execute();
}

function get_smart_recommendation()
{
    return db_all("
        SELECT id_produk, nama, harga, foto_barang, '🚨 Sisa Dikit!' AS label_promo 
        FROM produk 
        WHERE stok <= 5 AND stok > 0
        
        UNION ALL
        
        SELECT id_produk, nama, harga, foto_barang, '💎 Premium' AS label_promo 
        FROM produk 
        WHERE harga >= 15000000
    ");
}

function get_ringkasan_produk(): array
{
    return db_all("SELECT * FROM ringkasan_produk ORDER BY nama_produk");
}

function get_produk_komputer(): array
{
    return db_all("SELECT * FROM produk_komputer ORDER BY nama");
}

function get_produk_handphone(): array
{
    return db_all("SELECT * FROM produk_handphone ORDER BY nama");
}

function get_produk_aksesoris(): array
{
    return db_all("SELECT * FROM produk_aksesoris ORDER BY nama");
}

function get_produk_kamera(): array
{
    return db_all("SELECT * FROM produk_kamera ORDER BY nama");
}

function get_produk_prt(): array
{
    return db_all("SELECT * FROM produk_prt ORDER BY nama");
}
