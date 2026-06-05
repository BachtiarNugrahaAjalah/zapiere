<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/UserModel.php';

function require_role(string $role): array
{
    if (empty($_SESSION['zapiere_user'])) {
        header('Location: ' . url_for('login.php'));
        exit;
    }

    $user = $_SESSION['zapiere_user'];

    if (($user['role'] ?? '') !== $role) {
        header('Location: ' . url_for('login.php'));
        exit;
    }

    return $user;
}
