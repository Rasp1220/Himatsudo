<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$_ENV['JWT_SECRET']  = 'test-jwt-secret-key-for-unit-tests-only-32ch';
$_ENV['DB_DSN']      = 'sqlite::memory:';
$_ENV['DB_USER']     = '';
$_ENV['DB_PASSWORD'] = '';
