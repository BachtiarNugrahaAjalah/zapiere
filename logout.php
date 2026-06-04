<?php
require_once __DIR__ . '/config/config.php';

unset($_SESSION['zapiere_user']);

header('Location: ' . url_for('login.php'));
exit;
