<?php

$common->print_template_card('Log File Contents', 'start');

$log_file = $_GET['log'] ?? '';
$log_dir = $_SERVER['DOCUMENT_ROOT'] . '/logs/';
$full_path = $log_dir . basename($log_file);

if (file_exists($full_path) && is_readable($full_path)) {
    $log_contents = file_get_contents($full_path);
    $log_contents = htmlspecialchars($log_contents);

    echo "<h2>Contents of " . htmlspecialchars($log_file) . "</h2>";
    echo "<pre><code class='language-plaintext'>" . $log_contents . "</code></pre>";
} else {
    echo "<div class='alert alert-danger'>Error: Unable to read the log file or file does not exist.</div>";
}

$common->print_template_card('', 'end');
