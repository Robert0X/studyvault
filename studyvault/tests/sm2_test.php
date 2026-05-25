<?php
require __DIR__ . '/../config/init.php';
require __DIR__ . '/assert.php';

// Primer acierto (quality 5, fácil): interval=1, reps=1, ef sube a 2.6
$r = Flashcard::sm2(2.5, 0, 0, 5);
eq('1er acierto -> interval 1', 1, $r['interval_days']);
eq('1er acierto -> reps 1', 1, $r['repetitions']);
eq('1er acierto -> ef 2.6', 2.6, round($r['ease_factor'], 4));

// Segundo acierto: interval=6, reps=2
$r = Flashcard::sm2(2.5, 1, 1, 5);
eq('2do acierto -> interval 6', 6, $r['interval_days']);
eq('2do acierto -> reps 2', 2, $r['repetitions']);

// Tercer acierto: interval = round(6 * 2.6) = 16
$r = Flashcard::sm2(2.6, 2, 6, 4);
eq('3er acierto -> interval round(6*2.6)=16', 16, $r['interval_days']);

// Fallo (quality < 3): reinicia interval=1, reps=0
$r = Flashcard::sm2(2.5, 5, 30, 2);
eq('fallo -> interval 1', 1, $r['interval_days']);
eq('fallo -> reps 0', 0, $r['repetitions']);

// Piso del ease factor en 1.3
$r = Flashcard::sm2(1.3, 0, 0, 2);
eq('ef no baja de 1.3', 1.3, round($r['ease_factor'], 4));

// Estados de madurez
$r = Flashcard::sm2(2.5, 2, 12, 5); // round(12*2.5)=30 -> mature
eq('interval 30 -> mature', 'mature', $r['status']);
$r = Flashcard::sm2(2.5, 2, 40, 5); // round(40*2.5)=100 -> mastered
eq('interval 100 -> mastered', 'mastered', $r['status']);

done();
