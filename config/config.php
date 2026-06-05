<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "zapiere";

/** @var \mysqli $conn */
$conn = null;

if (function_exists('mysqli_connect')) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect($host, $username, $password, $database);

    if ($conn) {
        mysqli_set_charset($conn, "utf8mb4");
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function db_available()
{
    global $conn;
    return $conn instanceof mysqli;
}

function db_escape($value)
{
    global $conn;
    return db_available() ? mysqli_real_escape_string($conn, (string) $value) : addslashes((string) $value);
}

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

function db_one($sql)
{
    $rows = db_all($sql);
    return $rows ? $rows[0] : null;
}

function db_value($sql, $default = 0)
{
    $row = db_one($sql);
    if (!$row) {
        return $default;
    }

    $values = array_values($row);
    return $values[0] ?? $default;
}

function db_exec($sql)
{
    global $conn;

    if (!db_available()) {
        return false;
    }

    return (bool) mysqli_query($conn, $sql);
}

function table_exists($table)
{
    $table = db_escape($table);
    return (bool) db_one("SHOW TABLES LIKE '{$table}'");
}

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

function url_for($path)
{
    return app_base_url() . '/' . ltrim($path, '/');
}

function asset_url($path)
{
    return url_for($path);
}
