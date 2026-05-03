<?php
$file = 'H:\Mi unidad\2026\yumbo\Concejos_valle_2019_2023.csv';
$handle = fopen($file, 'r');
if ($handle) {
    $header = fgets($handle);
    $row1 = fgets($handle);
    $row2 = fgets($handle);
    fclose($handle);
    $result = [
        'header' => trim($header),
        'row1' => trim($row1),
        'row2' => trim($row2),
        'separator' => ';'
    ];
    file_put_contents('csv_analysis_results.json', json_encode($result, JSON_PRETTY_PRINT));
    echo "Done";
} else {
    echo "Error opening file";
}
