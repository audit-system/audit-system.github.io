<?php
// login.php
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    echo json_encode(['ok' => true]);
    exit;
}

$body     = json_decode(file_get_contents('php://input'), true) ?? [];
$username = trim($body['username'] ?? '');
$password = $body['password']  ?? '';

if (!$username || !$password) {
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $st = db()->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([$username]);
    $u = $st->fetch();

    if (!$u) {
        echo json_encode(['ok' => false]);
        exit;
    }

//    // Vérifier le mot de passe : supporte bcrypt ET anciens mots de passe en clair
//    $ok = false;
//    if (password_verify($password, $u['password'])) {
//        $ok = true;
//    } elseif ($u['password'] === $password) {
//        // Ancien mdp en clair → on le migre vers bcrypt automatiquement
//        $hash = password_hash($password, PASSWORD_BCRYPT);
//        db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $u['id']]);
//        $ok = true;
//    }
    if (!$u) {
        $ok = false;
    } else {
        $ok = password_verify($password, $u['password']);
    }

    if (!$ok) {
        echo json_encode(['ok' => false]);
        exit;
    }

    $_SESSION['user'] = $u;
    echo json_encode(['ok' => true, 'user' => [
        'username' => $u['username'],
        'nom'      => $u['nom'],
        'niveau'   => (int)$u['niveau'],
        'role'     => $u['role'],
        'zone'     => $u['zone'],
    ]]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'err' => $e->getMessage()]);
}
