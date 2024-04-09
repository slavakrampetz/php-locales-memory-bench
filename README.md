# Measure PHP locales memory usage

Main goal is find how much memory will be eaten on each Megabyte of text locales.

## For run

```sh
chmod +x run.sh
./run.sh
```

## Current results

Average increasing of memory usage after include locales containung about 10Mb of text
|Test|By `memory_get_usage`|By `memory_get_peak_usage`|Max, per Mb|
| :--------------------- | ---------------: | ---------------: | ---: |
| Array-based locales    | 14 050 918 bytes | 15 609 037 bytes | 1,49 |
| Function-based locales | 31 666 995 bytes | 33 311 949 bytes | 3,18 |


## Re-generate locales
See `generate.php` for details of locales generation.
