<?php
// admin.php — Gestion des utilisateurs (niveau 1 uniquement)
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    echo json_encode(['ok' => false, 'err' => 'Non connecté']);
    exit;
}
if ((int)$_SESSION['user']['niveau'] !== 1) {
    echo json_encode(['ok' => false, 'err' => 'Accès refusé']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? $_GET['action'] ?? '';

try {
    // ── Lister tous les utilisateurs ──────────────────────────────
    if ($action === 'list') {
        $rows = db()->query('SELECT id, username, nom, niveau, role, zone FROM users ORDER BY niveau, username')->fetchAll();
        echo json_encode(['ok' => true, 'users' => $rows]);
        exit;
    }

    // ── Ajouter un utilisateur ────────────────────────────────────
    if ($action === 'add') {
        $username = trim($body['username'] ?? '');
        $nom      = trim($body['nom']      ?? '');
        $password = $body['password'] ?? '';
        $niveau   = (int)($body['niveau']  ?? 3);
        $role     = trim($body['role']     ?? '');
        $zone     = trim($body['zone']     ?? '');

        if (!$username || !$nom || strlen($password) < 6 || !in_array($niveau, [1,2,3])) {
            echo json_encode(['ok' => false, 'err' => 'Données invalides (mdp min 6 car.)']);
            exit;
        }

        // Vérifier unicité du username
        $chk = db()->prepare('SELECT id FROM users WHERE username = ?');
        $chk->execute([$username]);
        if ($chk->fetch()) {
            echo json_encode(['ok' => false, 'err' => "L'identifiant '$username' existe déjà"]);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $st = db()->prepare('INSERT INTO users (username, password, nom, niveau, role, zone) VALUES (?,?,?,?,?,?)');
        $st->execute([$username, $hash, $nom, $niveau, $role, $zone]);
        echo json_encode(['ok' => true, 'id' => (int)db()->lastInsertId()]);
        exit;
    }

    // ── Modifier un utilisateur ───────────────────────────────────
    if ($action === 'edit') {
        $id   = (int)($body['id'] ?? 0);
        $nom  = trim($body['nom']  ?? '');
        $role = trim($body['role'] ?? '');
        $zone = trim($body['zone'] ?? '');
        $niveau = isset($body['niveau']) ? (int)$body['niveau'] : null;

        if (!$id || strlen($nom) < 2) {
            echo json_encode(['ok' => false, 'err' => 'Données invalides']);
            exit;
        }

        if ($niveau && in_array($niveau, [1,2,3])) {
            db()->prepare('UPDATE users SET nom=?, role=?, zone=?, niveau=? WHERE id=?')
                ->execute([$nom, $role, $zone, $niveau, $id]);
        } else {
            db()->prepare('UPDATE users SET nom=?, role=?, zone=? WHERE id=?')
                ->execute([$nom, $role, $zone, $id]);
        }

        // Reset mot de passe si fourni
        if (!empty($body['password']) && strlen($body['password']) >= 6) {
            $hash = password_hash($body['password'], PASSWORD_BCRYPT);
            db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $id]);
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    // ── Supprimer un utilisateur ──────────────────────────────────
    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        // Protéger : ne pas supprimer l'admin courant
        if ($id === (int)$_SESSION['user']['id']) {
            echo json_encode(['ok' => false, 'err' => 'Impossible de se supprimer soi-même']);
            exit;
        }
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'err' => 'Action inconnue']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'err' => $e->getMessage()]);
}
