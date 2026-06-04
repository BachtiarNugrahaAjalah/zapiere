<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "zapiere";

$conn = null;

if (function_exists('mysqli_connect')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect($host, $username, $password, $database);

    if ($conn) {
        mysqli_set_charset($conn, "utf8mb4");
    }
}

/**
 * Melakukan escape pada string HTML untuk mencegah serangan XSS.
 *
 * @param mixed $value Nilai yang akan di-escape.
 * @return string Nilai yang sudah aman dari karakter HTML berbahaya.
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Mengecek apakah koneksi database (MySQLi) tersedia atau tidak.
 *
 * @return bool True jika koneksi berhasil, False jika gagal.
 */
function db_available()
{
    global $conn;
    return $conn instanceof mysqli;
}

/**
 * Mengamankan input string dari karakter berbahaya sebelum dimasukkan ke dalam query SQL.
 * Mencegah SQL Injection.
 *
 * @param mixed $value Nilai yang akan di-escape.
 * @return string Nilai yang sudah di-escape.
 */
function db_escape($value)
{
    global $conn;
    return db_available() ? mysqli_real_escape_string($conn, (string) $value) : addslashes((string) $value);
}

/**
 * Menjalankan query SELECT dan mengembalikan semua baris hasil (Array Multidimensi).
 *
 * @param string $sql Query SQL SELECT.
 * @return array Array berisi semua baris hasil, kosong jika gagal/tidak ada data.
 */
function db_all($sql)
{
    global $conn;

    if (!db_available()) {
        return [];
    }

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        return [];
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Menjalankan query SELECT dan mengembalikan hanya baris pertama (Array Asosiatif).
 *
 * @param string $sql Query SQL SELECT.
 * @return array|null Baris pertama, atau null jika tidak ada data.
 */
function db_one($sql)
{
    $rows = db_all($sql);
    return $rows ? $rows[0] : null;
}

/**
 * Mengembalikan satu nilai tunggal dari hasil query (misal: hasil SELECT COUNT).
 *
 * @param string $sql Query SQL SELECT.
 * @param mixed $default Nilai default yang dikembalikan jika query tidak menemukan data.
 * @return mixed Nilai dari kolom pertama di baris pertama, atau nilai default.
 */
function db_value($sql, $default = 0)
{
    $row = db_one($sql);
    if (!$row) {
        return $default;
    }

    $values = array_values($row);
    return $values[0] ?? $default;
}

/**
 * Menjalankan query non-SELECT seperti INSERT, UPDATE, atau DELETE.
 *
 * @param string $sql Query SQL yang akan dieksekusi.
 * @return bool True jika eksekusi berhasil, False jika gagal.
 */
function db_exec($sql)
{
    global $conn;

    if (!db_available()) {
        return false;
    }

    return (bool) mysqli_query($conn, $sql);
}

/**
 * Mengecek apakah sebuah tabel ada di dalam database.
 *
 * @param string $table Nama tabel yang dicek.
 * @return bool True jika tabel ditemukan.
 */
function table_exists($table)
{
    $table = db_escape($table);
    return (bool) db_one("SHOW TABLES LIKE '{$table}'");
}

/**
 * Mendapatkan URL dasar (Base URL) dari root aplikasi web saat ini.
 *
 * @return string Base URL (misal: /zapiere)
 */
function app_base_url()
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $viewsPos = strpos($scriptName, '/views/');

    if ($viewsPos !== false) {
        return rtrim(substr($scriptName, 0, $viewsPos), '/');
    }

    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return $dir === '/' ? '' : $dir;
}

/**
 * Membuat URL lengkap (absolute URL) untuk sebuah path/halaman internal.
 *
 * @param string $path Path relatif (misal: views/admin/dashboard.php)
 * @return string URL absolut ke path tersebut.
 */
function url_for($path)
{
    return app_base_url() . '/' . ltrim($path, '/');
}

/**
 * Membuat URL lengkap menuju file aset statis (CSS, JS, Gambar).
 *
 * @param string $path Path relatif aset (misal: assets/images/logo.png)
 * @return string URL absolut ke file aset tersebut.
 */
function asset_url($path)
{
    return url_for($path);
}

