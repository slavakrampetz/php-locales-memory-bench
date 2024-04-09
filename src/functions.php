<?php

/** @noinspection UnknownInspectionInspection */

const EE_OUT_MAX_LEN = 120;

function rnd(int $min, int $max): int {
	/** @noinspection PhpUnhandledExceptionInspection */
	return random_int($min, $max);
}


function measureData(array $block): array {
	$bytes = 0;
	$chars = 0;
	$min = 5000;
	$max = 0;
	$nof = count($block);

	foreach ($block as $row) {
		$bytes += strlen($row);
		$len = mb_strlen($row);
		$min = min($min, $len);
		$max = max($max, $len);
		$chars += $len;
	}

	return [$nof, $chars, $bytes, $min, $max];
}

/**
 * @param array $args
 * @return false
 */
function ee(...$args): bool {
	echo
	date('H:i:s '),
	vsl(PHP_EOL . '  ', EE_OUT_MAX_LEN, ...$args),
	PHP_EOL;
	return false;
}

/**
 * Dump some variables to string, separate lines at length $maxLen by $separator
 *
 * @param string $separator Separator
 * @param int $maxLen Maximum length of characters at one line
 * @param array $args Variables
 * @return string
 */
function vsl(string $separator, int $maxLen, ...$args): string {
	$res = '';
	$nof = 0;
	if ($maxLen < 10) {
		$maxLen = 80;
	}
	foreach ($args as $arg) {
		$len = 0;
		$s = vdl($arg, $len);
		$sep = ' ';
		if ($len + 1 + $nof > $maxLen) {
			$sep = $separator;
			$nof = 0;
		} else if ($nof > 0) {
			$nof += 1 + $len;
		} else {
			if ($len <= 0) {
				$sep = '';
			}
			$nof += $len;
		}
		$res .= $sep . $s;
	}
	return $res;
}

/**
 * Dump one variable to string,
 *
 * @param mixed $arg Variable
 * @param int $len Length of result string, returned
 * @return string
 */
function vdl(mixed $arg, int &$len): string {
	if (null === $arg) {
		$msg = 'null';
	} else if (is_string($arg)) {
		$msg = $arg;
	} else if (is_bool($arg)) {
		$msg = $arg ? 'true' : 'false';
	} else if (is_scalar($arg)) {
		$msg = $arg;
	} else {
		$msg = var_export($arg, true);
		$msg = preg_replace('/(\s*\n)?\s*array\s*\(/', ' [', $msg);
		$msg = preg_replace('/,?\s*\)(,\s*\n)?/', ']\1', $msg);
	}
	$msg = trim($msg);
	$len = mb_strlen($msg);
	return $msg;
}


function formatInt(int $num): string {
	// &#8239; -- unicode narrow non-breaking space -- не работает на iOs
	// &#8381; -- unicode RUR
	//return number_format($num, 0, '.', '&#8239;'). '&#8239;&#8381;';
	return number_format($num, 0, '.', '_');
}
