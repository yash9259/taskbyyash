<?php
// InfinityFree database settings.
define('DB_HOST', 'sql107.infinityfree.com');
define('DB_PORT', 3306);
define('DB_USER', 'if0_41676112');
define('DB_PASS', 'gEE6TpVnEuA4u'); // Add your InfinityFree MySQL password here.
define('DB_NAME', 'if0_41676112_property_app'); // Use your exact InfinityFree DB name.

function getDbConnection(): mysqli
{
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    } catch (mysqli_sql_exception $e) {
        die(
            'Database connection failed. Verify InfinityFree DB host, username, password, and DB name in config.php. '
            . 'Current settings: host=' . DB_HOST . ', port=' . DB_PORT . ', user=' . DB_USER . ', db=' . DB_NAME
            . ' | Error: ' . $e->getMessage()
        );
    }

    if ($conn->connect_error) {
        die(
            'Database connection failed. Verify InfinityFree DB host, username, password, and DB name in config.php. '
            . 'Current settings: host=' . DB_HOST . ', port=' . DB_PORT . ', user=' . DB_USER . ', db=' . DB_NAME
            . ' | Error: ' . $conn->connect_error
        );
    }

    if (!$conn->set_charset('utf8mb4')) {
        die('Failed to set charset: ' . $conn->error);
    }

    return $conn;
}
