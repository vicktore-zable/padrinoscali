<?php
$file = 'H:\Mi unidad\2026\yumbo\Concejos_valle_2019_2023.csv';
$handle = fopen($file, 'r');
$found2019 = false;
$found2023 = false;
$lineCount = 0;
while (!feof($handle) && ($lineCount < 10000000)) {
    $line = fgets($handle);
    $lineCount++;
    if (!$found2019 && strpos($line, '2019') !== false) {
        $found2019 = [$lineCount, trim($line)];
    }
    if (!$found2023 && strpos($line, '2023') !== false) {
        $found2023 = [$lineCount, trim($line)];
    }
}
fclose($handle);
echo json_encode([
    'found2019' => $found2019,
    'found2023' => $found2023,
    'total_checked' => $lineCount
], JSON_PRETTY_PRINT);
