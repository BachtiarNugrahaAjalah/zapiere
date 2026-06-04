<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Mengambil produk beserta meta (kategori, penjual, total_terjual, omzet).
 * Menggunakan VIEW `v_produk_lengkap` dari database.
 *
 * @param int|null $sellerId  Filter berdasarkan id_user penjual (opsional)
 * @param int|null $limit     Batasi jumlah baris yang dikembalikan (opsional)
 */
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

/**
 * Mengambil baris penjualan (item terjual) untuk spesifik penjual.
 */
function sales_rows(int $sellerId): array
{
    return db_all("
        SELECT *
        FROM v_penjualan_detail
        WHERE id_user_penjual = {$sellerId}
        ORDER BY tanggal DESC
    ");
}

/**
 * Memastikan produk memiliki gambar yang valid dan mengembalikan URL-nya.
 * Jika file tidak ada atau string kosong, akan difallback ke image.png default.
 *
 * @param array $product Array asosiatif data produk.
 * @return string URL gambar yang valid.
 */
function product_image_url(array $product): string
{
    $file = $product['foto_barang'] ?? 'image.png';
    $known = ['asus_rog.png', 'logitech_g304.png', 'iphone15.png', 'soundcore.png', 'monitor_lg.png', 'default.png'];

    if (in_array($file, $known, true) || trim((string) $file) === '') {
        $file = 'image.png';
    }

    return asset_url('assets/images/' . $file);
}

/**
 * Mengecek apakah stok produk masih tersedia (lebih dari 0).
 * Memanggil langsung SQL Stored Function `f_cek_stok_tersedia`.
 */
function check_stock(int $productId): bool
{
    return (bool) db_value("SELECT f_cek_stok_tersedia({$productId})");
}

