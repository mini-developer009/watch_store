<?php
// Start the session
session_start();

// --- DATABASE CONFIGURATION ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // <-- Your MySQL username
define('DB_PASS', ''); // <-- Your MySQL password
define('DB_NAME', 'watch_store');      // <-- Your MySQL database name

// --- ALPHA SMS API CONFIGURATION ---
// Get keys from https://alphasms.com.bd/
define('ALPHA_API_KEY', 'r8E8787c7Dybf2gVP5cJcJAweAfMxJa49Lk9T60k');      // <-- Your Alpha SMS API Key
define('ALPHA_SENDER_ID', 'Random');  // <-- Your Alpha SMS Sender ID

// --- PDO Database Connection ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>