<?php
require_once __DIR__ . '/../config/config.php';

function users_data()
{
    return db_all("SELECT * FROM users ORDER BY id_user");
}

function current_user($defaultRole = 'admin')
{
    if (!empty($_SESSION['zapiere_user'])) {
        return $_SESSION['zapiere_user'];
    }

    $role = db_escape($defaultRole);
    $user = db_one("SELECT * FROM users WHERE role = '{$role}' ORDER BY id_user LIMIT 1");

    return $user ?: [
        'id_user' => 0,
        'username' => 'guest',
        'nama' => 'Guest',
        'role' => 'guest',
        'saldo' => 0
    ];
}

function set_current_user(array $user): void
{
    $_SESSION['zapiere_user'] = [
        'id_user'  => $user['id_user'],
        'username' => $user['username'],
        'nama'     => $user['nama'],
        'role'     => $user['role'],
        'saldo'    => $user['saldo'] ?? 0,
    ];
}

function get_buyer_total_spent(int $buyerId): int
{
    return (int) db_value("SELECT f_total_belanja({$buyerId})");
}

function get_seller_stats(int $sellerId): array
{
    $row = db_one("
        SELECT 
            f_format_rupiah(f_total_omzet_penjual({$sellerId})) AS total_omzet_rp,
            f_jumlah_produk_toko({$sellerId}) AS total_produk
    ");

    return $row ?: ['total_omzet_rp' => 'Rp 0', 'total_produk' => 0];
}

function register_user(string $username, string $nama, string $password, string $role): array
{
    $username = db_escape($username);
    $nama = db_escape($nama);
    $password = db_escape($password);
    $role = db_escape($role);

    $sql = "CALL p_register_user('{$username}', '{$nama}', '{$password}', '{$role}')";
    
    global $conn;
    if (!db_available()) {
        return ['success' => false, 'message' => 'Database tidak tersedia.'];
    }

    if (mysqli_query($conn, $sql)) {
        return ['success' => true, 'message' => 'Pendaftaran berhasil. Silakan login.'];
    }

    return ['success' => false, 'message' => mysqli_error($conn)];
}

function dashboard_url_for_role(string $role): string
{
    $map = [
        'admin' => 'views/admin/dashboard.php',
        'penjual' => 'views/penjual/dashboard.php',
        'pembeli' => 'views/pembeli/dashboard.php',
    ];

    return url_for($map[$role] ?? 'login.php');
}
