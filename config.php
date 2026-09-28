<?php
// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'furniture_management');

// Database Connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Royal Custom Connection Error Handling
if ($conn->connect_error) {
    die("
    <div style='
        font-family: Arial, sans-serif;
        background: #0f172a;
        color: #f8fafc;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        margin: 50px auto;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        border: 1px solid #d4af37;
        text-align: center;'>
        <h2 style='color: #ef4444; margin-top: 0;'>⚠️ Database Connection Failed</h2>
        <p style='color: #94a3b8;'>Unable to connect to the database system. Please check your credentials.</p>
        <div style='
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            font-family: monospace;'>
            " . htmlspecialchars($conn->connect_error) . "
        </div>
    </div>
    ");
}

// Set UTF-8 Encoding
$conn->set_charset("utf8mb4");

// Global System Settings
date_default_timezone_set('Asia/Colombo'); // Sri Lanka Timezone
?>