<?php
require 'config/config.php';
$db = getDB();
$s = $db->query('SELECT * FROM campanas');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
