<?php
// save.php — Enregistrement d'un audit
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) { echo json_encode(['ok' => false, 'err' => 'Non connecté']); exit; }

$u = $_SESSION['user'];
$b = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $rep = is_array($b['reponses']) ? json_encode($b['reponses']) : ($b['reponses'] ?? '{}');
    $O   = (int)($b['conformes']     ?? 0);
    $X   = (int)($b['non_conformes'] ?? 0);
    $sc  = ($O + $X) > 0 ? round($O / ($O + $X) * 100) : 0;

    // Images NA (optionnel) — stockées en JSON base64
    $images = null;
    if (!empty($b['images']) && is_array($b['images'])) {
        $images = json_encode($b['images']);
    }

    // Vérifie si la colonne images existe, sinon insère sans elle
    try {
        $st = db()->prepare('INSERT INTO soumissions
            (date_audit, niveau, username, nom_auditeur, zone, shift, semaine, mois,
             reponses, observations, conformes, non_conformes, score, images)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $st->execute([
            $b['date_audit'] ?? date('Y-m-d'),
            (int)($b['niveau'] ?? $u['niveau']),
            $u['username'], $u['nom'],
            $b['zone']    ?? '', $b['shift']   ?? '',
            $b['semaine'] ?? '', $b['mois']    ?? '',
            $rep, $b['observations'] ?? '',
            $O, $X, $sc, $images,
        ]);
    } catch (PDOException $ex) {
        // Colonne images absente → fallback sans elle
        if (strpos($ex->getMessage(), 'images') !== false) {
            $st = db()->prepare('INSERT INTO soumissions
                (date_audit, niveau, username, nom_auditeur, zone, shift, semaine, mois,
                 reponses, observations, conformes, non_conformes, score)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $st->execute([
                $b['date_audit'] ?? date('Y-m-d'),
                (int)($b['niveau'] ?? $u['niveau']),
                $u['username'], $u['nom'],
                $b['zone']    ?? '', $b['shift']   ?? '',
                $b['semaine'] ?? '', $b['mois']    ?? '',
                $rep, $b['observations'] ?? '',
                $O, $X, $sc,
            ]);
        } else {
            throw $ex;
        }
    }

    echo json_encode(['ok' => true, 'id' => (int)db()->lastInsertId(), 'score' => $sc]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'err' => $e->getMessage()]);
}
