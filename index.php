<?php
require_once __DIR__ . '/config/config.php';

if (!empty($_SESSION['zapiere_user'])) {
    header('Location: ' . dashboard_url_for_role($_SESSION['zapiere_user']['role']));
    exit;
}

header('Location: ' . url_for('login.php'));
exit;
