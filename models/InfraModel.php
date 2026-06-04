<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Mengambil data semua node database.
 */
function nodes_data(): array
{
    return db_all("SELECT * FROM node_database ORDER BY id_node");
}

/**
 * Mengambil status fragmentasi (tabel apa ditaruh di node mana).
 */
function fragments_data(): array
{
    return db_all("
        SELECT fd.*, nd.nama_node
        FROM fragmentasi_data fd
        LEFT JOIN node_database nd ON nd.id_node = fd.id_node
        ORDER BY fd.id_fragment
    ");
}

/**
 * Mengambil riwayat aktivitas backup antar node.
 */
function backups_data(): array
{
    return db_all("SELECT * FROM backup_sistem ORDER BY waktu_backup DESC");
}

/**
 * Mengambil log simulasi deadlock teratas.
 */
function deadlock_logs(): array
{
    return db_all("SELECT * FROM simulasi_deadlock_log ORDER BY created_at DESC, id_simulasi DESC LIMIT 8");
}
