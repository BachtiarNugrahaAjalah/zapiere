<?php
require_once __DIR__ . '/../config/config.php';

function orders_with_total(?int $buyerId = null): array
{
    $where = $buyerId !== null ? "WHERE id_user = {$buyerId}" : '';

    return db_all("
        SELECT *
        FROM v_pesanan_lengkap
        {$where}
        ORDER BY tanggal DESC
    ");
}

function get_total_revenue(): string
{
    $val = db_value("SELECT f_format_rupiah(SUM(total_bayar)) FROM v_pesanan_lengkap");
    return $val ? (string) $val : 'Rp 0';
}

function detail_pesanan(int $idPesanan) {
    return db_all("
        SELECT dp.id_pesanan, p.nama AS nama_produk, p.harga, dp.jumlah, p.foto_barang
        FROM detail_pesanan dp
        INNER JOIN produk p ON dp.id_produk = p.id_produk
        WHERE dp.id_pesanan = {$idPesanan}
    ");
}

function daftar_produk_terjual(int $id_user) {
    return db_all("
        SELECT p.nama AS nama_produk, p.harga, p.foto_barang, COALESCE(SUM(dp.jumlah), 0) AS jumlah_terjual
        FROM detail_pesanan dp
        RIGHT JOIN produk p ON dp.id_produk = p.id_produk
        WHERE p.id_user = {$id_user}
        GROUP BY p.id_produk
        ORDER BY jumlah_terjual DESC, p.nama ASC
    ");
}