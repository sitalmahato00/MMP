<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->query('CREATE DATABASE IF NOT EXISTS CMS');
    echo 'Database created successfully' . PHP_EOL;
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
