<?php

/**
 * This script helps set up the database for the Aimporo Marketplace application.
 * Run this script after installing XAMPP and creating the aimporo_marketplace database.
 */

echo "Aimporo Marketplace - Database Setup Script\n";
echo "==========================================\n\n";

// Check if the script is being run from the command line
if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

// Function to run an Artisan command
function run_command($command) {
    echo "Running: php artisan $command\n";
    $output = [];
    exec("php artisan $command 2>&1", $output, $return_var);
    
    // Print the command output
    foreach ($output as $line) {
        echo "$line\n";
    }
    
    // Check if it's a migration that already exists error, which we can ignore
    $error_message = implode("\n", $output);
    if ($return_var !== 0 && 
        !(strpos($error_message, "Migration already exists") !== false || 
          strpos($error_message, "already exists") !== false)) {
        echo "Error running command: php artisan $command\n";
        exit(1);
    }
    
    echo "Command completed.\n\n";
}

echo "Step 1: Creating sessions table...\n";
run_command("session:table");

echo "Step 2: Creating cache table...\n";
run_command("cache:table");

echo "Step 3: Creating queue table...\n";
run_command("queue:table");

echo "Step 4: Running migrations...\n";
run_command("migrate");

echo "Step 5: Clearing cache...\n";
run_command("cache:clear");
run_command("config:clear");
run_command("route:clear");
run_command("view:clear");

echo "\nDatabase setup completed successfully!\n";
echo "You can now start the Laravel development server with: php artisan serve\n"; 