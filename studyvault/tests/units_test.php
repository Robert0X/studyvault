<?php
require __DIR__ . '/../config/init.php';
require __DIR__ . '/assert.php';

// progressPct: porcentaje completado de un recurso según sus unidades
eq('sin unidades = 0%', 0, Unit::progressPct(0, 0));
eq('0 de 10 = 0%',      0, Unit::progressPct(0, 10));
eq('5 de 10 = 50%',     50, Unit::progressPct(5, 10));
eq('10 de 10 = 100%',   100, Unit::progressPct(10, 10));
eq('1 de 3 = 33%',      33, Unit::progressPct(1, 3));
eq('2 de 3 = 67%',      67, Unit::progressPct(2, 3));

done();
