<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/UserModel.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

$user = current_user('pembeli');
if (!$user || $user['id_user'] == 0) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login sebagai pembeli']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['cart']) || !is_array($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja masih kosong']);
    exit;
}

global $conn;
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database tidak tersedia']);
    exit;
}

// Mulai transaksi database
mysqli_begin_transaction($conn);

try {
    $userId = (int)$user['id_user'];
    
    // 1. Buat pesanan baru
    $stmt = mysqli_prepare($conn, "INSERT INTO pesanan (id_user, tanggal) VALUES (?, NOW())");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal membuat data pesanan utama.");
    }
    $pesananId = mysqli_insert_id($conn);
    
    // 2. Proses tiap barang di keranjang
    foreach ($data['cart'] as $item) {
        $produkId = (int)$item['id_produk'];
        $jumlah = (int)$item['jumlah'];
        
        // Lock row produk (Pessimistic Locking) untuk mengecek stok agar aman dari race condition
        $stmtProd = mysqli_prepare($conn, "SELECT stok, nama FROM produk WHERE id_produk = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmtProd, "i", $produkId);
        mysqli_stmt_execute($stmtProd);
        $resProd = mysqli_stmt_get_result($stmtProd);
        $prod = mysqli_fetch_assoc($resProd);
        
        if (!$prod) {
            throw new Exception("Sebuah produk di keranjang tidak ditemukan di database.");
        }
        if ($prod['stok'] < $jumlah) {
            throw new Exception("Stok '{$prod['nama']}' tidak mencukupi. Sisa stok: {$prod['stok']}.");
        }
        
        // Simpan detail pesanan
        $stmtDetail = mysqli_prepare($conn, "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmtDetail, "iii", $pesananId, $produkId, $jumlah);
        if (!mysqli_stmt_execute($stmtDetail)) {
            throw new Exception("Gagal menyimpan rincian pesanan untuk produk '{$prod['nama']}'.");
        }
        
        // Kurangi stok
        $newStok = $prod['stok'] - $jumlah;
        $stmtUpdate = mysqli_prepare($conn, "UPDATE produk SET stok = ? WHERE id_produk = ?");
        mysqli_stmt_bind_param($stmtUpdate, "ii", $newStok, $produkId);
        if (!mysqli_stmt_execute($stmtUpdate)) {
            throw new Exception("Gagal memperbarui sisa stok.");
        }
        
        // Simpan log aktifitas
        $keterangan = "Membeli Produk: {$prod['nama']} ({$jumlah} pcs)";
        $stmtLog = mysqli_prepare($conn, "INSERT INTO log_aktifitas (id_user, keterangan, tgl_aktifitas) VALUES (?, ?, NOW())");
        mysqli_stmt_bind_param($stmtLog, "is", $userId, $keterangan);
        mysqli_stmt_execute($stmtLog);
    }
    
    // Jika semua berhasil, commit transaksi
    mysqli_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Pesanan berhasil diproses.']);
    
} catch (Exception $e) {
    // Jika ada yang gagal, kembalikan semua perubahan ke kondisi semula (Rollback)
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
