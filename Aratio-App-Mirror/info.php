<?php
echo "<pre>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "REALPATH(__DIR__): " . realpath(__DIR__) . "\n";
echo "STR_REPLACE: " . str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__)) . "\n";
echo "</pre>";
