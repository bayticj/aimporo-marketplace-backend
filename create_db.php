<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '!Wh1skey');
    echo "Connected to MySQL successfully.\n";
    
    $pdo->exec('CREATE DATABASE IF NOT EXISTS aimporo_marketplace');
    echo "Database created successfully.\n";
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
} 