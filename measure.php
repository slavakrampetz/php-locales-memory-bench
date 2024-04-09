<?php

ini_set('memory_limit', -1);

function mem(int $i): string {
	$mem = memory_get_usage(true);
	$peak = memory_get_peak_usage(true);
	return "$i\t$mem\t$peak";
}

$prefix = $argv[1] ?? 'array';

$out = [
	"iter\tmemory\tpeak",
	mem(0),
];

for ($i = 1; $i <= 5; $i++) {
	require __DIR__ . "/locale/{$prefix}_$i.php";
	$out[] = mem($i);
}

echo implode(PHP_EOL, $out), PHP_EOL;
