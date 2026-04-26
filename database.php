<?php
/**
 * Database connection (MySQLi + Prepared Statements).
 * Change the credentials below to match your MySQL server.
 */

/**
 * Database connection (MySQLi + Prepared Statements).
 * SET THESE FROM YOUR INFINITYFREE DASHBOARD!
 */

define('DB_HOST', 'sqlXXX.infinityfree.com'); // Find this in your Hosting Account -> MySQL Details
define('DB_USER', 'if0_xxxxxxx');            // Find this in your Hosting Account -> MySQL Details
define('DB_PASS', 'your_account_password');  // This is your InfinityFree hosting password
define('DB_NAME', 'if0_xxxxxxx_waste_db');   // The name of the database you create in cPanel

define('APP_NAME', 'EcoTrack – Waste Management System');

// Automatically detect URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domain   = $_SERVER['HTTP_HOST'];
define('APP_URL', $protocol . $domain); 


/**
 * Returns a shared mysqli connection.
 * Throws a fatal error with a readable page if it fails.
 */
function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_OFF); // we handle errors manually
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        if ($conn->connect_errno) {
            http_response_code(500);
            die(
                "<h2 style='font-family:sans-serif;color:#b91c1c'>Database connection failed</h2>" .
                "<p style='font-family:sans-serif'>" . htmlspecialchars($conn->connect_error) . "</p>" .
                "<p style='font-family:sans-serif'>Check <code>config/database.php</code> and make sure the schema is imported.</p>"
            );
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
