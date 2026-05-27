<?php
require __DIR__ . '/../config/init.php';
require __DIR__ . '/assert.php';

$pdo = Database::getInstance()->getConnection();
register_shutdown_function(fn() => $pdo->inTransaction() && $pdo->rollBack());
$pdo->beginTransaction();

// ── CSRF (función pura) ──
$_SESSION['csrf_token'] = 'token_de_prueba_123';
eq('csrf token válido', true, csrf_check('token_de_prueba_123'));
eq('csrf token inválido', false, csrf_check('otro'));
eq('csrf token vacío', false, csrf_check(''));
eq('csrf token null', false, csrf_check(null));

// ── User: hashing y verificación ──
$u = new User();
$email = 'qa_' . uniqid() . '@studyvault.test';
$id = $u->create('QA', $email, 'secret123');
eq('usuario creado', true, $id > 0);
$row = $u->findByEmail($email);
eq('contraseña hasheada (no en claro)', false, $row['password'] === 'secret123');
eq('verifyPassword correcta', true, $u->verifyPassword('secret123', $row['password']));
eq('verifyPassword incorrecta', false, $u->verifyPassword('mala', $row['password']));
eq('emailExists verdadero', true, $u->emailExists($email));
eq('emailExists falso', false, $u->emailExists('no_existe_' . uniqid() . '@x.com'));

// ── LoginThrottle: bloqueo por fuerza bruta ──
$t   = new LoginThrottle(3, 15); // 3 intentos / 15 min
$ip  = '203.0.113.7';
$em  = 'brute_' . uniqid() . '@x.com';
eq('inicio: no bloqueado', false, $t->tooMany($ip, $em));
$t->record($ip, $em, false);
$t->record($ip, $em, false);
eq('2 fallos: aún permite (max 3)', false, $t->tooMany($ip, $em));
$t->record($ip, $em, false);
eq('3 fallos: bloquea', true, $t->tooMany($ip, $em));
eq('hay espera > 0', true, $t->secondsUntilRetry($ip, $em) > 0);
$t->clearFailures($em);
eq('clearFailures desbloquea', false, $t->tooMany($ip, $em));

$pdo->rollBack();
done();
