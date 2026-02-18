<?php
/**
 * Debug script to check environment variables
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$env_vars = [
    'DB_HOST' => getenv('DB_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'DB_NAME' => getenv('DB_NAME'),
    'DB_USER' => getenv('DB_USER'),
    'DB_PASS' => getenv('DB_PASS') ? '***hidden***' : 'NOT SET',
    'DB_CHARSET' => getenv('DB_CHARSET'),
    'MYSQL_HOST' => getenv('MYSQL_HOST'),
    'MYSQLHOST' => getenv('MYSQLHOST'),
    'MYSQL_URL' => getenv('MYSQL_URL') ? 'SET' : 'NOT SET',
    'MYSQL_PRIVATE_URL' => getenv('MYSQL_PRIVATE_URL') ? 'SET' : 'NOT SET',
];

echo json_encode([
    'success' => true,
    'environment_variables' => $env_vars,
    'all_env' => array_keys($_ENV)
], JSON_PRETTY_PRINT);
