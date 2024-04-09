<?php

require_once __DIR__ . '/src/functions.php';

const ALLOWED =
	'abcdefjhijklmnopqrstuvwxyz' .
	'ABCDEFJHIJKLMNOPQRSTUVWXYZ' .
	'0123456789' .
	'[]!@#$%^&*()_+=-{}"\';:/?.>,<~`' .
	'абвгдеёжзийклмнопрстуфхцчшщъыьэюя' .
	'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ'
;

const LINE_LEN_MIN = 180;
const LINE_LEN_MAX = 1200;
const BLOCK_SIZE = 10 * 1024 * 1024;
const PARTICLES = 5000;

function randomString(int $len): string {
	$res = '';
	$max = mb_strlen(ALLOWED);
	for ($i = 0; $i < $len; $i++) {
		$idx = rnd(0, $max);
		$res .= mb_substr(ALLOWED, $idx, 1);
	}
	return $res;
}

// Сгенерировать 5000 разных кусков по 100 символов
$parts = [];
for ($i = 0; $i < PARTICLES; $i++) {
	$parts[] = randomString(20);
}

// Блоки, каждый 10Мб => ~2000 строк по 500 символов
$blocks = [];
for ($b = 1; $b <= 5; $b++) {
	$pfx = "key_{$b}_";

	$data = [];
	$count = 0;
	$current = '';
	$nof = 0;
	$nextLen = rnd(LINE_LEN_MIN, LINE_LEN_MAX);
	while ($count < BLOCK_SIZE) {
		$idx = rnd(0, PARTICLES - 1);
		$part = $parts[$idx];
		$len = strlen($part); // bytes

		// Выравниваем по концу блока
		if ($count + $len > BLOCK_SIZE) {
			$len = BLOCK_SIZE - $count;
			$part = mb_substr($part, 0, $len);
		}
		$count += $len;

		if ($nof + $len < $nextLen) {
			$current .= $part;
			$nof += $len;
			continue;
		}

		$key = $pfx . count($data);
		$data[$key] = $current;
		$current = '';
		$nof = 0;
		$nextLen = rnd(LINE_LEN_MIN, LINE_LEN_MAX);
	}
	if ($current !== '') {
		$key = $pfx . count($data);
		$data[$key] = $current;
	}

	$blocks[] = $data;
}


// Write
$prefix = <<<'TXT'
<?php

if (!isset($LOC)) {
	$LOC = [];
}
if (!array_key_exists('ru', $LOC)) {
	$LOC['ru'] = [];
}
$LOC['ru'] = array_merge($LOC['ru'], [

TXT;
$suffix = <<<'TXT'
]);

TXT;

foreach ($blocks as $idx => $block) {
	[$nof, $chars, $bytes, $min, $max] = measureData($block);

	$num = $idx + 1;
	ee("block $num:",
		formatInt($nof), 'rows',
		formatInt($chars), 'chars',
		formatInt($bytes), 'bytes',
		$min . '-' . $max, 'chars/line',
	);

	$array = [$prefix];
	$func = [$prefix];
	foreach ($block as $key => $row) {
		$row = str_replace('\'', '\\\'', $row);
		$array[] = "'$key' => '$row',";
		$func[] = "'$key' => static function() { return '$row';},";
	}
	$array[] = $suffix;
	$func[] = $suffix;

	file_put_contents("locale/array_$num.php", implode(PHP_EOL, $array));
	file_put_contents("locale/func_$num.php", implode(PHP_EOL, $func));
}

ee('Memory peak:', formatInt(memory_get_peak_usage()));
