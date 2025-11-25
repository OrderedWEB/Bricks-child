<?php
/*
Plugin Name: Test EL Loader
Description: Test if EL files can be loaded
*/

// Test 1: Check directory path
add_action('admin_notices', function() {
    $dir = get_stylesheet_directory();
    $file = $dir . '/inc/el-print-system.php';
    
    echo '<div class="notice notice-info"><p>';
    echo '<strong>Directory Test:</strong><br>';
    echo 'Path: ' . $file . '<br>';
    echo 'File exists: ' . (file_exists($file) ? 'YES' : 'NO') . '<br>';
    echo 'File readable: ' . (is_readable($file) ? 'YES' : 'NO') . '<br>';
    
    if (file_exists($file)) {
        // Try to load it
        require_once $file;
        echo 'File loaded successfully!<br>';
        echo 'EL_Print_System class exists: ' . (class_exists('EL_Print_System') ? 'YES' : 'NO');
    }
    
    echo '</p></div>';
});