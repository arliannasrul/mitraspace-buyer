<?php

// Enable error reporting for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Forward request to Laravel public/index.php for Vercel Serverless Functions
try {
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo "<html><body>";
    echo "<h1>PHP Startup Debug Error:</h1>";
    echo "<p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><b>File:</b> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</body></html>";
}
