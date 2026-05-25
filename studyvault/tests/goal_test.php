<?php
require __DIR__ . '/../config/init.php';
require __DIR__ . '/assert.php';

// Progreso ponderado de una meta a partir de sus recursos
eq('sin recursos = 0', 0, Goal::weightedProgress([]));
eq('un recurso 100% = 100', 100, Goal::weightedProgress([['pct' => 100, 'weight' => 1]]));
eq('50 y 100 pesos iguales = 75', 75, Goal::weightedProgress([['pct' => 50, 'weight' => 1], ['pct' => 100, 'weight' => 1]]));
eq('ponderado 0(p3) 100(p1) = 25', 25, Goal::weightedProgress([['pct' => 0, 'weight' => 3], ['pct' => 100, 'weight' => 1]]));
eq('peso 0 no rompe = 0', 0, Goal::weightedProgress([['pct' => 80, 'weight' => 0]]));

// Ritmo (pace) hacia la fecha objetivo
$p = Goal::pace('2026-05-01', '2026-05-11', 50, '2026-05-06'); // mitad del tiempo
eq('pace esperado 50%', 50, $p['expected_pct']);
eq('pace en camino (50>=50)', true, $p['on_track']);
eq('pace dias restantes 5', 5, $p['days_left']);

$p2 = Goal::pace('2026-05-01', '2026-05-11', 20, '2026-05-09'); // 80% esperado, 20% real
eq('pace atrasado', false, $p2['on_track']);

$p3 = Goal::pace('2026-05-01', null, 30, '2026-05-09'); // sin fecha límite
eq('sin fecha -> has_target false', false, $p3['has_target']);

done();
