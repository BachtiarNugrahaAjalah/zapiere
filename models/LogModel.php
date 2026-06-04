<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Mengambil log aktifitas terbaru beserta nama dan role user.
 * Menggunakan VIEW `v_log_aktifitas` dari database.
 *
 * @param int $limit  Jumlah log yang diambil (default: 6)
 */
function recent_logs(int $limit = 6): array
{
    $limit = (int) $limit;
    return db_all("
        SELECT *
        FROM v_log_aktifitas
        ORDER BY tgl_aktifitas DESC
        LIMIT {$limit}
    ");
}
