<?php
// Pruebas de control de acceso por dueño (IDOR): un usuario NO debe poder
// leer/modificar/borrar datos de otro a través de los modelos.
require __DIR__ . '/../config/init.php';
require __DIR__ . '/assert.php';

$pdo = Database::getInstance()->getConnection();
register_shutdown_function(fn() => $pdo->inTransaction() && $pdo->rollBack());
$pdo->beginTransaction();

$uA = 1; // admin demo
$uB = 2; // student demo

// ── Flashcard ──
$f = new Flashcard();
$fid = $f->create(['user_id' => $uA, 'deck' => 'vocab', 'front' => 'x', 'back' => 'y']);
eq('A ve su tarjeta', true, $f->findById($fid, $uA) !== false);
eq('B NO ve la tarjeta de A (IDOR)', false, $f->findById($fid, $uB) !== false);
eq('B no puede repasar la de A', false, $f->review($fid, $uB, 5));
$f->delete($fid, $uB); // intento de borrado ajeno
eq('borrado de B no afecta a A', true, $f->findById($fid, $uA) !== false);

// ── Goal ──
$g = new Goal();
$gid = $g->create($uA, 'MetaA', '', null);
eq('A ve su meta', true, $g->findById($gid, $uA) !== false);
eq('B NO ve la meta de A', false, $g->findById($gid, $uB) !== false);
$g->setPublic($gid, $uB, true); // B intenta publicar la meta de A
$still = $g->findById($gid, $uA);
eq('B no pudo publicar la meta de A', 0, (int) $still['is_public']);

// ── Unit (sobre recurso de A) ──
$rid = (int) $pdo->query("SELECT id FROM resources WHERE user_id = $uA AND deleted_at IS NULL LIMIT 1")->fetchColumn();
if ($rid) {
    $un = new Unit();
    $unitId = $un->create($rid, 'Capítulo de prueba');
    eq('unitOwned de A: ok', true, $un->unitOwned($unitId, $uA) !== false);
    eq('unitOwned de B: falla (IDOR)', false, $un->unitOwned($unitId, $uB) !== false);
}

// ── CpProblem ──
$cp = new CpProblem();
$cid = $cp->create(['user_id' => $uA, 'name' => 'Problema A', 'status' => 'solved']);
eq('A ve su problema', true, $cp->findById($cid, $uA) !== false);
eq('B NO ve el problema de A', false, $cp->findById($cid, $uB) !== false);

$pdo->rollBack();
done();
