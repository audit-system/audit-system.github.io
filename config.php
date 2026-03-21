<?php
// ================================================
// config.php — Connexion MySQL
// Modifier les 4 lignes ci-dessous
// ================================================
//define('DB_HOST', 'localhost');
//define('DB_NAME', 'lpa_db');
//define('DB_USER', 'root');
//define('DB_PASS', '');          // XAMPP = vide par défaut
define('DB_HOST', 'sql111.infinityfree.com');
define('DB_NAME', 'if0_41408771_lpa_db');
define('DB_USER', 'if0_41408771');
define('DB_PASS', '12CHAKIR');          // XAMPP = vide par défaut

function db() {
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    return $pdo;
}
