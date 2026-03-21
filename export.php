<?php
// ================================================
// export.php — Export JSON pour VBA Excel
//
// Appeler depuis VBA :
//   http://localhost/audit2/export.php
//   http://localhost/audit2/export.php?niveau=3
//   http://localhost/audit2/export.php?niveau=3&limit=50
// ================================================
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $where  = [];
    $params = [];

    if (!empty($_GET['niveau']) && in_array((int)$_GET['niveau'], [1,2,3])) {
        $where[]  = 'niveau = ?';
        $params[] = (int)$_GET['niveau'];
    }
//    $limit    = min((int)($_GET['limit'] ?? 500), 1000);
//    $clause   = $where ? 'WHERE ' . implode(' AND ', $where) : '';
//
//    $params[] = $limit;
//    $st = db()->prepare("SELECT id, date_audit, date_saisie, niveau, username,
//        nom_auditeur, zone, shift, semaine, mois, reponses, observations,
//        conformes, non_conformes, score
//        FROM soumissions $clause ORDER BY date_saisie DESC LIMIT ?");
//    $st->execute($params);

    $limit  = min((int)($_GET['limit'] ?? 500), 1000);
    $clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $st = db()->prepare("SELECT id, date_audit, date_saisie, niveau, username,
    nom_auditeur, zone, shift, semaine, mois, reponses, observations,
    conformes, non_conformes, score
    FROM soumissions $clause
    ORDER BY date_saisie DESC
    LIMIT $limit");

    $st->execute($params);
    $rows = $st->fetchAll();

    foreach ($rows as &$r) {
        $r['reponses']      = json_decode($r['reponses'] ?? '{}', true);
        $r['niveau']        = (int)$r['niveau'];
        $r['conformes']     = (int)$r['conformes'];
        $r['non_conformes'] = (int)$r['non_conformes'];
        $r['score']         = (int)$r['score'];
    }

    // Stats par niveau (bonus pour VBA)
    $stats = [];
    foreach ([1,2,3] as $lv) {
        $s = db()->prepare('SELECT COUNT(*) nb, IFNULL(ROUND(AVG(score),1),0) moy FROM soumissions WHERE niveau=?');
        $s->execute([$lv]);
        $x = $s->fetch();
        $stats["lpa$lv"] = ['nb' => (int)$x['nb'], 'score_moyen' => (float)$x['moy']];
    }

    echo json_encode([
        'statut'      => 'ok',
        'total'       => count($rows),
        'stats'       => $stats,
        'soumissions' => $rows,
        'genere_le'   => date('Y-m-d H:i:s'),
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['statut' => 'erreur', 'message' => $e->getMessage()]);
}
