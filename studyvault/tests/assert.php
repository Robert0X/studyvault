<?php
/** Mini arnés de pruebas (sin dependencias). Uso: eq($nombre,$esperado,$actual); ... done(); */
$GLOBALS['__t'] = ['pass' => 0, 'fail' => 0];

function eq(string $name, $expected, $actual): void {
    $ok = ($expected === $actual)
        || (is_float($expected) && is_numeric($actual) && abs($expected - (float)$actual) < 1e-9);
    if ($ok) {
        $GLOBALS['__t']['pass']++;
        echo "PASS: $name\n";
    } else {
        $GLOBALS['__t']['fail']++;
        echo "FAIL: $name (esperado " . json_encode($expected) . ", obtuve " . json_encode($actual) . ")\n";
    }
}

function done(): void {
    $t = $GLOBALS['__t'];
    echo "--- {$t['pass']} pass, {$t['fail']} fail ---\n";
    exit($t['fail'] > 0 ? 1 : 0);
}
