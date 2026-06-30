<?php
/**
 * ALAS - Procesador de Acciones Pendientes
 * Ejecutar cada 5 minutos via cron Hostinger
 * 
 * Cron: php /home/u577647812/domains/padrinoscali.org/public_html/aratio/cron/workflow_processor.php
 * Programar: cada 5 minutos en Hostinger
 */

require_once __DIR__ . '/../config/config.php';

if (php_sapi_name() !== 'cli') {
    die('Este script solo se ejecuta desde CLI');
}

require_once __DIR__ . '/../includes/WorkflowEngine.php';

$procesadas = WorkflowEngine::procesarPendientes();

echo date('[Y-m-d H:i:s]') . " ALAS Workflow: Procesadas {$procesadas} acciones pendientes\n";
