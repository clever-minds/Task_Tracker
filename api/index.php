<?php

// Silence PHP 8.5 deprecation warnings on Vercel production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

// Forward Vercel serverless requests to the public bootstrap
require __DIR__ . '/../public/index.php';
