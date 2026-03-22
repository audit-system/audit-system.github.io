<?php
// ================================================
// config.php — Connexion MySQL
// Modifier les 4 lignes ci-dessous
// ================================================
//define('DB_HOST', 'localhost');
//define('DB_NAME', 'lpa_db');
//define('DB_USER', 'root');
//define('DB_PASS', '');          // XAMPP = vide par défaut
//define('DB_HOST', 'sql.freedb.tech');
//define('DB_NAME', 'freedb_audit_db');
//define('DB_USER', 'freedb_auditdb_user');
//define('DB_PASS', 'avvZVJ@Ab2F3na&');          // XAMPP = vide par défaut

define('DB_HOST', 'db.fr-pari1.bengt.wasmernet.com');
define('DB_NAME', 'dbHZLNNdXPdhYjLMBKaYs3zY');
define('DB_USER', 'f3562a7b76b98000c108b810145f');
define('DB_PASS', '069bf356-2a7b-77c2-8000-abe2733fecfc');          // XAMPP = vide par défaut
   
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
