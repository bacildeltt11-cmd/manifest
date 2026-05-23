<?php
/**
 * Configuration file for Manifest Cargo System
 * Move sensitive credentials to environment variables in production
 */

// Database configuration
$mongodb_uri = getenv('MONGODB_URI') ?: 'mongodb+srv://sagitarifqy11_db_user:7AKKNkDPN1wjVj9C@cluster0.ctftqar.mongodb.net/';
$database = getenv('MONGODB_DB') ?: 'cargo_manifest';

// App configuration
$app_name = 'CV. MANUNGGAL - Cargo Manifest System';
$version = '1.0.0';

// Security
$csrf_token_lifetime = 3600; // 1 hour in seconds
