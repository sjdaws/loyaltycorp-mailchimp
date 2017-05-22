<?php

require __DIR__ . '/../vendor/autoload.php';

// Execute the command and store the process id so we can kill it when testing is over
$command = sprintf('php -S %s:%d -t %s >/dev/null 2>&1 & echo $!', SERVER_HOST, SERVER_PORT, SERVER_ROOT);
$output = [];
exec($command, $output);
$pid = (int)reset($output);

echo PHP_EOL . sprintf('Started test web server on http://%s:%d with PID %d', SERVER_HOST, SERVER_PORT, $pid) . PHP_EOL . PHP_EOL;

// Register method to kill the test server if it's still running on shutdown
register_shutdown_function(function() use ($pid) {
    echo PHP_EOL . sprintf('Killing test web server with PID %d', $pid) . PHP_EOL;

    // Only kill PID if it's still running to prevent this from giving tests a false positive
    if (posix_kill($pid, 0)) {
        exec('kill ' . $pid);
    }
});

