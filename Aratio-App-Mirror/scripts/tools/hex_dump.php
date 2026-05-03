<?php
$file = 'H:\Mi unidad\2026\yumbo\Concejos_valle_2019_2023.csv';
$handle = fopen($file, 'r');
$data = fread($handle, 200);
fclose($handle);
echo bin2hex($data);
