<?php
// data.php — Données filtrées selon le niveau de l'utilisateur
// Niveau 1 voit : niveaux 1, 2, 3
// Niveau 2 voit : niveaux 2, 3
// Niveau 3 voit : niveau 3 uniquement (ses propres soumissions)
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user'])) {
    echo json_encode(['ok' => false, 'rows' => []]);
    exit;
}

$u  = $_SESSION['user'];
$lv = (int)$u['niveau'];

// Niveaux visibles selon la règle métier
// Niveau 1 → voit tout (1,2,3)
// Niveau 2 → voit niveaux 2 et 3
// Niveau 3 → voit uniquement ses propres soumissions (niveau 3, son username)
try {
    if ($lv === 1) {
        $rows = db()->query('SELECT id, date_audit, date_saisie, niveau, username,
            nom_auditeur, zone, shift, semaine, mois, reponses, observations,
            conformes, non_conformes, score
            FROM soumissions ORDER BY date_saisie DESC LIMIT 300')->fetchAll();

    } elseif ($lv === 2) {
        $st = db()->prepare('SELECT id, date_audit, date_saisie, niveau, username,
            nom_auditeur, zone, shift, semaine, mois, reponses, observations,
            conformes, non_conformes, score
            FROM soumissions WHERE niveau >= 2
            ORDER BY date_saisie DESC LIMIT 300');
        $st->execute();
        $rows = $st->fetchAll();

    } else {
        // Niveau 3 : ses soumissions uniquement
        $st = db()->prepare('SELECT id, date_audit, date_saisie, niveau, username,
            nom_auditeur, zone, shift, semaine, mois, reponses, observations,
            conformes, non_conformes, score
            FROM soumissions WHERE username = ?
            ORDER BY date_saisie DESC LIMIT 300');
        $st->execute([$u['username']]);
        $rows = $st->fetchAll();
    }

    foreach ($rows as &$r) {
        $r['reponses']      = json_decode($r['reponses'] ?? '{}', true);
        $r['niveau']        = (int)$r['niveau'];
        $r['conformes']     = (int)$r['conformes'];
        $r['non_conformes'] = (int)$r['non_conformes'];
        $r['score']         = (int)$r['score'];
    }

    echo json_encode(['ok' => true, 'rows' => $rows]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'err' => $e->getMessage(), 'rows' => []]);
}
