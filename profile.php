<?php
// profile.php — Modifier son propre nom et mot de passe
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    echo json_encode(['ok' => false, 'err' => 'Non connecté']);
    exit;
}

$u    = $_SESSION['user'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

try {
    // ── Modifier le nom ────────────────────────────────────────────
    if ($action === 'rename') {
        $nom = trim($body['nom'] ?? '');
        if (strlen($nom) < 2) {
            echo json_encode(['ok' => false, 'err' => 'Nom trop court']);
            exit;
        }
        db()->prepare('UPDATE users SET nom = ? WHERE id = ?')->execute([$nom, $u['id']]);
        $_SESSION['user']['nom'] = $nom;
        echo json_encode(['ok' => true, 'nom' => $nom]);
        exit;
    }

    // ── Modifier le mot de passe ───────────────────────────────────
    if ($action === 'passwd') {
        $old = $body['old'] ?? '';
        $new = $body['new'] ?? '';

        if (strlen($new) < 6) {
            echo json_encode(['ok' => false, 'err' => 'Nouveau mot de passe trop court (min 6 car.)']);
            exit;
        }

        // Récupérer le hash actuel depuis la BDD
        $st = db()->prepare('SELECT password FROM users WHERE id = ?');
        $st->execute([$u['id']]);
        $row = $st->fetch();
        $hash = $row['password'];

        // Vérifier l'ancien (supporte bcrypt + ancien clair)
        $ok = password_verify($old, $hash) || ($hash === $old);
        if (!$ok) {
            echo json_encode(['ok' => false, 'err' => 'Mot de passe actuel incorrect']);
            exit;
        }

        $newHash = password_hash($new, PASSWORD_BCRYPT);
        db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $u['id']]);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'err' => 'Action inconnue']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'err' => $e->getMessage()]);
}
