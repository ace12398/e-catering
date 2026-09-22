<?php

// Prepare writable storage directories in Vercel Serverless Environment (/tmp)
$tmpStorage = '/tmp/storage';
$dirs = [
    $tmpStorage . '/framework/views',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/cache',
    $tmpStorage . '/framework/testing',
    $tmpStorage . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Ensure SQLite database exists in writable /tmp directory
$tmpDb = '/tmp/database.sqlite';
$srcDb = __DIR__ . '/../database/seed_database.sqlite';
if (!file_exists($srcDb)) {
    $srcDb = __DIR__ . '/../database/database.sqlite';
}
if (!file_exists($tmpDb) && file_exists($srcDb)) {
    @copy($srcDb, $tmpDb);
}

// Forward request to Laravel public/index.php
require __DIR__ . '/../public/index.php';
