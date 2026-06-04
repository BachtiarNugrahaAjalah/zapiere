<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Mengambil semua pesanan beserta total bayar, jumlah item, dan daftar produk.
 * Menggunakan VIEW `v_pesanan_lengkap` dari database.
 *
 * @param int|null $buyerId  Filter berdasarkan id_user pembeli (opsional)
 */
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

/**
 * Mengambil total nilai pendapatan seluruh sistem, langsung dalam format rupiah.
 */
function get_total_revenue(): string
{
    $val = db_value("SELECT f_format_rupiah(SUM(total_bayar)) FROM v_pesanan_lengkap");
    return $val ? (string) $val : 'Rp 0';
}
