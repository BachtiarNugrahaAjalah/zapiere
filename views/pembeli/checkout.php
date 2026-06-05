<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid']);
    exit;
}

if (empty($_SESSION['zapiere_user']) || ($_SESSION['zapiere_user']['role'] ?? '') !== 'pembeli') {
    echo json_encode(['success' => false, 'message' => 'Anda harus login sebagai pembeli']);
    exit;
}
$user = $_SESSION['zapiere_user'];


$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (empty($data['cart']) || !is_array($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja masih kosong']);
    exit;
}

global $conn;
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database tidak tersedia']);
    exit;
}

$idPembeli = (int)$user['id_user'];
$jsonCartData = json_encode($data['cart']);

try {
    $result = checkout($idPembeli, $jsonCartData);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}