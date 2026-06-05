<?php
require_once __DIR__ . '/../config/config.php';

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
