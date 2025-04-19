<?php
/**
 * Debug utility file - can be included to log information or used directly
 */

function logToFile($data, $title = null) {
    $logFile = __DIR__ . '/debug-log.txt';
    $timestamp = date('Y-m-d H:i:s');
    
    $log = "==== $timestamp ====\n";
    if ($title) {
        $log .= "=== $title ===\n";
    }
    
    if (is_array($data) || is_object($data)) {
        $log .= print_r($data, true);
    } else {
        $log .= $data;
    }
    $log .= "\n\n";
    
    file_put_contents($logFile, $log, FILE_APPEND);
}

// If called directly, dump useful debug info
if (basename($_SERVER['PHP_SELF']) === 'debug.php') {
    header('Content-Type: text/plain');
    echo "=== PHP INFO ===\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";
    
    echo "=== SESSION ===\n";
    session_start();
    print_r($_SESSION);
    echo "\n";
    
    echo "=== POST DATA ===\n";
    print_r($_POST);
    echo "\n";
    
    echo "=== GET DATA ===\n";
    print_r($_GET);
    echo "\n";
    
    echo "=== FILES ===\n";
    print_r($_FILES);
    echo "\n";
    
    echo "=== SERVER ===\n";
    $safeServerVars = array_intersect_key(
        $_SERVER, 
        array_flip(['REQUEST_METHOD', 'HTTP_HOST', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'REMOTE_ADDR'])
    );
    print_r($safeServerVars);
}
