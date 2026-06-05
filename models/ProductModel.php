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

function product_image_url(array $product): string
{
    $file = $product['foto_barang'] ?? 'image.png';
    $known = ['asus_rog.png', 'logitech_g304.png', 'iphone15.png', 'soundcore.png', 'monitor_lg.png', 'default.png'];

    if (in_array($file, $known, true) || trim((string) $file) === '') {
        $file = 'image.png';
    }

    return asset_url('assets/images/' . $file);
}

function check_stock(int $productId): bool
{
    return (bool) db_value("SELECT f_cek_stok_tersedia({$productId})");
}

function get_all_data_produk(?int $sellerId = null) {
    $where = $sellerId !== null ? "WHERE p.id_user = {$sellerId}" : '';
    return db_all("
        SELECT p.*, u.nama as penjual, k.nama as kategori FROM produk p 
        RIGHT JOIN users u ON p.id_user = u.id_user
        RIGHT JOIN kategori k ON p.id_kategori = k.id_kategori
        {$where}
    ");
}