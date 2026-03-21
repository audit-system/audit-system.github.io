<?php
require_once 'config.php';

$users = [
    'shift_leader_1'   => 'sl1pass',
    'shift_leader_2'   => 'sl2pass',
    'segment_leader_1' => 'segl1pass',
    'segment_leader_2' => 'segl2pass',
    'directeur_prod'   => 'dirpass'
];

$pdo = db();
$stmt = $pdo->prepare("UPDATE users SET password=? WHERE username=?");

foreach ($users as $user => $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt->execute([$hash, $user]);
    echo "$user updated<br>";
}