#!/bin/sh

echo "Measure array-based locales"
php measure.php

echo "Measure func-based locales"
php measure.php func
